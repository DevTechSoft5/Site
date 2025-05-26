<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Responsive T-Shirt Designer</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.2.4/fabric.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.7.1/jszip.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

  <style>
    /* Root CSS Variables for consistent theming */
    :root {
      --primary-color: #6a5acd; /* Royal Purple */
      --secondary-color: #8a2be2; /* Blue Violet */
      --accent-color: #5d3fd3; /* Darker Purple */
      --bg-gradient-start: #667eea;
      --bg-gradient-end: #764ba2;
      --sidebar-bg: #ffffff;
      --canvas-bg: #f5f7fa;
      --text-color-dark: #333333;
      --text-color-light: #6c757d;
      --hover-bg: #f0f0f0;
      --active-bg: #e0e0e0;
      --border-color: #e0e0e0;
      --shadow-medium: 0 5px 15px rgba(0, 0, 0, 0.08);
      --shadow-light: 0 4px 12px rgba(0,0,0,0.1);
      --shadow-strong: 0 12px 40px rgba(0, 0, 0, 0.18);
      --border-radius-sm: 8px;
      --border-radius-md: 12px;
      --border-radius-lg: 18px;
    }

    /* Universal Box-Sizing and Tap Highlight Removal */
    * {
      box-sizing: border-box;
      -webkit-tap-highlight-color: transparent; /* Remove tap highlight on mobile */
    }

    /* Body Styling */
    body {
      font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
      margin: 0;
      padding: 0;
      display: flex;
      flex-direction: column; /* Default to column for mobile */
      height: 100vh;
      background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%);
      overflow: hidden; /* Prevent body scroll */
      color: var(--text-color-dark);
    }

    /* Main App Container Layout */
    #app-container {
      display: flex;
      flex: 1; /* Takes full available height */
      overflow: hidden; /* Prevent content scroll */
      position: relative; /* For bottom panel positioning */
    }

    /* Left Sidebar - Tools (Desktop View) */
    #sidebar {
      width: 250px;
      background: var(--sidebar-bg);
      padding: 25px 20px;
      box-shadow: var(--shadow-medium);
      display: flex;
      flex-direction: column;
      border-radius: 0 var(--border-radius-lg) var(--border-radius-lg) 0;
      gap: 15px;
      flex-shrink: 0;
    }

    .sidebar-heading {
      font-size: 1.1em;
      font-weight: 700;
      color: var(--primary-color);
      margin-bottom: 20px;
      text-align: center;
    }

    /* Common Button Styling (Desktop Sidebar / Mobile Bottom Nav) */
    .menu-btn {
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 16px;
      background: transparent;
      border: none;
      color: var(--text-color-dark);
      padding: 14px;
      width: 100%;
      text-align: left;
      border-radius: var(--border-radius-sm);
      cursor: pointer;
      transition: all 0.2s ease-in-out;
      font-weight: 500;
    }
    .menu-btn i {
      font-size: 1.25em;
      color: var(--primary-color);
    }
    .menu-btn:hover {
      background: var(--hover-bg);
      color: var(--accent-color);
      transform: translateY(-2px);
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }
    .menu-btn:active {
      transform: translateY(0);
      background: var(--active-bg);
    }
    .menu-btn:disabled {
      opacity: 0.6;
      cursor: not-allowed;
      background: transparent;
      color: var(--text-color-light);
      transform: none;
      box-shadow: none;
    }
    /* Visual cue for enabled delete button */
    .menu-btn#delete-btn-desktop:not(:disabled),
    .menu-btn-circle#delete-btn-mobile:not(:disabled) {
        background: #6a5acd; /* Royal Purple */
        color: white;
        box-shadow: 0 4px 12px rgba(106, 90, 205, 0.3); /* Purple shadow */
    }
    .menu-btn#delete-btn-desktop:not(:disabled):hover,
    .menu-btn-circle#delete-btn-mobile:not(:disabled):hover {
        background: var(--accent-color); /* Darker purple on hover */
        transform: translateY(-2px);
    }


    /* Canvas Container */
    #canvas-container {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      background: var(--canvas-bg);
      padding: 30px;
      overflow: hidden; /* Prevent canvas container scroll, use canvas itself for pan */
      position: relative; /* For canvas controls if any */
    }

    canvas {
      border-radius: var(--border-radius-lg);
      border: 2.5px solid var(--primary-color);
      box-shadow: var(--shadow-strong);
      max-width: 100%;
      height: auto;
      transition: all 0.3s ease-in-out;
    }

    /* Right Sidebar - Properties Panel (Desktop View) */
    #properties-panel {
      width: 280px;
      background: var(--sidebar-bg);
      padding: 25px 20px;
      box-shadow: var(--shadow-medium);
      border-radius: var(--border-radius-lg) 0 0 var(--border-radius-lg);
      display: flex;
      flex-direction: column;
      gap: 15px;
      flex-shrink: 0;
      transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out;
      transform: translateX(0); /* Default visible for desktop */
      opacity: 1;
    }

    #properties-panel.hidden {
      transform: translateX(100%);
      opacity: 0;
      width: 0;
      padding: 0;
      overflow: hidden;
    }

    .panel-section {
      border-bottom: 1px solid var(--border-color);
      padding-bottom: 15px;
      margin-bottom: 15px;
    }
    .panel-section:last-child {
      border-bottom: none;
      margin-bottom: 0;
      padding-bottom: 0;
    }

    .panel-section label {
      margin-top: 10px;
      font-weight: 600;
      font-size: 0.9em;
      display: block;
      color: var(--text-color-light);
      margin-bottom: 8px;
    }

    .panel-section input[type="text"],
    .panel-section input[type="number"],
    .panel-section input[type="color"],
    .panel-section select {
      width: 100%;
      padding: 10px 12px;
      font-size: 0.95em;
      border-radius: var(--border-radius-sm);
      border: 1.5px solid var(--border-color);
      margin-top: 5px;
      transition: border-color 0.2s, box-shadow 0.2s;
    }
    .panel-section input:focus,
    .panel-section select:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(106, 90, 205, 0.2);
      outline: none;
    }
    .panel-section input[type="color"] {
      height: 40px;
      padding: 2px;
    }
    .panel-section input[type="number"]::-webkit-inner-spin-button,
    .panel-section input[type="number"]::-webkit-outer-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }
    .panel-section input[type="number"] {
      -moz-appearance: textfield;
    }

    /* Modals (Desktop & Mobile) */
    .modal {
      display: none;
      position: fixed;
      z-index: 999;
      left: 0;
      top: 0;
      width: 100vw;
      height: 100vh;
      background: rgba(0, 0, 0, 0.4);
      justify-content: center;
      align-items: center;
      opacity: 0;
      transition: opacity 0.3s ease-in-out;
    }
    .modal.show {
      display: flex;
      opacity: 1;
    }

    .modal-content {
      background: white;
      padding: 25px 30px;
      border-radius: var(--border-radius-md);
      width: 100%;
      max-width: 450px;
      box-shadow: var(--shadow-strong);
      position: relative;
      transform: translateY(-20px);
      transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out;
      opacity: 0;
    }
    .modal.show .modal-content {
      transform: translateY(0);
      opacity: 1;
    }
    /* Specific for bottom-sliding properties panel modal on mobile */
    #properties-modal .modal-content {
        max-width: 100%;
        width: 100%;
        border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;
        position: absolute;
        bottom: 0;
        transform: translateY(100%);
        transition: transform 0.3s ease-out;
        opacity: 1; /* Always visible when active */
    }
    #properties-modal.show .modal-content {
        transform: translateY(0);
    }
    #properties-modal.show .modal-content h3 {
        padding-bottom: 10px;
        border-bottom: 1px solid var(--border-color);
        margin-bottom: 20px;
    }


    .modal-content h3 {
      margin-top: 0;
      margin-bottom: 20px;
      color: var(--primary-color);
      font-weight: 700;
      text-align: center;
    }

    .close-btn {
      position: absolute;
      right: 15px;
      top: 10px;
      font-size: 28px;
      cursor: pointer;
      color: var(--text-color-light);
      transition: color 0.2s;
    }
    .close-btn:hover {
      color: var(--text-color-dark);
    }
    /* Specific for properties modal close button */
    #properties-modal .close-btn {
        top: 20px;
        right: 20px;
    }

    #icons-container {
      margin-top: 15px;
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(48px, 1fr));
      gap: 12px;
      max-height: 400px;
      overflow-y: auto;
      padding-right: 8px;
    }
    #icons-container::-webkit-scrollbar {
      width: 8px;
    }
    #icons-container::-webkit-scrollbar-track {
      background: var(--hover-bg);
      border-radius: 10px;
    }
    #icons-container::-webkit-scrollbar-thumb {
      background: var(--primary-color);
      border-radius: 10px;
    }
    #icons-container::-webkit-scrollbar-thumb:hover {
      background: var(--accent-color);
    }


    .icon-btn {
      cursor: pointer;
      font-size: 28px;
      color: var(--primary-color);
      background: var(--hover-bg);
      padding: 10px;
      border-radius: var(--border-radius-sm);
      text-align: center;
      border: 2px solid transparent;
      transition: all 0.2s ease-in-out;
      display: flex;
      justify-content: center;
      align-items: center;
      aspect-ratio: 1 / 1;
    }
    .icon-btn:hover {
      border-color: var(--primary-color);
      background: var(--active-bg);
      transform: translateY(-2px);
    }
    .icon-btn:active {
      transform: translateY(0);
    }

    .modal-actions {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-top: 25px;
    }

    .modal-btn {
      padding: 12px 25px;
      font-size: 16px;
      font-weight: 600;
      border: none;
      border-radius: var(--border-radius-sm);
      cursor: pointer;
      transition: background 0.2s, transform 0.1s;
    }
    .modal-btn.primary {
      color: #fff;
      background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
      box-shadow: var(--shadow-light);
    }
    .modal-btn.primary:hover {
      background: linear-gradient(45deg, var(--accent-color), var(--primary-color));
      transform: translateY(-1px);
    }
    .modal-btn.secondary {
      background: var(--hover-bg);
      color: var(--text-color-dark);
      border: 1px solid var(--border-color);
    }
    .modal-btn.secondary:hover {
      background: var(--active-bg);
    }

    #image-upload-input {
      display: none;
    }
    .custom-file-upload {
      display: block;
      width: 100%;
      padding: 12px;
      text-align: center;
      background-color: var(--hover-bg);
      color: var(--text-color-dark);
      border: 1.5px dashed var(--primary-color);
      border-radius: var(--border-radius-sm);
      cursor: pointer;
      margin-top: 15px;
      transition: background-color 0.2s, border-color 0.2s;
      font-weight: 500;
    }
    .custom-file-upload:hover {
      background-color: var(--active-bg);
      border-color: var(--accent-color);
    }
    .custom-file-upload i {
      margin-right: 8px;
      color: var(--primary-color);
    }

    /* Fabric.js object controls styling */
    .canvas-container .upper-canvas {
        cursor: grab;
    }
    .canvas-container .upper-canvas.active {
        cursor: grab !important;
    }
    .canvas-container .upper-canvas.active .tl,
    .canvas-container .upper-canvas.active .tr,
    .canvas-container .upper-canvas.active .bl,
    .canvas-container .upper-canvas.active .br,
    .canvas-container .upper-canvas.active .ml,
    .canvas-container .upper-canvas.active .mr,
    .canvas-container .upper-canvas.active .mb,
    .canvas-container .upper-canvas.active .mt {
        border-radius: 50%;
        background-color: var(--primary-color) !important;
        opacity: 0.8;
        border: 1px solid white;
        box-shadow: 0 0 0 2px rgba(255,255,255,0.7);
    }

    .canvas-container .upper-canvas.active .mtr { /* Rotation control */
        background-color: var(--secondary-color) !important;
        opacity: 0.8;
        border: 1px solid white;
        box-shadow: 0 0 0 2px rgba(255,255,255,0.7);
    }

    /* --- RESPONSIVE STYLES --- */

    /* Default (Mobile First) */
    #sidebar, #properties-panel {
      display: none; /* Hide sidebars by default for mobile */
    }
    #bottom-nav {
      display: flex; /* Show bottom nav by default for mobile */
    }
    #app-container {
      flex-direction: column; /* Stack content vertically on mobile */
      height: calc(100vh - 70px); /* Adjust for bottom nav height */
    }
    .modal-content {
        margin: auto 15px; /* Add some side margin to modals on mobile */
    }
    /* Mobile Bottom Navigation Bar */
    #bottom-nav {
      position: fixed;
      bottom: 0;
      left: 0;
      width: 100%;
      background: var(--sidebar-bg);
      box-shadow: var(--shadow-medium);
      justify-content: space-around; /* Distribute buttons evenly */
      align-items: center;
      padding: 10px 0;
      z-index: 100; /* Ensure it's above other content */
      border-top-left-radius: var(--border-radius-md);
      border-top-right-radius: var(--border-radius-md);
    }

    #bottom-nav .menu-btn {
      flex-direction: column; /* Icon on top, text below */
      gap: 5px; /* Smaller gap */
      padding: 8px 5px; /* Smaller padding */
      font-size: 0.75em; /* Smaller font size for text */
      width: auto; /* Allow buttons to size based on content */
      min-width: 60px; /* Minimum width for touch target */
      text-align: center;
    }
    #bottom-nav .menu-btn i {
      font-size: 1.5em; /* Larger icons for visibility */
    }
    #bottom-nav .menu-btn:hover {
      transform: none; /* No lift effect on mobile for faster taps */
      box-shadow: none;
    }

    /* Desktop/Tablet View (min-width: 769px) */
    @media (min-width: 769px) {
      body {
        flex-direction: row; /* Layout horizontally for desktop */
      }

      #app-container {
        flex-direction: row; /* Layout content horizontally */
        height: 100vh; /* Full height */
      }

      /* Show Desktop Sidebar */
      #sidebar {
        display: flex;
      }

      /* Show Desktop Properties Panel */
      #properties-panel {
        display: flex;
      }

      /* Hide Mobile Bottom Navigation */
      #bottom-nav {
        display: none;
      }

      /* Ensure modals are centered and smaller on desktop */
      .modal-content {
          margin: auto;
          max-width: 450px;
      }
    }

    .menu-btn-circle {
      position: fixed;
      bottom: 100px;
      left: 40px;

      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: white;
      border: none;
      color: #7654ff;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      transition: background 0.3s ease;
    }

    .menu-btn-circle i {
      font-size: 1.75em;
      pointer-events: none;
    }

    .menu-btn-circle:hover:not(:disabled) {
      background: var(--accent-color);
    }

    .menu-btn-circle:disabled {
      background: rgba(255, 255, 255, 0.812);
      cursor: not-allowed;
    
      opacity: 0.6;
    }

    /* Hide .menu-btn-circle on desktop */
    @media (min-width: 768px) {
      .menu-btn-circle {
        display: none;
      }
    }

    /* Styles for navigation arrows */
    .nav-arrow {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      background: rgba(255, 255, 255, 0.7);
      border: none;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5em;
      color: var(--primary-color);
      cursor: pointer;
      z-index: 10;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
      transition: background 0.3s, transform 0.3s;
    }

    .nav-arrow:hover {
      background: rgba(255, 255, 255, 0.9);
      transform: translateY(-50%) scale(1.05);
    }

    #arrow-left {
      left: 10px;
    }

    #arrow-right {
      right: 10px;
    }

    /* Styles for zoom control */
    #zoom-controls {
      position: absolute;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      display: flex;
      gap: 10px;
      background: rgba(255, 255, 255, 0.8);
      padding: 10px 15px;
      border-radius: var(--border-radius-md);
      box-shadow: var(--shadow-medium);
      z-index: 10;
    }

    #zoom-controls button {
      background: var(--primary-color);
      color: white;
      border: none;
      border-radius: var(--border-radius-sm);
      padding: 8px 12px;
      font-size: 1em;
      cursor: pointer;
      transition: background 0.2s, transform 0.2s;
    }

    #zoom-controls button:hover {
      background: var(--accent-color);
      transform: translateY(-1px);
    }

    #zoom-controls span {
      display: flex;
      align-items: center;
      font-weight: 600;
      color: var(--text-color-dark);
    }

