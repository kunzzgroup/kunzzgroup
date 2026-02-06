<!DOCTYPE html>
<html lang="zh">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEnglish ? 'Job Positions Management' : '招聘职位管理'; ?> - KUNZZ HOLDINGS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/joinpage3upload.css">
</head>
<body class="has-sidebar">
    <?php $basePath = '../'; include '../pages/sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <h1><?php echo $isEnglish ? 'Job Positions Management' : '招聘职位管理'; ?></h1>
            <div class="language-switch">
                <a href="?lang=zh" class="btn <?php echo !$isEnglish ? 'active' : ''; ?>">中文</a>
                <a href="?lang=en" class="btn <?php echo $isEnglish ? 'active' : ''; ?>">English</a>
            </div>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard"><?php echo $isEnglish ? 'Dashboard' : '仪表板'; ?></a> > 
            <a href="media_manager"><?php echo $isEnglish ? 'Media Management' : '媒体管理'; ?></a> > 
            <span><?php echo $isEnglish ? 'Job Positions Management' : '招聘职位管理'; ?></span>
        </div>
        
        <div class="content">       
            <?php if (isset($success) && $success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if (isset($error) && $error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="form-section">
                <h2><?php echo $editJob ? ($isEnglish ? 'Edit Job Position' : '编辑职位') : ($isEnglish ? 'Add New Job Position' : '添加新职位'); ?></h2>
                <form method="post">
                    <input type="hidden" name="action" value="<?php echo $editJob ? 'edit' : 'add'; ?>">
                    <?php if ($editJob): ?>
                        <input type="hidden" name="job_id" value="<?php echo $editJob['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="job_title"><?php echo $isEnglish ? 'Job Title' : '职位名称'; ?> *</label>
                            <input type="text" id="job_title" name="job_title" 
                                   value="<?php echo $editJob ? htmlspecialchars($editJob['job_title']) : ''; ?>" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="job_count"><?php echo $isEnglish ? 'Recruitment Count' : '招聘人数'; ?> *</label>
                            <input type="text" id="job_count" name="job_count" 
                                   value="<?php echo $editJob ? htmlspecialchars($editJob['recruitment_count']) : ''; ?>" 
                                   placeholder="<?php echo $isEnglish ? 'e.g.: 1 person' : '例如：1人'; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="job_experience"><?php echo $isEnglish ? 'Work Experience Required' : '工作经验要求'; ?> *</label>
                            <input type="text" id="job_experience" name="job_experience" 
                                   value="<?php echo $editJob ? htmlspecialchars($editJob['work_experience']) : ''; ?>" 
                                   placeholder="<?php echo $isEnglish ? 'e.g.: 3' : '例如：3'; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="publish_date"><?php echo $isEnglish ? 'Publish Date' : '发布日期'; ?> *</label>
                            <input type="date" id="publish_date" name="publish_date" 
                                   value="<?php echo $editJob ? $editJob['publish_date'] : date('Y-m-d'); ?>" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="job_category"><?php echo $isEnglish ? 'Company Category' : '公司分类'; ?> *</label>
                            <select id="job_category" name="job_category" required onchange="toggleDepartmentField()">
                                <option value=""><?php echo $isEnglish ? 'Please select company' : '请选择公司'; ?></option>
                                <option value="KUNZZ HOLDINGS" <?php echo ($editJob && $editJob['company_category'] === 'KUNZZ HOLDINGS') ? 'selected' : ''; ?>>KUNZZ HOLDINGS</option>
                                <option value="TOKYO JAPANESE CUISINE" <?php echo ($editJob && $editJob['company_category'] === 'TOKYO JAPANESE CUISINE') ? 'selected' : ''; ?>>TOKYO JAPANESE CUISINE</option>
                                <option value="TOKYO IZAKAYA" <?php echo ($editJob && $editJob['company_category'] === 'TOKYO IZAKAYA') ? 'selected' : ''; ?>>TOKYO IZAKAYA</option>
                            </select>
                        </div>

                        <div class="form-group" id="department-group" style="display: none;">
                            <label for="company_department"><?php echo $isEnglish ? 'Department' : '部门'; ?> *</label>
                            <select id="company_department" name="company_department">
                                <option value=""><?php echo $isEnglish ? 'Please select department' : '请选择部门'; ?></option>
                                <option value="Kitchen" <?php echo ($editJob && $editJob['company_department'] === 'Kitchen') ? 'selected' : ''; ?>>Kitchen</option>
                                <option value="Hall" <?php echo ($editJob && $editJob['company_department'] === 'Hall') ? 'selected' : ''; ?>>Hall</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="salary"><?php echo $isEnglish ? 'Salary' : '薪资待遇'; ?></label>
                            <input type="text" id="salary" name="salary" 
                                   value="<?php echo $editJob ? htmlspecialchars($editJob['salary']) : ''; ?>" 
                                   placeholder="<?php echo $isEnglish ? 'e.g.: Negotiable' : '例如：面议'; ?>">
                        </div>

                        <div class="form-group">
                            <label for="company_location"><?php echo $isEnglish ? 'Job Location' : '工作地点'; ?></label>
                            <input type="text" id="company_location" name="company_location" 
                                   value="<?php echo $editJob ? htmlspecialchars($editJob['company_location']) : ''; ?>" 
                                   placeholder="<?php echo $isEnglish ? 'e.g.: Kuala Lumpur' : '例如：吉隆坡'; ?>">
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="job_description"><?php echo $isEnglish ? 'Job Description' : '职位描述/要求'; ?> *</label>
                            <textarea id="job_description" name="job_description" required><?php echo $editJob ? htmlspecialchars($editJob['job_description']) : ''; ?></textarea>
                        </div>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="submit" class="btn"><?php echo $editJob ? ($isEnglish ? 'Update Position' : '更新职位') : ($isEnglish ? 'Add Position' : '提交发布'); ?></button>
                        <?php if ($editJob): ?>
                            <a href="joinpage3upload?lang=<?php echo $language; ?>" class="btn btn-secondary"><?php echo $isEnglish ? 'Cancel' : '取消编辑'; ?></a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <div class="jobs-list">
                <h2><?php echo $isEnglish ? 'Current Job Positions' : '已有职位列表'; ?> (<?php echo count($jobs); ?>)</h2>
                
                <?php if (empty($jobs)): ?>
                    <p style="text-align: center; color: #666; padding: 20px;">
                        <?php echo $isEnglish ? 'No job positions found.' : '暂无招聘职位。'; ?>
                    </p>
                <?php else: ?>
                    <?php foreach ($jobs as $job): ?>
                        <div class="job-item">
                            <div class="job-header-item">
                                <div>
                                    <div class="job-title-item"><?php echo htmlspecialchars($job['job_title']); ?></div>
                                    <div class="job-meta-list">
                                        <span class="job-meta-item-list"><i class="fas fa-building"></i> <?php echo htmlspecialchars($job['company_category']); ?></span>
                                        <?php if ($job['company_department']): ?>
                                            <span class="job-meta-item-list"><i class="fas fa-users"></i> <?php echo htmlspecialchars($job['company_department']); ?></span>
                                        <?php endif; ?>
                                        <span class="job-meta-item-list"><i class="fas fa-user-plus"></i> <?php echo htmlspecialchars($job['recruitment_count']); ?></span>
                                        <span class="job-meta-item-list"><i class="fas fa-calendar-alt"></i> <?php echo $job['publish_date']; ?></span>
                                    </div>
                                    <div class="job-meta-list" style="margin-top: 5px;">
                                        <span class="job-meta-item-list"><i class="fas fa-money-bill-wave"></i> <?php echo htmlspecialchars($job['salary'] ?: ($isEnglish ? 'Not specified' : '未注明')); ?></span>
                                        <span class="job-meta-item-list"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['company_location'] ?: ($isEnglish ? 'Not specified' : '未注明')); ?></span>
                                        <span class="job-meta-item-list"><i class="fas fa-briefcase"></i> <?php echo htmlspecialchars($job['work_experience']); ?></span>
                                    </div>
                                </div>
                                <div class="job-actions">
                                    <a href="?lang=<?php echo $language; ?>&edit=<?php echo $job['id']; ?>" class="action-btn edit-btn" title="<?php echo $isEnglish ? 'Edit' : '编辑'; ?>">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('<?php echo $isEnglish ? 'Are you sure you want to delete this position?' : '确定要删除这个职位吗？'; ?>')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                        <button type="submit" class="action-btn delete-btn" title="<?php echo $isEnglish ? 'Delete' : '删除'; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="job-description-preview" style="white-space: pre-wrap;"><?php echo htmlspecialchars($job['job_description']); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Toast 通知容器 -->
    <div id="toast-container" class="toast-container"></div>
    
    <script src="../js/joinpage3upload.js"></script>
</body>
</html>
