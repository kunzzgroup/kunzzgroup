// 游戏核心类
class Game {
    constructor() {
        this.player = null;
        this.currentEnemy = null;
        this.inCombat = false;
        this.currentLocation = 'village';
        this.quests = [];
        this.inventory = [];
        this.gameData = this.initGameData();
        this.init();
    }

    init() {
        this.showCharacterCreation();
        this.setupEventListeners();
    }

    initGameData() {
        return {
            locations: {
                village: {
                    name: '新手村',
                    description: '一个宁静的小村庄，适合新手冒险者开始他们的旅程。',
                    enemies: ['goblin', 'wolf'],
                    actions: ['explore', 'shop', 'rest']
                },
                forest: {
                    name: '黑暗森林',
                    description: '一片神秘的森林，充满了危险的生物和隐藏的宝藏。',
                    enemies: ['orc', 'spider', 'bear'],
                    actions: ['explore', 'hunt']
                },
                dungeon: {
                    name: '古老地牢',
                    description: '一个被遗忘的地牢，深处隐藏着强大的怪物和珍贵的宝物。',
                    enemies: ['skeleton', 'zombie', 'demon'],
                    actions: ['explore', 'boss']
                }
            },
            enemies: {
                goblin: {
                    name: '哥布林',
                    level: 1,
                    health: 30,
                    maxHealth: 30,
                    attack: 5,
                    defense: 2,
                    exp: 15,
                    gold: 10
                },
                wolf: {
                    name: '野狼',
                    level: 2,
                    health: 50,
                    maxHealth: 50,
                    attack: 8,
                    defense: 3,
                    exp: 25,
                    gold: 20
                },
                orc: {
                    name: '兽人',
                    level: 3,
                    health: 80,
                    maxHealth: 80,
                    attack: 12,
                    defense: 5,
                    exp: 40,
                    gold: 35
                },
                spider: {
                    name: '巨型蜘蛛',
                    level: 4,
                    health: 100,
                    maxHealth: 100,
                    attack: 15,
                    defense: 4,
                    exp: 50,
                    gold: 45
                },
                bear: {
                    name: '巨熊',
                    level: 5,
                    health: 150,
                    maxHealth: 150,
                    attack: 20,
                    defense: 8,
                    exp: 75,
                    gold: 60
                },
                skeleton: {
                    name: '骷髅战士',
                    level: 6,
                    health: 120,
                    maxHealth: 120,
                    attack: 18,
                    defense: 6,
                    exp: 65,
                    gold: 50
                },
                zombie: {
                    name: '僵尸',
                    level: 7,
                    health: 180,
                    maxHealth: 180,
                    attack: 22,
                    defense: 7,
                    exp: 90,
                    gold: 70
                },
                demon: {
                    name: '恶魔',
                    level: 10,
                    health: 300,
                    maxHealth: 300,
                    attack: 35,
                    defense: 12,
                    exp: 200,
                    gold: 150
                }
            },
            items: {
                healthPotion: {
                    name: '生命药水',
                    type: 'consumable',
                    effect: { health: 50 },
                    price: 20
                },
                manaPotion: {
                    name: '魔法药水',
                    type: 'consumable',
                    effect: { mana: 30 },
                    price: 25
                },
                ironSword: {
                    name: '铁剑',
                    type: 'weapon',
                    attack: 5,
                    price: 100
                },
                steelArmor: {
                    name: '钢甲',
                    type: 'armor',
                    defense: 5,
                    price: 150
                }
            },
            classes: {
                warrior: {
                    name: '战士',
                    stats: { strength: 15, dexterity: 8, intelligence: 5, constitution: 12 },
                    health: 120,
                    mana: 30
                },
                rogue: {
                    name: '盗贼',
                    stats: { strength: 8, dexterity: 15, intelligence: 8, constitution: 9 },
                    health: 90,
                    mana: 50
                },
                mage: {
                    name: '法师',
                    stats: { strength: 5, dexterity: 8, intelligence: 15, constitution: 7 },
                    health: 70,
                    mana: 100
                }
            }
        };
    }

