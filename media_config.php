<?php
require_once __DIR__ . '/config.php';
// updated for subdomain storage
/**
 * 获取子域名媒体存储配置
 * @return array 包含物理路径和URL的配置数组
 */
function getSubdomainMediaConfig() {
    // updated for subdomain storage
    // 子域名URL基础路径
    $subdomainUrl = 'https://media.kunzzgroup.com/public_html/comphotos/';

    // updated for subdomain storage
    // 尝试多个可能的物理路径（Hostinger共享主机常见路径）
    $possiblePaths = [
        '/home/u857194726/domains/media.kunzzgroup.com/public_html/comphotos/',
        $_SERVER['DOCUMENT_ROOT'] . '/../media.kunzzgroup.com/public_html/comphotos/',
        '/home/' . get_current_user() . '/domains/media.kunzzgroup.com/public_html/comphotos/',
        dirname($_SERVER['DOCUMENT_ROOT']) . '/media.kunzzgroup.com/public_html/comphotos/',
    ];

    $physicalPath = null;

    // updated for subdomain storage
    // 自动检测可用的物理路径
    foreach ($possiblePaths as $path) {
        if (is_dir(dirname($path))) {
            $physicalPath = $path;

            // updated for subdomain storage
            // 如果目录不存在，自动创建
            if (!is_dir($physicalPath)) {
                if (mkdir($physicalPath, 0755, true)) {
                    error_log("Subdomain storage: Created directory at $physicalPath");
                } else {
                    error_log("Subdomain storage: Failed to create directory at $physicalPath");
                    continue;
                }
            }

            // 验证目录可写
            if (is_writable($physicalPath)) {
                break;
            } else {
                error_log("Subdomain storage: Directory not writable: $physicalPath");
                $physicalPath = null;
            }
        }
    }

    // updated for subdomain storage
    // 如果无法找到或创建子域名目录，回退到本地路径
    if (!$physicalPath) {
        error_log("Subdomain storage: Could not find or create subdomain directory, falling back to local path");

        // updated for subdomain storage
        // 回退路径选项
        $fallbackPaths = [
            dirname($_SERVER['DOCUMENT_ROOT']) . '/comphoto/comphoto/',
            $_SERVER['DOCUMENT_ROOT'] . '/comphoto/comphoto/',
            'comphoto/comphoto/'
        ];

        foreach ($fallbackPaths as $fallbackPath) {
            $fullPath = realpath($fallbackPath);
            if (!$fullPath) {
                $fullPath = $fallbackPath;
            }

            if (!is_dir($fullPath)) {
                if (mkdir($fullPath, 0755, true)) {
                    $physicalPath = $fullPath;
                    error_log("Subdomain storage: Created fallback directory at $physicalPath");
                    break;
                }
            } else if (is_writable($fullPath)) {
                $physicalPath = $fullPath;
                error_log("Subdomain storage: Using fallback directory at $physicalPath");
                break;
            }
        }

        // 最后的备用方案
        if (!$physicalPath) {
            $physicalPath = 'comphoto/comphoto/';
            if (!is_dir($physicalPath)) {
                mkdir($physicalPath, 0755, true);
            }
        }
    }

    // updated for subdomain storage
    // 确保路径以斜杠结尾
    $physicalPath = rtrim($physicalPath, '/') . '/';

    error_log("Subdomain storage: Final physical path: $physicalPath");
    error_log("Subdomain storage: URL base: $subdomainUrl");

    return [
        'physical_path' => $physicalPath,
        'url_base' => $subdomainUrl
    ];
}

/**
 * 读取媒体配置文件
 * @param string $mediaType 媒体类型
 * @return array 媒体信息
 */
function getMediaConfig($mediaType) {
    // 尝试多个可能的配置文件路径
    $possiblePaths = [
        'media_config.json',  // 根目录
        '../media_config.json',  // 从 frontend 目录访问根目录
        '../../media_config.json'  // 从其他子目录访问根目录
    ];
    
    $configFile = null;
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            $configFile = $path;
            break;
        }
    }
    $defaultConfig = [
        'home_background' => [
            'file' => 'video/video/home_background.webm',
            'type' => 'video'
        ],
        'about_background' => [
            'file' => 'images/images/about_background.jpg',
            'type' => 'image'
        ],
        'joinus_background' => [
            'file' => 'images/images/joinus_background.jpg',
            'type' => 'image'
        ]
    ];
    
    if ($configFile) {
        $config = json_decode(file_get_contents($configFile), true);
        if ($config && isset($config[$mediaType])) {
            return $config[$mediaType];
        }
    }
    
    return isset($defaultConfig[$mediaType]) ? $defaultConfig[$mediaType] : $defaultConfig['home_background'];
}

/**
 * 获取媒体文件的HTML标签
 * @param string $mediaType 媒体类型
 * @param array $attributes 额外的HTML属性
 * @return string HTML标签
 */
