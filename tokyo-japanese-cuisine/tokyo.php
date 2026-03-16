<?php include_once '../media_config.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" type="image/png" href="image/tokyologo.png">
  <title>TOKYO JAPANESE CUISINE | 东京日式料理</title>
  <link
    href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Noto+Sans+SC:wght@300;400;500;600&display=swap"
    rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <style>
    /* Typography and Color Variables - Tokyo Theme */
    :root {
      --font-serif: 'DM Serif Display', serif;
      --font-sans: 'Noto Sans SC', sans-serif;
      --color-primary: #a68a64;
      --color-primary-dark: #8b7353;
      --color-text: #33322f;
      --color-text-light: #5d5a54;
      --color-bg: #f5f2ed;
      --color-bg-alt: #ebe7e0;
      --color-border: #E5E5E5;
      --color-line: rgba(255, 255, 255, 0.5);

      /* Culture Carousel Variables */
      --card-w: 340px;
      --card-h: 480px;
      --gap: 40px;
    }

    * {
      box-sizing: border-box;
    }

    html,
    body {
      position: relative;
      height: 100%;
    }

    body {
      margin: 0;
      padding: 0;
      line-height: 1.6;
      overflow-y: auto;
      /* Allow native scroll so footer is reachable */
    }

    /* --- Swiper Configuration --- */
    .swiper {
      width: 100%;
      height: 100vh;
    }

    .swiper-slide {
      height: 100vh;
      box-sizing: border-box;
      display: flex !important;
      flex-direction: column;
      justify-content: center;
    }


    /* Anchor offset for fixed navbar — prevent content hidden behind navbar */
    #hero,
    #about-culture,
    #about,
    #mission-vision,
    #featured,
    #location {
      scroll-margin-top: 112px;
    }

    body {
      font-family: var(--font-sans);
      -webkit-font-smoothing: antialiased;
      color: var(--color-text);
      background-color: var(--color-bg);
      margin: 0;
      padding: 0;
      line-height: 1.6;
    }

    /* Scroll Animations */
    .reveal {
      opacity: 0;
      transform: translateY(30px);
      transition: all 0.8s cubic-bezier(0.5, 0, 0, 1);
    }

    .reveal.active {
      opacity: 1;
      transform: translateY(0);
    }

    .reveal.fade-left {
      transform: translateX(-30px);
    }

    .reveal.fade-right {
      transform: translateX(30px);
    }

    .reveal.scale-up {
      transform: scale(0.95);
    }

    .reveal.active.fade-left,
    .reveal.active.fade-right {
      transform: translateX(0);
    }

    .reveal.active.scale-up {
      transform: scale(1);
    }

    /* Stagger delays */
    .delay-100 {
      transition-delay: 0.1s;
    }

    .delay-200 {
      transition-delay: 0.2s;
    }

    .delay-300 {
      transition-delay: 0.3s;
    }

    .delay-400 {
      transition-delay: 0.4s;
    }

    a {
      color: inherit;
      text-decoration: none;
    }


    /* Navigation Styles */
    #nav {
      position: fixed;
      top: 24px;
      left: 50%;
      transform: translateX(-50%);
      width: 95%;
      max-width: 1300px;
      z-index: 1000;
      background-color: #ffffffc9;
      border-radius: 9999px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
      transition: all 0.3s ease;
      height: 72px;
      display: flex;
      align-items: center;
      padding: 0 16px;
    }

    .nav-container {
      width: 100%;
      padding: 0 8px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    @media (min-width: 640px) {
      .nav-container {
        padding: 0 16px;
      }
    }

    .nav-logo-link {
      display: flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
    }

    .haidilao-logo-bg {
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      border: 2px solid var(--color-primary);
      padding: 2px;
    }

    .nav-logo {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
      flex-shrink: 0;
    }

    .nav-text-group {
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .nav-text-group .primary {
      color: var(--color-text-light);
      font-family: var(--font-sans);
      font-size: 18px;
      font-weight: 800;
      line-height: 1.1;
      letter-spacing: -0.02em;
    }

    .nav-text-group .secondary {
      color: var(--color-text-light);
      font-family: var(--font-sans);
      font-size: 12px;
      font-weight: 500;
      line-height: 1.1;
    }

    .nav-links {
      display: none;
      align-items: center;
      gap: 40px;
    }

    @media (min-width: 992px) {
      .nav-links {
        display: flex;
      }
    }

    .nav-links a {
      color: var(--color-text);
      font-size: 15px;
      font-weight: 600;
      transition: color 0.2s ease;
      position: relative;
      text-decoration: none;
    }

    .nav-links a:hover {
      color: var(--color-primary);
    }

    .nav-links a::after {
      content: '';
      position: absolute;
      width: 0;
      height: 2px;
      bottom: -4px;
      left: 0;
      background-color: var(--color-primary);
      transition: width 0.3s;
    }

    .nav-links a:hover::after {
      width: 100%;
    }

    /* ── Nav Dropdown (特色推荐) ── */
    .nav-dropdown {
      position: relative;
    }

    /* Trigger looks exactly like a normal nav link */
    .nav-dropdown-trigger {
      color: var(--color-text);
      font-size: 15px;
      font-weight: 600;
      text-decoration: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 4px;
      white-space: nowrap;
      transition: color 0.2s;
    }

    .nav-dropdown:hover .nav-dropdown-trigger {
      color: var(--color-primary);
    }

    .nav-dropdown-arrow {
      font-size: 10px;
      display: inline-block;
      transition: transform 0.25s;
    }

    .nav-dropdown:hover .nav-dropdown-arrow {
      transform: rotate(180deg);
    }

    /* Outer wrapper — transparent, continuous hover zone, pushes card past nav bar */
    .nav-dropdown-menu {
      position: absolute;
      top: 100%;
      left: 50%;
      transform: translateX(-50%);
      padding-top: 28px;
      /* bridge the ~26px gap below trigger to nav bottom */
      background: transparent;
      z-index: 999;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.18s ease;
    }

    .nav-dropdown:hover .nav-dropdown-menu {
      opacity: 1;
      pointer-events: auto;
    }

    /* Visual card */
    .nav-dropdown-card {
      background: #faf8f5;
      border-radius: 14px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.14);
      padding: 6px;
      display: flex;
      flex-direction: column;
      gap: 2px;
      min-width: 154px;
    }

    /* Items — override .nav-links a inherited styles */
    .nav-dropdown-card .nav-dropdown-item {
      display: block;
      padding: 10px 18px;
      border-radius: 10px;
      color: var(--color-text);
      font-size: 14px;
      font-weight: 500;
      text-decoration: none;
      white-space: nowrap;
      transition: background-color 0.18s, color 0.18s;
    }

    .nav-dropdown-card .nav-dropdown-item::after {
      display: none;
      /* kill the nav-links underline animation */
    }

    .nav-dropdown-card .nav-dropdown-item:hover {
      background: var(--color-primary);
      color: white;
    }


    .nav-actions {
      display: flex;
      align-items: center;
      gap: 24px;
    }

    .nav-action-link {
      display: none;
      align-items: center;
      gap: 6px;
      color: var(--color-text);
      font-size: 14px;
      font-weight: 600;
      text-decoration: none;
      transition: color 0.2s;
    }

    @media (min-width: 768px) {
      .nav-action-link {
        display: flex;
      }
    }

    .nav-action-link:hover {
      color: var(--color-primary);
    }

    .action-icon {
      width: 18px;
      height: 18px;
      fill: currentColor;
    }

    .nav-hamburger {
      width: 45px;
      height: 45px;
      border-radius: 50%;
      background-color: var(--color-primary);
      border: none;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 4px;
      cursor: pointer;
      transition: background-color 0.3s, transform 0.2s;
      padding: 0;
      box-shadow: 0 4px 10px rgba(166, 138, 100, 0.3);
    }

    .nav-hamburger:hover {
      background-color: #8c7353;
      transform: translateY(-2px);
    }

    .nav-hamburger span {
      width: 18px;
      height: 2px;
      background-color: var(--color-line);
      border-radius: 2px;
      transition: all 0.3s ease;
    }

    .nav-btn {
      padding: 8px 20px;
      font-size: 14px;
    }

    .nav-btn.btn-secondary {
      display: none;
    }

    @media (min-width: 640px) {
      .nav-btn.btn-secondary {
        display: inline-block;
      }
    }


    @media (max-width: 640px) {
      .nav-text-group .primary {
        font-size: 16px;
      }

      .nav-text-group .secondary {
        font-size: 10px;
      }
    }

    /* Buttons */
    .btn-primary {
      padding: 12px 32px;
      border-radius: 9999px;
      background-color: var(--color-primary);
      color: white;
      font-weight: 600;
      border: 2px solid var(--color-primary);
      display: inline-block;
      transition: all 0.3s ease;
      cursor: pointer;
      text-decoration: none;
      box-shadow: 0 4px 10px rgba(166, 138, 100, 0.3);
    }

    .btn-primary:hover {
      background-color: var(--color-primary-dark);
      border-color: var(--color-primary-dark);
      transform: translateY(-2px);
      box-shadow: 0 6px 15px rgba(166, 138, 100, 0.4);
    }

    .btn-secondary {
      padding: 12px 32px;
      border-radius: 9999px;
      border: 2px solid var(--color-border);
      color: white;
      font-weight: 600;
      display: inline-block;
      transition: all 0.3s ease;
      background-color: transparent;
      cursor: pointer;
    }

    .btn-secondary:hover {
      border-color: var(--color-primary);
      color: var(--color-primary);
      background-color: rgba(166, 138, 100, 0.05);
      transform: translateY(-2px);
    }

    .btn-outline-white {
      border-color: white;
      color: white;
    }

    .btn-outline-white:hover {
      background: white;
      color: var(--color-primary);
    }


    /* Hero Section */
    #hero {
      position: relative;
      height: 100vh;
      /* Changed from min-height to height */
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      padding: clamp(80px, 10vw, 140px) 0;
      padding-top: clamp(120px, 15vw, 200px);
      /* Adjusting for navbar + breathing room */
      background-color: #333;
    }

    .hero-slider {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      z-index: 0;
      overflow: hidden;
    }

    .hero-slide {
      flex: 0 0 100%;
      /* Each slide is exactly 100% width of the container */
      width: 100%;
      height: 100%;
      background-size: cover;
      background-position: center;
    }


    .hero-overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(180deg,
          rgba(0, 0, 0, 0.45) 0%,
          rgba(0, 0, 0, 0.25) 40%,
          rgba(0, 0, 0, 0.6) 100%);
      z-index: 5;
      pointer-events: none;
    }

    .shoji-lattice {
      display: none;
    }

    .hero-content {
      position: relative;
      z-index: 10;
      text-align: left;
      padding: 0 5%;
      max-width: 1300px;
      width: 100%;
      margin: 0 auto;
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      justify-content: center;
    }

    .hero-logo {
      width: 112px;
      height: 112px;
      margin: 0 0 24px 0;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid white;
    }

    .hero-title {
      font-family: var(--font-serif);
      font-size: 35px;
      line-height: 1.15;
      color: white;
      margin-bottom: 13px;
      text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    }

    .hero-subtitle {
      color: rgba(255, 255, 255, 0.95);
      font-size: 18px;
      font-weight: 500;
      margin-bottom: 8px;
    }

    .hero-tagline {
      color: rgba(255, 255, 255, 0.8);
      font-size: 15px;
      margin-bottom: 32px;
    }

    @media (min-width: 768px) {
      .hero-title {
        font-size: 72px;
        margin-bottom: 16px;
      }

      .hero-subtitle {
        font-size: 20px;
      }

      .hero-tagline {
        font-size: 18px;
        margin-bottom: 40px;
      }
    }

    .hero-buttons {
      display: flex;
      flex-wrap: wrap;
      justify-content: flex-start;
      gap: 24px;
    }

    /* ── Section 2: About Us — Split Card ──────────────────── */
    .about-split-section {
      background-color: var(--color-bg);
      position: relative;
      overflow: hidden;
      height: 100vh;
      display: flex !important;
      align-items: center;
      justify-content: center;
    }

    .about-split-wrapper {
      max-width: 1400px;
      width: 90%;
      margin: 0 auto;
    }

    .about-split-card {
      display: grid;
      grid-template-columns: 1fr 1fr;
      border-radius: 28px;
      overflow: hidden;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.18);
      min-height: 420px;
    }

    /* ─── Left dark panel ─────────────────────────────────── */
    .about-split-left {
      background-color: #1e1a17;
      padding: 52px 48px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      gap: 24px;
    }

    .about-split-badge {
      display: inline-block;
      padding: 5px 16px;
      border-radius: 999px;
      border: 1px solid rgba(166, 138, 100, 0.5);
      color: var(--color-primary);
      font-size: 12px;
      font-weight: 600;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      width: fit-content;
    }

    .about-split-title {
      font-family: var(--font-serif);
      font-size: clamp(30px, 3.5vw, 46px);
      color: #fff;
      line-height: 1.2;
      margin: 0;
    }

    .about-split-desc {
      color: rgba(255, 255, 255, 0.72);
      font-size: 14.5px;
      line-height: 1.85;
      margin: 0;
      max-width: 400px;
    }

    /* Bullet list — gold em-dash, no stars */
    .about-split-list {
      list-style: none;
      padding: 0;
      margin: 0;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .about-split-list li {
      color: rgba(255, 255, 255, 0.82);
      font-size: 14px;
      display: flex;
      align-items: baseline;
      gap: 10px;
    }

    .about-split-list li::before {
      content: '—';
      color: var(--color-primary);
      font-size: 13px;
      flex-shrink: 0;
    }

    .about-split-btn {
      display: inline-block;
      padding: 13px 32px;
      border-radius: 999px;
      background-color: #fff;
      color: #1e1a17;
      font-size: 14px;
      font-weight: 700;
      text-decoration: none;
      width: fit-content;
      transition: background-color 0.2s, color 0.2s, transform 0.2s;
    }

    .about-split-btn:hover {
      background-color: var(--color-primary);
      color: #fff;
      transform: translateY(-2px);
    }

    /* ─── Right photo panel ───────────────────────────────── */
    .about-split-right {
      position: relative;
      overflow: hidden;
      min-height: 380px;
    }

    .about-split-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center top;
      display: block;
      transition: transform 0.6s ease;
    }

    .about-split-card:hover .about-split-img {
      transform: scale(1.04);
    }

    /* ─── Responsive: stack on mobile ─────────────────────── */
    @media (max-width: 767px) {
      .about-split-card {
        grid-template-columns: 1fr;
      }

      .about-split-left {
        padding: 40px 28px;
        gap: 20px;
      }

      .about-split-right {
        min-height: 260px;
        order: -1;
        /* photo on top on mobile */
      }
    }


    /* Section Components - Light Theme */
    .section-padding {
      padding: 100px 0;
      background-color: var(--color-bg);
    }

    .section-padding.alt-bg {
      background-color: var(--color-bg-alt);
    }

    .section-header {
      text-align: center;
      margin-bottom: 64px;
      max-width: 800px;
      margin-left: auto;
      margin-right: auto;
    }

    .section-badge {
      color: var(--color-primary);
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      font-size: 14px;
      margin-bottom: 8px;
      display: inline-block;
    }

    .section-title {
      font-family: var(--font-serif);
      font-size: 29px;
      color: var(--color-text);
      margin-bottom: 16px;
      position: relative;
      display: inline-block;
      line-height: 1.25;
    }

    .section-title::after {
      content: '';
      display: block;
      width: 50px;
      height: 3px;
      background-color: var(--color-primary);
      margin: 16px auto 0;
    }

    .section-desc {
      color: var(--color-text-light);
      font-size: 15px;
      max-width: 600px;
      margin: 0 auto;
    }

    @media (min-width: 768px) {
      .section-title {
        font-size: 45px;
      }

      .section-title::after {
        width: 60px;
      }

      .section-desc {
        font-size: 18px;
      }
    }

    /* Unified About & Culture Section - Bento Grid */
    #about-culture {
      background-color: var(--color-bg);
      position: relative;
      overflow: hidden;
      height: 100vh;
      display: flex !important;
      align-items: center;
      justify-content: center;
    }

    .about-culture-container {
      max-width: 1280px;
      margin: 0 auto;
      padding: 0 24px;
      position: relative;
      z-index: 10;
    }

    /* ── Wa (和) Japanese Zen Layout ──────────────────── */

    /* Header: eyebrow + thin rule */
    .wa-header {
      margin-bottom: 48px;
    }

    .wa-header-top {
      display: flex;
      align-items: center;
      gap: 20px;
      margin-bottom: 20px;
    }

    .wa-eyebrow {
      font-size: 11px;
      font-weight: 600;
      letter-spacing: 0.25em;
      text-transform: uppercase;
      color: var(--color-primary);
      white-space: nowrap;
    }

    .wa-rule {
      display: block;
      flex: 1;
      height: 1px;
      background: var(--color-border);
    }

    .wa-header-body {
      display: grid;
      grid-template-columns: 1fr;
      gap: 20px;
    }

    @media (min-width: 768px) {
      .wa-header-body {
        grid-template-columns: auto 1fr;
        gap: 64px;
        align-items: start;
      }
    }

    .wa-title {
      font-family: var(--font-serif);
      font-size: 40px;
      color: var(--color-text);
      margin: 0;
      line-height: 1.2;
      letter-spacing: -0.01em;
    }

    .wa-desc {
      color: var(--color-text-light);
      font-size: 15px;
      line-height: 2;
      margin: 0;
      max-width: 500px;
      padding-top: 6px;
    }

    /* Cinematic main photo — 2.35:1 ratio */
    .wa-cinema {
      position: relative;
      width: 100%;
      aspect-ratio: 21 / 9;
      overflow: hidden;
      cursor: pointer;
      background: var(--color-bg-alt);
      margin-bottom: 6px;
    }

    .wa-cinema-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      transition: transform 0.8s cubic-bezier(0.25, 1, 0.5, 1),
        opacity 0.4s ease;
    }

    .wa-cinema:hover .wa-cinema-img {
      transform: scale(1.03);
    }

    .wa-cinema-caption {
      position: absolute;
      bottom: 20px;
      left: 24px;
      color: white;
      font-size: 11px;
      font-weight: 600;
      letter-spacing: 0.2em;
      text-transform: uppercase;
      background: rgba(0, 0, 0, 0.35);
      padding: 5px 12px;
      backdrop-filter: blur(4px);
    }

    .wa-cinema-hint {
      position: absolute;
      bottom: 20px;
      right: 24px;
      color: rgba(255, 255, 255, 0.6);
      font-size: 10px;
      letter-spacing: 0.15em;
      text-transform: uppercase;
    }

    /* 3-photo strip */
    .wa-strip {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 6px;
      margin-top: 6px;
    }

    .wa-strip-item {
      position: relative;
      overflow: hidden;
      aspect-ratio: 1;
      background: var(--color-bg-alt);
    }

    .wa-strip-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      transition: transform 0.6s cubic-bezier(0.25, 1, 0.5, 1);
    }

    .wa-strip-item:hover .wa-strip-img {
      transform: scale(1.06);
    }

    .wa-strip-label {
      position: absolute;
      bottom: 10px;
      left: 12px;
      color: white;
      font-size: 12px;
      font-weight: 500;
      letter-spacing: 0.12em;
      text-shadow: 0 1px 4px rgba(0, 0, 0, 0.6);
    }

    /* ─────────────────────────────────────────────────── */

    /* Editorial Header — split left/right */

    .editorial-header {
      display: flex;
      flex-direction: column;
      gap: 24px;
      margin-bottom: 40px;
    }

    @media (min-width: 768px) {
      .editorial-header {
        flex-direction: row;
        align-items: flex-start;
        gap: 64px;
      }

      .editorial-header-left {
        flex: 0 0 auto;
      }

      .editorial-header-right {
        flex: 1;
        padding-top: 8px;
      }
    }

    .editorial-header-left .section-title::after {
      margin: 12px 0 0 0;
      /* left-align the underline */
    }

    /* Editorial Main Grid */
    .editorial-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 12px;
      margin-bottom: 12px;
    }

    @media (min-width: 768px) {
      .editorial-grid {
        grid-template-columns: 2fr 1fr 1fr;
        grid-template-rows: 360px;
      }
    }

    /* Shared image cell */
    .editorial-main,
    .editorial-stack-item,
    .editorial-tall {
      position: relative;
      border-radius: 16px;
      overflow: hidden;
      background: var(--color-bg-alt);
    }

    .editorial-main {
      cursor: pointer;
      min-height: 260px;
    }

    .editorial-tall {
      min-height: 260px;
    }

    .editorial-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      transition: transform 0.6s cubic-bezier(0.25, 1, 0.5, 1),
        opacity 0.4s ease;
    }

    .editorial-main:hover .editorial-img,
    .editorial-stack-item:hover .editorial-img,
    .editorial-tall:hover .editorial-img {
      transform: scale(1.05);
    }

    /* Middle stacked column */
    .editorial-stack {
      display: grid;
      grid-template-rows: 1fr 1fr;
      gap: 12px;
    }

    /* Label badge */
    .editorial-label {
      position: absolute;
      bottom: 12px;
      left: 12px;
      background: rgba(166, 138, 100, 0.88);
      color: white;
      font-size: 12px;
      font-weight: 700;
      letter-spacing: 0.08em;
      padding: 5px 12px;
      border-radius: 999px;
      backdrop-filter: blur(6px);
      pointer-events: none;
    }

    /* Bottom full-width banner */
    .editorial-banner {
      position: relative;
      border-radius: 16px;
      overflow: hidden;
      height: 200px;
    }

    @media (min-width: 768px) {
      .editorial-banner {
        height: 260px;
      }
    }

    .editorial-banner-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center 30%;
      display: block;
      transition: transform 0.6s ease;
    }

    .editorial-banner:hover .editorial-banner-img {
      transform: scale(1.03);
    }

    .editorial-banner-overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(to right, rgba(0, 0, 0, 0.55) 0%, rgba(0, 0, 0, 0.1) 100%);
      display: flex;
      align-items: center;
      padding: 0 40px;
      color: white;
      font-family: var(--font-serif);
      font-size: 18px;
      letter-spacing: 0.05em;
    }

    /* Random Spotlight */
    .random-spotlight {
      display: flex;
      justify-content: center;
      width: 100%;
    }

    .random-spotlight-frame {
      position: relative;
      width: 100%;
      max-width: 700px;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
      cursor: pointer;
    }

    .random-spotlight-img {
      width: 100%;
      aspect-ratio: 16 / 9;
      object-fit: cover;
      display: block;
      transition: transform 0.6s cubic-bezier(0.25, 1, 0.5, 1);
      animation: spotlight-fadein 0.8s ease-out both;
    }

    .random-spotlight-frame:hover .random-spotlight-img {
      transform: scale(1.04);
    }

    .random-spotlight-badge {
      position: absolute;
      bottom: 16px;
      left: 16px;
      background: rgba(166, 138, 100, 0.92);
      color: white;
      font-size: 13px;
      font-weight: 700;
      letter-spacing: 0.08em;
      padding: 6px 14px;
      border-radius: 999px;
      backdrop-filter: blur(6px);
    }

    @keyframes spotlight-fadein {
      from {
        opacity: 0;
        transform: scale(0.97);
      }

      to {
        opacity: 1;
        transform: scale(1);
      }
    }


    .bento-grid-wrapper {
      margin-top: 48px;
      width: 100%;
    }

    .bento-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 16px;
      grid-auto-flow: dense;
    }

    @media (min-width: 768px) {
      .bento-grid {
        grid-template-columns: repeat(4, 1fr);
        grid-template-rows: repeat(2, 300px);
        gap: 24px;
      }
    }

    .bento-item {
      position: relative;
      border-radius: 24px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
      min-height: 250px;
    }

    /* Main large image spans 2 columns and 2 rows on tablet/desktop */
    @media (min-width: 768px) {
      .bento-large {
        grid-column: span 2;
        grid-row: span 2;
      }

      .bento-wide {
        grid-column: span 2;
      }
    }

    .bento-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.8s cubic-bezier(0.25, 1, 0.5, 1);
      display: block;
    }

    .bento-item:hover .bento-img {
      transform: scale(1.05);
    }

    .bento-overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(180deg, transparent 40%, rgba(0, 0, 0, 0.7) 100%);
      display: flex;
      flex-direction: column;
      justify-content: flex-end;
      padding: 32px;
      color: white;
      pointer-events: none;
    }

    .bento-overlay h3 {
      font-family: var(--font-serif);
      font-size: 29px;
      margin: 0 0 8px 0;
      transform: translateY(10px);
      opacity: 0.9;
      transition: all 0.4s ease;
    }

    .bento-overlay p {
      margin: 0;
      font-size: 16px;
      color: rgba(255, 255, 255, 0.8);
      transform: translateY(10px);
      opacity: 0;
      transition: all 0.4s ease;
      transition-delay: 0.1s;
    }

    .bento-item:hover .bento-overlay h3,
    .bento-item:hover .bento-overlay p {
      transform: translateY(0);
      opacity: 1;
    }

    /* Hero Section Animations */
    #hero {
      position: relative;
      /* ... existing styles ... */
      overflow: hidden;
    }

    /* Bokeh Particles */
    .hero-bokeh {
      position: absolute;
      width: 100%;
      height: 100%;
      top: 0;
      left: 0;
      z-index: 1;
      /* Behind content (2) */
      pointer-events: none;
    }

    .hero-bokeh span {
      position: absolute;
      width: 10px;
      height: 10px;
      background: rgba(201, 162, 39, 0.4);
      border-radius: 50%;
      box-shadow: 0 0 10px rgba(201, 162, 39, 0.4);
      animation: move-particle 15s linear infinite;
    }

    /* Randomized particles (simplified for CSS) */
    .hero-bokeh span:nth-child(1) {
      top: 20%;
      left: 20%;
      animation-duration: 12s;
      transform: scale(1);
    }

    .hero-bokeh span:nth-child(2) {
      top: 50%;
      left: 80%;
      animation-duration: 18s;
      transform: scale(0.8);
    }

    .hero-bokeh span:nth-child(3) {
      top: 80%;
      left: 40%;
      animation-duration: 15s;
      transform: scale(1.2);
    }

    .hero-bokeh span:nth-child(4) {
      top: 10%;
      left: 60%;
      animation-duration: 20s;
      transform: scale(0.5);
    }

    .hero-bokeh span:nth-child(5) {
      top: 70%;
      left: 10%;
      animation-duration: 14s;
      transform: scale(0.9);
    }

    /* Add more if needed or rely on these 5-12 spans moving around */

    @keyframes move-particle {
      0% {
        transform: translateY(0) translateX(0) scale(1);
        opacity: 0;
      }

      10% {
        opacity: 1;
      }

      90% {
        opacity: 1;
      }

      100% {
        transform: translateY(-100px) translateX(50px) scale(0);
        opacity: 0;
      }
    }

    /* Floating Logo with Pulse */
    .hero-logo.floating-logo {
      animation: float-logo 6s ease-in-out infinite;
    }

    .hero-logo.pulse-glow {
      /* Combined float and pulse requires wrapping or careful keyframes. 
     Float uses transform, Pulse uses filter/shadow. distinct props. */
      animation: float-logo 6s ease-in-out infinite, pulse-glow 3s ease-in-out infinite;
    }

    @keyframes float-logo {

      0%,
      100% {
        transform: translateY(0);
      }

      50% {
        transform: translateY(-15px);
      }
    }

    @keyframes pulse-glow {

      0%,
      100% {
        filter: drop-shadow(0 0 10px rgba(201, 162, 39, 0.3));
      }

      50% {
        filter: drop-shadow(0 0 25px rgba(201, 162, 39, 0.8));
      }
    }

    /* Letter by Letter Reveal */
    .hero-title .letter-reveal {
      display: inline-block;
      opacity: 0;
      transform: translateY(20px);
      animation: reveal-letter 0.6s cubic-bezier(0.5, 0, 0, 1) forwards;
      animation-delay: var(--d);
      min-width: 0.3em;
      /* Avoid collapse for spaces */
    }

    @keyframes reveal-letter {
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Shimmer Text */
    .hero-subtitle.shimmer-text {
      background: linear-gradient(90deg, #fff 0%, #c9a227 50%, #fff 100%);
      background-size: 200% auto;
      color: transparent;
      -webkit-background-clip: text;
      background-clip: text;
      animation: shimmer 3s linear infinite;
      display: inline-block;
    }

    @keyframes shimmer {
      to {
        background-position: 200% center;
      }
    }

    /* Spring Buttons with Hover Sweep */
    .btn-primary.spring-btn,
    .btn-secondary.spring-btn {
      transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), background-color 0.3s, color 0.3s;
      position: relative;
      overflow: hidden;
      z-index: 1;
    }

    .btn-primary.spring-btn:hover,
    .btn-secondary.spring-btn:hover {
      transform: scale(1.05);
    }

    .btn-primary.spring-btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 0%;
      height: 100%;
      background: rgba(255, 255, 255, 0.2);
      transform: skewX(-25deg);
      transition: width 0.4s ease;
      z-index: -1;
    }

    .btn-primary.spring-btn:hover::before {
      width: 150%;
    }

    /* Scroll Indicator */
    .scroll-indicator {
      position: absolute;
      bottom: 32px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 10;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 8px;
      opacity: 0.8;
    }

    .mouse {
      width: 26px;
      height: 40px;
      border: 2px solid rgba(255, 255, 255, 0.6);
      border-radius: 999px;
      position: relative;
    }

    .wheel {
      width: 4px;
      height: 4px;
      background: #c9a227;
      border-radius: 50%;
      position: absolute;
      top: 6px;
      left: 50%;
      transform: translateX(-50%);
      animation: mouse-scroll 1.5s infinite;
    }

    @keyframes mouse-scroll {
      0% {
        opacity: 1;
        top: 6px;
      }

      100% {
        opacity: 0;
        top: 26px;
      }
    }

    .arrow-scroll span {
      display: block;
      width: 10px;
      height: 10px;
      border-bottom: 2px solid rgba(255, 255, 255, 0.6);
      border-right: 2px solid rgba(255, 255, 255, 0.6);
      transform: rotate(45deg);
      animation: array-scroll 2s infinite;
      margin: -4px 0;
    }

    .arrow-scroll span:nth-child(2) {
      animation-delay: 0.2s;
    }

    .arrow-scroll span:nth-child(3) {
      animation-delay: 0.4s;
    }

    @keyframes array-scroll {
      0% {
        opacity: 0;
        transform: rotate(45deg) translate(-5px, -5px);
      }

      50% {
        opacity: 1;
      }

      100% {
        opacity: 0;
        transform: rotate(45deg) translate(5px, 5px);
      }
    }


    .culture-container {
      position: relative;
      z-index: 10;
      max-width: 1280px;
      margin: 0 auto;
      padding: 0 24px;
    }

    /* Removed culture-tabs styles */


    .culture-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 24px;
    }

    @media (min-width: 768px) {
      .culture-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 32px;
      }
    }

    .culture-card {
      background-color: #3d2914;
      border: 1px solid rgba(251, 191, 36, 0.5);
      border-radius: 24px;
      padding: 32px;
      color: #fff;
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
      position: relative;
      z-index: 20;
      transition: transform 0.3s ease, border-color 0.3s ease;
    }

    @media (min-width: 768px) {
      .culture-card {
        padding: 40px;
      }
    }

    .culture-card:hover {
      transform: translateY(-5px);
      border-color: #c9a227;
    }

    .culture-card h3 {
      font-family: var(--font-serif);
      font-size: 24px;
      line-height: 32px;
      margin-bottom: 16px;
      margin-top: 0;
      color: #c9a227;
    }

    @media (min-width: 768px) {
      .culture-card h3 {
        font-size: 28px;
        line-height: 36px;
      }
    }

    .culture-card p {
      line-height: 1.625;
      margin-top: 0;
      margin-bottom: 0;
      color: #e5e7eb;
    }

    /* Location Section */
    #location {
      background-color: var(--color-bg);
      position: relative;
      overflow: hidden;
      height: 100vh;
      display: flex !important;
      align-items: center;
      justify-content: center;
    }

    .location-container {
      max-width: 1400px;
      width: 90%;
      margin: 0 auto;
      position: relative;
      z-index: 2;
    }



    /* Featured Section */
    #featured {
      background-color: var(--color-bg-alt);
      position: relative;
    }

    .featured-container {
      position: relative;
      z-index: 10;
      max-width: 1280px;
      margin: 0 auto;
      padding: 0 24px;
    }

    #featured h2 {
      font-family: var(--font-serif);
      font-size: 30px;
      line-height: 36px;
      color: var(--color-primary);
      text-align: center;
      margin-bottom: 48px;
      margin-top: 0;
    }

    @media (min-width: 768px) {
      #featured h2 {
        font-size: 36px;
        line-height: 40px;
      }
    }

    .featured-cards-wrapper {
      border: 2px solid rgba(201, 162, 39, 0.6);
      border-radius: 24px;
      padding: 24px;
      display: flex;
      flex-direction: column;
      gap: 24px;
      width: 100%;
      justify-content: center;
      align-items: stretch;
    }

    @media (min-width: 640px) {
      .featured-cards-wrapper {
        flex-direction: row;
        gap: 24px;
        align-items: stretch;
      }
    }

    .featured-card {
      flex: 1;
      min-width: 0;
      max-width: 448px;
      margin: 0 auto;
      border-radius: 12px;
      overflow: hidden;
      border: 2px solid rgba(201, 162, 39, 0.7);
      transition: border-color 0.2s ease;
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
      display: flex;
      flex-direction: column;
    }

    @media (min-width: 640px) {
      .featured-card {
        margin: 0;
      }
    }

    .featured-card:hover {
      border-color: #fbbf24;
    }

    .featured-card-header {
      padding: 24px;
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      background-color: transparent;
    }

    .featured-card-icon,
    .featured-card-icon-img {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      border: 2px solid #c9a227;
      display: block;
      margin-bottom: 12px;
      flex-shrink: 0;
      object-fit: cover;
    }

    .featured-card-title {
      color: white;
      font-family: var(--font-serif);
      font-size: 18px;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    /* Featured Cards Specifics */
    .featured-cards-wrapper {
      background-color: transparent;
      border: 1px solid var(--color-border);
      border-radius: 24px;
      padding: 32px;
      display: grid;
      grid-template-columns: 1fr;
      gap: 32px;
      max-width: 1000px;
      margin: 0 auto;
    }

    @media (min-width: 768px) {
      .featured-cards-wrapper {
        grid-template-columns: 1fr 1fr;
        padding: 48px;
      }
    }

    .featured-card {
      text-decoration: none;
      background-color: transparent;
      border: 1px solid #c9a227;
      border-radius: 8px;
      overflow: hidden;
      transition: transform 0.3s ease, border-color 0.3s ease;
      display: block;
      /* Ensure it wraps the image */
    }

    .featured-card:hover {
      transform: translateY(-5px);
      border-color: #fff;
    }

    .featured-card-image-wrapper {
      position: relative;
      overflow: hidden;
      /* Remove fixed height to let image aspect ratio dictate */
      width: 100%;
      aspect-ratio: 3 / 4;
      /* Approximate the poster shape */
    }

    .featured-card-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.6s ease;
      /* Keep adjustment from previous task if needed, or reset? 
     The user's screenshot shows the images FULLY visible. 
     I'll reset object-position to center to avoid cutting off text if the ratio is correct.
     Transform fixes were for the previous 'cut off' layout.
     If I show the full card, I probably don't need the shift.
     I'll comment out the specific shifts for now (or remove them).
  */
      object-position: center;
    }

    .featured-card:hover .featured-card-img {
      transform: scale(1.05);
      /* Gentle zoom */
    }

    .featured-card-overlay {
      position: absolute;
      inset: 0;
      background: rgba(44, 24, 16, 0.4);
      /* Brown tint */
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .featured-card:hover .featured-card-overlay {
      opacity: 1;
    }

    .featured-card-btn {
      padding: 12px 24px;
      background-color: transparent;
      color: white;
      border: 1px solid #c9a227;
      /* Gold border button */
      border-radius: 9999px;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 14px;
      letter-spacing: 0.05em;
      transform: translateY(10px);
      transition: all 0.3s ease;
    }

    .featured-card-btn:hover {
      background-color: #c9a227;
      color: #2c1810;
    }

    .featured-card:hover .featured-card-btn {
      transform: translateY(0);
    }

    /* Hide the distinct info block as user wants just the poster look */
    .featured-card-info {
      display: none;
    }

    /* Reset specific adjustments since we are changing layout */
    .featured-card:nth-child(1) .featured-card-img,
    .featured-card:nth-child(2) .featured-card-img {
      transform: none;
      object-position: center;
    }

    .featured-card:nth-child(1):hover .featured-card-img,
    .featured-card:nth-child(2):hover .featured-card-img {
      transform: scale(1.05);
    }

    /* Sushi Menu (standalone page or inside section 4) */
    .sushi-page {
      min-height: 100vh;
      background-color: #2c1810;
      padding: 80px 0 64px;
    }

    @media (min-width: 768px) {
      .sushi-page {
        padding: 96px 0 80px;
      }
    }

    .sushi-page .sushi-menu {
      margin-top: 0;
      padding-top: 0;
      border-top: none;
    }

    .sushi-page-header {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 50;
      height: 64px;
      background-color: rgba(44, 24, 16, 0.95);
      backdrop-filter: blur(4px);
      border-bottom: 1px solid rgba(201, 162, 39, 0.2);
    }

    .sushi-page-header-inner {
      max-width: 1280px;
      margin: 0 auto;
      padding: 0 16px;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .sushi-page-back {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      color: rgba(251, 191, 36, 0.95);
      font-size: 14px;
      text-decoration: none;
      transition: color 0.2s;
    }

    .sushi-page-back:hover {
      color: white;
    }

    .sushi-page-back-arrow {
      font-size: 20px;
    }

    .sushi-page-logo-link {
      display: flex;
      align-items: center;
      gap: 8px;
      color: white;
      text-decoration: none;
    }

    .sushi-page-logo {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      object-fit: cover;
    }

    .sushi-page-logo-text {
      font-family: var(--font-serif);
      font-size: 18px;
      font-weight: 500;
    }

    .sushi-page-footer {
      background-color: rgba(44, 24, 16, 0.98);
      border-top: 1px solid rgba(201, 162, 39, 0.2);
      padding: 16px 24px;
      display: flex;
      justify-content: center;
      gap: 32px;
      flex-wrap: wrap;
    }

    .sushi-page-footer a {
      color: rgba(251, 191, 36, 0.9);
      font-size: 14px;
      text-decoration: none;
    }

    .sushi-page-footer a:hover {
      color: white;
    }

    .sushi-menu {
      margin-top: 64px;
      padding-top: 48px;
      border-top: 1px solid rgba(201, 162, 39, 0.3);
    }

    .sushi-menu-title {
      font-family: var(--font-serif);
      font-size: 30px;
      color: rgba(251, 191, 36, 0.95);
      text-align: center;
      margin-bottom: 32px;
      margin-top: 0;
    }

    @media (min-width: 768px) {
      .sushi-menu-title {
        font-size: 36px;
        margin-bottom: 40px;
      }
    }

    .sushi-menu-tabs {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      justify-content: center;
      margin-bottom: 32px;
    }

    .sushi-menu-tab {
      padding: 8px 16px;
      border-radius: 9999px;
      background-color: rgba(255, 255, 255, 0.1);
      color: rgba(251, 191, 36, 0.9);
      border: 1px solid rgba(251, 191, 36, 0.4);
      cursor: pointer;
      font-size: 14px;
      font-weight: 500;
      transition: all 0.2s ease;
    }

    .sushi-menu-tab:hover {
      background-color: rgba(255, 255, 255, 0.2);
      color: white;
    }

    .sushi-menu-tab.active {
      background-color: #c9a227;
      color: var(--color-brown);
      border-color: #c9a227;
    }

    .sushi-menu-panel {
      display: none;
    }

    .sushi-menu-panel.active {
      display: block;
    }

    .sushi-menu-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 16px;
    }

    @media (min-width: 640px) {
      .sushi-menu-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
      }
    }

    @media (min-width: 1024px) {
      .sushi-menu-grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
      }
    }

    .sushi-menu-item {
      background-color: rgba(255, 255, 255, 0.06);
      border-radius: 12px;
      overflow: hidden;
      border: 1px solid rgba(201, 162, 39, 0.25);
      transition: transform 0.2s ease, border-color 0.2s ease;
      display: flex;
      flex-direction: column;
    }

    .sushi-menu-item:hover {
      transform: translateY(-2px);
      border-color: rgba(201, 162, 39, 0.5);
    }

    .sushi-menu-item img {
      width: 100%;
      aspect-ratio: 1;
      object-fit: cover;
      display: block;
    }

    .sushi-menu-item span {
      padding: 8px 12px;
      font-size: 14px;
      color: rgba(251, 191, 36, 0.9);
      text-align: center;
    }

    /* Location Section */
    #location {
      padding: 100px 0;
      background-color: #f5f0e8;
      position: relative;
    }

    @media (min-width: 768px) {
      #location {
        padding: 100px 0;
        background-color: #f5f0e8;
        position: relative;
      }
    }

    .location-decoration {
      position: absolute;
      width: 64px;
      height: 64px;
      opacity: 0.4;
    }

    @media (min-width: 768px) {
      .location-decoration {
        width: 80px;
        height: 80px;
      }
    }

    .location-decoration.left {
      top: 48px;
      left: 32px;
    }

    @media (min-width: 768px) {
      .location-decoration.left {
        left: 64px;
      }
    }

    .location-decoration.right {
      top: 160px;
      right: 32px;
      transform: scaleX(-1);
    }

    @media (min-width: 768px) {
      .location-decoration.right {
        right: 64px;
      }
    }

    .location-container {
      max-width: 1280px;
      margin: 0 auto;
      padding: 0 24px;
      position: relative;
    }

    #location h2 {
      font-family: var(--font-serif);
      font-size: 30px;
      line-height: 36px;
      color: var(--color-brown);
      text-align: center;
      margin-bottom: 48px;
      margin-top: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 16px;
    }

    @media (min-width: 768px) {
      #location h2 {
        font-size: 36px;
        line-height: 40px;
      }
    }

    .location-divider {
      display: inline-block;
      width: 32px;
      height: 1px;
      background-color: rgba(201, 162, 39, 0.6);
      flex-shrink: 0;
    }

    .location-cards {
      display: grid;
      grid-template-columns: 1fr;
      gap: 32px;
    }

    @media (min-width: 768px) {
      .location-cards {
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      }
    }

    .location-card {
      background-color: white;
      border-radius: 24px;
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }

    .location-card-map {
      aspect-ratio: 16 / 9;
      background-color: #d1d5db;
      position: relative;
      border-radius: 20px;
      /* Adjusted from original #store-map */
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      margin-bottom: 32px;
      overflow: hidden;
    }

    .location-card-map iframe {
      width: 100%;
      height: 100%;
      border: none;
    }

    .location-card-info {
      padding: 24px;
    }

    .location-card h3 {
      font-family: var(--font-serif);
      font-size: 20px;
      color: var(--color-brown);
      margin-bottom: 8px;
      margin-top: 0;
    }

    .location-card p {
      color: #4b5563;
      font-size: 14px;
      margin-bottom: 8px;
      margin-top: 0;
    }

    .location-card-info a {
      color: inherit;
      text-decoration: none;
      border-bottom: 1px dotted rgba(75, 85, 99, 0.5);
      transition: all 0.2s ease;
    }

    .location-card-info a:hover {
      color: var(--color-primary);
      border-bottom-color: var(--color-primary);
    }


    /* Zoom Modal */
    .zoom-modal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.9);
      z-index: 1000;
      display: flex;
      align-items: center;
      justify-content: center;
      animation: fadeIn 0.2s ease-in;
    }

    .zoom-modal.hidden {
      display: none;
    }

    .zoom-modal-content {
      position: relative;
      max-width: 90vw;
      max-height: 90vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .zoom-modal-image {
      max-width: 100%;
      max-height: 100%;
      object-fit: contain;
      border-radius: 8px;
      animation: slideIn 0.3s ease-out;
    }


    .zoom-modal-close {
      position: absolute;
      top: -48px;
      right: 0;
      background: none;
      border: none;
      color: white;
      font-size: 48px;
      cursor: pointer;
      padding: 0;
      line-height: 1;
      transition: color 0.2s ease;
      z-index: 1001;
    }

    .zoom-modal-close:hover {
      color: rgba(251, 191, 36, 1);
    }

    @media (max-width: 768px) {
      .zoom-modal-close {
        top: 16px;
        right: 16px;
        color: white;
      }
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
      }

      to {
        opacity: 1;
      }
    }

    @keyframes slideIn {
      from {
        transform: scale(0.9);
        opacity: 0;
      }

      to {
        transform: scale(1);
        opacity: 1;
      }
    }

    .sushi-menu-item img {
      transition: transform 0.2s ease;
    }

    .sushi-menu-item img:hover {
      transform: scale(1.05);
      opacity: 0.9;
    }

    /* Menu Category Toggle */
    .menu-category-toggle {
      display: flex;
      justify-content: center;
      gap: 16px;
      margin-bottom: 48px;
      flex-wrap: wrap;
    }

    .menu-toggle-btn {
      padding: 12px 32px;
      font-family: var(--font-serif);
      font-size: 20px;
      color: rgba(201, 162, 39, 0.7);
      background: transparent;
      border: 2px solid rgba(201, 162, 39, 0.4);
      border-radius: 9999px;
      cursor: pointer;
      transition: all 0.3s ease;
      min-width: 160px;
    }

    .menu-toggle-btn:hover {
      background-color: rgba(201, 162, 39, 0.1);
      color: rgba(201, 162, 39, 1);
      border-color: rgba(201, 162, 39, 0.8);
    }

    .menu-toggle-btn.active {
      background-color: #c9a227;
      color: var(--color-brown);
      border-color: #c9a227;
      box-shadow: 0 0 15px rgba(201, 162, 39, 0.3);
    }

    /* Utility Hidden Class */
    .hidden {
      display: none !important;
    }

    /* Grand Menu Panel */
    .grand-menu-panel {
      display: none;
      animation: fadeIn 0.4s ease-out;
    }

    .grand-menu-panel.active {
      display: block;
    }

    /* Ensure sushi panels also have animation */

    /* Sakura Animation */
    .sakura-container {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      pointer-events: none;
      z-index: 9999;
      overflow: hidden;
    }

    .sakura {
      position: absolute;
      top: -10%;
      opacity: 0.8;
      animation-name: fall, sway;
      animation-timing-function: linear, ease-in-out;
      animation-iteration-count: 1, infinite;
      animation-fill-mode: forwards;
    }

    @keyframes fall {
      0% {
        top: -10%;
        transform: rotate(0deg);
      }

      100% {
        top: 100%;
        transform: rotate(360deg);
      }
    }

    @keyframes sway {
      0% {
        transform: translateX(0px) rotate(0deg);
      }

      50% {
        transform: translateX(50px) rotate(180deg);
      }

      100% {
        transform: translateX(0px) rotate(360deg);
      }
    }

    /* Culture Section - Light Cards Removed (Moved to Split System) */
    /* #culture { padding: 96px 0; ... } removed */

    .culture-container {
      max-width: 1280px;
      margin: 0 auto;
      padding: 0 24px;
    }

    .culture-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 24px;
    }

    @media (min-width: 768px) {
      .culture-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 32px;
      }
    }

    .culture-card {
      background-color: white;
      border: 1px solid var(--color-border);
      border-radius: 16px;
      padding: 40px;
      color: var(--color-text);
      height: 100%;
      transition: all 0.3s ease;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .culture-card:hover {
      transform: translateY(-5px);
      border-color: var(--color-primary);
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    .culture-card h3 {
      font-family: var(--font-serif);
      font-size: 24px;
      margin-bottom: 16px;
      color: var(--color-primary);
    }

    .culture-card p {
      color: var(--color-text-light);
      line-height: 1.6;
    }


    .culture-card h3 {
      font-family: var(--font-serif);
      font-size: 24px;
      margin-bottom: 16px;
      color: var(--color-primary);
    }

    .culture-card p {
      color: var(--color-text-light);
      line-height: 1.6;
    }

    /* ── Section: Featured (特色推荐) ────────────────────── */

    #featured {
      background-color: var(--color-bg-alt);
      height: 100vh;
      display: flex !important;
      align-items: center;
      justify-content: center;
    }

    .featured-container {
      max-width: 1400px;
      width: 90%;
      margin: 0 auto;
      text-align: center;
      margin-top: 100px;

    }

    #featured h2 {
      font-family: var(--font-serif);
      font-size: 40px;
      color: var(--color-primary);
      text-align: center;
      margin-bottom: 48px;
    }

    .featured-cards-wrapper {
      display: flex;
      flex-direction: column;
      gap: 32px;
      justify-content: center;
      position: relative;
      padding: 40px;
    }

    /* Outer thin border (slightly separate from the inner frame corners) */
    .featured-cards-wrapper::after {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      border: 1px solid rgba(201, 162, 39, 0.4);
      /* Thin gold border */
      pointer-events: none;
      z-index: 10;
    }

    /* Inner Top-Left Corner Bracket */
    .featured-cards-wrapper::before {
      content: "";
      position: absolute;
      top: 20px;
      left: 20px;
      width: 60px;
      height: 60px;
      border-top: 2px solid #c9a227;
      border-left: 2px solid #c9a227;
      pointer-events: none;
      z-index: 10;
    }

    /* Add the Bottom-Right Corner Bracket (using a child span as both pseudoelements are used, or just box-shadow trick. I will add a span to HTML or use a background gradient trick. ) wait, the span is better but let's see if we can just use another element. Wait, there's `h2` outside. I can use the container. Let's add an explicit span in the html. Wait, I can't modify HTML in this exact block easily without another call. I'll just change the wrapper's border-bottom and border-right... wait, the reference image has L-shapes. 
Let's use a subtle hack: a box-shadow trick or multiple background images on the wrapper.
Actually, I'll use `::before` for TL and I'll modify HTML to add an element for BR, but since I am editing CSS, I'll define `.corner-br` and add it to HTML. */
    .featured-corner-br {
      position: absolute;
      bottom: 20px;
      right: 20px;
      width: 60px;
      height: 60px;
      border-bottom: 2px solid #c9a227;
      border-right: 2px solid #c9a227;
      pointer-events: none;
      z-index: 10;
    }

    @media (min-width: 640px) {
      .featured-cards-wrapper {
        flex-direction: row;
        padding: 60px;
      }

      .featured-cards-wrapper::before {
        top: 30px;
        left: 30px;
        width: 80px;
        height: 80px;
      }

      .featured-corner-br {
        bottom: 30px;
        right: 30px;
        width: 80px;
        height: 80px;
      }
    }

    .featured-card {
      flex: 1;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease;
      background: white;
      border: none;
    }

    .featured-card:hover {
      transform: translateY(-8px);
    }



    /* Diagonal Split Layout System */
    :root {
      --color-split-light: #F4F1EA;
      /* Cream/Rice paper */
      --color-split-dark: #2c1810;
      /* Dark charcoal */
      --color-accent-text: #D92E2E;
      /* Red */
    }

    .split-layout-container {
      display: flex;
      flex-direction: column;
      position: relative;
      overflow: visible;
      /* Changed to visible to allow overlap if needed, but z-index handles stacking. Overflow hidden might clip shadows. */
      z-index: 20;
      /* Sit above #featured (5) */
    }

    /* ── Section: About Culture (Full-screen Split Layout) ────────────────────── */
    #about-culture {
      background-color: var(--color-bg);
      /* #f5f2ed */
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
    }

    .about-split-wrapper {
      width: 90%;
      max-width: 1400px;
      height: 80vh;
      max-height: 800px;
      margin: 0 auto;
      display: flex;
      border-radius: 24px;
      overflow: hidden;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
      background-color: var(--color-split-dark);
      /* #2c1810 */
    }

    .about-split-card {
      display: flex;
      flex-direction: column;
      width: 100%;
      height: 100%;
    }

    @media (min-width: 992px) {
      .about-split-card {
        flex-direction: row;
      }
    }

    /* ── Zen About Layout ── */
    .zen-about {
      padding: 160px 8% 120px 8%;
      background: linear-gradient(to right, #f6f4f1, #eae7e3);
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
    }

    .zen-container {
      max-width: 1400px;
      margin: auto;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 80px;
      width: 100%;
      /* background: #ffffff; */
      padding: 50px 60px 120px 60px;
      border-radius: 24px;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08);
      transform: translateY(0);
      transition: transform 0.5s ease-out, box-shadow 0.5s ease-out;
    }

    .zen-container:hover {
      transform: translateY(-12px);
      box-shadow: 0 35px 70px rgba(0, 0, 0, 0.12);
    }

    .zen-text {
      flex: 1;
    }

    .zen-label {
      display: flex;
      align-items: center;
      gap: 15px;
      font-size: 14px;
      letter-spacing: 4px;
      color: #b86b5b;
      margin-bottom: 40px;
    }

    .zen-line {
      width: 60px;
      height: 1px;
      background: #b86b5b;
    }

    .zen-title {
      font-family: var(--font-serif, "Cormorant Garamond", serif);
      font-size: 90px;
      font-weight: 400;
      line-height: 1.1;
      color: #2b2b2b;
      margin: 0;
    }

    .zen-italic {
      font-style: italic;
      font-weight: 300;
      color: #8a8a8a;
    }

    .zen-desc {
      font-family: var(--font-sans, "DM Sans", sans-serif);
      margin-top: 40px;
      max-width: 500px;
      font-size: 18px;
      line-height: 1.8;
      color: #5a5a5a;
    }

    .zen-scroll {
      margin-top: 50px;
      width: 60px;
      height: 60px;
      border: 1px solid #2b2b2b;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.3s ease;
      color: #2b2b2b;
    }

    .zen-scroll:hover {
      background: #2b2b2b;
      color: white;
    }

    .zen-images {
      flex: 1;
      position: relative;
    }

    .zen-img {
      border-radius: 6px;
      box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
      transition: transform 0.3s ease;
      display: block;
    }

    .zen-img-top {
      width: 100%;
    }

    .zen-img-bottom {
      width: 65%;
      position: absolute;
      bottom: -80px;
      left: -60px;
    }

    /* 初始隐藏动画 */
    .zen-title span,
    .zen-desc,
    .zen-img {
      opacity: 0;
      transform: translateY(40px);
      transition: all 1s ease;
    }

    .zen-title span.zen-active,
    .zen-desc.zen-active,
    .zen-img.zen-active,
    .zen-active {
      opacity: 1 !important;
      transform: translateY(0);
    }

    .zen-img-bottom.zen-active {
      animation: float 6s ease-in-out infinite 1s;
    }

    /* RWD */
    @media (max-width: 1024px) {
      .zen-about {
        height: auto;
        padding: 100px 5%;
      }

      .zen-container {
        flex-direction: column;
        gap: 40px;
      }

      .zen-title {
        font-size: 60px;
      }

      .zen-img-bottom {
        position: relative;
        bottom: 0;
        left: 0;
        width: 100%;
        margin-top: 20px;
        border: none;
      }
    }

    .floating-animation {
      animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
      0% {
        transform: translateY(0px);
      }

      50% {
        transform: translateY(-15px);
      }

      100% {
        transform: translateY(0px);
      }
    }

    .btn-cta-order {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 12px 32px;
      border: 2px solid var(--color-accent-text);
      color: var(--color-accent-text);
      border-radius: 9999px;
      font-weight: 700;
      text-transform: uppercase;
      transition: all 0.3s ease;
      background: transparent;
    }

    .btn-cta-order:hover {
      background-color: var(--color-accent-text);
      color: white;
    }

    /* =========================================
   Section 3: Culture & Services (Premium Cards V2)
   ========================================= */
    .tc-culture-section {
      padding: 100px 6%;
      width: 100%;
      background: var(--color-bg);
      /* Match the theme's background */
      font-family: "DM Sans", sans-serif;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }

    .tc-container {
      max-width: 1400px;
      margin: auto;
      width: 100%;
      padding: 60px 80px;
      border-radius: 24px;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08);
      transform: translateY(0);
      transition: transform 0.5s ease-out, box-shadow 0.5s ease-out;
    }

    .tc-container:hover {
      transform: translateY(-12px);
      box-shadow: 0 35px 70px rgba(0, 0, 0, 0.12);
    }

    /* ── Header ── */
    .tc-section-header {
      text-align: center;
      margin-bottom: 64px;
    }

    .tc-section-header .tc-eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 12px;
      font-size: 11px;
      font-weight: 500;
      letter-spacing: 4px;
      text-transform: uppercase;
      color: var(--color-primary);
      /* #a68a64 */
      margin-bottom: 20px;
    }

    .tc-section-header .tc-eyebrow::before,
    .tc-section-header .tc-eyebrow::after {
      content: "";
      width: 28px;
      height: 1px;
      background: var(--color-primary);
      display: block;
    }

    .tc-section-header h2 {
      font-family: var(--font-serif);
      font-size: 58px;
      font-weight: 300;
      color: var(--color-text);
      letter-spacing: -0.5px;
      line-height: 1;
    }

    .tc-section-header h2 em {
      font-style: normal;
      color: var(--color-primary);
    }

    .tc-header-rule {
      width: 1px;
      height: 36px;
      background: linear-gradient(to bottom, var(--color-primary), transparent);
      margin: 22px auto;
    }

    .tc-section-header p {
      max-width: 500px;
      margin: 0 auto;
      font-size: 15px;
      color: #6b7a8d;
      line-height: 1.8;
      font-weight: 300;
    }

    /* ── Grid ── */
    .tc-card-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 18px;
    }

    /* ── Base Card ── */
    .tc-card {
      position: relative;
      height: 440px;
      border-radius: 18px;
      overflow: hidden;
      cursor: pointer;
      transition: transform 0.45s cubic-bezier(0.25, 0.46, 0.45, 0.94),
        box-shadow 0.45s ease,
        opacity 0.4s ease;
    }

    /* Dim siblings */
    .tc-card-grid.any-hovered .tc-card:not(.is-hovered) {
      opacity: 0.5;
      transform: scale(0.985);
    }

    /* ── Image cards ── */
    .tc-card img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      transition: transform 0.65s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    .tc-card.is-hovered img {
      transform: scale(1.07);
    }

    /* Gradient overlay */
    .tc-card-img-overlay {
      position: absolute;
      inset: 0;
      z-index: 1;
      background: linear-gradient(to top,
          rgba(6, 10, 18, 0.88) 0%,
          rgba(6, 10, 18, 0.28) 45%,
          transparent 70%);
      transition: background 0.45s ease;
    }

    .tc-card.is-hovered .tc-card-img-overlay {
      background: linear-gradient(to top,
          rgba(6, 10, 18, 0.97) 0%,
          rgba(6, 10, 18, 0.6) 50%,
          rgba(6, 10, 18, 0.12) 100%);
    }

    /* ── Image card text overlay ── */
    .tc-card-overlay {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      padding: 28px 30px 32px;
      z-index: 2;
    }

    .tc-card-overlay .tc-num {
      display: block;
      font-size: 11px;
      letter-spacing: 3px;
      color: rgba(255, 255, 255, 0.4);
      margin-bottom: 8px;
      transition: color 0.35s ease;
    }

    .tc-card.is-hovered .tc-card-overlay .tc-num {
      color: rgba(255, 255, 255, 0.7);
    }

    .tc-card-overlay h3 {
      font-size: 20px;
      font-weight: 600;
      color: #e8edf2;
      line-height: 1.3;
      transition: color 0.35s ease;
    }

    .tc-card.is-hovered .tc-card-overlay h3 {
      color: #ffffff;
    }

    /* Description — hidden, slides up on hover */
    .tc-card-overlay .tc-desc {
      font-size: 13px;
      line-height: 1.75;
      color: rgba(255, 255, 255, 0.6);
      font-weight: 300;
      margin-top: 10px;
      opacity: 0;
      transform: translateY(10px);
      max-height: 0;
      overflow: hidden;
      transition: opacity 0.4s ease 0.07s,
        transform 0.4s ease 0.07s,
        max-height 0.42s ease 0.02s;
    }

    .tc-card.is-hovered .tc-card-overlay .tc-desc {
      opacity: 1;
      transform: translateY(0);
      max-height: 100px;
    }

    /* Accent line */
    .tc-card-overlay .tc-draw-line {
      height: 1px;
      background: rgba(255, 255, 255, 0.35);
      width: 0;
      margin-top: 14px;
      transition: width 0.4s ease 0.12s;
    }

    .tc-card.is-hovered .tc-card-overlay .tc-draw-line {
      width: 36px;
    }

    /* ── Responsive ── */
    @media (max-width: 1100px) {
      .tc-card-grid {
        grid-template-columns: repeat(2, 1fr);
      }

      .tc-section-header h2 {
        font-size: 44px;
      }
    }

    @media (max-width: 768px) {
      .tc-card-grid {
        grid-template-columns: 1fr;
      }

      .tc-culture-section {
        padding: 80px 5%;
        height: auto;
      }

      .tc-section-header h2 {
        font-size: 36px;
      }
    }

    /* Mobile Responsiveness Fixes for Clip Path */
    @media (max-width: 768px) {

      /* Remove legacy clip-path overrides — all sections use flat rectangular layout */
      #about-culture.section-padding,
      #about.section-light-top,
      #featured,
      #location {
        margin-bottom: 0 !important;
        padding-bottom: 100px !important;
      }

      #featured,
      #location,
      footer {
        padding-top: 100px !important;
      }

      /* Ensure containers have padding */
      .about-container-new,
      .featured-container,
      .location-container,
      .footer-container {
        padding-left: 16px;
        padding-right: 16px;
      }



    }

    /* --- Tokyo Menu Layout (High-End Minimal) --- */
    .haidilao-menu-body {
      background-color: #ffffff;
      color: #33322f;
      padding-top: 110px;
    }

    /* Top Category Nav bar */
    .haidilao-top-nav-container {
      background: #ffffff;
      padding: 20px 0;
      margin-bottom: 32px;
      border-bottom: 1px solid #f0ece6;
    }

    .haidilao-top-nav {
      display: flex;
      justify-content: center;
      gap: 48px;
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
      overflow-x: auto;
    }

    .haidilao-top-tab {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 10px;
      background: none;
      border: none;
      cursor: pointer;
      padding: 12px 18px;
      border-radius: 14px;
      transition: background 0.25s ease;
      outline: none;
    }

    .haidilao-top-tab:hover,
    .haidilao-top-tab.active {
      background: rgba(166, 138, 100, 0.12);
    }

    .top-tab-icon {
      width: 68px;
      height: 68px;
      border-radius: 14px;
      overflow: hidden;
      position: relative;
      border: 2px solid transparent;
      transition: border-color 0.3s, transform 0.25s ease;
    }

    .haidilao-top-tab.active .top-tab-icon {
      border-color: #a68a64;
    }

    .top-tab-icon img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .haidilao-top-tab span {
      font-size: 14px;
      font-weight: 600;
      color: #5d5a54;
      letter-spacing: 0.04em;
      text-transform: uppercase;
    }

    .haidilao-top-tab.active span {
      color: #a68a64;
    }

    /* =============================================
   LAYOUT — sidebar + content side by side
   the sidebar naturally starts at card level
   ============================================= */
    .haidilao-layout {
      display: flex;
      align-items: flex-start;
      max-width: 1600px;
      margin: 0 auto;
      padding: 0 40px 96px 40px;
      gap: 0;
    }

    /* =============================================
   SIDEBAR — sticky, text-only, Haidilao style
   ============================================= */
    .haidilao-sidebar {
      width: 160px;
      flex-shrink: 0;
      position: sticky;
      top: 100px;
      max-height: calc(100vh - 120px);
      overflow-y: auto;
      overflow-x: hidden;
      scrollbar-width: none;
      padding: 8px 12px 40px 0;
      border-right: 1.5px solid #ececec;
      margin-right: 32px;
    }

    .haidilao-sidebar::-webkit-scrollbar {
      display: none;
    }

    .sidebar-category {
      display: flex;
      flex-direction: column;
      gap: 2px;
      width: 100%;
    }

    /* Inactive tab — full-width text button */
    .haidilao-sidebar-tab {
      display: block;
      width: 100%;
      background: none;
      -webkit-appearance: none;
      appearance: none;
      border: none;
      padding: 10px 16px;
      font-size: 16px;
      color: #666 !important;
      font-weight: 400;
      cursor: pointer;
      text-align: left;
      border-radius: 26px;
      transition: background 0.15s ease, color 0.15s ease;
      outline: none;
      letter-spacing: 0.01em;
      font-family: 'Noto Sans SC', sans-serif;
      -webkit-font-smoothing: antialiased;
      white-space: nowrap;
    }

    .haidilao-sidebar-tab::before {
      display: none;
    }

    .haidilao-sidebar-tab:hover {
      background: #f7f4f0;
      color: #333 !important;
    }

    /* Active tab — full-width gold pill */
    .haidilao-sidebar-tab.active {
      background-color: #a68a64 !important;
      color: #fff !important;
    }

    /* Content Area */
    .haidilao-content {
      flex-grow: 1;
      min-width: 0;
    }

    .haidilao-content-header {
      margin-bottom: 32px;
      font-size: 14px;
      color: #9b8f7e;
      letter-spacing: 0.06em;
      text-transform: uppercase;
    }

    .haidilao-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 32px;
    }

    /* Cards — premium stage */
    .haidilao-card {
      background: #fff;
      border-radius: 22px;
      overflow: hidden;
      box-shadow: 0 4px 20px rgba(51, 50, 47, 0.07);
      transition: transform 0.38s cubic-bezier(0.25, 0.46, 0.45, 0.94), box-shadow 0.38s ease;
      display: flex;
      flex-direction: column;
      cursor: pointer;
      border: 1px solid rgba(93, 90, 84, 0.06);
    }

    .haidilao-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 20px 50px rgba(51, 50, 47, 0.13);
    }

    .img-wrap {
      width: 100%;
      aspect-ratio: 1 / 1;
      background-color: #ede8e0;
      overflow: hidden;
      position: relative;
      flex-shrink: 0;
    }

    .img-wrap img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center;
      transition: transform 0.4s ease;
      cursor: pointer;
      display: block;
    }

    .haidilao-card:hover .img-wrap img {
      transform: scale(1.08);
    }

    .info-wrap {
      padding: 22px 26px 26px 26px;
      display: flex;
      align-items: center;
      background: #fff;
      flex-grow: 1;
      border-top: 1px solid rgba(0, 0, 0, 0.04);
    }

    .title {
      font-size: 18px;
      font-weight: 600;
      color: #2c1810;
      line-height: 1.35;
      flex: 1;
      letter-spacing: 0em;
    }

    .add-btn {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      border: 1px solid #e60012;
      background: transparent;
      color: #e60012;
      display: none !important;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .add-btn:hover {
      background: #e60012;
      color: #fff;
    }

    .add-btn svg {
      width: 18px;
      height: 18px;
      fill: currentColor;
    }

    @media (max-width: 768px) {
      .haidilao-layout {
        flex-direction: column;
        padding: 0 15px 32px 15px;
      }

      .haidilao-sidebar {
        width: 100%;
        margin-bottom: 32px;
      }

      .sidebar-category {
        flex-direction: row;
        flex-wrap: nowrap;
        overflow-x: auto;
        padding-bottom: 10px;
        gap: 8px;
      }

      .haidilao-sidebar-tab {
        white-space: nowrap;
        padding: 8px 15px;
        background: #f5f5f5;
        border-radius: 30px;
        color: #333;
      }

      .haidilao-sidebar-tab::before {
        display: none;
      }

      .haidilao-sidebar-tab.active {
        margin-left: 0;
      }
    }

    /* --- Zoom Modal --- */
    .zoom-modal {
      display: flex;
      position: fixed;
      z-index: 9999;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.85);
      align-items: center;
      justify-content: center;
      opacity: 1;
      transition: opacity 0.3s ease;
    }

    .zoom-modal.hidden {
      display: none;
      opacity: 0;
    }

    .zoom-modal-content {
      position: relative;
      max-width: 90%;
      max-height: 90%;
    }

    .zoom-modal-image {
      max-width: 100vw;
      max-height: 90vh;
      object-fit: contain;
      border-radius: 8px;
      box-shadow: 0 5px 30px rgba(0, 0, 0, 0.5);
      animation: zoomIn 0.3s ease forwards;
    }

    @keyframes zoomIn {
      from {
        transform: scale(0.95);
        opacity: 0;
      }

      to {
        transform: scale(1);
        opacity: 1;
      }
    }

    .zoom-modal-close {
      position: absolute;
      top: -40px;
      right: 0;
      color: #fff;
      font-size: 35px;
      font-weight: bold;
      background: transparent;
      border: none;
      cursor: pointer;
      transition: color 0.2s;
    }

    .zoom-modal-close:hover {
      color: #e60012;
    }

    /* --- Dish Detail Side Panel --- */
    .dish-panel-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.45);
      z-index: 998;
      transition: opacity 0.35s ease;
      opacity: 1;
    }

    .dish-panel-overlay.hidden {
      opacity: 0;
      pointer-events: none;
    }

    .dish-panel {
      position: fixed;
      top: 0;
      right: 0;
      width: 420px;
      max-width: 95vw;
      height: 100%;
      background: #fff;
      z-index: 999;
      box-shadow: -5px 0 30px rgba(0, 0, 0, 0.15);
      transform: translateX(0);
      transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      display: flex;
      flex-direction: column;
      overflow-y: auto;
    }

    .dish-panel.slide-out {
      transform: translateX(100%);
    }

    .dish-panel-close {
      position: absolute;
      top: 18px;
      right: 18px;
      z-index: 10;
      background: rgba(255, 255, 255, 0.9);
      border: none;
      border-radius: 50%;
      width: 38px;
      height: 38px;
      font-size: 20px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
      transition: background 0.2s, transform 0.2s;
      color: #333;
    }

    .dish-panel-close:hover {
      background: #e60012;
      color: #fff;
      transform: rotate(90deg);
    }

    .dish-panel-img-wrap {
      width: 100%;
      aspect-ratio: 1 / 1;
      background: linear-gradient(135deg, #f5ede4, #e8d5c4);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 32px;
      flex-shrink: 0;
    }

    .dish-panel-image {
      max-width: 100%;
      max-height: 100%;
      object-fit: contain;
      filter: drop-shadow(0 12px 20px rgba(0, 0, 0, 0.2));
      animation: dishImgIn 0.5s ease forwards;
    }

    @keyframes dishImgIn {
      from {
        transform: scale(0.92) translateY(10px);
        opacity: 0;
      }

      to {
        transform: scale(1) translateY(0);
        opacity: 1;
      }
    }

    .dish-panel-info {
      padding: 32px 29px;
      flex-grow: 1;
    }

    .dish-panel-title {
      font-family: 'DM Serif Display', serif;
      font-size: 29px;
      color: #2c1810;
      margin: 0 0 8px 0;
      line-height: 1.3;
    }

    .dish-panel-category {
      font-size: 15px;
      color: #e60012;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin: 0;
    }

    @media (max-width: 480px) {
      .dish-panel {
        width: 100vw;
        max-width: 100vw;
      }
    }

    /* --- Menu Page Animations --- */

    /* Card entrance fade-up */
    @keyframes cardFadeUp {
      from {
        opacity: 0;
        transform: translateY(24px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .haidilao-card {
      animation: cardFadeUp 0.5s ease both;
    }

    /* Stagger each card slightly */
    .haidilao-card:nth-child(1) {
      animation-delay: 0.05s;
    }

    .haidilao-card:nth-child(2) {
      animation-delay: 0.10s;
    }

    .haidilao-card:nth-child(3) {
      animation-delay: 0.15s;
    }

    .haidilao-card:nth-child(4) {
      animation-delay: 0.20s;
    }

    .haidilao-card:nth-child(5) {
      animation-delay: 0.25s;
    }

    .haidilao-card:nth-child(6) {
      animation-delay: 0.30s;
    }

    .haidilao-card:nth-child(7) {
      animation-delay: 0.35s;
    }

    .haidilao-card:nth-child(8) {
      animation-delay: 0.40s;
    }

    /* Ripple zoom effect on card image hover */
    .img-wrap::after {
      content: '';
      position: absolute;
      inset: 0;
      background: rgba(255, 255, 255, 0);
      transition: background 0.3s ease;
      pointer-events: none;
    }

    .haidilao-card:hover .img-wrap::after {
      background: rgba(255, 255, 255, 0.08);
    }

    /* Sidebar tab active sliding indicator */
    .haidilao-sidebar-tab {
      position: relative;
      overflow: hidden;
    }

    .haidilao-sidebar-tab::after {
      content: '';
      position: absolute;
      left: 0;
      bottom: 0;
      height: 2px;
      width: 0;
      background: #e60012;
      transition: width 0.3s ease;
    }

    .haidilao-sidebar-tab:hover::after,
    .haidilao-sidebar-tab.active::after {
      width: 100%;
    }

    /* Top tab icon scale on hover */
    .haidilao-top-tab:hover .top-tab-icon {
      transform: scale(1.08);
      transition: transform 0.25s ease;
    }

    .top-tab-icon {
      transition: transform 0.25s ease;
    }

    /* Button press animation */
    .btn-primary,
    .btn-secondary,
    .nav-btn {
      transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.2s ease;
    }

    .btn-primary:active,
    .btn-secondary:active,
    .nav-btn:active {
      transform: scale(0.96);
      box-shadow: none !important;
    }

    /* Zoom modal image smooth scale in */
    .zoom-modal-image {
      animation: zoomIn 0.3s ease forwards;
    }

    @keyframes zoomIn {
      from {
        transform: scale(0.9);
        opacity: 0;
      }

      to {
        transform: scale(1);
        opacity: 1;
      }
    }

    /* ======================================
   Menu Page Responsive – Tablet & Mobile
   ====================================== */

    /* --- Tablet (≤ 1024px) --- */
    @media (max-width: 1024px) {

      /* Slightly smaller grid on tablet */
      .haidilao-grid {
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 16px;
      }

      /* Shrink sidebar width */
      .haidilao-sidebar {
        width: 180px;
      }

      /* Compact top nav icons */
      .top-tab-icon {
        width: 54px;
        height: 54px;
      }

      .haidilao-top-tab span {
        font-size: 14px;
      }

      .haidilao-layout {
        padding: 0 20px 48px 20px;
        gap: 24px;
      }
    }

    /* --- Mobile (≤ 640px) --- */
    @media (max-width: 640px) {

      /* Body extra top padding for nav */
      .haidilao-menu-body {
        padding-top: 100px;
      }

      /* Top category tabs: scrollable, smaller icons */
      .haidilao-top-nav {
        justify-content: flex-start;
        gap: 16px;
        padding: 0 15px;
      }

      .haidilao-top-tab {
        padding: 8px 10px;
        flex-shrink: 0;
      }

      .top-tab-icon {
        width: 52px;
        height: 52px;
        border-radius: 10px;
      }

      .haidilao-top-tab span {
        font-size: 12px;
      }

      .haidilao-top-nav-container {
        padding: 15px 0;
        margin-bottom: 16px;
      }

      /* Layout: flex row — sidebar LEFT + content RIGHT */
      .haidilao-layout {
        flex-direction: row;
        align-items: flex-start;
        padding: 0 10px 48px 0;
        gap: 0;
      }

      /* Sidebar stays on LEFT, narrow and sticky */
      .haidilao-sidebar {
        width: 88px;
        flex-shrink: 0;
        position: sticky;
        top: 70px;
        max-height: calc(100vh - 80px);
        overflow-y: auto;
        overflow-x: hidden;
        background: #fff;
        border-right: 1px solid #ececec;
        border-bottom: none;
        margin-right: 0;
        padding: 10px 6px 40px 6px;
        z-index: 10;
        scrollbar-width: none;
      }

      .haidilao-sidebar::-webkit-scrollbar {
        display: none;
      }

      /* Vertical column on mobile */
      .sidebar-category {
        flex-direction: column;
        flex-wrap: nowrap;
        overflow-x: visible;
        gap: 4px;
        padding: 0;
      }

      /* Compact mobile sidebar tab */
      .haidilao-sidebar-tab {
        display: block;
        width: 100%;
        white-space: normal;
        word-break: break-word;
        padding: 8px 8px;
        font-size: 12px;
        font-weight: 500;
        border-radius: 10px;
        background: none;
        color: #666 !important;
        border: none;
        min-height: 40px;
        text-align: center;
        line-height: 1.3;
      }

      .haidilao-sidebar-tab.active {
        background: #a68a64 !important;
        color: #fff !important;
        font-weight: 600;
      }

      /* Content: takes up remaining width */
      .haidilao-content {
        flex: 1;
        min-width: 0;
        padding: 10px 10px 0 10px;
      }

      /* Grid: 1 column (big cards) */
      .haidilao-grid {
        grid-template-columns: 1fr;
        gap: 14px;
      }

      /* Cards: wide landscape image */
      .img-wrap {
        aspect-ratio: 4 / 3;
      }

      /* Card info */
      .info-wrap {
        padding: 14px 16px 16px 16px;
      }

      .title {
        font-size: 15px;
      }

      /* Top nav (Grand / Sushi tabs) — compact on mobile */
      .haidilao-top-nav-container {
        padding: 12px 0;
        margin-bottom: 0;
      }

      .haidilao-top-tab {
        padding: 6px 10px;
        flex-shrink: 0;
      }

      .top-tab-icon {
        width: 52px;
        height: 52px;
        border-radius: 10px;
      }

      .haidilao-top-tab span {
        font-size: 12px;
      }

      /* Zoom modal full screen on mobile */
      .zoom-modal-content {
        max-width: 100%;
        max-height: 100%;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
      }

      .zoom-modal-image {
        border-radius: 8px;
        max-height: 80vh;
        width: auto;
      }

      .zoom-modal-close {
        top: 8px;
        right: 8px;
        font-size: 28px;
        width: 36px;
        height: 36px;
      }

      /* Nav: logo left + 返回首页 right only */
      .nav-links {
        display: none;
      }

      .mobile-menu-btn,
      .mobile-nav-dropdown {
        display: none !important;
      }

      .nav-actions .btn-primary,
      .nav-actions .btn-secondary {
        display: none !important;
      }

      /* Make nav a floating pill / capsule on mobile as requested */
      #nav {
        width: 92%;
        max-width: 400px;
        /* Limits width on extra large phones so it doesn't look stretched */
        height: 61px;
        top: 16px;
        left: 50%;
        transform: translateX(-50%);
        border-radius: 9999px;
        padding: 0 16px;
        background-color: #fdfaf6;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
      }
    }

    /* Desktop Default Hidden Elements */
    .mobile-menu-btn,
    .mobile-sidebar-overlay,
    .mobile-sidebar {
      display: none !important;
    }

    /* Minimalist Hamburger Button & Sidebar Display Logic */
    @media (max-width: 640px) {
      .mobile-menu-btn {
        display: flex !important;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        gap: 6px;
        width: 40px;
        height: 40px;
        background: transparent;
        border: none;
        cursor: pointer;
        margin-left: auto;
        padding: 0;
        z-index: 1000;
      }

      .mobile-sidebar-overlay {
        display: block !important;
      }

      .mobile-sidebar {
        display: flex !important;
      }
    }

    .mobile-menu-btn span {
      display: block;
      width: 24px;
      height: 2px;
      background: #a68a64;
      /* Elegant gold lines */
      border-radius: 2px;
      transition: all 0.3s ease;
    }

    /* Sidebar Overlay (Dark Fade) */
    .mobile-sidebar-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.4);
      backdrop-filter: blur(4px);
      z-index: 9998;
      opacity: 0;
      visibility: hidden;
      transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    .mobile-sidebar-overlay.active {
      opacity: 1;
      visibility: visible;
    }

    /* Minimalist Right Sidebar Panel */
    .mobile-sidebar {
      position: fixed;
      top: 0;
      right: -320px;
      /* Hidden off-screen */
      width: 300px;
      max-width: 85vw;
      height: 100%;
      background: #fdfaf6;
      z-index: 9999;
      box-shadow: -10px 0 30px rgba(0, 0, 0, 0.1);
      transition: transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
      display: flex;
      flex-direction: column;
      padding: 64px 32px 32px;
    }


    /* 
.mobile-sidebar.active {
  transform: translateX(-320px);
  Slide in 
}
*/
    /* Sidebar Close Button (`×`) */
    .mobile-sidebar-close {
      position: absolute;
      top: 24px;
      right: 24px;
      font-size: 40px;
      color: #a68a64;
      background: none;
      border: none;
      cursor: pointer;
      line-height: 1;
      padding: 0;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: transform 0.3s ease;
    }

    .mobile-sidebar-close:active {
      transform: scale(0.9);
    }

    /* =========================================
   MOBILE SIDEBAR NAVIGATION (CONSOLIDATED)
   ========================================= */
    .mobile-sidebar-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(5px);
      -webkit-backdrop-filter: blur(5px);
      opacity: 0;
      visibility: hidden;
      transition: all 0.4s ease;
      z-index: 10000;
      /* Must be higher than the 9999 nav bar */
    }

    .mobile-sidebar-overlay.active {
      opacity: 1;
      visibility: visible;
    }

    .mobile-sidebar {
      position: fixed;
      top: 0;
      right: -300px;
      /* Hide off-screen right */
      width: 280px;
      height: 100vh;
      background-color: var(--color-bg);
      /* #f5f2ed */
      box-shadow: -5px 0 20px rgba(0, 0, 0, 0.1);
      transition: right 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
      z-index: 10002;
      /* Must be higher than overlay */
      display: flex;
      flex-direction: column;
      padding: 40px 30px;
    }

    .mobile-sidebar.active {
      right: 0;
      /* Slide in to be flush with right edge */
    }

    .mobile-sidebar-close {
      position: absolute;
      top: 20px;
      right: 20px;
      background: none;
      border: none;
      font-size: 2.5rem;
      color: var(--color-text);
      /* #33322f */
      cursor: pointer;
      line-height: 1;
      padding: 0;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: transform 0.3s ease;
    }

    .mobile-sidebar-close:hover,
    .mobile-sidebar-close:active {
      transform: rotate(90deg) scale(0.9);
      color: var(--color-primary);
      /* #a68a64 */
    }

    .mobile-sidebar-content {
      display: flex;
      flex-direction: column;
      gap: 20px;
      margin-top: 50px;
      /* Space below close btn */
    }

    .sidebar-link {
      font-family: var(--font-sans);
      font-size: 1.4rem;
      font-weight: 500;
      color: var(--color-text);
      text-decoration: none;
      display: block;
      /* Guaranteed layout structure */
      padding: 8px 0;
      border-bottom: 1px solid rgba(166, 138, 100, 0.15);
      /* Faint horizontal line */
      position: relative;
      transition: color 0.3s ease, padding-left 0.3s ease;
      /* Removed opacity trickery to ensure they are fully visible */
      opacity: 1;
      transform: none;
    }

    .sidebar-link:active,
    .sidebar-link:hover {
      color: var(--color-primary);
      /* #a68a64 */
      padding-left: 10px;
      /* Slight indent on interaction */
    }

    /* =========================================
   USER CUSTOM SCROLL SNAP AND ANIMATION
   ========================================= */

    html,
    body {
      margin: 0;
      padding: 0;
      height: 100%;
      overflow: hidden;
    }

    /* Allow normal scroll on menu page */
    html.menu-html,
    html.menu-html body {
      overflow: auto !important;
      height: auto !important;
    }

    .ag-scroll-wrapper {
      height: 100vh;
      overflow-y: auto;
      scroll-snap-type: y mandatory;
      scroll-behavior: smooth;
    }

    .ag-section {
      height: 100vh;
      scroll-snap-align: start;
      position: relative;
      opacity: 0;
      transform: translateY(60px);
      transition: all 0.9s cubic-bezier(.22, 1, .36, 1);
    }

    .ag-section.ag-active {
      opacity: 1;
      transform: translateY(0);
    }

    /* ═══════════════════════════════
   FOOTER SECTION
═══════════════════════════════ */

    /* Footer - 非全屏，居中容器 */
    .footer-section {
      background-color: #2c1a0e;
      color: #f5ebe0;
      padding: 60px 20px 0;
    }

    .footer-scroll-buffer {
      height: auto !important;
      min-height: max-content;
      display: block !important;
    }

    .footer-container {
      max-width: 1100px;
      margin: 0 auto;
    }

    .footer-content {
      display: grid;
      grid-template-columns: 2fr 1fr 1.5fr 1.5fr;
      gap: 40px;
      padding-bottom: 40px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .footer-brand .footer-logo {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 16px;
    }

    .footer-logo-name {
      font-size: 14px;
      font-weight: 700;
      letter-spacing: 2px;
      color: #c9a96e;
    }

    .footer-logo-sub {
      font-size: 10px;
      letter-spacing: 3px;
      color: #a0785a;
    }

    .footer-tagline {
      font-size: 13px;
      color: #b8967a;
      line-height: 1.7;
    }

    .footer-links h4,
    .footer-contact h4,
    .footer-hours h4 {
      font-size: 13px;
      letter-spacing: 2px;
      color: #c9a96e;
      margin-bottom: 16px;
      text-transform: uppercase;
    }

    .footer-links ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .footer-links ul li {
      margin-bottom: 10px;
    }

    .footer-links ul li a {
      color: #b8967a;
      text-decoration: none;
      font-size: 13px;
      transition: color 0.2s;
    }

    .footer-links ul li a:hover {
      color: #f5ebe0;
    }

    .footer-contact p,
    .footer-hours p {
      font-size: 13px;
      color: #b8967a;
      margin-bottom: 8px;
      line-height: 1.6;
    }

    .footer-contact a {
      color: #b8967a;
      text-decoration: none;
      transition: color 0.2s;
    }

    .footer-contact a:hover {
      color: #f5ebe0;
    }

    .footer-bottom {
      text-align: center;
      padding: 20px 0;
      font-size: 12px;
      color: #7a5c45;
      letter-spacing: 1px;
    }

    /* 响应式 */
    @media (max-width: 768px) {
      .footer-content {
        grid-template-columns: 1fr 1fr;
        gap: 30px;
      }
    }

    @media (max-width: 480px) {
      .footer-content {
        grid-template-columns: 1fr;
      }
    }

    /* ═══════════════════════════════
/* ═══════════════════════════════
/* ═══════════════════════════════
   CUSTOM SWIPER PAGINATION (IMAGE DESIGN)
═══════════════════════════════ */
    /* Container */
    .swiper-pagination {
      position: absolute;
      left: 0% !important;
      /* Left side */
      right: auto !important;
      top: 50% !important;
      transform: translateY(-50%) !important;
      display: flex;
      flex-direction: column;
      gap: 0px;
      /* Increased spacing between the icons */
      z-index: 50 !important;
      transition: opacity 0.5s ease, visibility 0.5s ease;
    }

    /* Base style for all inactive segments (Faded icons) */
    .swiper-pagination-bullet {
      width: 45px !important;
      /* Enlarged icon width */
      height: 45px !important;
      /* Enlarged icon height */
      background: url('image/bamboo-icon.png') no-repeat center center !important;
      background-size: contain !important;
      /* Ensure image fits perfectly without distortion */
      background-color: transparent !important;
      /* Remove any solid background */
      border-radius: 0 !important;
      /* Icons don't need border radius */
      opacity: 0.65 !important;
      /* Increased from 0.35 to make it visible */
      box-shadow: none !important;
      margin: 0 !important;
      transition: all 0.4s cubic-bezier(0.25, 1, 0.5, 1) !important;
      /* Smooth morphing and glow */
      cursor: pointer;
      position: relative;
      /* Magic filter to turn the dark green icon into a bright golden cream color (#c9a96e) */
      filter: brightness(0) saturate(100%) invert(80%) sepia(20%) saturate(600%) hue-rotate(345deg) brightness(95%) contrast(85%) !important;
    }

    /* Hover effect on inactive segments */
    .swiper-pagination-bullet:hover {
      opacity: 0.8 !important;
      transform: scale(1.15) !important;
      /* Slightly expands on hover */
    }

    /* 
 * Active segment styling:
 * ONLY this one changes. The rest fade back to the default state.
 */
    .swiper-pagination-bullet-active {
      opacity: 1 !important;
      /* Fully bright */
      transform: scale(1.3) !important;
      /* Enlarge the active icon */
      /* Fix typo: drop-shadow instead of drop_shadow, and add a heavy golden glow */
      filter: brightness(0) saturate(100%) invert(80%) sepia(20%) saturate(600%) hue-rotate(345deg) brightness(50%) contrast(110%) drop-shadow(0 0 10px rgba(201, 169, 110, 0.9)) !important;
    }

    /* =========================================
   MOBILE OPTIMIZATIONS (NORMAL SCROLL & NORMAL SIDEBAR)
   ========================================= */
    @media (max-width: 768px) {

      /* Hide computer-specific UI */
      .swiper-pagination {
        display: none !important;
      }

      /* Reset Swiper structures to allow normal native scrolling */
      html,
      body {
        overflow: auto !important;
        height: auto !important;
        position: static !important;
      }

      .swiper {
        height: auto !important;
        overflow: visible !important;
      }

      .swiper-wrapper {
        height: auto !important;
        display: block !important;
        transform: none !important;
        flex-direction: column !important;
      }

      .swiper-slide {
        height: auto !important;
        min-height: 100vh;
        display: block !important;
        /* Remove flex constraints */
        position: relative !important;
      }
    }

    /* --- Generic Prominent Section Decoration --- */
    .section-decoration {
      position: absolute;
      width: 120px;
      height: 120px;
      opacity: 0.7;
      pointer-events: none;
      z-index: 10;
    }

    @media (min-width: 768px) {
      .section-decoration {
        width: 200px;
        height: 200px;
        opacity: 0.9;
      }
    }

    .section-decoration.left {
      top: 15%;
      left: -20px;
    }

    @media (min-width: 768px) {
      .section-decoration.left {
        left: 2%;
      }
    }

    .section-decoration.right {
      top: 65%;
      right: -20px;
      transform: scaleX(-1);
    }

    @media (min-width: 768px) {
      .section-decoration.right {
        right: 2%;
      }
    }

    /* Ensure sections holding decorations are relative */
    #about-culture,
    #mission-vision,
    #featured,
    #location {
      position: relative;
      overflow: hidden;
      /* Prevent decorations from creating horizontal scroll */
    }

    /* ══════════════════════════════════════
   CULTURE CAROUSEL SECTION
══════════════════════════════════════ */
    .culture-carousel-section {
      padding: 120px 0 140px;
      text-align: center;
      background: var(--color-bg);
      overflow: hidden;
    }

    /* ── Header ── */
    .cc-eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 14px;
      font-size: 10.5px;
      font-weight: 500;
      letter-spacing: 4.5px;
      text-transform: uppercase;
      color: var(--color-primary);
      margin-bottom: 22px;
    }

    .cc-eyebrow::before,
    .cc-eyebrow::after {
      content: "";
      width: 30px;
      height: 1px;
      background: var(--color-primary);
      display: block;
    }

    .cc-main-title {
      font-family: var(--font-serif);
      font-size: 62px;
      font-weight: 300;
      color: var(--color-text);
      letter-spacing: -0.5px;
      line-height: 1;
    }

    .cc-main-title em {
      font-style: normal;
      color: var(--color-primary);
    }

    .cc-rule {
      width: 1px;
      height: 36px;
      background: linear-gradient(to bottom, var(--color-primary), transparent);
      margin: 24px auto 0;
    }

    /* ── Dynamic title ── */
    .cc-slide-title {
      font-family: var(--font-serif);
      font-size: 42px;
      font-weight: 300;
      color: var(--color-text);
      margin: 90px auto 0;
      letter-spacing: 0.5px;
      transition: opacity 0.25s ease, transform 0.25s ease;
      min-height: 52px;
    }

    .cc-slide-title.fading {
      opacity: 0;
      transform: translateY(5px);
    }

    /* ══════════════════════════════════════
   CAROUSEL LAYOUT
══════════════════════════════════════ */
    .cc-carousel-outer {
      position: relative;
      width: calc(var(--card-w) * 3 + var(--gap) * 2);
      margin: 40px auto 0;
      user-select: none;
    }

    .cc-viewport {
      overflow: hidden;
      margin: 0 -28px;
    }

    .cc-track {
      position: relative;
      cursor: grab;
      /* Width/Height will be calculated by JS based on card size + gaps */
    }

    .cc-track.grabbing {
      cursor: grabbing;
    }

    /* ── Card base ── */
    .cc-card {
      position: absolute;
      width: var(--card-w);
      height: var(--card-h);
      border-radius: 20px;
      overflow: hidden;
      transform: translate3d(0, 0, 0);
      will-change: transform, opacity, filter;
      cursor: pointer;
    }

    .cc-card img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      pointer-events: none;
    }

    .cc-card-overlay {
      position: absolute;
      inset: 0;
      z-index: 1;
      background: linear-gradient(to top, rgba(10, 9, 7, .82) 0%, rgba(10, 9, 7, .4) 50%, rgba(10, 9, 7, .2) 100%);
      transition: opacity 0.42s ease;
    }

    .cc-card.active .cc-card-overlay {
      background: linear-gradient(to top, rgba(10, 9, 7, .4) 0%, transparent 70%);
      opacity: 0.35;
      /* Active card is much brighter and clearer */
    }

    .cc-card-num {
      position: absolute;
      bottom: 24px;
      left: 24px;
      z-index: 2;
      font-size: 10px;
      letter-spacing: 3px;
      color: rgba(255, 255, 255, 0.45);
      font-family: var(--font-sans);
    }

    /* ── Nav ── */
    .cc-nav {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      width: 44px;
      height: 44px;
      border-radius: 50%;
      border: none;
      background: transparent;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 10;
      padding: 0;
      transition: transform 0.22s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    .cc-nav::before {
      content: "";
      position: absolute;
      inset: 0;
      border-radius: 50%;
      border: 1px solid rgba(166, 138, 100, 0.28);
      background: rgba(245, 242, 237, 0.98);
      transition: border-color .3s, background .3s, box-shadow .3s;
    }

    .cc-nav:hover::before {
      border-color: var(--color-primary);
      background: rgba(255, 255, 255, 0.96);
      box-shadow: 0 4px 18px rgba(166, 138, 100, 0.2);
    }

    .cc-nav svg {
      position: relative;
      z-index: 1;
      width: 14px;
      height: 14px;
      stroke: var(--color-primary);
      stroke-width: 1.8;
      stroke-linecap: round;
      stroke-linejoin: round;
      fill: none;
      transition: transform 0.22s ease;
    }

    .cc-nav:hover {
      transform: translateY(-50%) scale(1.1);
    }

    .cc-nav.left:hover svg {
      transform: translateX(-2px);
    }

    .cc-nav.right:hover svg {
      transform: translateX(2px);
    }

    .cc-nav.left {
      left: -80px;
    }

    .cc-nav.right {
      right: -80px;
    }

    /* ── Description ── */
    .cc-description {
      max-width: 800px;
      margin: 44px auto 0;
      font-size: 18px;
      line-height: 1.85;
      color: var(--color-text-light);
      font-weight: 300;
      letter-spacing: .2px;
      transition: opacity .25s ease, transform .25s ease;
      min-height: 54px;
    }

    .cc-description.fading {
      opacity: 0;
      transform: translateY(5px);
    }

    /* ── Dots ── */
    .cc-dots {
      display: flex;
      justify-content: center;
      gap: 8px;
      margin-top: 40px;
    }

    .cc-dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      border: none;
      padding: 0;
      background: rgba(166, 138, 100, .25);
      transition: background .3s, transform .3s;
      cursor: pointer;
    }

    .cc-dot.active {
      background: var(--color-primary);
      transform: scale(1.4);
    }

    /* ── Responsive ── */
    @media (max-width: 1300px) {
      .cc-nav.left {
        left: -20px;
      }

      .cc-nav.right {
        right: -20px;
      }
    }

    @media (max-width: 940px) {
      :root {
        --card-w: 220px;
        --card-h: 310px;
      }

      .cc-nav.left {
        left: -10px;
      }

      .cc-nav.right {
        right: -10px;
      }
    }

    @media (max-width: 768px) {
      .culture-carousel-section {
        padding: 80px 0;
        height: auto !important;
      }
    }

    @media (max-width: 700px) {
      :root {
        --card-w: 170px;
        --card-h: 240px;
        --gap: 10px;
      }

      .cc-main-title {
        font-size: 40px;
      }

      .cc-slide-title {
        font-size: 22px;
      }

      .cc-nav.left {
        left: 2px;
      }

      .cc-nav.right {
        right: 2px;
      }
    }
  </style>