    showCharacterCreation() {
        const modal = document.getElementById('character-creation-modal');
        modal.classList.add('active');

        // 职业选择
        document.querySelectorAll('.class-option').forEach(option => {
            option.addEventListener('click', () => {
                document.querySelectorAll('.class-option').forEach(opt => opt.classList.remove('selected'));
                option.classList.add('selected');
            });
        });

        // 创建角色按钮
        document.getElementById('create-character-btn').addEventListener('click', () => {
            this.createCharacter();
        });
    }

    createCharacter() {
        const name = document.getElementById('player-name').value.trim();
        const selectedClass = document.querySelector('.class-option.selected');

        if (!name) {
            alert('请输入角色名称！');
            return;
        }

        if (!selectedClass) {
            alert('请选择职业！');
            return;
        }

        const classType = selectedClass.dataset.class;
        const classData = this.gameData.classes[classType];

        this.player = {
            name: name,
            class: classType,
            className: classData.name,
            level: 1,
            exp: 0,
            expToNext: 100,
            gold: 100,
            health: classData.health,
            maxHealth: classData.health,
            mana: classData.mana,
            maxMana: classData.mana,
            stats: { ...classData.stats },
            equipment: {
                weapon: null,
                armor: null
            }
        };

        document.getElementById('character-creation-modal').classList.remove('active');
        this.updateUI();
        this.addStoryEntry(`欢迎，${name}！你选择了${classData.name}职业，准备开始你的冒险之旅吧！`, 'welcome');
        this.updateActionButtons();
    }

    updateUI() {
        if (!this.player) return;

        // 更新角色信息
        document.getElementById('character-name').textContent = this.player.name;
        document.getElementById('character-class').textContent = this.player.className;
        document.getElementById('player-level').textContent = this.player.level;
        document.getElementById('player-gold').textContent = this.player.gold;

        // 更新生命值
        const healthPercent = (this.player.health / this.player.maxHealth) * 100;
        document.getElementById('health-bar').style.width = healthPercent + '%';
        document.getElementById('health-text').textContent = `${this.player.health}/${this.player.maxHealth}`;

        // 更新魔法值
        const manaPercent = (this.player.mana / this.player.maxMana) * 100;
        document.getElementById('mana-bar').style.width = manaPercent + '%';
        document.getElementById('mana-text').textContent = `${this.player.mana}/${this.player.maxMana}`;

        // 更新经验值
        const expPercent = (this.player.exp / this.player.expToNext) * 100;
        document.getElementById('exp-bar').style.width = expPercent + '%';
        document.getElementById('exp-text').textContent = `${this.player.exp}/${this.player.expToNext}`;

        // 更新属性
        document.getElementById('attr-strength').textContent = this.player.stats.strength;
        document.getElementById('attr-dexterity').textContent = this.player.stats.dexterity;
        document.getElementById('attr-intelligence').textContent = this.player.stats.intelligence;
        document.getElementById('attr-constitution').textContent = this.player.stats.constitution;

        // 更新地点
        const location = this.gameData.locations[this.currentLocation];
        document.getElementById('current-location').textContent = location.name;
        document.getElementById('location-desc').textContent = location.description;

        // 更新背包
        this.updateInventory();
    }

    updateInventory() {
        const inventoryGrid = document.getElementById('inventory');
        inventoryGrid.innerHTML = '';

        for (let i = 0; i < 16; i++) {
            const slot = document.createElement('div');
            slot.className = 'inventory-slot';
            
            if (this.inventory[i]) {
                slot.classList.add('filled');
                const item = this.inventory[i];
                slot.innerHTML = `
                    <div class="item-name">${item.name}</div>
                    ${item.count > 1 ? `<div class="item-count">${item.count}</div>` : ''}
                `;
                slot.addEventListener('click', () => this.useItem(i));
            }
            
            inventoryGrid.appendChild(slot);
        }
    }