function getMediaHtml($mediaType, $attributes = []) {
    $media = getMediaConfig($mediaType);
    
    // 处理文件路径：如果不是以 / 或 http 开头，添加 ../
    $filePath = $media['file'];
    if (strpos($filePath, '/') !== 0 && strpos($filePath, 'http') !== 0) {
        // 从 frontend 目录访问，需要添加 ../
        $filePath = '../' . $filePath;
    }
    
    // 只在能读取本地文件时使用版本号，避免每次请求都强制重新下载远程/缺失资源。
    $timestamp = file_exists($filePath) ? '?v=' . filemtime($filePath) : '';
    $fileUrl = $filePath . $timestamp;
    
    if ($media['type'] === 'video') {
        $defaultAttrs = [
            'class' => 'background-video',
            'autoplay' => '',
            'muted' => '',
            'loop' => '',
            'playsinline' => '',
            'preload' => 'metadata'
        ];
        $attrs = array_merge($defaultAttrs, $attributes);
        $lazyVideo = isset($attrs['data-lazy-video']) && $attrs['data-lazy-video'] === 'true';
        if ($lazyVideo) {
            $attrs['preload'] = 'none';
        }
        
        $attrString = '';
        foreach ($attrs as $key => $value) {
            $attrString .= $value === '' ? " {$key}" : " {$key}=\"{$value}\"";
        }
        
        // 根据文件扩展名确定MIME类型
        $extension = strtolower(pathinfo($media['file'], PATHINFO_EXTENSION));
        $mimeType = 'video/mp4'; // 默认
        switch ($extension) {
            case 'webm':
                $mimeType = 'video/webm';
                break;
            case 'mov':
                $mimeType = 'video/quicktime';
                break;
            case 'avi':
                $mimeType = 'video/x-msvideo';
                break;
            case 'mp4':
            default:
                $mimeType = 'video/mp4';
                break;
        }
        
        $sourceAttr = $lazyVideo ? 'data-src' : 'src';
        return "<video{$attrString}><source {$sourceAttr}=\"{$fileUrl}\" type=\"{$mimeType}\" /></video>";
    } else {
        $defaultAttrs = [
            'class' => 'background-image',
            'decoding' => 'async',
            'fetchpriority' => 'high',
            'style' => 'width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0;'
        ];
        $attrs = array_merge($defaultAttrs, $attributes);
        
        $attrString = '';
        foreach ($attrs as $key => $value) {
            $attrString .= " {$key}=\"{$value}\"";
        }
        
        return "<img src=\"{$fileUrl}\" alt=\"Background\"{$attrString}>";
    }
}


/**
 * 获取公司照片数组
 * @return array 照片路径数组
 */
function getCompanyPhotos() {
    // 子域名配置
    $subdomainMediaUrl = 'https://media.kunzzgroup.com/comphotos/';
    $subdomainPhysicalPath = '/home/u857194726/domains/media.kunzzgroup.com/public_html/comphotos/';

    // 尝试多个可能的配置文件路径
    $possiblePaths = [
        'media_config.json',  // 根目录
        '../media_config.json',  // 从 frontend 目录访问根目录
        '../../media_config.json'  // 从其他子目录访问根目录
    ];

    $configFile = null;
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            $configFile = $path;
            break;
        }
    }

    $photos = [];

    // 调试信息
    error_log("getCompanyPhotos: 开始执行，当前目录: " . getcwd());
    error_log("getCompanyPhotos: 尝试的路径: " . implode(', ', $possiblePaths));
    error_log("getCompanyPhotos: 找到的配置文件: " . ($configFile ?: '无'));
    error_log("getCompanyPhotos: 子域名物理路径: " . $subdomainPhysicalPath); // updated for subdomain storage
    error_log("getCompanyPhotos: 子域名URL: " . $subdomainMediaUrl); // updated for subdomain storage

    if ($configFile) {
        $config = json_decode(file_get_contents($configFile), true);
        if ($config) {
            error_log("getCompanyPhotos: JSON 解析成功，配置键数量: " . count($config));

            // 只获取实际存在的照片，不添加占位图
            for ($i = 1; $i <= 30; $i++) {
                $key = 'comphoto_' . $i;
                if (isset($config[$key])) {
                    // 检查文件是否存在（优先检查子域名路径）
                    $fileExists = false;
                    $photoUrl = '';
                    $timestamp = time();

                    // 优先检查子域名路径
                    $subdomainFilePath = $subdomainPhysicalPath . basename($config[$key]['file']);
                    if (file_exists($subdomainFilePath)) {
                        $fileExists = true;
                        $timestamp = filemtime($subdomainFilePath);
                        
                        if (isset($config[$key]['url'])) {
                            $photoUrl = $config[$key]['url'];
                        } else {
                            $photoUrl = $subdomainMediaUrl . basename($config[$key]['file']);
                        }
                    } elseif (file_exists($config[$key]['file'])) {
                        $fileExists = true;
                        $timestamp = filemtime($config[$key]['file']);
                        
                        if (isset($config[$key]['url'])) {
                            $photoUrl = $config[$key]['url'];
                        } else {
                            $photoUrl = $config[$key]['file'];
                        }
                    }

                    if ($fileExists) {
                        $photoUrl .= '?v=' . $timestamp;
                        $photos[] = $photoUrl;
                        error_log("getCompanyPhotos: 添加照片 $key: $photoUrl");
                    }
                }
                // 注意：这里不再添加占位图
            }
        } else {
            error_log("getCompanyPhotos: JSON 解析失败: " . json_last_error_msg());
        }
    } else {
        error_log("getCompanyPhotos: 所有配置文件路径都不存在");
    }

    // updated for subdomain storage
    // 如果从配置文件没有找到照片，尝试直接从子域名目录扫描
    if (count($photos) == 0) {
        error_log("getCompanyPhotos: 从配置文件未找到照片，尝试直接扫描目录");

        // 优先尝试子域名物理路径，然后是本地路径
        $comphotoPaths = [
            $subdomainPhysicalPath,     // 子域名物理路径
            'comphoto/comphoto/',       // 根目录
            '../comphoto/comphoto/',    // 从frontend目录
            '../../comphoto/comphoto/', // 从其他子目录
            './comphoto/comphoto/'      // 当前目录
        ];

        foreach ($comphotoPaths as $comphotoDir) {
            if (is_dir($comphotoDir)) {
                $files = glob($comphotoDir . '*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE);
                if ($files) {
                    error_log("getCompanyPhotos: 在目录 $comphotoDir 找到 " . count($files) . " 个图片文件");

                    // 按文件名排序
                    sort($files);

                    foreach ($files as $file) {
                        // 如果是从子域名物理路径扫描出来的，使用子域名URL
                        if ($comphotoDir === $subdomainPhysicalPath) {
                            $photoUrl = $subdomainMediaUrl . basename($file) . '?v=' . filemtime($file);
                        } else {
                            // 否则直接使用本地扫描到的相对路径
                            $photoUrl = $file . '?v=' . filemtime($file);
                        }
                        $photos[] = $photoUrl;
                        error_log("getCompanyPhotos: 直接添加照片: $photoUrl");
                    }
                    break; // 找到文件后退出循环
                }
            }
        }
    }

    error_log("getCompanyPhotos: 返回照片数量: " . count($photos));

    // 如果没有找到任何照片，返回空数组
    return $photos;
}

