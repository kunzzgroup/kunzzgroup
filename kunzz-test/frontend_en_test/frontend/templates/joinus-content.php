<div class="swiper">
  <div class="swiper-wrapper">

  <div class="swiper-slide">
  <section class="joinus-section">
    <!-- 上半部分：加入我们 -->
    <div class="joinus-banner">
        <?php echo getMediaHtml('joinus_background', ['style' => 'width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0; z-index: -1;']); ?>
        <div class="joinus-content">
            <h1>加入我们</h1>
            <p>在这里，你的努力不止换来薪资，更参与到品牌建设的每一步，一起迈向更大的舞台。</p>
        </div>
    </div>

    <!-- 下半部分：员工福利 -->
    <div class="benefits-wrapper" id="benefits">
      <h2>公司福利</h2>
      <div class="benefits-grid">
        <div class="benefit-item">
          <img src="../../images/images/带薪假期.png" alt="带薪假期">
          <p>带薪假期</p>
        </div>
        <div class="benefit-item">
          <img src="../../images/images/旅游奖励.png" alt="旅游奖励">
          <p>旅游奖励</p>
        </div>
        <div class="benefit-item">
          <img src="../../images/images/汽车奖励.png" alt="汽车奖励">
          <p>汽车奖励</p>
        </div>
        <div class="benefit-item">
          <img src="../../images/images/房子奖励.png" alt="房子奖励">
          <p>房子奖励</p>
        </div>
        <div class="benefit-item">
          <img src="../../images/images/年度绩效奖励.png" alt="年度绩效奖励">
          <p>年度绩效奖励</p>
        </div>
        <div class="benefit-item">
          <img src="../../images/images/专业培训与学习机会.png" alt="专业培训与学习机会">
          <p>专业培训与学习机会</p>
        </div>
      </div>
    </div>
  </section>
</div>

<div class="swiper-slide">
    <div class="comphoto-section" id="comphoto-container">
        <!-- 粒子容器 -->
        <div id="particles"></div>
        <div class="comphoto-title">我们的足迹</div>
    </div>
        <div id="comphoto-modal" class="comphoto-modal">
            <span class="comphoto-close">&times;</span>
            <div class="comphoto-modal-content">
                <img id="comphoto-modal-img" src="" alt="放大的照片">
            </div>
        </div>
    </div>

<div class="swiper-slide">

    <div class="job-section">
        <div class="job-table-container">
            <h2 class="job-table-title">目前在招聘的职位</h2>
        </div>
    <div class ="jobs-wrapper">    
        <div class="jobs-container">
            <?php echo getJobsHtml('zh'); ?>
        </div>
    </div>    