    addStoryEntry(text, type = 'normal') {
        const storyContent = document.getElementById('story-content');
        const entry = document.createElement('div');
        entry.className = `story-entry ${type}`;
        entry.innerHTML = `<p>${text}</p>`;
        storyContent.appendChild(entry);
        storyContent.scrollTop = storyContent.scrollHeight;
    }

    updateActionButtons() {
        const actionButtons = document.getElementById('action-buttons');
        actionButtons.innerHTML = '';

        if (this.inCombat) {
            return; // 战斗按钮在战斗面板中
        }

        const location = this.gameData.locations[this.currentLocation];
        
        location.actions.forEach(action => {
            const btn = document.createElement('button');
            btn.className = 'btn btn-primary';
            
            switch(action) {
                case 'explore':
                    btn.textContent = '探索';
                    btn.addEventListener('click', () => this.explore());
                    break;
                case 'shop':
                    btn.textContent = '商店';
                    btn.addEventListener('click', () => this.openShop());
                    break;
                case 'rest':
                    btn.textContent = '休息';
                    btn.addEventListener('click', () => this.rest());
                    break;
                case 'hunt':
                    btn.textContent = '狩猎';
                    btn.addEventListener('click', () => this.hunt());
                    break;
                case 'boss':
                    btn.textContent = '挑战Boss';
                    btn.className = 'btn btn-danger';
                    btn.addEventListener('click', () => this.fightBoss());
                    break;
            }
            
            actionButtons.appendChild(btn);
        });

        // 添加移动按钮
        if (this.currentLocation !== 'village') {
            const moveBtn = document.createElement('button');
            moveBtn.className = 'btn btn-secondary';
            moveBtn.textContent = '返回新手村';
            moveBtn.addEventListener('click', () => this.changeLocation('village'));
            actionButtons.appendChild(moveBtn);
        }

        if (this.currentLocation === 'village' && this.player.level >= 3) {
            const moveBtn = document.createElement('button');
            moveBtn.className = 'btn btn-secondary';
            moveBtn.textContent = '前往黑暗森林';
            moveBtn.addEventListener('click', () => this.changeLocation('forest'));
            actionButtons.appendChild(moveBtn);
        }

        if (this.currentLocation === 'forest' && this.player.level >= 6) {
            const moveBtn = document.createElement('button');
            moveBtn.className = 'btn btn-secondary';
            moveBtn.textContent = '进入古老地牢';
            moveBtn.addEventListener('click', () => this.changeLocation('dungeon'));
            actionButtons.appendChild(moveBtn);
        }
    }

    explore() {
        const location = this.gameData.locations[this.currentLocation];
        const enemyTypes = location.enemies;
        const randomEnemy = enemyTypes[Math.floor(Math.random() * enemyTypes.length)];
        
        this.addStoryEntry(`你在${location.name}中探索时，遇到了一个${this.gameData.enemies[randomEnemy].name}！`, 'combat');
        this.startCombat(randomEnemy);
    }

    hunt() {
        this.explore(); // 狩猎就是探索的变体
    }

    fightBoss() {
        this.addStoryEntry('你决定挑战地牢的最终Boss - 强大的恶魔！', 'combat');
        this.startCombat('demon');
    }

    startCombat(enemyType) {
        const enemyData = this.gameData.enemies[enemyType];
        this.currentEnemy = {
            type: enemyType,
            ...enemyData,
            health: enemyData.maxHealth
        };
        this.inCombat = true;

        document.getElementById('combat-panel').style.display = 'block';
        this.updateCombatUI();
        this.updateCombatActions();
    }

    updateCombatUI() {
        if (!this.currentEnemy) return;

        document.getElementById('enemy-name').textContent = this.currentEnemy.name;
        document.getElementById('enemy-level').textContent = this.currentEnemy.level;
        
        const healthPercent = (this.currentEnemy.health / this.currentEnemy.maxHealth) * 100;
        document.getElementById('enemy-health-bar').style.width = healthPercent + '%';
        document.getElementById('enemy-health-text').textContent = `${this.currentEnemy.health}/${this.currentEnemy.maxHealth}`;
    }

