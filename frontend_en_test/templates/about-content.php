<div class="swiper">
  <div class="swiper-wrapper">

  <div class="swiper-slide">
    <section class="aboutus-section">
    <div class="aboutus-banner">
        <?php echo getMediaHtml('about_background'); ?>
      <div class="aboutus-content">
        <h1>About Us</h1>
        <p>Explore Kunzz Holdings’ Vision and Growth Journey</p>
      </div>
    </div>

    <div class="aboutus-intro">
      <div class="intro-content">
        <h1>Group Profile</h1>
        <p>
          Kunzz Holdings is a diversified Malaysian group dedicated to resource integration and efficiency.<br>
          We offer strategic guidance and operational synergy to all our subsidiaries.<br>
          We build brands. We empower teams. We create impact.
        </p>
      </div>
    </div>
</section>
    </div>
  
    <div class="swiper-slide">
    <section id="vision" class="vision">
    <div class="vision-content animate-on-scroll vision-slide-down">
      <h1>Our Beliefs and Direction</h1>
      <p>
        We believe that every great achievement begins with a clear belief.<br>
        Our mission, vision, culture, and values are both the guiding light and the bottom line we all uphold.<br>
        With these principles in mind, we continue to grow, to break through, and to lift each other higher.
      </p>

      <div class="vision-cards">
        <!-- Card 1 -->
        <div class="vision-card animate-on-scroll slide-in-left">
          <div class="vision-label">Our Mission</div>
          <h2>Creating a positive and comfortable working environment</h2>
          <p>
            Here, we believe that a positive work environment nurtures stronger teams. 
            We strive to create a warm and welcoming space where everyone feels a true sense of belonging — 
            a place where each member can feel safe to give their best and grow together. In such an environment, 
            challenges no longer feel cold, and every effort is seen, valued, and appreciated.
          </p>
        </div>

        <!-- Card 2 -->
        <div class="vision-card animate-on-scroll slide-in-right">
          <div class="vision-label">Our Vision</div>
          <h2>Build an efficient team, create the future of the industry</h2>
          <p>
            A great team is the source of continuous value creation for any enterprise. 
            Only when efficiency and innovation go hand in hand can a team break boundaries and achieve greatness. 
            With steady steps, we are on the path to becoming an industry benchmark — letting achievements speak and moving forward with belief.
          </p>
        </div>
      </div>
    </div>
  </section>
  </div>

  <div class="swiper-slide">
  <section id="values" class="values-section">
        <div class="values-top animate-on-scroll">
            <h2 class="values-title animate-on-scroll values-scale-fade delay-3">Our Core <span style="color: #FF5C00;">Values</span></h2>
            <p class="values-description animate-on-scroll values-scale-fade delay-4">
                Our core values are present in every effort and every act of collaboration. 
                They unite us in culture, strengthen our belief through challenges, 
                and keep our original purpose steady as we grow.
            </p>
        </div>
      
        <div class="values-bottom animate-on-scroll card-tilt-in-left">
            <div class="values-card">
                <img src="../images/images/目标导向.png" alt="icon" class="values-icon">
                <h3>Goal-Oriented</h3>
                <p>Result-oriented, focused on key tasks, with clear direction and purpose at every step.</p>
            </div>
            <div class="values-card">
                <img src="../images/images/理念一致.png" alt="icon" class="values-icon">
                <h3>Aligned Thinking</h3>
                <p>Maintain strong consensus, stay mentally in sync, align on goals, and reduce internal friction.</p>
            </div>
            <div class="values-card">
                <img src="../images/images/追求卓越.png" alt="icon" class="values-icon">
                <h3>Seek Excellence</h3>
                <p>Not just completing tasks — but doing them better, aiming higher, and improving continuously.</p>
            </div>
            <div class="values-card">
                <img src="../images/images/创新精神.png" alt="icon" class="values-icon">
                <h3>Creativity</h3>
                <p>Embrace change, dare to try, break limits, and keep exploring new ways to grow.</p>
            </div>
        </div>
    </section>
  </div>

  <div class="swiper-slide">
  <section class="timeline-section" id="timeline-1">
        <h1>— Milestones —</h1>
        
        <!-- 横向时间线导航 -->
        <div class="timeline-nav">
            <div class="nav-arrow prev" onclick="navigateTimeline('prev')">‹</div>
            <div class="nav-arrow next" onclick="navigateTimeline('next')">›</div>
            
            <div class="timeline-scroll-container">
                <div class="timeline-track"></div>
                <div class="timeline-items-container" id="timelineContainer">
                    <?php 
                    $index = 0;
                    foreach ($timelineItems as $item): 
                        $year = $item['year'];
                    ?>
                    <div class="timeline-item <?php echo $index === 0 ? 'active' : ''; ?>" data-year="<?php echo htmlspecialchars($year); ?>">
                        <div class="timeline-bullet"><?php echo htmlspecialchars($year); ?></div>
                    </div>
                    <?php 
                    $index++;
                    endforeach; 
                    ?>
                </div>
            </div>
        </div>

        <!-- 卡片容器 -->
        <div class="timeline-content-container">
            <div class="timeline-cards-wrapper">
                <?php 
                $index = 0;
                foreach ($timelineItems as $item): 
                    $year = $item['year'];
                    $itemClass = $index === 0 ? 'active' : ($index === 1 ? 'next' : 'hidden');
                ?>
                <!-- <?php echo htmlspecialchars($year); ?>年内容 -->
                <div class="timeline-content-item <?php echo $itemClass; ?>" data-year="<?php echo htmlspecialchars($year); ?>" data-index="<?php echo $index; ?>">
                    <div class="timeline-content" onclick="selectCardIndex(<?php echo (int)$index; ?>)">
                        <div class="timeline-image">
                            <img src="<?php echo $item['image_url']; ?>" alt="<?php echo htmlspecialchars($year); ?>年发展">
                        </div>
                        <div class="timeline-text">
                            <div class="year-badge"><?php echo $year; ?><?php echo !empty($item['month']) ? ' · ' . (int)$item['month'] : ''; ?></div>
                            <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                            <p><?php echo htmlspecialchars($item['description1']); ?></p>
                            <p><?php echo htmlspecialchars($item['description2']); ?></p>
                        </div>
                    </div>
                </div>
                <?php 
                $index++;
                endforeach; 
                ?>
            </div>
        </div>
    </section>
  </div>

  <?php include '../public_en/footer.php'; ?>

  </div> <!-- 关闭 swiper-wrapper -->
</div> <!-- 关闭 swiper -->