@media (max-width: 768px) {
  .canvas-container {
    width: 100% !important;
    height: calc(100vh - 140px) !important; /* adjust based on your header/nav */
    max-width: 100%;
    margin: 0 auto;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15); /* subtle outer shadow */
    background: #fff; /* optional clean background */
    border-radius: var(--border-radius-md); /* match your theme */
    overflow: hidden;
  }

  .canvas-container canvas {
    width: 100% !important;
    height: 98% !important;
    display: block;
  }
}



    @media (max-width: 768px) {

 
  #zoom-controls {
  display: none; /* Hide sidebars by default for mobile */
  }
}


    /* Progress bar styles */
    #generation-progress-container {
        width: 90%;
        margin: 20px auto;
        background-color: var(--border-color);
        border-radius: var(--border-radius-sm);
        height: 25px;
        overflow: hidden;
        position: relative;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
        display: none; /* Hidden by default */
    }

    #generation-progress-bar {
        height: 100%;
        width: 0%;
        background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        border-radius: var(--border-radius-sm);
        text-align: center;
        line-height: 25px;
        color: white;
        font-weight: bold;
        transition: width 0.3s ease-in-out;
    }

    #generation-progress-text {
        position: absolute;
        width: 100%;
        text-align: center;
        line-height: 25px;
        color: var(--text-color-dark);
        font-size: 0.9em;
        top: 0;
        left: 0;
    }

    /* Custom Message Box */
    .custom-message-box {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: #333;
        color: white;
        padding: 15px 25px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        z-index: 1000;
        font-size: 1.1em;
        opacity: 0;
        transition: opacity 0.3s ease-in-out;
        text-align: center;
    }
    .custom-message-box.show {
        opacity: 1;
    }

    /* Styles for T-shirt color circles */
    .color-circle {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: 2px solid var(--border-color);
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }
    .color-circle:hover {
        transform: scale(1.1);
        box-shadow: 0 0 8px rgba(0, 0, 0, 0.2);
    }
    .color-circle.active {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px var(--primary-color), 0 0 10px rgba(0, 0, 0, 0.3);
        transform: scale(1.15);
    }
    .color-circle.active::after {
        content: '\2713'; /* Checkmark */
        font-size: 1.2em;
        color: white; /* Or a contrasting color */
        text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
    }
    /* For white color circle, make checkmark dark */
    .color-circle[data-color-name="white"].active::after {
        color: var(--text-color-dark);
    }
  </style>
