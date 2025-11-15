@extends('layouts.app')
@section('title','Mô phỏng lý thuyết lái xe')

@push('styles')
<style>
  /* Reset cho trang simulation */
  .simulation-page {
    background: #f6f7fb;
    min-height: 100vh;
    padding-bottom: 20px;
  }

  .simulation-page .container {
    max-width: 1400px;
  }

  /* Banner header */
  .sim-banner {
    background: linear-gradient(135deg, #2563eb, #3b82f6);
    color: #fff;
    padding: 24px 20px;
    border-radius: 12px;
    margin: 20px auto;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
  }

  .sim-banner-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
  }

  .sim-banner-text {
    flex: 1;
  }

  .sim-banner-title {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 8px;
  }

  .sim-banner-subtitle {
    font-size: 14px;
    opacity: 0.95;
    line-height: 1.6;
  }

  /* Mode buttons */
  .sim-mode-buttons {
    display: flex;
    gap: 12px;
    flex-shrink: 0;
  }

  .sim-mode-btn {
    padding: 12px 24px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    background: rgba(255, 255, 255, 0.15);
    color: #fff;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-block;
  }

  .sim-mode-btn:hover {
    background: rgba(255, 255, 255, 0.25);
    border-color: rgba(255, 255, 255, 0.5);
    transform: translateY(-2px);
  }

  .sim-mode-btn.active {
    background: #fff;
    color: #2563eb;
    border-color: #fff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  }

  /* Main layout - 3 cột */
  .sim-main-layout {
    display: grid;
    grid-template-columns: 220px 1fr 260px;
    gap: 16px;
    margin: 0 auto;
    max-width: 1600px;
    padding: 0 20px;
  }

  /* Sidebar trái - Danh sách tình huống */
  .sim-sidebar-left {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    overflow: hidden;
    height: fit-content;
    max-height: calc(100vh - 240px);
    overflow-y: auto;
  }

  .sim-sidebar-title {
    padding: 16px;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
    font-weight: 600;
    font-size: 16px;
    color: #1f2937;
  }

  .sim-chapter {
    padding: 12px 16px;
  }

  .sim-chapter-header {
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
    font-size: 14px;
  }

  .sim-situation-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    margin: 4px 0;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    color: #4b5563;
    font-size: 14px;
    text-decoration: none;
  }

  .sim-situation-item:hover {
    background: #f3f4f6;
    color: #2563eb;
  }

  .sim-situation-item.active {
    background: #dbeafe;
    color: #2563eb;
    font-weight: 600;
  }

  .sim-situation-radio {
    width: 18px;
    height: 18px;
    border: 2px solid #9ca3af;
    border-radius: 50%;
    position: relative;
    flex-shrink: 0;
  }

  .sim-situation-item.active .sim-situation-radio {
    border-color: #2563eb;
  }

  .sim-situation-item.active .sim-situation-radio::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 10px;
    height: 10px;
    background: #2563eb;
    border-radius: 50%;
  }

  /* Video area - Center */
  .sim-video-area {
    background: #000;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    min-height: 0; /* Cho phép flex item co lại */
  }

  .sim-video-wrapper {
    position: relative;
    width: 100%;
    min-height: 400px;
    max-height: calc(100vh - 300px);
    aspect-ratio: 16 / 9;
    background: #000;
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 1;
  }

  .sim-video-wrapper video {
    width: 100%;
    height: 100%;
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
  }

  /* Video controls */
  .sim-video-controls {
    background: #1f2937;
    padding: 12px 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
  }

  .sim-control-btn {
    width: 40px;
    height: 40px;
    border: none;
    background: #374151;
    color: #fff;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    transition: all 0.2s;
    flex-shrink: 0;
  }

  .sim-control-btn:hover {
    background: #4b5563;
    transform: scale(1.05);
  }

  /* Nút bấm Space */
  .sim-space-btn {
    background: linear-gradient(135deg, #dc2626, #ef4444);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 10px 20px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(220, 38, 38, 0.3);
    flex-shrink: 0;
    min-width: 120px;
    justify-content: center;
  }

  .sim-space-btn:hover {
    background: linear-gradient(135deg, #b91c1c, #dc2626);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
  }

  .sim-space-btn:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(220, 38, 38, 0.3);
  }

  .sim-space-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
  }

  /* Progress bar với màu sắc */
  .sim-progress-container {
    flex: 1;
    position: relative;
    height: 8px;
    background: #374151;
    border-radius: 4px;
    overflow: visible;
    cursor: pointer;
  }

  .sim-progress-bar {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: 100%;
    display: flex;
  }

  .sim-progress-segment {
    height: 100%;
    transition: opacity 0.2s;
  }

  .sim-progress-segment.diem5 { background: #22c55e; }
  .sim-progress-segment.diem4 { background: #84cc16; }
  .sim-progress-segment.diem3 { background: #fbbf24; }
  .sim-progress-segment.diem2 { background: #f97316; }
  .sim-progress-segment.diem1 { background: #ef4444; }
  .sim-progress-segment.normal { background: #4b5563; }

  .sim-progress-cursor {
    position: absolute;
    top: 0;
    width: 3px;
    height: 100%;
    background: #fff;
    box-shadow: 0 0 4px rgba(255,255,255,0.8);
    z-index: 10;
    pointer-events: none;
    transition: left 0.1s linear;
  }

  /* Cờ đỏ tại vị trí bấm Space */
  .sim-flag-marker {
    position: absolute;
    top: -8px;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 0;
    border-left: 8px solid transparent;
    border-right: 8px solid transparent;
    border-top: 16px solid #dc2626;
    z-index: 15;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.2s;
  }

  .sim-flag-marker.show {
    opacity: 1;
  }

  .sim-flag-marker::after {
    content: '';
    position: absolute;
    top: -16px;
    left: -1px;
    width: 2px;
    height: 8px;
    background: #dc2626;
  }

  /* Marker cho các vị trí điểm */
  .sim-point-marker {
    position: absolute;
    top: -20px;
    transform: translateX(-50%);
    width: 4px;
    height: 20px;
    z-index: 12;
    pointer-events: none;
    display: none;
  }

  .sim-point-marker.show {
    display: block;
  }

  .sim-point-marker.diem5 {
    background: #22c55e;
    box-shadow: 0 0 4px rgba(34, 197, 94, 0.6);
  }

  .sim-point-marker.diem4 {
    background: #84cc16;
    box-shadow: 0 0 4px rgba(132, 204, 22, 0.6);
  }

  .sim-point-marker.diem3 {
    background: #fbbf24;
    box-shadow: 0 0 4px rgba(251, 191, 36, 0.6);
  }

  .sim-point-marker.diem2 {
    background: #f97316;
    box-shadow: 0 0 4px rgba(249, 115, 22, 0.6);
  }

  .sim-point-marker.diem1 {
    background: #ef4444;
    box-shadow: 0 0 4px rgba(239, 68, 68, 0.6);
  }

  /* Label cho marker điểm */
  .sim-point-marker-label {
    position: absolute;
    top: -35px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 11px;
    font-weight: 600;
    color: #fff;
    background: rgba(0, 0, 0, 0.7);
    padding: 2px 6px;
    border-radius: 4px;
    white-space: nowrap;
    pointer-events: none;
  }

  /* Progress bar riêng nằm dưới video (hiển thị kết quả) */
  .sim-result-progress-container {
    margin: 20px 0;
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  }

  .sim-result-progress-bar {
    position: relative;
    width: 100%;
    height: 16px;
    background: #e5e7eb;
    border-radius: 8px;
    overflow: visible;
    display: flex;
    border: 1px solid #d1d5db;
  }

  /* Con trỏ trên progress bar kết quả */
  .sim-result-progress-cursor {
    position: absolute;
    top: 0;
    width: 4px;
    height: 100%;
    background: #fff;
    box-shadow: 0 0 6px rgba(255,255,255,0.9), 0 0 12px rgba(255,255,255,0.6);
    z-index: 25;
    pointer-events: none;
    transition: left 0.1s linear;
    border-radius: 2px;
  }

  .sim-result-progress-segment {
    height: 100%;
    transition: opacity 0.2s;
  }

  .sim-result-progress-segment.diem5 { background: #22c55e; }
  .sim-result-progress-segment.diem4 { background: #84cc16; }
  .sim-result-progress-segment.diem3 { background: #fbbf24; }
  .sim-result-progress-segment.diem2 { background: #f97316; }
  .sim-result-progress-segment.diem1 { background: #ef4444; }
  .sim-result-progress-segment.normal { background: #9ca3af; }

  /* Cờ đỏ trên progress bar kết quả */
  .sim-result-flag-marker {
    position: absolute;
    top: -20px;
    left: 0%;
    transform: translateX(-50%);
    width: 0;
    height: 0;
    border-left: 10px solid transparent;
    border-right: 10px solid transparent;
    border-top: 24px solid #dc2626;
    z-index: 30;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.2s, left 0.1s;
    display: block;
  }

  .sim-result-flag-marker.show {
    opacity: 1;
    display: block;
  }

  .sim-result-flag-marker::after {
    content: '';
    position: absolute;
    top: -24px;
    left: -2px;
    width: 3px;
    height: 12px;
    background: #dc2626;
  }

  /* Marker điểm trên progress bar kết quả */
  .sim-result-point-marker {
    position: absolute;
    top: -28px;
    transform: translateX(-50%);
    width: 6px;
    height: 28px;
    z-index: 15;
    pointer-events: none;
    display: none;
  }

  .sim-result-point-marker.show {
    display: block;
  }

  .sim-result-point-marker.diem5 {
    background: #22c55e;
    box-shadow: 0 0 6px rgba(34, 197, 94, 0.8);
  }

  .sim-result-point-marker.diem4 {
    background: #84cc16;
    box-shadow: 0 0 6px rgba(132, 204, 22, 0.8);
  }

  .sim-result-point-marker.diem3 {
    background: #fbbf24;
    box-shadow: 0 0 6px rgba(251, 191, 36, 0.8);
  }

  .sim-result-point-marker.diem2 {
    background: #f97316;
    box-shadow: 0 0 6px rgba(249, 115, 22, 0.8);
  }

  .sim-result-point-marker.diem1 {
    background: #ef4444;
    box-shadow: 0 0 6px rgba(239, 68, 68, 0.8);
  }

  /* Label cho marker điểm trên progress bar kết quả */
  /* Markers cho các lần bấm Space */
  #spacePressMarkersContainer {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 16px;
    pointer-events: none;
    z-index: 10;
  }

  .sim-space-press-marker {
    position: absolute;
    top: -20px;
    transform: translateX(-50%);
    width: 3px;
    height: 36px;
    background: #3b82f6;
    border-radius: 2px;
    box-shadow: 0 0 4px rgba(59, 130, 246, 0.6);
    z-index: 15;
    transition: all 0.3s ease;
  }

  /* Hiệu ứng highlight cho marker có điểm trùng với điểm cuối */
  .sim-space-press-marker.highlight-match {
    background: #22c55e;
    width: 4px;
    height: 44px;
    box-shadow: 0 0 12px rgba(34, 197, 94, 0.8), 0 0 20px rgba(34, 197, 94, 0.4);
    animation: pulse-glow 2s ease-in-out infinite;
  }

  @keyframes pulse-glow {
    0%, 100% {
      box-shadow: 0 0 12px rgba(34, 197, 94, 0.8), 0 0 20px rgba(34, 197, 94, 0.4);
      transform: translateX(-50%) scale(1);
    }
    50% {
      box-shadow: 0 0 20px rgba(34, 197, 94, 1), 0 0 30px rgba(34, 197, 94, 0.6);
      transform: translateX(-50%) scale(1.1);
    }
  }

  .sim-space-press-label {
    position: absolute;
    top: -32px;
    left: 50%;
    transform: translateX(-50%);
    background: #3b82f6;
    color: #fff;
    font-size: 11px;
    font-weight: 600;
    padding: 2px 6px;
    border-radius: 4px;
    white-space: nowrap;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
  }

  /* Hiệu ứng cho label của marker có điểm trùng */
  .sim-space-press-label.score-match {
    background: #22c55e;
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    padding: 4px 8px;
    box-shadow: 0 4px 12px rgba(34, 197, 94, 0.6), 0 0 20px rgba(34, 197, 94, 0.3);
    animation: label-bounce 0.6s ease-in-out;
  }

  @keyframes label-bounce {
    0%, 100% {
      transform: translateX(-50%) scale(1);
    }
    50% {
      transform: translateX(-50%) scale(1.2);
    }
  }

  .sim-result-point-label {
    position: absolute;
    top: -48px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 13px;
    font-weight: 700;
    color: #fff;
    background: rgba(0, 0, 0, 0.85);
    padding: 4px 8px;
    border-radius: 6px;
    white-space: nowrap;
    pointer-events: none;
    min-width: 24px;
    text-align: center;
  }

  /* Highlight vùng đáp án đúng */
  .sim-progress-segment.correct-zone {
    position: relative;
    box-shadow: 0 0 12px rgba(34, 197, 94, 0.6);
    border: 2px solid #22c55e;
    z-index: 5;
  }

  .sim-progress-segment.correct-zone::before {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: #fff;
    font-size: 12px;
    font-weight: bold;
    z-index: 6;
  }

  /* Vòng tròn hiển thị đáp án đúng (theo Figma) */
  .sim-answer-circle {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 200px;
    height: 200px;
    border-radius: 50%;
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 100;
    box-shadow: 0 10px 40px rgba(37, 99, 235, 0.4);
    animation: answerCircleAppear 0.5s ease-out;
    pointer-events: none;
  }

  @keyframes answerCircleAppear {
    from {
      opacity: 0;
      transform: translate(-50%, -50%) scale(0.5);
    }
    to {
      opacity: 1;
      transform: translate(-50%, -50%) scale(1);
    }
  }

  .sim-answer-circle-inner {
    text-align: center;
    color: #fff;
  }

  .sim-answer-circle-number {
    font-size: 72px;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 8px;
  }

  .sim-answer-circle-label {
    font-size: 18px;
    font-weight: 500;
    opacity: 0.9;
  }

  @media (max-width: 768px) {
    .sim-answer-circle {
      width: 150px;
      height: 150px;
    }

    .sim-answer-circle-number {
      font-size: 54px;
    }

    .sim-answer-circle-label {
      font-size: 14px;
    }
  }

  .sim-progress-time {
    color: #fff;
    font-size: 13px;
    min-width: 80px;
    text-align: center;
    font-weight: 500;
  }

  /* Sidebar phải - Kết quả */
  .sim-sidebar-right {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    overflow: hidden;
    height: fit-content;
    max-height: calc(100vh - 240px);
    overflow-y: auto;
  }

  .sim-results-title {
    padding: 16px;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
    font-weight: 600;
    font-size: 16px;
    color: #1f2937;
  }

  .sim-results-content {
    padding: 20px;
  }

  .sim-result-item {
    margin-bottom: 20px;
  }

  .sim-result-label {
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 6px;
  }

  .sim-result-value {
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
  }

  .sim-result-value.score {
    color: #059669;
    font-size: 24px;
  }

  .sim-situation-description {
    margin-top: 20px;
  }

  .sim-description-label {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
  }

  .sim-description-text {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 12px;
    font-size: 14px;
    color: #4b5563;
    line-height: 1.6;
    min-height: 100px;
  }

  /* Instruction text */
  .sim-instruction-text {
    text-align: center;
    color: #dc2626;
    font-weight: 600;
    font-size: 15px;
    margin: 16px auto;
    padding: 12px 20px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;
    max-width: 1400px;
  }

  /* Responsive cho Desktop lớn */
  @media (min-width: 1920px) {
    .sim-main-layout {
      grid-template-columns: 240px 1fr 280px;
      max-width: 1800px;
    }
    
    .sim-video-wrapper {
      max-height: calc(100vh - 280px);
    }
  }

  /* Responsive cho Desktop vừa */
  @media (max-width: 1400px) {
    .sim-main-layout {
      grid-template-columns: 200px 1fr 240px;
      gap: 14px;
    }
    
    .sim-video-wrapper {
      max-height: calc(100vh - 280px);
    }
  }

  /* Responsive cho Tablet */
  @media (max-width: 1024px) {
    .sim-main-layout {
      grid-template-columns: 180px 1fr 220px;
      gap: 12px;
      padding: 0 12px;
    }
    
    .sim-video-wrapper {
      min-height: 350px;
      max-height: calc(100vh - 250px);
    }
  }

  /* Responsive cho Mobile */
  @media (max-width: 768px) {
    .sim-banner {
      margin: 12px 16px;
      padding: 16px;
    }

    .sim-banner-content {
      flex-direction: column;
      align-items: flex-start;
      gap: 16px;
    }

    .sim-mode-buttons {
      width: 100%;
      justify-content: space-between;
    }

    .sim-mode-btn {
      flex: 1;
      text-align: center;
      padding: 10px 16px;
      font-size: 14px;
    }

    .sim-banner-title {
      font-size: 18px;
    }

    .sim-banner-subtitle {
      font-size: 13px;
    }

    .sim-main-layout {
      grid-template-columns: 1fr;
      gap: 12px;
      padding: 0 12px;
    }

    .sim-sidebar-left,
    .sim-sidebar-right {
      max-height: 300px;
    }

    .sim-video-wrapper {
      aspect-ratio: 16 / 9;
    }

    .sim-video-controls {
      padding: 10px 12px;
      gap: 8px;
    }

    .sim-control-btn {
      width: 36px;
      height: 36px;
      font-size: 16px;
    }

    .sim-space-btn {
      min-width: 100%;
      padding: 12px 16px;
      font-size: 15px;
      margin-top: 4px;
      order: 5; /* Đưa nút Space xuống dưới cùng */
    }

    .sim-progress-container {
      order: 4; /* Progress bar trước nút Space */
      width: 100%;
      margin: 8px 0;
    }

    .sim-progress-time {
      order: 6; /* Thời gian sau nút Space */
      width: 100%;
      text-align: center;
      margin-top: 4px;
    }

    .sim-instruction-text {
      margin: 12px 16px;
      font-size: 13px;
      padding: 10px;
    }
  }
</style>
@endpush

@section('content')
<div class="simulation-page">
  {{-- Banner --}}
  <div class="sim-banner">
    <div class="sim-banner-content">
      <div class="sim-banner-text">
        <div class="sim-banner-title">Mô phỏng 120 câu cho các hạng B, C1, C, D, E</div>
        <div class="sim-banner-subtitle">
          Phần mềm thi thử 120 tình huống giao thông online được phát triển trên phần mềm offline do Tổng Cục Đường Bộ Việt Nam ban hành trước đó.
        </div>
      </div>
      <div class="sim-mode-buttons">
        <a 
          href="{{ route('simulation', array_filter(['mode' => 'practice', 'v' => $mainVideo->id ?? null])) }}" 
          class="sim-mode-btn {{ ($mode ?? 'practice') === 'practice' ? 'active' : '' }}"
        >
          📚 Ôn tập
        </a>
        <a 
          href="{{ route('simulation', array_filter(['mode' => 'test', 'v' => $mainVideo->id ?? null])) }}" 
          class="sim-mode-btn {{ ($mode ?? 'practice') === 'test' ? 'active' : '' }}"
        >
          ✏️ Thi thử
        </a>
      </div>
    </div>
  </div>

  {{-- Main Layout - 3 cột --}}
  <div class="sim-main-layout">
    {{-- Cột trái - Danh sách tình huống --}}
    <aside class="sim-sidebar-left">
      <div class="sim-sidebar-title">
        @if(($mode ?? 'practice') === 'test')
          Thi thử ({{ count($allVideos ?? []) }} câu)
        @else
          Nội dung
        @endif
      </div>
      <div class="sim-chapter">
        @if(($mode ?? 'practice') === 'test')
          <div class="sim-chapter-header">ĐỀ THI THỬ</div>
        @else
          <div class="sim-chapter-header">CHƯƠNG 1</div>
        @endif
        @foreach($allVideos ?? [] as $index => $video)
          <a 
            href="{{ route('simulation', ['v' => $video->id, 'mode' => $mode ?? 'practice']) }}"
            class="sim-situation-item {{ ($mainVideo && $video->id == $mainVideo->id) ? 'active' : '' }}"
            data-video-id="{{ $video->id }}"
            title="{{ $video->tieu_de ?? 'TH ' . ($video->stt ?? $video->id) }}"
          >
            <div class="sim-situation-radio"></div>
            <span>
              @if(($mode ?? 'practice') === 'test')
                Câu {{ $index + 1 }}
              @else
                TH{{ $video->stt ?? $video->id }}
              @endif
            </span>
          </a>
        @endforeach
      </div>
    </aside>

    {{-- Cột giữa - Video player --}}
    <main class="sim-video-area">
      @if($mainVideo)
        {{-- Vòng tròn hiển thị đáp án đúng (theo Figma) --}}
        <div class="sim-answer-circle" id="answerCircle" style="display: none;">
          <div class="sim-answer-circle-inner">
            <div class="sim-answer-circle-number" id="answerCircleNumber">5</div>
            <div class="sim-answer-circle-label">Điểm</div>
          </div>
        </div>
        
        <div class="sim-video-wrapper">
          <video 
            id="mainVideo" 
            controls
            preload="metadata"
            data-video-id="{{ $mainVideo->id }}"
            data-diem5="{{ $mainVideo->diem5 }}"
            data-diem4="{{ $mainVideo->diem4 }}"
            data-diem3="{{ $mainVideo->diem3 }}"
            data-diem2="{{ $mainVideo->diem2 }}"
            data-diem1="{{ $mainVideo->diem1 }}"
            data-diem1end="{{ $mainVideo->diem1end }}"
            data-duration="0"
          >
            <source src="{{ asset('videos/' . $mainVideo->video) }}" type="video/mp4">
            Trình duyệt của bạn không hỗ trợ video.
          </video>
        </div>

        {{-- Progress bar riêng nằm dưới video (hiển thị kết quả) --}}
        <div class="sim-result-progress-container" id="resultProgressContainer" style="display: none;">
          <div class="sim-result-progress-bar" id="resultProgressBar">
            <div class="sim-result-progress-cursor" id="resultProgressCursor"></div>
            <div class="sim-result-flag-marker" id="resultFlagMarker"></div>
            {{-- Container cho các markers của các lần bấm Space --}}
            <div id="spacePressMarkersContainer"></div>
          </div>
          {{-- Markers cho các vị trí điểm --}}
          <div class="sim-result-point-marker diem5" id="resultMarkerDiem5">
            <div class="sim-result-point-label">5</div>
          </div>
          <div class="sim-result-point-marker diem4" id="resultMarkerDiem4">
            <div class="sim-result-point-label">4</div>
          </div>
          <div class="sim-result-point-marker diem3" id="resultMarkerDiem3">
            <div class="sim-result-point-label">3</div>
          </div>
          <div class="sim-result-point-marker diem2" id="resultMarkerDiem2">
            <div class="sim-result-point-label">2</div>
          </div>
          <div class="sim-result-point-marker diem1" id="resultMarkerDiem1">
            <div class="sim-result-point-label">1</div>
          </div>
        </div>

        {{-- Video controls với progress bar --}}
        <div class="sim-video-controls">
          <button class="sim-control-btn" id="btnPrev" title="Tình huống trước">⏮</button>
          <button class="sim-control-btn" id="btnPlayPause" title="Phát/Tạm dừng">▶</button>
          <button class="sim-control-btn" id="btnRestart" title="Phát lại">↻</button>
          <button class="sim-control-btn" id="btnNext" title="Tình huống tiếp">⏭</button>
          
          <button class="sim-space-btn" id="btnSpace" title="Bấm Space để phát hiện tình huống nguy hiểm">
            <span>⏱</span>
            <span>Bấm Space</span>
          </button>
          
          <div class="sim-progress-container" id="progressContainer">
            <div class="sim-progress-bar" id="progressBar"></div>
            <div class="sim-progress-cursor" id="progressCursor"></div>
            <div class="sim-flag-marker" id="flagMarker"></div>
            {{-- Markers cho các vị trí điểm --}}
            <div class="sim-point-marker diem5" id="markerDiem5">
              <div class="sim-point-marker-label">5</div>
            </div>
            <div class="sim-point-marker diem4" id="markerDiem4">
              <div class="sim-point-marker-label">4</div>
            </div>
            <div class="sim-point-marker diem3" id="markerDiem3">
              <div class="sim-point-marker-label">3</div>
            </div>
            <div class="sim-point-marker diem2" id="markerDiem2">
              <div class="sim-point-marker-label">2</div>
            </div>
            <div class="sim-point-marker diem1" id="markerDiem1">
              <div class="sim-point-marker-label">1</div>
            </div>
          </div>

          <div class="sim-progress-time">
            <span id="currentTime">00:00</span> / <span id="totalTime">00:00</span>
          </div>
        </div>
      @else
        <div style="display:flex;align-items:center;justify-content:center;height:100%;color:#fff;padding:40px;">
          <p>Chưa có video mô phỏng nào</p>
        </div>
      @endif
    </main>

    {{-- Cột phải - Kết quả --}}
    <aside class="sim-sidebar-right">
      <div class="sim-results-title">Kết quả</div>
      <div class="sim-results-content">
        @if($mainVideo)
          @if(($mode ?? 'practice') === 'test')
            {{-- Kết quả thi thử --}}
            <div class="sim-result-item">
              <div class="sim-result-label">Câu hiện tại:</div>
              <div class="sim-result-value" id="currentQuestion">1/10</div>
            </div>
            <div class="sim-result-item">
              <div class="sim-result-label">Điểm câu này:</div>
              <div class="sim-result-value score" id="resultScore">-/5</div>
            </div>
            <div class="sim-result-item" style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #e5e7eb;">
              <div class="sim-result-label">Tổng điểm:</div>
              <div class="sim-result-value score" id="totalScore" style="font-size: 28px;">0/50</div>
            </div>
            <div class="sim-result-item">
              <div class="sim-result-label">Kết quả:</div>
              <div class="sim-result-value" id="finalResult" style="font-size: 18px; font-weight: 700;">-</div>
            </div>
            <div id="testResultsList" style="margin-top: 20px; display: none;">
              <div class="sim-description-label">Chi tiết từng câu:</div>
              <div class="sim-description-text" id="testResultsDetail" style="max-height: 200px; overflow-y: auto;"></div>
              <button 
                onclick="if(confirm('Bạn có chắc muốn bắt đầu lại thi thử?')) { localStorage.removeItem('simulation_test_results'); window.location.href='{{ route('simulation', ['mode' => 'test']) }}'; }"
                style="margin-top: 12px; width: 100%; padding: 10px; background: #2563eb; color: #fff; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;"
              >
                🔄 Bắt đầu lại
              </button>
            </div>
          @else
            {{-- Kết quả ôn tập --}}
            <div class="sim-result-item">
              <div class="sim-result-label">Số tình huống:</div>
              <div class="sim-result-value">1</div>
            </div>
            <div class="sim-result-item">
              <div class="sim-result-label">Điểm:</div>
              <div class="sim-result-value score" id="resultScore">-/5</div>
            </div>
            
            {{-- Kết quả chi tiết (hiển thị sau khi video kết thúc) --}}
            <div id="resultDetails" style="display: none; margin-top: 20px; padding-top: 20px; border-top: 2px solid #e5e7eb;">
              <div class="sim-result-item">
                <div class="sim-result-label">Đáp án đúng:</div>
                <div class="sim-result-value" id="correctAnswer" style="font-size: 14px; color: #059669; font-weight: 600;">-</div>
              </div>
              <div style="margin-top: 12px; font-size: 13px; color: #6b7280;">
                <div>🔴 Vị trí bạn đã bấm (cờ đỏ trên thanh tiến trình)</div>
                <div style="margin-top: 8px;">📍 Các vị trí điểm trên thanh tiến trình:</div>
                <div style="margin-top: 4px; padding-left: 12px;">
                  <span style="color: #22c55e;">●</span> 5 điểm | 
                  <span style="color: #84cc16;">●</span> 4 điểm | 
                  <span style="color: #fbbf24;">●</span> 3 điểm | 
                  <span style="color: #f97316;">●</span> 2 điểm | 
                  <span style="color: #ef4444;">●</span> 1 điểm
                </div>
              </div>
            </div>
            
            <div class="sim-situation-description">
              <div class="sim-description-label">Tình huống:</div>
              <div class="sim-description-text" id="situationDesc">
                @if($mainVideo->tieu_de)
                  <strong>{{ $mainVideo->tieu_de }}</strong>
                  @if($mainVideo->mo_ta_ngan)
                    <br><br>{{ $mainVideo->mo_ta_ngan }}
                  @endif
                @else
                  Tình huống {{ $mainVideo->stt ?? $mainVideo->id }} - {{ $mainVideo->video }}
                @endif
              </div>
            </div>
          @endif
        @endif
      </div>
    </aside>
  </div>

  {{-- Instruction text --}}
  <div class="sim-instruction-text">
    Học viên ấn phím SPACE khi phát hiện tình huống nguy hiểm
  </div>
</div>

@push('scripts')
<script>
(function() {
  const video = document.getElementById('mainVideo');
  if (!video) return;

  // Kiểm tra mode
  const isTestMode = {{ ($mode ?? 'practice') === 'test' ? 'true' : 'false' }};
  const allVideos = @json($allVideos ?? []);
  const currentVideoIndex = allVideos.findIndex(v => v.id == video.dataset.videoId);

  const videoId = video.dataset.videoId;
  const progressBar = document.getElementById('progressBar');
  const progressCursor = document.getElementById('progressCursor');
  const progressContainer = document.getElementById('progressContainer');
  const currentTimeEl = document.getElementById('currentTime');
  const totalTimeEl = document.getElementById('totalTime');
  const btnPlayPause = document.getElementById('btnPlayPause');
  const btnRestart = document.getElementById('btnRestart');
  const btnPrev = document.getElementById('btnPrev');
  const btnNext = document.getElementById('btnNext');
  const resultScore = document.getElementById('resultScore');
  const flagMarker = document.getElementById('flagMarker');
  
  // Elements cho test mode
  const currentQuestionEl = document.getElementById('currentQuestion');
  const totalScoreEl = document.getElementById('totalScore');
  const finalResultEl = document.getElementById('finalResult');
  const testResultsListEl = document.getElementById('testResultsList');
  const testResultsDetailEl = document.getElementById('testResultsDetail');

  // Lưu điểm thi thử vào localStorage
  const TEST_STORAGE_KEY = 'simulation_test_results';
  
  function getTestResults() {
    const stored = localStorage.getItem(TEST_STORAGE_KEY);
    return stored ? JSON.parse(stored) : {};
  }
  
  function saveTestResult(questionIndex, videoId, score) {
    const results = getTestResults();
    results[questionIndex] = { videoId, score, timestamp: Date.now() };
    localStorage.setItem(TEST_STORAGE_KEY, JSON.stringify(results));
    updateTestResults();
  }
  
  function clearTestResults() {
    localStorage.removeItem(TEST_STORAGE_KEY);
    updateTestResults();
  }
  
  function updateTestResults() {
    if (!isTestMode) return;
    
    const results = getTestResults();
    let totalScore = 0;
    let answeredCount = 0;
    
    // Tính tổng điểm
    for (let i = 0; i < allVideos.length; i++) {
      if (results[i]) {
        totalScore += results[i].score;
        answeredCount++;
      }
    }
    
    // Cập nhật UI
    if (totalScoreEl) {
      totalScoreEl.textContent = totalScore + '/50';
    }
    
    // Tính kết quả đậu/rớt
    if (finalResultEl) {
      if (answeredCount === allVideos.length) {
        // Đã trả lời hết
        if (totalScore >= 35) {
          finalResultEl.textContent = 'ĐẬU';
          finalResultEl.style.color = '#059669';
        } else {
          finalResultEl.textContent = 'RỚT';
          finalResultEl.style.color = '#dc2626';
        }
        
        // Hiển thị chi tiết
        if (testResultsListEl) testResultsListEl.style.display = 'block';
        if (testResultsDetailEl) {
          let detailHtml = '';
          for (let i = 0; i < allVideos.length; i++) {
            const result = results[i];
            const score = result ? result.score : 0;
            detailHtml += `Câu ${i + 1}: ${score}/5 điểm<br>`;
          }
          testResultsDetailEl.innerHTML = detailHtml;
        }
      } else {
        finalResultEl.textContent = '-';
        finalResultEl.style.color = '#6b7280';
      }
    }
    
    // Cập nhật câu hiện tại
    if (currentQuestionEl) {
      currentQuestionEl.textContent = (currentVideoIndex + 1) + '/' + allVideos.length;
    }
  }
  
  // Khởi tạo kết quả test
  if (isTestMode) {
    // Kiểm tra xem có phải bắt đầu thi thử mới không (không có video ID trong URL)
    const urlParams = new URLSearchParams(window.location.search);
    const hasVideoId = urlParams.has('v');
    
    // Nếu không có video ID, xóa kết quả cũ để bắt đầu mới
    if (!hasVideoId) {
      clearTestResults();
    }
    
    updateTestResults();
  }

  // Điểm trừ (DECIMAL(6,3) - số thập phân)
  const diem5 = parseFloat(video.dataset.diem5) || 0;
  const diem4 = parseFloat(video.dataset.diem4) || 0;
  const diem3 = parseFloat(video.dataset.diem3) || 0;
  const diem2 = parseFloat(video.dataset.diem2) || 0;
  const diem1 = parseFloat(video.dataset.diem1) || 0;
  const diem1end = parseFloat(video.dataset.diem1end) || 0;

  let totalDuration = 0;
  let currentScore = 0; // Bắt đầu từ 0, chỉ tăng khi bấm đúng vùng màu
  let hasPressedSpace = false; // Track xem đã bấm Space chưa (cho ôn tập)
  let spacePressTime = null; // Thời điểm bấm Space (cho ôn tập - chỉ 1 lần)
  let spacePressTimes = []; // Mảng lưu tất cả các thời điểm đã bấm Space (cho test mode)
  let spacePressData = []; // Mảng lưu {time, score} cho mỗi lần bấm Space

  // Load metadata để lấy duration
  video.addEventListener('loadedmetadata', function() {
    totalDuration = video.duration;
    video.dataset.duration = totalDuration;
    totalTimeEl.textContent = formatTime(totalDuration);
    buildProgressBar();
    
    // Ẩn progress bar kết quả khi load video mới (chỉ hiển thị sau khi bấm Space)
    const resultProgressContainer = document.getElementById('resultProgressContainer');
    if (resultProgressContainer) {
      resultProgressContainer.style.display = 'none';
    }
    
    // Ẩn cờ đỏ khi load video mới
    if (flagMarker) {
      flagMarker.classList.remove('show');
    }
    
    // Reset trạng thái bấm Space
    hasPressedSpace = false;
    spacePressTime = null;
    spacePressTimes = []; // Reset mảng các lần bấm Space
    spacePressData = []; // Reset mảng dữ liệu các lần bấm Space
    currentScore = 0;
    
    // Ẩn kết quả chi tiết
    const resultDetailsEl = document.getElementById('resultDetails');
    if (resultDetailsEl) {
      resultDetailsEl.style.display = 'none';
    }
    
    // Ẩn vòng tròn đáp án đúng
    const answerCircle = document.getElementById('answerCircle');
    if (answerCircle) {
      answerCircle.style.display = 'none';
    }
    
    // Xóa highlight vùng đáp án đúng
    if (progressBar) {
      progressBar.querySelectorAll('.sim-progress-segment').forEach(seg => {
        seg.classList.remove('correct-zone');
      });
    }
    
    // Cập nhật trạng thái nút Space
    if (btnSpace) {
      btnSpace.disabled = false;
    }
    
    // Trong test mode, tự động phát và kiểm tra đã trả lời chưa
    if (isTestMode) {
      const results = getTestResults();
      if (results[currentVideoIndex]) {
        // Đã trả lời câu này rồi, hiển thị điểm
        currentScore = results[currentVideoIndex].score;
        resultScore.textContent = currentScore + '/5';
      } else {
        // Chưa trả lời, reset điểm
        currentScore = 0;
        resultScore.textContent = '-/5';
      }
      // Tự động phát video
      video.play();
      btnPlayPause.textContent = '⏸';
    } else {
      // Chế độ ôn tập
      resultScore.textContent = '-/5';
    }
  });
  
  // Tìm vùng đáp án đúng (vùng điểm tốt nhất)
  function getCorrectAnswerZone() {
    const hasMarkers = diem5 > 0 || diem4 > 0 || diem3 > 0 || diem2 > 0 || diem1 > 0;
    if (!hasMarkers) {
      return null;
    }
    
    // Vùng đáp án đúng là vùng 5 điểm (từ diem5 đến diem4)
    if (diem5 > 0) {
      const endPoint = diem4 > 0 ? diem4 : (diem3 > 0 ? diem3 : (diem2 > 0 ? diem2 : (diem1 > 0 ? diem1 : (diem1end > 0 ? diem1end : totalDuration))));
      return {
        start: diem5,
        end: endPoint,
        score: 5,
        label: 'Từ ' + formatTime(diem5) + ' đến ' + formatTime(endPoint)
      };
    }
    
    // Nếu không có diem5, tìm vùng điểm cao nhất
    if (diem4 > 0) {
      const endPoint = diem3 > 0 ? diem3 : (diem2 > 0 ? diem2 : (diem1 > 0 ? diem1 : (diem1end > 0 ? diem1end : totalDuration)));
      return {
        start: diem4,
        end: endPoint,
        score: 4,
        label: 'Từ ' + formatTime(diem4) + ' đến ' + formatTime(endPoint)
      };
    }
    
    if (diem3 > 0) {
      const endPoint = diem2 > 0 ? diem2 : (diem1 > 0 ? diem1 : (diem1end > 0 ? diem1end : totalDuration));
      return {
        start: diem3,
        end: endPoint,
        score: 3,
        label: 'Từ ' + formatTime(diem3) + ' đến ' + formatTime(endPoint)
      };
    }
    
    if (diem2 > 0) {
      const endPoint = diem1 > 0 ? diem1 : (diem1end > 0 ? diem1end : totalDuration);
      return {
        start: diem2,
        end: endPoint,
        score: 2,
        label: 'Từ ' + formatTime(diem2) + ' đến ' + formatTime(endPoint)
      };
    }
    
    if (diem1 > 0 && diem1end > 0) {
      return {
        start: diem1,
        end: diem1end,
        score: 1,
        label: 'Từ ' + formatTime(diem1) + ' đến ' + formatTime(diem1end)
      };
    }
    
    return null;
  }

  // Highlight vùng đáp án đúng trên progress bar
  function highlightCorrectZone() {
    const correctZone = getCorrectAnswerZone();
    if (!correctZone || totalDuration === 0) return;
    
    const segments = progressBar.querySelectorAll('.sim-progress-segment');
    let accumulated = 0;
    
    segments.forEach(seg => {
      const width = parseFloat(seg.style.width);
      const startTime = (accumulated / 100) * totalDuration;
      const endTime = ((accumulated + width) / 100) * totalDuration;
      
      // Kiểm tra xem segment có nằm trong vùng đáp án đúng không
      if (startTime < correctZone.end && endTime > correctZone.start) {
        seg.classList.add('correct-zone');
      }
      
      accumulated += width;
    });
  }

  // Tính điểm khi video kết thúc (dựa trên vị trí cờ đỏ)
  video.addEventListener('ended', function() {
    const resultDetailsEl = document.getElementById('resultDetails');
    const correctAnswerEl = document.getElementById('correctAnswer');
    const answerCircle = document.getElementById('answerCircle');
    const answerCircleNumber = document.getElementById('answerCircleNumber');
    
    // Hiển thị kết quả chi tiết
    if (resultDetailsEl) {
      resultDetailsEl.style.display = 'block';
    }
    
    // Tìm đáp án đúng (vùng điểm tốt nhất)
    const correctZone = getCorrectAnswerZone();
    let correctAnswerScore = 5; // Mặc định là 5 điểm
    
    if (correctZone) {
      correctAnswerScore = correctZone.score;
    }
    
    // Tính điểm thực tế mà người dùng đạt được
    let finalScore = 0;
    let finalPressTime = null;
    
    if (hasPressedSpace && spacePressTime !== null) {
      if (isTestMode && spacePressData.length > 0) {
        // Trong test mode, lấy điểm cao nhất từ tất cả các lần bấm
        finalScore = Math.max(...spacePressData.map(d => d.score));
        const bestPress = spacePressData.find(d => d.score === finalScore) || spacePressData[0];
        finalPressTime = bestPress.time;
      } else {
        // Trong ôn tập, tính điểm từ lần bấm duy nhất
        finalScore = calculateScore(spacePressTime);
        finalPressTime = spacePressTime;
      }
      currentScore = finalScore;
      resultScore.textContent = finalScore + '/5';
      
      // Highlight đoạn tương ứng
      highlightSegment(finalPressTime);
    } else {
      // Chưa bấm Space hoặc không có cờ đỏ, điểm = 0
      currentScore = 0;
      resultScore.textContent = '0/5';
    }
    
    // Hiển thị vòng tròn với điểm thực tế mà người dùng đạt được (không phải điểm tốt nhất)
    if (answerCircle && answerCircleNumber) {
      answerCircleNumber.textContent = finalScore; // Hiển thị điểm thực tế
      answerCircle.style.display = 'flex';
      
      // Ẩn sau 3 giây
      setTimeout(() => {
        if (answerCircle) {
          answerCircle.style.display = 'none';
        }
      }, 3000);
    }
    
    // Hiển thị progress bar kết quả sau khi video kết thúc
    const resultProgressContainer = document.getElementById('resultProgressContainer');
    const resultProgressBar = document.getElementById('resultProgressBar');
    const resultProgressCursor = document.getElementById('resultProgressCursor');
    const resultFlagMarker = document.getElementById('resultFlagMarker');
    
    if (resultProgressContainer && resultProgressBar && totalDuration > 0) {
      // Build progress bar kết quả nếu chưa được build
      if (!resultProgressBar.querySelector('.sim-progress-segment')) {
        buildResultProgressBar(resultProgressBar);
      }
      
      // Hiển thị container
      resultProgressContainer.style.display = 'block';
      
      // Cập nhật con trỏ đến cuối
      if (resultProgressCursor) {
        resultProgressCursor.style.left = '100%';
      }
      
      // Hiển thị cờ đỏ tại vị trí đã bấm Space trên progress bar kết quả
      if (resultFlagMarker && finalPressTime !== null && totalDuration > 0) {
        const percent = (finalPressTime / totalDuration) * 100;
        resultFlagMarker.style.left = percent + '%';
        resultFlagMarker.classList.add('show');
      }
      
      // Hiển thị các marker điểm
      updateResultPointMarkers();
    }
    
    // Hiển thị các marker điểm trên progress bar trong controls (nếu cần)
    updatePointMarkers();
    const markers = ['markerDiem5', 'markerDiem4', 'markerDiem3', 'markerDiem2', 'markerDiem1'];
    markers.forEach(markerId => {
      const marker = document.getElementById(markerId);
      if (marker) {
        marker.classList.add('show');
      }
    });
    
    // Hiển thị tất cả các markers cho các lần bấm Space và highlight marker có điểm trùng
    updateSpacePressMarkers(finalScore);
    
    // Hiển thị đáp án đúng (chỉ hiển thị vùng điểm tốt nhất)
    if (correctAnswerEl) {
      if (correctZone) {
        correctAnswerEl.textContent = 'Vùng ' + correctZone.score + ' điểm (tốt nhất)';
      } else {
        correctAnswerEl.textContent = 'Chưa có đáp án';
        correctAnswerEl.style.color = '#6b7280';
      }
    }
    
    // Highlight vùng đáp án đúng trên progress bar
    highlightCorrectZone();
    
    // Trong test mode, lưu điểm và chuyển câu
    if (isTestMode) {
      if (currentVideoIndex >= 0) {
        saveTestResult(currentVideoIndex, videoId, currentScore);
      }
      
      if (currentVideoIndex < allVideos.length - 1) {
        // Chuyển câu tiếp theo sau 1 giây
        setTimeout(() => {
          const nextVideo = allVideos[currentVideoIndex + 1];
          if (nextVideo) {
            window.location.href = '{{ route("simulation") }}?mode=test&v=' + nextVideo.id;
          }
        }, 1000);
      } else {
        // Câu cuối cùng, hiển thị kết quả
        updateTestResults();
      }
    }
  });

  // Update thời gian
  video.addEventListener('timeupdate', function() {
    const current = video.currentTime;
    currentTimeEl.textContent = formatTime(current);
    
    // Update cursor trên progress bar controls
    if (totalDuration > 0) {
      const percent = (current / totalDuration) * 100;
      progressCursor.style.left = percent + '%';
      
      // Update cursor trên progress bar kết quả (chỉ nếu đã bấm Space và progress bar đang hiển thị)
      const resultProgressContainer = document.getElementById('resultProgressContainer');
      const resultProgressCursor = document.getElementById('resultProgressCursor');
      if (resultProgressContainer && resultProgressCursor && resultProgressContainer.style.display !== 'none') {
        resultProgressCursor.style.left = percent + '%';
      }
    }
  });

  // Build progress bar với màu sắc
  function buildProgressBar() {
    if (totalDuration === 0) return;
    
    progressBar.innerHTML = '';
    
    // Tạo mảng các điểm mốc thời gian
    const milestones = [];
    
    milestones.push({ time: 0, type: 'normal' });
    
    if (diem5 > 0 && diem5 < totalDuration) {
      milestones.push({ time: diem5, type: 'diem5-start' });
    }
    if (diem4 > 0 && diem4 < totalDuration) {
      milestones.push({ time: diem4, type: 'diem4-start' });
    }
    if (diem3 > 0 && diem3 < totalDuration) {
      milestones.push({ time: diem3, type: 'diem3-start' });
    }
    if (diem2 > 0 && diem2 < totalDuration) {
      milestones.push({ time: diem2, type: 'diem2-start' });
    }
    if (diem1 > 0 && diem1 < totalDuration) {
      milestones.push({ time: diem1, type: 'diem1-start' });
    }
    if (diem1end > 0 && diem1end < totalDuration) {
      milestones.push({ time: diem1end, type: 'normal' });
    }
    
    milestones.push({ time: totalDuration, type: 'normal' });
    
    // Sắp xếp theo thời gian
    milestones.sort((a, b) => a.time - b.time);
    
    // Loại bỏ các điểm trùng lặp
    const uniqueMilestones = [];
    let prevTime = -1;
    milestones.forEach(m => {
      if (m.time !== prevTime) {
        uniqueMilestones.push(m);
        prevTime = m.time;
      }
    });
    
    // Tạo các đoạn màu
    for (let i = 0; i < uniqueMilestones.length - 1; i++) {
      const start = uniqueMilestones[i].time;
      const end = uniqueMilestones[i + 1].time;
      const width = ((end - start) / totalDuration) * 100;
      
      if (width > 0) {
        const segment = document.createElement('div');
        
        // Xác định màu dựa trên khoảng thời gian
        let segmentType = 'normal';
        if (start >= diem1 && end <= diem1end) {
          segmentType = 'diem1'; // Đỏ
        } else if (start >= diem2 && (diem1 === 0 || end <= diem1)) {
          segmentType = 'diem2'; // Cam
        } else if (start >= diem3 && (diem2 === 0 || end <= diem2)) {
          segmentType = 'diem3'; // Vàng
        } else if (start >= diem4 && (diem3 === 0 || end <= diem3)) {
          segmentType = 'diem4'; // Vàng xanh
        } else if (start >= diem5 && (diem4 === 0 || end <= diem4)) {
          segmentType = 'diem5'; // Xanh lá
        }
        
        segment.className = `sim-progress-segment ${segmentType}`;
        segment.style.width = width + '%';
        segment.setAttribute('data-start', start);
        segment.setAttribute('data-end', end);
        progressBar.appendChild(segment);
      }
    }
    
    // Nếu không có điểm nào, tạo 1 đoạn normal
    if (progressBar.children.length === 0) {
      const segment = document.createElement('div');
      segment.className = 'sim-progress-segment normal';
      segment.style.width = '100%';
      progressBar.appendChild(segment);
    }
    
    // Hiển thị các marker điểm (chỉ khi video kết thúc)
    updatePointMarkers();
  }

  // Cập nhật vị trí các marker điểm
  function updatePointMarkers() {
    if (totalDuration === 0) return;
    
    const markers = {
      diem5: document.getElementById('markerDiem5'),
      diem4: document.getElementById('markerDiem4'),
      diem3: document.getElementById('markerDiem3'),
      diem2: document.getElementById('markerDiem2'),
      diem1: document.getElementById('markerDiem1')
    };
    
    const points = [
      { id: 'diem5', value: diem5 },
      { id: 'diem4', value: diem4 },
      { id: 'diem3', value: diem3 },
      { id: 'diem2', value: diem2 },
      { id: 'diem1', value: diem1 }
    ];
    
    points.forEach(point => {
      const marker = markers[point.id];
      if (marker && point.value > 0 && point.value < totalDuration) {
        const percent = (point.value / totalDuration) * 100;
        marker.style.left = percent + '%';
      }
    });
  }

  // Build progress bar kết quả (nằm dưới video)
  function buildResultProgressBar(progressBarEl) {
    if (totalDuration === 0) return;
    
    progressBarEl.innerHTML = '';
    
    // Tạo mảng các điểm mốc thời gian
    const milestones = [];
    
    milestones.push({ time: 0, type: 'normal' });
    
    if (diem5 > 0 && diem5 < totalDuration) {
      milestones.push({ time: diem5, type: 'diem5-start' });
    }
    if (diem4 > 0 && diem4 < totalDuration) {
      milestones.push({ time: diem4, type: 'diem4-start' });
    }
    if (diem3 > 0 && diem3 < totalDuration) {
      milestones.push({ time: diem3, type: 'diem3-start' });
    }
    if (diem2 > 0 && diem2 < totalDuration) {
      milestones.push({ time: diem2, type: 'diem2-start' });
    }
    if (diem1 > 0 && diem1 < totalDuration) {
      milestones.push({ time: diem1, type: 'diem1-start' });
    }
    if (diem1end > 0 && diem1end < totalDuration) {
      milestones.push({ time: diem1end, type: 'normal' });
    }
    
    milestones.push({ time: totalDuration, type: 'normal' });
    
    // Sắp xếp theo thời gian
    milestones.sort((a, b) => a.time - b.time);
    
    // Loại bỏ các điểm trùng lặp
    const uniqueMilestones = [];
    let prevTime = -1;
    milestones.forEach(m => {
      if (m.time !== prevTime) {
        uniqueMilestones.push(m);
        prevTime = m.time;
      }
    });
    
    // Tạo các đoạn màu
    for (let i = 0; i < uniqueMilestones.length - 1; i++) {
      const start = uniqueMilestones[i].time;
      const end = uniqueMilestones[i + 1].time;
      const width = ((end - start) / totalDuration) * 100;
      
      if (width > 0) {
        const segment = document.createElement('div');
        
        // Xác định màu dựa trên khoảng thời gian
        let segmentType = 'normal';
        if (start >= diem1 && end <= diem1end) {
          segmentType = 'diem1'; // Đỏ
        } else if (start >= diem2 && (diem1 === 0 || end <= diem1)) {
          segmentType = 'diem2'; // Cam
        } else if (start >= diem3 && (diem2 === 0 || end <= diem2)) {
          segmentType = 'diem3'; // Vàng
        } else if (start >= diem4 && (diem3 === 0 || end <= diem3)) {
          segmentType = 'diem4'; // Vàng xanh
        } else if (start >= diem5 && (diem4 === 0 || end <= diem4)) {
          segmentType = 'diem5'; // Xanh lá
        }
        
        segment.className = `sim-result-progress-segment ${segmentType}`;
        segment.style.width = width + '%';
        progressBarEl.appendChild(segment);
      }
    }
    
    // Nếu không có điểm nào, tạo 1 đoạn normal
    if (progressBarEl.children.length === 0) {
      const segment = document.createElement('div');
      segment.className = 'sim-result-progress-segment normal';
      segment.style.width = '100%';
      progressBarEl.appendChild(segment);
    }
  }

  // Cập nhật vị trí các marker điểm trên progress bar kết quả
  // Hiển thị markers cho tất cả các lần bấm Space
  function updateSpacePressMarkers(finalScore = null) {
    const container = document.getElementById('spacePressMarkersContainer');
    if (!container || totalDuration === 0 || spacePressData.length === 0) {
      if (container) container.innerHTML = '';
      return;
    }
    
    // Xóa các markers cũ
    container.innerHTML = '';
    
    // Tạo marker cho mỗi lần bấm Space
    spacePressData.forEach((pressData, index) => {
      const pressTime = pressData.time;
      const pressScore = pressData.score;
      const percent = (pressTime / totalDuration) * 100;
      
      const marker = document.createElement('div');
      marker.className = 'sim-space-press-marker';
      
      // Nếu điểm của lần bấm này trùng với điểm cuối cùng, thêm class highlight
      if (finalScore !== null && pressScore === finalScore) {
        marker.classList.add('highlight-match');
      }
      
      marker.style.left = percent + '%';
      marker.dataset.score = pressScore;
      marker.title = `Bấm Space lần ${index + 1} tại ${formatTime(pressTime)} - Điểm: ${pressScore}/5`;
      
      // Thêm số thứ tự
      const label = document.createElement('div');
      label.className = 'sim-space-press-label';
      label.textContent = index + 1;
      
      // Nếu điểm trùng, thêm hiệu ứng pulse
      if (finalScore !== null && pressScore === finalScore) {
        label.classList.add('score-match');
        label.textContent = `${index + 1} (${pressScore}/5)`;
      }
      
      marker.appendChild(label);
      
      container.appendChild(marker);
    });
  }

  function updateResultPointMarkers() {
    if (totalDuration === 0) return;
    
    const markers = {
      diem5: document.getElementById('resultMarkerDiem5'),
      diem4: document.getElementById('resultMarkerDiem4'),
      diem3: document.getElementById('resultMarkerDiem3'),
      diem2: document.getElementById('resultMarkerDiem2'),
      diem1: document.getElementById('resultMarkerDiem1')
    };
    
    const points = [
      { id: 'diem5', value: diem5 },
      { id: 'diem4', value: diem4 },
      { id: 'diem3', value: diem3 },
      { id: 'diem2', value: diem2 },
      { id: 'diem1', value: diem1 }
    ];
    
    points.forEach(point => {
      const marker = markers[point.id];
      if (marker && point.value > 0 && point.value < totalDuration) {
        const percent = (point.value / totalDuration) * 100;
        marker.style.left = percent + '%';
        marker.classList.add('show');
      }
    });
  }

  // Tính điểm dựa trên thời điểm nhấn Space
  function calculateScore(currentTime) {
    // Kiểm tra xem có điểm đánh dấu nào được cấu hình không
    const hasMarkers = diem5 > 0 || diem4 > 0 || diem3 > 0 || diem2 > 0 || diem1 > 0;
    
    // Nếu không có điểm đánh dấu nào, trả về 0
    if (!hasMarkers) {
      return 0;
    }
    
    // Chỉ tính điểm khi bấm đúng vào các vùng màu đã đánh dấu
    
    // Vùng 1 điểm: từ diem1 đến diem1end
    if (diem1 > 0 && diem1end > 0 && currentTime >= diem1 && currentTime <= diem1end) {
      return 1;
    }
    
    // Vùng 2 điểm: từ diem2 đến diem1 (nếu diem1 > 0) hoặc đến diem1end (nếu diem1 = 0)
    if (diem2 > 0) {
      const endPoint = diem1 > 0 ? diem1 : (diem1end > 0 ? diem1end : totalDuration);
      if (currentTime >= diem2 && currentTime < endPoint) {
        return 2;
      }
    }
    
    // Vùng 3 điểm: từ diem3 đến diem2 (nếu diem2 > 0) hoặc đến điểm tiếp theo
    if (diem3 > 0) {
      const endPoint = diem2 > 0 ? diem2 : (diem1 > 0 ? diem1 : (diem1end > 0 ? diem1end : totalDuration));
      if (currentTime >= diem3 && currentTime < endPoint) {
        return 3;
      }
    }
    
    // Vùng 4 điểm: từ diem4 đến diem3 (nếu diem3 > 0) hoặc đến điểm tiếp theo
    if (diem4 > 0) {
      const endPoint = diem3 > 0 ? diem3 : (diem2 > 0 ? diem2 : (diem1 > 0 ? diem1 : (diem1end > 0 ? diem1end : totalDuration)));
      if (currentTime >= diem4 && currentTime < endPoint) {
        return 4;
      }
    }
    
    // Vùng 5 điểm: từ diem5 đến diem4 (nếu diem4 > 0) hoặc đến điểm tiếp theo
    if (diem5 > 0) {
      const endPoint = diem4 > 0 ? diem4 : (diem3 > 0 ? diem3 : (diem2 > 0 ? diem2 : (diem1 > 0 ? diem1 : (diem1end > 0 ? diem1end : totalDuration))));
      if (currentTime >= diem5 && currentTime < endPoint) {
        return 5;
      }
    }
    
    // Nếu không rơi vào vùng nào đã đánh dấu, trả về 0
    return 0;
  }

  // Function xử lý bấm Space (dùng chung cho cả phím và nút)
  function handleSpacePress() {
    // Nếu đang pause, cho phép play
    if (video.paused) {
      video.play();
      btnPlayPause.textContent = '⏸';
      return;
    }
    
    // Chỉ xử lý khi video đang phát
    if (!video.paused) {
      // Trong chế độ ôn tập, chỉ cho phép bấm 1 lần
      if (!isTestMode && hasPressedSpace) {
        return; // Đã bấm rồi, không xử lý nữa
      }
      
      // Đánh dấu đã bấm Space
      hasPressedSpace = true;
      const currentTime = video.currentTime;
      spacePressTime = currentTime;
      
      // Tính điểm cho lần bấm này
      const score = calculateScore(currentTime);
      
      // Lưu vào mảng các lần bấm Space (cho test mode có thể bấm nhiều lần)
      if (isTestMode) {
        // Trong test mode, cho phép bấm nhiều lần
        spacePressTimes.push(currentTime);
        spacePressData.push({ time: currentTime, score: score });
      } else {
        // Trong ôn tập, chỉ lưu 1 lần
        spacePressTimes = [currentTime];
        spacePressData = [{ time: currentTime, score: score }];
      }
      
      // Vô hiệu hóa nút Space trong chế độ ôn tập (chỉ bấm 1 lần)
      if (!isTestMode && btnSpace) {
        btnSpace.disabled = true;
      }
      
      // Hiển thị cờ đỏ tại vị trí con trỏ trên progress bar controls
      if (flagMarker && totalDuration > 0) {
        const percent = (currentTime / totalDuration) * 100;
        flagMarker.style.left = percent + '%';
        flagMarker.classList.add('show');
      }
      
      // KHÔNG hiển thị progress bar kết quả khi bấm Space
      // Chỉ hiển thị sau khi video kết thúc
      
      // Trong test mode, tính điểm ngay khi bấm
      if (isTestMode) {
        const score = calculateScore(currentTime);
        if (score > currentScore) {
          currentScore = score;
          resultScore.textContent = score + '/5';
        }
        
        // Lưu điểm cho test mode (lưu điểm cao nhất)
        if (currentVideoIndex >= 0) {
          const results = getTestResults();
          const existingResult = results[currentVideoIndex];
          if (!existingResult || score > existingResult.score) {
            saveTestResult(currentVideoIndex, videoId, score);
          }
        }
        
        // Highlight đoạn tương ứng
        highlightSegment(currentTime);
      } else {
        // Trong chế độ ôn tập, chưa tính điểm, chỉ hiển thị cờ đỏ
        // Sẽ tính điểm khi video kết thúc
      }
    }
  }

  // Nhấn Space trên bàn phím
  document.addEventListener('keydown', function(e) {
    // Chỉ xử lý khi không phải đang focus vào input/textarea
    const target = e.target;
    const isInput = target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.isContentEditable;
    
    if (e.code === 'Space' && !isInput) {
      e.preventDefault();
      e.stopPropagation();
      handleSpacePress();
    }
  }, true); // Sử dụng capture phase để đảm bảo sự kiện được xử lý

  // Click nút "Bấm Space" trên giao diện
  if (btnSpace) {
    btnSpace.addEventListener('click', function() {
      handleSpacePress();
    });
  }

  // Highlight đoạn trên progress bar
  function highlightSegment(time) {
    const segments = progressBar.querySelectorAll('.sim-progress-segment');
    segments.forEach(seg => seg.style.opacity = '0.3');
    
    // Tìm và highlight đoạn chứa thời điểm này
    let accumulated = 0;
    segments.forEach(seg => {
      const width = parseFloat(seg.style.width);
      const startTime = (accumulated / 100) * totalDuration;
      const endTime = ((accumulated + width) / 100) * totalDuration;
      
      if (time >= startTime && time <= endTime) {
        seg.style.opacity = '1';
        seg.style.boxShadow = '0 0 8px rgba(255,255,255,0.6)';
      }
      
      accumulated += width;
    });
  }

  // Video controls
  btnPlayPause.addEventListener('click', function() {
    if (video.paused) {
      video.play();
      this.textContent = '⏸';
    } else {
      video.pause();
      this.textContent = '▶';
    }
  });

  btnRestart.addEventListener('click', function() {
    if (isTestMode) {
      // Trong test mode, xóa kết quả câu này và phát lại
      const results = getTestResults();
      delete results[currentVideoIndex];
      localStorage.setItem(TEST_STORAGE_KEY, JSON.stringify(results));
      updateTestResults();
    }
    
    video.currentTime = 0;
    currentScore = 0;
    resultScore.textContent = '-/5';
    
    // Reset trạng thái bấm Space
    hasPressedSpace = false;
    spacePressTime = null;
    spacePressTimes = [];
    spacePressData = [];
    
    // Ẩn kết quả chi tiết
    const resultDetailsEl = document.getElementById('resultDetails');
    if (resultDetailsEl) {
      resultDetailsEl.style.display = 'none';
    }
    
    // Ẩn vòng tròn đáp án đúng
    const answerCircle = document.getElementById('answerCircle');
    if (answerCircle) {
      answerCircle.style.display = 'none';
    }
    
    // Ẩn progress bar kết quả
    const resultProgressContainer = document.getElementById('resultProgressContainer');
    if (resultProgressContainer) {
      resultProgressContainer.style.display = 'none';
    }
    
    // Reset con trỏ trên progress bar kết quả
    const resultProgressCursor = document.getElementById('resultProgressCursor');
    if (resultProgressCursor) {
      resultProgressCursor.style.left = '0%';
    }
    
    // Ẩn cờ đỏ trên progress bar kết quả
    const resultFlagMarker = document.getElementById('resultFlagMarker');
    if (resultFlagMarker) {
      resultFlagMarker.classList.remove('show');
    }
    
    // Ẩn các marker điểm trên progress bar kết quả
    const resultMarkers = ['resultMarkerDiem5', 'resultMarkerDiem4', 'resultMarkerDiem3', 'resultMarkerDiem2', 'resultMarkerDiem1'];
    resultMarkers.forEach(markerId => {
      const marker = document.getElementById(markerId);
      if (marker) {
        marker.classList.remove('show');
      }
    });
    
    // Ẩn các marker điểm trên progress bar controls
    const markers = ['markerDiem5', 'markerDiem4', 'markerDiem3', 'markerDiem2', 'markerDiem1'];
    markers.forEach(markerId => {
      const marker = document.getElementById(markerId);
      if (marker) {
        marker.classList.remove('show');
      }
    });
    
    // Xóa highlight vùng đáp án đúng
    if (progressBar) {
      progressBar.querySelectorAll('.sim-progress-segment').forEach(seg => {
        seg.classList.remove('correct-zone');
      });
    }
    
    // Kích hoạt lại nút Space
    if (btnSpace) {
      btnSpace.disabled = false;
    }
    
    // Ẩn cờ đỏ
    if (flagMarker) {
      flagMarker.classList.remove('show');
    }
    
    progressBar.querySelectorAll('.sim-progress-segment').forEach(seg => {
      seg.style.opacity = '1';
      seg.style.boxShadow = 'none';
    });
    video.play();
    btnPlayPause.textContent = '⏸';
  });

  // Click vào progress bar để seek
  progressContainer.addEventListener('click', function(e) {
    const rect = progressContainer.getBoundingClientRect();
    const percent = (e.clientX - rect.left) / rect.width;
    video.currentTime = percent * totalDuration;
  });

  // Navigation
  btnPrev.addEventListener('click', function() {
    if (isTestMode && currentVideoIndex > 0) {
      const prevVideo = allVideos[currentVideoIndex - 1];
      if (prevVideo) {
        window.location.href = '{{ route("simulation") }}?mode=test&v=' + prevVideo.id;
      }
    } else {
      const currentItem = document.querySelector('.sim-situation-item.active');
      if (currentItem) {
        const prevItem = currentItem.previousElementSibling;
        if (prevItem && prevItem.classList.contains('sim-situation-item')) {
          const href = prevItem.getAttribute('href');
          if (href) window.location.href = href;
        }
      }
    }
  });

  btnNext.addEventListener('click', function() {
    if (isTestMode && currentVideoIndex < allVideos.length - 1) {
      const nextVideo = allVideos[currentVideoIndex + 1];
      if (nextVideo) {
        window.location.href = '{{ route("simulation") }}?mode=test&v=' + nextVideo.id;
      }
    } else {
      const currentItem = document.querySelector('.sim-situation-item.active');
      if (currentItem) {
        const nextItem = currentItem.nextElementSibling;
        if (nextItem && nextItem.classList.contains('sim-situation-item')) {
          const href = nextItem.getAttribute('href');
          if (href) window.location.href = href;
        }
      }
    }
  });

  // Format time
  function formatTime(seconds) {
    const m = Math.floor(seconds / 60);
    const s = Math.floor(seconds % 60);
    return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
  }
})();
</script>
@endpush
@endsection