    updateCombatActions() {
        const combatActions = document.getElementById('combat-actions');
        combatActions.innerHTML = '';

        const attackBtn = document.createElement('button');
        attackBtn.className = 'btn btn-danger';
        attackBtn.textContent = '攻击';
        attackBtn.addEventListener('click', () => this.playerAttack());
        combatActions.appendChild(attackBtn);

        if (this.player.class === 'mage' && this.player.mana >= 20) {
            const magicBtn = document.createElement('button');
            magicBtn.className = 'btn btn-primary';
            magicBtn.textContent = '火球术 (消耗20魔法)';
            magicBtn.addEventListener('click', () => this.playerMagicAttack());
            combatActions.appendChild(magicBtn);
        }

        const itemBtn = document.createElement('button');
        itemBtn.className = 'btn btn-warning';
        itemBtn.textContent = '使用物品';
        itemBtn.addEventListener('click', () => this.showItemMenu());
        combatActions.appendChild(itemBtn);

        const fleeBtn = document.createElement('button');
        fleeBtn.className = 'btn btn-secondary';
        fleeBtn.textContent = '逃跑';
        fleeBtn.addEventListener('click', () => this.fleeCombat());
        combatActions.appendChild(fleeBtn);
    }

    playerAttack() {
        if (!this.inCombat || !this.currentEnemy) return;

        const baseDamage = this.player.stats.strength;
        const weaponBonus = this.player.equipment.weapon ? this.player.equipment.weapon.attack : 0;
        const damage = Math.max(1, baseDamage + weaponBonus - this.currentEnemy.defense + Math.floor(Math.random() * 5));

        this.currentEnemy.health -= damage;
        this.addStoryEntry(`你对${this.currentEnemy.name}造成了${damage}点伤害！`, 'combat');

        if (this.currentEnemy.health <= 0) {
            this.victory();
        } else {
            this.updateCombatUI();
            setTimeout(() => this.enemyAttack(), 500);
        }
    }

    playerMagicAttack() {
        if (!this.inCombat || !this.currentEnemy || this.player.mana < 20) return;

        this.player.mana -= 20;
        const baseDamage = this.player.stats.intelligence * 2;
        const damage = Math.max(1, baseDamage - this.currentEnemy.defense + Math.floor(Math.random() * 10));

        this.currentEnemy.health -= damage;
        this.addStoryEntry(`你释放火球术，对${this.currentEnemy.name}造成了${damage}点魔法伤害！`, 'combat');
        this.updateUI();

        if (this.currentEnemy.health <= 0) {
            this.victory();
        } else {
            this.updateCombatUI();
            setTimeout(() => this.enemyAttack(), 500);
        }
    }

    enemyAttack() {
        if (!this.inCombat || !this.currentEnemy) return;

        const baseDamage = this.currentEnemy.attack;
        const armorBonus = this.player.equipment.armor ? this.player.equipment.armor.defense : 0;
        const damage = Math.max(1, baseDamage - armorBonus - Math.floor(this.player.stats.constitution / 2) + Math.floor(Math.random() * 3));

        this.player.health -= damage;
        this.addStoryEntry(`${this.currentEnemy.name}对你造成了${damage}点伤害！`, 'combat');
        this.updateUI();

        if (this.player.health <= 0) {
            this.defeat();
        }
    }

    victory() {
        const exp = this.currentEnemy.exp;
        const gold = this.currentEnemy.gold;

        this.player.exp += exp;
        this.player.gold += gold;

        this.addStoryEntry(`你击败了${this.currentEnemy.name}！获得${exp}经验值和${gold}金币！`, 'victory');

        // 检查升级
        while (this.player.exp >= this.player.expToNext) {
            this.levelUp();
        }

        this.inCombat = false;
        this.currentEnemy = null;
        document.getElementById('combat-panel').style.display = 'none';
        this.updateUI();
        this.updateActionButtons();
    }

