<?php
/**
 * MEDIA ROOT CONFIG
 * Fixed static path configuration to prevent scanning errors.
 */

define('MEDIA_DOMAIN', 'https://media.kunzzgroup.com');
define('MEDIA_SERVER_PATH', '/home/u690174784/domains/media.kunzzgroup.com/public_html');

/**
 * Get Media Root Path
 * @return string
 */
function getMediaRootPath() {
    if (!is_dir(MEDIA_SERVER_PATH)) {
        // Fail safely as requested
        return '';
    }
    return MEDIA_SERVER_PATH;
}

/**
 * Get Public Media URL
 * @param string $file Relative path to file
 * @return string Full URL or safe empty string
 */
function getMediaUrl($file) {
    if (empty($file)) return '';
    return MEDIA_DOMAIN . '/' . ltrim($file, '/');
}

/**
 * Get Background Music HTML
 * Uses fixed path bgmusic.mp3
 * @return string HTML or empty
 */
function getBgMusicHtml() {
    $file = 'bgmusic.mp3';
    // Use getMediaRootPath to ensure directory exists first
    $root = getMediaRootPath();
    if (!$root) return '';
    
    $path = $root . '/' . $file;
    if (!file_exists($path)) return '';

    $url = getMediaUrl($file);

    return "<audio autoplay loop id='bgmusic'>
                <source src='{$url}' type='audio/mpeg'>
            </audio>";
}

/**
 * Get Generic Media HTML (Image)
 * @param string $file Relative path
 * @return string HTML img tag or empty
 */
function getMediaHtml($file = '') {
    if (!$file) return '';

    $root = getMediaRootPath();
    if (!$root) return '';

    $path = $root . '/' . $file;
    if (!file_exists($path)) return '';

    $url = getMediaUrl($file);

    return "<img src='{$url}' loading='lazy'>";
}
?>