/*
  Block Puzzle（Block Blast 风格）— 纯原生 Canvas，无外部依赖

  关键数据结构：
  - Grid: 10x10，cell = 0(空) 或 {c:colorIndex, t:placedAtMs} 的简化编码（实际用 number + 动画表）
  - BlockDef: { id, cells:[{x,y}], size:{w,h}, weight, name }
  - BlockInstance: { def, colorIndex, used:boolean }
  - GameState: grid, hand(3 blocks), score, combo, turn, selectedIdx, gameOver
  - Animations:
      - placePops: Map key "x,y" -> {t0,dur}
      - clearAnims: Array<{t0,dur,cells:[{x,y}], kind:'row'|'col'}>
      - particles: Array<{x,y,vx,vy,t0,dur,color}>
*/

(() => {
  'use strict';

  /** @type {HTMLCanvasElement} */
  const canvas = document.getElementById('gameCanvas');
  const scoreEl = document.getElementById('score');
  const comboEl = document.getElementById('combo');
  const turnEl = document.getElementById('turn');
  const newGameBtn = document.getElementById('newGameBtn');
  const restartBtn = document.getElementById('restartBtn');
  const overlay = document.getElementById('gameOverOverlay');
  const gameOverText = document.getElementById('gameOverText');

  const trayEl = document.getElementById('tray');
  const slotEls = [
    document.querySelector('.slot[data-slot="0"]'),
    document.querySelector('.slot[data-slot="1"]'),
    document.querySelector('.slot[data-slot="2"]'),
  ];
  /** @type {HTMLCanvasElement[]} */
  const slotCanvases = [
    document.getElementById('slot0'),
    document.getElementById('slot1'),
    document.getElementById('slot2'),
  ];
  const slotMetaEls = [
    document.getElementById('slot0Meta'),
    document.getElementById('slot1Meta'),
    document.getElementById('slot2Meta'),
  ];

  // -------------------------
  // 配置
  // -------------------------
  const GRID_SIZE = 10;
  const COLORS = [
    '#4cc9f0', // cyan
    '#f72585', // magenta
    '#b5179e', // purple
    '#7209b7', // deep purple
    '#4361ee', // blue
    '#4895ef', // light blue
    '#3a0ca3', // indigo
    '#2ecc71', // green
    '#f9c74f', // yellow
    '#f8961e', // orange
  ];

  const SCORE_PER_CELL = 1;
  const SCORE_PER_LINE = 10;
  const COMBO_BONUS = 6; // 每级连消额外 +6*(linesCleared)

  const ANIM_PLACE_DUR = 160;
  const ANIM_CLEAR_DUR = 260;
  const PARTICLES_ENABLED = true;

  // -------------------------
  // 工具函数
  // -------------------------
  const now = () => performance.now();
  const clamp = (v, a, b) => Math.max(a, Math.min(b, v));
  const lerp = (a, b, t) => a + (b - a) * t;
  const easeOutBack = (t) => {
    // t:0..1
    const c1 = 1.70158;
    const c3 = c1 + 1;
    return 1 + c3 * Math.pow(t - 1, 3) + c1 * Math.pow(t - 1, 2);
  };
  const easeInOutQuad = (t) => (t < 0.5 ? 2 * t * t : 1 - Math.pow(-2 * t + 2, 2) / 2);
  const rand = (a, b) => a + Math.random() * (b - a);
  const keyXY = (x, y) => `${x},${y}`;

  function fitCanvasToCSSSize(c) {
    const dpr = Math.max(1, Math.floor(window.devicePixelRatio || 1));
    const rect = c.getBoundingClientRect();
    const w = Math.max(1, Math.floor(rect.width * dpr));
    const h = Math.max(1, Math.floor(rect.height * dpr));
    if (c.width !== w || c.height !== h) {
      c.width = w;
      c.height = h;
    }
    return { dpr, w, h, cssW: rect.width, cssH: rect.height };
  }

  // -------------------------
  // 方块定义（不可旋转）
  // -------------------------
  /** @type {{id:string,name:string,cells:{x:number,y:number}[]}[]} */
  const BLOCK_DEFS_RAW = [
    // 单格/直线
    { id: '1', name: '1', cells: [{ x: 0, y: 0 }] },
    { id: '2h', name: '2', cells: [{ x: 0, y: 0 }, { x: 1, y: 0 }] },
    { id: '3h', name: '3', cells: [{ x: 0, y: 0 }, { x: 1, y: 0 }, { x: 2, y: 0 }] },
    { id: '4h', name: '4', cells: [{ x: 0, y: 0 }, { x: 1, y: 0 }, { x: 2, y: 0 }, { x: 3, y: 0 }] },
    { id: '5h', name: '5', cells: [{ x: 0, y: 0 }, { x: 1, y: 0 }, { x: 2, y: 0 }, { x: 3, y: 0 }, { x: 4, y: 0 }] },

    { id: '2v', name: '2', cells: [{ x: 0, y: 0 }, { x: 0, y: 1 }] },
    { id: '3v', name: '3', cells: [{ x: 0, y: 0 }, { x: 0, y: 1 }, { x: 0, y: 2 }] },
    { id: '4v', name: '4', cells: [{ x: 0, y: 0 }, { x: 0, y: 1 }, { x: 0, y: 2 }, { x: 0, y: 3 }] },
    { id: '5v', name: '5', cells: [{ x: 0, y: 0 }, { x: 0, y: 1 }, { x: 0, y: 2 }, { x: 0, y: 3 }, { x: 0, y: 4 }] },

    // 方块
    { id: '2x2', name: '2x2', cells: [{ x: 0, y: 0 }, { x: 1, y: 0 }, { x: 0, y: 1 }, { x: 1, y: 1 }] },
    { id: '3x3', name: '3x3', cells: [
      { x: 0, y: 0 }, { x: 1, y: 0 }, { x: 2, y: 0 },
      { x: 0, y: 1 }, { x: 1, y: 1 }, { x: 2, y: 1 },
      { x: 0, y: 2 }, { x: 1, y: 2 }, { x: 2, y: 2 },
    ] },

    // L / 角
    { id: 'L3', name: 'L3', cells: [{ x: 0, y: 0 }, { x: 0, y: 1 }, { x: 1, y: 1 }] },
    { id: 'L4', name: 'L4', cells: [{ x: 0, y: 0 }, { x: 0, y: 1 }, { x: 0, y: 2 }, { x: 1, y: 2 }] },
    { id: 'L5', name: 'L5', cells: [{ x: 0, y: 0 }, { x: 0, y: 1 }, { x: 0, y: 2 }, { x: 1, y: 2 }, { x: 2, y: 2 }] },

    // T
    { id: 'T4', name: 'T4', cells: [{ x: 0, y: 0 }, { x: 1, y: 0 }, { x: 2, y: 0 }, { x: 1, y: 1 }] },
    { id: 'T5', name: 'T5', cells: [{ x: 0, y: 0 }, { x: 1, y: 0 }, { x: 2, y: 0 }, { x: 1, y: 1 }, { x: 1, y: 2 }] },

    // Z / S（不旋转，仅提供两个方向）
    { id: 'Z4', name: 'Z4', cells: [{ x: 0, y: 0 }, { x: 1, y: 0 }, { x: 1, y: 1 }, { x: 2, y: 1 }] },
    { id: 'S4', name: 'S4', cells: [{ x: 1, y: 0 }, { x: 2, y: 0 }, { x: 0, y: 1 }, { x: 1, y: 1 }] },

    // 3 角（像小“Γ”）
    { id: 'corner3', name: '角3', cells: [{ x: 0, y: 0 }, { x: 1, y: 0 }, { x: 0, y: 1 }] },
    // 5 “P”型
    { id: 'P5', name: 'P5', cells: [{ x: 0, y: 0 }, { x: 1, y: 0 }, { x: 0, y: 1 }, { x: 1, y: 1 }, { x: 0, y: 2 }] },
  ];

  function normalizeDef(raw) {
    let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
    for (const p of raw.cells) {
      minX = Math.min(minX, p.x);
      minY = Math.min(minY, p.y);
      maxX = Math.max(maxX, p.x);
      maxY = Math.max(maxY, p.y);
    }
    const cells = raw.cells.map(p => ({ x: p.x - minX, y: p.y - minY }));
    const w = maxX - minX + 1;
    const h = maxY - minY + 1;
    return {
      id: raw.id,
      name: raw.name,
      cells,
      size: { w, h },
      weight: clamp(8 - cells.length, 1, 8), // 小块更常见
    };
  }

  /** @type {{id:string,name:string,cells:{x:number,y:number}[],size:{w:number,h:number},weight:number}[]} */
  const BLOCK_DEFS = BLOCK_DEFS_RAW.map(normalizeDef);

  function pickRandomDef() {
    const total = BLOCK_DEFS.reduce((s, d) => s + d.weight, 0);
    let r = Math.random() * total;
    for (const d of BLOCK_DEFS) {
      r -= d.weight;
      if (r <= 0) return d;
    }
    return BLOCK_DEFS[0];
  }

  function newBlockInstance() {
    const def = pickRandomDef();
    const colorIndex = Math.floor(Math.random() * COLORS.length);
    return { def, colorIndex, used: false };
  }

  // -------------------------
  // 棋盘与游戏状态
  // -------------------------
  function newGrid() {
    // grid[y][x] = -1 表示空；>=0 表示颜色索引
    const g = Array.from({ length: GRID_SIZE }, () => Array.from({ length: GRID_SIZE }, () => -1));
    return g;
  }

  const state = {
    grid: newGrid(),
    hand: [newBlockInstance(), newBlockInstance(), newBlockInstance()],
    score: 0,
    combo: 0,
    turn: 1,
    selectedIdx: -1,
    gameOver: false,

    // hover 信息（用于幽灵预览）
    hoverCell: null, // {x,y}
    lastPointerPos: null, // {x,y} canvas-space(px)

    // 动画
    placePops: new Map(), // key "x,y" -> {t0,dur}
    clearAnims: [], // {t0,dur,cells:[{x,y}]}
    particles: [],
  };

  // -------------------------
  // 逻辑：碰撞、放置、消除、结束判定
  // -------------------------
  function canPlaceAt(def, baseX, baseY) {
    for (const p of def.cells) {
      const x = baseX + p.x;
      const y = baseY + p.y;
      if (x < 0 || x >= GRID_SIZE || y < 0 || y >= GRID_SIZE) return false;
      if (state.grid[y][x] !== -1) return false;
    }
    return true;
  }

  function anyPlacementExistsFor(def) {
    // 朴素扫全图：10x10 很小够用
    for (let y = 0; y < GRID_SIZE; y++) {
      for (let x = 0; x < GRID_SIZE; x++) {
        if (canPlaceAt(def, x, y)) return true;
      }
    }
    return false;
  }

  function anyHandMoveExists() {
    for (const b of state.hand) {
      if (!b.used && anyPlacementExistsFor(b.def)) return true;
    }
    return false;
  }

  function placeSelectedAt(baseX, baseY) {
    const idx = state.selectedIdx;
    if (idx < 0) return false;
    const blk = state.hand[idx];
    if (!blk || blk.used) return false;
    if (!canPlaceAt(blk.def, baseX, baseY)) return false;

    const t0 = now();
    let placedCells = 0;
    for (const p of blk.def.cells) {
      const x = baseX + p.x;
      const y = baseY + p.y;
      state.grid[y][x] = blk.colorIndex;
      placedCells++;
      state.placePops.set(keyXY(x, y), { t0, dur: ANIM_PLACE_DUR });
      if (PARTICLES_ENABLED) spawnParticlesForCell(x, y, blk.colorIndex, t0);
    }

    // 计分：落子
    state.score += placedCells * SCORE_PER_CELL;
    blk.used = true;
    state.selectedIdx = -1;
    state.hoverCell = null;

    // 消除检测与执行
    const cleared = clearFullLines();
    if (cleared.lines > 0) {
      state.combo += 1;
      const comboBonus = state.combo * COMBO_BONUS * cleared.lines;
      state.score += cleared.lines * SCORE_PER_LINE + comboBonus;
    } else {
      state.combo = 0;
    }

    // 如果 3 个都用完：发新一回合
    if (state.hand.every(b => b.used)) {
      state.turn += 1;
      state.hand = [newBlockInstance(), newBlockInstance(), newBlockInstance()];
    }

    syncUI();

    // 结束判定：当前手牌（未用的）都无法放置
    if (!anyHandMoveExists()) {
      endGame('没有可放置的方块了（当前 3 个都无法放进棋盘）。');
    }

    return true;
  }

  function clearFullLines() {
    const fullRows = [];
    const fullCols = [];

    for (let y = 0; y < GRID_SIZE; y++) {
      let ok = true;
      for (let x = 0; x < GRID_SIZE; x++) {
        if (state.grid[y][x] === -1) { ok = false; break; }
      }
      if (ok) fullRows.push(y);
    }

    for (let x = 0; x < GRID_SIZE; x++) {
      let ok = true;
      for (let y = 0; y < GRID_SIZE; y++) {
        if (state.grid[y][x] === -1) { ok = false; break; }
      }
      if (ok) fullCols.push(x);
    }

    if (fullRows.length === 0 && fullCols.length === 0) return { lines: 0 };

    // 先记录待清除的 cells（用于动画），避免与交叉重复
    const cellsSet = new Set();
    /** @type {{x:number,y:number}[]} */
    const cells = [];
    for (const y of fullRows) {
      for (let x = 0; x < GRID_SIZE; x++) {
        const k = keyXY(x, y);
        if (!cellsSet.has(k)) { cellsSet.add(k); cells.push({ x, y }); }
      }
    }
    for (const x of fullCols) {
      for (let y = 0; y < GRID_SIZE; y++) {
        const k = keyXY(x, y);
        if (!cellsSet.has(k)) { cellsSet.add(k); cells.push({ x, y }); }
      }
    }

    // 逻辑立即清空（避免动画期间“占格”导致手感卡顿），但保存颜色用于淡出动画绘制
    /** @type {{x:number,y:number,colorIndex:number}[]} */
    const animCells = [];
    for (const p of cells) {
      const colorIndex = state.grid[p.y][p.x];
      if (colorIndex !== -1) {
        animCells.push({ x: p.x, y: p.y, colorIndex });
        state.grid[p.y][p.x] = -1;
      }
    }

    const t0 = now();
    state.clearAnims.push({ t0, dur: ANIM_CLEAR_DUR, cells: animCells });

    return { lines: fullRows.length + fullCols.length };
  }

  function endGame(reason) {
    state.gameOver = true;
    gameOverText.textContent = reason;
    overlay.classList.add('show');
    overlay.setAttribute('aria-hidden', 'false');
    syncUI();
  }

  function resetGame() {
    state.grid = newGrid();
    state.hand = [newBlockInstance(), newBlockInstance(), newBlockInstance()];
    state.score = 0;
    state.combo = 0;
    state.turn = 1;
    state.selectedIdx = -1;
    state.gameOver = false;
    state.hoverCell = null;
    state.lastPointerPos = null;
    state.placePops.clear();
    state.clearAnims.length = 0;
    state.particles.length = 0;
    overlay.classList.remove('show');
    overlay.setAttribute('aria-hidden', 'true');
    syncUI();
  }

  // -------------------------
  // 粒子（可选）
  // -------------------------
  function spawnParticlesForCell(gridX, gridY, colorIndex, t0) {
    // 粒子坐标在渲染时会转换；这里存格子中心的“格子坐标”
    const count = 5;
    for (let i = 0; i < count; i++) {
      state.particles.push({
        gx: gridX + 0.5,
        gy: gridY + 0.5,
        vx: rand(-1.8, 1.8),
        vy: rand(-2.2, -0.6),
        t0,
        dur: rand(220, 360),
        colorIndex,
      });
    }
  }

  // -------------------------
  // 渲染
  // -------------------------
  const ctx = canvas.getContext('2d');
  const slotCtx = slotCanvases.map(c => c.getContext('2d'));

  function computeBoardMetrics() {
    const { dpr, w, h } = fitCanvasToCSSSize(canvas);
    const pad = 18 * dpr;
    const size = Math.min(w, h) - pad * 2;
    const cell = size / GRID_SIZE;
    const originX = (w - cell * GRID_SIZE) / 2;
    const originY = (h - cell * GRID_SIZE) / 2;
    return { dpr, w, h, originX, originY, cell };
  }

  function gridFromCanvasPos(px, py, metrics) {
    const x = Math.floor((px - metrics.originX) / metrics.cell);
    const y = Math.floor((py - metrics.originY) / metrics.cell);
    if (x < 0 || x >= GRID_SIZE || y < 0 || y >= GRID_SIZE) return null;
    return { x, y };
  }

  function drawRoundedRect(c, x, y, w, h, r) {
    const rr = Math.min(r, w / 2, h / 2);
    c.beginPath();
    c.moveTo(x + rr, y);
    c.arcTo(x + w, y, x + w, y + h, rr);
    c.arcTo(x + w, y + h, x, y + h, rr);
    c.arcTo(x, y + h, x, y, rr);
    c.arcTo(x, y, x + w, y, rr);
    c.closePath();
  }

  function drawClearAnims(c, m, t) {
    for (const a of state.clearAnims) {
      const dt = t - a.t0;
      if (dt < 0 || dt > a.dur) continue;
      const tt = clamp(dt / a.dur, 0, 1);
      const alpha = 1 - easeInOutQuad(tt);
      const flash = Math.sin(tt * Math.PI * 6) * 0.35;

      c.save();
      for (const p of a.cells) {
        const x = m.originX + p.x * m.cell;
        const y = m.originY + p.y * m.cell;
        const pad = 0.10 * m.cell;
        const w = m.cell - pad * 2;
        const r = 0.22 * w;

        drawRoundedRect(c, x + pad, y + pad, w, w, r);
        c.globalAlpha = alpha * 0.85;
        c.fillStyle = COLORS[p.colorIndex % COLORS.length];
        c.fill();

        c.globalAlpha = alpha;
        c.lineWidth = 2 * m.dpr;
        c.strokeStyle = `rgba(255,255,255,${0.18 + flash})`;
        c.stroke();
      }
      c.restore();
    }
  }

  function render() {
    const t = now();
    const m = computeBoardMetrics();
    ctx.clearRect(0, 0, m.w, m.h);

    // 背景柔光
    ctx.save();
    ctx.globalAlpha = 0.8;
    const g = ctx.createRadialGradient(m.w * 0.4, m.h * 0.25, 10, m.w * 0.4, m.h * 0.25, Math.max(m.w, m.h));
    g.addColorStop(0, 'rgba(76,201,240,.14)');
    g.addColorStop(0.55, 'rgba(114,9,183,.08)');
    g.addColorStop(1, 'rgba(0,0,0,0)');
    ctx.fillStyle = g;
    ctx.fillRect(0, 0, m.w, m.h);
    ctx.restore();

    // 棋盘底板
    const boardX = m.originX - 6 * m.dpr;
    const boardY = m.originY - 6 * m.dpr;
    const boardS = m.cell * GRID_SIZE + 12 * m.dpr;
    ctx.save();
    drawRoundedRect(ctx, boardX, boardY, boardS, boardS, 16 * m.dpr);
    ctx.fillStyle = 'rgba(0,0,0,.22)';
    ctx.fill();
    ctx.strokeStyle = 'rgba(255,255,255,.08)';
    ctx.lineWidth = 1 * m.dpr;
    ctx.stroke();
    ctx.restore();

    // 网格线
    ctx.save();
    ctx.translate(m.originX, m.originY);
    for (let i = 0; i <= GRID_SIZE; i++) {
      const strong = (i % 5 === 0);
      ctx.strokeStyle = strong ? 'rgba(255,255,255,.18)' : 'rgba(255,255,255,.10)';
      ctx.lineWidth = (strong ? 1.5 : 1) * m.dpr;
      // vertical
      ctx.beginPath();
      ctx.moveTo(i * m.cell, 0);
      ctx.lineTo(i * m.cell, GRID_SIZE * m.cell);
      ctx.stroke();
      // horizontal
      ctx.beginPath();
      ctx.moveTo(0, i * m.cell);
      ctx.lineTo(GRID_SIZE * m.cell, i * m.cell);
      ctx.stroke();
    }
    ctx.restore();

    // 幽灵预览（如果选中方块且 hover 在棋盘内）
    if (!state.gameOver && state.selectedIdx >= 0 && state.hoverCell) {
      const blk = state.hand[state.selectedIdx];
      if (blk && !blk.used) {
        const ok = canPlaceAt(blk.def, state.hoverCell.x, state.hoverCell.y);
        drawGhost(ctx, m, blk, state.hoverCell.x, state.hoverCell.y, ok);
      }
    }

    // 画已放置方块
    for (let y = 0; y < GRID_SIZE; y++) {
      for (let x = 0; x < GRID_SIZE; x++) {
        const cidx = state.grid[y][x];
        if (cidx === -1) continue;
        const pop = state.placePops.get(keyXY(x, y));
        drawCell(ctx, m, x, y, cidx, t, pop);
      }
    }

    // 消除动画层（逻辑已立即清空，但视觉淡出保留）
    drawClearAnims(ctx, m, t);

    // 粒子
    if (PARTICLES_ENABLED) {
      drawParticles(ctx, m, t);
    }

    // 清理过期动画
    cleanupAnims(t);

    requestAnimationFrame(render);
  }

  function drawCell(c, m, gx, gy, colorIndex, t, pop) {
    const x = m.originX + gx * m.cell;
    const y = m.originY + gy * m.cell;
    const pad = 0.10 * m.cell;
    const w = m.cell - pad * 2;
    const h = w;
    const r = 0.22 * w;

    // pop 动画（缩放弹入）
    let scale = 1;
    if (pop) {
      const tt = clamp((t - pop.t0) / pop.dur, 0, 1);
      scale = lerp(0.2, 1.0, easeOutBack(tt));
    }

    // 常规已占用格子不参与 clear（clear 动画由 drawClearAnims 单独绘制）
    let alpha = 1;
    let flash = 0;

    const cx = x + pad + w / 2;
    const cy = y + pad + h / 2;
    c.save();
    c.translate(cx, cy);
    c.scale(scale, scale);
    c.translate(-cx, -cy);

    // 阴影
    c.globalAlpha = alpha;
    c.shadowColor = 'rgba(0,0,0,.35)';
    c.shadowBlur = 10 * m.dpr;
    c.shadowOffsetY = 4 * m.dpr;

    const base = COLORS[colorIndex % COLORS.length];
    drawRoundedRect(c, x + pad, y + pad, w, h, r);
    c.fillStyle = base;
    c.fill();

    // 内高光
    c.shadowBlur = 0;
    c.globalAlpha = alpha;
    c.lineWidth = 1.5 * m.dpr;
    c.strokeStyle = `rgba(255,255,255,${0.22 + flash})`;
    c.stroke();

    // 顶部微光
    c.globalAlpha = alpha * 0.22;
    const gg = c.createLinearGradient(x, y, x, y + m.cell);
    gg.addColorStop(0, 'rgba(255,255,255,1)');
    gg.addColorStop(1, 'rgba(255,255,255,0)');
    c.fillStyle = gg;
    drawRoundedRect(c, x + pad, y + pad, w, h * 0.7, r);
    c.fill();

    c.restore();
  }

  function drawGhost(c, m, blk, baseX, baseY, ok) {
    const t = now();
    const pulse = 0.35 + 0.15 * Math.sin(t / 110);
    const border = ok ? `rgba(46,204,113,${0.65 + pulse * 0.25})` : `rgba(255,77,79,${0.65 + pulse * 0.25})`;
    const fill = ok ? `rgba(46,204,113,${0.16 + pulse * 0.08})` : `rgba(255,77,79,${0.16 + pulse * 0.08})`;

    c.save();
    for (const p of blk.def.cells) {
      const gx = baseX + p.x;
      const gy = baseY + p.y;
      if (gx < 0 || gx >= GRID_SIZE || gy < 0 || gy >= GRID_SIZE) continue;
      const x = m.originX + gx * m.cell;
      const y = m.originY + gy * m.cell;
      const pad = 0.10 * m.cell;
      const w = m.cell - pad * 2;
      const r = 0.22 * w;
      drawRoundedRect(c, x + pad, y + pad, w, w, r);
      c.fillStyle = fill;
      c.fill();
      c.lineWidth = 2 * m.dpr;
      c.strokeStyle = border;
      c.stroke();
    }
    c.restore();
  }

  function drawParticles(c, m, t) {
    // 粒子坐标：gx/gy 为格子坐标（中心），需要转换成像素
    const alive = [];
    for (const p of state.particles) {
      const tt = (t - p.t0) / p.dur;
      if (tt < 0 || tt >= 1) continue;
      const e = easeInOutQuad(tt);
      const gx = p.gx + p.vx * e * 0.12;
      const gy = p.gy + p.vy * e * 0.18 + 0.55 * e * e; // 重力
      const x = m.originX + gx * m.cell;
      const y = m.originY + gy * m.cell;
      const r = (1.8 + 2.2 * (1 - tt)) * m.dpr;
      c.save();
      c.globalAlpha = (1 - tt) * 0.65;
      c.fillStyle = COLORS[p.colorIndex % COLORS.length];
      c.beginPath();
      c.arc(x, y, r, 0, Math.PI * 2);
      c.fill();
      c.restore();
      alive.push(p);
    }
    state.particles = alive;
  }

  function cleanupAnims(t) {
    // pop
    for (const [k, a] of state.placePops.entries()) {
      if (t - a.t0 > a.dur + 30) state.placePops.delete(k);
    }
    // clear
    state.clearAnims = state.clearAnims.filter(a => (t - a.t0) <= a.dur + 60);
  }

  // -------------------------
  // 托盘（3 个方块）渲染
  // -------------------------
  function renderSlot(i) {
    const c = slotCanvases[i];
    const cx = slotCtx[i];
    const { dpr, w, h } = fitCanvasToCSSSize(c);
    cx.clearRect(0, 0, w, h);

    const blk = state.hand[i];
    if (!blk) return;

    // used: 画淡
    const alpha = blk.used ? 0.25 : 1;

    // 计算缩放：把块居中画在 slot canvas
    const cells = blk.def.cells;
    const bw = blk.def.size.w;
    const bh = blk.def.size.h;
    const pad = 10 * dpr;
    const cell = Math.min((w - pad * 2) / (bw + 0.4), (h - pad * 2) / (bh + 0.4));
    const originX = (w - bw * cell) / 2;
    const originY = (h - bh * cell) / 2;

    // 底色轻微衬托
    cx.save();
    cx.globalAlpha = 0.7;
    drawRoundedRect(cx, 6 * dpr, 6 * dpr, w - 12 * dpr, h - 12 * dpr, 12 * dpr);
    cx.fillStyle = 'rgba(255,255,255,.03)';
    cx.fill();
    cx.restore();

    cx.save();
    cx.globalAlpha = alpha;
    for (const p of cells) {
      const x = originX + p.x * cell;
      const y = originY + p.y * cell;
      const inner = 0.10 * cell;
      const rr = 0.22 * (cell - inner * 2);
      drawRoundedRect(cx, x + inner, y + inner, cell - inner * 2, cell - inner * 2, rr);
      cx.fillStyle = COLORS[blk.colorIndex % COLORS.length];
      cx.fill();
      cx.lineWidth = 1.25 * dpr;
      cx.strokeStyle = 'rgba(255,255,255,.20)';
      cx.stroke();
    }
    cx.restore();

    // meta
    slotMetaEls[i].textContent = blk.used ? '已用' : `${blk.def.name}/${blk.def.cells.length}`;
  }

  function renderTray() {
    for (let i = 0; i < 3; i++) renderSlot(i);
    // slot 样式
    for (let i = 0; i < 3; i++) {
      const blk = state.hand[i];
      const el = slotEls[i];
      el.classList.toggle('used', !!blk?.used);
      el.classList.toggle('selected', state.selectedIdx === i && !blk?.used && !state.gameOver);
    }
  }

  // -------------------------
  // UI 与输入
  // -------------------------
  function syncUI() {
    scoreEl.textContent = String(state.score);
    comboEl.textContent = String(state.combo);
    turnEl.textContent = String(state.turn);
    renderTray();
  }

  function setSelectedSlot(i) {
    if (state.gameOver) return;
    const blk = state.hand[i];
    if (!blk || blk.used) return;
    state.selectedIdx = (state.selectedIdx === i) ? -1 : i;
    syncUI();
  }

  function canvasClientToCanvasPx(ev) {
    const rect = canvas.getBoundingClientRect();
    const dpr = Math.max(1, Math.floor(window.devicePixelRatio || 1));
    const clientX = ev.clientX;
    const clientY = ev.clientY;
    const x = (clientX - rect.left) * dpr;
    const y = (clientY - rect.top) * dpr;
    return { x, y };
  }

  function updateHoverFromPointer(px, py) {
    const m = computeBoardMetrics();
    const g = gridFromCanvasPos(px, py, m);
    state.hoverCell = g;
  }

  // 统一使用 Pointer Events（兼容鼠标/触摸/笔），避免移动端 click 的二次触发
  canvas.addEventListener('pointerdown', (ev) => {
    if (state.gameOver) return;
    ev.preventDefault();
    canvas.setPointerCapture?.(ev.pointerId);
    const p = canvasClientToCanvasPx(ev);
    state.lastPointerPos = p;
    updateHoverFromPointer(p.x, p.y);
  }, { passive: false });

  canvas.addEventListener('pointermove', (ev) => {
    if (state.gameOver) return;
    ev.preventDefault();
    const p = canvasClientToCanvasPx(ev);
    state.lastPointerPos = p;
    updateHoverFromPointer(p.x, p.y);
  }, { passive: false });

  canvas.addEventListener('pointerleave', () => {
    state.hoverCell = null;
  });

  canvas.addEventListener('pointerup', (ev) => {
    if (state.gameOver) return;
    ev.preventDefault();
    const p = canvasClientToCanvasPx(ev);
    const m = computeBoardMetrics();
    const g = gridFromCanvasPos(p.x, p.y, m);
    if (!g) return;
    placeSelectedAt(g.x, g.y);
  }, { passive: false });

  // 托盘选择
  trayEl.addEventListener('click', (ev) => {
    const target = ev.target.closest?.('.slot');
    if (!target) return;
    const i = Number(target.getAttribute('data-slot'));
    if (Number.isFinite(i)) setSelectedSlot(i);
  });

  // 键盘：Esc 取消选择
  window.addEventListener('keydown', (ev) => {
    if (ev.key === 'Escape') {
      state.selectedIdx = -1;
      state.hoverCell = null;
      syncUI();
    }
  });

  // 按钮
  newGameBtn.addEventListener('click', resetGame);
  restartBtn.addEventListener('click', resetGame);

  // resize：重绘 slot（因为 DPR/CSS 大小变化）
  window.addEventListener('resize', () => {
    renderTray();
  });

  // -------------------------
  // 启动
  // -------------------------
  function boot() {
    // 防止触摸滚动/双击缩放影响操作
    canvas.style.touchAction = 'none';
    // slot canvas 初始适配
    for (const c of slotCanvases) fitCanvasToCSSSize(c);
    fitCanvasToCSSSize(canvas);
    syncUI();

    // 初始若 3 块都无法放置，则直接结束（极低概率，但逻辑完整）
    if (!anyHandMoveExists()) {
      endGame('开局就无处可放（极少发生）。点击“重新开始”。');
    }

    requestAnimationFrame(render);
  }

  boot();
})();


