<!DOCTYPE html>
<html lang="zh">

<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KUNZZ HOLDINGS</title>
    <link rel="stylesheet" href="../frontend/css/index.css" />
    <link rel="stylesheet" href="../frontend/css/animation.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@400;500;700&display=swap" rel="stylesheet">
</head>

<body>
    <?php $basePath = './'; include __DIR__ . '/../sidebar.php'; ?>

    <!-- Dashboard Content Wrapper (Swiper expected by dashboard.js) -->
    <div class="swiper">
        <div class="swiper-wrapper">
            <div class="swiper-slide">
                <section class="home">
                    <!-- Background image or video would go here -->
                    <div class="home-content hidden animate-on-scroll">
                        <h1 class="scale-fade-in">欢迎使用 <span style="font-size: 1.5em;">KUNZZ</span> 管理系统</h1>
                        <div class="decor-line scale-fade-in"></div>
                        <p class="scale-fade-in">
                            高效源于信任，创新源于自由。<br />
                            一支有温度的团队，才能创造持续的价值。
                        </p>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <script src="../frontend/js/app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="js/dashboard.js"></script>
</body>

</html>