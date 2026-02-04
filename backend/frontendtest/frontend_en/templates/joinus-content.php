<div class="swiper">
  <div class="swiper-wrapper">

  <div class="swiper-slide">
  <section class="joinus-section">
    <!-- 上半部分：加入我们 -->
    <div class="joinus-banner">
        <?php echo getMediaHtml('joinus_background', ['style' => 'width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0; z-index: -1;']); ?>
        <div class="joinus-content">
            <h1>Join Us</h1>
            <p>Here, your effort shapes more than results — you help build the brand and grow alongside it.</p>
        </div>
    </div>

    <!-- 下半部分：员工福利 -->
    <div class="benefits-wrapper" id="benefits">
      <h2>Benefits</h2>
      <div class="benefits-grid">
        <div class="benefit-item">
          <img src="../../images/images/带薪假期.png" alt="带薪假期">
          <p>Annual Leave</p>
        </div>
        <div class="benefit-item">
          <img src="../../images/images/旅游奖励.png" alt="旅游奖励">
          <p>Travel Incentive</p>
        </div>
        <div class="benefit-item">
          <img src="../../images/images/汽车奖励.png" alt="汽车奖励">
          <p>Car Allowance</p>
        </div>
        <div class="benefit-item">
          <img src="../../images/images/房子奖励.png" alt="房子奖励">
          <p>Housing Allowance</p>
        </div>
        <div class="benefit-item">
          <img src="../../images/images/年度绩效奖励.png" alt="年度绩效奖励">
          <p>Annual Bonus</p>
        </div>
        <div class="benefit-item">
          <img src="../../images/images/专业培训与学习机会.png" alt="专业培训与学习机会">
          <p>Training & Learning</p>
        </div>
      </div>
    </div>
  </section>
</div>

<div class="swiper-slide">
    <div class="comphoto-section" id="comphoto-container">
        <div class="comphoto-title">Our Journey</div>
    </div>
        <div id="comphoto-modal" class="comphoto-modal">
            <span class="comphoto-close">&times;</span>
            <div class="comphoto-modal-content">
                <img id="comphoto-modal-img" src="" alt="Enlarged photo">
            </div>
        </div>
    </div>

<div class="swiper-slide">

    <div class="job-section">
        <div class="job-table-container">
            <h2 class="job-table-title">Career Opportunities</h2>
        </div>
    <div class ="jobs-wrapper">    
        <div class="jobs-container">
            <?php echo getJobsHtml('en'); ?>
        </div>
    </div>    