/**
 * 获取时间线配置
 * @param string $year 年份
 * @param string $language 语言版本 ('zh' 或 'en')
 * @return array 时间线数据
 */
function getTimelineConfig($year = null, $language = 'zh') {
    $configFileName = $language === 'en' ? 'timeline_config_en.json' : 'timeline_config.json';
    
    // 尝试多个可能的配置文件路径
    $possiblePaths = [
        $configFileName,  // 根目录
        '../' . $configFileName,  // 从 frontend 目录访问根目录
        '../../' . $configFileName  // 从其他子目录访问根目录
    ];
    
    $configFile = null;
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            $configFile = $path;
            break;
        }
    }
    
    // $defaultTimeline = $language === 'en' ? [
    //     '2022' => [
    //         'title' => 'Cook with Heart, Warm the Soul ✨',
    //         'description1' => 'Tokyo Japanese Cuisine opened its first branch in JB Mid Valley Southkey in 2022.
    //                             Since our establishment, we have been dedicated to crafting dishes with heart and serving with sincerity.
    //                             We hope that every customer who steps into Tokyo Japanese Cuisine feels the warmth of home
    //                             and enjoys a pleasant and memorable dining experience.
    //                             ',
    //         'description2' => '',
    //         'image' => '/images/images/2022发展.jpg'
    //     ],
    //     '2023' => [
    //         'title' => 'Standardized Management, Steady Progress 🌱',
    //         'description1' => 'Since its establishment in 2023, Kunzz Holdings Sdn Bhd has grown steadily, carrying both dreams and warmth.
    //                             As a mission-driven holding group, we serve as a strong foundation and strategic guide for our subsidiaries.
    //                             Through systematic management and long-term planning,
    //                             we nurture and amplify every idea and dream,
    //                             allowing them to shine brightly on the stage of our time.
    //                             ',
    //         'description2' => '',
    //         'image' => '/images/images/2023的发展.jpg'
    //     ],
    //     '2025' => [
    //         'title' => 'Delivering Deliciousness, Continuing Warmth 🚀',
    //         'description1' => 'In January 2025, Tokyo Japanese Cuisine proudly opened its second branch at JB Paradigm Mall — another significant milestone in our journey of growth.
    //                             At Paradigm Mall, we continue to uphold our commitment to exquisite cuisine and heartfelt service,
    //                             creating a warm and relaxing dining atmosphere where every guest can enjoy a comfortable and memorable experience.
    //                             ',
    //         'description2' => '',
    //         'image' => '/images/images/2025的发展.jpg'
    //     ]
    // ] : [
    //     '2022' => [
    //         'title' => '用心料理，温暖人心 ✨',
    //         'description1' => 'Tokyo Japanese Cuisine 于 2022 年在 JB Mid Valley Southkey 开出首家分店。自创立之初，我们便以匠心料理与真诚服务为本，希望让每一位走进 Tokyo Japanese Cuisine 的顾客，都能感受到家的温馨，收获一段愉悦而难忘的用餐体验',
    //         'description2' => '我们始终坚持以客户为中心，以质量为生命，用专业的态度和创新的思维，为客户创造更大价值，为行业树立新的标杆。',
    //         'image' => '/images/images/2022发展.jpg'
    //     ],
    //     '2023' => [
    //         'title' => '规范管理，稳健前行 🌱',
    //         'description1' => 'Kunzz Holdings Sdn Bhd自 2023 年成立以来，稳健成长，承载着梦想与温度。作为一家使命驱动的控股集团，我们是子公司的坚实后盾与战略引路人。以系统化管理和长远布局，孕育并放大每一个创意与梦想，让它们在时代舞台上绽放光芒。',
    //         'description2' => '我们深信，唯有用心管理，倾力推广，才能让每一个独特的创意与梦想，在时代的舞台上绽放出最璀璨的光芒，成为改变世界的力量。',
    //         'image' => '/images/images/2023的发展.jpg'
    //     ],
    //     '2025' => [
    //         'title' => '传递美味，延续温暖 🚀',
    //         'description1' => 'Tokyo Japanese Cuisine 于 2025 年1月在 JB Paradigm Mall 迎来第二间分店的开业。这是品牌成长道路上的又一重要里程碑。在 Paradigm Mall，我们依然坚守对精致美食与真挚服务的坚持，营造温馨自在的用餐氛围，让顾客尽享舒适而难忘的体验。',
    //         'description2' => '我们始终坚持以客户为中心，以质量为生命，用专业的态度和创新的思维，为客户创造更大价值，为行业树立新的标杆。',
    //         'image' => '/images/images/2025的发展.jpg'
    //     ]
    // ];
    
    // 读取与兼容：支持“扁平记录数组（含year/month）”或“按年份分组对象”
    $flatItems = [];
    if ($configFile) {
        $raw = json_decode(file_get_contents($configFile), true);
        if (is_array($raw)) {
            // 情况1：扁平数组
            if (array_keys($raw) === range(0, count($raw) - 1)) {
                $flatItems = $raw;
            } else {
                // 情况2：按年份分组 -> 扁平化
                // Sort years numerically to ensure consistent order
                $years = array_keys($raw);
                sort($years, SORT_NUMERIC);

                foreach ($years as $yearKey) {
                    $entries = $raw[$yearKey];
                    if (is_array($entries)) {
                        // 判断是"多条记录的列表"还是"单条记录的对象"
                        $isList = array_keys($entries) === range(0, count($entries) - 1);
                        if ($isList) {
                            foreach ($entries as $entry) {
                                $entryArray = is_array($entry) ? $entry : [ 'title' => (string)$entry ];
                                $flatItems[] = array_merge($entryArray, [
                                    'year' => (string)$yearKey,
                                    'month' => isset($entryArray['month']) ? (int)$entryArray['month'] : 0,
                                ]);
                            }
                        } else {
                            // 单条记录对象
                            $entryArray = $entries;
                            $flatItems[] = array_merge($entryArray, [
                                'year' => (string)$yearKey,
                                'month' => isset($entryArray['month']) ? (int)$entryArray['month'] : 0,
                            ]);
                        }
                    }
                }
            }
        }
    }

    // // 注入默认数据（防止空）
    // if (empty($flatItems)) {
    //     // 使用最上方默认示例中的中文/英文三条，构造成扁平
    //     $flatItems = $language === 'en' ? [
    //         [ 'year' => '2022', 'month' => 1, 'title' => 'Cook with Heart, Warm the Soul ✨', 'description1' => '...', 'description2' => '', 'image' => 'images/images/2022发展.jpg' ],
    //         [ 'year' => '2023', 'month' => 1, 'title' => 'Standardized Management, Steady Progress 🌱', 'description1' => '...', 'description2' => '', 'image' => 'images/images/2023的发展.jpg' ],
    //         [ 'year' => '2025', 'month' => 1, 'title' => 'Delivering Deliciousness, Continuing Warmth 🚀', 'description1' => '...', 'description2' => '', 'image' => 'images/images/2025的发展.jpg' ],
    //     ] : [
    //         [ 'year' => '2022', 'month' => 1, 'title' => '用心料理，温暖人心 ✨', 'description1' => '...', 'description2' => '...', 'image' => 'images/images/2022发展.jpg' ],
    //         [ 'year' => '2023', 'month' => 1, 'title' => '规范管理，稳健前行 🌱', 'description1' => '...', 'description2' => '...', 'image' => 'images/images/2023的发展.jpg' ],
    //         [ 'year' => '2025', 'month' => 1, 'title' => '传递美味，延续温暖 🚀', 'description1' => '...', 'description2' => '...', 'image' => 'images/images/2025的发展.jpg' ],
    //     ];
    // }

    // 排序：年升序，月升序
    usort($flatItems, function($a, $b){
        $ay=(int)($a['year']??0); $by=(int)($b['year']??0);
        if ($ay===$by) { return (int)($a['month']??0) - (int)($b['month']??0); }
        return $ay - $by;
    });

    // 分组为前端所需结构：以年份为键，主卡片取该年第一条；同时提供 entries 以便可扩展
    $grouped = [];
    foreach ($flatItems as $item) {
        $y = (string)($item['year'] ?? '');
        if ($y === '') { continue; }
        if (!isset($grouped[$y])) { $grouped[$y] = [ 'entries' => [] ]; }
        $grouped[$y]['entries'][] = $item;
    }

    // 生成最终结构并处理图片URL
    $result = [];
    foreach ($grouped as $y => $bundle) {
        $entries = $bundle['entries'];
        // 主展示使用第一条
        $main = $entries[0];
        $data = [
            'title' => $main['title'] ?? '',
            'description1' => $main['description1'] ?? '',
            'description2' => $main['description2'] ?? '',
            'image_url' => '',
        ];
        // entries 扩展（附带 month）
        $data['entries'] = array_map(function($e){ return [
            'title' => $e['title'] ?? '',
            'description1' => $e['description1'] ?? '',
            'description2' => $e['description2'] ?? '',
            'image' => $e['image'] ?? '',
            'month' => isset($e['month']) ? (int)$e['month'] : 0,
        ]; }, $entries);

        // 处理图片路径 -> image_url
        $imagePath = $main['image'] ?? '';
        $foundPath = false;
        if ($imagePath) {
            if (strpos($imagePath, '/') !== 0) {
                $possibleImagePaths = [
                    $imagePath,
                    '../' . $imagePath,
                    '../../' . $imagePath,
                    '/images/images/' . basename($imagePath)
                ];
                if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/frontend_en/') !== false) {
                    $possibleImagePaths = array_merge([
                        '../' . $imagePath,
                        '../../' . $imagePath,
                        '../../images/images/' . basename($imagePath),
                        '../images/images/' . basename($imagePath)
                    ], $possibleImagePaths);
                }
                foreach ($possibleImagePaths as $possiblePath) {
                    if (file_exists($possiblePath)) { $imagePath = $possiblePath; $foundPath = true; break; }
                }
            } else {
                $possibleImagePaths = [ $imagePath, '.' . $imagePath, '../' . $imagePath, '../../' . $imagePath ];
                foreach ($possibleImagePaths as $possiblePath) {
                    if (file_exists($possiblePath)) { $imagePath = $possiblePath; $foundPath = true; break; }
                }
            }
        }
        $data['image_url'] = $imagePath ? ($imagePath . '?v=' . ($foundPath ? filemtime($imagePath) : time())) : '';

        $result[$y] = $data;
    }

    // 按年份排序
    uksort($result, function($a,$b){ return (int)$a - (int)$b; });

    return $year ? (isset($result[$year]) ? $result[$year] : null) : $result;
}

