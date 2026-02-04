<div class="swiper">
  <div class="swiper-wrapper">

  <div class="swiper-slide">
    <section class="aboutus-section">
    <div class="aboutus-banner">
        <?php echo getMediaHtml('about_background'); ?>
      <div class="aboutus-content">
        <h1>关于我们</h1>
        <p>深入了解 Kunzz Holdings 的初心与成长轨迹</p>
      </div>
    </div>

    <div class="aboutus-intro">
      <div class="intro-content">
        <h1>集团简介</h1>
        <p>
          Kunzz Holdings 是一家总部位于马来西亚的多元化控股集团，专注资源整合与效率提升，<br>
          为旗下公司提供战略支持与运营协同。我们致力于用心打造品牌，<br>
          激发团队潜力，助力企业在竞争中脱颖而出。
        </p>
      </div>
    </div>
</section>
    </div>
  
    <div class="swiper-slide">
    <section id="vision" class="vision">
    <div class="vision-content animate-on-scroll vision-slide-down">
      <h1>我们的信念与方向</h1>
      <p>
        我们相信，所有伟大的成就，都始于一份清晰的信念。<br>
        使命、愿景、文化与价值观，是前进的灯塔，也是我们共同坚守的底线。<br>
        在这样的理念指引下，我们持续成长、持续突破、持续成就彼此。
      </p>

      <div class="vision-cards">
        <!-- Card 1 -->
        <div class="vision-card animate-on-scroll slide-in-left">
          <div class="vision-label">我们的使命</div>
          <h2>塑造积极向上和舒适的工作环境</h2>
          <p>
            在这里，我们相信好的工作环境，能孕育出更好的团队。
            我们努力打造一个温暖、有温度、有归属感的空间，
            让每位成员都能安心发挥，共同成长。
            在这里，挑战不再冰冷，努力也值得被看见。
          </p>
        </div>

        <!-- Card 2 -->
        <div class="vision-card animate-on-scroll slide-in-right">
          <div class="vision-label">我们的愿景</div>
          <h2>打造高效的团队，创造行业未来</h2>
          <p>
            一个好团队，是企业价值持续创造的源头。
            唯有高效与创新并行，团队才能突破边界、成就非凡。
            我们正以坚实步伐，走在打造行业标杆的路上，
            用成就说话，用信念前行。
          </p>
        </div>
      </div>
    </div>
  </section>
  </div>

  <div class="swiper-slide">
  <section id="values" class="values-section">
        <div class="values-top animate-on-scroll">
            <h2 class="values-title animate-on-scroll values-scale-fade delay-3">我们的核心<span style="color: #FF5C00;">价值观</span></h2>
            <p class="values-description animate-on-scroll values-scale-fade delay-4">
                核心价值观，贯穿在每一份努力、每一个团队协作之中。
                它让我们在文化中凝聚一致，在挑战中保持信念，
                在成长中维持不变的初心。
            </p>
        </div>
      
        <div class="values-bottom animate-on-scroll card-tilt-in-left">
            <div class="values-card">
                <img src="../images/images/目标导向.png" alt="icon" class="values-icon">
                <h3>目标导向</h3>
                <p>以结果为导向，聚焦关键任务，明确每一步的方向与意义。</p>
            </div>
            <div class="values-card">
                <img src="../images/images/理念一致.png" alt="icon" class="values-icon">
                <h3>理念一致</h3>
                <p>保持高度共识，思想同频，目标一致，减少内耗。</p>
            </div>
            <div class="values-card">
                <img src="../images/images/追求卓越.png" alt="icon" class="values-icon">
                <h3>追求卓越</h3>
                <p>不满足于完成任务，要追求干得更好，更高标准地完成目标，持续优化每项工作。</p>
            </div>
            <div class="values-card">
                <img src="../images/images/创新精神.png" alt="icon" class="values-icon">
                <h3>创新精神</h3>
                <p>拥抱变化、敢于尝试，突破既有框架，不断探索新方法、新工具与新角度，推动企业成长。</p>
            </div>
        </div>
    </section>
  </div>

  <div class="swiper-slide">
  <section class="timeline-section" id="timeline-1">
        <h1>— 我们的发展历史 —</h1>
        
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
                            <div class="year-badge"><?php echo htmlspecialchars($year); ?>年<?php echo !empty($item['month']) ? ' · ' . (int)$item['month'] . '月' : ''; ?></div>
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

  <?php include '../public/footer.php'; ?>

  </div> <!-- 关闭 swiper-wrapper -->
</div> <!-- 关闭 swiper -->