</div>

    <!-- 职位详情弹窗 -->
    <div id="jobDetailModal" class="modal">
        <div class="job-detail-modal">
            <span class="close-btn" onclick="closeJobDetail()">&times;</span>
            <div class="job-detail-content">
                <h2 id="jobDetailTitle">职位详情</h2>
                <div class="job-detail-meta">
                    <div class="job-detail-item">
                        <span class="job-detail-label">人数:</span>
                        <span id="jobDetailCount">-</span>
                    </div>
                    <div class="job-detail-item">
                        <span class="job-detail-label">工作经验:</span>
                        <span id="jobDetailExperience">-</span>
                        <span class="job-detail-label"> 年</span>
                    </div>
                    <div class="job-detail-item">
                        <span class="job-detail-label">发布:</span>
                        <span id="jobDetailPublishDate">-</span>
                    </div>
                    <div class="job-detail-item">
                        <span class="job-detail-label">公司:</span>
                        <span id="jobDetailCompany">-</span>
                    </div>
                    <div class="job-detail-item" id="jobDetailDepartment" style="display: none;">
                        <span class="job-detail-label">部门:</span>
                        <span id="jobDetailDepartmentValue">-</span>
                    </div>
                    <div class="job-detail-item" id="jobDetailSalary" style="display: none;">
                        <span class="job-detail-label">薪资:</span>
                        <span id="jobDetailSalaryValue">-</span>
                    </div>
                </div>
                <div class="job-detail-description">
                    <h3>职位详情：</h3>
                    <p id="jobDetailDescription">-</p>
                </div>
                <div class="job-detail-address">
                    <h3>工作地址：</h3>
                    <p id="jobDetailAddress">-</p>
                </div>
                <div class="apply-btn-container">
                    <button class="apply-btn" onclick="openFormFromDetail()">申请职位</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 弹窗表单 -->
    <div id="formModal" class="modal">
        <div class="job-modal-content">
            <span class="close-btn" onclick="closeForm()">&times;</span>
            <form class="job-form" id="jobApplicationForm" action="https://api.web3forms.com/submit" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="access_key" value="a18bc4c6-2f16-4861-8d10-a3de747cab50">
                <input type="hidden" name="redirect" value="https://kunzzgroup.com/frontend/success.html">
                <h2>申请职位</h2>
                <label>职位名称：</label>
                <input type="text" id="formPosition" name="position" readonly>
                
                <!-- 中文姓名和性别在同一行 -->
                <div class="job-form-row">
                    <div class="job-half-width">
                        <label>中文姓名：</label>
                        <input type="text" name="chinese_name" required pattern="[\u4e00-\u9fa5]{2,}" title="请输入中文姓名（至少两个汉字）">
                    </div>
                    <div class="job-half-width">
                        <label>性别：</label>
                        <select name="gender" required>
                            <option value="">请选择</option>
                            <option value="male">男</option>
                            <option value="female">女</option>
                            <option value="other">其他</option>
                        </select>
                    </div>
                </div>
                
                <label>英文姓名：</label>
                <input type="text" name="english_name" required pattern="[A-Za-z ]{2,}" title="请输入英文姓名（只限英文字母）">
                <label>电子邮箱：</label>
                <input type="email" name="email" required>
                <label>电话号码：</label>
                <div class="job-phone-group">
                    <select name="country_code" required>
                        <option value="+60">马来西亚 (+60)</option>
                        <option value="+65">新加坡 (+65)</option>
                        <option value="+86">中国 (+86)</option>
                        <option value="+852">香港 (+852)</option>
                        <option value="+81">日本 (+81)</option>
                    </select>
                    <input type="tel" name="phone" required pattern="\d{1,10}" maxlength="10" title="请输入最多10位数字的电话号码">
                </div>
                <label>上传简历（PDF，≤3MB）：</label>
                <input type="file" name="resume" id="resume" accept=".pdf" required>
                <button type="submit" class="job-submit-btn">提交申请</button>
            </form>
        </div>
    </div>
  </div>    

  <!-- 意见表格 -->
  <div class="swiper-slide">
  <div class="form-wrapper">
  <h2 class="main-title">请提供您宝贵的意见</h2>
  <section class="join-us-form"> 
    <form id="feedbackForm" action="https://api.web3forms.com/submit" method="POST" enctype="multipart/form-data">

      <!-- 中文姓名 + 性别 -->
      <div class="form-group-row">
        <div class="half-width">
          <label for="chineseName">中文姓名*</label>
          <input type="hidden" name="access_key" value="a18bc4c6-2f16-4861-8d10-a3de747cab50">
          <input type="hidden" name="redirect" value="https://kunzzgroup.com/frontend/success.html">
          <input type="text" id="chineseName" name="chineseName" placeholder="请输入中文姓名" required pattern="[\u4e00-\u9fa5]{2,}" title="请输入中文姓名（至少两个汉字）">
        </div>

        <div class="half-width">
          <label>性别*</label>
          <div class="gender-options">
            <label><input type="radio" name="gender" value="male" required> 男</label>
            <label><input type="radio" name="gender" value="female" required> 女</label>
          </div>
        </div>
      </div>

      <!-- 英文姓名 + 职位类别 -->
      <div class="form-group-row">
        <div class="half-width">
          <label for="englishName">英文姓名*</label>
          <input type="text" id="englishName" name="englishName" placeholder="请输入英文姓名" required pattern="[A-Za-z ]{2,}" title="请输入英文姓名（只限字母）">
        </div>
      </div>

      <!-- 手机号码 -->
      <div class="form-group">
        <label for="phone">手机号码*</label>
        <div class="phone-input">
          <select id="countryCode" name="countryCode" required>
            <option value="+60">马来西亚 (+60)</option>
            <option value="+65">新加坡 (+65)</option>
            <option value="+86">中国 (+86)</option>
            <option value="+852">香港 (+852)</option>
            <option value="+81">日本 (+81)</option>
            <!-- 可以加更多国家 -->
          </select>
          <input type="tel" id="phoneNumber" name="phoneNumber" placeholder="请输入电话号码" required pattern="\d{1,10}" maxlength="10" inputmode="numeric" title="请输入正确手机号">
        </div>
      </div>

      <!-- 电子邮箱 -->
      <div class="form-group">
        <label for="email">电子邮箱*</label>
        <input type="email" id="email" name="email" placeholder="请输入邮箱地址" required pattern="^[a-zA-Z0-9._%+-]+@gmail\.com$" title="请输入正确邮箱地址">
      </div>

      <!-- 信息 -->
      <div class="form-group">
        <label for="message">信息*</label>
        <textarea id="message" name="message" rows="5" required></textarea>
      </div>

      <!-- 提交按钮 -->
      <div class="form-group">
        <button type="submit" class="submit-btn">提交</button>
      </div>
    </form>
</section>
</div>
</div>  

<div class="swiper-slide">
  <div class="contact-section-wrapper" id="map">
  <section class="contact-container">
  <div class="contact-info">
    <h2>联系我们</h2>
    <p>公司名称：Kunzz Holdings Sdn. Bhd.</p>
    <p>
      地址：
      <a href="javascript:void(0);" onclick="goToLocation()" class="no-style-link">
        25, Jln Tanjong 3, Taman Desa Cemerlang, 81800 Ulu Tiram, Johor Darul Ta'zim
      </a>
    </p>
    <p>电话：+60 13-553 5355</p>
    <p>邮箱：kunzzholdings@gmail.com</p>
    <p>营业时间：周一至周五 9AM-6PM</p>
  </div>

  <div class="map-container">
    <iframe
      id="custom-map"
      src="https://www.google.com/maps/d/embed?mid=1WGUSQUviVSNKcc7LNK-aSDA6j6S3EMc&ehbc=2E312F"
      width="640"
      height="480"
    ></iframe>
  </div>
</section>
</div>
</div>

<?php include '../public/footer.php'; ?>

  </div>
</div>