</head>
<body>
  <div id="app-container">
    <div id="sidebar">
      <div class="sidebar-heading">Tshirt Customized - Purchase</div>
      <button class="menu-btn" id="add-text-btn-desktop"><i class="bi bi-fonts"></i> Add Text</button>
      <button class="menu-btn" data-modal="iconModal" id="add-icon-btn-desktop"><i class="bi bi-stars"></i> Add Graphic</button>
      <button class="menu-btn" data-modal="imageUploadModal" id="upload-image-btn-desktop"><i class="bi bi-image"></i> Upload Image</button>
      <hr style="border: none; border-top: 1px solid var(--border-color); margin: 15px 0;">
      <button class="menu-btn" id="doodle-btn-desktop"><i class="bi bi-pencil-fill"></i> Doodle</button>
      <button class="menu-btn" id="arrow-tool-btn-desktop"><i class="bi-cursor-fill"></i> Select/Move</button>
      <hr style="border: none; border-top: 1px solid var(--border-color); margin: 15px 0;">
      <button class="menu-btn" id="delete-btn-desktop" disabled><i class="bi bi-trash"></i> Delete Selected</button>
      <button class="menu-btn" id="buy-now-btn"><i class="bi bi-cart-check"></i> Buy Now</button>
      
    </div>
    <div id="canvas-container" ondragover="allowDrop(event)" ondrop="dropImage(event)">
      <canvas id="canvas" width="500" height="600"></canvas>
      <button id="arrow-left" class="nav-arrow"><i class="bi bi-chevron-left"></i></button>
      <button id="arrow-right" class="nav-arrow"><i class="bi bi-chevron-right"></i></button>
      <div id="zoom-controls">
        <button id="zoom-in-btn"><i class="bi bi-zoom-in"></i></button>
        <button id="zoom-out-btn"><i class="bi bi-zoom-out"></i></button>
        <button id="reset-zoom-btn"><i class="bi bi-arrow-repeat"></i> Reset Zoom</button>
      </div>
    </div>

    <div id="properties-panel">
      <h3>Properties</h3>
      <div id="no-selection-message-desktop" style="text-align: center; color: var(--text-color-light); font-style: italic; margin-top: 50px;">
        Select an element on the canvas to edit its properties.
      </div>

      <div id="text-properties" class="panel-section" style="display: none;">
        <label for="font-select">Font Family</label>
        <select id="font-select">
          <option value="Arial">Arial</option>
          <option value="Helvetica">Helvetica</option>
          <option value="Verdana">Verdana</option>
          <option value="Times New Roman">Times New Roman</option>
          <option value="Georgia">Georgia</option>
          <option value="Courier New">Courier New</option>
          <option value="Comic Sans MS">Comic Sans MS</option>
          <option value="Impact">Impact</option>
        </select>

        <label for="font-size">Font Size</label>
        <input id="font-size" type="number" value="40" min="10" max="200" />

        <label for="text-color-picker">Text Color</label>
        <input id="text-color-picker" type="color" value="#000000" />
      </div>

      <div id="object-properties" class="panel-section" style="display: none;">
          <label for="object-color-picker">Color</label>
          <input id="object-color-picker" type="color" value="#000000" />
          <p style="font-size: 0.85em; color: var(--text-color-light); margin-top: 10px;">
            (Color applies to icons and SVG images. Raster images cannot be recolored.)
          </p>
      </div>

      <div id="drawing-properties" class="panel-section" style="display: none;">
        <label for="brush-color-picker">Brush Color</label>
        <input id="brush-color-picker" type="color" value="#000000" />

        <label for="brush-thickness">Brush Thickness</label>
        <input id="brush-thickness" type="number" value="5" min="1" max="50" />
      </div>
    </div>
  </div>

  <div id="bottom-nav">
    <button class="menu-btn" id="add-text-btn-mobile"><i class="bi bi-fonts"></i> Text</button>
    <button class="menu-btn" data-modal="iconModal" id="add-icon-btn-mobile"><i class="bi bi-stars"></i> Graphic</button>
    <button class="menu-btn" data-modal="imageUploadModal" id="upload-image-btn-mobile"><i class="bi bi-image"></i> Upload</button>
    <button class="menu-btn" id="doodle-btn-mobile"><i class="bi bi-pencil-fill"></i> Doodle</button>
    <button class="menu-btn" id="arrow-tool-btn-mobile"><i class="bi-cursor-fill"></i> Select</button>
    <button class="menu-btn" data-modal="colorModal" id="show-colors-btn-mobile"><i class="bi bi-palette-fill"></i> Colors</button> <button class="menu-btn" id="show-properties-btn-mobile"><i class="bi bi-sliders"></i> Properties</button> <button class="menu-btn" id="buy-now-btn-mobile"><i class="bi bi-cart-check"></i> Buy Now</button>
  </div>

  <div class="modal" id="iconModal">
    <div class="modal-content">
      <span class="close-btn" data-close="iconModal">&times;</span>
      <h3>Select an Icon or Enter URL</h3>
      <div id="icons-container"></div>
      <div style="margin-top: 20px; border-top: 1px solid var(--border-color); padding-top: 20px;">
        <label for="icon-url-input" style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9em; color: var(--text-color-light);">Or Add from URL:</label>
        <input type="text" id="icon-url-input" placeholder="Enter image URL (PNG, JPG, SVG)" style="width: 100%; padding: 10px 12px; font-size: 0.95em; border-radius: var(--border-radius-sm); border: 1.5px solid var(--border-color); transition: border-color 0.2s, box-shadow 0.2s;">
        <button class="modal-btn primary" id="add-icon-from-url-btn" style="margin-top: 15px; width: 100%;">Add Icon from URL</button>
      </div>
      <div class="modal-actions">
        <button class="modal-btn secondary" data-close="iconModal">Close</button>
      </div>
    </div>
  </div>

  <div class="modal" id="imageUploadModal">
    <div class="modal-content">
      <span class="close-btn" data-close="imageUploadModal">&times;</span>
      <h3>Upload Image</h3>
      <label for="image-upload-input" class="custom-file-upload">
        <i class="bi bi-upload"></i> Choose an Image File
      </label>
      <input type="file" id="image-upload-input" accept="image/*" />
      <div class="modal-actions">
        <button class="modal-btn secondary" data-close="imageUploadModal">Cancel</button>
      </div>
    </div>
  </div>

  <div class="modal" id="properties-modal">
    <div class="modal-content">
      <span class="close-btn" data-close="properties-modal">&times;</span>
      <h3>Properties</h3>
      <div id="properties-modal-content">
        <div id="no-selection-message-mobile" style="text-align: center; color: var(--text-color-light); font-style: italic; margin-top: 50px;">
          Select an element on the canvas to edit its properties.
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="colorModal">
      <div class="modal-content">
          <span class="close-btn" data-close="colorModal">&times;</span>
          <h3>Select T-shirt Color</h3>
          <div id="color-options-container-modal" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 15px; margin-top: 20px;">
              </div>
          <div class="modal-actions" style="justify-content: center; margin-top: 25px;">
              <button class="modal-btn secondary" data-close="colorModal">Close</button>
          </div>
      </div>
  </div>


  <div class="modal" id="buyNowModal">
    <div class="modal-content">
      <span class="close-btn" data-close="buyNowModal">&times;</span>
      <h3>Your Custom T-Shirts</h3>
      <div id="generation-progress-container">
        <div id="generation-progress-bar"></div>
        <div id="generation-progress-text">Generating images... 0%</div>
      </div>
      <div id="custom-tshirts-preview" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 20px; max-height: 70vh; overflow-y: auto; padding: 10px;">
        </div>
      <div class="modal-actions" style="justify-content: center;">
        <button class="modal-btn primary" id="download-all-btn"><i class="bi bi-download"></i> Download All (Zip)</button>
        <button class="modal-btn secondary" data-close="buyNowModal">Close</button>
      </div>
    </div>
  </div>

<button class="menu-btn-circle" id="delete-btn-mobile" >
  <i class="bi bi-trash"></i>
</button>

<script>
// Initialize Fabric.js Canvas
const canvas = new fabric.Canvas('canvas', {
    preserveObjectStacking: true,
    backgroundColor: 'transparent',
    uniScaleTransform: true, // Allow uniform scaling without shift key
    centeredScaling: true, // Scale from center
});