/**
 * 获取时间线HTML内容
 * @return string HTML内容
 */
function getTimelineHtml() {
    $timeline = getTimelineConfig();
    $html = '';
    $index = 0;
    
    foreach ($timeline as $year => $data) {
        $activeClass = $index === 0 ? 'active' : ($index === 1 ? 'next' : 'hidden');
        
        $html .= "<div class=\"timeline-content-item {$activeClass}\" data-year=\"{$year}\" data-index=\"{$index}\">";
        $html .= "<div class=\"timeline-content\" onclick=\"selectCard({$year})\">";
        $html .= "<div class=\"timeline-image\">";
        $html .= "<img src=\"{$data['image_url']}\" alt=\"{$year}年发展\">";
        $html .= "</div>";
        $html .= "<div class=\"timeline-text\">";
        $html .= "<div class=\"year-badge\">{$year}年</div>";
        $html .= "<h3>{$data['title']}</h3>";
        $html .= "<p>{$data['description1']}</p>";
        $html .= "<p>{$data['description2']}</p>";
        $html .= "</div>";
        $html .= "</div>";
        $html .= "</div>";
        
        $index++;
    }
    
    return $html;
}

/**
 * 获取排序后的年份数组
 * @param string $language 语言版本 ('zh' 或 'en')
 * @return array 排序后的年份数组
 */
