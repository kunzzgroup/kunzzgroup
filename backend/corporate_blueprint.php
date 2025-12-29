<?php
session_start();
ob_start();

// 设置字符编码
header('Content-Type: text/html; charset=UTF-8');

// 加载JSON数据 - 文件在backend目录中
$jsonFile = __DIR__ . '/corporate_strategy.json';
$strategyData = null;

if (file_exists($jsonFile)) {
    $jsonContent = file_get_contents($jsonFile);
    $strategyData = json_decode($jsonContent, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        $strategyData = null;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>企业蓝图</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Microsoft YaHei', sans-serif;
            background-color: #faf7f2;
            color: #000000;
            min-height: 100vh;
            overflow-x: hidden;
            overflow-y: auto;
            line-height: 1.6;
        }

        /* 主内容容器 */
        .main-container {
            max-width: 1800px;
            margin: 0 auto;
            padding: clamp(16px, 1.25vw, 24px) 24px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* 标题区域 */
        .header {
            margin-bottom: clamp(24px, 2.08vw, 40px);
        }

        .header-title {
            font-size: clamp(24px, 2.6vw, 50px);
            font-weight: bold;
            color: #000000ff;
            margin-bottom: 10px;
            text-align: left;
        }

        .header-title::after {
            content: "";
            display: block;
            height: 3px;
            width: 100%;
            margin-top: 16px;
            background: linear-gradient(90deg, rgba(255,92,0,0) 0%, rgba(0, 0, 0, 1) 25%, rgba(0, 0, 0, 1) 75%, rgba(255,92,0,0) 100%);
        }

        .header-subtitle {
            font-size: clamp(14px, 1.25vw, 18px);
            color: #6b7280;
        }

        /* 章节样式 */
        .section {
            margin-bottom: clamp(32px, 3.13vw, 60px);
        }

        .section-title {
            font-size: clamp(20px, 2.08vw, 32px);
            font-weight: bold;
            color: #000000ff;
            margin-bottom: clamp(16px, 1.67vw, 24px);
            padding-bottom: clamp(8px, 0.83vw, 12px);
            border-bottom: 3px solid #ff5c00;
            display: inline-block;
        }

        /* 卡片样式 */
        .card {
            background: rgba(255, 255, 255, 1);
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            padding: clamp(20px, 2.08vw, 32px);
            margin-bottom: clamp(16px, 1.67vw, 24px);
        }

        /* Header Section */
        .header-panel {
            background: #ffffff;
            border-radius: clamp(16px, 1.67vw, 24px);
            padding: clamp(32px, 3.13vw, 48px) clamp(40px, 4.17vw, 64px);
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: clamp(32px, 3.13vw, 48px);
        }

        /* Left side text content */
        .header-text-content {
            flex: 1;
            text-align: left;
            position: relative;
            padding-left: clamp(20px, 2.08vw, 32px);
        }

        /* Vertical golden line on the left */
        .header-text-content::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 5px;
            background: #ff5c00;
        }

        .header-label {
            font-size: clamp(12px, 1.25vw, 16px);
            color: #ffd700;
            font-weight: 500;
            margin-bottom: clamp(16px, 1.67vw, 24px);
            letter-spacing: 0.5px;
        }

        .company-name-large {
            font-size: clamp(36px, 4.69vw, 64px);
            font-weight: 700;
            color: #000000;
            margin-bottom: clamp(12px, 1.25vw, 16px);
            letter-spacing: 1px;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .company-subtitle {
            font-size: clamp(36px, 4.69vw, 64px);
            font-weight: 700;
            color: #000000;
            margin-bottom: clamp(12px, 1.25vw, 16px);
            letter-spacing: 1px;
            line-height: 1.2;
        }

        .company-subtitle-upper {
            text-transform: uppercase;
        }

        .plan-title-en {
            font-size: clamp(12px, 1.25vw, 16px);
            color: #000000;
            font-weight: 400;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* Right side logo */
        .header-logo-container {
            flex-shrink: 0;
            position: relative;
            width: clamp(140px, 14.58vw, 200px);
            height: clamp(140px, 14.58vw, 200px);
        }

        .header-logo {
            width: 100%;
            height: 100%;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .header-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        /* Logo reflection */
        .logo-reflection {
            position: absolute;
            bottom: clamp(-30px, -3.13vw, -40px);
            left: 50%;
            transform: translateX(-50%) scaleY(-1);
            width: 80%;
            height: 20%;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.1) 0%, transparent 100%);
            border-radius: 50%;
            opacity: 0.3;
            filter: blur(2px);
            z-index: 0;
        }

        /* Timeline Section */
        .timeline-container {
            position: relative;
            padding: clamp(40px, 4.17vw, 60px) 0;
        }

        .timeline-header {
            text-align: center;
            margin-bottom: clamp(50px, 5.21vw, 70px);
            position: relative;
        }

        .timeline-main-title {
            font-size: clamp(36px, 3.75vw, 56px);
            font-weight: 800;
            background: linear-gradient(135deg, #ff5c00 0%, #ff8c42 50%, #ffd700 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: clamp(14px, 1.46vw, 20px);
            letter-spacing: 1px;
            text-shadow: 0 4px 8px rgba(255, 92, 0, 0.2);
            position: relative;
        }

        .timeline-main-title::after {
            content: '';
            position: absolute;
            bottom: -12px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, transparent, #ff5c00, transparent);
            border-radius: 2px;
        }

        .timeline-subtitle {
            font-size: clamp(15px, 1.56vw, 20px);
            color: #4a5568;
            font-weight: 500;
            letter-spacing: 0.3px;
            margin-top: clamp(20px, 2.08vw, 28px);
        }

        .timeline-wrapper {
            position: relative;
            padding: clamp(100px, 10.42vw, 150px) clamp(40px, 4.17vw, 60px);
            overflow: visible;
            background: 
                radial-gradient(circle at 20% 50%, rgba(255, 92, 0, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 80% 50%, rgba(255, 215, 0, 0.02) 0%, transparent 50%);
            border-radius: 12px;
            min-height: clamp(400px, 41.67vw, 600px);
        }

        /* SVG Wave Timeline Path */
        .timeline-svg-container {
            position: absolute;
            top: 0;
            left: clamp(80px, 8.33vw, 120px);
            right: clamp(80px, 8.33vw, 120px);
            height: 100%;
            width: calc(100% - clamp(160px, 16.67vw, 240px));
            z-index: 1;
            overflow: visible;
        }

        .timeline-svg-path {
            stroke: #ff5c00;
            stroke-width: 5;
            fill: none;
            filter: drop-shadow(0 2px 4px rgba(255, 92, 0, 0.3));
            stroke-dasharray: 1000;
            stroke-dashoffset: 1000;
            transition: stroke-dashoffset 2s cubic-bezier(0.65, 0, 0.35, 1);
        }

        .timeline-svg-path.animate-in {
            stroke-dashoffset: 0;
        }

        /* Wave line gradient */
        .timeline-svg-gradient {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }

        /* Start point - rectangle */
        .timeline-start {
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%) scale(0);
            background: linear-gradient(135deg, #ff5c00 0%, #ff8c42 100%);
            padding: clamp(14px, 1.46vw, 18px) clamp(28px, 2.92vw, 36px);
            color: #ffffff;
            font-size: clamp(14px, 1.46vw, 18px);
            font-weight: 700;
            border-radius: 8px;
            z-index: 4;
            white-space: nowrap;
            box-shadow: 
                0 4px 12px rgba(255, 92, 0, 0.4),
                0 2px 4px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            letter-spacing: 0.5px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .timeline-start.animate-in {
            transform: translateY(-50%) scale(1);
        }

        /* Start point event (below the box) */
        .timeline-start-event {
            position: absolute;
            left: 0;
            top: 60%;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: clamp(140px, 14.58vw, 200px);
            transform: translate(-50%, 0) translateY(20px);
            opacity: 0;
            transition: transform 0.8s cubic-bezier(0.34, 1.56, 0.64, 1),
                        opacity 0.8s ease,
                        filter 0.3s ease;
            z-index: 3;
        }

        .timeline-start-event.animate-in {
            opacity: 1;
            transform: translate(-50%, 0) translateY(0);
        }

        .timeline-start-event:hover {
            transform: translate(-50%, 0) translateY(-8px) scale(1.15);
            filter: drop-shadow(0 12px 24px rgba(255, 92, 0, 0.3));
        }

        .timeline-start-event .timeline-arrow {
            width: 0;
            height: 0;
            margin-bottom: clamp(10px, 1.04vw, 14px);
            border-left: clamp(9px, 0.94vw, 13px) solid transparent;
            border-right: clamp(9px, 0.94vw, 13px) solid transparent;
            border-bottom: clamp(14px, 1.46vw, 18px) solid #000000;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
            transform: rotate(-90deg);
        }

        .timeline-start-event .timeline-year-label {
            font-size: clamp(18px, 1.88vw, 26px);
            font-weight: 800;
            background: linear-gradient(135deg, #ff5c00 0%, #ff8c42 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: clamp(10px, 1.04vw, 14px);
            letter-spacing: 0.5px;
            text-shadow: 0 2px 4px rgba(255, 92, 0, 0.2);
        }

        .timeline-start-event .timeline-goal-text {
            font-size: clamp(14px, 1.46vw, 18px);
            color: #2c3e50;
            text-align: center;
            line-height: 1.6;
            font-weight: 500;
            padding: clamp(12px, 1.25vw, 16px) clamp(16px, 1.67vw, 20px);
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08), 0 2px 4px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(255, 92, 0, 0.1);
            max-width: 100%;
            word-wrap: break-word;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            backdrop-filter: blur(10px);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .timeline-start-event .timeline-goal-text::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 92, 0, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .timeline-start-event:hover .timeline-goal-text {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15), 0 4px 12px rgba(255, 92, 0, 0.15);
            border-color: rgba(255, 92, 0, 0.3);
            transform: translateY(-2px);
        }

        .timeline-start-event:hover .timeline-goal-text::before {
            left: 100%;
        }

        /* End point - star */
        .timeline-end {
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%) scale(0) rotate(0deg);
            width: clamp(70px, 7.29vw, 90px);
            height: clamp(70px, 7.29vw, 90px);
            background: linear-gradient(135deg, #ff5c00 0%, #ff8c42 100%);
            clip-path: polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: clamp(13px, 1.35vw, 17px);
            font-weight: 700;
            z-index: 4;
            box-shadow: 
                0 4px 16px rgba(255, 92, 0, 0.4),
                0 2px 6px rgba(0, 0, 0, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
            letter-spacing: 0.5px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .timeline-end.animate-in {
            transform: translateY(-50%) scale(1) rotate(360deg);
        }

        .timeline-end::before {
            content: '';
            position: absolute;
            inset: -2px;
            background: linear-gradient(135deg, rgba(255, 92, 0, 0.3), rgba(255, 140, 66, 0.3));
            clip-path: polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%);
            z-index: -1;
            filter: blur(4px);
            animation: pulse-glow 2s ease-in-out infinite;
        }

        @keyframes pulse-glow {
            0%, 100% { opacity: 0.6; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.05); }
        }

        /* End point event (below the star) */
        .timeline-end-event {
            position: absolute;
            right: 0;
            top: 60%;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: clamp(140px, 14.58vw, 200px);
            transform: translate(50%, 0) translateY(20px);
            opacity: 0;
            transition: transform 0.8s cubic-bezier(0.34, 1.56, 0.64, 1),
                        opacity 0.8s ease,
                        filter 0.3s ease;
            z-index: 3;
        }

        .timeline-end-event.animate-in {
            opacity: 1;
            transform: translate(50%, 0) translateY(0);
        }

        .timeline-end-event:hover {
            transform: translate(50%, 0) translateY(-8px) scale(1.15);
            filter: drop-shadow(0 12px 24px rgba(255, 92, 0, 0.3));
        }

        .timeline-end-event .timeline-arrow {
            width: 0;
            height: 0;
            margin-bottom: clamp(10px, 1.04vw, 14px);
            border-left: clamp(9px, 0.94vw, 13px) solid transparent;
            border-right: clamp(9px, 0.94vw, 13px) solid transparent;
            border-bottom: clamp(14px, 1.46vw, 18px) solid #000000;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
            transform: rotate(90deg);
        }

        .timeline-end-event .timeline-year-label {
            font-size: clamp(18px, 1.88vw, 26px);
            font-weight: 800;
            background: linear-gradient(135deg, #ff5c00 0%, #ff8c42 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: clamp(10px, 1.04vw, 14px);
            letter-spacing: 0.5px;
            text-shadow: 0 2px 4px rgba(255, 92, 0, 0.2);
        }

        .timeline-end-event .timeline-goal-text {
            font-size: clamp(14px, 1.46vw, 18px);
            color: #2c3e50;
            text-align: center;
            line-height: 1.6;
            font-weight: 500;
            padding: clamp(12px, 1.25vw, 16px) clamp(16px, 1.67vw, 20px);
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08), 0 2px 4px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(255, 92, 0, 0.1);
            max-width: 100%;
            word-wrap: break-word;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            backdrop-filter: blur(10px);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .timeline-end-event .timeline-goal-text::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 92, 0, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .timeline-end-event:hover .timeline-goal-text {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15), 0 4px 12px rgba(255, 92, 0, 0.15);
            border-color: rgba(255, 92, 0, 0.3);
            transform: translateY(-2px);
        }

        .timeline-end-event:hover .timeline-goal-text::before {
            left: 100%;
        }

        /* Timeline items container */
        .timeline-items {
            position: relative;
            padding: 0 clamp(100px, 10.42vw, 140px);
            width: 100%;
            height: 100%;
            min-height: clamp(400px, 41.67vw, 600px);
        }

        .timeline-event {
            position: absolute;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: clamp(140px, 14.58vw, 200px);
            opacity: 0;
            visibility: hidden;
            transition: transform 0.8s cubic-bezier(0.34, 1.56, 0.64, 1), 
                        opacity 0.8s ease,
                        visibility 0s 0.8s,
                        filter 0.3s ease;
            /* 初始位置将在JavaScript中设置 */
        }

        .timeline-event.animate-in {
            opacity: 1;
            visibility: visible;
            transition: transform 0.8s cubic-bezier(0.34, 1.56, 0.64, 1), 
                        opacity 0.8s ease,
                        visibility 0s 0s,
                        filter 0.3s ease;
        }

        .timeline-event:hover {
            transform: scale(1.15) translateY(-8px);
            filter: drop-shadow(0 12px 24px rgba(255, 92, 0, 0.3));
            z-index: 10;
        }

        .timeline-arrow {
            width: 0;
            height: 0;
            margin-bottom: clamp(10px, 1.04vw, 14px);
            transition: filter 0.3s ease;
            border-left: clamp(9px, 0.94vw, 13px) solid transparent;
            border-right: clamp(9px, 0.94vw, 13px) solid transparent;
            border-bottom: clamp(14px, 1.46vw, 18px) solid #000000;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        }

        .timeline-year-label {
            font-size: clamp(18px, 1.88vw, 26px);
            font-weight: 800;
            background: linear-gradient(135deg, #ff5c00 0%, #ff8c42 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: clamp(10px, 1.04vw, 14px);
            letter-spacing: 0.5px;
            text-shadow: 0 2px 4px rgba(255, 92, 0, 0.2);
            transition: transform 0.3s ease;
        }

        .timeline-event:hover .timeline-year-label {
            transform: scale(1.1);
        }

        .timeline-event:nth-child(even) .timeline-year-label {
            margin-bottom: clamp(10px, 1.04vw, 14px);
            margin-top: 0;
        }

        .timeline-goal-text {
            font-size: clamp(14px, 1.46vw, 18px);
            color: #2c3e50;
            text-align: center;
            line-height: 1.6;
            font-weight: 500;
            padding: clamp(12px, 1.25vw, 16px) clamp(16px, 1.67vw, 20px);
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08), 0 2px 4px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(255, 92, 0, 0.1);
            max-width: 100%;
            word-wrap: break-word;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            backdrop-filter: blur(10px);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .timeline-goal-text::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 92, 0, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .timeline-event:hover .timeline-goal-text,
        .timeline-start-event:hover .timeline-goal-text,
        .timeline-end-event:hover .timeline-goal-text {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15), 0 4px 12px rgba(255, 92, 0, 0.15);
            border-color: rgba(255, 92, 0, 0.3);
            transform: translateY(-2px);
        }

        .timeline-event:hover .timeline-goal-text::before,
        .timeline-start-event:hover .timeline-goal-text::before,
        .timeline-end-event:hover .timeline-goal-text::before {
            left: 100%;
        }

        /* Corporate Core Section */
        .core-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: clamp(16px, 1.67vw, 24px);
        }

        .core-card {
            background: #fff;
            border-radius: 8px;
            padding: clamp(20px, 2.08vw, 32px);
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .core-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .core-card-title {
            font-size: clamp(16px, 1.56vw, 20px);
            font-weight: bold;
            color: #ff5c00;
            margin-bottom: clamp(12px, 1.04vw, 16px);
            padding-bottom: clamp(8px, 0.83vw, 12px);
            border-bottom: 2px solid #ff5c00;
        }

        .core-card-content {
            font-size: clamp(13px, 1.04vw, 16px);
            color: #374151;
            line-height: 1.8;
        }

        .core-card-list {
            list-style: none;
            padding: 0;
        }

        .core-card-list li {
            padding: clamp(6px, 0.63vw, 10px) 0;
            font-size: clamp(13px, 1.04vw, 16px);
            color: #374151;
            border-bottom: 1px solid #f3f4f6;
        }

        .core-card-list li:last-child {
            border-bottom: none;
        }

        .core-card-list li::before {
            content: '•';
            color: #ff5c00;
            font-weight: bold;
            margin-right: 8px;
        }

        /* Culture & Values Explanation */
        .explanation-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: clamp(16px, 1.67vw, 24px);
        }

        .explanation-card {
            background: #fff;
            border-radius: 8px;
            padding: clamp(20px, 2.08vw, 32px);
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .explanation-key {
            font-size: clamp(16px, 1.56vw, 20px);
            font-weight: bold;
            color: #ff5c00;
            margin-bottom: clamp(8px, 0.83vw, 12px);
        }

        .explanation-description {
            font-size: clamp(13px, 1.04vw, 16px);
            color: #374151;
            line-height: 1.8;
        }

        /* Organization Structure */
        .org-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: clamp(24px, 2.6vw, 40px);
        }

        .org-section {
            background: #fff;
            border-radius: 8px;
            padding: clamp(20px, 2.08vw, 32px);
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .org-section-title {
            font-size: clamp(18px, 1.88vw, 24px);
            font-weight: bold;
            color: #000000ff;
            margin-bottom: clamp(16px, 1.67vw, 24px);
            padding-bottom: clamp(8px, 0.83vw, 12px);
            border-bottom: 2px solid #ff5c00;
        }

        .org-list {
            list-style: none;
            padding: 0;
        }

        .org-list-item {
            padding: clamp(10px, 1.04vw, 16px) 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .org-list-item:last-child {
            border-bottom: none;
        }

        .org-name {
            font-size: clamp(14px, 1.25vw, 18px);
            font-weight: 600;
            color: #000000ff;
            margin-bottom: 4px;
        }

        .org-title {
            font-size: clamp(12px, 1.04vw, 16px);
            color: #6b7280;
        }

        /* Strategic Objectives */
        .objectives-container {
            display: flex;
            flex-direction: column;
            gap: clamp(32px, 3.13vw, 48px);
        }

        .year-section {
            background: #fff;
            border-radius: 8px;
            padding: clamp(24px, 2.6vw, 40px);
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .year-title {
            font-size: clamp(20px, 2.08vw, 28px);
            font-weight: bold;
            color: #ff5c00;
            margin-bottom: clamp(20px, 2.08vw, 32px);
            padding-bottom: clamp(8px, 0.83vw, 12px);
            border-bottom: 3px solid #ff5c00;
        }

        .objectives-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: clamp(16px, 1.67vw, 24px);
        }

        .objective-card {
            background: #f9fafb;
            border-radius: 8px;
            padding: clamp(20px, 2.08vw, 32px);
            border-left: 4px solid #ff5c00;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .objective-card:hover {
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .objective-department {
            font-size: clamp(14px, 1.25vw, 18px);
            font-weight: bold;
            color: #ff5c00;
            margin-bottom: clamp(8px, 0.83vw, 12px);
        }

        .objective-strategy {
            font-size: clamp(13px, 1.04vw, 16px);
            color: #374151;
            margin-bottom: clamp(12px, 1.04vw, 16px);
            line-height: 1.7;
        }

        .objective-metrics {
            margin-bottom: clamp(12px, 1.04vw, 16px);
        }

        .objective-metrics-title {
            font-size: clamp(12px, 1.04vw, 14px);
            font-weight: 600;
            color: #6b7280;
            margin-bottom: clamp(6px, 0.63vw, 8px);
        }

        .objective-metrics-list {
            list-style: none;
            padding: 0;
        }

        .objective-metrics-list li {
            font-size: clamp(11px, 0.94vw, 13px);
            color: #374151;
            padding: clamp(4px, 0.42vw, 6px) 0;
            padding-left: clamp(16px, 1.56vw, 24px);
            position: relative;
        }

        .objective-metrics-list li::before {
            content: '→';
            position: absolute;
            left: 0;
            color: #ff5c00;
        }

        .objective-meta {
            display: flex;
            flex-wrap: wrap;
            gap: clamp(12px, 1.25vw, 16px);
            padding-top: clamp(12px, 1.04vw, 16px);
            border-top: 1px solid #e5e7eb;
            font-size: clamp(11px, 0.94vw, 13px);
            color: #6b7280;
        }

        .objective-meta-item {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .objective-meta-label {
            font-weight: 600;
            color: #6b7280;
        }

        .objective-meta-value {
            color: #374151;
        }

        /* 自定义滚动条样式 */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        html {
            scrollbar-width: thin;
            scrollbar-color: #c1c1c1 #f1f1f1;
        }

        /* 响应式设计 */
        @media (max-width: 1024px) {
            .header-panel {
                flex-direction: column;
                text-align: center;
            }

            .header-text-content {
                text-align: center;
            }

            .header-logo-container {
                margin-top: clamp(32px, 3.13vw, 48px);
            }
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 16px;
            }

            .header-panel {
                padding: clamp(24px, 2.5vw, 32px) clamp(24px, 2.5vw, 32px);
            }

            .core-grid,
            .explanation-grid,
            .objectives-grid {
                grid-template-columns: 1fr;
            }

            .org-container {
                grid-template-columns: 1fr;
            }

            .timeline-wrapper {
                padding: clamp(40px, 4.17vw, 60px) clamp(24px, 2.5vw, 32px);
            }

            .timeline-line {
                left: clamp(60px, 6.25vw, 80px);
                right: clamp(60px, 6.25vw, 80px);
            }

            .timeline-wrapper {
                padding: clamp(60px, 6.25vw, 80px) clamp(24px, 2.5vw, 32px);
            }

            .timeline-start {
                padding: clamp(12px, 1.25vw, 14px) clamp(20px, 2.08vw, 24px);
                font-size: clamp(12px, 1.25vw, 14px);
            }

            .timeline-start-event {
                width: clamp(110px, 11.46vw, 160px);
                transform: translate(-50%, calc(100% + clamp(20px, 2.08vw, 30px)));
            }

            .timeline-start-event .timeline-goal-text,
            .timeline-end-event .timeline-goal-text,
            .timeline-event .timeline-goal-text {
                padding: clamp(10px, 1.04vw, 14px) clamp(12px, 1.25vw, 16px);
                font-size: clamp(12px, 1.25vw, 15px);
            }

            .timeline-end {
                width: clamp(55px, 5.73vw, 70px);
                height: clamp(55px, 5.73vw, 70px);
            }

            .timeline-end-event {
                width: clamp(110px, 11.46vw, 160px);
                transform: translate(50%, calc(100% + clamp(20px, 2.08vw, 30px)));
            }

            .timeline-year-label {
                font-size: clamp(16px, 1.67vw, 22px);
            }

            .timeline-start-event .timeline-year-label,
            .timeline-end-event .timeline-year-label {
                font-size: clamp(16px, 1.67vw, 22px);
            }

            .timeline-items {
                padding: 0 clamp(70px, 7.29vw, 100px);
                min-height: clamp(180px, 18.75vw, 250px);
            }

            .timeline-event {
                width: clamp(100px, 10.42vw, 140px);
            }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <!-- 主内容区域 -->
    <div class="main-content">
        <div class="main-container">
            <!-- 页面标题 -->
            <div class="header">
                <h1 class="header-title">企业蓝图</h1>
            </div>

            <?php if ($strategyData): ?>
                <!-- Header Section -->
                <div class="section">
                    <div class="header-panel">
                        <!-- Left side text content -->
                        <div class="header-text-content">
                            <div class="company-name-large">KUNZZ HOLDINGS</div>
                            <div class="company-subtitle">
                                <span class="company-subtitle-upper">SDN BHD</span> 战略计划
                            </div>
                        </div>

                        <!-- Right side logo -->
                        <div class="header-logo-container">
                            <div class="header-logo">
                                <img src="../images/images/logo.png" alt="KUNZZ HOLDINGS Logo">
                                <div class="logo-reflection"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Timeline Section -->
                <?php if (!empty($strategyData['timeline'])): ?>
                <div class="section">
                    <div class="timeline-container">
                        <div class="timeline-header">
                            <div class="timeline-main-title">以终为始</div>
                            <div class="timeline-subtitle">请明确写出公司真正要去的终点</div>
                        </div>
                        
                        <div class="timeline-wrapper">
                            <!-- SVG Wave Path -->
                            <svg class="timeline-svg-container" viewBox="0 0 1000 400" preserveAspectRatio="none">
                                <defs>
                                    <linearGradient id="waveGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                        <stop offset="0%" style="stop-color:rgba(255, 92, 0, 0.3);stop-opacity:1" />
                                        <stop offset="20%" style="stop-color:#ff5c00;stop-opacity:1" />
                                        <stop offset="80%" style="stop-color:#ff5c00;stop-opacity:1" />
                                        <stop offset="100%" style="stop-color:rgba(255, 92, 0, 0.3);stop-opacity:1" />
                                    </linearGradient>
                                </defs>
                                <path class="timeline-svg-path" d="M 0,200 Q 125,100 250,200 T 500,200 T 750,200 T 1000,200" 
                                      stroke="url(#waveGradient)" 
                                      id="timelinePath"/>
                            </svg>
                            
                            <!-- Start point -->
                            <div class="timeline-start">起始</div>
                            <?php 
                            // Get start year and goal from first timeline item
                            $startItem = !empty($strategyData['timeline']) ? $strategyData['timeline'][0] : null;
                            ?>
                            <?php if ($startItem): ?>
                            <div class="timeline-start-event">
                                <div class="timeline-arrow"></div>
                                <div class="timeline-year-label"><?php echo htmlspecialchars($startItem['year'] ?? ''); ?>年</div>
                                <div class="timeline-goal-text"><?php echo htmlspecialchars($startItem['goal'] ?? ''); ?></div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- End point -->
                            <div class="timeline-end">终点</div>
                            <?php 
                            // Get end year and goal from last timeline item
                            $endItem = !empty($strategyData['timeline']) ? end($strategyData['timeline']) : null;
                            ?>
                            <?php if ($endItem): ?>
                            <div class="timeline-end-event">
                                <div class="timeline-arrow"></div>
                                <div class="timeline-year-label"><?php echo htmlspecialchars($endItem['year'] ?? ''); ?>年</div>
                                <div class="timeline-goal-text"><?php echo htmlspecialchars($endItem['goal'] ?? ''); ?></div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Middle events -->
                            <div class="timeline-items">
                                <?php 
                                // Skip first and last items since they're shown at start/end points
                                $middleItems = !empty($strategyData['timeline']) ? array_slice($strategyData['timeline'], 1, -1) : [];
                                $totalMiddleItems = count($middleItems);
                                foreach ($middleItems as $index => $item): 
                                ?>
                                <div class="timeline-event">
                                    <div class="timeline-arrow"></div>
                                    <div class="timeline-year-label"><?php echo htmlspecialchars($item['year'] ?? ''); ?>年</div>
                                    <div class="timeline-goal-text"><?php echo htmlspecialchars($item['goal'] ?? ''); ?></div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Corporate Core Section -->
                <?php if (!empty($strategyData['corporateCore'])): ?>
                <div class="section">
                    <h2 class="section-title">企业核心</h2>
                    <div class="core-grid">
                        <!-- Mission -->
                        <div class="core-card">
                            <div class="core-card-title">使命 Mission</div>
                            <div class="core-card-content">
                                <?php echo htmlspecialchars($strategyData['corporateCore']['mission'] ?? ''); ?>
                            </div>
                        </div>

                        <!-- Vision -->
                        <div class="core-card">
                            <div class="core-card-title">愿景 Vision</div>
                            <div class="core-card-content">
                                <?php echo htmlspecialchars($strategyData['corporateCore']['vision'] ?? ''); ?>
                            </div>
                        </div>

                        <!-- Culture -->
                        <div class="core-card">
                            <div class="core-card-title">文化 Culture</div>
                            <ul class="core-card-list">
                                <?php if (!empty($strategyData['corporateCore']['culture'])): ?>
                                    <?php foreach ($strategyData['corporateCore']['culture'] as $culture): ?>
                                        <li><?php echo htmlspecialchars($culture); ?></li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>

                        <!-- Values -->
                        <div class="core-card">
                            <div class="core-card-title">价值观 Values</div>
                            <ul class="core-card-list">
                                <?php if (!empty($strategyData['corporateCore']['values'])): ?>
                                    <?php foreach ($strategyData['corporateCore']['values'] as $value): ?>
                                        <li><?php echo htmlspecialchars($value); ?></li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Culture Explanation -->
                <?php if (!empty($strategyData['cultureExplanation'])): ?>
                <div class="section">
                    <h2 class="section-title">文化阐述</h2>
                    <div class="explanation-grid">
                        <?php foreach ($strategyData['cultureExplanation'] as $culture): ?>
                        <div class="explanation-card">
                            <div class="explanation-key"><?php echo htmlspecialchars($culture['key'] ?? ''); ?></div>
                            <div class="explanation-description">
                                <?php echo htmlspecialchars($culture['description'] ?? ''); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Values Explanation -->
                <?php if (!empty($strategyData['valuesExplanation'])): ?>
                <div class="section">
                    <h2 class="section-title">价值观阐述</h2>
                    <div class="explanation-grid">
                        <?php foreach ($strategyData['valuesExplanation'] as $value): ?>
                        <div class="explanation-card">
                            <div class="explanation-key"><?php echo htmlspecialchars($value['key'] ?? ''); ?></div>
                            <div class="explanation-description">
                                <?php echo htmlspecialchars($value['description'] ?? ''); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Organization Structure -->
                <?php if (!empty($strategyData['organizationStructure'])): ?>
                <div class="section">
                    <h2 class="section-title">组织结构</h2>
                    <div class="org-container">
                        <!-- Executives -->
                        <?php if (!empty($strategyData['organizationStructure']['executives'])): ?>
                        <div class="org-section">
                            <div class="org-section-title">管理层</div>
                            <ul class="org-list">
                                <?php foreach ($strategyData['organizationStructure']['executives'] as $exec): ?>
                                <li class="org-list-item">
                                    <div class="org-name"><?php echo htmlspecialchars($exec['name'] ?? ''); ?></div>
                                    <div class="org-title"><?php echo htmlspecialchars($exec['title'] ?? ''); ?></div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <!-- Departments -->
                        <?php if (!empty($strategyData['organizationStructure']['departments'])): ?>
                        <div class="org-section">
                            <div class="org-section-title">部门</div>
                            <ul class="org-list">
                                <?php foreach ($strategyData['organizationStructure']['departments'] as $dept): ?>
                                <li class="org-list-item">
                                    <div class="org-name"><?php echo htmlspecialchars($dept['name'] ?? ''); ?></div>
                                    <div class="org-title"><?php echo htmlspecialchars($dept['head'] ?? ''); ?></div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Strategic Objectives -->
                <?php if (!empty($strategyData['strategicObjectives'])): ?>
                <div class="section">
                    <h2 class="section-title">战略目标</h2>
                    <div class="objectives-container">
                        <?php 
                        // Sort years in ascending order
                        $years = array_keys($strategyData['strategicObjectives']);
                        sort($years, SORT_NUMERIC);
                        
                        foreach ($years as $year): 
                            $objectives = $strategyData['strategicObjectives'][$year];
                            if (empty($objectives)) continue;
                        ?>
                        <div class="year-section">
                            <div class="year-title"><?php echo htmlspecialchars($year); ?> 年</div>
                            <div class="objectives-grid">
                                <?php foreach ($objectives as $objective): ?>
                                <div class="objective-card">
                                    <div class="objective-department">
                                        <?php echo htmlspecialchars($objective['department'] ?? ''); ?>
                                    </div>
                                    <div class="objective-strategy">
                                        <?php echo htmlspecialchars($objective['strategy'] ?? ''); ?>
                                    </div>
                                    
                                    <?php if (!empty($objective['dashboardMetrics'])): ?>
                                    <div class="objective-metrics">
                                        <div class="objective-metrics-title">关键指标：</div>
                                        <ul class="objective-metrics-list">
                                            <?php foreach ($objective['dashboardMetrics'] as $metric): ?>
                                            <li><?php echo htmlspecialchars($metric); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <?php endif; ?>

                                    <div class="objective-meta">
                                        <?php if (!empty($objective['pic'])): ?>
                                        <div class="objective-meta-item">
                                            <span class="objective-meta-label">负责人：</span>
                                            <span class="objective-meta-value"><?php echo htmlspecialchars($objective['pic']); ?></span>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($objective['startDate']) || !empty($objective['endDate'])): ?>
                                        <div class="objective-meta-item">
                                            <span class="objective-meta-label">时间：</span>
                                            <span class="objective-meta-value">
                                                <?php 
                                                if (!empty($objective['startDate']) && !empty($objective['endDate'])) {
                                                    echo date('Y-m-d', strtotime($objective['startDate'])) . ' ~ ' . date('Y-m-d', strtotime($objective['endDate']));
                                                } elseif (!empty($objective['startDate'])) {
                                                    echo date('Y-m-d', strtotime($objective['startDate']));
                                                }
                                                ?>
                                            </span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- 如果没有JSON数据，显示错误信息 -->
                <div class="card">
                    <p style="text-align: center; color: #6b7280; padding: 40px;">
                        无法加载战略计划数据。请确保 corporate_strategy.json 文件存在于backend目录且格式正确。
                    </p>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <script>
        // 时间线动画控制器 - 波浪形路径
        document.addEventListener('DOMContentLoaded', function() {
            const timelineWrapper = document.querySelector('.timeline-wrapper');
            if (!timelineWrapper) return;

            // 获取SVG路径
            const path = document.getElementById('timelinePath');
            if (!path) return;

            // 计算波浪路径上的点
            function getPointAtLength(pathLength, totalLength, index, totalItems) {
                const path = document.getElementById('timelinePath');
                const point = path.getPointAtLength(pathLength);
                return point;
            }

            // 立即尝试初始化（如果元素已在视口中）
            function initTimeline() {
                if (timelineWrapper.getBoundingClientRect().top < window.innerHeight + 200) {
                    animateTimeline(timelineWrapper);
                } else {
                    // 如果不在视口，使用IntersectionObserver
                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                animateTimeline(entry.target);
                                observer.unobserve(entry.target);
                            }
                        });
                    }, {
                        threshold: 0.1,
                        rootMargin: '200px 0px'
                    });
                    observer.observe(timelineWrapper);
                }
            }
            
            // 延迟初始化以确保DOM完全渲染
            setTimeout(initTimeline, 200);

            function animateTimeline(container) {
                // 1. 先显示起始点
                const startPoint = container.querySelector('.timeline-start');
                if (startPoint) {
                    setTimeout(() => {
                        startPoint.classList.add('animate-in');
                    }, 100);
                }

                // 2. 显示波浪路径动画
                const svgPath = container.querySelector('.timeline-svg-path');
                if (svgPath) {
                    setTimeout(() => {
                        svgPath.classList.add('animate-in');
                    }, 400);
                }

                // 3. 显示终点
                const endPoint = container.querySelector('.timeline-end');
                if (endPoint) {
                    setTimeout(() => {
                        endPoint.classList.add('animate-in');
                    }, 800);
                }

                // 4. 逐个显示起始事件
                const startEvent = container.querySelector('.timeline-start-event');
                if (startEvent) {
                    setTimeout(() => {
                        startEvent.classList.add('animate-in');
                    }, 1200);
                }

                // 5. 计算并定位中间事件到波浪路径上
                const events = container.querySelectorAll('.timeline-event');
                if (events.length > 0 && path) {
                    const pathLength = path.getTotalLength();
                    const wrapperWidth = container.offsetWidth;
                    const wrapperHeight = container.offsetHeight;
                    const svgLeftOffset = clamp(80, 8.33, 120);
                    const svgElement = container.querySelector('.timeline-svg-container');
                    const svgRect = svgElement ? svgElement.getBoundingClientRect() : { width: wrapperWidth, height: wrapperHeight };
                    
                    events.forEach((event, index) => {
                        // 计算事件在波浪路径上的位置（0到1之间的值）
                        const progress = (index + 1) / (events.length + 1);
                        const pathPoint = path.getPointAtLength(pathLength * progress);
                        
                        // 获取SVG的viewBox转换
                        const svgWidth = svgRect.width || (wrapperWidth - (svgLeftOffset * 2));
                        const svgHeight = svgRect.height || wrapperHeight;
                        
                        // 将SVG坐标转换为容器坐标
                        const x = svgLeftOffset + (pathPoint.x / 1000) * svgWidth;
                        const y = (pathPoint.y / 400) * svgHeight;
                        
                        // 计算箭头方向（垂直于路径在该点的切线）
                        const nextProgress = Math.min(progress + 0.01, 1);
                        const nextPoint = path.getPointAtLength(pathLength * nextProgress);
                        const angle = Math.atan2(nextPoint.y - pathPoint.y, nextPoint.x - pathPoint.x) * 180 / Math.PI;
                        
                        // 保存角度以供悬停时使用
                        event.dataset.angle = angle;
                        
                        // 设置事件位置
                        event.style.left = x + 'px';
                        event.style.top = y + 'px';
                        event.style.transform = `translate(-50%, -50%) translateY(20px) rotate(${angle}deg)`;
                        
                        // 箭头指向路径（垂直于路径）
                        const arrow = event.querySelector('.timeline-arrow');
                        if (arrow) {
                            arrow.style.transform = `rotate(${angle + 90}deg)`;
                        }
                        
                        // 动画显示
                        setTimeout(() => {
                            event.classList.add('animate-in');
                            event.style.transform = `translate(-50%, -50%) rotate(${angle}deg)`;
                        }, 1600 + (index * 150));
                        
                        // 添加悬停事件
                        event.addEventListener('mouseenter', function() {
                            const angle = parseFloat(this.dataset.angle || 0);
                            this.style.transform = `translate(-50%, -50%) scale(1.15) translateY(-8px) rotate(${angle}deg)`;
                        });
                        
                        event.addEventListener('mouseleave', function() {
                            const angle = parseFloat(this.dataset.angle || 0);
                            this.style.transform = `translate(-50%, -50%) rotate(${angle}deg)`;
                        });
                    });
                }

                // 6. 最后显示终点事件
                const endEvent = container.querySelector('.timeline-end-event');
                if (endEvent) {
                    setTimeout(() => {
                        endEvent.classList.add('animate-in');
                    }, 1600 + (events.length * 150) + 200);
                }
            }

            // 辅助函数：clamp
            function clamp(min, vw, max) {
                return Math.min(Math.max(window.innerWidth * vw / 100, min), max);
            }

            // 增强交互：点击事件卡片时的高亮效果
            const eventCards = document.querySelectorAll('.timeline-goal-text');
            eventCards.forEach(card => {
                card.addEventListener('click', function() {
                    // 移除其他卡片的高亮
                    eventCards.forEach(c => c.classList.remove('active'));
                    // 添加当前卡片的高亮
                    this.classList.add('active');
                });
            });

            } // 关闭animateTimeline函数
        });

        // 添加卡片激活状态的样式（通过内联样式或CSS类）
        const style = document.createElement('style');
        style.textContent = `
            .timeline-goal-text.active {
                background: rgba(255, 92, 0, 0.1) !important;
                border-color: rgba(255, 92, 0, 0.5) !important;
                box-shadow: 0 8px 24px rgba(255, 92, 0, 0.25) !important;
                transform: translateY(-3px) scale(1.02) !important;
            }
        `;
        document.head.appendChild(style);
    </script>

</body>
</html>
<?php
ob_end_flush();
?>

