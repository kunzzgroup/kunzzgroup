<?php
if (!headers_sent()) {
    header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate");
    header("Pragma: no-cache");
    header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");
}
?>
<?php
require_once 'session_check.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <title>问卷回答 - KUNZZ HOLDINGS</title>
    <script src="https://cdn.jsdelivr.net/npm/pdf-lib/dist/pdf-lib.min.js"></script>
    <script src="get_fontkit.php"></script>
    <link rel="stylesheet" href="css/qna.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="container">
        <div class="header">
            <h1>问卷回答</h1>
            <div class="header-actions">
                <button type="button" class="btn-print-template" onclick="printTemplate()">
                    <i class="fas fa-print"></i>
                    打印问卷
                </button>
            </div>
        </div>
        
        <div id="messageArea"></div>
        
        <div class="qna-content-container">
            <div class="qna-content-wrapper">
                <!-- 编辑模式 -->
                <form id="qnaForm" class="edit-mode">
                <div class="form-section">
                    <div class="form-section-header">问题 1</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">如果不考虑现实限制,你希望自己在3-5年后成为什么样的人?</div>
                            <textarea class="question-input" name="question1" id="question1" required></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 2</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">你目前最重要的个人目标或梦想是什么?</div>
                            <div class="question-example">(例如:事业发展,专业技能,经济目标,生活稳定,家庭等)</div>
                            <textarea class="question-input" name="question2" id="question2" required></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 3</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">如果公司为你提供机会,你是否愿意承担更高的责任与压力?你认为这些责任具体体现在哪些方面?</div>
                            <div class="question-example">(例如:结果要求,学习投入,团队管理,时间管理,抗压能力等)</div>
                            <textarea class="question-input" name="question3" id="question3" required></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 4</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">在实现的目标过程中,你目前遇到最大的困难或挑战是什么?</div>
                            <div class="question-example">(可以是工作上的,也可以是个人层面的)</div>
                            <textarea class="question-input" name="question4" id="question4" required></textarea>
                        </div>
                    </div>
                </div>
                              <div class="form-section">
                    <div class="form-section-header">问题 4</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">在实现的目标过程中,你目前遇到最大的困难或挑战是什么?</div>
                            <div class="question-example">(可以是工作上的,也可以是个人层面的)</div>
                            <textarea class="question-input" name="question4.1  " id="question4.1" required></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 5</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">如果公司可以提供支持,你最希望公司在哪些方面给予帮助?</div>
                            <textarea class="question-input" name="question5" id="question5" required></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 6</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">在目前的公司中,有没有你特别希望尝试或发展的方向?为什么?</div>
                            <div class="question-example">(例如:管理,专业深度,跨部门,新项目等)</div>
                            <textarea class="question-input" name="question6" id="question6" required></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 7</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">你认为哪些能力或经验,是你未来1-2年最需要重点提升的?</div>
                            <textarea class="question-input" name="question7" id="question7" required></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 8</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">如果未来1年内，公司只能为你提供一项最有价值的支持，你希望是什么？</div>
                            <div class="question-example">(请写下你认为最重要的一项)</div>
                            <textarea class="question-input" name="question8" id="question8"></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 9</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">当你想到“理想的工作状态”时，请写下你最重视的3个关键词。</div>
                            <div class="question-example">(例如：成长，稳定，被尊重，有挑战，自由，有意义等)</div>
                            <textarea class="question-input" name="question9" id="question9"></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 10</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">你希望公司在“员工发展”这件事上，扮演什么角色？</div>
                            <div class="question-example">(例如：平台，导师，伙伴，资源提供者，稳定后盾等)</div>
                            <textarea class="question-input" name="question10" id="question10"></textarea>
                        </div>
                    </div>
                </div>

                </form>

                <!-- 查看模式 -->
                <div class="view-mode">
                <div class="form-section">
                    <div class="form-section-header">问题 1</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">如果不考虑现实限制,你希望自己在3-5年后成为什么样的人?</div>
                            <div class="view-answer" id="view-question1"><span class="empty-answer">未填写</span></div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 2</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">你目前最重要的个人目标或梦想是什么?</div>
                            <div class="view-answer" id="view-question2"><span class="empty-answer">未填写</span></div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 3</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">如果公司为你提供机会,你是否愿意承担更高的责任与压力?你认为这些责任具体体现在哪些方面?</div>
                            <div class="view-answer" id="view-question3"><span class="empty-answer">未填写</span></div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 4</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">在实现的目标过程中,你目前遇到最大的困难或挑战是什么?</div>
                            <div class="view-answer" id="view-question4"><span class="empty-answer">未填写</span></div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 5</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">如果公司可以提供支持,你最希望公司在哪些方面给予帮助?</div>
                            <div class="view-answer" id="view-question5"><span class="empty-answer">未填写</span></div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 6</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">在目前的公司中,有没有你特别希望尝试或发展的方向?为什么?</div>
                            <div class="view-answer" id="view-question6"><span class="empty-answer">未填写</span></div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 7</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">你认为哪些能力或经验,是你未来1-2年最需要重点提升的?</div>
                            <div class="view-answer" id="view-question7"><span class="empty-answer">未填写</span></div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 8</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">如果未来1年内，公司只能为你提供一项最有价值的支持，你希望是什么？</div>
                            <div class="view-answer" id="view-question8"><span class="empty-answer">未填写</span></div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 9</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">当你想到"理想的工作状态"时，请写下你最重视的3个关键词。</div>
                            <div class="view-answer" id="view-question9"><span class="empty-answer">未填写</span></div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-header">问题 10</div>
                    <div class="form-section-content">
                        <div class="question-item">
                            <div class="question-text">你希望公司在"员工发展"这件事上，扮演什么角色？</div>
                            <div class="view-answer" id="view-question10"><span class="empty-answer">未填写</span></div>
                        </div>
                    </div>
                </div>

                </div>
            </div>
        </div>
        
        <!-- <div class="button-group" id="buttonGroup">
            <button type="button" class="btn btn-reset" onclick="resetForm()" id="resetBtn" style="display: none;">重新回答</button>
            <button type="submit" class="btn" id="submitBtn" form="qnaForm" style="display: none;">提交问卷</button>
            <button type="button" class="btn" onclick="generatePDF()" id="printBtn" style="display: none;">打印问卷</button>
        </div> -->
    </div>

    
    <script src="js/qna.js?v=<?php echo time(); ?>"></script>
</body>
</html>