// --- T-shirt Image Sides and Colors ---
// Define T-shirt colors and their base paths for images (1.png, 2.png, 3.png, 4.png)
const tshirtColors = [
    { name: 'maroon', hex: '#800000', basePath: 'https://localhost/PRIVATE/maroon/' },
    { name: 'brown', hex: '#A52A2A', basePath: 'https://localhost/PRIVATE/brown/' },
    { name: 'midgreen', hex: '#6B8E23', basePath: 'https://localhost/PRIVATE/midgreen/' },
    { name: 'darkblue', hex: '#00008B', basePath: 'https://localhost/PRIVATE/darkblue/' },
    { name: 'orange', hex: '#FFA500', basePath: 'https://localhost/PRIVATE/orange/' },
    { name: 'darkgreen', hex: '#006400', basePath: 'https://localhost/PRIVATE/darkgreen/' },
    { name: 'red', hex: '#FF0000', basePath: 'https://localhost/PRIVATE/red/' },
    { name: 'darkpink', hex: '#E75480', basePath: 'https://localhost/PRIVATE/darkpink/' },
    { name: 'white', hex: '#FFFFFF', basePath: 'https://localhost/PRIVATE/white/' },
    { name: 'darkpurple', hex: '#301934', basePath: 'https://localhost/PRIVATE/darkpurple/' },
    { name: 'lightblue', hex: '#ADD8E6', basePath: 'https://localhost/PRIVATE/lightblue/' },
    { name: 'lightgreen', hex: '#90EE90', basePath: 'https://localhost/PRIVATE/lightgreen/' },
    { name: 'lightpink', hex: '#FFB6C1', basePath: 'https://localhost/PRIVATE/lightpink/' },
    { name: 'lightpurple', hex: '#B19CD9', basePath: 'https://localhost/PRIVATE/lightpurple/' },
    // Original default images as a color option for initial load - Make sure this is also correctly served or uses proper CORS
    { name: 'default', hex: '#CCCCCC', basePath: 'https://raw.githubusercontent.com/DevTechSoft5/Site/refs/heads/main/' }
];
let currentTshirtIndex = 0; // Represents the current side (0-3 for 1.png to 4.png)
let tshirtObject = null; // To store the Fabric.js T-shirt image object
let selectedTshirtColor = tshirtColors[tshirtColors.length - 1]; // Default to the 'default' color (grey)

// --- Data structure to store canvas states for each T-shirt side ---
// Each element in this array will store the Fabric.js JSON of the objects on that side
// Initialize with empty arrays for each of the 4 sides.
const canvasStates = Array(4).fill(null).map(() => []);

// Function to save the current canvas state (excluding the T-shirt background)
function saveCanvasState() {
    const objectsToSave = canvas.getObjects().filter(obj => obj.id !== 'tshirtBackground');
    canvasStates[currentTshirtIndex] = objectsToSave.map(obj => obj.toJSON());
    console.log(`Canvas state saved for side ${currentTshirtIndex}:`, canvasStates[currentTshirtIndex]);
}

// Function to load a canvas state from a given index
function loadCanvasState(index) {
    const objectsToRemove = canvas.getObjects().filter(obj => obj.id !== 'tshirtBackground');
    objectsToRemove.forEach(obj => canvas.remove(obj));
    canvas.discardActiveObject();
    
    loadTshirtImage(() => { // Load the T-shirt image for the current side and selected color
        const savedObjects = canvasStates[index];
        console.log(`Loading state for side ${index}:`, savedObjects);
        if (savedObjects && savedObjects.length > 0) {
            fabric.util.enlivenObjects(savedObjects, function(objects) {
                objects.forEach(function(obj) {
                    canvas.add(obj);
                });
                canvas.renderAll();
                console.log(`Objects loaded for side ${index}.`);
            }, { crossOrigin: 'anonymous' }); // Ensure crossOrigin for enlivenObjects too if SVGs/images are in saved objects
        } else {
            console.log(`No objects to load for side ${index}.`);
        }
        canvas.renderAll();
    });
}

// --- Load T-shirt image as a Fabric.js object on initialization ---
// This function now uses the `selectedTshirtColor` and `currentTshirtIndex` to determine the image URL.
function loadTshirtImage(callback) {
    const imageUrl = `${selectedTshirtColor.basePath}${currentTshirtIndex + 1}.png`;
    console.log(`Loading T-shirt image: ${imageUrl}`);
    if (tshirtObject) {
        canvas.remove(tshirtObject);
    }
    fabric.Image.fromURL(imageUrl, function(img) {
        img.scaleToWidth(canvas.width);
        if (img.getScaledHeight() > canvas.height) {
            img.scaleToHeight(canvas.height);
        }

        img.set({
            left: (canvas.width - img.getScaledWidth()) / 2,
            top: (canvas.height - img.getScaledHeight()) / 2,
            selectable: false,
            evented: false,
            excludeFromExport: false,
            id: 'tshirtBackground'
        });
        canvas.add(img);
        canvas.sendToBack(img);
        tshirtObject = img;
        canvas.renderAll();
        console.log(`T-shirt image loaded and added.`);
        if (callback) callback();
    }, { crossOrigin: 'anonymous' }); // Important for loading images from other domains due to CORS
}

// --- Custom Message Box Function ---
function showCustomMessageBox(message, type = 'info', duration = 3000) {
    const messageBox = document.createElement('div');
    messageBox.className = 'custom-message-box';
    messageBox.textContent = message;

    // Apply type-specific styling
    if (type === 'success') {
        messageBox.style.backgroundColor = '#4CAF50'; // Green
    } else if (type === 'error') {
        messageBox.style.backgroundColor = '#f44336'; // Red
    } else if (type === 'warning') {
        messageBox.style.backgroundColor = '#ff9800'; // Orange
    } else {
        messageBox.style.backgroundColor = '#333'; // Default dark
    }

    document.body.appendChild(messageBox);

    // Trigger reflow to enable transition
    void messageBox.offsetWidth; 
    messageBox.classList.add('show');

    setTimeout(() => {
        messageBox.classList.remove('show');
        messageBox.addEventListener('transitionend', () => {
            if (messageBox.parentNode) {
                messageBox.parentNode.removeChild(messageBox);
            }
        }, { once: true });
    }, duration);
}


window.onload = () => {
    loadTshirtImage(); // Call without arguments
    renderColorOptions(); // Render color options on load
    updatePropertiesPanel(); // Initial update of panels/modals
};

// --- Button References (both desktop and mobile) ---
const addTextBtnDesktop = document.getElementById('add-text-btn-desktop');
const addTextBtnMobile = document.getElementById('add-text-btn-mobile');
const deleteBtnDesktop = document.getElementById('delete-btn-desktop');
const deleteBtnMobile = document.getElementById('delete-btn-mobile');
const buyNowBtn = document.getElementById('buy-now-btn');
const buyNowBtnMobile = document.getElementById('buy-now-btn-mobile');

// Event listener for mobile "Buy Now" button to trigger desktop one
if (buyNowBtnMobile) {
    buyNowBtnMobile.addEventListener('click', async () => { buyNowBtn.click(); });
}

const arrowLeft = document.getElementById('arrow-left');
const arrowRight = document.getElementById('arrow-right');
const zoomInBtn = document.getElementById('zoom-in-btn');
const zoomOutBtn = document.getElementById('zoom-out-btn');
const resetZoomBtn = document.getElementById('reset-zoom-btn');

const doodleBtnDesktop = document.getElementById('doodle-btn-desktop');
const arrowToolBtnDesktop = document.getElementById('arrow-tool-btn-desktop'); // Renamed
const doodleBtnMobile = document.getElementById('doodle-btn-mobile');
const arrowToolBtnMobile = document.getElementById('arrow-tool-btn-mobile'); // Renamed
const showPropertiesBtnMobile = document.getElementById('show-properties-btn-mobile'); // NEW: Mobile Properties Button
const showColorsBtnMobile = document.getElementById('show-colors-btn-mobile'); // NEW: Mobile Colors Button

const propertiesPanelDesktop = document.getElementById('properties-panel');
const propertiesModal = document.getElementById('properties-modal');
const propertiesModalContent = document.getElementById('properties-modal-content');

const textPropertiesPanel = document.getElementById('text-properties');
const objectPropertiesPanel = document.getElementById('object-properties');
const noSelectionMessageDesktop = document.getElementById('no-selection-message-desktop');
const noSelectionMessageMobile = document.getElementById('no-selection-message-mobile');

const fontSelect = document.getElementById('font-select');
const fontSizeInput = document.getElementById('font-size');
const textColorPicker = document.getElementById('text-color-picker');
const objectColorPicker = document.getElementById('object-color-picker');

const drawingPropertiesPanel = document.getElementById('drawing-properties');
const brushColorPicker = document.getElementById('brush-color-picker');
const brushThicknessInput = document.getElementById('brush-thickness');

const iconsContainer = document.getElementById('icons-container');
const imageUploadInput = document.getElementById('image-upload-input');

const buyNowModal = document.getElementById('buyNowModal');
const customTshirtsPreview = document.getElementById('custom-tshirts-preview');
const downloadAllBtn = document.getElementById('download-all-btn');
const generationProgressContainer = document.getElementById('generation-progress-container');
const generationProgressBar = document.getElementById('generation-progress-bar');
const generationProgressText = document.getElementById('generation-progress-text');

// New elements for URL input for icons
const iconUrlInput = document.getElementById('icon-url-input');
const addIconFromUrlBtn = document.getElementById('add-icon-from-url-btn');