    defeat() {
        this.addStoryEntry('你被击败了！你失去了所有金币，但被好心人救回了新手村。', 'defeat');
        
        this.player.health = Math.floor(this.player.maxHealth * 0.5);
        this.player.mana = this.player.maxMana;
        this.player.gold = 0;
        this.currentLocation = 'village';

        this.inCombat = false;
        this.currentEnemy = null;
        document.getElementById('combat-panel').style.display = 'none';
        this.updateUI();
        this.updateActionButtons();
    }

    fleeCombat() {
        const fleeChance = this.player.stats.dexterity / 20;
        if (Math.random() < fleeChance) {
            this.addStoryEntry('你成功逃跑了！', 'normal');
        } else {
            this.addStoryEntry('逃跑失败！', 'combat');
            setTimeout(() => this.enemyAttack(), 500);
            return;
        }

        this.inCombat = false;
        this.currentEnemy = null;
        document.getElementById('combat-panel').style.display = 'none';
        this.updateActionButtons();
    }

    levelUp() {
        this.player.exp -= this.player.expToNext;
        this.player.level++;
        this.player.expToNext = Math.floor(this.player.expToNext * 1.5);

        // 属性提升
        this.player.stats.strength += 2;
        this.player.stats.dexterity += 2;
        this.player.stats.intelligence += 2;
        this.player.stats.constitution += 2;

        // 生命值和魔法值提升
        this.player.maxHealth += 20;
        this.player.maxMana += 10;
        this.player.health = this.player.maxHealth;
        this.player.mana = this.player.maxMana;

        this.addStoryEntry(`恭喜！你升级到了${this.player.level}级！所有属性提升了！`, 'victory');
    }

    rest() {
        this.player.health = this.player.maxHealth;
        this.player.mana = this.player.maxMana;
        this.addStoryEntry('你休息了一会儿，恢复了所有生命值和魔法值。', 'normal');
        this.updateUI();
    }

    changeLocation(location) {
        if (this.inCombat) {
            this.addStoryEntry('战斗中无法移动！', 'combat');
            return;
        }

        this.currentLocation = location;
        const locationData = this.gameData.locations[location];
        this.addStoryEntry(`你来到了${locationData.name}。${locationData.description}`, 'normal');
        this.updateUI();
        this.updateActionButtons();
    }

    openShop() {
        this.addStoryEntry('商店功能开发中...你可以通过战斗获得金币和物品。', 'normal');
    }

    useItem(index) {
        if (!this.inventory[index]) return;

        const item = this.inventory[index];
        
        if (item.type === 'consumable') {
            if (item.effect.health) {
                this.player.health = Math.min(this.player.maxHealth, this.player.health + item.effect.health);
                this.addStoryEntry(`你使用了${item.name}，恢复了${item.effect.health}点生命值。`, 'normal');
            }
            if (item.effect.mana) {
                this.player.mana = Math.min(this.player.maxMana, this.player.mana + item.effect.mana);
                this.addStoryEntry(`你使用了${item.name}，恢复了${item.effect.mana}点魔法值。`, 'normal');
            }

            item.count--;
            if (item.count <= 0) {
                this.inventory[index] = null;
            }
        }

        this.updateUI();
    }

    showItemMenu() {
        // 简化版：自动使用第一个可用物品
        for (let i = 0; i < this.inventory.length; i++) {
            if (this.inventory[i] && this.inventory[i].type === 'consumable') {
                this.useItem(i);
                return;
            }
        }
        this.addStoryEntry('你没有可用的消耗品！', 'normal');
    }

    setupEventListeners() {
        // 回车键创建角色
        document.getElementById('player-name').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                document.getElementById('create-character-btn').click();
            }
        });
    }
}

// 初始化游戏
let game;
window.addEventListener('DOMContentLoaded', () => {
    game = new Game();
});