function getTimelineYears($language = 'zh') {
    $config = getTimelineConfig(null, $language);
    $years = array_keys($config);
    sort($years, SORT_NUMERIC);
    return $years;
}

/**
 * 获取扁平时间线记录（保留每条记录，允许同一年多条）
 * @param string $language
 * @return array 扁平记录，含 year, month, title, description1, description2, image_url
 */
function getTimelineItems($language = 'zh') {
    $configFileName = $language === 'en' ? 'timeline_config_en.json' : 'timeline_config.json';
    $possiblePaths = [ $configFileName, '../' . $configFileName, '../../' . $configFileName ];
    $configFile = null;
    foreach ($possiblePaths as $path) { if (file_exists($path)) { $configFile = $path; break; } }

    $items = [];
    if ($configFile) {
        $raw = json_decode(file_get_contents($configFile), true);
        if (is_array($raw)) {
            if (array_keys($raw) === range(0, count($raw) - 1)) {
                // 扁平数组：仅接收数组型项
                foreach ($raw as $it) {
                    if (is_array($it)) { $items[] = $it; }
                }
            } else {
                // 按年份分组 -> 扁平化（兼容单对象/多条列表）
                // Sort years numerically to ensure consistent order
                $years = array_keys($raw);
                sort($years, SORT_NUMERIC);

                foreach ($years as $y) {
                    $entries = $raw[$y];
                    if (is_array($entries)) {
                        $isList = array_keys($entries) === range(0, count($entries) - 1);
                        if ($isList) {
                            foreach ($entries as $entry) {
                                $entryArray = is_array($entry) ? $entry : [ 'title' => (string)$entry ];
                                $items[] = array_merge($entryArray, [ 'year' => (string)$y, 'month' => isset($entryArray['month']) ? (int)$entryArray['month'] : 0 ]);
                            }
                        } else {
                            $entryArray = $entries;
                            $items[] = array_merge($entryArray, [ 'year' => (string)$y, 'month' => isset($entryArray['month']) ? (int)$entryArray['month'] : 0 ]);
                        }
                    }
                }
            }
        }
    }
    // 过滤掉“空白/占位”记录：无标题且无描述，且图片不存在或为占位
    if (!empty($items)) {
        $items = array_values(array_filter($items, function($it) {
            $title = isset($it['title']) ? trim((string)$it['title']) : '';
            $d1 = isset($it['description1']) ? trim((string)$it['description1']) : '';
            $d2 = isset($it['description2']) ? trim((string)$it['description2']) : '';
            $img = isset($it['image']) ? trim((string)$it['image']) : '';

            // 若文本均为空，则要求必须存在有效图片，否则过滤掉
            if ($title === '' && $d1 === '' && $d2 === '') {
                if ($img === '' || $img === 'images/images/default.jpg') { return false; }
                // 检查图片是否真实存在
                $possibleImagePaths = [ $img, '../' . $img, '../../' . $img, '/images/images/' . basename($img) ];
                if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/frontend_en/') !== false) {
                    $possibleImagePaths = array_merge([ '../' . $img, '../../' . $img, '../../images/images/' . basename($img), '../images/images/' . basename($img) ], $possibleImagePaths);
                }
                foreach ($possibleImagePaths as $p) { if (file_exists($p)) { return true; } }
                return false;
            }
            return true;
        }));
    }
    // 改进过滤逻辑
    if (!empty($items)) {
        $items = array_values(array_filter($items, function($it) {
            // 检查是否是占位内容
            if (isset($it['title']) && $it['title'] === 'New Milestone ✨') {
                return false;
            }
            if (isset($it['description1']) && 
                $it['description1'] === 'Please fill in the first description here...') {
                return false;
            }
            
            // 至少需要有标题或描述之一
            $hasContent = (
                (!empty($it['title']) && trim($it['title']) !== '') ||
                (!empty($it['description1']) && trim($it['description1']) !== '')
            );
            
            return $hasContent;
        }));
    }
    // 排序
    usort($items, function($a,$b){ $ay=(int)($a['year']??0); $by=(int)($b['year']??0); if($ay===$by){return (int)($a['month']??0)-(int)($b['month']??0);} return $ay-$by; });
    // 处理图片URL
    foreach ($items as &$it) {
        $imagePath = $it['image'] ?? '';
        $foundPath = false;
        if ($imagePath) {
            if (strpos($imagePath, '/') !== 0) {
                $possibleImagePaths = [ $imagePath, '../' . $imagePath, '../../' . $imagePath, '/images/images/' . basename($imagePath) ];
                if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/frontend_en/') !== false) {
                    $possibleImagePaths = array_merge([ '../' . $imagePath, '../../' . $imagePath, '../../images/images/' . basename($imagePath), '../images/images/' . basename($imagePath) ], $possibleImagePaths);
                }
                foreach ($possibleImagePaths as $p) { if (file_exists($p)) { $imagePath = $p; $foundPath = true; break; } }
            } else {
                $possibleImagePaths = [ $imagePath, '.' . $imagePath, '../' . $imagePath, '../../' . $imagePath ];
                foreach ($possibleImagePaths as $p) { if (file_exists($p)) { $imagePath = $p; $foundPath = true; break; } }
            }
        }
        $it['image_url'] = $imagePath ? ($imagePath . '?v=' . ($foundPath ? filemtime($imagePath) : time())) : '';
    }
    unset($it);
    return $items;
}

/**
 * 获取扁平年份序列（允许重复）
 * @param string $language
 * @return array
 */
function getTimelineYearsFlat($language = 'zh') {
    $items = getTimelineItems($language);
    return array_map(function($it){ return (string)($it['year'] ?? ''); }, $items);
}

/**
 * 添加新年份条目
 * @param string $year 年份
 * @param array $data 年份数据
 * @param string $configFile 配置文件路径
 * @return bool 成功返回true
 */