// References to new T-shirt color elements
const colorOptionsContainerModal = document.getElementById('color-options-container-modal'); // For the color modal


// --- Bootstrap Icons Data (subset for demo) ---
const bootstrapIcons = [
    "facebook", "twitter", "instagram", "linkedin", "youtube", "tiktok", "whatsapp",
    "telegram", "snapchat", "reddit", "github", "stack-overflow", "pinterest", "discord",
    "envelope", "envelope-fill", "chat", "chat-dots", "chat-dots-fill", "chat-fill", "chat-left", "chat-left-dots",
    "chat-left-dots-fill", "chat-left-fill", "chat-left-quote", "chat-left-quote-fill",
    "chat-left-text", "chat-left-text-fill", "chat-quote", "chat-quote-fill", "chat-right",
    "chat-right-dots", "chat-right-dots-fill", "chat-right-fill", "chat-right-quote",
    "chat-right-quote-fill", "chat-right-text", "chat-right-text-fill", "chat-square",
    "chat-square-dots", "chat-square-dots-fill", "chat-square-fill", "chat-square-quote",
    "chat-square-quote-fill", "dribbble", "behance", "medium", "rss", "person", "person-fill"
];

bootstrapIcons.forEach(iconName => {
    const iconBtn = document.createElement('i');
    iconBtn.className = `bi bi-${iconName} icon-btn`;
    iconBtn.title = iconName.replace(/-/g, ' ').replace(/\b\w/g, char => char.toUpperCase());
    iconBtn.setAttribute('data-icon', iconName);
    iconsContainer.appendChild(iconBtn);
});

// --- Modal Control Functions ---
function showModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.add('show');
        console.log(`Modal ${id} shown.`);
    }
}

function hideModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.remove('show');
        console.log(`Modal ${id} hidden.`);
    }
}

// Event listeners for modal-triggering buttons in bottom nav for other modals (icon, upload, color, properties)
document.querySelectorAll('.menu-btn[data-modal]').forEach(btn => {
    btn.addEventListener('click', () => {
        disableAllModes(); // Disable drawing/selection modes
        hideModal('properties-modal'); // Hide properties modal if open
        showModal(btn.getAttribute('data-modal')); // Show specific modal
    });
});

// Event listener for mobile "Show Properties" button
if (showPropertiesBtnMobile) {
    showPropertiesBtnMobile.addEventListener('click', () => {
        disableAllModes();
        updatePropertiesPanel(); // Ensure panel content is updated before showing
        showModal('properties-modal');
    });
}


// Close buttons for all modals
document.querySelectorAll('.close-btn').forEach(closeBtn => {
    closeBtn.addEventListener('click', () => { hideModal(closeBtn.getAttribute('data-close')); });
});

// Close modal when clicking outside content area
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', (e) => {
        if (e.target === modal) hideModal(modal.id);
    });
});

// --- Canvas Object Handling ---
function addTextToCanvas() {
    disableAllModes(); 
    const defaultText = "Your Text Here";
    const textbox = new fabric.Textbox(defaultText, {
        left: canvas.width / 2 - 100,
        top: canvas.height / 2 - 20,
        fontFamily: fontSelect.value,
        fontSize: parseInt(fontSizeInput.value, 10),
        fill: textColorPicker.value,
        editable: true,
        objectCaching: false,
        cornerColor: 'var(--primary-color)',
        borderColor: 'var(--primary-color)',
        cornerSize: 10,
        transparentCorners: false,
        lockUniScaling: false,
        minWidth: 30,
        minHeight: 20,
        width: 200,
    });
    canvas.add(textbox).setActiveObject(textbox);
    canvas.requestRenderAll();
    saveCanvasState();
    textbox.enterEditing();
    textbox.setSelectionStart(0);
    textbox.setSelectionEnd(defaultText.length);
}

// Event Listeners for Add Text Buttons
if (addTextBtnDesktop) addTextBtnDesktop.addEventListener('click', addTextToCanvas);
if (addTextBtnMobile) addTextBtnMobile.addEventListener('click', addTextToCanvas);


iconsContainer.addEventListener('click', (e) => {
    const target = e.target;
    if (target.classList.contains('icon-btn')) {
        addBootstrapIcon(target.getAttribute('data-icon'));
        hideModal('iconModal');
    }
});

async function addBootstrapIcon(iconName) {
    try {
        const res = await fetch(`https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/icons/${iconName}.svg`);
        if (!res.ok) {
            const errorText = await res.text();
            throw new Error(`Icon not found or network error: ${res.status} - ${errorText.substring(0, 100)}`);
        }
        const svgText = await res.text();

        fabric.loadSVGFromString(svgText, (objects, options) => {
            if (!objects || objects.length === 0) {
                console.error("No SVG objects loaded from string for icon:", iconName);
                showCustomMessageBox("Could not add graphic. SVG data might be invalid.", 'error');
                return;
            }
            const icon = fabric.util.groupSVGElements(objects, options);
            icon.set({
                left: canvas.width / 2 - (icon.width / 2),
                top: canvas.height / 2 - (icon.height / 2),
                fill: objectColorPicker.value, // Set initial color from picker
                scaleX: 2,
                scaleY: 2,
                hasRotatingPoint: true,
                cornerColor: 'var(--primary-color)',
                borderColor: 'var(--primary-color)',
                cornerSize: 10,
                transparentCorners: false,
                lockUniScaling: false,
            });
            canvas.add(icon).setActiveObject(icon);
            canvas.requestRenderAll();
            saveCanvasState();
        }, { crossOrigin: 'anonymous' }); // Important for loading SVGs with external resources if any
    } catch (err) {
        console.error("Failed to load icon:", iconName, err);
        showCustomMessageBox("Failed to load icon: " + iconName + ". " + err.message, 'error');
    }
}

imageUploadInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (f) => {
        fabric.Image.fromURL(f.target.result, (img) => {
            img.set({
                left: canvas.width / 2 - (img.width / 2),
                top: canvas.height / 2 - (img.height / 2),
                scaleX: Math.min(1, canvas.width / (img.width * 1.5)),
                scaleY: Math.min(1, canvas.height / (img.height * 1.5)),
                cornerColor: 'var(--primary-color)',
                borderColor: 'var(--primary-color)',
                cornerSize: 10,
                transparentCorners: false,
                hasRotatingPoint: true,
                lockUniScaling: false,
            });
            canvas.add(img).setActiveObject(img);
            canvas.requestRenderAll();
            saveCanvasState();
        }, { crossOrigin: 'anonymous' }); // Important for images loaded via data URL or file system if used in export
    };
    reader.readAsDataURL(file);
    hideModal('imageUploadModal');
    e.target.value = '';
});

// --- Drag and Drop Functions ---
function allowDrop(event) {
    event.preventDefault();
}

function dropImage(event) {
    event.preventDefault();
    const files = event.dataTransfer.files;
    if (files.length > 0) {
        const file = files[0];
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                fabric.Image.fromURL(e.target.result, (img) => {
                    // Position at drop point relative to canvas top-left
                    const canvasRect = canvas.getElement().getBoundingClientRect();
                    const dropX = event.clientX - canvasRect.left;
                    const dropY = event.clientY - canvasRect.top;

                    img.set({
                        left: dropX - (img.width * img.scaleX) / 2,
                        top: dropY - (img.height * img.scaleY) / 2,
                        scaleX: Math.min(1, canvas.width / (img.width * 1.5)),
                        scaleY: Math.min(1, canvas.height / (img.height * 1.5)),
                        cornerColor: 'var(--primary-color)',
                        borderColor: 'var(--primary-color)',
                        cornerSize: 10,
                        transparentCorners: false,
                        hasRotatingPoint: true,
                        lockUniScaling: false,
                    });
                    canvas.add(img).setActiveObject(img);
                    canvas.requestRenderAll();
                    saveCanvasState();
                }, { crossOrigin: 'anonymous' }); // Ensure crossOrigin for drag-dropped images too
            };
            reader.readAsDataURL(file);
        } else {
            showCustomMessageBox('Please drop an image file.', 'warning');
        }
    }
}

// --- Add Image from URL Function ---
addIconFromUrlBtn.addEventListener('click', () => {
    const imageUrl = iconUrlInput.value.trim();
    if (imageUrl) {
        addImageFromUrl(imageUrl);
        hideModal('iconModal');
        iconUrlInput.value = ''; // Clear input after adding
    } else {
        showCustomMessageBox('Please enter a valid image URL.', 'warning');
    }
});

function addImageFromUrl(url) {
    fabric.Image.fromURL(url, (img) => {
        img.set({
            left: canvas.width / 2 - (img.width * img.scaleX) / 2,
            top: canvas.height / 2 - (img.height * img.scaleY) / 2,
            scaleX: Math.min(1, canvas.width / (img.width * 1.5)),
            scaleY: Math.min(1, canvas.height / (img.height * 1.5)),
            cornerColor: 'var(--primary-color)',
            borderColor: 'var(--primary-color)',
            cornerSize: 10,
            transparentCorners: false,
            hasRotatingPoint: true,
            lockUniScaling: false,
        });
        canvas.add(img).setActiveObject(img);
        canvas.requestRenderAll();
        saveCanvasState();
    }, { crossOrigin: 'anonymous' }); // Important for loading images from other domains
}


