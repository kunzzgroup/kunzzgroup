(() => {
  "use strict";

  /**
   * 简易 Angry-Birds 风格 Demo（无依赖）
   * - 弹弓拖拽瞄准（Pointer Events）
   * - 重力抛体（固定步长物理）
   * - 碰撞：弹丸(圆) vs 方块(AABB)
   * - 方块HP、撞击伤害、销毁
   */

  // ---------------------------
  // DOM / Canvas
  // ---------------------------
  const canvas = /** @type {HTMLCanvasElement} */ (document.getElementById("game"));
  const ctx = canvas.getContext("2d", { alpha: false });
  if (!ctx) throw new Error("Canvas 2D context not available");

  const hudState = /** @type {HTMLElement} */ (document.getElementById("hudState"));
  const hudPower = /** @type {HTMLElement} */ (document.getElementById("hudPower"));
  const hudSpeed = /** @type {HTMLElement} */ (document.getElementById("hudSpeed"));
  const hudBlocks = /** @type {HTMLElement} */ (document.getElementById("hudBlocks"));
  const hudDestroyed = /** @type {HTMLElement} */ (document.getElementById("hudDestroyed"));
  const hudShots = /** @type {HTMLElement} */ (document.getElementById("hudShots"));

  const btnResetShot = /** @type {HTMLButtonElement} */ (document.getElementById("btnResetShot"));
  const btnResetLevel = /** @type {HTMLButtonElement} */ (document.getElementById("btnResetLevel"));
  const btnToggleDebug = /** @type {HTMLButtonElement} */ (document.getElementById("btnToggleDebug"));

  // ---------------------------
  // Utils
  // ---------------------------
  const clamp = (v, a, b) => Math.max(a, Math.min(b, v));
  const lerp = (a, b, t) => a + (b - a) * t;

  const vec2 = (x = 0, y = 0) => ({ x, y });
  const vAdd = (a, b) => ({ x: a.x + b.x, y: a.y + b.y });
  const vSub = (a, b) => ({ x: a.x - b.x, y: a.y - b.y });
  const vMul = (a, s) => ({ x: a.x * s, y: a.y * s });
  const vDot = (a, b) => a.x * b.x + a.y * b.y;
  const vLen = (a) => Math.hypot(a.x, a.y);
  const vNorm = (a) => {
    const l = vLen(a);
    if (l < 1e-8) return { x: 0, y: 0 };
    return { x: a.x / l, y: a.y / l };
  };

  const cssVar = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim();

  // ---------------------------
  // Render scaling
  // ---------------------------
  let dpr = Math.max(1, Math.min(2.5, window.devicePixelRatio || 1));
  let viewW = 0;
  let viewH = 0;

  function resizeCanvas() {
    dpr = Math.max(1, Math.min(2.5, window.devicePixelRatio || 1));
    const rect = canvas.getBoundingClientRect();
    viewW = Math.max(2, Math.floor(rect.width));
    viewH = Math.max(2, Math.floor(rect.height));
    canvas.width = Math.floor(viewW * dpr);
    canvas.height = Math.floor(viewH * dpr);
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
  }

  resizeCanvas();

  // ---------------------------
  // World / Game state
  // ---------------------------
  const world = {
    gravity: 1700, // px/s^2
    airDrag: 0.20, // 0..1-ish, per second
    restitution: 0.35,
    groundFriction: 0.70,
    wallFriction: 0.85,
    fixedDt: 1 / 120,
  };

  const ui = {
    debug: false,
    destroyed: 0,
    shots: 0,
  };

  const slingshot = {
    anchor: vec2(150, 0), // y will be set by layout()
    maxStretch: 120,
    launchPower: 8.2, // velocity = stretchVec * launchPower
    pocketOffset: vec2(18, 10),
  };

  const ground = {
    y: 0, // set by layout()
    height: 56,
  };

  /** @type {{pos:{x:number,y:number}, vel:{x:number,y:number}, r:number, state:"idle"|"dragging"|"flying"|"rest", bounces:number}} */
  const ball = {
    pos: vec2(0, 0),
    vel: vec2(0, 0),
    r: 12,
    state: "idle",
    bounces: 0,
  };

  /** @type {{x:number,y:number,w:number,h:number,hp:number,maxHp:number, lastHitT:number}[]} */
  let blocks = [];

  /** @type {{x:number,y:number, t:number, text:string, color:string}[]} */
  let floaters = [];

  let simT = 0;

  function layoutWorld() {
    ground.y = Math.max(260, viewH - ground.height);
    slingshot.anchor.y = ground.y - 110;

    // 只在 idle 时把球放回“口袋”；dragging 由指针控制位置
    if (ball.state === "idle") {
      const p = getPocketPos();
      ball.pos.x = p.x;
      ball.pos.y = p.y;
    }
  }

  function getPocketPos() {
    return vAdd(slingshot.anchor, slingshot.pocketOffset);
  }

  function resetShot() {
    const p = getPocketPos();
    ball.pos = vec2(p.x, p.y);
    ball.vel = vec2(0, 0);
    ball.state = "idle";
    ball.bounces = 0;
  }

  function resetLevel() {
    ui.destroyed = 0;
    ui.shots = 0;
    floaters = [];

    // 简单方块堆（右侧）
    const baseX = Math.max(520, Math.floor(viewW * 0.62));
    const baseY = ground.y - 28;
    const bw = 44;
    const bh = 28;

    /** @type {typeof blocks} */
    const b = [];
    const cols = 5;
    const rows = 5;
    for (let r = 0; r < rows; r++) {
      for (let c = 0; c < cols; c++) {
        const x = baseX + c * (bw + 8);
        const y = baseY - (r + 1) * (bh + 8);
        const hp = 3 + Math.floor(r * 0.7) + (c % 2);
        b.push({ x, y, w: bw, h: bh, hp, maxHp: hp, lastHitT: -999 });
      }
    }

    // 加几个“硬一点”的大块
    b.push({ x: baseX + 80, y: baseY - 6 * (bh + 10), w: 96, h: 32, hp: 12, maxHp: 12, lastHitT: -999 });
    b.push({ x: baseX + 10, y: baseY - 7 * (bh + 10), w: 70, h: 26, hp: 8, maxHp: 8, lastHitT: -999 });

    blocks = b;
    resetShot();
  }

  // 初次布局与关卡
  layoutWorld();
  resetLevel();

  // ---------------------------
  // Input (Pointer Events)
  // ---------------------------
  const pointer = {
    active: false,
    id: /** @type {number|null} */ (null),
    pos: vec2(0, 0),
  };

  function canvasPointFromEvent(e) {
    const rect = canvas.getBoundingClientRect();
    return vec2(e.clientX - rect.left, e.clientY - rect.top);
  }

  function canGrabBall(p) {
    if (!(ball.state === "idle" || ball.state === "rest")) return false;
    const d = vLen(vSub(p, ball.pos));
    return d <= Math.max(24, ball.r * 2.1);
  }

  function setBallDragging(p) {
    ball.state = "dragging";
    ball.vel.x = 0;
    ball.vel.y = 0;

    const raw = vSub(p, slingshot.anchor);
    const L = vLen(raw);
    const clamped = L > slingshot.maxStretch ? vMul(raw, slingshot.maxStretch / L) : raw;
    const pos = vAdd(slingshot.anchor, clamped);
    ball.pos.x = pos.x;
    ball.pos.y = pos.y;
  }

  function releaseBall() {
    if (ball.state !== "dragging") return;
    const stretchVec = vSub(slingshot.anchor, ball.pos); // 反向（越拉越大）
    const stretch = vLen(stretchVec);
    if (stretch < 6) {
      resetShot();
      return;
    }
    const v0 = vMul(stretchVec, slingshot.launchPower);
    ball.vel.x = v0.x;
    ball.vel.y = v0.y;
    ball.state = "flying";
    ball.bounces = 0;
    ui.shots += 1;
  }

  canvas.addEventListener(
    "pointerdown",
    (e) => {
      const p = canvasPointFromEvent(e);
      pointer.pos = p;
      if (!canGrabBall(p)) return;
      pointer.active = true;
      pointer.id = e.pointerId;
      canvas.setPointerCapture(e.pointerId);
      setBallDragging(p);
    },
    { passive: true }
  );

  canvas.addEventListener(
    "pointermove",
    (e) => {
      if (!pointer.active || pointer.id !== e.pointerId) return;
      const p = canvasPointFromEvent(e);
      pointer.pos = p;
      setBallDragging(p);
    },
    { passive: true }
  );

  function pointerUpOrCancel(e) {
    if (!pointer.active || pointer.id !== e.pointerId) return;
    pointer.active = false;
    pointer.id = null;
    releaseBall();
  }

  canvas.addEventListener("pointerup", pointerUpOrCancel, { passive: true });
  canvas.addEventListener("pointercancel", pointerUpOrCancel, { passive: true });

  // Buttons
  btnResetShot.addEventListener("click", () => resetShot());
  btnResetLevel.addEventListener("click", () => resetLevel());
  btnToggleDebug.addEventListener("click", () => {
    ui.debug = !ui.debug;
    btnToggleDebug.setAttribute("aria-pressed", ui.debug ? "true" : "false");
  });

  // ---------------------------
  // Collision: Circle vs AABB
  // ---------------------------
  function circleAabbCollision(circlePos, r, rect) {
    const cx = circlePos.x;
    const cy = circlePos.y;
    const rx = rect.x;
    const ry = rect.y;
    const rw = rect.w;
    const rh = rect.h;

    const closestX = clamp(cx, rx, rx + rw);
    const closestY = clamp(cy, ry, ry + rh);
    const dx = cx - closestX;
    const dy = cy - closestY;
    const dist2 = dx * dx + dy * dy;
    if (dist2 > r * r) return null;

    const dist = Math.sqrt(Math.max(1e-10, dist2));
    const n = dist > 1e-6 ? { x: dx / dist, y: dy / dist } : { x: 0, y: -1 };
    const penetration = r - dist;
    return { n, penetration, closest: { x: closestX, y: closestY } };
  }

  function reflectVelocity(v, n, restitution) {
    const vn = vDot(v, n);
    if (vn >= 0) return v; // 正在远离
    // v' = v - (1+e)*(v·n)*n
    return vSub(v, vMul(n, (1 + restitution) * vn));
  }

  function applyTangentialFriction(v, n, friction) {
    // 从 v 中去掉法向分量，得到切向分量，并衰减
    const vn = vDot(v, n);
    const vN = vMul(n, vn);
    const vT = vSub(v, vN);
    return vAdd(vN, vMul(vT, friction));
  }

  function damageFromImpact(normalSpeed) {
    // normalSpeed: |v·n|
    // 经验值：速度 200 左右造成 1 点；速度越大伤害上升（封顶）
    const dmg = Math.ceil(normalSpeed / 220);
    return clamp(dmg, 1, 8);
  }

  // ---------------------------
  // Physics step
  // ---------------------------
  function step(dt) {
    simT += dt;

    // Floaters
    for (const f of floaters) f.t += dt;
    floaters = floaters.filter((f) => f.t < 0.85);

    if (!(ball.state === "flying" || ball.state === "rest")) return;

    // Integrate (semi-implicit Euler)
    ball.vel.y += world.gravity * dt;
    const drag = Math.exp(-world.airDrag * dt);
    ball.vel.x *= drag;
    ball.vel.y *= drag;

    ball.pos.x += ball.vel.x * dt;
    ball.pos.y += ball.vel.y * dt;

    // World bounds
    const left = 10;
    const right = viewW - 10;

    if (ball.pos.x - ball.r < left) {
      ball.pos.x = left + ball.r;
      ball.vel.x = -ball.vel.x * world.restitution;
      ball.vel.y *= world.wallFriction;
      ball.bounces += 1;
    } else if (ball.pos.x + ball.r > right) {
      ball.pos.x = right - ball.r;
      ball.vel.x = -ball.vel.x * world.restitution;
      ball.vel.y *= world.wallFriction;
      ball.bounces += 1;
    }

    // Ground
    if (ball.pos.y + ball.r > ground.y) {
      ball.pos.y = ground.y - ball.r;
      if (ball.vel.y > 0) {
        ball.vel.y = -ball.vel.y * world.restitution;
        ball.vel.x *= world.groundFriction;
        ball.bounces += 1;
      }
    }

    // Blocks
    for (let i = 0; i < blocks.length; i++) {
      const blk = blocks[i];
      const hit = circleAabbCollision(ball.pos, ball.r, blk);
      if (!hit) continue;

      // positional correction
      ball.pos.x += hit.n.x * hit.penetration;
      ball.pos.y += hit.n.y * hit.penetration;

      // bounce + friction
      const vBefore = vec2(ball.vel.x, ball.vel.y);
      const vRef = reflectVelocity(vBefore, hit.n, world.restitution);
      const vAfter = applyTangentialFriction(vRef, hit.n, 0.88);
      ball.vel.x = vAfter.x;
      ball.vel.y = vAfter.y;
      ball.bounces += 1;

      // damage throttling to avoid "multi-hit spam" in one spot
      if (simT - blk.lastHitT > 0.06) {
        blk.lastHitT = simT;
        const normalSpeed = Math.abs(vDot(vBefore, hit.n));
        const dmg = damageFromImpact(normalSpeed);
        blk.hp -= dmg;
        floaters.push({
          x: hit.closest.x,
          y: hit.closest.y - 6,
          t: 0,
          text: `-${dmg}`,
          color: "rgba(255,120,200,0.95)",
        });
        if (blk.hp <= 0) {
          ui.destroyed += 1;
          floaters.push({
            x: blk.x + blk.w / 2,
            y: blk.y + blk.h / 2,
            t: 0,
            text: "碎裂!",
            color: "rgba(124,240,199,0.95)",
          });
          blocks.splice(i, 1);
          i -= 1;
        }
      }
    }

    // Sleep / rest condition
    const speed = vLen(ball.vel);
    if (ball.pos.y + ball.r >= ground.y - 0.5 && speed < 45) {
      ball.vel.x *= 0.92;
      ball.vel.y *= 0.92;
      if (vLen(ball.vel) < 18) {
        ball.vel.x = 0;
        ball.vel.y = 0;
        ball.state = "rest";
      }
    } else {
      ball.state = "flying";
    }
  }

  // ---------------------------
  // Trajectory preview
  // ---------------------------
  function getPredictedVelocity() {
    const stretchVec = vSub(slingshot.anchor, ball.pos);
    return vMul(stretchVec, slingshot.launchPower);
  }

  function drawTrajectoryPreview() {
    if (ball.state !== "dragging") return;
    const v0 = getPredictedVelocity();
    const p0 = vec2(ball.pos.x, ball.pos.y);

    const pts = [];
    let p = p0;
    let v = v0;
    const steps = 34;
    const dt = 1 / 30;

    for (let i = 0; i < steps; i++) {
      v = vec2(v.x, v.y + world.gravity * dt);
      const drag = Math.exp(-world.airDrag * dt);
      v.x *= drag;
      v.y *= drag;
      p = vec2(p.x + v.x * dt, p.y + v.y * dt);
      if (p.y > ground.y - 2) break;
      pts.push(p);
    }

    ctx.save();
    ctx.globalAlpha = 0.9;
    ctx.fillStyle = "rgba(122,168,255,0.80)";
    for (let i = 0; i < pts.length; i++) {
      const t = i / Math.max(1, pts.length - 1);
      const r = lerp(3.5, 1.6, t);
      ctx.beginPath();
      ctx.arc(pts[i].x, pts[i].y, r, 0, Math.PI * 2);
      ctx.fill();
    }
    ctx.restore();
  }

  // ---------------------------
  // Draw
  // ---------------------------
  function draw() {
    layoutWorld();

    // background
    ctx.fillStyle = "#071022";
    ctx.fillRect(0, 0, viewW, viewH);

    // subtle grid
    ctx.save();
    ctx.globalAlpha = 0.14;
    ctx.strokeStyle = "rgba(255,255,255,0.10)";
    ctx.lineWidth = 1;
    for (let x = 0; x < viewW; x += 34) {
      ctx.beginPath();
      ctx.moveTo(x + 0.5, 0);
      ctx.lineTo(x + 0.5, viewH);
      ctx.stroke();
    }
    for (let y = 0; y < viewH; y += 34) {
      ctx.beginPath();
      ctx.moveTo(0, y + 0.5);
      ctx.lineTo(viewW, y + 0.5);
      ctx.stroke();
    }
    ctx.restore();

    // ground
    ctx.save();
    ctx.fillStyle = "rgba(255,255,255,0.06)";
    ctx.fillRect(0, ground.y, viewW, viewH - ground.y);
    ctx.strokeStyle = "rgba(255,255,255,0.18)";
    ctx.lineWidth = 2;
    ctx.beginPath();
    ctx.moveTo(0, ground.y + 0.5);
    ctx.lineTo(viewW, ground.y + 0.5);
    ctx.stroke();
    ctx.restore();

    // slingshot posts
    const a = slingshot.anchor;
    ctx.save();
    ctx.lineCap = "round";
    ctx.strokeStyle = "rgba(255,255,255,0.22)";
    ctx.lineWidth = 10;
    const postH = 78;
    const gap = 20;
    ctx.beginPath();
    ctx.moveTo(a.x - gap, a.y + 8);
    ctx.lineTo(a.x - gap, a.y + 8 - postH);
    ctx.stroke();
    ctx.beginPath();
    ctx.moveTo(a.x + gap, a.y + 8);
    ctx.lineTo(a.x + gap, a.y + 8 - postH);
    ctx.stroke();

    // band
    const pocket = ball.state === "dragging" ? ball.pos : getPocketPos();
    ctx.strokeStyle = "rgba(255,120,200,0.55)";
    ctx.lineWidth = 5;
    ctx.beginPath();
    ctx.moveTo(a.x - gap, a.y - 28);
    ctx.quadraticCurveTo((a.x + pocket.x) / 2, (a.y + pocket.y) / 2, pocket.x, pocket.y);
    ctx.stroke();
    ctx.beginPath();
    ctx.moveTo(a.x + gap, a.y - 28);
    ctx.quadraticCurveTo((a.x + pocket.x) / 2, (a.y + pocket.y) / 2, pocket.x, pocket.y);
    ctx.stroke();
    ctx.restore();

    // trajectory
    drawTrajectoryPreview();

    // blocks
    ctx.save();
    for (const blk of blocks) {
      const ratio = blk.maxHp > 0 ? clamp(blk.hp / blk.maxHp, 0, 1) : 0;
      const fill = `rgba(${Math.floor(lerp(255, 124, ratio))},${Math.floor(
        lerp(120, 240, ratio)
      )},${Math.floor(lerp(200, 199, ratio))},0.22)`;
      ctx.fillStyle = fill;
      ctx.strokeStyle = `rgba(255,255,255,${lerp(0.32, 0.12, ratio)})`;
      ctx.lineWidth = 2;
      ctx.beginPath();
      roundRect(ctx, blk.x, blk.y, blk.w, blk.h, 10);
      ctx.fill();
      ctx.stroke();

      // HP bar
      const barH = 5;
      const pad = 6;
      const barW = blk.w - pad * 2;
      const hpW = barW * ratio;
      ctx.fillStyle = "rgba(0,0,0,0.24)";
      ctx.fillRect(blk.x + pad, blk.y + blk.h - barH - pad, barW, barH);
      ctx.fillStyle = "rgba(122,168,255,0.90)";
      ctx.fillRect(blk.x + pad, blk.y + blk.h - barH - pad, hpW, barH);

      if (ui.debug) {
        ctx.strokeStyle = "rgba(124,240,199,0.55)";
        ctx.lineWidth = 1;
        ctx.strokeRect(blk.x + 0.5, blk.y + 0.5, blk.w, blk.h);
      }
    }
    ctx.restore();

    // ball
    ctx.save();
    ctx.fillStyle = "rgba(122,168,255,0.92)";
    ctx.shadowColor = "rgba(122,168,255,0.35)";
    ctx.shadowBlur = 18;
    ctx.beginPath();
    ctx.arc(ball.pos.x, ball.pos.y, ball.r, 0, Math.PI * 2);
    ctx.fill();
    ctx.shadowBlur = 0;

    // highlight
    ctx.globalAlpha = 0.45;
    ctx.fillStyle = "rgba(255,255,255,0.85)";
    ctx.beginPath();
    ctx.arc(ball.pos.x - 4, ball.pos.y - 5, ball.r * 0.35, 0, Math.PI * 2);
    ctx.fill();
    ctx.restore();

    // floaters
    ctx.save();
    ctx.textAlign = "center";
    ctx.textBaseline = "middle";
    ctx.font = "700 14px ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial";
    for (const f of floaters) {
      const t = f.t;
      const a2 = clamp(1 - t / 0.85, 0, 1);
      ctx.globalAlpha = a2;
      ctx.fillStyle = f.color;
      ctx.fillText(f.text, f.x, f.y - t * 40);
    }
    ctx.restore();

    // debug overlays
    if (ui.debug) {
      ctx.save();
      ctx.fillStyle = "rgba(255,255,255,0.80)";
      ctx.font = "12px ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace";
      ctx.fillText(`dtFixed=${world.fixedDt.toFixed(4)}  simT=${simT.toFixed(2)}`, 12, 18);
      ctx.fillText(`ball: (${ball.pos.x.toFixed(1)},${ball.pos.y.toFixed(1)}) v=${vLen(ball.vel).toFixed(1)}`, 12, 36);
      ctx.restore();
    }

    // HUD
    const stretch = ball.state === "dragging" ? vLen(vSub(ball.pos, slingshot.anchor)) : 0;
    const power = Math.round((clamp(stretch, 0, slingshot.maxStretch) / slingshot.maxStretch) * 100);
    hudState.textContent = ball.state;
    hudPower.textContent = `${power}%`;
    hudSpeed.textContent = `${Math.round(vLen(ball.vel))}`;
    hudBlocks.textContent = `${blocks.length}`;
    hudDestroyed.textContent = `${ui.destroyed}`;
    hudShots.textContent = `${ui.shots}`;
  }

  function roundRect(c, x, y, w, h, r) {
    const rr = Math.min(r, w / 2, h / 2);
    c.moveTo(x + rr, y);
    c.arcTo(x + w, y, x + w, y + h, rr);
    c.arcTo(x + w, y + h, x, y + h, rr);
    c.arcTo(x, y + h, x, y, rr);
    c.arcTo(x, y, x + w, y, rr);
    c.closePath();
  }

  // ---------------------------
  // Main loop (fixed timestep)
  // ---------------------------
  let last = performance.now();
  let acc = 0;

  function frame(now) {
    const dt = clamp((now - last) / 1000, 0, 0.08);
    last = now;
    acc += dt;

    const maxSubSteps = 16;
    let steps = 0;
    while (acc >= world.fixedDt && steps < maxSubSteps) {
      step(world.fixedDt);
      acc -= world.fixedDt;
      steps += 1;
    }
    if (steps >= maxSubSteps) acc = 0; // drop remainder to avoid spiral of death

    draw();
    requestAnimationFrame(frame);
  }

  requestAnimationFrame(frame);

  // Small UX: if user resizes, keep things consistent
  window.addEventListener(
    "resize",
    () => {
      resizeCanvas();
      layoutWorld();
      // 关卡位置依赖 viewW/viewH，直接重置以避免方块跑出屏幕
      resetLevel();
    },
    { passive: true }
  );
})();


