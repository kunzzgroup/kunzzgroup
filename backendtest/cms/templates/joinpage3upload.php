<!DOCTYPE html>
<?php $language = $isEnglish ? 'en' : 'zh'; ?>
<html lang="zh">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEnglish ? 'Job Positions Management' : '招聘职位管理'; ?> - KUNZZ HOLDINGS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/joinpage3upload.css">
</head>
<body>
    <?php include CORE_PATH . '/sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <h1><?php echo $isEnglish ? 'Job Positions Management' : '招聘职位管理'; ?></h1>
            <div class="language-switch">
                <a href="?lang=zh" class="btn <?php echo !$isEnglish ? 'active' : ''; ?>">中文</a>
                <a href="?lang=en" class="btn <?php echo $isEnglish ? 'active' : ''; ?>">English</a>
            </div>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard.php"><?php echo $isEnglish ? 'Dashboard' : '仪表板'; ?></a> > 
            <a href="media_manager.php"><?php echo $isEnglish ? 'Media Management' : '媒体管理'; ?></a> > 
            <span><?php echo $isEnglish ? 'Job Positions Management' : '招聘职位管理'; ?></span>
        </div>
        
        <div class="content">       
            <!-- 添加/编辑职位表单 -->
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
                                <option value="<?php echo $isEnglish ? 'Front Desk' : '前台'; ?>" <?php echo ($editJob && $editJob['company_department'] === ($isEnglish ? 'Front Desk' : '前台')) ? 'selected' : ''; ?>><?php echo $isEnglish ? 'Front Desk' : '前台'; ?></option>
                                <option value="<?php echo $isEnglish ? 'Kitchen' : '厨房'; ?>" <?php echo ($editJob && $editJob['company_department'] === ($isEnglish ? 'Kitchen' : '厨房')) ? 'selected' : ''; ?>><?php echo $isEnglish ? 'Kitchen' : '厨房'; ?></option>
                                <option value="sushi bar" <?php echo ($editJob && $editJob['company_department'] === 'sushi bar') ? 'selected' : ''; ?>>sushi bar</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="salary"><?php echo $isEnglish ? 'Salary Range' : '薪资范围'; ?> *</label>
                            <input type="text" id="salary" name="salary" 
                                   value="<?php echo $editJob ? htmlspecialchars($editJob['salary']) : ''; ?>" 
                                   placeholder="<?php echo $isEnglish ? 'e.g.: 3000-5000' : '例如：3000-5000'; ?>" 
                                   pattern="\d+-\d+" 
                                   title="<?php echo $isEnglish ? 'Please enter salary range' : '请输入薪资范围'; ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="company_location"><?php echo $isEnglish ? 'Company Address' : '公司地址'; ?></label>
                            <input type="text" id="company_location" name="company_location" 
                                   value="<?php echo $editJob ? htmlspecialchars($editJob['company_location']) : ''; ?>" 
                                   placeholder="25, Jln Tanjong 3, Taman Desa Cemerlang, 81800 Ulu Tiram, Johor">
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="job_description"><?php echo $isEnglish ? 'Job Description' : '职位详情'; ?> *</label>
                            <textarea id="job_description" name="job_description" 
                                      placeholder="<?php echo $isEnglish ? 'Please enter detailed job description...' : '请输入详细的职位描述...'; ?>" required><?php echo $editJob ? htmlspecialchars($editJob['job_description']) : ''; ?></textarea>
                        </div>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="submit" class="btn">
                            <?php echo $editJob ? ($isEnglish ? 'Update Job Position' : '更新职位') : ($isEnglish ? 'Add Job Position' : '添加职位'); ?>
                        </button>
                        <?php if ($editJob): ?>
                            <a href="joinpage3upload.php?lang=<?php echo $language; ?>" class="btn btn-secondary"><?php echo $isEnglish ? 'Cancel Edit' : '取消编辑'; ?></a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <!-- 现有职位列表 -->
            <div class="jobs-list">
                <h2><?php echo $isEnglish ? 'Current Job Positions' : '现有职位列表'; ?> (<?php echo count($jobs); ?>)</h2>
                
                <?php if (empty($jobs)): ?>
                    <p style="text-align: center; color: #999; padding: 40px;"><?php echo $isEnglish ? 'No job positions available' : '暂无职位信息'; ?></p>
                <?php else: ?>
                    <?php foreach ($jobs as $job): ?>
                        <div class="job-item">
                            <div class="job-header-item">
                                <div>
                                    <div class="job-title-item"><?php echo htmlspecialchars($job['job_title']); ?></div>
                                    <div class="job-meta-list">
                                        <span class="job-meta-item-list">👥 <?php echo $isEnglish ? 'Count:' : '人数'; ?> <?php echo htmlspecialchars($job['recruitment_count']); ?></span>
                                        <span class="job-meta-item-list">💼 <?php echo $isEnglish ? 'Experience:' : '经验'; ?> <?php echo htmlspecialchars($job['work_experience']); ?></span>
                                        <span class="job-meta-item-list">📅 <?php echo $isEnglish ? 'Published:' : '发布'; ?> <?php echo $job['publish_date']; ?></span>
                                        <span class="job-meta-item-list">🏷️ <?php echo $isEnglish ? 'Company:' : '公司'; ?> <?php echo htmlspecialchars($job['company_category'] ?? ($isEnglish ? 'Uncategorized' : '未分类')); ?></span>
                                        <?php if (!empty($job['company_department'])): ?>
                                        <span class="job-meta-item-list">🏢 <?php echo $isEnglish ? 'Department:' : '部门'; ?> <?php echo htmlspecialchars($job['company_department']); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($job['salary'])): ?>
                                        <span class="job-meta-item-list">💰 <?php echo $isEnglish ? 'Salary:' : '薪资'; ?> <?php echo htmlspecialchars($job['salary']); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($job['company_location'])): ?>
                                        <span class="job-meta-item-list">📍 <?php echo $isEnglish ? 'Address:' : '地址'; ?> <?php echo htmlspecialchars($job['company_location']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="job-description-preview">
                                        <strong><?php echo $isEnglish ? 'Job Description:' : '职位详情：'; ?></strong><?php echo htmlspecialchars($job['job_description']); ?>
                                    </div>
                                </div>
                                <div class="job-actions">
                                    <a href="?lang=<?php echo $language; ?>&edit=<?php echo $job['id']; ?>" class="action-btn edit-btn" title="<?php echo $isEnglish ? 'Edit' : '编辑'; ?>">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="post" style="display: inline-block;" 
                                          onsubmit="return confirm('<?php echo $isEnglish ? 'Are you sure you want to delete this job position?' : '确定要删除这个职位吗？'; ?>')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                        <button type="submit" class="action-btn delete-btn" title="<?php echo $isEnglish ? 'Delete' : '删除'; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 通知容器 -->
    <div class="toast-container" id="toast-container">
        <!-- 动态通知内容 -->
    </div>

    <script src="js/joinpage3upload.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($success)): ?>
                showAlert('<?php echo addslashes($success); ?>', 'success');
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                showAlert('<?php echo addslashes($error); ?>', 'error');
            <?php endif; ?>
        });
    </script>
</body>
</html>
