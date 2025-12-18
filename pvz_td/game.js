(() => {
  /** @type {HTMLCanvasElement} */
  const canvas = document.getElementById("game");
  const ctx = canvas.getContext("2d");

  // ====== 基础参数（你可以很方便地在这里改规则）======
  const LANES = 5;
  const COLS = 9;
  const CELL_W = 86;
  const CELL_H = 90;
  const GRID_X = 120; // 左侧留一点“草地/基地区域”
  const GRID_Y = 45;
  const GRID_W = COLS * CELL_W;
  const GRID_H = LANES * CELL_H;

  // ====== 资源 / 基地 ======
  const TOWER_COST = 25;
  const RESOURCE_PER_SEC = 6;
  const KILL_REWARD = 8;

  const base = { hp: 100, maxHp: 100 };
  let resources = 50;
  let kills = 0;

  // ====== 输入状态 ======
  const mouse = { x: 0, y: 0, inside: false };
  let selectedTowerType = "pea";

  // ====== 实体 ======
  /** @type {(Tower|null)[][]} */
  const towers = Array.from({ length: LANES }, () => Array.from({ length: COLS }, () => null));
  /** @type {Enemy[]} */
  const enemies = [];
  /** @type {Projectile[]} */
  const projectiles = [];

  // ====== 计时 ======
  let lastT = performance.now();
  let spawnTimer = 0;
  let spawnEvery = 1.8; // 秒
  let resourceAcc = 0;
  let gameOver = false;

  // ====== DOM HUD ======
  const resVal = document.getElementById("resVal");
  const spawnVal = document.getElementById("spawnVal");
  const killVal = document.getElementById("killVal");
  const placePeaBtn = document.getElementById("placePea");
  const restartBtn = document.getElementById("restart");
  const baseBar = document.getElementById("baseBar");
  const baseHpText = document.getElementById("baseHpText");

  placePeaBtn.addEventListener("click", () => {
    selectedTowerType = "pea";
  });

  restartBtn.addEventListener("click", () => resetGame());

  canvas.addEventListener("mousemove", (e) => {
    const r = canvas.getBoundingClientRect();
    mouse.x = ((e.clientX - r.left) / r.width) * canvas.width;
    mouse.y = ((e.clientY - r.top) / r.height) * canvas.height;
    mouse.inside = true;
  });
  canvas.addEventListener("mouseleave", () => {
    mouse.inside = false;
  });

  canvas.addEventListener("click", () => {
    if (gameOver) return;
    const cell = getCellFromMouse();
    if (!cell) return;
    tryPlaceTower(cell.r, cell.c);
  });

  // ====== 类型定义（用 JSDoc 让编辑器更友好）======
  /**
   * @typedef {Object} Tower
   * @property {number} r
   * @property {number} c
   * @property {number} x
   * @property {number} y
   * @property {number} fireRate
   * @property {number} cooldown
   * @property {number} damage
   * @property {number} projectileSpeed
   */
  /**
   * @typedef {Object} Enemy
   * @property {number} lane
   * @property {number} x
   * @property {number} y
   * @property {number} w
   * @property {number} h
   * @property {number} speed
   * @property {number} hp
   * @property {number} maxHp
   */
  /**
   * @typedef {Object} Projectile
   * @property {number} lane
   * @property {number} x
   * @property {number} y
   * @property {number} vx
   * @property {number} r
   * @property {number} damage
   */

  // ====== Step 1: 网格系统 + 放置 ======
  function getCellFromMouse() {
    if (!mouse.inside) return null;
    const gx = mouse.x - GRID_X;
    const gy = mouse.y - GRID_Y;
    if (gx < 0 || gy < 0 || gx >= GRID_W || gy >= GRID_H) return null;
    const c = Math.floor(gx / CELL_W);
    const r = Math.floor(gy / CELL_H);
    return { r, c };
  }

  function cellToWorldCenter(r, c) {
    return {
      x: GRID_X + c * CELL_W + CELL_W / 2,
      y: GRID_Y + r * CELL_H + CELL_H / 2,
    };
  }

  function tryPlaceTower(r, c) {
    if (towers[r][c]) return;
    if (resources < TOWER_COST) return;
    // 允许放置范围：整个格子区
    const p = cellToWorldCenter(r, c);
    towers[r][c] = makePeaTower(r, c, p.x, p.y);
    resources -= TOWER_COST;
  }

  function makePeaTower(r, c, x, y) {
    /** @type {Tower} */
    const t = {
      r,
      c,
      x,
      y,
      fireRate: 1.0, // 每秒 1 发
      cooldown: 0,
      damage: 18,
      projectileSpeed: 420,
    };
    return t;
  }

  // ====== Step 2: 敌人生成 + 移动（右 -> 左）======
  function spawnEnemy() {
    const lane = Math.floor(Math.random() * LANES);
    const y = GRID_Y + lane * CELL_H + CELL_H / 2;
    const w = 44;
    const h = 58;
    const hp = 70 + Math.floor(Math.random() * 35);
    /** @type {Enemy} */
    const e = {
      lane,
      x: canvas.width + 30,
      y,
      w,
      h,
      speed: 34 + Math.random() * 18, // px/s
      hp,
      maxHp: hp,
    };
    enemies.push(e);
  }

  function updateEnemies(dt) {
    for (let i = enemies.length - 1; i >= 0; i--) {
      const e = enemies[i];
      e.x -= e.speed * dt;
      // 到基地：扣血并移除
      if (e.x - e.w / 2 <= 18) {
        base.hp = Math.max(0, base.hp - 10);
        enemies.splice(i, 1);
        if (base.hp <= 0) gameOver = true;
      }
    }
  }

  // ====== Step 3: 塔攻击逻辑（仅攻击同一行）======
  function getFirstEnemyInLaneAhead(lane, x) {
    let best = null;
    for (const e of enemies) {
      if (e.lane !== lane) continue;
      if (e.x < x) continue; // 只打塔右侧的目标
      if (!best || e.x < best.x) best = e;
    }
    return best;
  }

  function updateTowers(dt) {
    for (let r = 0; r < LANES; r++) {
      for (let c = 0; c < COLS; c++) {
        const t = towers[r][c];
        if (!t) continue;
        t.cooldown -= dt;
        const target = getFirstEnemyInLaneAhead(r, t.x);
        if (!target) continue;
        if (t.cooldown <= 0) {
          fireProjectile(t, target);
          t.cooldown = 1 / t.fireRate;
        }
      }
    }
  }

  function fireProjectile(tower, enemy) {
    // 子弹从塔中心向右直线飞行（同 lane）
    /** @type {Projectile} */
    const p = {
      lane: tower.r,
      x: tower.x + 18,
      y: tower.y,
      vx: tower.projectileSpeed,
      r: 6,
      damage: tower.damage,
    };
    projectiles.push(p);
  }

  function updateProjectiles(dt) {
    for (let i = projectiles.length - 1; i >= 0; i--) {
      const p = projectiles[i];
      p.x += p.vx * dt;

      // 碰撞：与同 lane 的任意敌人 AABB 近似
      let hitIndex = -1;
      for (let j = 0; j < enemies.length; j++) {
        const e = enemies[j];
        if (e.lane !== p.lane) continue;
        if (Math.abs(p.x - e.x) <= e.w / 2 + p.r && Math.abs(p.y - e.y) <= e.h / 2 + p.r) {
          hitIndex = j;
          break;
        }
      }

      if (hitIndex !== -1) {
        const e = enemies[hitIndex];
        e.hp -= p.damage;
        projectiles.splice(i, 1);
        if (e.hp <= 0) {
          enemies.splice(hitIndex, 1);
          kills += 1;
          resources += KILL_REWARD;
        }
        continue;
      }

      // 飞出画面
      if (p.x > canvas.width + 40) {
        projectiles.splice(i, 1);
      }
    }
  }

  // ====== 绘制 ======
  function draw() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    drawBackground();
    drawGrid();
    drawTowers();
    drawEnemies();
    drawProjectiles();
    drawOverlay();
  }

  function drawBackground() {
    // 基地区域
    ctx.fillStyle = "rgba(255,255,255,0.03)";
    ctx.fillRect(0, 0, GRID_X, canvas.height);

    // 基地线
    ctx.strokeStyle = "rgba(255,255,255,0.18)";
    ctx.lineWidth = 2;
    ctx.beginPath();
    ctx.moveTo(GRID_X, GRID_Y);
    ctx.lineTo(GRID_X, GRID_Y + GRID_H);
    ctx.stroke();

    // 车道淡色条纹
    for (let r = 0; r < LANES; r++) {
      const y = GRID_Y + r * CELL_H;
      ctx.fillStyle = r % 2 === 0 ? "rgba(255,255,255,0.02)" : "rgba(0,0,0,0.05)";
      ctx.fillRect(GRID_X, y, GRID_W, CELL_H);
    }
  }

  function drawGrid() {
    // 外框
    ctx.strokeStyle = "rgba(255,255,255,0.16)";
    ctx.lineWidth = 1;
    ctx.strokeRect(GRID_X, GRID_Y, GRID_W, GRID_H);

    // 内线
    ctx.strokeStyle = "rgba(255,255,255,0.10)";
    ctx.beginPath();
    for (let c = 1; c < COLS; c++) {
      const x = GRID_X + c * CELL_W;
      ctx.moveTo(x, GRID_Y);
      ctx.lineTo(x, GRID_Y + GRID_H);
    }
    for (let r = 1; r < LANES; r++) {
      const y = GRID_Y + r * CELL_H;
      ctx.moveTo(GRID_X, y);
      ctx.lineTo(GRID_X + GRID_W, y);
    }
    ctx.stroke();

    // 悬停高亮
    const cell = getCellFromMouse();
    if (cell && !gameOver) {
      const x = GRID_X + cell.c * CELL_W;
      const y = GRID_Y + cell.r * CELL_H;
      const occupied = !!towers[cell.r][cell.c];
      const canAfford = resources >= TOWER_COST;

      ctx.fillStyle = occupied
        ? "rgba(251,113,133,0.14)"
        : canAfford
          ? "rgba(110,231,183,0.12)"
          : "rgba(255,255,255,0.06)";
      ctx.fillRect(x + 2, y + 2, CELL_W - 4, CELL_H - 4);

      ctx.strokeStyle = occupied
        ? "rgba(251,113,133,0.45)"
        : canAfford
          ? "rgba(110,231,183,0.45)"
          : "rgba(255,255,255,0.25)";
      ctx.strokeRect(x + 2.5, y + 2.5, CELL_W - 5, CELL_H - 5);
    }
  }

  function drawTowers() {
    for (let r = 0; r < LANES; r++) {
      for (let c = 0; c < COLS; c++) {
        const t = towers[r][c];
        if (!t) continue;
        // 身体
        ctx.fillStyle = "rgba(110,231,183,0.95)";
        roundRect(ctx, t.x - 18, t.y - 22, 36, 44, 10);
        ctx.fill();
        // 炮口
        ctx.fillStyle = "rgba(255,255,255,0.9)";
        roundRect(ctx, t.x + 8, t.y - 6, 16, 12, 6);
        ctx.fill();
        // 冷却指示
        const cd = Math.max(0, Math.min(1, t.cooldown * t.fireRate)); // 0..1
        ctx.strokeStyle = "rgba(255,255,255,0.35)";
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.arc(t.x, t.y + 30, 10, -Math.PI / 2, -Math.PI / 2 + (1 - cd) * Math.PI * 2);
        ctx.stroke();
      }
    }
  }

  function drawEnemies() {
    for (const e of enemies) {
      // 身体
      ctx.fillStyle = "rgba(248,113,113,0.95)";
      roundRect(ctx, e.x - e.w / 2, e.y - e.h / 2, e.w, e.h, 10);
      ctx.fill();

      // 血条
      const hpPct = Math.max(0, e.hp / e.maxHp);
      const barW = e.w;
      const barH = 6;
      const bx = e.x - barW / 2;
      const by = e.y - e.h / 2 - 10;
      ctx.fillStyle = "rgba(0,0,0,0.35)";
      roundRect(ctx, bx, by, barW, barH, 4);
      ctx.fill();
      ctx.fillStyle = "rgba(255,255,255,0.85)";
      roundRect(ctx, bx, by, barW * hpPct, barH, 4);
      ctx.fill();
    }
  }

  function drawProjectiles() {
    for (const p of projectiles) {
      ctx.fillStyle = "rgba(255,255,255,0.95)";
      ctx.beginPath();
      ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
      ctx.fill();
    }
  }

  function drawOverlay() {
    // 左侧基地提示
    ctx.fillStyle = "rgba(255,255,255,0.65)";
    ctx.font = "12px ui-sans-serif, system-ui";
    ctx.fillText("基地", 18, 26);
    ctx.fillStyle = "rgba(255,255,255,0.25)";
    ctx.fillText("敌人到这里会扣血", 18, 44);

    if (gameOver) {
      ctx.fillStyle = "rgba(0,0,0,0.55)";
      ctx.fillRect(0, 0, canvas.width, canvas.height);
      ctx.fillStyle = "rgba(255,255,255,0.95)";
      ctx.font = "700 34px ui-sans-serif, system-ui";
      ctx.fillText("游戏结束", canvas.width / 2 - 78, canvas.height / 2 - 10);
      ctx.fillStyle = "rgba(255,255,255,0.75)";
      ctx.font = "14px ui-sans-serif, system-ui";
      ctx.fillText("点击右侧“重新开始”再来一局", canvas.width / 2 - 120, canvas.height / 2 + 20);
    }
  }

  function roundRect(c, x, y, w, h, r) {
    c.beginPath();
    c.moveTo(x + r, y);
    c.arcTo(x + w, y, x + w, y + h, r);
    c.arcTo(x + w, y + h, x, y + h, r);
    c.arcTo(x, y + h, x, y, r);
    c.arcTo(x, y, x + w, y, r);
    c.closePath();
  }

  // ====== 主循环 ======
  function tick(now) {
    const dt = Math.min(0.033, (now - lastT) / 1000);
    lastT = now;

    if (!gameOver) {
      // 资源随时间增长
      resourceAcc += dt * RESOURCE_PER_SEC;
      if (resourceAcc >= 1) {
        const add = Math.floor(resourceAcc);
        resources += add;
        resourceAcc -= add;
      }

      // 敌人生成
      spawnTimer += dt;
      if (spawnTimer >= spawnEvery) {
        spawnTimer -= spawnEvery;
        spawnEnemy();
        // 缓慢加快节奏
        spawnEvery = Math.max(0.85, spawnEvery * 0.985);
      }

      updateTowers(dt);
      updateProjectiles(dt);
      updateEnemies(dt);
    }

    draw();
    updateHud();
    requestAnimationFrame(tick);
  }

  function updateHud() {
    resVal.textContent = String(resources);
    spawnVal.textContent = `${spawnEvery.toFixed(2)}s`;
    killVal.textContent = String(kills);

    placePeaBtn.disabled = resources < TOWER_COST || gameOver;

    const pct = base.hp / base.maxHp;
    baseBar.style.width = `${Math.max(0, Math.min(1, pct)) * 100}%`;
    baseHpText.textContent = `${base.hp}/${base.maxHp}`;
  }

  function resetGame() {
    enemies.length = 0;
    projectiles.length = 0;
    for (let r = 0; r < LANES; r++) {
      for (let c = 0; c < COLS; c++) towers[r][c] = null;
    }
    resources = 50;
    kills = 0;
    base.hp = base.maxHp;
    spawnTimer = 0;
    spawnEvery = 1.8;
    resourceAcc = 0;
    gameOver = false;
  }

  // 启动
  resetGame();
  requestAnimationFrame(tick);
})();