// --- Doodle and Arrow Tool Functionality ---
function enableDoodleMode() {
    disableAllModes(); 
    canvas.isDrawingMode = true;
    canvas.freeDrawingBrush.width = parseInt(brushThicknessInput.value, 10) || 5;
    canvas.freeDrawingBrush.color = brushColorPicker.value || '#000000';
    canvas.freeDrawingBrush.globalCompositeOperation = 'source-over';
    canvas.selection = false; // Disable object selection while doodling
    canvas.discardActiveObject().renderAll();
    updatePropertiesPanel(); // Update properties panel for drawing controls
    if (window.innerWidth <= 768) { // On mobile, show properties modal
        showModal('properties-modal');
    }
}

// Function to enable the general selection/move tool
function enableArrowTool() {
    disableAllModes(); // This correctly sets canvas.isDrawingMode to false and canvas.selection to true
    canvas.discardActiveObject().renderAll(); // Clear any active object
    updatePropertiesPanel(); // Update properties panel (will show "no selection" if nothing active)
    // On mobile, keep the properties modal open if it was already open, or if user explicitly clicks Properties.
    // Do NOT hide modal here. User should explicitly close it.
}

// Global function to disable all custom modes and return to default selection behavior
function disableAllModes() {
    canvas.isDrawingMode = false;
    canvas.freeDrawingBrush.globalCompositeOperation = 'source-over';
    canvas.selection = true; // Always re-enable general object selection
    canvas.discardActiveObject().renderAll();

    // Ensure all objects are set to selectable when returning to general mode
    canvas.forEachObject(obj => {
        if (obj.id !== 'tshirtBackground') {
            obj.set({
                evented: true,
                selectable: true
            });
        }
    });
    // No call to updatePropertiesPanel here, it will be called by selection events or button clicks.
}

// Event Listeners for Doodle Buttons
if (doodleBtnDesktop) doodleBtnDesktop.addEventListener('click', enableDoodleMode);
if (doodleBtnMobile) doodleBtnMobile.addEventListener('click', enableDoodleMode);

// Event Listeners for Arrow Tool Buttons
if (arrowToolBtnDesktop) arrowToolBtnDesktop.addEventListener('click', enableArrowTool);
if (arrowToolBtnMobile) arrowToolBtnMobile.addEventListener('click', enableArrowTool);

// Event listeners for brush properties
if (brushColorPicker) {
    brushColorPicker.addEventListener('input', () => {
        if (canvas.isDrawingMode) canvas.freeDrawingBrush.color = brushColorPicker.value;
        canvas.renderAll();
    });
}
if (brushThicknessInput) {
    brushThicknessInput.addEventListener('input', () => {
        if (canvas.isDrawingMode) canvas.freeDrawingBrush.width = parseInt(brushThicknessInput.value, 10) || 1;
        canvas.renderAll();
    });
}

canvas.on('mouse:up', function(opt) {
    if (this.isDrawingMode) {
        saveCanvasState();
    }
    this.isDragging = false;
});

// Canvas selection events: update properties panel and show modal on mobile
canvas.on('selection:created', (e) => {
    updatePropertiesPanel();
    if (window.innerWidth <= 768) { // Only show modal on mobile
        showModal('properties-modal');
    }
});
canvas.on('selection:updated', (e) => {
    updatePropertiesPanel();
});
canvas.on('selection:cleared', (e) => {
    updatePropertiesPanel();
    // Do NOT hide modal here. It should only hide via its close button
});

// --- Event listeners for text properties (font, size, color) ---
fontSelect.addEventListener('change', () => {
    const activeObject = canvas.getActiveObject();
    if (activeObject && (activeObject.type === 'textbox' || activeObject.type === 'i-text')) {
        activeObject.set('fontFamily', fontSelect.value);
        canvas.renderAll();
        saveCanvasState();
    }
});

fontSizeInput.addEventListener('input', () => {
    const activeObject = canvas.getActiveObject();
    if (activeObject && (activeObject.type === 'textbox' || activeObject.type === 'i-text')) {
        activeObject.set('fontSize', parseInt(fontSizeInput.value, 10));
        canvas.renderAll();
        saveCanvasState();
    }
});

textColorPicker.addEventListener('input', () => {
    const activeObject = canvas.getActiveObject();
    if (activeObject && (activeObject.type === 'textbox' || activeObject.type === 'i-text')) {
        activeObject.set('fill', textColorPicker.value);
        canvas.renderAll();
        saveCanvasState();
    }
});

objectColorPicker.addEventListener('input', () => {
    const activeObject = canvas.getActiveObject();
    if (activeObject && (activeObject.type === 'group' || activeObject.type === 'path' || activeObject.type === 'path-group')) {
        activeObject.set('fill', objectColorPicker.value);
        canvas.renderAll();
        saveCanvasState();
    }
});


// --- Properties Panel Management (Responsive Logic) ---
function updatePropertiesPanel() {
    const activeObject = canvas.getActiveObject();
    const isMobileView = window.innerWidth <= 768;

    // Reset visibility of all property sections initially
    noSelectionMessageDesktop.style.display = 'none';
    noSelectionMessageMobile.style.display = 'none';
    textPropertiesPanel.style.display = 'none';
    objectPropertiesPanel.style.display = 'none';
    if (drawingPropertiesPanel) drawingPropertiesPanel.style.display = 'none';
    
    // T-shirt color properties are no longer here, handled by separate modal

    let targetPropertiesContainer;
    if (!isMobileView) {
        // Desktop view: properties panel is always shown
        propertiesPanelDesktop.classList.remove('hidden');
        targetPropertiesContainer = propertiesPanelDesktop;
        hideModal('properties-modal'); // Ensure modal is hidden on desktop
    } else {
        // Mobile view: properties panel is hidden, content moves to modal
        propertiesPanelDesktop.classList.add('hidden');
        targetPropertiesContainer = propertiesModalContent;
        // Clear properties modal content before populating to avoid duplicates
        propertiesModalContent.innerHTML = ''; 
    }

    // Append property sections to the correct container (desktop or mobile modal)
    // This part ensures the DOM elements are moved into the correct parent.
    if (textPropertiesPanel.parentNode !== targetPropertiesContainer) {
        targetPropertiesContainer.appendChild(textPropertiesPanel);
    }
    if (objectPropertiesPanel.parentNode !== targetPropertiesContainer) {
        targetPropertiesContainer.appendChild(objectPropertiesPanel);
    }
    if (drawingPropertiesPanel && drawingPropertiesPanel.parentNode !== targetPropertiesContainer) {
        targetPropertiesContainer.appendChild(drawingPropertiesPanel);
    }

    // Determine which message/properties to show within the active container
    if (canvas.isDrawingMode) {
        if (drawingPropertiesPanel) {
            drawingPropertiesPanel.style.display = 'block';
            brushColorPicker.value = canvas.freeDrawingBrush.color;
            brushThicknessInput.value = canvas.freeDrawingBrush.width;
        }
        deleteBtnDesktop.disabled = true;
        deleteBtnMobile.disabled = true;
    } else if (!activeObject || activeObject.id === 'tshirtBackground') {
        // No valid object selected or it's the background T-shirt
        if (!isMobileView) {
            noSelectionMessageDesktop.style.display = 'block';
        } else {
            // For mobile, the 'no selection' message is shown within the modal if no object is selected.
            noSelectionMessageMobile.style.display = 'block';
        }
        deleteBtnDesktop.disabled = true;
        deleteBtnMobile.disabled = true;
    } else {
        // A valid object is selected
        deleteBtnDesktop.disabled = false;
        deleteBtnMobile.disabled = false;

        if (activeObject.type === 'textbox' || activeObject.type === 'i-text') {
            textPropertiesPanel.style.display = 'block';
            fontSelect.value = activeObject.fontFamily || 'Arial';
            fontSizeInput.value = activeObject.fontSize || 40;
            textColorPicker.value = activeObject.fill || '#000000';
        } else if (activeObject.type === 'group' || activeObject.type === 'image' || activeObject.type === 'path' || activeObject.type === 'path-group') {
            objectPropertiesPanel.style.display = 'block';
            if (activeObject.type === 'group' || activeObject.type === 'path' || activeObject.type === 'path-group') {
                objectColorPicker.value = activeObject.fill || '#000000';
                objectColorPicker.disabled = false;
            } else {
                objectColorPicker.value = '#000000'; // Raster images cannot be recolored
                objectColorPicker.disabled = true;
            }
        }
    }
}

// --- Delete Selected Object (triggered by both desktop and mobile buttons) ---
function deleteSelectedObject() {
    const activeObject = canvas.getActiveObject();
    if (activeObject && activeObject.id !== 'tshirtBackground') {
        canvas.remove(activeObject);
        canvas.discardActiveObject();
        canvas.requestRenderAll();
        saveCanvasState();
        updatePropertiesPanel(); // Update panel after deletion (might show 'no selection')
    }
}