</head>

<body>



  <!-- 粘性导航 -->
  <nav id="nav" class="haidilao-nav">
    <div class="nav-container">
      <a href="#hero" class="nav-logo-link">
        <div class="haidilao-logo-bg">
          <img src="image/tokyologo.png" alt="Tokyo" class="nav-logo">
        </div>
        <div class="nav-text-group">
          <span class="nav-text primary">TOKYO JAPANESE</span>
          <span class="nav-text secondary">CUISINE</span>
        </div>
      </a>
      <div class="nav-links">
        <a href="https://kunzzgroup.com/frontend/index">首页</a>
        <a href="#about-culture">关于我们</a>
        <a href="#mission-vision">文化+服务</a>
        <div class="nav-dropdown">
          <a href="#featured" class="nav-dropdown-trigger">特色推荐 <span class="nav-dropdown-arrow">▾</span></a>
          <div class="nav-dropdown-menu">
            <div class="nav-dropdown-card">
              <a href="menu#sushi" class="nav-dropdown-item">Sushi Menu</a>
              <a href="menu#grand" class="nav-dropdown-item">Grand Menu</a>
            </div>
          </div>
        </div>
        <a href="#location">我们在这</a>
      </div>
      <div class="nav-actions">
        <a href="#location" class="btn-primary nav-btn"
          style="background-color: #a68a64; border-color: #a68a64; box-shadow: 0 4px 10px rgba(166, 138, 100, 0.3);">联系我们</a>
        <a href="#featured" class="btn-secondary nav-btn" style="color: #33322f; border-color: #33322f;">查看菜单</a>
        <!-- Elegant Minimalist Hamburger Button -->
        <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Menu">
          <span></span>
          <span></span>
          <span></span>
        </button>
      </div>
    </div>
  </nav>

  <!-- Minimalist Right Sidebar Mobile Menu -->
  <div class="mobile-sidebar-overlay" id="mobile-sidebar-overlay"></div>
  <aside class="mobile-sidebar" id="mobile-sidebar">
    <button class="mobile-sidebar-close" id="mobile-sidebar-close" aria-label="Close menu">×</button>
    <div class="mobile-sidebar-content">
      <a href="https://kunzzgroup.com/frontend/index" class="sidebar-link">首页</a>
      <a href="#about-culture" class="sidebar-link">关于我们</a>
      <a href="#mission-vision" class="sidebar-link">文化+服务</a>
      <a href="menu" class="sidebar-link">查看菜单</a>
      <a href="#location" class="sidebar-link">我们在这</a>
      <a href="#contact" class="sidebar-link">联系我们</a>
      <a href="#member" class="sidebar-link">会员</a>
    </div>
  </aside>

  <div class="swiper mySwiper">
    <div class="swiper-wrapper">

      <!-- Hero 英雄区 -->
      <section id="hero" class="swiper-slide">
        <div class="hero-bokeh">
          <span></span><span></span><span></span><span></span><span></span><span></span>
          <span></span><span></span><span></span><span></span><span></span><span></span>
        </div>
        <div class="hero-slider" id="hero-slider">
          <?php if (isset($mediaConfig['tokyo_background']) && $mediaConfig['tokyo_background']['type'] === 'video'): ?>
            <video autoplay loop muted playsinline style="position: absolute; width: 100%; height: 100%; object-fit: cover;">
              <source src="<?php echo htmlspecialchars($mediaConfig['tokyo_background']['url']); ?>" type="video/mp4">
            </video>
          <?php elseif (isset($mediaConfig['tokyo_background']) && $mediaConfig['tokyo_background']['type'] === 'image'): ?>
            <div class="hero-slide active" style="background-image: url('<?php echo htmlspecialchars($mediaConfig['tokyo_background']['url']); ?>'); height: 100%; width: 100%; background-size: cover; background-position: center;"></div>
          <?php else: ?>
            <div class="hero-slide active" style="background-image: url('image/sushi-dish-asian-restaurant.jpg'); height: 100%; width: 100%; background-size: cover; background-position: center;"></div>
          <?php endif; ?>
        </div>
        <div class="hero-overlay"></div>
        <div class="shoji-lattice"></div>

        <div class="hero-content">
          <img src="image/tokyologo.png" alt="Tokyo Japanese Cuisine" class="hero-logo floating-logo pulse-glow" />
          <h1 class="hero-title">
            <span style="white-space: nowrap;">
              <span class="letter-reveal" style="--d:0.05s">T</span><span class="letter-reveal"
                style="--d:0.1s">o</span><span class="letter-reveal" style="--d:0.15s">k</span><span
                class="letter-reveal" style="--d:0.2s">y</span><span class="letter-reveal" style="--d:0.25s">o</span>
            </span>
            <span class="letter-reveal" style="--d:0.3s">&nbsp;</span>
            <span style="white-space: nowrap;">
              <span class="letter-reveal" style="--d:0.35s">J</span><span class="letter-reveal"
                style="--d:0.4s">a</span><span class="letter-reveal" style="--d:0.45s">p</span><span
                class="letter-reveal" style="--d:0.5s">a</span><span class="letter-reveal"
                style="--d:0.55s">n</span><span class="letter-reveal" style="--d:0.6s">e</span><span
                class="letter-reveal" style="--d:0.65s">s</span><span class="letter-reveal" style="--d:0.7s">e</span>
            </span>
            <span class="letter-reveal" style="--d:0.75s">&nbsp;</span>
            <span style="white-space: nowrap;">
              <span class="letter-reveal" style="--d:0.8s">C</span><span class="letter-reveal"
                style="--d:0.85s">u</span><span class="letter-reveal" style="--d:0.9s">i</span><span
                class="letter-reveal" style="--d:0.95s">s</span><span class="letter-reveal"
                style="--d:1.0s">i</span><span class="letter-reveal" style="--d:1.05s">n</span><span
                class="letter-reveal" style="--d:1.1s">e</span>
            </span>
          </h1>
          <p class="hero-subtitle shimmer-text">精致美食·品越服务</p>
          <p class="hero-tagline reveal delay-300">成就世界级日料品牌</p>
          <div class="hero-buttons reveal delay-400">
            <a href="#location" class="btn-primary spring-btn">联系我们</a>
            <a href="#about-culture" class="btn-secondary spring-btn">了解更多</a>
          </div>
        </div>

        <div class="scroll-indicator">
          <div class="arrow-scroll">
            <span></span><span></span><span></span>
          </div>
        </div>
      </section>

      <!-- Section 2: About Us — Zen Minimalist Layout -->
      <section id="about-culture" class="zen-about swiper-slide">
        <svg class="section-decoration left" viewBox="0 0 80 40" fill="none" stroke="#c9a227" stroke-width="1.5"
          xmlns="http://www.w3.org/2000/svg">
          <path d="M10 20 Q30 8 50 20 T90 20" opacity="0.6" />
          <path d="M5 25 Q25 12 45 25 T85 25" opacity="0.3" />
        </svg>
        <svg class="section-decoration right" viewBox="0 0 80 40" fill="none" stroke="#c9a227" stroke-width="1.5"
          xmlns="http://www.w3.org/2000/svg">
          <path d="M10 20 Q30 8 50 20 T90 20" opacity="0.6" />
          <path d="M5 25 Q25 12 45 25 T85 25" opacity="0.3" />
        </svg>
        <div class="zen-container">

          <div class="zen-text">
            <div class="zen-label">
              <span class="zen-line"></span>
              <span>ABOUT US</span>
            </div>

            <h2 class="zen-title">
              <span>关于我们</span>

            </h2>

            <p class="zen-desc">
              我们是一家致力于提供精致料理与品越服务的日式料理餐厅。
              以极致的匠心打造美食。严选当季新鲜食材,融合传统与创意,呈现日本料理美。
              餐厅环境清雅舒适,充满日式格调。宾客在此不仅能品味精妙料理,更能感受到细致入微的服务与文化魅力。
              我们立志将每一次用餐变成难忘的美食之旅, 以品越的服务和精致的料理成为世界级日料品牌。
            </p>

            <a href="#location" class="btn-primary spring-btn"
              style="margin-top: 50px; text-decoration: none; width: fit-content; display: inline-block;">联系我们</a>
          </div>

          <div class="zen-images">
            <img src="image/tokyologo.png" class="zen-img zen-img-top"
              style="height: 500px; object-fit: cover; border-radius: 16px;">
            <img src="image/sushi-dish-asian-restaurant.jpg" class="zen-img zen-img-bottom">
          </div>

        </div>
      </section>


      <!-- Section 3: Culture Carousel (Infinite Track) -->
      <section id="mission-vision" class="culture-carousel-section swiper-slide">

        <h3 class="cc-slide-title" id="cc-title"></h3>

        <div class="cc-carousel-outer">
          <button class="cc-nav left" id="cc-prev" aria-label="Previous">
            <svg viewBox="0 0 14 14">
              <polyline points="9,2 4,7 9,12" />
            </svg>
          </button>
          <div class="cc-viewport">
            <div class="cc-track" id="cc-track">
              <!-- Initial 4 Source Cards (JS will clone these for infinite scroll) -->
              <div class="cc-card" data-title="使命 Mission"
                data-desc="以极致的匠心和热情为每一位顾客呈现最正宗的日式料理体验我们致力于将传统日本饮食文化与现代创新完美融合"
                style="background: linear-gradient(135deg,#2d5a27,#1a3a16)">
                <img src="image/使命.jpeg" alt="Mission">
                <div class="cc-card-overlay"></div>
                <span class="cc-card-num">01</span>
              </div>
              <div class="cc-card" data-title="愿景 Vision"
                data-desc="成为世界级的日料品牌让更多人感受到日本料理的精致与美好我们希望通过品越的服务和品质成为顾客心中最值得信赖的日式餐厅"
                style="background: linear-gradient(135deg,#3d4a2d,#232e1a)">
                <img src="image/愿景.jpeg" alt="Vision">
                <div class="cc-card-overlay"></div>
                <span class="cc-card-num">02</span>
              </div>
              <div class="cc-card" data-title="价值观 Values" data-desc="专注品质追求完美尊重传统勇于创新我们相信细节决定成败每一个环节都精益求精为顾客创造难忘的用餐体验"
                style="background: linear-gradient(135deg,#4a3d1a,#2e2610)">
                <img src="image/价值观.jpeg" alt="Values">
                <div class="cc-card-overlay"></div>
                <span class="cc-card-num">03</span>
              </div>
              <div class="cc-card" data-title="人品 Character"
                data-desc="诚信为本匠心独运服务至上我们以最真诚的态度对待每一位顾客用心制作每一道料理让顾客感受到家一般的温暖和关怀"
                style="background: linear-gradient(135deg,#1a2d4a,#101e2e)">
                <img src="image/人品.jpeg" alt="Character">
                <div class="cc-card-overlay"></div>
                <span class="cc-card-num">04</span>
              </div>
            </div>
          </div>
          <button class="cc-nav right" id="cc-next" aria-label="Next">
            <svg viewBox="0 0 14 14">
              <polyline points="5,2 10,7 5,12" />
            </svg>
          </button>
        </div>

        <p class="cc-description" id="cc-desc"></p>
        <div class="cc-dots" id="cc-dots"></div>
      </section>

      <!-- 特色推荐：礼品卡 / 菜单卡片 + 寿司菜单 -->
      <section id="featured" class="swiper-slide">
        <svg class="section-decoration left" viewBox="0 0 80 40" fill="none" stroke="#c9a227" stroke-width="1.5"
          xmlns="http://www.w3.org/2000/svg">
          <path d="M10 20 Q30 8 50 20 T90 20" opacity="0.6" />
          <path d="M5 25 Q25 12 45 25 T85 25" opacity="0.3" />
        </svg>
        <svg class="section-decoration right" viewBox="0 0 80 40" fill="none" stroke="#c9a227" stroke-width="1.5"
          xmlns="http://www.w3.org/2000/svg">
          <path d="M10 20 Q30 8 50 20 T90 20" opacity="0.6" />
          <path d="M5 25 Q25 12 45 25 T85 25" opacity="0.3" />
        </svg>
        <div class="featured-container">
          <h2>特色推荐</h2>
          <div class="featured-cards-wrapper">
            <span class="featured-corner-br"></span>
            <a href="menu#sushi" class="featured-card reveal scale-up delay-100">
              <div class="featured-card-image-wrapper">
                <img src="image/menu1.png" alt="Sushi Menu" class="featured-card-img">
                <div class="featured-card-overlay">
                  <span class="featured-card-btn">View Sushi & Sashimi Menu</span>
                </div>
              </div>
              <div class="featured-card-info">
                <h3>Sushi & Sashimi Menu</h3>
                <p>Fresh catch from Tokyo Bay</p>
              </div>
            </a>
            <a href="menu#grand" class="featured-card reveal scale-up delay-200">
              <div class="featured-card-image-wrapper">
                <img src="image/menu2.png" alt="Grand Menu" class="featured-card-img">
                <div class="featured-card-overlay">
                  <span class="featured-card-btn">View Grand Menu</span>
                </div>
              </div>
              <div class="featured-card-info">
                <h3>Grand Menu</h3>
                <p>Authentic Hot Dishes & Sets</p>
              </div>
            </a>
          </div>
        </div>
      </section>

      <!-- 我们在这 -->
      <section id="location" class="swiper-slide">
        <svg class="section-decoration left" viewBox="0 0 80 40" fill="none" stroke="#c9a227" stroke-width="1"
          xmlns="http://www.w3.org/2000/svg">
          <path d="M10 20 Q30 8 50 20 T90 20" opacity="0.8" />
          <path d="M5 25 Q25 12 45 25 T85 25" opacity="0.5" />
        </svg>
        <svg class="section-decoration right" viewBox="0 0 80 40" fill="none" stroke="#c9a227" stroke-width="1"
          xmlns="http://www.w3.org/2000/svg">
          <path d="M10 20 Q30 8 50 20 T90 20" opacity="0.8" />
          <path d="M5 25 Q25 12 45 25 T85 25" opacity="0.5" />
        </svg>
        <div class="location-container">
          <h2>
            <span class="location-divider"></span>
            我们在这
            <span class="location-divider"></span>
          </h2>
          <div class="location-cards">
            <div class="location-card reveal fade-left delay-100">
              <div class="location-card-map">
                <iframe title="总店地图"
                  src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.4475276450275!2d103.77369647567853!3d1.501963561066547!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31da6dd0d05beaa3%3A0x5617d385f1f81e11!2sTokyo%20Japanese%20Cuisine%20%40%20Mid%20Valley%20Southkey%20(NORTH%20COURT)%20JB!5e0!3m2!1sen!2smy!4v1762160658810!5m2!1sen!2smy"
                  allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
              </div>
              <div class="location-card-info">
                <h3>总店</h3>
                <p>地址：<a
                    href="https://www.google.com/maps/search/?api=1&query=T-042+Level+3,+Mid+Valley,+The+Mall,+Southkey,+81100+Johor+Bahru,+Johor+Darul+Ta'zim"
                    target="_blank" rel="noopener noreferrer">T-042 Level 3, Mid Valley, The Mall, Southkey, 81100 Johor
                    Bahru, Johor Darul Ta'zim</a></p>
                <p>电话：<a href="tel:+60197108090">+60 19-710 8090</a></p>
              </div>
            </div>
            <div class="location-card reveal fade-right delay-200">
              <div class="location-card-map">
                <iframe title="分店地图"
                  src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d31907.468559496578!2d103.73373499468003!3d1.50959129373446!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31da73d12ff16aa9%3A0x45bb0865198b1bc3!2sTokyo%20Japanese%20Cuisine%20%40%20Paradigm%20Mall%20JB!5e0!3m2!1sen!2smy!4v1762160553896!5m2!1sen!2smy"
                  allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
              </div>
              <div class="location-card-info">
                <h3>分店</h3>
                <p>地址：<a
                    href="https://www.google.com/maps/search/?api=1&query=Lot+UG-25,+Upper+Ground+Floor,+Paradigm+Mall,+Lbh+Skudai,+Taman+Bukit+Mewah,+81200+Johor+Bahru,+Johor+Darul+Ta'zim"
                    target="_blank" rel="noopener noreferrer">Lot UG-25, Upper Ground Floor, Paradigm Mall, Lbh Skudai,
                    Taman Bukit Mewah, 81200 Johor Bahru, Johor Darul Ta'zim</a></p>
                <p>电话：<a href="tel:+60187738090">+60 18-773 8090</a></p>
              </div>
            </div>
          </div>
        </div>
      </section>
      <!-- Footer Section -->
      <section id="footer" class="footer-section swiper-slide footer-scroll-buffer">
        <div class="footer-container">
          <div class="footer-content">

            <!-- Logo & Brand -->
            <div class="footer-brand">
              <div class="footer-logo">
                <img src="image/tokyologo.png" alt="Tokyo Japanese Cuisine"
                  style="height:40px; border-radius:50%; border:2px solid #c9a96e;">
                <div>
                  <div class="footer-logo-name">TOKYO JAPANESE</div>
                  <div class="footer-logo-sub">CUISINE</div>
                </div>
              </div>
              <p class="footer-tagline">正宗日式料理，匠心传递每一道风味</p>
            </div>

            <!-- Quick Links -->
            <div class="footer-links">
              <h4>快速导航</h4>
              <ul>
                <li><a href="#about-culture">文化 &amp; 服务</a></li>
                <li><a href="#mission-vision">使命愿景</a></li>
                <li><a href="#featured">特色推荐</a></li>
                <li><a href="#location">我们在这</a></li>
              </ul>
            </div>

            <!-- Contact -->
            <div class="footer-contact">
              <h4>联系我们</h4>
              <p>📍 总店：Mid Valley Southkey, JB</p>
              <p>📞 <a href="tel:+60197108090">+60 19-710 8090</a></p>
              <p>📍 分店：Paradigm Mall, JB</p>
              <p>📞 <a href="tel:+60187738090">+60 18-773 8090</a></p>
            </div>

            <!-- Hours -->
            <div class="footer-hours">
              <h4>营业时间</h4>
              <p>周一 – 周五：11:00 – 22:00</p>
              <p>周六 – 周日：10:00 – 22:30</p>
              <p>节假日照常营业</p>
            </div>

          </div>

          <div class="footer-bottom">
            <p>🌸 © 2026 Tokyo Japanese Cuisine. All Rights Reserved.</p>
          </div>
        </div>
      </section>

    </div><!-- /.swiper-wrapper -->

    <!-- Custom Sakura Scrollbar -->
    <div class="swiper-pagination"></div>
  </div><!-- /.swiper -->
  <!-- Swiper JS -->
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script>
    // --- Random Image Spotlight ---
    (function () {
      const pool = [
        { src: 'image/sushi-dish-asian-restaurant.jpg', label: '今日特选 · 精致寿司' },
        { src: 'image/vision.png', label: '今日特选 · 匠心料理' },
        { src: 'image/tokyologo.png', label: '今日特选 · 餐厅风貌' },
        { src: 'image/menu2.png', label: '今日特选 · 精选菜单' },
        { src: 'image/menu1.png', label: '今日特选 · 寿司菜单' },
        { src: 'image/tokyologo.png', label: '今日特选 · 主厨风采' }
      ];

      let current = Math.floor(Math.random() * pool.length);

      function setSpotlight(index) {
        const img = document.getElementById('random-spotlight-img');
        const label = document.getElementById('random-spotlight-label');
        if (!img || !label) return;
        img.style.opacity = '0';
        setTimeout(() => {
          img.src = pool[index].src;
          label.textContent = pool[index].label;
          img.style.transition = 'opacity 0.5s ease';
          img.style.opacity = '1';
        }, 300);
      }

      document.addEventListener('DOMContentLoaded', function () {
        setSpotlight(current);
        // Click main photo to cycle to next image
        const frame = document.getElementById('editorial-main-wrap') ||
          document.querySelector('.random-spotlight-frame');
        if (frame) {
          frame.addEventListener('click', function () {
            current = (current + 1) % pool.length;
            setSpotlight(current);
          });
        }
      });
    })();

    document.addEventListener('DOMContentLoaded', function () {


      // --- Sushi Menu Tabs Logic ---
      const sushiTabs = document.querySelectorAll('.sushi-menu-tab[data-sushi-category]');
      if (sushiTabs.length > 0) {
        const panels = document.querySelectorAll('.sushi-menu-panel');
        const categoryToPanel = {
          'sashimi': 'sushi-panel-sashimi',
          'sushi': 'sushi-panel-sushi',
          'hosomaki': 'sushi-panel-hosomaki',
          'ippin': 'sushi-panel-ippin',
          'temaki': 'sushi-panel-temaki',
          'special-roll': 'sushi-panel-special-roll',
          'sarada': 'sushi-panel-sarada'
        };

        sushiTabs.forEach(function (tab) {
          tab.addEventListener('click', function () {
            const cat = this.getAttribute('data-sushi-category');
            const panelId = categoryToPanel[cat];
            if (!panelId) return;

            sushiTabs.forEach(t => {
              t.classList.remove('active');
              t.setAttribute('aria-selected', 'false');
            });
            this.classList.add('active');
            this.setAttribute('aria-selected', 'true');

            panels.forEach(p => {
              p.classList.remove('active');
              p.hidden = true;
            });
            const panel = document.getElementById(panelId);
            if (panel) {
              panel.classList.add('active');
              panel.hidden = false;
            }
          });
        });
      }

      // --- Grand Menu Tabs Logic ---
      const grandTabs = document.querySelectorAll('.grand-tab');
      if (grandTabs.length > 0) {
        const grandPanels = document.querySelectorAll('.grand-menu-panel');
        const grandMap = {
          'zensai': 'grand-panel-zensai',
          'wanmono': 'grand-panel-wanmono',
          'agemono': 'grand-panel-agemono',
          'yakimono': 'grand-panel-yakimono',
          'ippin-ryori': 'grand-panel-ippin-ryori',
          'menrui': 'grand-panel-menrui',
          'curry-don': 'grand-panel-curry-don',
          'donburi': 'grand-panel-donburi',
          'set-meal': 'grand-panel-set-meal',
          'dessert': 'grand-panel-dessert',
          'beverage': 'grand-panel-beverage'
        };

        grandTabs.forEach(function (tab) {
          tab.addEventListener('click', function () {
            const cat = this.getAttribute('data-grand-category');
            const panelId = grandMap[cat];
            if (!panelId) return;

            grandTabs.forEach(t => {
              t.classList.remove('active');
              t.setAttribute('aria-selected', 'false');
            });
            this.classList.add('active');
            this.setAttribute('aria-selected', 'true');

            grandPanels.forEach(p => {
              p.classList.remove('active');
              p.hidden = true;
            });
            const panel = document.getElementById(panelId);
            if (panel) {
              panel.classList.add('active');
              panel.hidden = false;
            }
          });
        });
      }

      // --- Zoom Modal Logic ---
      const zoomModal = document.getElementById('zoom-modal');
      if (zoomModal) {
        const zoomModalImage = document.getElementById('zoom-modal-image');
        const zoomCloseBtn = document.querySelector('.zoom-modal-close');
        // Cover both sushi-menu-item images and haidilao card images
        const menuImages = document.querySelectorAll('.sushi-menu-item img, .haidilao-card .img-wrap img');

        function openZoom(imageSrc) {
          zoomModalImage.src = imageSrc;
          zoomModal.classList.remove('hidden');
          document.body.style.overflow = 'hidden';
        }

        function closeZoom() {
          zoomModal.classList.add('hidden');
          document.body.style.overflow = '';
        }

        menuImages.forEach(function (img) {
          img.style.cursor = 'pointer';
          img.addEventListener('click', function () {
            openZoom(this.src);
          });
        });

        if (zoomCloseBtn) {
          zoomCloseBtn.addEventListener('click', closeZoom);
        }

        zoomModal.addEventListener('click', function (e) {
          if (e.target === zoomModal) {
            closeZoom();
          }
        });

        document.addEventListener('keydown', function (e) {
          if (e.key === 'Escape' && !zoomModal.classList.contains('hidden')) {
            closeZoom();
          }
        });
      }

      // --- Scroll Animations (Intersection Observer) ---
      const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1
      };

      const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('active');
            observer.unobserve(entry.target); // Run animation once
          }
        });
      }, observerOptions);

      const revealElements = document.querySelectorAll('.reveal');
      revealElements.forEach(el => observer.observe(el));

      // --- Optimized Parallax Effect ---
      let ticking = false;
      window.addEventListener('scroll', function () {
        if (!ticking) {
          window.requestAnimationFrame(function () {
            const scrolled = window.scrollY; // more performant than pageYOffset
            const parallaxElements = document.querySelectorAll('.parallax');

            parallaxElements.forEach(el => {
              const speed = el.getAttribute('data-speed') || 0.5;
              // Use translate3d for hardware acceleration
              const yPos = -(scrolled * speed);
              el.style.transform = `translate3d(0, ${yPos}px, 0)`;
            });
            ticking = false;
          });
          ticking = true;
        }
      }, { passive: true }); // Passive listener for better scroll performance

      // --- Sakura Animation (Only on landing page, not on menu pages) ---
      if (!document.body.classList.contains('haidilao-menu-body')) {
        const sakuraContainer = document.createElement('div');
        sakuraContainer.classList.add('sakura-container');
        document.body.appendChild(sakuraContainer);

        const petalImages = [
          'image/petal1.png',
          'image/petal2.png',
          'image/petal3.png',
          'image/petal4.png',
          'image/petal5.png',
          'image/petal6.png',
          'image/petal7.png',
          'image/petal8.png'
        ];

        function createSakura() {
          // Limit max petals to avoid DOM overload
          if (sakuraContainer.childElementCount > 20) return;

          const sakura = document.createElement('img');
          // Randomly select a petal image
          const randomImage = petalImages[Math.floor(Math.random() * petalImages.length)];
          sakura.src = randomImage;
          sakura.classList.add('sakura');
          sakura.style.willChange = 'transform, top, left'; // Hint to browser

          // Randomize starting position and animation properties
          const startLeft = Math.random() * 100;
          const animationDuration = Math.random() * 3 + 6; // Slower: 6-9s
          const animationDelay = Math.random() * 2;
          const size = Math.random() * 15 + 10; // 10-25px

          sakura.style.left = startLeft + 'vw';
          sakura.style.animationDuration = animationDuration + 's';
          sakura.style.animationDelay = animationDelay + 's';
          sakura.style.width = size + 'px';
          sakura.style.height = size + 'px';

          sakuraContainer.appendChild(sakura);

          // Remove after animation to prevent memory leaks
          setTimeout(() => {
            sakura.remove();
          }, (animationDuration + animationDelay) * 1000);
        }

        // Reduce frequency: every 800ms instead of 300ms
        setInterval(createSakura, 800);
      }

      // --- Swipable Hero Carousel Logic ---
      // (Removed: hero section is now a single image)

      // --- Haidilao Menu Logic ---
      const haidilaoTopTabs = document.querySelectorAll('.haidilao-top-tab');
      const haidilaoSidebarCategories = document.querySelectorAll('.sidebar-category');
      const haidilaoSidebarTabs = document.querySelectorAll('.haidilao-sidebar-tab');
      const haidilaoContentPanels = document.querySelectorAll('.haidilao-panel');

      if (haidilaoTopTabs.length > 0) {
        haidilaoTopTabs.forEach(tab => {
          tab.addEventListener('click', function () {
            // Update active top tab
            haidilaoTopTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            const mainCategory = this.getAttribute('data-menu');

            // Show corresponding sidebar category
            haidilaoSidebarCategories.forEach(sidebar => {
              sidebar.style.display = 'none';
              if (sidebar.id === 'sidebar-' + mainCategory) {
                sidebar.style.display = 'flex';
                // Auto-click first tab in this sidebar
                const firstTab = sidebar.querySelector('.haidilao-sidebar-tab');
                if (firstTab) firstTab.click();
              }
            });
          });
        });

        // Initialize based on URL hash (e.g., menu#sushi or menu#grand)
        const hash = window.location.hash.replace('#', '');
        let targetTab;

        if (hash) {
          targetTab = document.querySelector(`.haidilao-top-tab[data-menu="${hash}"]`);
        }

        // Fallback to the default active tab if no hash or invalid hash
        if (!targetTab) {
          targetTab = document.querySelector('.haidilao-top-tab.active');
        }

        if (targetTab) {
          // Need a slight delay to ensure DOM is fully ready if transitioning pages
          setTimeout(() => targetTab.click(), 50);
        }

        haidilaoSidebarTabs.forEach(tab => {
          tab.addEventListener('click', function () {
            // Update active sidebar tab within its category
            const parentCategory = this.closest('.sidebar-category');
            parentCategory.querySelectorAll('.haidilao-sidebar-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            const targetPanelId = 'panel-' + this.getAttribute('data-target');

            // Show corresponding panel
            haidilaoContentPanels.forEach(panel => {
              panel.style.display = 'none';
              panel.classList.remove('active');
              if (panel.id === targetPanelId) {
                panel.style.display = 'block';
                setTimeout(() => panel.classList.add('active'), 10);
              }
            });
          });
        });
      }

      // --- Minimalist Mobile Sidebar Menu Logic ---
      const mobileMenuBtn = document.getElementById('mobile-menu-btn');
      const mobileSidebar = document.getElementById('mobile-sidebar');
      const mobileSidebarOverlay = document.getElementById('mobile-sidebar-overlay');
      const mobileSidebarClose = document.getElementById('mobile-sidebar-close');
      const sidebarLinks = document.querySelectorAll('.sidebar-link');

      if (mobileMenuBtn && mobileSidebar && mobileSidebarOverlay && mobileSidebarClose) {
        function openSidebar() {
          mobileSidebar.classList.add('active');
          mobileSidebarOverlay.classList.add('active');
          document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }

        function closeSidebar() {
          mobileSidebar.classList.remove('active');
          mobileSidebarOverlay.classList.remove('active');
          document.body.style.overflow = '';
        }

        mobileMenuBtn.addEventListener('click', openSidebar);
        mobileSidebarClose.addEventListener('click', closeSidebar);
        mobileSidebarOverlay.addEventListener('click', closeSidebar);

        sidebarLinks.forEach(link => {
          link.addEventListener('click', closeSidebar);
        });
      }

      // --- Centered Anchor Scroll ---
      // When clicking any internal nav link (#section), scroll so the section
      // is vertically centred in the viewport instead of top-aligned.
      document.querySelectorAll('a[href^="#"]').forEach(link => {
        link.addEventListener('click', function (e) {
          const hash = this.getAttribute('href');
          if (!hash || hash === '#') return;

          const target = document.querySelector(hash);
          if (!target) return;

          e.preventDefault();
          // Swiper anchor scrolling
          if (target && target.classList.contains('swiper-slide')) {
            const slides = Array.from(document.querySelectorAll('.swiper-slide'));
            const targetIndex = slides.indexOf(target);
            if (targetIndex !== -1) {
              if (typeof swiper !== 'undefined' && swiper.enabled) {
                swiper.slideTo(targetIndex);
              } else {
                target.scrollIntoView({ behavior: 'smooth' });
              }
            }
          } else {
            target.scrollIntoView({ behavior: 'smooth' });
          }
        });
      });

      // =========================================
      // SWIPER INIT (Frontend Reference style)
      // =========================================
      const swiper = new Swiper('.swiper', {
        direction: 'vertical',
        mousewheel: {
          enabled: true,
          releaseOnEdges: true,  // Allow scroll to continue to footer after last slide
        },
        speed: 800,
        simulateTouch: false,
        slidesPerView: 'auto', // Allows footer to be its natural height
        spaceBetween: 0,
        keyboard: {
          enabled: true,
        },
        observer: true,
        observeParents: true,
        pagination: {
          el: '.swiper-pagination',
          clickable: true,
          type: 'bullets',
        },
        breakpoints: {
          // Disable swiper on mobile (max-width: 767px)
          320: {
            enabled: false,
          },
          // Enable on desktop
          768: {
            enabled: true,
          }
        },
        on: {
          init: function () {
            // Hide pagination on initial load if on first slide
            const paginationEl = document.querySelector('.swiper-pagination');
            if (paginationEl) {
              if (this.activeIndex === 0) {
                paginationEl.style.opacity = '0';
                paginationEl.style.visibility = 'hidden';
              } else {
                paginationEl.style.opacity = '1';
                paginationEl.style.visibility = 'visible';
              }
            }
          },
          slideChange: function () {
            // Toggle pagination visibility based on slide index
            const paginationEl = document.querySelector('.swiper-pagination');
            if (paginationEl) {
              if (this.activeIndex === 0) {
                paginationEl.style.opacity = '0';
                paginationEl.style.visibility = 'hidden';
              } else {
                paginationEl.style.opacity = '1';
                paginationEl.style.visibility = 'visible';
              }
            }
          }
        }
      });

      // Fallback: Manually destroy/disable swiper if breakpoints fail
      function handleResize() {
        if (window.innerWidth <= 768 && swiper.enabled) {
          swiper.disable();
        } else if (window.innerWidth > 768 && !swiper.enabled) {
          swiper.enable();
        }
      }

      window.addEventListener('resize', handleResize);
      handleResize(); // Check on load

      // =========================================
      // CULTURE CAROUSEL LOGIC
      // =========================================
      (function () {
        'use strict';
        const track = document.getElementById('cc-track');
        if (!track) return;

        const rawDivs = Array.from(track.querySelectorAll('.cc-card'));
        const SLIDES = rawDivs.map(el => ({
          title: el.getAttribute('data-title'),
          desc: el.getAttribute('data-desc'),
          html: el.innerHTML,
          bg: el.style.background
        }));
        rawDivs.forEach(el => el.remove());

        const N = SLIDES.length;
        if (N === 0) return;
        const mod = i => ((i % N) + N) % N;

        const titleEl = document.getElementById('cc-title');
        const descEl = document.getElementById('cc-desc');
        const dotsEl = document.getElementById('cc-dots');
        const outerEl = document.querySelector('.cc-carousel-outer');
        const prevBtn = document.getElementById('cc-prev');
        const nextBtn = document.getElementById('cc-next');

        const cards = SLIDES.map(s => {
          const el = document.createElement('div');
          el.className = 'cc-card';
          if (s.bg) el.style.background = s.bg;
          el.innerHTML = s.html;
          track.appendChild(el);
          return el;
        });

        if (dotsEl) {
          dotsEl.innerHTML = '';
          SLIDES.forEach((_, i) => {
            const d = document.createElement('button');
            d.className = 'cc-dot';
            d.addEventListener('click', () => go(i));
            dotsEl.appendChild(d);
          });
        }
        const dots = dotsEl ? Array.from(dotsEl.querySelectorAll('.cc-dot')) : [];

        const SIDE_S = 0.82;
        const SIDE_O = 0.45;
        const SIDE_F = 0.82;

        let CW, CH, GAP, STEP, TRACK_W, TRACK_H;

        function measure() {
          CW = cards[0].offsetWidth || 340;
          CH = cards[0].offsetHeight || 480;
          GAP = 40; // match CSS --gap
          STEP = CW + GAP;
          TRACK_W = CW * 3 + GAP * 2;
          TRACK_H = CH;

          track.style.width = TRACK_W + 'px';
          track.style.height = CH + 'px';
          if (outerEl) outerEl.style.width = TRACK_W + 'px';

          cards.forEach(c => {
            c.style.width = CW + 'px';
            c.style.height = CH + 'px';
            c.style.top = '0';
          });
        }

        function slotX(slot) { return (slot + 1) * STEP; }

        const state = cards.map(() => ({ x: 0, scale: SIDE_S, opacity: 0, visible: false }));

        function writeCard(i) {
          const st = state[i];
          if (!st.visible) {
            cards[i].style.opacity = '0';
            cards[i].style.transform = `translate3d(-9999px,0,0)`;
            cards[i].style.pointerEvents = 'none';
            cards[i].classList.remove('active');
            return;
          }
          cards[i].style.pointerEvents = 'auto';
          cards[i].style.transform = `translate3d(${st.x}px,0,0) scale(${st.scale})`;
          cards[i].style.opacity = String(st.opacity);
          cards[i].style.filter = `brightness(${st.scale === 1 ? 1 : SIDE_F})`;

          if (st.scale === 1) {
            cards[i].classList.add('active');
          } else {
            cards[i].classList.remove('active');
          }
        }

        const TRANSITION = 'transform .42s cubic-bezier(.25,.46,.45,.94), opacity .42s ease, filter .42s ease, box-shadow .42s ease';
        function enableTransition(i) { cards[i].style.transition = TRANSITION; }
        function disableTransition(i) { cards[i].style.transition = 'none'; }

        let cur = 0;
        let animating = false;

        function layout(instantly) {
          const L = mod(cur - 1);
          const C = cur;
          const R = mod(cur + 1);

          cards.forEach((_, i) => {
            if (instantly) disableTransition(i);
            if (i === L) {
              state[i] = { x: slotX(-1), scale: SIDE_S, opacity: SIDE_O, visible: true };
            } else if (i === C) {
              state[i] = { x: slotX(0), scale: 1, opacity: 1, visible: true };
            } else if (i === R) {
              state[i] = { x: slotX(1), scale: SIDE_S, opacity: SIDE_O, visible: true };
            } else {
              state[i] = { x: 0, scale: SIDE_S, opacity: 0, visible: false };
            }
            writeCard(i);
          });
        }

        function syncDots(i) { dots.forEach((d, j) => d.classList.toggle('active', j === i)); }

        let textTimer;
        function syncText(i, instant) {
          clearTimeout(textTimer);
          const s = SLIDES[i];
          if (!titleEl || !descEl) return;

          if (instant) {
            titleEl.textContent = s.title;
            descEl.textContent = s.desc;
            titleEl.classList.remove('out');
            descEl.classList.remove('out');
          } else {
            titleEl.classList.add('out');
            descEl.classList.add('out');
            textTimer = setTimeout(() => {
              titleEl.textContent = s.title;
              descEl.textContent = s.desc;
              titleEl.classList.remove('out');
              descEl.classList.remove('out');
            }, 180);
          }
        }

        let autoPlayTimer = null;
        function startAutoPlay() {
          if (autoPlayTimer) clearInterval(autoPlayTimer);
          autoPlayTimer = setInterval(() => {
            if (!dragging && !animating) {
              go(mod(cur + 1));
            }
          }, 3000);
        }

        function go(next) {
          if (animating || next === cur) return;
          animating = true;
          startAutoPlay();

          const prev = cur;
          cur = mod(next);

          const fwd = mod(cur - prev);
          const dir = fwd <= N / 2 ? 1 : -1;

          const idxCenter = cur;
          const idxNewSide = mod(cur + dir);
          const idxOldSide = mod(prev - dir);
          const idxOldCenter = prev;

          disableTransition(idxCenter);
          disableTransition(idxNewSide);

          const stageOffX = dir > 0 ? slotX(2) : slotX(-2);

          state[idxCenter] = { x: stageOffX, scale: SIDE_S, opacity: SIDE_O, visible: true };
          state[idxNewSide] = { x: stageOffX, scale: SIDE_S, opacity: SIDE_O, visible: true };
          writeCard(idxCenter);
          writeCard(idxNewSide);

          syncText(cur, false);
          syncDots(cur);

          requestAnimationFrame(() => {
            requestAnimationFrame(() => {
              enableTransition(idxCenter);
              enableTransition(idxOldCenter);
              enableTransition(idxNewSide);
              enableTransition(idxOldSide);

              state[idxCenter] = { x: slotX(0), scale: 1, opacity: 1, visible: true };
              writeCard(idxCenter);

              state[idxOldCenter] = { x: slotX(-dir), scale: SIDE_S, opacity: SIDE_O, visible: true };
              writeCard(idxOldCenter);

              state[idxNewSide] = { x: slotX(dir), scale: SIDE_S, opacity: SIDE_O, visible: true };
              writeCard(idxNewSide);

              state[idxOldSide] = { x: slotX(-dir * 2), scale: SIDE_S, opacity: 0, visible: true };
              writeCard(idxOldSide);

              const onDone = () => {
                disableTransition(idxOldSide);
                state[idxOldSide].visible = false;
                writeCard(idxOldSide);
                animating = false;
              };

              cards[idxCenter].addEventListener('transitionend', function h(e) {
                if (e.propertyName !== 'transform') return;
                cards[idxCenter].removeEventListener('transitionend', h);
                onDone();
              });

              setTimeout(() => {
                if (animating) onDone();
              }, 450);
            });
          });
        }

        let dragging = false;
        let ptrX = 0;
        let dragBase = 0;
        let lastDelta = 0;
        let rafId = null;
        let snapX = {};

        function dragFrame() {
          if (!dragging) return;
          const delta = ptrX - dragBase;
          lastDelta = delta;

          const pull = Math.abs(delta);
          const d = pull > STEP
            ? Math.sign(delta) * (STEP + (pull - STEP) * 0.2)
            : delta;

          [mod(cur - 1), cur, mod(cur + 1)].forEach(i => {
            if (snapX[i] === undefined) return;
            const isCenter = (i === cur);
            cards[i].style.transform = `translate3d(${snapX[i] + d}px,0,0) scale(${isCenter ? 1 : SIDE_S})`;
          });

          const thresh = STEP * 0.28;
          if (delta < -thresh) highlightDrag(mod(cur + 1));
          else if (delta > thresh) highlightDrag(mod(cur - 1));
          else highlightDrag(cur);

          rafId = requestAnimationFrame(dragFrame);
        }

        let dragHighlight = -1;
        function highlightDrag(i) {
          if (dragHighlight === i) return;
          if (dragHighlight >= 0) {
            cards[dragHighlight].classList.remove('active');
            cards[dragHighlight].style.filter = `brightness(${SIDE_F})`;
          }
          dragHighlight = i;
          if (i === cur) {
            cards[i].classList.add('active');
          } else {
            cards[i].style.filter = 'brightness(1)';
          }
        }

        function onDown(e) {
          if (animating) return;
          dragging = true;
          lastDelta = 0;
          dragHighlight = -1;
          dragBase = e.clientX ?? e.touches[0].clientX;
          ptrX = dragBase;

          snapX = {};
          [mod(cur - 1), cur, mod(cur + 1)].forEach(i => {
            disableTransition(i);
            snapX[i] = state[i].x;
          });

          track.classList.add('grabbing');
          cancelAnimationFrame(rafId);
          rafId = requestAnimationFrame(dragFrame);
          if (autoPlayTimer) clearInterval(autoPlayTimer);
        }

        function onMove(e) {
          if (!dragging) return;
          ptrX = e.clientX ?? (e.touches ? e.touches[0].clientX : 0);
        }

        function onUp(e) {
          if (!dragging) return;
          dragging = false;
          cancelAnimationFrame(rafId);
          track.classList.remove('grabbing');

          [mod(cur - 1), cur, mod(cur + 1)].forEach(i => enableTransition(i));

          const cx = e.clientX ?? (e.changedTouches && e.changedTouches[0].clientX);

          if (Math.abs(lastDelta) < 5) {
            const el = document.elementFromPoint(cx, track.getBoundingClientRect().top + CH / 2);
            const card = el && el.closest('.cc-card');
            if (card) {
              const idx = cards.indexOf(card);
              if (idx !== -1 && idx !== cur) { go(idx); return; }
            }
            layout(false);
            startAutoPlay();
            return;
          }

          const thresh = STEP * 0.18;
          if (lastDelta < -thresh) go(mod(cur + 1));
          else if (lastDelta > thresh) go(mod(cur - 1));
          else { layout(false); startAutoPlay(); }
        }

        track.addEventListener('mousedown', onDown);
        window.addEventListener('mousemove', onMove);
        window.addEventListener('mouseup', onUp);
        track.addEventListener('touchstart', onDown, { passive: true });
        track.addEventListener('touchmove', e => { if (dragging) { e.preventDefault(); onMove(e); } }, { passive: false });
        track.addEventListener('touchend', onUp);

        if (prevBtn) prevBtn.addEventListener('click', () => { if (!animating) go(mod(cur - 1)); });
        if (nextBtn) nextBtn.addEventListener('click', () => { if (!animating) go(mod(cur + 1)); });
        document.addEventListener('keydown', e => {
          if (e.key === 'ArrowRight' && !animating) go(mod(cur + 1));
          if (e.key === 'ArrowLeft' && !animating) go(mod(cur - 1));
        });

        if (outerEl) {
          outerEl.addEventListener('mouseenter', () => clearInterval(autoPlayTimer));
          outerEl.addEventListener('mouseleave', startAutoPlay);
        }

        function init() {
          measure();
          layout(true);
          syncDots(cur);
          syncText(cur, true);
          startAutoPlay();
        }

        window.addEventListener('resize', () => { measure(); layout(true); });

        // Ensure DOM layout is ready before measuring
        setTimeout(init, 50);

      })();

      // =========================================
      // ZEN ABOUT SECTION LOGIC
      // =========================================
      // 滚动淡入
      const zenElements = document.querySelectorAll(
        ".zen-title span, .zen-desc, .zen-img"
      );

      const zenObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add("zen-active");
          }
        });
      }, { threshold: 0.2 });

      zenElements.forEach(el => zenObserver.observe(el));

      // 图片轻微视差
      window.addEventListener("scroll", function () {
        const scrollY = window.scrollY;
        const topImg = document.querySelector(".zen-img-top");
        const bottomImg = document.querySelector(".zen-img-bottom");

        if (topImg && bottomImg) {
          topImg.style.transform = `translateY(${scrollY * 0.05}px)`;
          bottomImg.style.transform = `translateY(${scrollY * 0.1}px)`;
        }
      });

      // 平滑滚动
      const scrollBtn = document.getElementById("zenScroll");
      if (scrollBtn) {
        scrollBtn.addEventListener("click", function () {
          // Integrate seamlessly with Swiper if it's active
          if (typeof swiper !== 'undefined' && swiper.enabled) {
            swiper.slideNext();
          } else {
            // Native fallback
            window.scrollTo({
              top: window.innerHeight,
              behavior: "smooth"
            });
          }
        });
      }
    });

  </script>
</body>

</html>
