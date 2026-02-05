<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>企业蓝图管理 - KUNZZ HOLDINGS</title>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <link rel="stylesheet" href="../css/corporate_blueprint_edit.css">
</head>
<body class="has-sidebar">
    <?php $basePath = '../'; include '../pages/sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <h1>企业蓝图管理</h1>
            <p>编辑企业蓝图数据和咨询信息</p>
        </div>
        
        <div class="content">
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" id="corporate-form" onsubmit="return handleFormSubmit(event)">
                <!-- 标签导航栏 -->
                <div class="tab-navigation">
                    <button type="button" class="tab-btn active" onclick="switchTab('overview', this)">公司概述</button>
                    <button type="button" class="tab-btn" onclick="switchTab('timeline', this)">时间线</button>
                    <button type="button" class="tab-btn" onclick="switchTab('corporate-core', this)">企业核心</button>
                    <button type="button" class="tab-btn" onclick="switchTab('culture-explanation', this)">文化解说</button>
                    <button type="button" class="tab-btn" onclick="switchTab('values-explanation', this)">价值观解说</button>
                    <button type="button" class="tab-btn" onclick="switchTab('org-structure', this)">高层组织架构</button>
                    <button type="button" class="tab-btn" onclick="switchTab('internal-org', this)">内部组织架构</button>
                    <button type="button" class="tab-btn" onclick="switchTab('strategic-objectives', this)">战略目标</button>
                </div>
                
                <!-- 公司概述 -->
                <div class="section tab-section active" data-tab="overview">
                    <h2>公司概述</h2>
                    <div class="form-group">
                        <label>公司名称</label>
                        <input type="text" name="companyName" value="<?php echo htmlspecialchars($companyOverview['companyName'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>计划标题</label>
                        <input type="text" name="planTitle" value="<?php echo htmlspecialchars($companyOverview['planTitle'] ?? ''); ?>">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>战略开始年份</label>
                            <input type="number" name="strategyStartYear" value="<?php echo htmlspecialchars($companyOverview['strategyStartYear'] ?? date('Y')); ?>">
                        </div>
                        <div class="form-group">
                            <label>战略结束年份</label>
                            <input type="number" name="strategyEndYear" value="<?php echo htmlspecialchars($companyOverview['strategyEndYear'] ?? date('Y') + 5); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>终极目标</label>
                        <textarea name="ultimateGoal"><?php echo htmlspecialchars($companyOverview['ultimateGoal'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <!-- 高层组织架构 -->
                <div class="section tab-section" data-tab="org-structure">
                    <h2>高层组织架构</h2>
                    <div class="sub-section">
                        <h3>CEO</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label>姓名</label>
                                <input type="text" name="ceo_name" value="<?php echo htmlspecialchars($orgStructure['ceo']['name'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>职位</label>
                                <input type="text" name="ceo_title" value="<?php echo htmlspecialchars($orgStructure['ceo']['title'] ?? 'CEO'); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="sub-section">
                        <h3>PA (个人助理)</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label>姓名</label>
                                <input type="text" name="pa_name" value="<?php echo htmlspecialchars($orgStructure['pa']['name'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>职位</label>
                                <input type="text" name="pa_title" value="<?php echo htmlspecialchars($orgStructure['pa']['title'] ?? 'PA'); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="sub-section">
                        <h3>C-Level 高管</h3>
                        <div id="clevel-container">
                            <?php foreach (($orgStructure['cLevel'] ?? []) as $index => $clevel): ?>
                                <div class="clevel-item">
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>姓名</label>
                                            <input type="text" name="clevel[<?php echo $index; ?>][name]" value="<?php echo htmlspecialchars($clevel['name'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>职位</label>
                                            <input type="text" name="clevel[<?php echo $index; ?>][title]" value="<?php echo htmlspecialchars($clevel['title'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>完整职位名称</label>
                                        <input type="text" name="clevel[<?php echo $index; ?>][fullTitle]" value="<?php echo htmlspecialchars($clevel['fullTitle'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>汇报对象</label>
                                        <input type="text" name="clevel[<?php echo $index; ?>][reportsTo]" value="<?php echo htmlspecialchars($clevel['reportsTo'] ?? 'CEO'); ?>">
                                    </div>
                                    <button type="button" class="remove-btn" onclick="removeCLevel(this)">删除</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="add-btn" onclick="addCLevel()">添加 C-Level 高管</button>
                    </div>
                </div>
                
                <!-- 内部组织架构 -->
                <div class="section tab-section" data-tab="internal-org">
                    <h2>内部组织架构</h2>
                    <div id="departments-container">
                        <?php foreach (($internalOrg['departments'] ?? []) as $deptIndex => $dept): ?>
                            <div class="department-item">
                                <div class="form-group">
                                    <label>部门名称</label>
                                    <input type="text" name="departments[<?php echo $deptIndex; ?>][name]" value="<?php echo htmlspecialchars($dept['name'] ?? ''); ?>">
                                </div>
                                <div class="positions-container">
                                    <h3>职位列表</h3>
                                    <?php foreach (($dept['positions'] ?? []) as $posIndex => $pos): ?>
                                        <div class="position-item">
                                            <div class="form-group" style="margin-bottom: 0;">
                                                <label>职位</label>
                                                <input type="text" name="departments[<?php echo $deptIndex; ?>][positions][<?php echo $posIndex; ?>][title]" value="<?php echo htmlspecialchars($pos['title'] ?? ''); ?>">
                                            </div>
                                            <div class="form-group" style="margin-bottom: 0;">
                                                <label>姓名</label>
                                                <input type="text" name="departments[<?php echo $deptIndex; ?>][positions][<?php echo $posIndex; ?>][name]" value="<?php echo htmlspecialchars($pos['name'] ?? ''); ?>">
                                            </div>
                                            <button type="button" class="remove-btn" onclick="removePosition(this)">删除</button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="button" class="add-btn" onclick="addPosition(this)">添加职位</button>
                                <button type="button" class="remove-btn" onclick="removeDepartment(this)" style="margin-left: 10px;">删除部门</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="add-btn" onclick="addDepartment()">添加部门</button>
                </div>
                
                <!-- 时间线 -->
                <div class="section tab-section" data-tab="timeline">
                    <h2>时间线</h2>
                    <div id="timeline-container">
                        <?php foreach ($timeline as $index => $item): ?>
                            <div class="timeline-item">
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label>年份</label>
                                    <input type="number" name="timeline[<?php echo $index; ?>][year]" value="<?php echo htmlspecialchars($item['year'] ?? ''); ?>">
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label>目标</label>
                                    <input type="text" name="timeline[<?php echo $index; ?>][goal]" value="<?php echo htmlspecialchars($item['goal'] ?? ''); ?>">
                                </div>
                                <button type="button" class="remove-btn" onclick="removeTimeline(this)">删除</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="add-btn" onclick="addTimeline()">添加时间线项目</button>
                </div>
                
                <!-- 企业核心 -->
                <div class="section tab-section" data-tab="corporate-core">
                    <h2>企业核心</h2>
                    <div class="form-group">
                        <label>使命 (Mission)</label>
                        <textarea name="mission" rows="3"><?php echo htmlspecialchars($corporateCore['mission'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>愿景 (Vision)</label>
                        <textarea name="vision" rows="3"><?php echo htmlspecialchars($corporateCore['vision'] ?? ''); ?></textarea>
                    </div>
                    <div class="sub-section">
                        <h3>文化 (Culture)</h3>
                        <div id="culture-container">
                            <?php foreach (($corporateCore['culture'] ?? []) as $index => $culture): ?>
                                <div class="culture-item">
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <input type="text" name="culture[<?php echo $index; ?>]" value="<?php echo htmlspecialchars($culture); ?>">
                                    </div>
                                    <button type="button" class="remove-btn" onclick="removeCulture(this)">删除</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="add-btn" onclick="addCulture()">添加文化项</button>
                    </div>
                    <div class="sub-section">
                        <h3>价值观 (Values)</h3>
                        <div id="values-container">
                            <?php foreach (($corporateCore['values'] ?? []) as $index => $value): ?>
                                <div class="values-item">
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <input type="text" name="values[<?php echo $index; ?>]" value="<?php echo htmlspecialchars($value); ?>">
                                    </div>
                                    <button type="button" class="remove-btn" onclick="removeValue(this)">删除</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="add-btn" onclick="addValue()">添加价值观</button>
                    </div>
                </div>
                
                <!-- 文化解说 -->
                <div class="section tab-section" data-tab="culture-explanation">
                    <h2>文化解说 & 考核</h2>
                    <div id="culture-explanation-container">
                        <?php foreach ($cultureExplanation as $index => $explanation): ?>
                            <div class="culture-explanation-item">
                                <div class="form-group">
                                    <label>关键词 (Key)</label>
                                    <input type="text" name="cultureExplanation[<?php echo $index; ?>][key]" value="<?php echo htmlspecialchars($explanation['key'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label>描述 (Description)</label>
                                    <textarea name="cultureExplanation[<?php echo $index; ?>][description]" rows="4"><?php echo htmlspecialchars($explanation['description'] ?? ''); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>评分标准</label>
                                    <?php foreach (($explanation['scoring'] ?? []) as $scoreIndex => $score): ?>
                                        <div style="display: grid; grid-template-columns: 80px 1fr; gap: 10px; margin-bottom: 10px; align-items: center;">
                                            <label><?php echo htmlspecialchars($score['point']); ?>分:</label>
                                            <input type="text" name="cultureExplanation[<?php echo $index; ?>][scoring][<?php echo $scoreIndex; ?>][description]" value="<?php echo htmlspecialchars($score['description'] ?? ''); ?>">
                                            <input type="hidden" name="cultureExplanation[<?php echo $index; ?>][scoring][<?php echo $scoreIndex; ?>][point]" value="<?php echo htmlspecialchars($score['point']); ?>">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="button" class="remove-btn" onclick="removeCultureExplanation(this)">删除</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="add-btn" onclick="addCultureExplanation()">添加文化解说</button>
                </div>
                
                <!-- 价值观解说 -->
                <div class="section tab-section" data-tab="values-explanation">
                    <h2>价值观解说 & 考核</h2>
                    <div id="values-explanation-container">
                        <?php foreach ($valuesExplanation as $index => $explanation): ?>
                            <div class="values-explanation-item">
                                <div class="form-group">
                                    <label>关键词 (Key)</label>
                                    <input type="text" name="valuesExplanation[<?php echo $index; ?>][key]" value="<?php echo htmlspecialchars($explanation['key'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label>描述 (Description)</label>
                                    <textarea name="valuesExplanation[<?php echo $index; ?>][description]" rows="4"><?php echo htmlspecialchars($explanation['description'] ?? ''); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>评分标准</label>
                                    <?php foreach (($explanation['scoring'] ?? []) as $scoreIndex => $score): ?>
                                        <div style="display: grid; grid-template-columns: 80px 1fr; gap: 10px; margin-bottom: 10px; align-items: center;">
                                            <label><?php echo htmlspecialchars($score['point']); ?>分:</label>
                                            <input type="text" name="valuesExplanation[<?php echo $index; ?>][scoring][<?php echo $scoreIndex; ?>][description]" value="<?php echo htmlspecialchars($score['description'] ?? ''); ?>">
                                            <input type="hidden" name="valuesExplanation[<?php echo $index; ?>][scoring][<?php echo $scoreIndex; ?>][point]" value="<?php echo htmlspecialchars($score['point']); ?>">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="button" class="remove-btn" onclick="removeValuesExplanation(this)">删除</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="add-btn" onclick="addValuesExplanation()">添加价值观解说</button>
                </div>
                
                <!-- 战略目标 -->
                <div class="section tab-section" data-tab="strategic-objectives">
                    <h2>战略目标</h2>
                    <div id="strategic-objectives-container">
                        <?php foreach ($strategicObjectives as $year => $objectives): ?>
                            <div class="year-objectives">
                                <h3><?php echo htmlspecialchars($year); ?>年</h3>
                                <div class="objectives-list" data-year="<?php echo htmlspecialchars($year); ?>">
                                    <?php foreach ($objectives as $objIndex => $obj): ?>
                                        <div class="objective-item">
                                            <div class="form-row">
                                                <div class="form-group">
                                                    <label>部门</label>
                                                    <input type="text" name="strategicObjectives[<?php echo htmlspecialchars($year); ?>][<?php echo $objIndex; ?>][department]" value="<?php echo htmlspecialchars($obj['department'] ?? ''); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label>负责人 (PIC)</label>
                                                    <input type="text" name="strategicObjectives[<?php echo htmlspecialchars($year); ?>][<?php echo $objIndex; ?>][pic]" value="<?php echo htmlspecialchars($obj['pic'] ?? ''); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label>策略</label>
                                                <textarea name="strategicObjectives[<?php echo htmlspecialchars($year); ?>][<?php echo $objIndex; ?>][strategy]" rows="2"><?php echo htmlspecialchars($obj['strategy'] ?? ''); ?></textarea>
                                            </div>
                                            <div class="form-row">
                                                <div class="form-group">
                                                    <label>开始日期</label>
                                                    <input type="date" name="strategicObjectives[<?php echo htmlspecialchars($year); ?>][<?php echo $objIndex; ?>][startDate]" value="<?php echo htmlspecialchars($obj['startDate'] ?? ''); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label>结束日期</label>
                                                    <input type="date" name="strategicObjectives[<?php echo htmlspecialchars($year); ?>][<?php echo $objIndex; ?>][endDate]" value="<?php echo htmlspecialchars($obj['endDate'] ?? ''); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label>仪表板指标 (每行一个)</label>
                                                <textarea name="strategicObjectives[<?php echo htmlspecialchars($year); ?>][<?php echo $objIndex; ?>][dashboardMetrics]" rows="3"><?php echo htmlspecialchars(implode("\n", $obj['dashboardMetrics'] ?? [])); ?></textarea>
                                            </div>
                                            <button type="button" class="remove-btn" onclick="removeObjective(this, '<?php echo htmlspecialchars($year); ?>')">删除</button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="button" class="add-btn" onclick="addObjective('<?php echo htmlspecialchars($year); ?>')">添加目标</button>
                                <button type="button" class="remove-btn" onclick="removeYear('<?php echo htmlspecialchars($year); ?>')" style="margin-left: 10px;">删除年份</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="add-btn" onclick="addYear()">添加年份</button>
                </div>
                
                <!-- 固定操作按钮 -->
                <div class="fixed-actions">
                    <button type="submit" class="btn">保存更改</button>
                    <a href="corporate_blueprint.php" class="btn btn-secondary">返回查看</a>
                </div>
            </form>
        </div>
    </div>
    <script src="../js/corporate_blueprint_edit.js"></script>
</body>
</html>
