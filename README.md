kunzzweb/
│
├── public/                          # 公共资源
│   ├── css/
│   │   ├── global.css
│   │   ├── reset.css
│   │   ├── variables.css
│   │   └── components/
│   │       ├── header.css
│   │       └── footer.css
│   │       └── social.css
│   ├── header.php
│   ├── header.js
│   ├── sidebar.php
│   └── footer.php
│   └── social.php
│
├── frontend/                        # 前台用户界面
│   ├── css/
│   │   ├── index.css
│   │   ├── about.css
│   │   ├── joinus.css
│   │   └── responsive.css
│   │
│   ├── index.php
│   ├── about.php
│   ├── joinus.php
│   ├── login.php
│   ├── login.html
│   ├── reset_password.php
│   ├── reset_password.html
│   ├── verify_code.php
│   ├── send_verification.php
│   ├── success.html
│   ├── app.js
│   └── account.js
│
│   └── tokyo/                       # Tokyo品牌页面
│       ├── css/
│       │   ├── tokyo.css
│       │   ├── tokyoanimation.css
│       │   └── tokyo-responsive.css
│       │
│       ├── tokyo-japanese-cuisine.php
│       ├── tokyo-japanese-cuisine-about.html
│       ├── tokyo-japanese-cuisine-joinus.html
│       ├── tokyo-japanese-cuisine-success.html
│       ├── tokyologin.php
│       ├── tokyologin.html
│       ├── tokyoregister.php
│       ├── tokyoregister.html
│       ├── tokyosend_verification.php
│       └── tokyoverify_code.php
│
├── backend/                         # 后台管理系统
│   ├── css/
│   │   ├── backend-main.css
│   │   └── admin-responsive.css
│   │
│   ├── dashboard.php
│   ├── edit_profile.php
│   ├── logout.php
│   ├── check_permissions.php
│   ├── mailman.php
│   │
│   ├── kpi/                         # KPI管理模块
│   │   ├── css/
│   │   │   ├── kpi.css
│   │   │
│   │   ├── api.php （要改名成kpiapi.php)
│   │   ├── kpi.php
│   │   ├── kpiedit.php
│   │   └── kpi.js
│   │
│   ├── stock/                       # 库存管理模块
│   │   ├── css/
│   │   │   ├── stock.css
│   │   │
│   │   ├── stocklist.php
│   │   ├── stocklistall.php
│   │   ├── stocklistapi.php
│   │   ├── stockedit.php
│   │   ├── stockeditall.php
│   │   ├── stockeditapi.php
│   │   ├── stockapi.php
│   │   ├── stockminimum.php
│   │   ├── stockminimumapi.php
│   │   ├── stockproductname.php
│   │   ├── stockremark.php
│   │   └── stockremarkapi.php
│   │
│   │   ├── j1/                      # J1库存系统
│   │   │   │
│   │   │   ├── j1stocklist.php
│   │   │   ├── j1stocklistapi.php
│   │   │   ├── j1stockedit.php
│   │   │   ├── j1stockeditapi.php
│   │   │   ├── j1stockeditpage.php
│   │   │   └── j1stockeditpageapi.php
│   │   │
│   │   └── j2/                      # J2库存系统
│   │       │
│   │       ├── j2stocklist.php
│   │       ├── j2stocklistapi.php
│   │       ├── j2stockedit.php
│   │       ├── j2stockeditapi.php
│   │       ├── j2stockeditpage.php
│   │       └── j2stockeditpageapi.php
│   │
│   ├── upload/                      # 内容上传管理
│   │   ├── css/
│   │   │   ├── upload.css
│   │   │
│   │   ├── media_manager.php
│   │   ├── media_config.php
│   │   ├── photos_config.php
│   │   ├── bgmusicupload.php
│   │   ├── homepage1upload.php
│   │   ├── aboutpage1upload.php
│   │   ├── aboutpage4upload.php
│   │   ├── joinpage1upload.php
│   │   ├── joinpage2upload.php
│   │   ├── joinpage3upload.php
│   │   ├── tokyopage1upload.php
│   │   └── tokyopage5upload.php
│   │
│   ├── jobs/                        # 招聘管理
│   │   ├── css/
│   │   │   ├── jobs.css
│   │   │
│   │   ├── get_jobs_api.php
│   │   └── job_positions_api.php
│   │
│   ├── auth/                        # 后台认证
│   │   ├── css/
│   │   │   ├── auth.css
│   │   │   └── login-forms.css
│   │   │
│   │   ├── aboutlogin.html
│   │   ├── joinuslogin.html
│   │   └── logintest.html
│   │
│   └── api/                         # 后台API
│       ├── api.php
│       └── generatecode.php
│       └── generatecodeapi.php
│
├── frontend-en/                     # 英文前台
│   ├── css/
│   │  
│   └── (英文前台相关文件)
│
├── backend-en/                      # 英文后台
│   ├── css/
│   │
│   └── (英文后台相关文件)
│
├── vendor/                          # 第三方依赖
│   ├── autoload.php
│   ├── composer/
│   └── phpmailer/
│
├── images/                          # 图片资源
│   └── images/
│
├── config.php                       # 配置文件
├── composer.json
├── composer.lock
└── phpinfo.php

现代化响应式：
1. 使用clamp{}跟随屏幕的PPI/DPI/DPR去改变元素的像素大小
2. @media只用于手机/平板竖屏的断点来改变排版布局，元素大小

预设网页像素：
html {
    font-size: 62.5%;
}
1. 把像素点默认在10px，方便换算rem: 1rem = 10px