// Assign delete function to buttons (desktop and mobile)
deleteBtnDesktop.addEventListener('click', deleteSelectedObject);
deleteBtnMobile.addEventListener('click', deleteSelectedObject);

// Keyboard delete support (Delete and Backspace keys)
window.addEventListener('keydown', e => {
    if (e.key === 'Delete' || e.key === 'Backspace') {
        const activeObject = canvas.getActiveObject();
        // Prevent deletion if an input field or textarea is focused
        if (activeObject && !(e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') && activeObject.id !== 'tshirtBackground') {
            deleteSelectedObject(); // Call the unified delete function
        }
    }
});

// --- T-shirt Side Navigation ---
function navigateTshirt(direction) {
    saveCanvasState();
    disableAllModes(); // Disable modes when changing T-shirt side
    let newTshirtIndex = currentTshirtIndex + direction;
    if (newTshirtIndex < 0) {
        newTshirtIndex = canvasStates.length - 1; // Use canvasStates.length for total sides
    } else if (newTshirtIndex >= canvasStates.length) { // Use canvasStates.length for total sides
        newTshirtIndex = 0;
    }
    currentTshirtIndex = newTshirtIndex;
    loadCanvasState(currentTshirtIndex);
    // Optionally update properties panel to reflect no selection if active object is cleared
    // updatePropertiesPanel(); 
}

arrowLeft.addEventListener('click', () => navigateTshirt(-1));
arrowRight.addEventListener('click', () => navigateTshirt(1));

// --- Zoom Functionality ---
const ZOOM_MAX = 3;
const ZOOM_MIN = 0.5;
const ZOOM_STEP = 0.1;

function applyZoom(newZoom) {
    const center = canvas.getCenterPoint();
    canvas.zoomToPoint(center, newZoom);
}

zoomInBtn.addEventListener('click', () => {
    const currentZoom = canvas.getZoom();
    const newZoom = Math.min(ZOOM_MAX, currentZoom + ZOOM_STEP);
    applyZoom(newZoom);
});

zoomOutBtn.addEventListener('click', () => {
    const currentZoom = canvas.getZoom();
    const newZoom = Math.max(ZOOM_MIN, currentZoom - ZOOM_STEP);
    applyZoom(newZoom);
});

resetZoomBtn.addEventListener('click', () => {
    applyZoom(1);
    canvas.viewportTransform = [1, 0, 0, 1, 0, 0]; // Reset pan as well
    canvas.renderAll();
});

canvas.on('mouse:down', function(opt) {
    // Only pan if not in drawing mode AND no object is selected OR it's the background
    if (canvas.isDrawingMode) {
        return;
    }
    if (opt.target && opt.target.id !== 'tshirtBackground') {
        return; // If an object other than background is clicked, allow selection
    }
    // Check if Alt key is pressed OR if canvas is zoomed AND no object is selected
    var evt = opt.e;
    if (evt.altKey === true || (canvas.getZoom() > 1 && !canvas.getActiveObject())) {
        this.isDragging = true;
        this.selection = false; // Temporarily disable selection
        this.lastPosX = evt.clientX;
        this.lastPosY = evt.clientY;
    }
});

canvas.on('mouse:move', function(opt) {
    if (this.isDragging && !canvas.isDrawingMode) { // Only pan if dragging and not in doodle mode
        var e = opt.e;
        var vpt = this.viewportTransform;
        vpt[4] += e.clientX - this.lastPosX;
        vpt[5] += e.clientY - this.lastPosY;
        this.requestRenderAll();
        this.lastPosX = e.clientX;
        this.lastPosY = e.clientY; // Corrected: ensure lastPosY updates correctly
    }
});

canvas.on('mouse:up', function(opt) {
    if (this.isDrawingMode) {
        saveCanvasState();
    }
    this.isDragging = false;
    this.selection = true; // Re-enable selection after drag
});

canvas.on('mouse:wheel', function(opt) {
    var delta = opt.e.deltaY;
    var zoom = canvas.getZoom();
    zoom *= 0.999 ** delta;
    if (zoom > ZOOM_MAX) zoom = ZOOM_MAX;
    if (zoom < ZOOM_MIN) zoom = ZOOM_MIN;
    canvas.zoomToPoint({ x: opt.e.offsetX, y: opt.e.offsetY }, zoom);
    opt.e.preventDefault();
    opt.e.stopPropagation();
});

// --- BUY NOW Functionality ---
// Modified to accept sideIndex to get the correct T-shirt image URL
async function generateTshirtImage(objectsData, multiplier = 2, sideIndex) {
    return new Promise((resolve, reject) => {
        const tempCanvas = new fabric.StaticCanvas(null, {
            width: canvas.width,
            height: canvas.height,
            backgroundColor: 'transparent',
        });

        // Construct the image URL using the selected color's base path and the side index
        const imageUrl = `${selectedTshirtColor.basePath}${sideIndex + 1}.png`;
        console.log(`Generating image for side ${sideIndex + 1} with T-shirt: ${imageUrl}`);

        fabric.Image.fromURL(imageUrl, (bgImg) => {
            bgImg.scaleToWidth(tempCanvas.width);
            if (bgImg.getScaledHeight() > tempCanvas.height) {
                bgImg.scaleToHeight(tempCanvas.height);
            }
            bgImg.set({
                left: (tempCanvas.width - bgImg.getScaledWidth()) / 2,
                top: (tempCanvas.height - bgImg.getScaledHeight()) / 2,
                selectable: false,
                evented: false,
                excludeFromExport: false, // Ensure background is exported
            });

            tempCanvas.add(bgImg);
            tempCanvas.sendToBack(bgImg);

            // Using enlivenObjects from fabric.js to load saved objects
            // Ensure crossOrigin is applied to individual objects if they are images/SVGs
            fabric.util.enlivenObjects(objectsData || [], (objects) => {
                objects.forEach((obj) => {
                    // Important: if objects themselves contain images or SVGs from external sources,
                    // they also need crossOrigin. Fabric.js's toJSON/fromObject handles this usually,
                    // but direct image/SVG loading needs it.
                    if (obj.type === 'image' || obj.type === 'group' && obj._objects && obj._objects.some(o => o.type === 'image' || o.type === 'path')) {
                         // This part is tricky. Fabric.js's enlivenObjects handles crossOrigin if saved correctly.
                         // But for direct URL images, you might need to re-apply crossOrigin if the JSON doesn't carry it.
                         // For now, trust enlivenObjects with the saved JSON.
                    }
                    obj.set({
                        selectable: false,
                        evented: false,
                        hasControls: false,
                        hasBorders: false,
                    });
                    tempCanvas.add(obj);
                });

                tempCanvas.renderAll();

                // Give a small delay to ensure rendering is complete
                setTimeout(() => {
                    try {
                        const dataUrl = tempCanvas.toDataURL({
                            format: 'png',
                            quality: 1, // Higher quality
                            multiplier: multiplier, // Scale up for high-res
                        });
                        resolve(dataUrl);
                        tempCanvas.dispose(); // Clean up temporary canvas
                    } catch (e) {
                        console.error('Canvas export error:', e);
                        showCustomMessageBox('Error exporting image. Check console for CORS issues.', 'error');
                        reject(e);
                        tempCanvas.dispose();
                    }
                }, 250); // Increased delay slightly
            }, { crossOrigin: 'anonymous' }); // Apply crossOrigin to enlivenObjects itself
        }, {
            crossOrigin: 'anonymous' // Apply crossOrigin to the background image
        }).on('error', (err) => {
            console.error('Error loading T-shirt background image for generation:', err);
            showCustomMessageBox('Could not load T-shirt background image for generation. Check image paths/CORS.', 'error', 5000);
            reject(err);
        });
    });
}


function downloadImage(dataUrl, filename) {
    const a = document.createElement('a');
    a.href = dataUrl;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}

buyNowBtn.addEventListener('click', async () => {
    console.log('Buy Now clicked!');
    saveCanvasState(); // Ensure the current state is saved before generation

    customTshirtsPreview.innerHTML = '';
    generationProgressContainer.style.display = 'block';
    generationProgressBar.style.width = '0%';
    generationProgressText.textContent = 'Generating images... 0%';

    // Disable all relevant buttons during generation
    buyNowBtn.disabled = true;
    buyNowBtnMobile.disabled = true; // Disable mobile counterpart too
    arrowLeft.disabled = true;
    arrowRight.disabled = true;
    downloadAllBtn.disabled = true;
    document.querySelectorAll('#buyNowModal .modal-btn.secondary').forEach(btn => btn.disabled = true); // Disable modal's close button temporarily


    showModal('buyNowModal');
    console.log('Buy Now modal shown.');

    const allImageDataUrls = [];
    const totalImages = canvasStates.length;

    for (let i = 0; i < totalImages; i++) {
        generationProgressText.textContent = `Generating Side ${i + 1} of ${totalImages}...`;
        console.log(`Attempting to generate image for side ${i + 1}`);
        try {
            const previewDataUrl = await generateTshirtImage(canvasStates[i], 1, i); // Preview resolution
            allImageDataUrls.push({ url: previewDataUrl, filename: `tshirt_side_${i + 1}.png` });
            console.log(`Image for side ${i + 1} generated successfully.`);

            const progress = Math.round(((i + 1) / totalImages) * 100);
            generationProgressBar.style.width = `${progress}%`;
            generationProgressText.textContent = `Generating Side ${i + 1} of ${totalImages}... (${progress}%)`;
        } catch (error) {
            console.error(`Error generating image for side ${i + 1}:`, error);
            showCustomMessageBox(`Error generating image for side ${i + 1}. Please check browser console for details (e.g., CORS).`, 'error', 7000);
            
            // Re-enable buttons if an error occurs and stop process
            buyNowBtn.disabled = false;
            buyNowBtnMobile.disabled = false;
            arrowLeft.disabled = false;
            arrowRight.disabled = false;
            downloadAllBtn.disabled = false;
            document.querySelectorAll('#buyNowModal .modal-btn.secondary').forEach(btn => btn.disabled = false);
            generationProgressContainer.style.display = 'none';
            return; // Exit the function if an error occurs
        }
    }

    generationProgressContainer.style.display = 'none';
    console.log('All preview images generated. Populating modal.');

    allImageDataUrls.forEach((item, index) => {
        const previewDiv = document.createElement('div');
        previewDiv.style.textAlign = 'center';
        previewDiv.style.marginBottom = '10px';
        previewDiv.style.flex = '0 0 auto';
        previewDiv.style.width = '160px';

        const img = document.createElement('img');
        img.src = item.url;
        img.alt = item.filename;
        img.style.width = '150px';
        img.style.height = '150px';
        img.style.objectFit = 'contain';
        img.style.border = '1px solid var(--border-color)';
        img.style.borderRadius = 'var(--border-radius-sm)';

        const downloadIndividualBtn = document.createElement('button');
        downloadIndividualBtn.className = 'modal-btn secondary';
        downloadIndividualBtn.style.marginTop = '5px';
        downloadIndividualBtn.style.padding = '5px 10px';
        downloadIndividualBtn.style.fontSize = '0.8em';
        downloadIndividualBtn.innerHTML = `<i class="bi bi-download"></i> Side ${index + 1}`;
        // Individual download buttons start enabled
        downloadIndividualBtn.disabled = false; 

        downloadIndividualBtn.onclick = async () => {
            downloadIndividualBtn.disabled = true;
            downloadIndividualBtn.innerHTML = 'Generating...';
            console.log(`Generating high-res image for side ${index + 1}`);
            try {
                // Pass the side index to generateTshirtImage for high resolution
                const highResDataUrl = await generateTshirtImage(canvasStates[index], 2, index);
                downloadImage(highResDataUrl, `tshirt_side_${index + 1}_highres.png`);
                showCustomMessageBox(`Side ${index + 1} downloaded!`, 'success', 2000);
            } catch (error) {
                console.error(`Error downloading high-res image for side ${index + 1}:`, error);
                showCustomMessageBox(`Failed to download side ${index + 1}. Check console for details.`, 'error');
            } finally {
                downloadIndividualBtn.disabled = false;
                downloadIndividualBtn.innerHTML = `<i class="bi bi-download"></i> Side ${index + 1}`;
            }
        };

        previewDiv.appendChild(img);
        previewDiv.appendChild(document.createElement('br'));
        previewDiv.appendChild(downloadIndividualBtn);
        customTshirtsPreview.appendChild(previewDiv);
    });

    // Re-enable main action buttons after all previews are generated
    buyNowBtn.disabled = false;
    buyNowBtnMobile.disabled = false;
    arrowLeft.disabled = false;
    arrowRight.disabled = false;
    downloadAllBtn.disabled = false;
    document.querySelectorAll('#buyNowModal .modal-btn.secondary').forEach(btn => btn.disabled = false); // Re-enable modal's close button


    downloadAllBtn.onclick = async () => {
        if (typeof JSZip !== 'undefined' && typeof saveAs !== 'undefined') {
            downloadAllBtn.disabled = true;
            downloadAllBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Preparing Zip...';
            document.querySelectorAll('#buyNowModal .modal-btn.secondary').forEach(btn => btn.disabled = true); // Disable close button during zip

            generationProgressContainer.style.display = 'block';
            generationProgressBar.style.width = '0%';
            generationProgressText.textContent = 'Preparing zip file... 0%';
            console.log('Starting ZIP generation...');

            const zip = new JSZip();
            const downloadPromises = [];

            let generatedCount = 0;
            const totalDownloadImages = canvasStates.length;

            for (let i = 0; i < totalDownloadImages; i++) {
                const promise = generateTshirtImage(canvasStates[i], 2, i) // High resolution for ZIP
                    .then(dataUrl => {
                        const base64Data = dataUrl.split(',')[1];
                        zip.file(`tshirt_side_${i + 1}.png`, base64Data, { base64: true });
                        generatedCount++;
                        const progress = Math.round((generatedCount / totalDownloadImages) * 100);
                        generationProgressBar.style.width = `${progress}%`;
                        generationProgressText.textContent = `Adding images to zip: ${generatedCount}/${totalDownloadImages} (${progress}%)`;
                        console.log(`Added image for side ${i + 1} to zip.`);
                    })
                    .catch(err => {
                        console.error(`Error adding image for side ${i + 1} to zip:`, err);
                        showCustomMessageBox(`Error adding image for side ${i + 1} to zip.`, 'error');
                        // Do not re-throw, allow other promises to complete if possible
                    });
                downloadPromises.push(promise);
            }

            await Promise.all(downloadPromises);
            console.log('All images processed for ZIP. Compressing...');

            generationProgressText.textContent = 'Compressing zip file... This may take a moment.';
            generationProgressBar.style.width = '100%'; // Show full for compression phase

            zip.generateAsync({ type: "blob" }, function updateCallback(metadata) {
                if (metadata.percent) {
                    const percent = Math.round(metadata.percent);
                    generationProgressBar.style.width = `${percent}%`;
                    generationProgressText.textContent = `Compressing zip... ${percent}%`;
                }
            })
                .then(function(content) {
                    saveAs(content, "custom_tshirts.zip");
                    showCustomMessageBox("Your T-shirt designs are being downloaded as a ZIP file!", 'success', 3000);
                    console.log("ZIP file generated and download initiated.");
                })
                .catch(err => {
                    console.error("Error generating zip:", err);
                    showCustomMessageBox("Failed to generate zip file. See console for details.", 'error', 5000);
                })
                .finally(() => {
                    downloadAllBtn.disabled = false;
                    downloadAllBtn.innerHTML = '<i class="bi bi-download"></i> Download All (Zip)';
                    generationProgressContainer.style.display = 'none';
                    document.querySelectorAll('#buyNowModal .modal-btn.secondary').forEach(btn => btn.disabled = false); // Re-enable close button
                });
        } else {
            showCustomMessageBox("To download all as a zip, please ensure JSZip and FileSaver.js libraries are included in your HTML. Proceeding with individual downloads.", 'warning', 5000);
            downloadAllBtn.disabled = false;
            downloadAllBtn.innerHTML = '<i class="bi bi-download"></i> Download All (Zip)';
            document.querySelectorAll('#buyNowModal .modal-btn.secondary').forEach(btn => btn.disabled = false); // Re-enable close button
            // Fallback to individual downloads if JSZip/FileSaver are missing
            for (let i = 0; i < canvasStates.length; i++) {
                try {
                    const highResDataUrl = await generateTshirtImage(canvasStates[i], 2, i);
                    downloadImage(highResDataUrl, `tshirt_side_${i + 1}_highres.png`);
                } catch (error) {
                    console.error(`Failed to download individual image ${i + 1} as fallback:`, error);
                    showCustomMessageBox(`Failed to download individual image ${i + 1}.`, 'error');
                }
            }
        }
    };
});

// --- T-shirt Color Options Rendering ---
function renderColorOptions() {
    // Check if the modal container exists before clearing its content
    if (colorOptionsContainerModal) {
        colorOptionsContainerModal.innerHTML = ''; // Clear previous options
    }

    tshirtColors.forEach(color => {
        const colorDiv = document.createElement('div');
        colorDiv.className = 'color-circle';
        colorDiv.style.backgroundColor = color.hex;
        colorDiv.title = color.name;
        colorDiv.setAttribute('data-color-name', color.name); // For white color checkmark styling
        if (color.name === selectedTshirtColor.name) {
            colorDiv.classList.add('active');
        }
        colorDiv.addEventListener('click', () => {
            // Remove active class from all circles in the modal
            document.querySelectorAll('#colorModal .color-circle').forEach(circle => circle.classList.remove('active'));
            // Add active class to clicked circle
            colorDiv.classList.add('active');

            selectedTshirtColor = color;
            saveCanvasState(); // Save current state before changing background
            loadCanvasState(currentTshirtIndex); // Load current side with new color
            hideModal('colorModal'); // Close the modal after selection
            showCustomMessageBox(`T-shirt color changed to ${color.name}!`, 'info', 2000);
        });
        // Append to the new modal container
        if (colorOptionsContainerModal) {
            colorOptionsContainerModal.appendChild(colorDiv);
        }
    });
}
</script>
</body>
</html>