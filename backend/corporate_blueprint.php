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
            overflow-y: auto;
            line-height: 1.6;
        }
        
        body {
            overflow-x: hidden;
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
            padding: clamp(50px, 5.21vw, 80px) clamp(20px, 2.08vw, 30px);
            overflow: visible;
            background: 
                radial-gradient(circle at 20% 30%, rgba(255, 92, 0, 0.05) 0%, transparent 40%),
                radial-gradient(circle at 80% 70%, rgba(255, 215, 0, 0.03) 0%, transparent 40%),
                repeating-linear-gradient(
                    0deg,
                    transparent,
                    transparent 20px,
                    rgba(255, 92, 0, 0.02) 20px,
                    rgba(255, 92, 0, 0.02) 21px
                ),
                repeating-linear-gradient(
                    90deg,
                    transparent,
                    transparent 20px,
                    rgba(255, 92, 0, 0.02) 20px,
                    rgba(255, 92, 0, 0.02) 21px
                );
            border-radius: 12px;
            min-height: clamp(300px, 31.25vw, 450px);
            width: 100%;
            /* 确保容器包含所有子元素（包括绝对定位的里程碑和SVG） */
            isolation: isolate;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Map-style SVG path container */
        .map-timeline-svg {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 83.33%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        /* Path for the route */
        .map-route-path {
            fill: none;
            stroke: #ff5c00;
            stroke-width: 3;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-dasharray: 1000;
            stroke-dashoffset: 1000;
            filter: drop-shadow(0 2px 4px rgba(255, 92, 0, 0.3));
            transition: stroke-dashoffset 2s ease-in-out;
        }

        .map-route-path.animate-in {
            stroke-dashoffset: 0;
        }

        /* Route glow effect */
        .map-route-glow {
            fill: none;
            stroke: rgba(255, 92, 0, 0.3);
            stroke-width: 6;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        /* Horizontal timeline line */
        .timeline-line {
            position: absolute;
            top: 50%;
            left: clamp(80px, 8.33vw, 120px);
            right: clamp(80px, 8.33vw, 120px);
            height: 5px;
            background: linear-gradient(90deg, 
                rgba(255, 92, 0, 0.3) 0%, 
                #ff5c00 20%, 
                #ff5c00 80%, 
                rgba(255, 92, 0, 0.3) 100%);
            transform: translateY(-50%) scaleX(0);
            transform-origin: left center;
            z-index: 1;
            border-radius: 3px;
            box-shadow: 0 2px 8px rgba(255, 92, 0, 0.2);
            transition: transform 1.2s cubic-bezier(0.65, 0, 0.35, 1);
        }

        .timeline-line.animate-in {
            transform: translateY(-50%) scaleX(1);
        }

        .timeline-line::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, 
                transparent 0%, 
                rgba(255, 255, 255, 0.4) 50%, 
                transparent 100%);
            border-radius: 3px;
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
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
            z-index: 3;
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
            bottom: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: clamp(140px, 14.58vw, 200px);
            transform: translate(-50%, calc(100% + clamp(28px, 2.92vw, 40px))) translateY(20px);
            opacity: 0;
            transition: transform 0.8s cubic-bezier(0.34, 1.56, 0.64, 1),
                        opacity 0.8s ease,
                        filter 0.3s ease;
        }

        .timeline-start-event.animate-in {
            opacity: 1;
            transform: translate(-50%, calc(100% + clamp(28px, 2.92vw, 40px))) translateY(0);
        }

        .timeline-start-event:hover {
            transform: translate(-50%, calc(100% + clamp(28px, 2.92vw, 40px))) translateY(-5px) scale(1.08);
            filter: drop-shadow(0 12px 24px rgba(255, 92, 0, 0.25));
        }

        .timeline-start-event .timeline-arrow {
            width: 0;
            height: 0;
            margin-bottom: clamp(10px, 1.04vw, 14px);
            border-left: clamp(9px, 0.94vw, 13px) solid transparent;
            border-right: clamp(9px, 0.94vw, 13px) solid transparent;
            border-bottom: clamp(14px, 1.46vw, 18px) solid #000000;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
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
            z-index: 3;
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
            bottom: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: clamp(140px, 14.58vw, 200px);
            transform: translate(50%, calc(100% + clamp(28px, 2.92vw, 40px))) translateY(20px);
            opacity: 0;
            transition: transform 0.8s cubic-bezier(0.34, 1.56, 0.64, 1),
                        opacity 0.8s ease,
                        filter 0.3s ease;
        }

        .timeline-end-event.animate-in {
            opacity: 1;
            transform: translate(50%, calc(100% + clamp(28px, 2.92vw, 40px))) translateY(0);
        }

        .timeline-end-event:hover {
            transform: translate(50%, calc(100% + clamp(28px, 2.92vw, 40px))) translateY(-5px) scale(1.08);
            filter: drop-shadow(0 12px 24px rgba(255, 92, 0, 0.25));
        }

        .timeline-end-event .timeline-arrow {
            width: 0;
            height: 0;
            margin-bottom: clamp(10px, 1.04vw, 14px);
            border-left: clamp(9px, 0.94vw, 13px) solid transparent;
            border-right: clamp(9px, 0.94vw, 13px) solid transparent;
            border-bottom: clamp(14px, 1.46vw, 18px) solid #000000;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
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

        /* Map milestone marker */
        .map-milestone {
            position: absolute;
            z-index: 10;
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
            transform: translateX(-50%) translateY(calc(-1 * clamp(18px, 1.88vw, 25px))) scale(0);
            opacity: 0;
            transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1),
                        opacity 0.5s ease;
            /* top位置对应路径上的点，pin尖端需要对齐到这里，所以向上偏移半个pin高度 */
        }

        .map-milestone.animate-in {
            opacity: 1;
            transform: translateX(-50%) translateY(calc(-1 * clamp(18px, 1.88vw, 25px))) scale(1);
        }

        /* Milestone pin/marker icon */
        .milestone-pin {
            width: clamp(36px, 3.75vw, 50px);
            height: clamp(36px, 3.75vw, 50px);
            background: linear-gradient(135deg, #ff5c00 0%, #ff8c42 100%);
            border-radius: 50% 50% 50% 0;
            transform: rotate(-45deg);
            position: relative;
            box-shadow: 
                0 3px 10px rgba(255, 92, 0, 0.4),
                0 2px 5px rgba(0, 0, 0, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
            border: 2px solid #ffffff;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            z-index: 2;
            /* Pin旋转-45度后，尖端在底部中心。通过父容器的translateY向上偏移，让尖端对齐到路径 */
        }
        
        /* 使用伪元素或调整定位来让pin尖端对齐 */
        .map-milestone::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            width: clamp(36px, 3.75vw, 50px);
            height: clamp(36px, 3.75vw, 50px);
            transform: translateX(-50%);
            pointer-events: none;
        }

        .map-milestone:hover .milestone-pin {
            transform: rotate(-45deg) scale(1.15);
            box-shadow: 
                0 5px 18px rgba(255, 92, 0, 0.6),
                0 3px 8px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.4);
        }

        .milestone-pin::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(45deg);
            width: clamp(16px, 1.67vw, 22px);
            height: clamp(16px, 1.67vw, 22px);
            background: #ffffff;
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .milestone-pin::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(45deg);
            width: clamp(10px, 1.04vw, 14px);
            height: clamp(10px, 1.04vw, 14px);
            background: linear-gradient(135deg, #ff5c00 0%, #ff8c42 100%);
            border-radius: 50%;
        }

        /* Milestone content card */
        .milestone-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            padding: clamp(10px, 1.04vw, 14px) clamp(14px, 1.46vw, 18px);
            box-shadow: 
                0 6px 20px rgba(0, 0, 0, 0.12),
                0 3px 10px rgba(255, 92, 0, 0.1);
            border: 2px solid rgba(255, 92, 0, 0.2);
            min-width: clamp(120px, 12.5vw, 160px);
            text-align: center;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            position: relative;
        }

        /* Cards above the pin */
        .milestone-top .milestone-card {
            margin-bottom: clamp(28px, 2.92vw, 40px);
        }

        .milestone-top .milestone-card::before {
            content: '';
            position: absolute;
            bottom: -7px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 7px solid transparent;
            border-right: 7px solid transparent;
            border-top: 7px solid rgba(255, 92, 0, 0.2);
        }

        /* Cards below the pin */
        .milestone-bottom .milestone-card {
            margin-top: clamp(28px, 2.92vw, 40px);
        }

        .milestone-bottom .milestone-card::before {
            content: '';
            position: absolute;
            top: -7px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 7px solid transparent;
            border-right: 7px solid transparent;
            border-bottom: 7px solid rgba(255, 92, 0, 0.2);
        }

        .map-milestone:hover .milestone-card {
            transform: translateY(-5px);
            box-shadow: 
                0 12px 32px rgba(0, 0, 0, 0.18),
                0 6px 16px rgba(255, 92, 0, 0.15);
            border-color: rgba(255, 92, 0, 0.4);
        }

        .milestone-top:hover .milestone-card {
            transform: translateY(5px);
        }


        .milestone-year {
            font-size: clamp(16px, 1.67vw, 22px);
            font-weight: 800;
            background: linear-gradient(135deg, #ff5c00 0%, #ff8c42 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: clamp(6px, 0.63vw, 8px);
            letter-spacing: 0.5px;
        }

        .milestone-goal {
            font-size: clamp(12px, 1.25vw, 14px);
            color: #2c3e50;
            line-height: 1.5;
            font-weight: 500;
        }

        .timeline-arrow {
            width: 0;
            height: 0;
            margin-bottom: clamp(10px, 1.04vw, 14px);
            transition: filter 0.3s ease;
        }

        .timeline-event:nth-child(even) .timeline-arrow {
            margin-bottom: 0;
            margin-top: clamp(10px, 1.04vw, 14px);
            order: -1;
        }

        /* Odd items (below timeline) - arrow points up */
        .timeline-event:nth-child(odd) .timeline-arrow {
            border-left: clamp(9px, 0.94vw, 13px) solid transparent;
            border-right: clamp(9px, 0.94vw, 13px) solid transparent;
            border-bottom: clamp(14px, 1.46vw, 18px) solid #000000;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        }

        /* Even items (above timeline) - arrow points down */
        .timeline-event:nth-child(even) .timeline-arrow {
            border-left: clamp(9px, 0.94vw, 13px) solid transparent;
            border-right: clamp(9px, 0.94vw, 13px) solid transparent;
            border-top: clamp(14px, 1.46vw, 18px) solid #000000;
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
                padding: clamp(40px, 4.17vw, 60px) clamp(15px, 1.56vw, 20px);
                min-height: clamp(320px, 33.33vw, 450px);
            }

            .map-milestone {
                transform: translateX(-50%) translateY(calc(-1 * clamp(15px, 1.56vw, 21px))) scale(0.85);
            }
            
            .map-milestone.animate-in {
                transform: translateX(-50%) translateY(calc(-1 * clamp(15px, 1.56vw, 21px))) scale(0.85);
            }

            .milestone-pin {
                width: clamp(30px, 3.13vw, 42px);
                height: clamp(30px, 3.13vw, 42px);
            }

            .milestone-card {
                min-width: clamp(100px, 10.42vw, 140px);
                padding: clamp(8px, 0.83vw, 12px) clamp(12px, 1.25vw, 16px);
            }

            .milestone-year {
                font-size: clamp(14px, 1.46vw, 18px);
            }

            .milestone-goal {
                font-size: clamp(11px, 1.15vw, 13px);
            }

            .milestone-top .milestone-card {
                margin-bottom: clamp(24px, 2.5vw, 32px);
            }

            .milestone-bottom .milestone-card {
                margin-top: clamp(24px, 2.5vw, 32px);
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
                            <!-- Map-style SVG path -->
                            <svg class="map-timeline-svg" viewBox="0 0 600 600" preserveAspectRatio="none">
                                <defs>
                                    <linearGradient id="routeGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                        <stop offset="0%" style="stop-color:rgba(255, 92, 0, 0.3);stop-opacity:1" />
                                        <stop offset="50%" style="stop-color:#ff5c00;stop-opacity:1" />
                                        <stop offset="100%" style="stop-color:rgba(255, 92, 0, 0.3);stop-opacity:1" />
                                    </linearGradient>
                                </defs>
                                <!-- Route glow -->
                                <path class="map-route-glow" d="M 15 300 Q 180 180, 300 300 Q 420 420, 585 300" stroke="url(#routeGradient)"/>
                                <!-- Main route path -->
                                <path class="map-route-path" d="M 15 300 Q 180 180, 300 300 Q 420 420, 585 300" stroke="#ff5c00"/>
                            </svg>

                            <!-- Map milestones -->
                            <?php 
                            if (!empty($strategyData['timeline'])): 
                                $totalItems = count($strategyData['timeline']);
                                
                                // Function to calculate point on quadratic Bezier curve
                                // B(t) = (1-t)²P₀ + 2(1-t)tP₁ + t²P₂
                                function bezierQuad($t, $p0, $p1, $p2) {
                                    $mt = 1 - $t;
                                    return [
                                        $mt * $mt * $p0[0] + 2 * $mt * $t * $p1[0] + $t * $t * $p2[0],
                                        $mt * $mt * $p0[1] + 2 * $mt * $t * $p1[1] + $t * $t * $p2[1]
                                    ];
                                }
                                
                                // SVG path: M 15 300 Q 180 180, 300 300 Q 420 420, 585 300
                                // ViewBox: 600x600
                                // First curve: M 15 300 Q 180 180, 300 300
                                $p0_1 = [15, 300];    // Start point
                                $p1_1 = [180, 180];   // Control point
                                $p2_1 = [300, 300];   // End point
                                
                                // Second curve: Q 420 420, 585 300
                                $p0_2 = [300, 300];   // Start (same as p2_1)
                                $p1_2 = [420, 420];   // Control point
                                $p2_2 = [585, 300];   // End point
                                
                                foreach ($strategyData['timeline'] as $index => $item):
                                    $t = $totalItems > 1 ? $index / ($totalItems - 1) : 0; // 0 to 1
                                    
                                    // Determine which curve segment this point belongs to
                                    // Split the path roughly in half
                                    if ($t <= 0.5) {
                                        // First half: use first Bezier curve
                                        $t_curve = $t * 2; // Map to 0-1 for first curve
                                        $point = bezierQuad($t_curve, $p0_1, $p1_1, $p2_1);
                                    } else {
                                        // Second half: use second Bezier curve
                                        $t_curve = ($t - 0.5) * 2; // Map to 0-1 for second curve
                                        $point = bezierQuad($t_curve, $p0_2, $p1_2, $p2_2);
                                    }
                                    
                                    // Convert SVG coordinates (0-600, 0-600) to percentage
                                    // SVG is 83.33% width and centered, so adjust left position accordingly
                                    $svgWidthPercent = 83.33;
                                    $svgLeftOffset = (100 - $svgWidthPercent) / 2; // 8.335%
                                    $xPercentRelative = ($point[0] / 600) * 100; // Position within SVG (0-100%)
                                    $xPercent = $svgLeftOffset + ($xPercentRelative * $svgWidthPercent / 100); // Actual position in container
                                    $yPercent = ($point[1] / 600) * 100;
                                    
                                    // Alternate card position (above or below pin) for better layout
                                    $cardPosition = ($index % 2 == 0) ? 'top' : 'bottom';
                            ?>
                            <div class="map-milestone milestone-<?php echo $cardPosition; ?>" 
                                 style="left: <?php echo $xPercent; ?>%; top: <?php echo $yPercent; ?>%;"
                                 data-year="<?php echo htmlspecialchars($item['year'] ?? ''); ?>">
                                <div class="milestone-pin"></div>
                                <div class="milestone-card">
                                    <div class="milestone-year"><?php echo htmlspecialchars($item['year'] ?? ''); ?>年</div>
                                    <div class="milestone-goal"><?php echo htmlspecialchars($item['goal'] ?? ''); ?></div>
                                </div>
                            </div>
                            <?php 
                                endforeach; 
                            endif; 
                            ?>

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
        // 时间线动画控制器
        document.addEventListener('DOMContentLoaded', function() {
            const timelineWrapper = document.querySelector('.timeline-wrapper');
            if (!timelineWrapper) return;

            // 创建 IntersectionObserver 观察时间线容器
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        // 触发时间线动画
                        animateTimeline(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.3,
                rootMargin: '0px 0px -100px 0px'
            });

            observer.observe(timelineWrapper);

            function animateTimeline(container) {
                // 1. 先绘制路径
                const routePath = container.querySelector('.map-route-path');
                if (routePath) {
                    setTimeout(() => {
                        routePath.classList.add('animate-in');
                    }, 200);
                }

                // 2. 逐个显示里程碑（按路径顺序）
                const milestones = container.querySelectorAll('.map-milestone');
                milestones.forEach((milestone, index) => {
                    setTimeout(() => {
                        milestone.classList.add('animate-in');
                    }, 1000 + (index * 200)); // 路径动画后开始显示里程碑
                });
            }

            // 添加里程碑悬停时的路径高亮效果
            const milestones = document.querySelectorAll('.map-milestone');
            milestones.forEach(milestone => {
                milestone.addEventListener('mouseenter', function() {
                    this.style.zIndex = '20';
                });
                milestone.addEventListener('mouseleave', function() {
                    this.style.zIndex = '10';
                });
            });
        });
    </script>

</body>
</html>
<?php
ob_end_flush();
?>

