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

        .section {
            margin-bottom: clamp(24px, 2.08vw, 40px);
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

        /* Header Section - 新设计（匹配图片） */
        .header-panel {
            background: linear-gradient(135deg, 
                #fef9f5 0%, 
                #fff5eb 30%, 
                #ffe8d6 60%, 
                #ffddd0 100%);
            border-radius: clamp(16px, 1.67vw, 24px);
            padding: clamp(30px, 3.13vw, 45px) clamp(30px, 3.13vw, 45px);
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            overflow: hidden;
            min-height: clamp(280px, 29.17vw, 400px);
        }

        /* 柔和的圆形模糊背景效果 */
        .header-panel::before {
            content: '';
            position: absolute;
            top: -20%;
            left: -10%;
            width: 40%;
            height: 40%;
            background: radial-gradient(circle, rgba(255, 200, 150, 0.4) 0%, transparent 70%);
            border-radius: 50%;
            filter: blur(60px);
            z-index: 0;
        }

        .header-panel::after {
            content: '';
            position: absolute;
            bottom: -15%;
            right: -5%;
            width: 35%;
            height: 35%;
            background: radial-gradient(circle, rgba(255, 180, 120, 0.3) 0%, transparent 70%);
            border-radius: 50%;
            filter: blur(50px);
            z-index: 0;
        }

        /* 飘动的模糊圆球 - 更明显 */
        .floating-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(35px);
            z-index: 0;
            pointer-events: none;
            opacity: 0.6;
        }

        .floating-orb-1 {
            width: clamp(150px, 15.63vw, 220px);
            height: clamp(150px, 15.63vw, 220px);
            background: radial-gradient(circle, rgba(255, 180, 120, 0.8) 0%, rgba(255, 160, 100, 0.5) 40%, rgba(255, 140, 80, 0.2) 70%, transparent 100%);
            top: 10%;
            left: 15%;
            animation: float1 20s ease-in-out infinite;
        }

        .floating-orb-2 {
            width: clamp(130px, 13.54vw, 180px);
            height: clamp(130px, 13.54vw, 180px);
            background: radial-gradient(circle, rgba(255, 160, 100, 0.75) 0%, rgba(255, 140, 80, 0.45) 40%, rgba(255, 120, 60, 0.2) 70%, transparent 100%);
            top: 60%;
            right: 20%;
            animation: float2 25s ease-in-out infinite;
        }

        .floating-orb-3 {
            width: clamp(110px, 11.46vw, 160px);
            height: clamp(110px, 11.46vw, 160px);
            background: radial-gradient(circle, rgba(255, 200, 140, 0.7) 0%, rgba(255, 180, 120, 0.4) 40%, rgba(255, 160, 100, 0.2) 70%, transparent 100%);
            bottom: 20%;
            left: 25%;
            animation: float3 18s ease-in-out infinite;
        }

        .floating-orb-4 {
            width: clamp(120px, 12.5vw, 170px);
            height: clamp(120px, 12.5vw, 170px);
            background: radial-gradient(circle, rgba(255, 170, 110, 0.7) 0%, rgba(255, 150, 90, 0.4) 40%, rgba(255, 130, 70, 0.2) 70%, transparent 100%);
            top: 30%;
            right: 35%;
            animation: float4 22s ease-in-out infinite;
        }

        .floating-orb-5 {
            width: clamp(100px, 10.42vw, 140px);
            height: clamp(100px, 10.42vw, 140px);
            background: radial-gradient(circle, rgba(255, 190, 130, 0.65) 0%, rgba(255, 170, 110, 0.4) 40%, rgba(255, 150, 90, 0.2) 70%, transparent 100%);
            bottom: 40%;
            right: 10%;
            animation: float5 24s ease-in-out infinite;
        }

        /* 飘动动画 */
        @keyframes float1 {
            0%, 100% {
                transform: translate(0, 0) scale(1);
            }
            25% {
                transform: translate(30px, -40px) scale(1.1);
            }
            50% {
                transform: translate(-20px, -60px) scale(0.9);
            }
            75% {
                transform: translate(-30px, -20px) scale(1.05);
            }
        }

        @keyframes float2 {
            0%, 100% {
                transform: translate(0, 0) scale(1);
            }
            33% {
                transform: translate(-40px, 30px) scale(1.15);
            }
            66% {
                transform: translate(25px, -35px) scale(0.85);
            }
        }

        @keyframes float3 {
            0%, 100% {
                transform: translate(0, 0) scale(1);
            }
            30% {
                transform: translate(35px, 25px) scale(1.2);
            }
            60% {
                transform: translate(-25px, 40px) scale(0.8);
            }
            90% {
                transform: translate(15px, -15px) scale(1.1);
            }
        }

        @keyframes float4 {
            0%, 100% {
                transform: translate(0, 0) scale(1);
            }
            20% {
                transform: translate(-30px, -25px) scale(1.1);
            }
            40% {
                transform: translate(20px, -45px) scale(0.9);
            }
            60% {
                transform: translate(35px, 20px) scale(1.15);
            }
            80% {
                transform: translate(-15px, 30px) scale(0.95);
            }
        }

        @keyframes float5 {
            0%, 100% {
                transform: translate(0, 0) scale(1);
            }
            25% {
                transform: translate(20px, 35px) scale(1.05);
            }
            50% {
                transform: translate(-35px, 20px) scale(0.9);
            }
            75% {
                transform: translate(25px, -30px) scale(1.1);
            }
        }

        /* Logo 容器 - 居中显示 */
        .header-logo-container {
            position: relative;
            z-index: 2;
            margin-bottom: clamp(20px, 2.08vw, 28px);
            width: clamp(100px, 10.42vw, 140px);
            height: clamp(100px, 10.42vw, 140px);
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
            position: relative;
            z-index: 3;
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
            max-width: 100%;
            max-height: 100%;
            visibility: visible;
            opacity: 1;
        }

        /* Logo 加载失败时的占位符 */
        .logo-fallback {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #ff5c00;
            border-radius: 50%;
            z-index: 1;
            display: none;
        }

        /* 文本内容容器 */
        .header-text-content {
            position: relative;
            z-index: 2;
            text-align: center;
            width: 100%;
        }

        /* 英文公司名 */
        .company-name-large {
            font-size: clamp(24px, 2.5vw, 40px);
            font-weight: 700;
            color: #000000;
            margin-bottom: clamp(12px, 1.25vw, 18px);
            letter-spacing: 2px;
            line-height: 1.2;
            text-transform: uppercase;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Microsoft YaHei', sans-serif;
        }

        /* 橙色横线 */
        .company-name-large::after {
            content: '';
            display: block;
            /* 居中且不占满整行 */
            width: clamp(240px, 35vw, 520px);
            height: 3px;
            background: #ff5c00;
            margin: clamp(10px, 1.04vw, 14px) auto 0;
            border-radius: 2px;
        }

        /* 中文文字 */
        .company-subtitle {
            font-size: clamp(18px, 1.88vw, 28px);
            font-weight: 700;
            color: #000000;
            margin-top: clamp(12px, 1.25vw, 18px);
            letter-spacing: 1px;
            line-height: 1.4;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Microsoft YaHei', sans-serif;
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
            font-size: clamp(24px, 2.6vw, 50px);
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
            width: 200px;
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
            padding: 0 clamp(20px, 2.08vw, 30px) clamp(50px, 5.21vw, 80px) clamp(20px, 2.08vw, 30px);
            overflow: visible;
            background: 
                radial-gradient(circle at 20% 30%, rgba(255, 92, 0, 0.05) 0%, transparent 40%),
                radial-gradient(circle at 80% 70%, rgba(255, 215, 0, 0.03) 0%, transparent 40%),
                repeating-linear-gradient(
                    0deg,
                    transparent,
                    transparent 20px,
                    rgba(255, 92, 0, 0.08) 20px,
                    rgba(255, 92, 0, 0.08) 21px
                ),
                repeating-linear-gradient(
                    90deg,
                    transparent,
                    transparent 20px,
                    rgba(255, 92, 0, 0.08) 20px,
                    rgba(255, 92, 0, 0.08) 21px
                );
            border-radius: 12px;
            min-height: clamp(250px, 26.04vw, 380px);
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
            width: clamp(120px, 12.5vw, 160px);
            box-sizing: border-box;
            text-align: center;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            position: relative;
        }

        /* Cards above the pin */
        .milestone-top .milestone-card {
            margin-bottom: 10px;
        }

        /* Cards below the pin */
        .milestone-bottom .milestone-card {
            margin-top: 10px;
        }

        /* All milestone cards have arrow pointing upward */
        .milestone-card::before {
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
            z-index: 1;
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

        /* Corporate Core Section - 新设计（参考图片） */
        .core-header {
            text-align: center;
            margin-bottom: clamp(32px, 3.33vw, 40px);
        }

        .core-main-title {
            font-size: clamp(24px, 2.6vw, 40px);
            font-weight: 800;
            color: #ff5c00;
            letter-spacing: 2px;
            position: relative;
            display: inline-block;
            padding-bottom: clamp(10px, 1.04vw, 14px);
        }

        .core-main-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 200px;
            height: 3px;
            background: linear-gradient(90deg, transparent, #ff5c00, transparent);
            border-radius: 2px;
        }

        .core-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: clamp(16px, 1.67vw, 20px);
        }

        .core-card {
            display: grid;
            grid-template-columns: auto 1fr;
            align-items: stretch;
            background: #ffffff;
            border-radius: 12px;
            border: 2px solid #ffe0cc;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
            transition: box-shadow 0.25s ease, transform 0.25s ease;
        }

        .core-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(255, 92, 0, 0.18);
        }

        /* 左侧橙色编号块 */
        .core-card-number {
            background: #ff5c00;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 clamp(18px, 1.88vw, 24px);
            font-size: clamp(26px, 2.71vw, 36px);
            font-weight: 800;
            letter-spacing: 2px;
            min-width: clamp(70px, 7.29vw, 90px);
        }

        /* 右侧内容区域 */
        .core-card-content-wrapper {
            padding: clamp(18px, 1.88vw, 22px) clamp(20px, 2.08vw, 26px);
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: clamp(6px, 0.63vw, 10px);
        }

        .core-card-title {
            font-size: clamp(16px, 1.67vw, 22px);
            font-weight: 700;
            color: #111827;
        }

        .core-card-content {
            font-size: clamp(14px, 1.35vw, 18px);
            color: #4b5563;
            line-height: 1.6;
        }

        @media (max-width: 900px) {
            .core-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Culture Explanation Section - 新设计（参考图片） */
        .culture-explanation-header {
            text-align: center;
            margin-bottom: clamp(40px, 4.17vw, 60px);
            position: relative;
        }

        .culture-explanation-title-cn {
            font-size: clamp(24px, 2.6vw, 40px);
            font-weight: 800;
            color: #ff5c00;
            letter-spacing: 2px;
            position: relative;
            display: inline-block;
        }

        .culture-explanation-title-cn::after {
            content: '';
            position: absolute;
            bottom: -12px;
            left: 50%;
            transform: translateX(-50%);
            width: 200px;
            height: 3px;
            background: linear-gradient(90deg, transparent, #ff5c00, transparent);
            border-radius: 2px;
        }

        .culture-explanation-title-en {
            display: none;
        }

        .culture-explanation-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: clamp(16px, 1.67vw, 20px);
        }

        .culture-explanation-card {
            background: #fffaf3;
            border-radius: 10px;
            border: 1px solid #f6c99f;
            padding: clamp(18px, 1.88vw, 24px);
            display: flex;
            flex-direction: column;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
            transition: box-shadow 0.25s ease, transform 0.25s ease;
        }

        .culture-explanation-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(255, 92, 0, 0.14);
        }

        .culture-explanation-number {
            font-size: clamp(32px, 3.33vw, 48px);
            font-weight: 800;
            color: #ff5c00;
            line-height: 1;
            margin-bottom: clamp(16px, 1.67vw, 24px);
        }

        .culture-explanation-key {
            font-size: clamp(18px, 1.88vw, 24px);
            font-weight: 800;
            color: #000000;
            margin-bottom: clamp(12px, 1.25vw, 16px);
            line-height: 1.4;
        }

        .culture-explanation-description {
            font-size: clamp(13px, 1.35vw, 16px);
            color: #374151;
            line-height: 1.8;
            margin-bottom: clamp(20px, 2.08vw, 28px);
            flex-grow: 1;
        }

        /* 评分标准部分（表格风格，逐级加深，仅色条+文字） */
        .culture-scoring {
            margin-top: auto;
            background: #ffffff;
            border: 1px solid #f6c99f;
            border-radius: 8px;
            overflow: hidden;
        }

        .culture-scoring-item {
            padding: clamp(10px, 1.04vw, 12px) clamp(12px, 1.25vw, 16px);
            display: grid;
            grid-template-columns: auto 1fr;
            column-gap: clamp(8px, 0.83vw, 12px);
            align-items: flex-start;
            border-top: 1px solid #f6c99f;
            background: #ffffff;
        }
        .culture-scoring-item:first-child {
            border-top: none;
        }

        .culture-scoring-point {
            font-size: clamp(12px, 1.25vw, 15px);
            font-weight: 800;
            color: #ff5c00;
            line-height: 1.3;
            white-space: nowrap;
            padding-left: 4px;
            border-left: 4px solid transparent;
        }

        .culture-scoring-description {
            font-size: clamp(11px, 1.15vw, 14px);
            color: #4b5563;
            line-height: 1.6;
        }

        /* 1-5 分颜色逐级加深（整块背景 + 左侧色条 + 文字）
           注意有一个标题 div，评分项从第 2 个子元素开始计数 */
        .culture-scoring-item:nth-of-type(2) {
            background: #fffbf5;
        }
        .culture-scoring-item:nth-of-type(2) .culture-scoring-point {
            color: #ffcc99;
            border-left-color: #ffcc99;
        }

        .culture-scoring-item:nth-of-type(3) {
            background: #fff7ea;
        }
        .culture-scoring-item:nth-of-type(3) .culture-scoring-point {
            color: #ffb266;
            border-left-color: #ffb266;
        }

        .culture-scoring-item:nth-of-type(4) {
            background: #fff0d8;
        }
        .culture-scoring-item:nth-of-type(4) .culture-scoring-point {
            color: #ff9933;
            border-left-color: #ff9933;
        }

        .culture-scoring-item:nth-of-type(5) {
            background: #fbe3bb;
        }
        .culture-scoring-item:nth-of-type(5) .culture-scoring-point {
            color: #ff7f1a;
            border-left-color: #ff7f1a;
        }

        .culture-scoring-item:nth-of-type(6) {
            background: #ffd389;
        }
        .culture-scoring-item:nth-of-type(6) .culture-scoring-point {
            color: #ff6600;
            border-left-color: #ff6600;
        }

        /* Values Explanation - 复用与 Culture 相同的标题样式 */
        .values-explanation-header {
            text-align: center;
            margin-bottom: clamp(40px, 4.17vw, 60px);
            position: relative;
        }

        .values-explanation-title-cn {
            font-size: clamp(24px, 2.6vw, 40px);
            font-weight: 800;
            color: #ff5c00;
            letter-spacing: 2px;
            position: relative;
            display: inline-block;
        }

        .values-explanation-title-cn::after {
            content: '';
            position: absolute;
            bottom: -12px;
            left: 50%;
            transform: translateX(-50%);
            width: 200px;
            height: 3px;
            background: linear-gradient(90deg, transparent, #ff5c00, transparent);
            border-radius: 2px;
        }

        /* 响应式设计 */
        @media (max-width: 1200px) {
            .culture-explanation-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .culture-explanation-grid {
                grid-template-columns: 1fr;
            }
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
                padding: clamp(25px, 2.6vw, 35px) clamp(25px, 2.6vw, 35px);
                min-height: clamp(240px, 25vw, 320px);
            }

            .header-logo-container {
                width: clamp(90px, 9.38vw, 120px);
                height: clamp(90px, 9.38vw, 120px);
                margin-bottom: clamp(18px, 1.88vw, 24px);
            }
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 16px;
            }

            .header-panel {
                padding: clamp(20px, 2.08vw, 30px) clamp(20px, 2.08vw, 30px);
                min-height: clamp(220px, 22.92vw, 280px);
            }

            .header-logo-container {
                width: clamp(80px, 8.33vw, 100px);
                height: clamp(80px, 8.33vw, 100px);
                margin-bottom: clamp(16px, 1.67vw, 20px);
            }

            .company-name-large {
                font-size: clamp(22px, 2.29vw, 36px);
                letter-spacing: 1px;
            }

            .company-subtitle {
                font-size: clamp(16px, 1.67vw, 24px);
            }

            .core-grid,
            .culture-explanation-grid,
            .explanation-grid,
            .objectives-grid {
                grid-template-columns: 1fr;
            }

            .org-container {
                grid-template-columns: 1fr;
            }

            .timeline-wrapper {
                padding: 0 clamp(15px, 1.56vw, 20px) clamp(40px, 4.17vw, 60px) clamp(15px, 1.56vw, 20px);
                min-height: clamp(260px, 27.08vw, 380px);
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
                width: clamp(100px, 10.42vw, 140px);
                padding: clamp(8px, 0.83vw, 12px) clamp(12px, 1.25vw, 16px);
                box-sizing: border-box;
            }

            .milestone-year {
                font-size: clamp(14px, 1.46vw, 18px);
            }

            .milestone-goal {
                font-size: clamp(11px, 1.15vw, 13px);
            }

            .milestone-top .milestone-card {
                margin-bottom: 10px;
            }

            .milestone-bottom .milestone-card {
                margin-top: 10px;
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

        /* Binary Tree 组织架构样式 - 横向二叉树结构 */
        .mermaid-org-chart {
            background:
                radial-gradient(circle at 10% 20%, rgba(255, 92, 0, 0.02) 0, transparent 55%),
                radial-gradient(circle at 80% 80%, rgba(255, 215, 0, 0.03) 0, transparent 55%),
                #ffffff;
            border-radius: clamp(12px, 1.25vw, 16px);
            padding: clamp(40px, 4.17vw, 60px);
            position: relative;
            overflow-x: auto;
            overflow-y: visible;
            min-height: clamp(400px, 41.67vw, 600px);
            display: flex;
            justify-content: flex-start;
            align-items: center;
        }

        .org-tree {
            --dark: #ff5c00;
            margin-left: 30px;
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: center;
            width: 100%;
            min-width: fit-content;
        }

        .org-tree .node {
            display: flex;
            flex-direction: row;
            align-items: center;
            margin: 20px 0;
            position: relative;
        }

        .org-tree .node:not(.node--root) > .node__element::before {
            content: '';
            width: 20px;
            height: 2px;
            background-color: #ff5c00;
            display: block;
            position: absolute;
            top: 0;
            bottom: 0;
            left: -20px;
            margin: auto;
            transition: all ease 0.2s;
        }

        .org-tree .node.node--left {
            margin-bottom: 10px;
        }

        .org-tree .node.node--right {
            margin-top: 10px;
        }

        .org-tree .node__element {
            cursor: pointer;
            border: 2px solid #ff5c00;
            width: auto;
            min-width: clamp(120px, 12.5vw, 160px);
            max-width: clamp(160px, 16.67vw, 200px);
            min-height: 60px;
            background: linear-gradient(90deg, #ff7a1a 0%, #ff5c00 55%, #ff4b00 100%);
            border-radius: 12px;
            padding: clamp(10px, 1.04vw, 14px) clamp(12px, 1.25vw, 16px);
            font-size: clamp(12px, 1.25vw, 16px);
            line-height: 1.4;
            text-align: center;
            color: #ffffff;
            box-shadow:
                0 4px 12px rgba(0, 0, 0, 0.12),
                0 0 0 1px rgba(255, 255, 255, 0.4);
            transition: all ease 0.2s;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .org-tree .node__element .org-title {
            font-weight: 700;
            font-size: clamp(13px, 1.35vw, 17px);
            margin-bottom: 4px;
            color: #ffffff;
        }

        .org-tree .node__element .org-name {
            font-weight: 500;
            font-size: clamp(11px, 1.15vw, 15px);
            word-break: break-word;
            color: #ffffff;
        }

        .org-tree .node__bottom-line {
            width: 20px;
            height: 2px;
            background-color: #ff5c00;
            transition: all ease 0.2s;
        }

        .org-tree .node__element,
        .org-tree .node__element::before,
        .org-tree .node__children,
        .org-tree .node__bottom-line {
            transition: all ease 0.2s;
        }

        .org-tree .node__children {
            display: flex;
            flex-direction: column;
            padding: 0 20px;
            border-left: 2px solid #ff5c00;
            gap: 20px;
            align-items: center;
        }

        .org-tree .node__element:hover {
            border-color: #ff9440;
            background: linear-gradient(90deg, #ff9440 0%, #ff7a1a 55%, #ff5c00 100%);
            transform: translateX(-2px);
            box-shadow: 
                0 6px 16px rgba(255, 92, 0, 0.3),
                0 0 0 1px rgba(255, 255, 255, 0.4);
        }

        .org-tree .node__element:hover ~ .node__children .node__element::before {
            height: 3px;
            background-color: #ff9440;
        }

        .org-tree .node__element:hover ~ .node__bottom-line,
        .org-tree .node__element:hover ~ .node__children .node__bottom-line {
            height: 3px;
            background-color: #ff9440;
        }

        .org-tree .node__element:hover ~ .node__children,
        .org-tree .node__element:hover ~ .node__children .node__children {
            border-left-width: 3px;
            border-color: #ff9440;
        }

        /* 响应式调整 */
        @media (max-width: 768px) {
            .mermaid-org-chart {
                padding: clamp(20px, 2.08vw, 30px);
                min-height: clamp(300px, 31.25vw, 400px);
                overflow-x: auto;
                overflow-y: visible;
            }

            .org-tree {
                margin-left: 10px;
            }

            .org-tree .node {
                margin: 10px 0;
            }

            .org-tree .node__element {
                min-width: clamp(100px, 10.42vw, 140px);
                max-width: clamp(140px, 14.58vw, 180px);
                padding: clamp(8px, 0.83vw, 12px) clamp(10px, 1.04vw, 14px);
                min-height: 50px;
            }

            .org-tree .node__children {
                gap: 10px;
                padding: 0 15px;
            }

            .org-tree .node__bottom-line {
                width: 15px;
            }

            .org-tree .node:not(.node--root) > .node__element::before {
                width: 15px;
                left: -15px;
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
                <!-- Header Section - 新设计 -->
                <div class="section">
                    <div class="header-panel">
                        <!-- 飘动的模糊圆球 -->
                        <div class="floating-orb floating-orb-1"></div>
                        <div class="floating-orb floating-orb-2"></div>
                        <div class="floating-orb floating-orb-3"></div>
                        <div class="floating-orb floating-orb-4"></div>
                        <div class="floating-orb floating-orb-5"></div>
                        
                        <!-- Logo - 居中显示 -->
                        <div class="header-logo-container">
                            <div class="header-logo">
                                <?php 
                                $logoPath = '../images/images/logo.png';
                                $logoFullPath = __DIR__ . '/../images/images/logo.png';
                                if (file_exists($logoFullPath)): 
                                ?>
                                <img src="<?php echo htmlspecialchars($logoPath); ?>" 
                                     alt="KUNZZ HOLDINGS Logo"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                <?php else: ?>
                                <!-- 如果图片不存在，显示占位符 -->
                                <div class="logo-fallback" style="display: block;"></div>
                                <?php endif; ?>
                                <!-- 图片加载失败时的备用占位符 -->
                                <div class="logo-fallback" style="display: none;"></div>
                            </div>
                        </div>

                        <!-- 文本内容 - 居中显示 -->
                        <div class="header-text-content">
                            <div class="company-name-large">KUNZZ HOLDINGS SDN BHD</div>
                            <div class="company-subtitle">企业蓝图 · 战略计划</div>
                        </div>
                    </div>
                </div>

                <!-- Timeline Section -->
                <?php if (!empty($strategyData['timeline'])): ?>
                <div class="section">
                    <div class="timeline-container">
                        <div class="timeline-header">
                            <div class="timeline-main-title">以终为始</div>
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
                                    
                                    // 单数索引（第1、3、5个，index 0,2,4）往下，双数索引（第2、4个，index 1,3）往上
                                    $cardPosition = ($index % 2 == 0) ? 'bottom' : 'top';
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
                    <div class="core-header">
                        <div class="core-main-title">企业核心</div>
                    </div>
                    <div class="core-grid">
                        <!-- 01 Mission -->
                        <div class="core-card">
                            <div class="core-card-number">01</div>
                            <div class="core-card-content-wrapper">
                                <div class="core-card-title">使命:初心&感性的目标</div>
                                <div class="core-card-content">
                                    塑造积极向上和舒适的工作环境
                                </div>
                            </div>
                        </div>

                        <!-- 02 Vision -->
                        <div class="core-card">
                            <div class="core-card-number">02</div>
                            <div class="core-card-content-wrapper">
                                <div class="core-card-title">愿景:理性可具体化的目标</div>
                                <div class="core-card-content">
                                    打造高效的团队,创造行业未来
                                </div>
                            </div>
                        </div>

                        <!-- 03 Culture -->
                        <div class="core-card">
                            <div class="core-card-number">03</div>
                            <div class="core-card-content-wrapper">
                                <div class="core-card-title">文化:做人的态度</div>
                                <div class="core-card-content">
                                    积极向上,高效执行 灵活应变,诚信待人
                                </div>
                            </div>
                        </div>

                        <!-- 04 Values -->
                        <div class="core-card">
                            <div class="core-card-number">04</div>
                            <div class="core-card-content-wrapper">
                                <div class="core-card-title">价值观:做事的态度</div>
                                <div class="core-card-content">
                                    目标导向,理念一致 追求卓越,创新精神
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Culture Explanation - 新设计 -->
                <div class="section">
                    <div class="culture-explanation-header">
                        <div class="culture-explanation-title-cn">文化解说&考核</div>
                    </div>
                    <div class="culture-explanation-grid">
                        <!-- 01 积极向上 -->
                        <div class="culture-explanation-card">
                            <div class="culture-explanation-number">01</div>
                            <div class="culture-explanation-key">积极向上</div>
                            <div class="culture-explanation-description">
                                作为Holding管理公司,面对多家子公司及不同行业的挑战,员工和管理层都需要保持积极向上的态度。这种心态不仅有助于应对困难,还能激励团队不断寻求突破,推动公司持续成长和发展。
                            </div>
                            <div class="culture-scoring">
                                <div class="culture-scoring-title">评分标准:</div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">1分:</div>
                                    <div class="culture-scoring-description">经常表现出消极情绪,缺乏主动性</div>
                                </div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">2分:</div>
                                    <div class="culture-scoring-description">能够完成任务,但缺少正能量激励和带动的作用</div>
                                </div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">3分:</div>
                                    <div class="culture-scoring-description">整体态度积极,可以较好的应对挑战并维持稳定的状态</div>
                                </div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">4分:</div>
                                    <div class="culture-scoring-description">会主动激励同事,面对困难保持乐观的态度并寻求方法突破难关</div>
                                </div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">5分:</div>
                                    <div class="culture-scoring-description">始终保持积极乐观的心态,主动进步不断提升自己</div>
                                </div>
                            </div>
                        </div>

                        <!-- 02 高效执行 -->
                        <div class="culture-explanation-card">
                            <div class="culture-explanation-number">02</div>
                            <div class="culture-explanation-key">高效执行</div>
                            <div class="culture-explanation-description">
                                高效执行不仅是快速完成任务,更是确保战略精准落地。通过精简流程、优化资源分配,各部门紧密协作,减少延误。高效执行帮助公司在市场变化中迅速应对挑战,抢占先机,实现目标。
                            </div>
                            <div class="culture-scoring">
                                <div class="culture-scoring-title">评分标准:</div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">1分:</div>
                                    <div class="culture-scoring-description">工作常延迟,缺少计划性</div>
                                </div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">2分:</div>
                                    <div class="culture-scoring-description">可以完成任务,但效率不高,容易出错</div>
                                </div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">3分:</div>
                                    <div class="culture-scoring-description">能确保任务按时完成,流程执行基本符合要求</div>
                                </div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">4分:</div>
                                    <div class="culture-scoring-description">高效推进任务,能优化流程并减少拖延</div>
                                </div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">5分:</div>
                                    <div class="culture-scoring-description">执行力极强,能确保计划精准落实并能带动团队缩短任务时间</div>
                                </div>
                            </div>
                        </div>

                        <!-- 03 灵活应变 -->
                        <div class="culture-explanation-card">
                            <div class="culture-explanation-number">03</div>
                            <div class="culture-explanation-key">灵活应变</div>
                            <div class="culture-explanation-description">
                                灵活应变要求根据各子公司实际情况快速调整策略,促进团队合作与创新。确保公司在多变市场中保持领先。
                            </div>
                            <div class="culture-scoring">
                                <div class="culture-scoring-title">评分标准:</div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">1分:</div>
                                    <div class="culture-scoring-description">遇到变化不知所措,缺少应对能力</div>
                                </div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">2分:</div>
                                    <div class="culture-scoring-description">在指导下能适应变化,但缺乏独立应对的能力</div>
                                </div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">3分:</div>
                                    <div class="culture-scoring-description">能根据情况做出调整,并应对在一般的变化上</div>
                                </div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">4分:</div>
                                    <div class="culture-scoring-description">能快速调整策略,并推动创新与团队合作</div>
                                </div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">5分:</div>
                                    <div class="culture-scoring-description">反应迅速,能预判变化并引领团队一起应对</div>
                                </div>
                            </div>
                        </div>

                        <!-- 04 诚信待人 -->
                        <div class="culture-explanation-card">
                            <div class="culture-explanation-number">04</div>
                            <div class="culture-explanation-key">诚信待人</div>
                            <div class="culture-explanation-description">
                                诚信是公司文化的核心,无论是公司内部的员工管理,还是客户和合作伙伴的关系,诚信是建立信任的基础。诚信待人不仅能促进公司内部合作与沟通,也能增强外部与合作伙伴的关系,帮助公司长期发展。
                            </div>
                            <div class="culture-scoring">
                                <div class="culture-scoring-title">评分标准:</div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">1分:</div>
                                    <div class="culture-scoring-description">有失信的行为,影响合作关系</div>
                                </div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">2分:</div>
                                    <div class="culture-scoring-description">基本遵守承诺,但偶尔承诺与结果之间存在差距</div>
                                </div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">3分:</div>
                                    <div class="culture-scoring-description">为人诚实守信,合作关系稳定</div>
                                </div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">4分:</div>
                                    <div class="culture-scoring-description">在团队中树立良好的信誉,促进团队的合作顺畅</div>
                                </div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">5分:</div>
                                    <div class="culture-scoring-description">以诚信为核心价值,成为团队和外部伙伴的诚信典范</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Values Explanation - 新设计，风格与 Culture Explanation 一致 -->
                <div class="section">
                    <div class="values-explanation-header">
                        <div class="values-explanation-title-cn">价值观解说&考核</div>
                    </div>
                    <div class="culture-explanation-grid">
                        <!-- 01 目标导向 -->
                        <div class="culture-explanation-card">
                            <div class="culture-explanation-number">01</div>
                            <div class="culture-explanation-key">目标导向</div>
                            <div class="culture-explanation-description">
                                公司需要明确战略目标,并确保每个部门围绕这些目标高效运作。所有工作都应有清晰的方向,以实现公司整体愿景和长期发展。
                            </div>
                            <div class="culture-scoring">
                                <div class="culture-scoring-title">评分标准:</div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">1分:</div>
                                    <div class="culture-scoring-description">缺少明确的目标,经常偏离方向</div>
                                </div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">2分:</div>
                                    <div class="culture-scoring-description">知道整体方向,但常常在执行上存在偏移</div>
                                </div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">3分:</div>
                                    <div class="culture-scoring-description">有基本的目标意识,能根据公司目标安排好任务</div>
                                </div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">4分:</div>
                                    <div class="culture-scoring-description">会主动对照公司目标调整计划,确保工作的推进方向一致</div>
                                </div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">5分:</div>
                                    <div class="culture-scoring-description">以目标为核心进行规划,并能推动团队围绕共同目标高效落实</div>
                                </div>
                            </div>
                        </div>

                        <!-- 02 理念一致 -->
                        <div class="culture-explanation-card">
                            <div class="culture-explanation-number">02</div>
                            <div class="culture-explanation-key">理念一致</div>
                            <div class="culture-explanation-description">
                                管理层和团队需在战略、决策和执行上保持一致,以保障组织沟通顺畅,避免内部摩擦和资源浪费,共同推动公司整体稳健发展。
                            </div>
                            <div class="culture-scoring">
                                <div class="culture-scoring-title">评分标准:</div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">1分:</div>
                                    <div class="culture-scoring-description">与公司方向和理念差异明显,行为不够统一</div>
                                </div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">2分:</div>
                                    <div class="culture-scoring-description">大部分情况能配合公司理念,但偶尔仍按个人习惯行事</div>
                                </div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">3分:</div>
                                    <div class="culture-scoring-description">理解并基本认同公司理念,能按照要求执行</div>
                                </div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">4分:</div>
                                    <div class="culture-scoring-description">积极传递公司理念,并在团队中保持统一标准和做法</div>
                                </div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">5分:</div>
                                    <div class="culture-scoring-description">高度认同公司理念,能影响团队成员,共同维持一致的价值判断和行为标准</div>
                                </div>
                            </div>
                        </div>

                        <!-- 03 追求卓越 -->
                        <div class="culture-explanation-card">
                            <div class="culture-explanation-number">03</div>
                            <div class="culture-explanation-key">追求卓越</div>
                            <div class="culture-explanation-description">
                                公司不仅要优化管理流程,还要不断提升自身的管理水平。通过持续改进,提高工作效率和质量,在公司内部树立精益求精的文化。
                            </div>
                            <div class="culture-scoring">
                                <div class="culture-scoring-title">评分标准:</div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">1分:</div>
                                    <div class="culture-scoring-description">缺少主动进步的意愿,满足在最低的标准内完成工作</div>
                                </div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">2分:</div>
                                    <div class="culture-scoring-description">能做到基本要求,但很少主动提出优化或改进建议</div>
                                </div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">3分:</div>
                                    <div class="culture-scoring-description">在保证质量的前提下,会适度思考提升效率和成果</div>
                                </div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">4分:</div>
                                    <div class="culture-scoring-description">不断自我提升,主动优化工作流程,追求更高标准</div>
                                </div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">5分:</div>
                                    <div class="culture-scoring-description">以卓越为目标,持续突破既有成绩,对团队有明显的标杆示范作用</div>
                                </div>
                            </div>
                        </div>

                        <!-- 04 创新精神 -->
                        <div class="culture-explanation-card">
                            <div class="culture-explanation-number">04</div>
                            <div class="culture-explanation-key">创新精神</div>
                            <div class="culture-explanation-description">
                                通过流程、技术和管理模式上的不断创新,以提升内部管理效能。通过鼓励全员参与创新,能够更好地应对市场变化,增强公司的竞争力和决策能力。
                            </div>
                            <div class="culture-scoring">
                                <div class="culture-scoring-title">评分标准:</div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">1分:</div>
                                    <div class="culture-scoring-description">习惯依赖既有方法,不愿尝试新做法</div>
                                </div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">2分:</div>
                                    <div class="culture-scoring-description">可以接受变化,但多数情况为被动配合,缺少主动思考</div>
                                </div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">3分:</div>
                                    <div class="culture-scoring-description">在需要时会提出改进想法,并能配合推动部分创新措施</div>
                                </div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">4分:</div>
                                    <div class="culture-scoring-description">积极参与各类创新项目,能提出可行方案并协助落实</div>
                                </div>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point">5分:</div>
                                    <div class="culture-scoring-description">具有强烈的创新意识,能系统性思考并带领团队一起设计和落地创新方案</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Organization Structure - Binary Tree Structure -->
                <?php if (!empty($strategyData['organizationStructure'])): ?>
                <div class="section">
                    <h2 class="section-title">高层组织架构</h2>
                    <div class="mermaid-org-chart">
                        <?php 
                        $orgStructure = $strategyData['organizationStructure'];
                        
                        // 将数组形式的子节点转换为二叉树的左右节点结构
                        function convertToBinaryTree($children) {
                            if (empty($children) || !is_array($children)) {
                                return ['left' => null, 'right' => null];
                            }
                            
                            $count = count($children);
                            
                            // 如果只有一个子节点，放在左节点
                            if ($count === 1) {
                                return [
                                    'left' => convertNodeToBinary($children[0]),
                                    'right' => null
                                ];
                            }
                            
                            // 如果有两个子节点，分别放在左右
                            if ($count === 2) {
                                return [
                                    'left' => convertNodeToBinary($children[0]),
                                    'right' => convertNodeToBinary($children[1])
                                ];
                            }
                            
                            // 如果有三个或更多子节点，拆分到左右子树
                            // 将前一半放在左子树，后一半放在右子树
                            $mid = (int)ceil($count / 2);
                            $leftChildren = array_slice($children, 0, $mid);
                            $rightChildren = array_slice($children, $mid);
                            
                            // 递归处理左右子树
                            $leftBinary = convertToBinaryTree($leftChildren);
                            $rightBinary = convertToBinaryTree($rightChildren);
                            
                            // 如果左子树只有一个节点，直接使用
                            if ($leftBinary['right'] === null && $leftBinary['left'] !== null) {
                                $leftNode = $leftBinary['left'];
                            } else {
                                // 需要创建一个中间节点来包含左子树
                                $leftNode = [
                                    'element' => '',
                                    'title' => '',
                                    'name' => '',
                                    'left' => $leftBinary['left'],
                                    'right' => $leftBinary['right']
                                ];
                            }
                            
                            // 如果右子树只有一个节点，直接使用
                            if ($rightBinary['right'] === null && $rightBinary['left'] !== null) {
                                $rightNode = $rightBinary['left'];
                            } else {
                                // 需要创建一个中间节点来包含右子树
                                $rightNode = [
                                    'element' => '',
                                    'title' => '',
                                    'name' => '',
                                    'left' => $rightBinary['left'],
                                    'right' => $rightBinary['right']
                                ];
                            }
                            
                            return [
                                'left' => $leftNode,
                                'right' => $rightNode
                            ];
                        }
                        
                        // 将单个节点转换为二叉树节点格式
                        function convertNodeToBinary($node) {
                            $title = htmlspecialchars($node['title'] ?? $node['fullTitle'] ?? '');
                            $name = htmlspecialchars($node['name'] ?? '');
                            
                            $result = [
                                'element' => '',
                                'title' => $title,
                                'name' => $name,
                                'left' => null,
                                'right' => null
                            ];
                            
                            // 处理子节点
                            if (!empty($node['subordinates']) && is_array($node['subordinates'])) {
                                $binary = convertToBinaryTree($node['subordinates']);
                                $result['left'] = $binary['left'];
                                $result['right'] = $binary['right'];
                            }
                            
                            return $result;
                        }
                        
                        // 递归渲染二叉树节点
                        function renderBinaryTreeNode($node, $isRoot = false, $isLeft = false, $isRight = false) {
                            if (empty($node)) {
                                return '';
                            }
                            
                            // 检查是否有有效的节点内容（title或name）
                            $hasContent = !empty($node['title']) || !empty($node['name']);
                            
                            // 如果节点没有内容但也没有子节点，跳过
                            if (!$hasContent && empty($node['left']) && empty($node['right'])) {
                                return '';
                            }
                            
                            $hasChildren = (!empty($node['left']) || !empty($node['right']));
                            
                            // 如果没有内容但有子节点，创建一个容器节点
                            if (!$hasContent && $hasChildren) {
                                $html = '';
                                if (!empty($node['left'])) {
                                    $html .= renderBinaryTreeNode($node['left'], false, true, false);
                                }
                                if (!empty($node['right'])) {
                                    $html .= renderBinaryTreeNode($node['right'], false, false, true);
                                }
                                // 如果有两个子节点，需要包装在children容器中
                                if (!empty($node['left']) && !empty($node['right'])) {
                                    $html = '<div class="node__children">' . $html . '</div>';
                                }
                                return $html;
                            }
                            
                            // 构建节点类名
                            $nodeClass = 'node';
                            if ($isRoot) {
                                $nodeClass .= ' node--root';
                            }
                            if ($isLeft) {
                                $nodeClass .= ' node--left';
                            }
                            if ($isRight) {
                                $nodeClass .= ' node--right';
                            }
                            
                            $html = '<div class="' . $nodeClass . '">';
                            
                            // 渲染节点元素
                            $title = $node['title'] ?? '';
                            $name = $node['name'] ?? '';
                            
                            $html .= '<div class="node__element">';
                            if (!empty($title)) {
                                $html .= '<span class="org-title">' . $title . '</span>';
                            }
                            if (!empty($name)) {
                                $html .= '<span class="org-name">' . $name . '</span>';
                            }
                            $html .= '</div>';
                            
                            // 如果有子节点，添加连接线和子节点容器
                            if ($hasChildren) {
                                $html .= '<div class="node__bottom-line"></div>';
                                $html .= '<div class="node__children">';
                                
                                if (!empty($node['left'])) {
                                    $html .= renderBinaryTreeNode($node['left'], false, true, false);
                                }
                                
                                if (!empty($node['right'])) {
                                    $html .= renderBinaryTreeNode($node['right'], false, false, true);
                                }
                                
                                $html .= '</div>';
                            }
                            
                            $html .= '</div>';
                            
                            return $html;
                        }
                        
                        // 构建根节点（CEO）
                        $ceoTitle = htmlspecialchars($orgStructure['ceo']['title'] ?? $orgStructure['ceo']['fullTitle'] ?? 'CEO');
                        $ceoName = htmlspecialchars($orgStructure['ceo']['name'] ?? '');
                        
                        // 收集所有C-Level和PA作为CEO的子节点
                        $ceoChildren = [];
                        if (!empty($orgStructure['cLevel'])) {
                            foreach ($orgStructure['cLevel'] as $exec) {
                                $ceoChildren[] = $exec;
                            }
                        }
                        if (!empty($orgStructure['pa'])) {
                            $ceoChildren[] = $orgStructure['pa'];
                        }
                        
                        // 构建CEO节点
                        $rootNode = [
                            'element' => '',
                            'title' => $ceoTitle,
                            'name' => $ceoName,
                            'left' => null,
                            'right' => null
                        ];
                        
                        if (!empty($ceoChildren)) {
                            $binary = convertToBinaryTree($ceoChildren);
                            $rootNode['left'] = $binary['left'];
                            $rootNode['right'] = $binary['right'];
                        }
                        ?>

                        <div class="org-tree">
                            <?php echo renderBinaryTreeNode($rootNode, true); ?>
                        </div>
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

