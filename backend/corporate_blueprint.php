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
            align-items: start;
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
            height: 100%;
        }
        
        .culture-explanation-content {
            display: flex;
            flex-direction: column;
            flex: 0 0 auto;
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
            margin-bottom: 0;
        }

        /* 评分标准部分（表格风格，逐级加深，仅色条+文字） */
        .culture-scoring {
            background: #ffffff;
            border: 1px solid #f6c99f;
            border-radius: 8px;
            overflow: hidden;
            margin-top: clamp(20px, 2.08vw, 28px);
            flex: 0 0 auto;
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

        /* Strategic Objectives - 基于App.tsx设计 */
        .strategic-objectives-section {
            background: linear-gradient(to bottom, #FFF7EA, #FFFFFF);
            min-height: 100vh;
            padding: clamp(32px, 3.33vw, 48px) 0;
            position: relative;
            border: 1px solid #F7931E;
            border-radius: clamp(16px, 1.67vw, 24px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        /* 背景装饰 */
        .strategic-bg-decor {
            position: fixed;
            inset: 0;
            pointer-events: none;
            overflow: hidden;
            opacity: 0.1;
            z-index: 0;
        }

        .strategic-bg-decor::before {
            content: '';
            position: absolute;
            top: -10%;
            left: -10%;
            width: 40%;
            height: 40%;
            background: #ff5c00;
            border-radius: 50%;
            filter: blur(120px);
        }

        .strategic-bg-decor::after {
            content: '';
            position: absolute;
            bottom: -10%;
            right: -10%;
            width: 40%;
            height: 40%;
            background: #3b82f6;
            border-radius: 50%;
            filter: blur(120px);
        }

        .strategic-container {
            position: relative;
            z-index: 10;
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 clamp(16px, 1.67vw, 24px);
        }

        /* 头部区域 */
        .strategic-header {
            margin-bottom: clamp(32px, 3.33vw, 48px);
        }

        .strategic-header-content {
            display: flex;
            flex-direction: column;
            gap: clamp(16px, 1.67vw, 24px);
        }

        @media (min-width: 768px) {
            .strategic-header-content {
                flex-direction: row;
                align-items: flex-end;
                justify-content: space-between;
            }
        }

        .strategic-header-left {
            flex: 1;
        }

        .strategic-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #ff5c00;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: clamp(12px, 1.25vw, 14px);
            margin-bottom: clamp(8px, 0.83vw, 12px);
        }

        .strategic-main-title {
            font-size: clamp(32px, 3.33vw, 48px);
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
            margin-bottom: clamp(8px, 0.83vw, 12px);
        }

        .strategic-year {
            color: #ff5c00;
            margin-left: clamp(12px, 1.25vw, 16px);
        }


        /* 主要内容区域 */
        .strategic-main {
            display: grid;
            grid-template-columns: 1fr;
            gap: clamp(24px, 2.5vw, 32px);
        }

        @media (min-width: 1024px) {
            .strategic-main {
                grid-template-columns: 5fr 7fr;
            }
        }

        /* 策略列表 */
        .strategic-list {
            display: flex;
            flex-direction: column;
            gap: clamp(12px, 1.25vw, 16px);
            max-height: 520px;
            overflow-y: auto;
            padding-right: 4px;
            scrollbar-width: thin;              /* Firefox */
            scrollbar-color: #f59e0b transparent;
        }

        /* 策略列表滚动条样式（WebKit 浏览器） */
        .strategic-list::-webkit-scrollbar {
            width: 6px;
        }

        .strategic-list::-webkit-scrollbar-track {
            background: transparent;
        }

        .strategic-list::-webkit-scrollbar-thumb {
            background: #f59e0b;
            border-radius: 999px;
        }

        .strategic-list::-webkit-scrollbar-thumb:hover {
            background: #d97706;
        }

        .strategic-list-title {
            font-size: clamp(18px, 1.88vw, 20px);
            font-weight: 700;
            margin-bottom: clamp(16px, 1.67vw, 24px);
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0 8px;
        }

        .strategic-list-count {
            background: #e2e8f0;
            color: #64748b;
            font-size: clamp(10px, 1.04vw, 12px);
            padding: 2px 8px;
            border-radius: 9999px;
            font-weight: 600;
        }

        .strategy-card {
            background: rgba(255, 255, 255, 0.5);
            border-radius: clamp(12px, 1.25vw, 16px);
            padding: clamp(16px, 1.67vw, 20px);
            transition: all 0.3s ease;
            cursor: pointer;
            text-align: left;
            width: 100%;
            border: 1px solid #F7931E;
            position: relative;
            display: flex;
            align-items: flex-start;
            gap: clamp(12px, 1.25vw, 16px);
        }

        .strategy-card:hover {
            background: rgba(255, 255, 255, 1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-color: #F7931E;
        }

        .strategy-card.active {
            background: #ffffff;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            transform: scale(1.02);
            border: 2px solid #ff5c00;
        }

        .strategy-icon-wrapper {
            padding: clamp(10px, 1.04vw, 12px);
            border-radius: clamp(8px, 0.83vw, 12px);
            transition: all 0.3s ease;
            flex-shrink: 0;
        }

        .strategy-card:not(.active) .strategy-icon-wrapper {
            background: #f1f5f9;
            color: #64748b;
        }

        .strategy-card.active .strategy-icon-wrapper {
            background: #ff5c00;
            color: #ffffff;
        }

        .strategy-card:hover:not(.active) .strategy-icon-wrapper {
            background: #fff5e6;
            color: #ff5c00;
        }

        .strategy-icon {
            width: 24px;
            height: 24px;
        }

        .strategy-content {
            flex: 1;
        }

        .strategy-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 4px;
        }

        .strategy-id {
            font-size: clamp(10px, 1.04vw, 12px);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .strategy-card:not(.active) .strategy-id {
            color: #94a3b8;
        }

        .strategy-card.active .strategy-id {
            color: #ff5c00;
        }

        .strategy-check {
            width: 16px;
            height: 16px;
            color: #ff5c00;
            display: none;
        }

        .strategy-card.active .strategy-check {
            display: block;
        }

        .strategy-title {
            font-size: clamp(16px, 1.67vw, 18px);
            font-weight: 700;
            margin-bottom: clamp(6px, 0.63vw, 8px);
        }

        .strategy-card:not(.active) .strategy-title {
            color: #475569;
        }

        .strategy-card.active .strategy-title {
            color: #0f172a;
        }

        .strategy-description {
            font-size: clamp(13px, 1.35vw, 14px);
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .strategy-card:not(.active) .strategy-description {
            color: #94a3b8;
        }

        .strategy-card.active .strategy-description {
            color: #64748b;
        }

        .strategy-chevron {
            align-self: center;
            width: 20px;
            height: 20px;
            transition: transform 0.3s ease;
            flex-shrink: 0;
        }

        .strategy-card:not(.active) .strategy-chevron {
            color: #cbd5e1;
        }

        .strategy-card.active .strategy-chevron {
            transform: rotate(90deg);
            color: #ff5c00;
        }

        /* 详细视图 */
        .strategic-details {
            background: #ffffff;
            border-radius: clamp(16px, 1.67vw, 24px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            border: 1px solid #e2e8f0;
            min-height: 600px;
            display: flex;
            flex-direction: column;
            opacity: 1;
            transform: translateX(0);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .strategic-details.hidden {
            opacity: 0;
            transform: translateX(20px);
        }

        .details-header {
            background: #0f172a;
            padding: clamp(24px, 2.5vw, 32px);
            color: #ffffff;
            position: relative;
        }

        .details-header-icon {
            position: absolute;
            top: 0;
            right: 0;
            opacity: 0.1;
            width: 210px;
            height: 210px;
        }

        .details-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            background: #F7931E;
            color: #ffffff;
            font-size: clamp(12px, 1.25vw, 14px);
            font-weight: 700;
            border-radius: 9999px;
            letter-spacing: 0.02em;
            margin-bottom: clamp(12px, 1.25vw, 16px);
            line-height: 1;
        }

        .details-title {
            font-size: clamp(24px, 2.5vw, 30px);
            font-weight: 700;
            margin-bottom: clamp(12px, 1.25vw, 16px);
        }

        .details-body {
            padding: clamp(24px, 2.5vw, 32px);
            flex: 1;
            display: grid;
            grid-template-columns: 1fr;
            gap: clamp(24px, 2.5vw, 40px);
        }

        @media (min-width: 768px) {
            .details-body {
                grid-template-columns: 1fr 1fr;
            }
        }

        .details-section {
            display: flex;
            flex-direction: column;
            gap: clamp(24px, 2.5vw, 32px);
        }

        .details-section-title {
            font-size: clamp(12px, 1.25vw, 14px);
            font-weight: 800;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .details-section-icon {
            width: 16px;
            height: 16px;
        }

        .measure-item {
            display: flex;
            flex-direction: column;
            gap: clamp(12px, 1.25vw, 16px);
        }

        .measure-header {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .measure-badge {
            background: #fff5e6;
            color: #ff5c00;
            font-size: clamp(10px, 1.04vw, 12px);
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 4px;
        }

        .measure-label {
            font-weight: 700;
            color: #0f172a;
            font-size: clamp(14px, 1.46vw, 16px);
        }

        .measure-list {
            display: flex;
            flex-direction: column;
            gap: clamp(10px, 1.04vw, 12px);
        }

        .measure-list-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            transition: transform 0.2s ease;
        }

        .measure-list-item:hover {
            transform: scale(1.02);
        }

        .measure-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #ff5c00;
            margin-top: 6px;
            flex-shrink: 0;
            transition: transform 0.2s ease;
        }

        .measure-list-item:hover .measure-dot {
            transform: scale(1.5);
        }

        .measure-text {
            color: #475569;
            font-size: clamp(13px, 1.35vw, 14px);
            line-height: 1.6;
        }

        .execution-plan {
            background: #f8fafc;
            padding: clamp(20px, 2.08vw, 24px);
            border-radius: clamp(12px, 1.25vw, 16px);
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            gap: clamp(20px, 2.08vw, 24px);
        }

        .execution-pic {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .execution-pic-icon {
            width: 48px;
            height: 48px;
            background: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            color: #ff5c00;
        }

        .execution-pic-info {
            display: flex;
            flex-direction: column;
        }

        .execution-pic-label {
            font-size: clamp(10px, 1.04vw, 12px);
            color: #94a3b8;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .execution-pic-name {
            font-size: clamp(16px, 1.67vw, 18px);
            font-weight: 700;
            color: #0f172a;
        }

        .execution-dates {
            display: flex;
            flex-direction: column;
            gap: clamp(12px, 1.25vw, 16px);
        }

        .execution-date-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: clamp(13px, 1.35vw, 14px);
        }

        .execution-date-label {
            color: #64748b;
            font-weight: 500;
        }

        .execution-date-value {
            font-weight: 700;
            color: #0f172a;
        }

        .execution-date-divider {
            border-top: 1px solid #e2e8f0;
            padding-top: clamp(12px, 1.25vw, 16px);
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

        /* 组织架构样式 - 精确匹配Figma设计 */
        .mermaid-org-chart {
            background-color: #ffffff;
            height: 765px;
            width: 100%;
            max-width: 1536px;
            border-radius: 15px;
            padding: 0;
            position: relative;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        }

        /* Logo背景图片 - 精确匹配Figma */
        .org-chart-logo {
            height: 1312px;
            width: 1312px;
            left: 0;
            top: 0;
            position: absolute;
            opacity: 0.03;
            z-index: 0;
            pointer-events: none;
        }

        .org-chart-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        /* 标题容器 */
        .org-chart-header {
            position: relative;
            z-index: 2;
            width: 100%;
        }

        /* 橙色矩形背景 - 精确匹配Figma */
        .org-chart-title-bg {
            background-color: #ff5c00;
            height: 82px;
            width: 424px;
            border-radius: 0 30px 30px 0;
            position: absolute;
            top: 25px;
            left: 0;
            z-index: 1;
        }

        /* 标题文字 - 精确匹配Figma */
        .org-chart-title {
            color: #ffffff;
            text-align: center;
            vertical-align: text-top;
            font-size: 48px;
            font-family: Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Microsoft YaHei', sans-serif;
            line-height: auto;
            border-style: hidden;
            outline: none;
            left: 44px;
            top: 37px;
            position: absolute;
            width: 336px;
            z-index: 2;
            font-weight: 600;
            margin: 0;
            padding: 0;
            white-space: nowrap;
        }

        /* 标题下方的白色线条 - 精确匹配Figma */
        .org-chart-title-line {
            background-color: #ffffff;
            width: 336px;
            transform: rotate(-1deg);
            border: 3px solid #ffffff;
            top: 95px;
            left: 44px;
            position: absolute;
            z-index: 2;
            height: 0;
        }

        /* 组织架构工作区 - 基于React设计 */
        .org-workspace {
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(12px);
            border-radius: 48px;
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
            padding: clamp(32px, 3.33vw, 48px);
            overflow-x: auto;
            min-height: 600px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        /* 标题覆盖层 */
        .org-title-overlay {
            position: absolute;
            top: clamp(32px, 3.33vw, 48px);
            left: clamp(32px, 3.33vw, 48px);
            z-index: 10;
        }

        .org-title-badge {
            color: #ff5c00;
            font-weight: 800;
            font-size: clamp(10px, 1.04vw, 12px);
            text-transform: uppercase;
            letter-spacing: 0.4em;
            margin-bottom: 6px;
        }

        .org-title-main {
            font-size: clamp(24px, 2.5vw, 36px);
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
        }

        /* 树根容器 */
        .org-tree-root-container {
            position: relative;
            display: flex;
            align-items: center;
            padding: clamp(50px, 5.21vw, 80px) clamp(100px, 10.42vw, 160px);
        }

        /* 背景Logo图片 */
        .org-bg-text {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            user-select: none;
            opacity: 0.03;
            z-index: 0;
        }

        .org-bg-text img {
            width: clamp(300px, 31.25vw, 800px);
            height: clamp(300px, 31.25vw, 800px);
            object-fit: contain;
        }

        /* 扁平化网格布局 - 联系人墙风格 */
        .org-grid-container {
            position: relative;
            z-index: 1;
            width: 100%;
        }

        .org-cards-wall {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(clamp(140px, 14.58vw, 180px), 1fr));
            gap: clamp(16px, 1.67vw, 24px);
            padding: clamp(20px, 2.08vw, 32px);
            background: #ffffff;
            border-radius: clamp(16px, 1.67vw, 24px);
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .org-card {
            background: #ffffff;
            border: 2px solid #e2e8f0;
            border-radius: clamp(12px, 1.25vw, 16px);
            padding: clamp(16px, 1.67vw, 20px);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            position: relative;
        }

        .org-card:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            border-color: #ff5c00;
        }

        /* CEO卡片 - 最大，橙色渐变背景 */
        .org-card-level-ceo {
            grid-column: span 2;
            grid-row: span 2;
            background: linear-gradient(135deg, #ff5c00 0%, #ff8c42 100%);
            border: 3px solid #ffffff;
            box-shadow: 0 8px 32px rgba(255, 92, 0, 0.3);
            min-height: clamp(180px, 18.75vw, 260px);
        }

        .org-card-level-ceo .org-card-role {
            color: #ffffff;
            font-size: clamp(24px, 2.5vw, 36px);
        }

        .org-card-level-ceo .org-card-name {
            color: #ffffff;
            font-size: clamp(18px, 1.88vw, 24px);
        }

        /* C-Level卡片 - 中等大小，橙色边框 */
        .org-card-level-clevel {
            grid-column: span 1;
            grid-row: span 1;
            border: 2px solid #ff5c00;
            background: #fff5e6;
            min-height: clamp(120px, 12.5vw, 160px);
        }

        .org-card-level-clevel .org-card-role {
            color: #ff5c00;
            font-size: clamp(16px, 1.67vw, 22px);
        }

        .org-card-level-clevel .org-card-name {
            color: #0f172a;
            font-size: clamp(14px, 1.46vw, 18px);
        }

        .org-card-level-clevel:hover {
            background: #ffffff;
            border-color: #ff5c00;
            box-shadow: 0 8px 24px rgba(255, 92, 0, 0.2);
        }

        /* PA/其他卡片 - 标准大小，灰色边框 */
        .org-card-level-other {
            grid-column: span 1;
            grid-row: span 1;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            min-height: clamp(100px, 10.42vw, 140px);
        }

        .org-card-level-other .org-card-role {
            color: #64748b;
            font-size: clamp(14px, 1.46vw, 18px);
        }

        .org-card-level-other .org-card-name {
            color: #475569;
            font-size: clamp(12px, 1.25vw, 16px);
        }

        .org-card-level-other:hover {
            border-color: #ff5c00;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        .org-card-role {
            font-weight: 800;
            margin-bottom: clamp(8px, 0.83vw, 12px);
            letter-spacing: 0.05em;
            line-height: 1.2;
        }

        .org-card-name {
            font-weight: 600;
            line-height: 1.4;
        }

        .org-card-empty {
            color: #94a3b8;
            font-weight: 400;
            font-style: italic;
        }

        /* 内部组织架构图 - 垂直列布局 */
        .internal-org-workspace {
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(12px);
            border-radius: 48px;
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
            padding: clamp(32px, 3.33vw, 48px);
            overflow-x: auto;
            min-height: 600px;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .internal-org-title-overlay {
            position: absolute;
            top: clamp(32px, 3.33vw, 48px);
            left: clamp(32px, 3.33vw, 48px);
            z-index: 10;
        }

        .internal-org-title-badge {
            color: #ff5c00;
            font-weight: 800;
            font-size: clamp(10px, 1.04vw, 12px);
            text-transform: uppercase;
            letter-spacing: 0.4em;
            margin-bottom: 6px;
        }

        .internal-org-title-main {
            font-size: clamp(24px, 2.5vw, 36px);
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
        }

        .internal-org-bg-text {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            user-select: none;
            opacity: 0.03;
            z-index: 0;
        }

        .internal-org-bg-text img {
            width: clamp(300px, 31.25vw, 800px);
            height: clamp(300px, 31.25vw, 800px);
            object-fit: contain;
        }

        .internal-org-container {
            position: relative;
            z-index: 1;
            width: 100%;
            padding: clamp(80px, 8.33vw, 120px) clamp(40px, 4.17vw, 60px) clamp(40px, 4.17vw, 60px);
        }

        .internal-org-columns {
            display: grid;
            grid-template-columns: repeat(9, 1fr);
            gap: clamp(16px, 1.67vw, 24px);
            align-items: start;
        }

        .internal-org-column {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            min-height: 200px;
        }

        .internal-org-column-title {
            font-size: clamp(12px, 1.25vw, 16px);
            font-weight: 800;
            color: #0f172a;
            margin-bottom: clamp(16px, 1.67vw, 24px);
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: clamp(8px, 0.83vw, 12px) clamp(12px, 1.25vw, 16px);
            background: #fff5e6;
            border-radius: clamp(8px, 0.83vw, 12px);
            border: 2px solid #ff5c00;
            width: 100%;
            box-sizing: border-box;
        }

        .internal-org-column-cards {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0;
            position: relative;
            width: 100%;
            padding-top: clamp(4px, 0.42vw, 6px);
            padding-bottom: clamp(4px, 0.42vw, 6px);
        }

        /* 连接线 - 垂直细线 */
        .internal-org-column-cards::before {
            content: '';
            position: absolute;
            top: clamp(4px, 0.42vw, 6px);
            left: 50%;
            transform: translateX(-50%);
            width: 1.5px;
            height: calc(100% - clamp(8px, 0.83vw, 12px));
            background: #000000;
            z-index: 1;
        }

        .internal-org-position-card {
            background: #ff5c00;
            border-radius: clamp(8px, 0.83vw, 12px);
            padding: clamp(12px, 1.25vw, 16px) clamp(10px, 1.04vw, 14px);
            width: 100%;
            max-width: clamp(120px, 12.5vw, 160px);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
            z-index: 2;
            margin-bottom: clamp(8px, 0.83vw, 12px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .internal-org-position-card:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 4px 16px rgba(255, 92, 0, 0.3);
        }

        .internal-org-position-title {
            font-size: clamp(10px, 1.04vw, 14px);
            font-weight: 700;
            color: #ffffff;
            margin-bottom: clamp(6px, 0.63vw, 8px);
            line-height: 1.3;
            letter-spacing: 0.02em;
        }

        .internal-org-position-name {
            font-size: clamp(9px, 0.94vw, 12px);
            font-weight: 600;
            color: #ffffff;
            line-height: 1.4;
            word-break: break-word;
        }

        .internal-org-position-empty {
            color: rgba(255, 255, 255, 0.7);
            font-style: italic;
        }

        /* 响应式调整 */
        @media (max-width: 1400px) {
            .internal-org-columns {
                grid-template-columns: repeat(5, 1fr);
            }
        }

        @media (max-width: 1024px) {
            .internal-org-workspace {
                padding: clamp(24px, 2.5vw, 32px);
                border-radius: 32px;
                min-height: 500px;
            }

            .internal-org-container {
                padding: clamp(70px, 7.29vw, 100px) clamp(20px, 2.08vw, 30px) clamp(30px, 3.13vw, 40px);
            }

            .internal-org-columns {
                grid-template-columns: repeat(3, 1fr);
                gap: clamp(12px, 1.25vw, 18px);
            }

            .internal-org-column-title {
                font-size: clamp(10px, 1.04vw, 14px);
                padding: clamp(6px, 0.63vw, 10px) clamp(10px, 1.04vw, 14px);
            }

            .internal-org-position-card {
                max-width: clamp(100px, 10.42vw, 140px);
                padding: clamp(10px, 1.04vw, 14px) clamp(8px, 0.83vw, 12px);
            }

            .internal-org-position-title {
                font-size: clamp(9px, 0.94vw, 12px);
            }

            .internal-org-position-name {
                font-size: clamp(8px, 0.83vw, 11px);
            }

            .internal-org-bg-text img {
                width: clamp(250px, 26.04vw, 600px);
                height: clamp(250px, 26.04vw, 600px);
            }
        }

        @media (max-width: 768px) {
            .internal-org-columns {
                grid-template-columns: repeat(2, 1fr);
            }

            .internal-org-workspace {
                padding: clamp(20px, 2.08vw, 24px);
                min-height: 450px;
                border-radius: 24px;
            }

            .internal-org-title-overlay {
                top: clamp(20px, 2.08vw, 24px);
                left: clamp(20px, 2.08vw, 24px);
        }

            .internal-org-title-main {
                font-size: clamp(20px, 2.08vw, 28px);
            }

            .internal-org-container {
                padding: clamp(60px, 6.25vw, 80px) clamp(15px, 1.56vw, 20px) clamp(20px, 2.08vw, 30px);
            }
        }

        @media (max-width: 480px) {
            .internal-org-columns {
                grid-template-columns: 1fr;
            }
        }

        /* 响应式调整 */
        @media (max-width: 1024px) {
            .org-workspace {
                padding: clamp(24px, 2.5vw, 32px);
                border-radius: 32px;
                min-height: 500px;
            }

            .org-tree-root-container {
                padding: clamp(20px, 2.08vw, 30px);
            }

            .org-cards-wall {
                grid-template-columns: repeat(auto-fill, minmax(clamp(120px, 12.5vw, 160px), 1fr));
                gap: clamp(12px, 1.25vw, 18px);
                padding: clamp(16px, 1.67vw, 24px);
            }

            .org-card-level-ceo {
                grid-column: span 2;
                grid-row: span 1;
                min-height: clamp(140px, 14.58vw, 200px);
                padding: clamp(14px, 1.46vw, 18px);
            }

            .org-card-level-ceo .org-card-role {
                font-size: clamp(20px, 2.08vw, 28px);
            }

            .org-card-level-ceo .org-card-name {
                font-size: clamp(16px, 1.67vw, 20px);
            }

            .org-card-level-clevel {
                min-height: clamp(100px, 10.42vw, 140px);
                padding: clamp(12px, 1.25vw, 16px);
            }

            .org-card-level-clevel .org-card-role {
                font-size: clamp(14px, 1.46vw, 18px);
            }

            .org-card-level-clevel .org-card-name {
                font-size: clamp(12px, 1.25vw, 16px);
            }

            .org-card-level-other {
                min-height: clamp(90px, 9.38vw, 120px);
                padding: clamp(12px, 1.25vw, 16px);
            }

            .org-card-level-other .org-card-role {
                font-size: clamp(12px, 1.25vw, 16px);
            }

            .org-card-level-other .org-card-name {
                font-size: clamp(11px, 1.15vw, 14px);
            }

            .org-bg-text img {
                width: clamp(250px, 26.04vw, 600px);
                height: clamp(250px, 26.04vw, 600px);
            }
        }

        @media (max-width: 640px) {
            .org-cards-wall {
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
                gap: 12px;
                padding: clamp(12px, 1.25vw, 16px);
            }

            .org-card-level-ceo {
                grid-column: span 1;
                min-height: clamp(120px, 12.5vw, 160px);
            }

            .org-card-level-ceo .org-card-role {
                font-size: clamp(18px, 1.88vw, 24px);
            }

            .org-card-level-ceo .org-card-name {
                font-size: clamp(14px, 1.46vw, 18px);
            }
        }

        @media (max-width: 768px) {
            .org-workspace {
                padding: clamp(20px, 2.08vw, 24px);
                min-height: 450px;
                border-radius: 24px;
            }

            .org-title-overlay {
                top: clamp(20px, 2.08vw, 24px);
                left: clamp(20px, 2.08vw, 24px);
            }

            .org-title-main {
                font-size: clamp(20px, 2.08vw, 28px);
            }

            .org-tree-root-container {
                padding: clamp(20px, 2.08vw, 30px) clamp(20px, 2.08vw, 30px);
            }

            .org-tree-branch {
                gap: clamp(12px, 1.25vw, 20px);
            }

            .org-node-card:not(.small) {
                width: 120px;
            }

            .org-node-card.small {
                width: 90px;
            }

            .org-pa-container {
                position: relative;
                left: auto;
                top: auto;
                margin-top: 16px;
            }

            .org-bg-text img {
                width: clamp(200px, 20.83vw, 400px);
                height: clamp(200px, 20.83vw, 400px);
            }
        }

        /* 响应式调整 */
        @media (max-width: 1536px) {
            .mermaid-org-chart {
                height: auto;
                min-height: 765px;
            }
            
            .org-chart-logo {
                height: min(1312px, 85vw);
                width: min(1312px, 85vw);
            }
            
            .org-chart-title-bg {
                width: min(424px, 27.6vw);
                height: min(82px, 5.34vw);
                top: min(25px, 1.63vw);
            }
            
            .org-chart-title {
                font-size: min(48px, 3.13vw);
                left: min(44px, 2.86vw);
                top: min(37px, 2.4vw);
                width: min(336px, 21.88vw);
            }
            
            .org-chart-title-line {
                width: min(336px, 21.88vw);
                top: min(95px, 6.19vw);
                left: min(44px, 2.86vw);
            }
        }

        @media (max-width: 768px) {
            .mermaid-org-chart {
                padding: 0;
                min-height: 400px;
                overflow-x: auto;
                overflow-y: visible;
            }
            
            .org-chart-title-bg {
                width: min(300px, 80vw);
                height: 60px;
            }

            .org-chart-title {
                font-size: 32px;
                left: 20px;
                top: 20px;
                width: min(260px, 70vw);
            }
            
            .org-chart-title-line {
                width: min(260px, 70vw);
                top: 70px;
                left: 20px;
            }
            
            .org-tree-content {
                padding: 100px 10px 10px 10px;
            }

            .org-tree {
                padding-left: clamp(10px, 1.04vw, 20px);
            }

            .org-tree-root {
                margin-right: clamp(20px, 2.08vw, 30px);
            }

            .org-clevel-container {
                gap: clamp(15px, 1.56vw, 25px);
            }

            .org-node-box {
                min-width: clamp(100px, 10.42vw, 140px);
                max-width: clamp(140px, 14.58vw, 180px);
                padding: clamp(8px, 0.83vw, 12px) clamp(10px, 1.04vw, 14px);
                min-height: 50px;
            }

            .org-subordinates-container {
                gap: clamp(15px, 1.56vw, 20px);
                margin-left: clamp(20px, 2.08vw, 30px);
            }

            .org-ceo-to-clevel,
            .org-clevel-to-sub {
                width: clamp(20px, 2.08vw, 30px);
            }

            .org-sub-horizontal-line {
                width: clamp(20px, 2.08vw, 30px);
                left: calc(-1 * clamp(20px, 2.08vw, 30px));
            }

            .org-sub-vertical-line {
                left: calc(-1 * clamp(20px, 2.08vw, 30px));
            }
        }

        /* OrgChart.js 样式 */
        .orgchart-container-wrapper {
            background: #ffffff;
            border-radius: 12px;
            padding: clamp(20px, 2.08vw, 30px);
            position: relative;
            overflow: hidden;
            min-height: 600px;
            max-height: 800px;
            background-image: none !important;
            border: 2px solid #f7931e;
        }
        
        /* 当包含内部组织架构图时，允许容器滚动 */
        .orgchart-container-wrapper:has(#internal-orgchart-container) {
            overflow: hidden;
            max-height: none;
        }
        
        /* 部门切换按钮组 */
        .internal-dept-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: clamp(12px, 1.25vw, 16px);
            margin-bottom: clamp(30px, 3.13vw, 40px);
            position: relative;
            z-index: 2;
        }
        
        .internal-dept-btn {
            background: #ffffff;
            border: 2px solid #e5e7eb;
            border-radius: clamp(6px, 0.63vw, 8px);
            padding: clamp(6px, 0.63vw, 8px) clamp(12px, 1.25vw, 16px);
            font-size: clamp(12px, 1.25vw, 14px);
            font-weight: 600;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        
        .internal-dept-btn:hover {
            border-color: #ff5c00;
            color: #ff5c00;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 92, 0, 0.2);
        }
        
        .internal-dept-btn.active {
            background: #ff5c00;
            border-color: #ff5c00;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(255, 92, 0, 0.3);
        }
        
        /* 内部组织架构图容器 */
        #internal-orgchart-container {
            position: relative;
            z-index: 1;
            overflow: visible;
            max-height: none;
            min-height: 600px;
        }
        
        /* 内部部门组织架构图样式 */
        .internal-dept-chart-wrapper {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
            pointer-events: none;
        }
        
        .internal-dept-chart-wrapper.active {
            position: relative;
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }
        
        .internal-dept-orgchart {
            position: relative;
            overflow: hidden;
            background-image: none !important;
        }
        
        /* 移除内部组织架构图的网格背景 */
        .internal-dept-orgchart .orgchart {
            overflow: hidden !important;
            background-image: none !important;
            margin: 0 auto;
        }
        
        .internal-dept-orgchart .orgchart-wrapper {
            overflow: hidden !important;
            background-image: none !important;
            margin: 0 auto;
        }
        
        .internal-dept-orgchart * {
            background-image: none !important;
        }
        
        /* 内部组织架构图连线颜色 - 黑色 */
        .internal-dept-orgchart svg.edge {
            stroke: #000000 !important;
            stroke-width: 2px !important;
        }
        
        .internal-dept-orgchart svg path {
            stroke: #000000 !important;
            stroke-width: 2px !important;
        }
        
        .internal-dept-orgchart .lines .topEdge {
            border-top-color: #000000 !important;
            border-top-width: 2px !important;
        }
        
        .internal-dept-orgchart .lines .rightEdge {
            border-right-color: #000000 !important;
            border-right-width: 2px !important;
        }
        
        .internal-dept-orgchart .lines .bottomEdge {
            border-bottom-color: #000000 !important;
            border-bottom-width: 2px !important;
        }
        
        .internal-dept-orgchart .lines .leftEdge {
            border-left-color: #000000 !important;
            border-left-width: 2px !important;
        }
        
        /* 标题左对齐 */
        .orgchart-title-wrapper {
            text-align: left;
            margin-left: 0;
        }
        
        /* 背景Logo图片 */
        .orgchart-container-wrapper::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: clamp(600px, 62.5vw, 1000px);
            height: clamp(600px, 62.5vw, 1000px);
            background: url('../images/images/logo.png') no-repeat center;
            background-size: contain;
            opacity: 0.08;
            z-index: 0;
            pointer-events: none;
        }
        
        .orgchart-title-wrapper {
            position: relative;
            z-index: 2;
            font-size: clamp(24px, 2.5vw, 32px);
            font-weight: 700;
            color: #ffffff;
            background: #ff5c00;
            padding: clamp(16px, 1.67vw, 20px) clamp(30px, 3.13vw, 40px);
            border-radius: 30px;
            display: inline-block;
            margin-bottom: 40px;
            box-shadow: 0 4px 12px rgba(255, 92, 0, 0.3);
        }
        
        /* OrgChart 容器 */
        #orgchart-container {
            background: transparent;
            position: relative;
            z-index: 1;
            overflow: hidden;
            background-image: none !important;
            /* display: flex; */
            justify-content: center;
            align-items: center;
            width: 100%;
        }
        
        /* 禁用组织架构图的滚动 */
        #orgchart-container .orgchart {
            overflow: hidden !important;
            background-image: none !important;
            margin: 0 auto;
        }
        
        #orgchart-container .orgchart-wrapper {
            overflow: hidden !important;
            background-image: none !important;
            margin: 0 auto;
        }
        
        /* 居中组织架构图的根节点 */
        #orgchart-container .orgchart .orgchart-container {
            display: flex;
            justify-content: center;
            margin: 0 auto;
        }
        
        /* 移除OrgChart.js默认的网格背景 */
        #orgchart-container * {
            background-image: none !important;
        }
        
        /* 连线颜色 - 黑色 */
        #orgchart-container svg.edge {
            stroke: #000000 !important;
            stroke-width: 2px !important;
        }
        
        #orgchart-container svg path {
            stroke: #000000 !important;
            stroke-width: 2px !important;
        }
        
        #orgchart-container .lines .topEdge {
            border-top-color: #000000 !important;
            border-top-width: 2px !important;
        }
        
        #orgchart-container .lines .rightEdge {
            border-right-color: #000000 !important;
            border-right-width: 2px !important;
        }
        
        #orgchart-container .lines .leftEdge {
            border-left-color: #000000 !important;
            border-left-width: 2px !important;
        }
        
        #orgchart-container .lines .bottomEdge {
            border-bottom-color: #000000 !important;
            border-bottom-width: 2px !important;
        }
        
        #orgchart-container .lines {
            border-color: #000000 !important;
        }
        
        #orgchart-container .horizontalEdge {
            border-color: #000000 !important;
        }
        
        #orgchart-container .verticalEdge {
            border-color: #000000 !important;
        }
        
        /* 节点样式 - 透明背景，由内部元素控制 */
        #orgchart-container .node {
            background: transparent !important;
            border: none !important;
            border-radius: 12px;
            padding: 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            width: auto;
            min-width: clamp(120px, 12.5vw, 140px);
            text-align: center;
            overflow: hidden;
        }
        
        /* 职位部分 - 橙色背景，白色文字 */
        .orgchart-node-title {
            background: #ff5c00 !important;
            color: #ffffff !important;
            font-weight: 700;
            padding: clamp(12px, 1.25vw, 16px) clamp(16px, 1.67vw, 20px);
            font-size: clamp(16px, 1.67vw, 18px);
            line-height: 1.3;
            margin: 0;
            border-radius: 12px 12px 0 0;
        }
        
        /* 名字部分 - 白色背景，黑色文字 */
        .orgchart-node-content {
            background: #ffffff !important;
            color: #000000 !important;
            font-size: clamp(13px, 1.35vw, 14px);
            padding: clamp(10px, 1.04vw, 14px) clamp(16px, 1.67vw, 20px);
            line-height: 1.4;
            margin: 0;
            border-radius: 0 0 12px 12px;
            border-top: 1px solid #e5e7eb;
        }

    </style>
    <!-- 引入 OrgChart.js 库 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/orgchart@2.1.9/dist/css/jquery.orgchart.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/orgchart@2.1.9/dist/js/jquery.orgchart.min.js"></script>
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
                            <div class="company-name-large"><?php echo htmlspecialchars($strategyData['companyOverview']['companyName'] ?? 'KUNZZ HOLDINGS SDN BHD'); ?></div>
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
                <?php if (!empty($strategyData['corporateCore'])): 
                    $corporateCore = $strategyData['corporateCore'];
                ?>
                <div class="section">
                    <div class="core-header">
                        <div class="core-main-title">企业核心</div>
                    </div>
                    <div class="core-grid">
                        <!-- 01 Mission -->
                        <?php if (!empty($corporateCore['mission'])): ?>
                        <div class="core-card">
                            <div class="core-card-number">01</div>
                            <div class="core-card-content-wrapper">
                                <div class="core-card-title">使命:初心&感性的目标</div>
                                <div class="core-card-content">
                                    <?php echo nl2br(htmlspecialchars($corporateCore['mission'])); ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- 02 Vision -->
                        <?php if (!empty($corporateCore['vision'])): ?>
                        <div class="core-card">
                            <div class="core-card-number">02</div>
                            <div class="core-card-content-wrapper">
                                <div class="core-card-title">愿景:理性可具体化的目标</div>
                                <div class="core-card-content">
                                    <?php echo nl2br(htmlspecialchars($corporateCore['vision'])); ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- 03 Culture -->
                        <?php if (!empty($corporateCore['culture']) && is_array($corporateCore['culture'])): ?>
                        <div class="core-card">
                            <div class="core-card-number">03</div>
                            <div class="core-card-content-wrapper">
                                <div class="core-card-title">文化:做人的态度</div>
                                <div class="core-card-content">
                                    <?php echo htmlspecialchars(implode(', ', $corporateCore['culture'])); ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- 04 Values -->
                        <?php if (!empty($corporateCore['values']) && is_array($corporateCore['values'])): ?>
                        <div class="core-card">
                            <div class="core-card-number">04</div>
                            <div class="core-card-content-wrapper">
                                <div class="core-card-title">价值观:做事的态度</div>
                                <div class="core-card-content">
                                    <?php echo htmlspecialchars(implode(', ', $corporateCore['values'])); ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Culture Explanation - 新设计 -->
                <?php if (!empty($strategyData['cultureExplanation']) && is_array($strategyData['cultureExplanation'])): ?>
                <div class="section">
                    <div class="culture-explanation-header">
                        <div class="culture-explanation-title-cn">文化解说&考核</div>
                    </div>
                    <div class="culture-explanation-grid">
                        <?php foreach ($strategyData['cultureExplanation'] as $index => $explanation): ?>
                        <div class="culture-explanation-card">
                            <div class="culture-explanation-content">
                                <div class="culture-explanation-number"><?php echo str_pad($index + 1, 2, '0', STR_PAD_LEFT); ?></div>
                                <div class="culture-explanation-key"><?php echo htmlspecialchars($explanation['key'] ?? ''); ?></div>
                                <div class="culture-explanation-description">
                                    <?php echo nl2br(htmlspecialchars($explanation['description'] ?? '')); ?>
                                </div>
                            </div>
                            <?php if (!empty($explanation['scoring']) && is_array($explanation['scoring'])): ?>
                            <div class="culture-scoring">
                                <div class="culture-scoring-title">评分标准:</div>
                                <?php 
                                // 按分数排序
                                usort($explanation['scoring'], function($a, $b) {
                                    return ($a['point'] ?? 0) <=> ($b['point'] ?? 0);
                                });
                                foreach ($explanation['scoring'] as $score): ?>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point"><?php echo intval($score['point'] ?? 0); ?>分:</div>
                                    <div class="culture-scoring-description"><?php echo htmlspecialchars($score['description'] ?? ''); ?></div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Values Explanation - 新设计，风格与 Culture Explanation 一致 -->
                <?php if (!empty($strategyData['valuesExplanation']) && is_array($strategyData['valuesExplanation'])): ?>
                <div class="section">
                    <div class="values-explanation-header">
                        <div class="values-explanation-title-cn">价值观解说&考核</div>
                    </div>
                    <div class="culture-explanation-grid">
                        <?php foreach ($strategyData['valuesExplanation'] as $index => $explanation): ?>
                        <div class="culture-explanation-card">
                            <div class="culture-explanation-content">
                                <div class="culture-explanation-number"><?php echo str_pad($index + 1, 2, '0', STR_PAD_LEFT); ?></div>
                                <div class="culture-explanation-key"><?php echo htmlspecialchars($explanation['key'] ?? ''); ?></div>
                                <div class="culture-explanation-description">
                                    <?php echo nl2br(htmlspecialchars($explanation['description'] ?? '')); ?>
                                </div>
                            </div>
                            <?php if (!empty($explanation['scoring']) && is_array($explanation['scoring'])): ?>
                            <div class="culture-scoring">
                                <div class="culture-scoring-title">评分标准:</div>
                                <?php 
                                // 按分数排序
                                usort($explanation['scoring'], function($a, $b) {
                                    return ($a['point'] ?? 0) <=> ($b['point'] ?? 0);
                                });
                                foreach ($explanation['scoring'] as $score): ?>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point"><?php echo intval($score['point'] ?? 0); ?>分:</div>
                                    <div class="culture-scoring-description"><?php echo htmlspecialchars($score['description'] ?? ''); ?></div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Organization Structure - 使用 OrgChart.js -->
                <?php 
                // 转换组织架构数据为 OrgChart.js 所需的树形格式
                function convertToOrgChartFormat($orgStructure) {
                    // CEO节点（根节点）
                    if (empty($orgStructure['ceo'])) {
                        return null;
                    }
                    
                    $ceoTitle = $orgStructure['ceo']['title'] ?? $orgStructure['ceo']['fullTitle'] ?? 'CEO';
                    $ceoName = $orgStructure['ceo']['name'] ?? '';
                    
                    $ceoNode = [
                        'id' => 'ceo',
                        'name' => $ceoName ?: '—',
                        'title' => $ceoTitle,
                        'level' => 'ceo',
                        'children' => []
                    ];
                    
                    // C-Level节点作为CEO的子节点
                    if (!empty($orgStructure['cLevel']) && is_array($orgStructure['cLevel'])) {
                        foreach ($orgStructure['cLevel'] as $index => $member) {
                            $memberTitle = $member['title'] ?? $member['fullTitle'] ?? '';
                            $memberName = $member['name'] ?? '';
                            
                            $cLevelNode = [
                                'id' => 'clevel_' . $index,
                                'name' => $memberName ?: '—',
                                'title' => $memberTitle,
                                'level' => 'clevel',
                                'children' => []
                            ];
                            
                            // 处理下属
                            if (!empty($member['subordinates']) && is_array($member['subordinates'])) {
                                foreach ($member['subordinates'] as $subIndex => $sub) {
                                    $subTitle = $sub['title'] ?? $sub['fullTitle'] ?? '';
                                    $subName = $sub['name'] ?? '';
                                    
                                    $subNode = [
                                        'id' => 'sub_' . $index . '_' . $subIndex,
                                        'name' => $subName ?: '—',
                                        'title' => $subTitle,
                                        'level' => 'subordinate'
                                    ];
                                    $cLevelNode['children'][] = $subNode;
                                }
                            }
                            
                            $ceoNode['children'][] = $cLevelNode;
                        }
                    }
                    
                    // PA节点也作为CEO的子节点
                    if (!empty($orgStructure['pa'])) {
                        $paTitle = $orgStructure['pa']['title'] ?? $orgStructure['pa']['fullTitle'] ?? 'PA';
                        $paName = $orgStructure['pa']['name'] ?? '';
                        
                        $paNode = [
                            'id' => 'pa',
                            'name' => $paName ?: '—',
                            'title' => $paTitle,
                            'level' => 'pa'
                        ];
                        $ceoNode['children'][] = $paNode;
                    }
                    
                    return $ceoNode;
                }
                
                $orgChartData = null;
                if (!empty($strategyData['organizationStructure'])): 
                    $orgChartData = convertToOrgChartFormat($strategyData['organizationStructure']);
                ?>
                <div class="section">
                    <div class="orgchart-container-wrapper">
                        <h1 class="orgchart-title-wrapper">高层组织架构图</h1>
                        <div id="orgchart-container" style="width: 100%; min-height: 600px;"></div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php
                // 转换内部组织架构数据为 OrgChart.js 所需的树形格式
                // 返回每个部门作为独立的树形结构数组
                function convertInternalOrgToOrgChartFormat($internalOrgData) {
                    if (empty($internalOrgData) || empty($internalOrgData['departments'])) {
                        return [];
                    }
                    
                    $departmentTrees = [];
                    $departments = $internalOrgData['departments'];
                    
                    foreach ($departments as $deptIndex => $dept) {
                        $deptName = $dept['name'] ?? '';
                        $positions = $dept['positions'] ?? [];
                        
                        if (empty($positions)) {
                            continue;
                        }
                        
                        // 部门根节点（使用第一个职位作为部门头）
                        $firstPosition = $positions[0];
                        $deptTitle = $firstPosition['title'] ?? $deptName;
                        $deptNameValue = $firstPosition['name'] ?? '';
                        
                        $deptRootNode = [
                            'id' => 'dept_' . $deptIndex,
                            'name' => $deptNameValue ?: '—',
                            'title' => $deptTitle,
                            'level' => 'department',
                            'departmentName' => $deptName, // 保存部门名称用于显示
                            'children' => []
                        ];
                        
                        // 添加该部门的其他职位作为子节点
                        for ($i = 1; $i < count($positions); $i++) {
                            $pos = $positions[$i];
                            $posTitle = $pos['title'] ?? '';
                            $posName = $pos['name'] ?? '';
                            
                            $posNode = [
                                'id' => 'dept_' . $deptIndex . '_pos_' . $i,
                                'name' => $posName ?: '—',
                                'title' => $posTitle,
                                'level' => 'position'
                            ];
                            
                            $deptRootNode['children'][] = $posNode;
                        }
                        
                        $departmentTrees[] = $deptRootNode;
                    }
                    
                    return $departmentTrees;
                }
                
                // 内部组织架构数据 - 从JSON读取
                $internalOrgData = $strategyData['internalOrganization'] ?? null;
                
                $internalOrgChartData = $internalOrgData ? convertInternalOrgToOrgChartFormat($internalOrgData) : [];
                if (!empty($internalOrgChartData) && is_array($internalOrgChartData)):
                ?>

                <!-- 内部组织架构图 -->
                <div class="section">
                    <div class="orgchart-container-wrapper">
                        <h1 class="orgchart-title-wrapper">内部组织架构图</h1>
                        
                        <!-- 部门切换按钮组 -->
                        <div class="internal-dept-buttons">
                            <?php foreach ($internalOrgChartData as $deptIndex => $deptTree): ?>
                                <button 
                                    class="internal-dept-btn <?php echo $deptIndex === 0 ? 'active' : ''; ?>" 
                                    data-dept-index="<?php echo $deptIndex; ?>"
                                    onclick="switchInternalDept(<?php echo $deptIndex; ?>)"
                                >
                                    <?php echo htmlspecialchars($deptTree['departmentName'] ?? ''); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- 组织架构图容器 -->
                        <div id="internal-orgchart-container" style="width: 100%; min-height: 600px;">
                            <?php foreach ($internalOrgChartData as $deptIndex => $deptTree): ?>
                                <div class="internal-dept-chart-wrapper <?php echo $deptIndex === 0 ? 'active' : ''; ?>" data-dept-index="<?php echo $deptIndex; ?>">
                                    <div class="internal-dept-orgchart" id="internal-dept-chart-<?php echo $deptIndex; ?>" style="width: 100%; min-height: 500px;"></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Strategic Objectives -->
                <?php 
                $strategicObjectives = $strategyData['strategicObjectives'] ?? [];
                $ultimateGoal = $strategyData['companyOverview']['ultimateGoal'] ?? '';
                $strategyEndYear = $strategyData['companyOverview']['strategyEndYear'] ?? date('Y') + 5;
                
                // 将所有年份的目标合并到一个数组中，用于显示
                $allObjectives = [];
                foreach ($strategicObjectives as $year => $objectives) {
                    foreach ($objectives as $obj) {
                        $allObjectives[] = array_merge($obj, ['year' => $year]);
                    }
                }
                ?>
                <?php if (!empty($allObjectives)): ?>
                <div class="strategic-objectives-section">
                    <!-- 背景装饰 -->
                    <div class="strategic-bg-decor"></div>
                    
                    <div class="strategic-container">
                        <!-- 头部区域 -->
                        <header class="strategic-header">
                            <div class="strategic-header-content">
                                <div class="strategic-header-left">
                                    <div class="strategic-badge">
                                        <svg class="strategy-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"/>
                                            <path d="M12 6v6l4 2"/>
                                        </svg>
                                        <span>最终目标</span>
                                    </div>
                                    <h1 class="strategic-main-title">
                                        <?php echo htmlspecialchars($strategyEndYear); ?>年
                                        <?php if (!empty($ultimateGoal)): ?>
                                        <span class="strategic-year"><?php echo htmlspecialchars($ultimateGoal); ?></span>
                                        <?php endif; ?>
                                    </h1>
                                    </div>
                                </div>
                        </header>

                        <!-- 主要内容区域 -->
                        <main class="strategic-main">
                            <!-- 策略列表 -->
                            <div class="strategic-list-wrapper">
                                <h2 class="strategic-list-title">
                                    策略 · 检核
                                    <span class="strategic-list-count" id="strategicListCount">5</span>
                                </h2>
                                <div class="strategic-list" id="strategicList">
                                    <?php foreach ($allObjectives as $index => $obj): ?>
                                    <button 
                                        class="strategy-card <?php echo $index === 0 ? 'active' : ''; ?>" 
                                        data-strategy-index="<?php echo $index; ?>"
                                        onclick="selectStrategy(<?php echo $index; ?>)"
                                    >
                                        <div class="strategy-icon-wrapper">
                                            <svg class="strategy-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                                <circle cx="9" cy="7" r="4"/>
                                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                            </svg>
                                        </div>
                                        <div class="strategy-content">
                                            <div class="strategy-meta">
                                                <span class="strategy-id">S<?php echo $index + 1; ?>-<?php echo htmlspecialchars($obj['department'] ?? ''); ?> • <?php echo htmlspecialchars($obj['year'] ?? ''); ?></span>
                                                <svg class="strategy-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="<?php echo $index === 0 ? '' : 'display: none;'; ?>">
                                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                                    <polyline points="22 4 12 14.01 9 11.01"/>
                                                </svg>
                                            </div>
                                            <h3 class="strategy-title"><?php echo htmlspecialchars($obj['strategy'] ?? ''); ?></h3>
                                            <p class="strategy-description"><?php echo htmlspecialchars($obj['department'] ?? ''); ?></p>
                                        </div>
                                        <svg class="strategy-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="9 18 15 12 9 6"/>
                                        </svg>
                                    </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- 详细视图 -->
                            <div class="strategic-details" id="strategicDetails">
                                <div class="details-header">
                                    <svg class="details-header-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                        <circle cx="9" cy="7" r="4"/>
                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                    </svg>
                                    <div>
                                        <div class="details-badge" id="detailsBadge">人事部</div>
                                        <h2 class="details-title" id="detailsTitle">建立高效且有吸引力的人才管理体系</h2>
                                    </div>
                                </div>

                                <div class="details-body">
                                    <!-- 指标和措施 -->
                                    <div class="details-section">
                                        <h4 class="details-section-title">
                                            <svg class="details-section-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                                            </svg>
                                            策略 · 检核
                                        </h4>
                                        
                                        <div class="measure-item">
                                            <div class="measure-header">
                                                <span class="measure-badge">D1</span>
                                                <span class="measure-label">关键指标</span>
                                            </div>
                                            <ul class="measure-list" id="measureList">
                                                <li class="measure-list-item">
                                                    <div class="measure-dot"></div>
                                                    <span class="measure-text">人才引进与储备</span>
                                                </li>
                                                <li class="measure-list-item">
                                                    <div class="measure-dot"></div>
                                                    <span class="measure-text">文化宣传</span>
                                                </li>
                                        </ul>
                                    </div>
                                    </div>

                                    <!-- 执行计划 -->
                                    <div class="details-section">
                                        <h4 class="details-section-title">
                                            <svg class="details-section-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                                <line x1="16" y1="2" x2="16" y2="6"/>
                                                <line x1="8" y1="2" x2="8" y2="6"/>
                                                <line x1="3" y1="10" x2="21" y2="10"/>
                                            </svg>
                                            行动计划
                                        </h4>
                                        
                                        <div class="execution-plan">
                                            <div class="execution-pic">
                                                <div class="execution-pic-icon">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                                        <circle cx="12" cy="7" r="4"/>
                                                    </svg>
                                        </div>
                                                <div class="execution-pic-info">
                                                    <span class="execution-pic-label">负责人</span>
                                                    <span class="execution-pic-name" id="picName">Paris</span>
                                                </div>
                                            </div>
                                            
                                            <div class="execution-dates">
                                                <div class="execution-date-item">
                                                    <span class="execution-date-label">开始日期</span>
                                                    <span class="execution-date-value" id="startDate">—</span>
                                        </div>
                                                <div class="execution-date-item execution-date-divider">
                                                    <span class="execution-date-label">完成日期</span>
                                                    <span class="execution-date-value" id="endDate">—</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                        </main>
                    </div>
                </div>
                <?php endif; ?>
                
                <script>
                // 从PHP传递的战略目标数据
                const strategiesData = <?php echo json_encode($allObjectives, JSON_UNESCAPED_UNICODE); ?>;
                
                // 转换数据格式以匹配原有结构
                const formattedStrategiesData = strategiesData.map((obj, index) => ({
                    deptName: 'S' + (index + 1) + '-' + (obj.department || ''),
                    deptDisplay: obj.department || '',
                    strategy: obj.strategy || '',
                    department: obj.department || '',
                    pic: obj.pic || '',
                    startDate: obj.startDate || '',
                    endDate: obj.endDate || '',
                    dashboardMetrics: obj.dashboardMetrics || [],
                    year: obj.year || ''
                }));
                
                function selectStrategy(index) {
                    const strategy = formattedStrategiesData[index];
                    if (!strategy) return;
                    
                    // 更新卡片状态
                    document.querySelectorAll('.strategy-card').forEach((card, i) => {
                        if (i === index) {
                            card.classList.add('active');
                            card.querySelector('.strategy-check').style.display = 'block';
                        } else {
                            card.classList.remove('active');
                            card.querySelector('.strategy-check').style.display = 'none';
                        }
                    });
                    
                    // 更新详细视图
                    const detailsEl = document.getElementById('strategicDetails');
                    detailsEl.classList.add('hidden');
                    
                    setTimeout(() => {
                        // 更新内容
                        document.getElementById('detailsTitle').textContent = strategy.strategy;
                        document.getElementById('detailsBadge').textContent = strategy.deptDisplay || strategy.deptName || 'Selected Pillar';
                        document.getElementById('picName').textContent = strategy.pic || '—';
                        
                        // 格式化日期
                        const formatDate = (dateStr) => {
                            if (!dateStr) return '—';
                            try {
                                const date = new Date(dateStr);
                                const year = date.getFullYear();
                                const month = String(date.getMonth() + 1).padStart(2, '0');
                                const day = String(date.getDate()).padStart(2, '0');
                                return `${year}-${month}-${day}`;
                            } catch (e) {
                                return dateStr;
                            }
                        };
                        
                        document.getElementById('startDate').textContent = formatDate(strategy.startDate);
                        document.getElementById('endDate').textContent = formatDate(strategy.endDate);
                        
                        // 更新指标
                        const metricsList = document.getElementById('measureList');
                        if (metricsList) {
                            if (strategy.dashboardMetrics && strategy.dashboardMetrics.length > 0) {
                                metricsList.innerHTML = strategy.dashboardMetrics.map(metric => 
                                    `<li class="measure-list-item">
                                        <div class="measure-dot"></div>
                                        <span class="measure-text">${metric}</span>
                                    </li>`
                                ).join('');
                            } else {
                                metricsList.innerHTML = '<li class="measure-list-item"><span class="measure-text">暂无指标</span></li>';
                            }
                        }
                        
                        detailsEl.classList.remove('hidden');
                    }, 300);
                }
                
                // 初始化动画
                document.addEventListener('DOMContentLoaded', function() {
                    const cards = document.querySelectorAll('.strategy-card');
                    cards.forEach((card, index) => {
                        card.style.opacity = '0';
                        card.style.transform = 'translateX(-30px)';
                        setTimeout(() => {
                            card.style.transition = 'all 0.8s ease';
                            card.style.opacity = '1';
                            card.style.transform = 'translateX(0)';
                        }, 300 + (index * 100));
                    });

                    // 更新策略总数
                    const countEl = document.getElementById('strategicListCount');
                    if (countEl) {
                        countEl.textContent = formattedStrategiesData.length.toString();
                    }
                });
                </script>

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

    <!-- OrgChart.js 初始化脚本 -->
    <?php if (!empty($orgChartData)): ?>
    <script>
        $(document).ready(function() {
            // 组织架构数据（已经是树形结构）
            const orgData = <?php echo json_encode($orgChartData, JSON_UNESCAPED_UNICODE); ?>;
            
            if (!orgData) {
                console.error('组织架构数据为空');
                $('#orgchart-container').html('<p style="text-align: center; color: #6b7280; padding: 40px;">无法加载组织架构数据</p>');
                return;
            }
            
            console.log('组织架构数据:', orgData);
            
            // 初始化组织架构图 - OrgChart.js 使用树形结构
            $('#orgchart-container').orgchart({
                'data': orgData,
                'nodeContent': 'title',
                'nodeId': 'id',
                'pan': false,
                'zoom': false,
                'toggleSiblingsResp': true,
                'createNode': function($node, data) {
                    // 自定义节点样式
                    const level = data.level || '';
                    $node.addClass('level-' + level);
                    
                    // 自定义节点内容 - 显示职位和名字
                    const title = data.title || '—';
                    const name = data.name || '—';
                    
                    $node.html(
                        '<div class="orgchart-node-title">' + title + '</div>' +
                        '<div class="orgchart-node-content">' + name + '</div>'
                    );
                },
                'draggable': false,
                'direction': 't2b'
            });
            
            // 居中显示组织架构图
            setTimeout(function() {
                const orgchartEl = $('#orgchart-container .orgchart');
                if (orgchartEl.length) {
                    const containerWidth = $('#orgchart-container').width();
                    const chartWidth = orgchartEl.outerWidth();
                    if (chartWidth < containerWidth) {
                        const offsetLeft = (containerWidth - chartWidth) / 2;
                        orgchartEl.css('margin-left', offsetLeft + 'px');
                    }
                }
            }, 100);
        });
    </script>
    <?php endif; ?>
    
    <!-- 内部组织架构图 OrgChart.js 初始化脚本 -->
    <?php if (!empty($internalOrgChartData)): ?>
    <script>
        // 存储所有部门的组织架构数据
        const internalOrgData = <?php echo json_encode($internalOrgChartData, JSON_UNESCAPED_UNICODE); ?>;
        const initializedCharts = {}; // 记录已初始化的图表
        
        // 切换部门函数
        function switchInternalDept(deptIndex) {
            // 更新按钮状态
            $('.internal-dept-btn').removeClass('active');
            $('.internal-dept-btn[data-dept-index="' + deptIndex + '"]').addClass('active');
            
            // 更新图表显示
            $('.internal-dept-chart-wrapper').removeClass('active');
            $('.internal-dept-chart-wrapper[data-dept-index="' + deptIndex + '"]').addClass('active');
            
            // 如果该部门的图表还未初始化，则初始化它
            if (!initializedCharts[deptIndex] && internalOrgData[deptIndex]) {
                initializeDeptChart(deptIndex, internalOrgData[deptIndex]);
            }
        }
        
        // 初始化部门组织架构图
        function initializeDeptChart(index, deptTree) {
            const containerId = '#internal-dept-chart-' + index;
            const $container = $(containerId);
            
            if ($container.length === 0) {
                console.warn('容器不存在:', containerId);
                return;
            }
            
            // 初始化该部门的组织架构图
            $container.orgchart({
                'data': deptTree,
                'nodeContent': 'title',
                'nodeId': 'id',
                'pan': false,
                'zoom': false,
                'toggleSiblingsResp': true,
                'createNode': function($node, data) {
                    // 自定义节点样式
                    const level = data.level || '';
                    $node.addClass('level-' + level);
                    
                    // 自定义节点内容 - 显示职位和名字
                    const title = data.title || '—';
                    const name = data.name || '—';
                    
                    $node.html(
                        '<div class="orgchart-node-title">' + title + '</div>' +
                        '<div class="orgchart-node-content">' + name + '</div>'
                    );
                },
                'draggable': false,
                'direction': 't2b'
            });
            
            // 标记为已初始化
            initializedCharts[index] = true;
            
            // 居中显示该部门的组织架构图
            setTimeout(function() {
                const orgchartEl = $container.find('.orgchart');
                if (orgchartEl.length) {
                    const containerWidth = $container.width();
                    const chartWidth = orgchartEl.outerWidth();
                    if (chartWidth < containerWidth) {
                        const offsetLeft = (containerWidth - chartWidth) / 2;
                        orgchartEl.css('margin-left', offsetLeft + 'px');
                    }
                }
            }, 100);
        }
        
        $(document).ready(function() {
            if (!internalOrgData || internalOrgData.length === 0) {
                console.error('内部组织架构数据为空');
                $('#internal-orgchart-container').html('<p style="text-align: center; color: #6b7280; padding: 40px;">无法加载内部组织架构数据</p>');
                return;
            }
            
            console.log('内部组织架构数据:', internalOrgData);
            
            // 初始化第一个部门的组织架构图
            if (internalOrgData.length > 0) {
                initializeDeptChart(0, internalOrgData[0]);
            }
        });
    </script>
    <?php endif; ?>

    <!-- 对齐文化解说和价值观解说的评分标准部分 -->
    <script>
        function alignScoringSections() {
            // 处理所有 culture-explanation-grid
            document.querySelectorAll('.culture-explanation-grid').forEach(function(grid) {
                const cards = grid.querySelectorAll('.culture-explanation-card');
                if (cards.length === 0) return;
                
                // 重置所有内容区域的高度，以便重新计算
                cards.forEach(function(card) {
                    const content = card.querySelector('.culture-explanation-content');
                    if (content) {
                        content.style.minHeight = 'auto';
                    }
                });
                
                // 强制重排以获取实际高度
                void grid.offsetHeight;
                
                // 找出所有解说内容区域的最大高度
                let maxHeight = 0;
                cards.forEach(function(card) {
                    const content = card.querySelector('.culture-explanation-content');
                    if (content) {
                        const height = content.offsetHeight;
                        if (height > maxHeight) {
                            maxHeight = height;
                        }
                    }
                });
                
                // 设置所有解说内容区域的最小高度为最大高度
                cards.forEach(function(card) {
                    const content = card.querySelector('.culture-explanation-content');
                    if (content) {
                        content.style.minHeight = maxHeight + 'px';
                    }
                });
            });
        }
        
        // 页面加载完成后执行
        document.addEventListener('DOMContentLoaded', function() {
            alignScoringSections();
        });
        
        // 窗口大小改变时重新对齐
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                alignScoringSections();
            }, 250);
        });
    </script>

</body>
</html>
<?php
ob_end_flush();
?>