function addTimelineYearEntry($year, $data, $configFile = 'timeline_config.json', $language = 'zh') {
    $config = [];
    
    if (file_exists($configFile)) {
        $config = json_decode(file_get_contents($configFile), true) ?: [];
    }
    
    // 生成唯一的条目ID
    $entryId = 'entry_' . time() . '_' . rand(1000, 9999);
    
    if (!isset($config[$year])) {
        $config[$year] = [];
    }
    
    $config[$year][$entryId] = array_merge([
        'title' => $language === 'en' ? 'New Milestone ✨' : '新的里程碑 ✨',
        'description1' => $language === 'en' ? 'Please fill in the first description here...' : '请在这里填写第一段描述...',
        'description2' => $language === 'en' ? 'Please fill in the second description here...' : '请在这里填写第二段描述...',
        'image' => 'images/images/default.jpg',
        'created' => date('Y-m-d H:i:s')
    ], $data);
    
    return file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
}

/**
 * 删除年份条目
 * @param string $year 年份
 * @param string $entryId 条目ID
 * @param string $configFile 配置文件路径
 * @return bool 成功返回true
 */
function deleteTimelineYearEntry($year, $entryId, $configFile = 'timeline_config.json', $language = 'zh') {
    if (!file_exists($configFile)) {
        return false;
    }
    
    $config = json_decode(file_get_contents($configFile), true);
    if (!$config || !isset($config[$year]) || !isset($config[$year][$entryId])) {
        return false;
    }
    
    // 删除对应的图片文件
    if (isset($config[$year][$entryId]['image']) && file_exists($config[$year][$entryId]['image'])) {
        unlink($config[$year][$entryId]['image']);
    }
    
    unset($config[$year][$entryId]);
    
    // 如果该年份没有任何条目了，删除整个年份
    if (empty($config[$year])) {
        unset($config[$year]);
    }
    
    return file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
}

/**
 * 删除年份
 * @param string $year 年份
 * @param string $configFile 配置文件路径
 * @return bool 成功返回true
 */
function deleteTimelineYear($year, $configFile = 'timeline_config.json') {
    if (!file_exists($configFile)) {
        return false;
    }
    
    $config = json_decode(file_get_contents($configFile), true);
    if (!$config || !isset($config[$year])) {
        return false;
    }
    
    // 删除对应的图片文件
    if (isset($config[$year]['image']) && file_exists($config[$year]['image'])) {
        unlink($config[$year]['image']);
    }
    
    unset($config[$year]);
    
    return file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
}

/**
 * 获取Tokyo位置配置 - 增强版，支持动态添加
 * @return array Tokyo位置信息
 */
function getTokyoLocationConfig() {
    $configFile = __DIR__ . '/tokyo_location_config.json';
    $defaultConfig = [
        'section_title' => '我们在这', // 添加这行
        'main_store' => [
            'label' => '总店：',
            'address' => 'T-042 Level 3, Mid Valley, The Mall, Southkey, 81100 Johor Bahru, Johor Darul Ta\'zim',
            'phone' => '+60 19-710 8090',
            'map_url' => 'https://maps.app.goo.gl/VcQp7YGAeQadDNRx9',
            'order' => 1
        ],
        'branch_store' => [
            'label' => '分店：',
            'address' => 'Lot UG-25, Upper Ground Floor, Paradigm Mall, Lbh Skudai, Taman Bukit Mewah, 81200 Johor Bahru, Johor Darul Ta\'zim',
            'phone' => '+60 18-773 8090',
            'map_url' => 'https://maps.app.goo.gl/7vDymMQJ3h9Srp4M6',
            'order' => 2
        ]
    ];
    
    if (file_exists($configFile)) {
        $config = json_decode(file_get_contents($configFile), true);
        if ($config && is_array($config)) {
            // 合并默认配置和自定义配置
            $mergedConfig = array_merge($defaultConfig, $config);
            
            // 按order字段排序，如果没有order字段则使用键名排序
            uasort($mergedConfig, function($a, $b) {
                $orderA = isset($a['order']) ? $a['order'] : 999;
                $orderB = isset($b['order']) ? $b['order'] : 999;
                return $orderA - $orderB;
            });
            
            return $mergedConfig;
        }
    }
    
    return $defaultConfig;
}

/**
 * 保存Tokyo位置配置 - 增强版
 * @param array $config 位置配置数据
 * @return bool 成功返回true
 */
function saveTokyoLocationConfig($config) {
    $configFile = __DIR__ . '/tokyo_location_config.json';
    
    // 检查目录权限
    $dir = dirname($configFile);
    if (!is_writable($dir)) {
        error_log("目录不可写: $dir");
        return false;
    }
    
    // 验证数据结构
    if (!is_array($config)) {
        error_log("配置数据不是数组格式");
        return false;
    }
    
    // 添加时间戳和排序信息
    $order = 1;
    foreach ($config as $key => &$store) {
        if ($key === 'section_title') continue;
        
        if (is_array($store)) {
            $store['updated'] = date('Y-m-d H:i:s');
            if (!isset($store['order'])) {
                $store['order'] = $order++;
            }
        }
    }
    
    // 创建备份
    if (file_exists($configFile)) {
        copy($configFile, $configFile . '.backup.' . date('Y-m-d-H-i-s'));
    }
    
    // 保存文件
    $jsonData = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($jsonData === false) {
        error_log("JSON编码失败: " . json_last_error_msg());
        return false;
    }
    
    $result = file_put_contents($configFile, $jsonData);
    if ($result === false) {
        error_log("写入文件失败: $configFile");
        return false;
    }
    
    return true;
}

/**
 * 添加新的Tokyo店铺
 * @param string $storeKey 店铺键名
 * @param array $storeData 店铺数据
 * @return bool 成功返回true
 */
function addTokyoStore($storeKey, $storeData) {
    $config = getTokyoLocationConfig();
    
    // 设置默认值
    $defaultData = [
        'label' => '新店铺：',
        'address' => '',
        'phone' => '',
        'map_url' => '',
        'order' => count($config) + 1,
        'created' => date('Y-m-d H:i:s')
    ];
    
    $config[$storeKey] = array_merge($defaultData, $storeData);
    
    return saveTokyoLocationConfig($config);
}