</div>

    <!-- 职位详情弹窗 -->
    <div id="jobDetailModal" class="modal">
        <div class="job-detail-modal">
            <span class="close-btn" onclick="closeJobDetail()">&times;</span>
            <div class="job-detail-content">
                <h2 id="jobDetailTitle">Position Details</h2>
                <div class="job-detail-meta">
                    <div class="job-detail-item">
                        <span class="job-detail-label">Number of positions:</span>
                        <span id="jobDetailCount">-</span>
                    </div>
                    <div class="job-detail-item">
                        <span class="job-detail-label">Years of experience:</span>
                        <span id="jobDetailExperience">-</span>
                        <span class="job-detail-label"> Years</span>
                    </div>
                    <div class="job-detail-item">
                        <span class="job-detail-label">Posted:</span>
                        <span id="jobDetailPublishDate">-</span>
                    </div>
                    <div class="job-detail-item">
                        <span class="job-detail-label">Company:</span>
                        <span id="jobDetailCompany">-</span>
                    </div>
                    <div class="job-detail-item" id="jobDetailDepartment" style="display: none;">
                        <span class="job-detail-label">Department:</span>
                        <span id="jobDetailDepartmentValue">-</span>
                    </div>
                    <div class="job-detail-item" id="jobDetailSalary" style="display: none;">
                        <span class="job-detail-label">Salary:</span>
                        <span id="jobDetailSalaryValue">-</span>
                    </div>
                </div>
                <div class="job-detail-description">
                    <h3>Position Details: </h3>
                    <p id="jobDetailDescription">-</p>
                </div>
                <div class="job-detail-address">
                    <h3>Work location: </h3>
                    <p id="jobDetailAddress">-</p>
                </div>
                <div class="apply-btn-container">
                    <button class="apply-btn" onclick="openFormFromDetail()">Apply</button>
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
                <input type="hidden" name="redirect" value="https://kunzzgroup.com/frontend_en/success.html">
                <h2>Apply</h2>
                <label>Position Title: </label>
                <input type="text" id="formPosition" name="position" readonly>
                
                <!-- 中文姓名和性别在同一行 -->
                <div class="job-form-row">
                    <div class="job-half-width">
                        <label>Chinese Name: </label>
                        <input type="text" name="chinese_name" required pattern="[\u4e00-\u9fa5]{2,}" title="Please enter your Chinese name (at least two characters)">
                    </div>
                    <div class="job-half-width">
                        <label>Gender: </label>
                        <select name="gender" required>
                            <option value="">Please select</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Others</option>
                        </select>
                    </div>
                </div>
                
                <label>English Name: </label>
                <input type="text" name="english_name" required pattern="[A-Za-z ]{2,}" title="Please enter your English name (letters only)">
                <label>Email: </label>
                <input type="email" name="email" required>
                <label>Phone Number: </label>
                <div class="job-phone-group">
                    <select name="country_code" required>
                        <option value="+60">MY (+60)</option>
                        <option value="+65">SG (+65)</option>
                        <option value="+86">CN (+86)</option>
                        <option value="+852">HK (+852)</option>
                        <option value="+81">JP (+81)</option>
                    </select>
                    <input type="tel" name="phone" required pattern="\d{1,10}" maxlength="10" title="Please enter a phone number with up to 10 digits.">
                </div>
                <label>Upload Resume (PDF, ≤3MB):</label>
                <input type="file" name="resume" id="resume" accept=".pdf" required>
                <button type="submit" class="job-submit-btn">Submit</button>
            </form>
        </div>
    </div>
  </div>    

  <!-- 意见表格 -->
  <div class="swiper-slide">
  <div class="form-wrapper">
  <h2 class="main-title">Kindly Provide Your Feedback</h2>
  <section class="join-us-form"> 
    <form id="jobApplicationForm" action="https://api.web3forms.com/submit" method="POST" enctype="multipart/form-data">

      <!-- 中文姓名 + 性别 -->
      <div class="form-group-row">
        <div class="half-width">
          <label for="chineseName">Chinese Name*</label>
          <input type="hidden" name="access_key" value="a18bc4c6-2f16-4861-8d10-a3de747cab50">
          <input type="hidden" name="redirect" value="https://kunzzgroup.com/frontend_en/success.html">
          <input type="text" id="chineseName" name="chineseName" placeholder="Please enter your Chinese name" required pattern="[\u4e00-\u9fa5]{2,}" title="Please enter your Chinese name (at least two characters)">
        </div>

        <div class="half-width">
          <label>Gender*</label>
          <div class="gender-options">
            <label><input type="radio" name="gender" value="male" required> Male</label>
            <label><input type="radio" name="gender" value="female" required> Female</label>
          </div>
        </div>
      </div>

      <!-- 英文姓名 + 职位类别 -->
      <div class="form-group-row">
        <div class="half-width">
          <label for="englishName">English Name*</label>
          <input type="text" id="englishName" name="englishName" placeholder="Please enter your English name" required pattern="[A-Za-z ]{2,}" title="Please enter your English name (letters only)">
        </div>
      </div>

      <!-- 手机号码 -->
      <div class="form-group">
        <label for="phone">Phone Number*</label>
        <div class="phone-input">
          <select id="countryCode" name="countryCode" required>
            <option value="+60">MY (+60)</option>
            <option value="+65">SG (+65)</option>
            <option value="+86">CN (+86)</option>
            <option value="+852">HK (+852)</option>
            <option value="+81">JP (+81)</option>
            <!-- 可以加更多国家 -->
          </select>
          <input type="tel" id="phoneNumber" name="phoneNumber" placeholder="Please enter your Phone Number" required pattern="\d{1,10}" maxlength="10" inputmode="numeric" title="Please enter a phone number">
        </div>
      </div>

      <!-- 电子邮箱 -->
      <div class="form-group">
        <label for="email">Email*</label>
        <input type="email" id="email" name="email" placeholder="Please enter your Email" required pattern="^[a-zA-Z0-9._%+-]+@gmail\.com$" title="Please enter your Email">
      </div>

      <!-- 信息 -->
      <div class="form-group">
        <label for="message">Information*</label>
        <textarea id="message" name="message" rows="5" required></textarea>
      </div>

      <!-- 提交按钮 -->
      <div class="form-group">
        <button type="submit" class="submit-btn">Submit</button>
      </div>
    </form>
</section>
</div>
</div>  

<div class="swiper-slide">
  <div class="contact-section-wrapper" id="map">
  <section class="contact-container">
  <div class="contact-info">
    <h2>Contact Us</h2>
    <p>Company Name：Kunzz Holdings Sdn. Bhd.</p>
    <p>
      Address：
      <a href="javascript:void(0);" onclick="goToLocation()" class="no-style-link">
        25, Jln Tanjong 3, Taman Desa Cemerlang, 81800 Ulu Tiram, Johor Darul Ta'zim
      </a>
    </p>
    <p>Phone Number：+60 13-553 5355</p>
    <p>Email Address：kunzzholdings@gmail.com</p>
    <p>Business Hours：Monday to Friday 9AM-6PM</p>
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

<?php include '../public_en/footer.php'; ?>

  </div>
</div>
