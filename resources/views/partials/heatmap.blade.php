<div class="heatmap-container" id="heatmap">
    <div class="router"><i class="fa-solid fa-wifi"></i></div>
    <div class="tooltip" id="tooltip"></div>
</div>

<style>

    .heatmap-container {
      position: relative;
      width: 600px; /* Smaller container */
      height: 600px; /* Smaller container */
      background-color: #1a1c23;
      border-radius: 50%;
      overflow: hidden;
      box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.6);
    }

    .radar-circle {
      position: absolute;
      border-radius: 50%;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .device-node {
      position: absolute;
      width: 30px; /* Smaller node */
      height: 30px; /* Smaller node */
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: transform 0.2s ease;
    }

    .device-node:hover {
      transform: scale(1.2);
    }

    .tooltip {
      position: absolute;
      padding: 2px 6px; /* Smaller tooltip */
      background: rgba(0, 0, 0, 0.8);
      color: white;
      font-size: 12px; /* Smaller font size */
      border-radius: 4px;
      pointer-events: none;
      opacity: 0;
      transition: opacity 0.2s ease-in-out;
    }

    .router {
      position: absolute;
      width: 16px; /* Smaller router */
      height: 16pxx; /* Smaller router */
      /* background-color: #007bff; */
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      color: white;
      font-weight: bold;
      font-size: 16px; /* Smaller font size */
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
    }
</style>