/**
 * 删除Tokyo店铺
 * @param string $storeKey 店铺键名
 * @return bool 成功返回true
 */
function deleteTokyoStore($storeKey) {
    $config = getTokyoLocationConfig();
    
    if (!isset($config[$storeKey])) {
        return false;
    }
    
    // 不允许删除默认的主要店铺
    if (in_array($storeKey, ['main_store', 'branch_store'])) {
        return false;
    }
    
    unset($config[$storeKey]);
    
    return saveTokyoLocationConfig($config);
}

/**
 * 生成Tokyo位置信息HTML - 增强版
 * @return string HTML内容
 */
function getTokyoLocationHtml() {
    $config = getTokyoLocationConfig();
    $html = '';
    
    // 修改这行，使用配置中的标题
    $sectionTitle = isset($config['section_title']) ? $config['section_title'] : '我们在这';
    $html .= '<h2>' . htmlspecialchars($sectionTitle) . '</h2>';
    
    foreach ($config as $storeKey => $store) {
        // 跳过标题配置项
        if ($storeKey === 'section_title') continue;
        
        if (!empty($store['address'])) {
            $html .= '<p>' . htmlspecialchars($store['label']) . 
                    '<a href="' . htmlspecialchars($store['map_url']) . '" target="_blank" class="no-style-link">' . 
                    htmlspecialchars($store['address']) . 
                    '</a></p>';
            $html .= '<p>电话：' . htmlspecialchars($store['phone']) . '</p>';
        }
    }
    
    return $html;
}

/**
 * 获取店铺统计信息
 * @return array 统计数据
 */
function getTokyoStoreStats() {
    $config = getTokyoLocationConfig();
    
    return [
        'total_stores' => count($config),
        'active_stores' => count(array_filter($config, function($store) {
            return !empty($store['address']) && !empty($store['phone']);
        })),
        'last_updated' => max(array_column($config, 'updated'))
    ];
}

/**
 * 验证店铺数据
 * @param array $storeData 店铺数据
 * @return array 验证结果 ['valid' => bool, 'errors' => array]
 */
