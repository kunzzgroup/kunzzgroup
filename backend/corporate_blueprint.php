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
            padding: clamp(60px, 6.25vw, 100px) clamp(40px, 4.17vw, 60px);
            overflow: visible;
            background: 
                radial-gradient(circle at 20% 50%, rgba(255, 92, 0, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 80% 50%, rgba(255, 215, 0, 0.02) 0%, transparent 50%);
            border-radius: 12px;
        }

        /* Flowchart container */
        .flowchart-container {
            position: relative;
            width: 100%;
            min-height: clamp(400px, 41.67vw, 600px);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: clamp(24px, 2.5vw, 40px);
        }

        /* Flowchart connection lines */
        .flowchart-connector {
            width: 4px;
            background: linear-gradient(180deg, #ff5c00 0%, #ff8c42 100%);
            z-index: 1;
            box-shadow: 0 2px 8px rgba(255, 92, 0, 0.3);
            opacity: 0;
            transform: scaleY(0);
            transform-origin: top center;
            transition: opacity 0.6s ease, transform 0.8s cubic-bezier(0.65, 0, 0.35, 1);
            margin: 0 auto;
        }

        .flowchart-connector.animate-in {
            opacity: 1;
            transform: scaleY(1);
        }

        /* Arrow at the end of connector */
        .flowchart-connector::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 10px solid transparent;
            border-right: 10px solid transparent;
            border-top: 14px solid #ff5c00;
            filter: drop-shadow(0 2px 4px rgba(255, 92, 0, 0.3));
        }

        /* Flowchart node - Start (Rounded Rectangle) */
        .flowchart-node {
            position: relative;
            background: linear-gradient(135deg, #ff5c00 0%, #ff8c42 100%);
            color: #ffffff;
            padding: clamp(16px, 1.67vw, 24px) clamp(32px, 3.33vw, 48px);
            border-radius: 12px;
            z-index: 2;
            box-shadow: 
                0 6px 20px rgba(255, 92, 0, 0.4),
                0 2px 8px rgba(0, 0, 0, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
            text-align: center;
            font-weight: 700;
            font-size: clamp(14px, 1.46vw, 18px);
            letter-spacing: 0.5px;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.2);
            min-width: clamp(140px, 14.58vw, 200px);
            opacity: 0;
            transform: scale(0.8) translateY(20px);
            transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
            cursor: pointer;
        }

        .flowchart-node.animate-in {
            opacity: 1;
            transform: scale(1) translateY(0);
        }

        .flowchart-node:hover {
            transform: scale(1.05) translateY(-3px);
            box-shadow: 
                0 10px 30px rgba(255, 92, 0, 0.5),
                0 4px 12px rgba(0, 0, 0, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.4);
        }

        /* Start node specific */
        .flowchart-node-start {
            border-radius: 25px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            box-shadow: 
                0 6px 20px rgba(40, 167, 69, 0.4),
                0 2px 8px rgba(0, 0, 0, 0.15);
        }

        /* End node specific (Diamond shape) */
        .flowchart-node-end {
            position: relative;
            width: clamp(100px, 10.42vw, 140px);
            height: clamp(100px, 10.42vw, 140px);
            background: linear-gradient(135deg, #ff5c00 0%, #ff8c42 100%);
            transform: rotate(45deg);
            border-radius: 12px;
            box-shadow: 
                0 6px 20px rgba(255, 92, 0, 0.4),
                0 2px 8px rgba(0, 0, 0, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
            opacity: 0;
            transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
            cursor: pointer;
        }

        .flowchart-node-end.animate-in {
            opacity: 1;
            transform: rotate(45deg) scale(1);
        }

        .flowchart-node-end:hover {
            transform: rotate(45deg) scale(1.1);
            box-shadow: 
                0 10px 30px rgba(255, 92, 0, 0.5),
                0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .flowchart-node-end-text {
            transform: rotate(-45deg);
            color: #ffffff;
            font-weight: 700;
            font-size: clamp(13px, 1.35vw, 17px);
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
            letter-spacing: 0.5px;
        }

        /* Flowchart process node (Rectangle with rounded corners) */
        .flowchart-process-node {
            position: relative;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 3px solid #ff5c00;
            border-radius: 12px;
            padding: clamp(20px, 2.08vw, 32px);
            min-width: clamp(200px, 20.83vw, 300px);
            max-width: clamp(280px, 29.17vw, 400px);
            box-shadow: 
                0 6px 20px rgba(0, 0, 0, 0.1),
                0 2px 8px rgba(255, 92, 0, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.9);
            z-index: 2;
            opacity: 0;
            transform: scale(0.8) translateY(20px);
            transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
            cursor: pointer;
        }

        .flowchart-process-node.animate-in {
            opacity: 1;
            transform: scale(1) translateY(0);
        }

        .flowchart-process-node:hover {
            transform: scale(1.05) translateY(-5px);
            box-shadow: 
                0 12px 32px rgba(0, 0, 0, 0.15),
                0 4px 16px rgba(255, 92, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 1);
            border-color: #ff8c42;
        }

        /* Process node year label */
        .flowchart-year {
            font-size: clamp(20px, 2.08vw, 28px);
            font-weight: 800;
            background: linear-gradient(135deg, #ff5c00 0%, #ff8c42 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: clamp(12px, 1.25vw, 18px);
            text-align: center;
            letter-spacing: 0.5px;
        }

        /* Process node goal text */
        .flowchart-goal {
            font-size: clamp(14px, 1.46vw, 18px);
            color: #2c3e50;
            line-height: 1.7;
            text-align: center;
            font-weight: 500;
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

        /* Timeline items container */
        .timeline-items {
            position: relative;
            padding: 0 clamp(100px, 10.42vw, 140px);
            min-height: clamp(220px, 22.92vw, 320px);
        }

        .timeline-event {
            position: absolute;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: clamp(140px, 14.58vw, 200px);
            transform: translateX(-50%) translateY(20px);
            opacity: 0;
            transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), 
                        opacity 0.6s ease,
                        filter 0.3s ease;
        }

        .timeline-event.animate-in {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        .timeline-event:nth-child(odd).animate-in {
            transform: translateX(-50%) translateY(0);
        }

        .timeline-event:nth-child(even).animate-in {
            transform: translateX(-50%) translateY(0);
        }

        .timeline-event:nth-child(odd):hover {
            transform: translateX(-50%) translateY(-5px) scale(1.08);
            filter: drop-shadow(0 12px 24px rgba(255, 92, 0, 0.25));
        }

        .timeline-event:nth-child(even):hover {
            transform: translateX(-50%) translateY(5px) scale(1.08);
            filter: drop-shadow(0 12px 24px rgba(255, 92, 0, 0.25));
        }

        /* Alternate between top and bottom - odd items below, even items above */
        .timeline-event:nth-child(odd) {
            bottom: 0;
            flex-direction: column;
        }

        .timeline-event:nth-child(even) {
            top: 0;
            flex-direction: column;
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
                padding: clamp(40px, 4.17vw, 60px) clamp(24px, 2.5vw, 32px);
            }

            .timeline-line {
                left: clamp(60px, 6.25vw, 80px);
                right: clamp(60px, 6.25vw, 80px);
            }

            .timeline-wrapper {
                padding: clamp(40px, 4.17vw, 60px) clamp(24px, 2.5vw, 32px);
            }

            .flowchart-container {
                min-height: auto;
                gap: clamp(20px, 2.08vw, 30px);
            }

            .flowchart-node {
                padding: clamp(12px, 1.25vw, 18px) clamp(24px, 2.5vw, 36px);
                font-size: clamp(13px, 1.35vw, 16px);
                min-width: clamp(120px, 12.5vw, 180px);
            }

            .flowchart-process-node {
                padding: clamp(16px, 1.67vw, 24px);
                min-width: clamp(180px, 18.75vw, 260px);
                max-width: 90%;
            }

            .flowchart-node-end {
                width: clamp(80px, 8.33vw, 120px);
                height: clamp(80px, 8.33vw, 120px);
            }

            .flowchart-year {
                font-size: clamp(16px, 1.67vw, 24px);
                margin-bottom: clamp(10px, 1.04vw, 14px);
            }

            .flowchart-goal {
                font-size: clamp(13px, 1.35vw, 16px);
            }

            .flowchart-connector {
                width: 3px;
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
                            <div class="flowchart-container">
                                <?php if (!empty($strategyData['timeline'])): 
                                    $timelineItems = $strategyData['timeline'];
                                    $totalItems = count($timelineItems);
                                ?>
                                
                                <!-- Start Node -->
                                <div class="flowchart-node flowchart-node-start" data-index="0">
                                    起始
                                </div>
                                
                                <?php foreach ($timelineItems as $index => $item): ?>
                                    <!-- Connector -->
                                    <div class="flowchart-connector" 
                                         style="height: <?php echo clamp(60, 6.25, 100); ?>px;"
                                         data-connector="<?php echo $index; ?>">
                                    </div>
                                    
                                    <!-- Process Node -->
                                    <div class="flowchart-process-node" data-index="<?php echo $index + 1; ?>">
                                        <div class="flowchart-year"><?php echo htmlspecialchars($item['year'] ?? ''); ?>年</div>
                                        <div class="flowchart-goal"><?php echo htmlspecialchars($item['goal'] ?? ''); ?></div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <!-- Final Connector -->
                                <div class="flowchart-connector" 
                                     style="height: <?php echo clamp(60, 6.25, 100); ?>px;"
                                     data-connector="final">
                                </div>
                                
                                <!-- End Node -->
                                <div class="flowchart-node-end" data-index="<?php echo $totalItems + 1; ?>">
                                    <div class="flowchart-node-end-text">终点</div>
                                </div>
                                
                                <?php endif; ?>
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
        // 流程图式时间线动画控制器
        document.addEventListener('DOMContentLoaded', function() {
            const timelineWrapper = document.querySelector('.timeline-wrapper');
            if (!timelineWrapper) return;

            // 创建 IntersectionObserver 观察时间线容器
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        // 触发流程图动画
                        animateFlowchart(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.2,
                rootMargin: '0px 0px -50px 0px'
            });

            observer.observe(timelineWrapper);

            function animateFlowchart(container) {
                const flowchartContainer = container.querySelector('.flowchart-container');
                if (!flowchartContainer) return;

                // 1. 显示起始节点
                const startNode = flowchartContainer.querySelector('.flowchart-node-start');
                if (startNode) {
                    setTimeout(() => {
                        startNode.classList.add('animate-in');
                    }, 200);
                }

                // 2. 获取所有节点（按DOM顺序）
                const connectors = flowchartContainer.querySelectorAll('.flowchart-connector');
                const processNodes = flowchartContainer.querySelectorAll('.flowchart-process-node');
                const endNode = flowchartContainer.querySelector('.flowchart-node-end');

                // 3. 逐个显示连接线和流程节点（交替进行）
                connectors.forEach((connector, index) => {
                    // 显示连接线
                    setTimeout(() => {
                        connector.classList.add('animate-in');
                    }, 500 + (index * 250));

                    // 显示对应的流程节点（在连接线之后）
                    if (index < processNodes.length) {
                        setTimeout(() => {
                            processNodes[index].classList.add('animate-in');
                        }, 700 + (index * 250));
                    }
                });

                // 4. 显示终点节点（在所有连接线和流程节点之后）
                if (endNode) {
                    setTimeout(() => {
                        endNode.classList.add('animate-in');
                    }, 700 + (connectors.length * 250));
                }
            }

            // 增强交互：点击节点时的高亮效果
            const allNodes = document.querySelectorAll('.flowchart-node, .flowchart-process-node, .flowchart-node-end');
            allNodes.forEach(node => {
                node.addEventListener('click', function() {
                    // 移除其他节点的高亮
                    allNodes.forEach(n => n.classList.remove('active'));
                    // 添加当前节点的高亮
                    this.classList.add('active');
                });
            });
        });

        // 添加节点激活状态的样式
        const style = document.createElement('style');
        style.textContent = `
            .flowchart-node.active,
            .flowchart-process-node.active,
            .flowchart-node-end.active {
                animation: pulse-active 2s ease-in-out infinite;
            }

            @keyframes pulse-active {
                0%, 100% {
                    box-shadow: 
                        0 6px 20px rgba(255, 92, 0, 0.4),
                        0 0 0 0 rgba(255, 92, 0, 0.7);
                }
                50% {
                    box-shadow: 
                        0 6px 20px rgba(255, 92, 0, 0.4),
                        0 0 0 8px rgba(255, 92, 0, 0);
                }
            }

            .flowchart-process-node.active {
                border-color: #ff8c42 !important;
                background: linear-gradient(135deg, #fff8f5 0%, #ffffff 100%) !important;
            }
        `;
        document.head.appendChild(style);
    </script>

</body>
</html>
<?php
ob_end_flush();
?>