function validateTokyoStoreData($storeData) {
    $errors = [];
    
    if (empty($storeData['label'])) {
        $errors[] = '标签文字不能为空';
    }
    
    if (empty($storeData['address'])) {
        $errors[] = '地址不能为空';
    }
    
    if (empty($storeData['phone'])) {
        $errors[] = '电话号码不能为空';
    }
    
    if (empty($storeData['map_url'])) {
        $errors[] = '地图链接不能为空';
    } elseif (!filter_var($storeData['map_url'], FILTER_VALIDATE_URL)) {
        $errors[] = '地图链接格式不正确';
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * 搜索店铺
 * @param string $keyword 搜索关键词
 * @return array 匹配的店铺
 */
function searchTokyoStores($keyword) {
    $config = getTokyoLocationConfig();
    $results = [];
    
    foreach ($config as $storeKey => $store) {
        $searchText = $store['label'] . ' ' . $store['address'] . ' ' . $store['phone'];
        if (stripos($searchText, $keyword) !== false) {
            $results[$storeKey] = $store;
        }
    }
    
    return $results;
}

/**
 * 导出店铺配置为JSON
 * @return string JSON字符串
 */
function exportTokyoStoresJson() {
    $config = getTokyoLocationConfig();
    return json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

/**
 * 从JSON导入店铺配置
 * @param string $jsonData JSON数据
 * @return bool 成功返回true
 */
function importTokyoStoresJson($jsonData) {
    $config = json_decode($jsonData, true);
    
    if (!$config || !is_array($config)) {
        return false;
    }
    
    // 验证每个店铺数据
    foreach ($config as $storeKey => $storeData) {
        $validation = validateTokyoStoreData($storeData);
        if (!$validation['valid']) {
            return false;
        }
    }
    
    return saveTokyoLocationConfig($config);
}

/**
 * 生成备份文件名
 * @return string 备份文件名
 */
function generateTokyoBackupFilename() {
    return 'tokyo_stores_backup_' . date('Y-m-d_H-i-s') . '.json';
}

/**
 * 创建店铺配置备份
 * @return string|false 备份文件路径或失败时返回false
 */
function backupTokyoStores() {
    $backupDir = 'backups';
    if (!file_exists($backupDir)) {
        mkdir($backupDir, 0755, true);
    }
    
    $backupFile = $backupDir . '/' . generateTokyoBackupFilename();
    $config = getTokyoLocationConfig();
    
    if (file_put_contents($backupFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        return $backupFile;
    }
    
    return false;
}

/**
 * 获取所有备份文件
 * @return array 备份文件列表
 */
function getTokyoBackups() {
    $backupDir = 'backups';
    $backups = [];
    
    if (file_exists($backupDir) && is_dir($backupDir)) {
        $files = scandir($backupDir);
        foreach ($files as $file) {
            if (strpos($file, 'tokyo_stores_backup_') === 0) {
                $backups[] = [
                    'filename' => $file,
                    'path' => $backupDir . '/' . $file,
                    'created' => filemtime($backupDir . '/' . $file),
                    'size' => filesize($backupDir . '/' . $file)
                ];
            }
        }
        
        // 按创建时间倒序排列
        usort($backups, function($a, $b) {
            return $b['created'] - $a['created'];
        });
    }
    
    return $backups;
}

/**
 * 获取招聘职位配置
 * @return array 职位信息数组
 */
function getJobsConfig() {
    $configFile = 'jobs_config.json';
    $jobs = [];
    
    if (file_exists($configFile)) {
        $jobs = json_decode(file_get_contents($configFile), true) ?: [];
    }
    
    // 按发布日期排序（最新的在前）
    uasort($jobs, function($a, $b) {
        return strtotime($b['publish_date']) - strtotime($a['publish_date']);
    });
    
    return $jobs;
}

/**
 * 生成招聘职位HTML
 * @param string $language 语言版本 ('zh' 或 'en')
 * @return string 职位卡片HTML
 */
function getJobsHtml($language = 'zh') {
    $html = '';
    
    try {
        $pdo = get_pdo_connection();
        // 根据语言获取职位
        $stmt = $pdo->prepare("SELECT * FROM job_positions WHERE language = ? ORDER BY publish_date DESC, id DESC");
        $stmt->execute([$language]);
        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 按公司分类分组
        $groupedJobs = [];
        foreach ($jobs as $job) {
            $category = $job['company_category'] ?? 'KUNZZ HOLDINGS';
            $groupedJobs[$category][] = $job;
        }
        
        // 为每个公司创建独立的卡片容器，确保KUNZZHOLDINGS在左边
        $companyOrder = ['KUNZZ HOLDINGS', 'TOKYO JAPANESE CUISINE', 'TOKYO IZAKAYA'];
        foreach ($companyOrder as $company) {
            $html .= '<div class="company-job-container">';
            $html .= '<h3 class="company-title">' . htmlspecialchars($company) . '</h3>';
            $html .= '<div class="company-jobs-list">';
            
            if (isset($groupedJobs[$company]) && !empty($groupedJobs[$company])) {
                if ($company === 'TOKYO JAPANESE CUISINE' || $company === 'TOKYO IZAKAYA') {
                    // 为TOKYO公司按部门分组显示
                    $departmentJobs = [];
                    foreach ($groupedJobs[$company] as $job) {
                        $dept = $job['company_department'] ?? '其他';
                        $departmentJobs[$dept][] = $job;
                    }
                    
                    // 根据语言定义部门顺序和显示文本
                    if ($language === 'en') {
                        $departmentOrder = ['Front Desk', 'Kitchen', 'sushi bar'];
                        $departmentDisplay = [
                            'Front Desk' => 'Front Desk',
                            'Kitchen' => 'Kitchen', 
                            'sushi bar' => 'SUSHI BAR'
                        ];
                    } else {
                        $departmentOrder = ['前台', '厨房', 'sushi bar'];
                        $departmentDisplay = [
                            '前台' => '前台',
                            '厨房' => '厨房',
                            'sushi bar' => 'SUSHI BAR'
                        ];
                    }
                    
                    foreach ($departmentOrder as $dept) {
                        if (isset($departmentJobs[$dept]) && !empty($departmentJobs[$dept])) {
                            $jobCount = count($departmentJobs[$dept]);
                            $singleJobClass = ($jobCount == 1) ? ' single-job' : '';
                            
                            $html .= '<div class="department-section">';
                            $html .= '<div class="department-title">' . htmlspecialchars($departmentDisplay[$dept]) . '</div>';
                            $html .= '<div class="department-jobs' . $singleJobClass . '">';
                            
                            $jobIndex = 0;
                            foreach ($departmentJobs[$dept] as $job) {
                                $jobIndex++;
                                $isLastOddJob = ($jobCount > 2 && $jobCount % 2 == 1 && $jobIndex == $jobCount) ? ' last-odd-job' : '';
                                
                                $html .= '<div class="job-item' . $isLastOddJob . '" data-job-id="' . $job['id'] . '">';
                                $html .= '<div class="job-item-title">' . htmlspecialchars($job['job_title']) . '</div>';
                                $html .= '</div>';
                            }
                            
                            $html .= '</div>'; // department-jobs
                            $html .= '</div>'; // department-section
                        }
                    }
                } else {
                    // 其他公司（KUNZZ HOLDINGS）正常显示
                    foreach ($groupedJobs[$company] as $job) {
                        $html .= '<div class="job-item" data-job-id="' . $job['id'] . '">';
                        $html .= '<div class="job-item-title">' . htmlspecialchars($job['job_title']) . '</div>';
                        $html .= '</div>';
                    }
                }
            } else {
                $html .= '<div class="no-jobs-company">暂无职位</div>';
            }
            
            $html .= '</div>'; // company-jobs-list
            $html .= '</div>'; // company-job-container
        }
    } catch (Exception $e) {
        $html = '<div class="no-jobs">职位数据加载失败</div>';
    }
    
    return $html;
}

/**
 * 获取背景音乐配置
 * @return array 音乐信息
 */
function getBgMusicConfig() {
    $configFile = 'music_config.json';
    $defaultConfig = [
        'file' => 'audio/audio/music.mp3',
        'type' => 'audio',
        'format' => 'mp3'
    ];
    
    if (file_exists($configFile)) {
        $config = json_decode(file_get_contents($configFile), true);
        if ($config && isset($config['background_music']) && file_exists($config['background_music']['file'])) {
            return $config['background_music'];
        }
    }
    
    return $defaultConfig;
}

/**
 * 获取音乐HTML标签
 * @param array $attributes 额外的HTML属性
 * @return string HTML标签
 */
function getBgMusicHtml($attributes = []) {
    $music = getBgMusicConfig();
    
    // 处理文件路径：如果不是以 / 或 http 开头，添加 ../
    $filePath = $music['file'];
    if (strpos($filePath, '/') !== 0 && strpos($filePath, 'http') !== 0) {
        // 从 frontend 目录访问，需要添加 ../
        $filePath = '../' . $filePath;
    }
    
    // 只在能读取本地文件时使用版本号，避免每次请求都强制重新下载远程/缺失资源。
    $timestamp = file_exists($filePath) ? '?v=' . filemtime($filePath) : '';
    $fileUrl = $filePath . $timestamp;
    
    $defaultAttrs = [
        'id' => 'bgMusic',
        'loop' => '',
        'preload' => 'none'
    ];
    $attrs = array_merge($defaultAttrs, $attributes);
    
    $attrString = '';
    foreach ($attrs as $key => $value) {
        $attrString .= $value === '' ? " {$key}" : " {$key}=\"{$value}\"";
    }
    
    $mimeType = 'audio/' . ($music['format'] === 'mp3' ? 'mpeg' : $music['format']);
    
    return "<audio{$attrString}><source src=\"{$fileUrl}\" type=\"{$mimeType}\" /></audio>";
}
?>
