<div class="heatmap-container" id="heatmap">
    <!-- Range Circles -->
    <div class="range-circle" style="width: 100px; height: 100px;"></div>
    <div class="range-circle" style="width: 200px; height: 200px;"></div>
    <div class="range-circle" style="width: 300px; height: 300px;"></div>
    <div class="range-circle" style="width: 400px; height: 400px;"></div>

    <!-- Router icon at the center -->
    <div class="router">
        <i class="fa-solid fa-wifi"></i>
    </div>

    <div class="tooltip" id="tooltip"></div>
</div>

<style>
/* Heatmap Container with Darker Signal Strength Gradient */
.heatmap-container {
    position: relative;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(0, 100, 0, 0.9) 10%, rgba(150, 150, 0, 0.9) 50%, rgba(250, 5, 5, 0.9) 90%);
    border-radius: 50%;
    overflow: hidden;
    box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Range Circles Inside the Heatmap */
.range-circle {
    position: absolute;
    border-radius: 50%;
    border: 2px solid rgba(0, 0, 0, 0.8); /* Black Range Circles */
    background: transparent;
    box-shadow: inset 0px 0px 10px rgba(0, 0, 0, 0.5);
    transform: translate(-50%, -50%);
}
.fa-user {
    color: white;
    font-size: 16px;
}   

/* Position each circle correctly */
.range-circle:nth-child(1) { top: 50%; left: 50%; }
.range-circle:nth-child(2) { top: 50%; left: 50%; }
.range-circle:nth-child(3) { top: 50%; left: 50%; }
.range-circle:nth-child(4) { top: 50%; left: 50%; }

/* Device Nodes - Positioned Dynamically */
.device-node {
    position: absolute;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.2s ease, background 0.3s ease-in-out;
}

/* Darker Colors for Signal Strength */
/* .device-node[data-signal="strong"] {
    background-color: rgba(0, 255, 0, 0.9); /* Strong Signal - Green */


/* .device-node[data-signal="medium"] {
    background-color: rgba(255, 255, 0, 0.9); /* Medium Signal - Yellow 
}

.device-node[data-signal="weak"] {
    background-color: rgba(255, 0, 0, 0.9); /* Weak Signal - Red 
} 
*/

/* Hover Effect */
.device-node:hover {
    transform: scale(1.2);
}

/* Tooltip Styling */
.tooltip {
    position: absolute;
    padding: 5px 10px;
    background: rgba(20, 20, 20, 0.9);
    color: white;
    font-size: 12px;
    border-radius: 4px;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.2s ease-in-out;
}

/* Router Icon in the Center */
.router {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: black;
    box-shadow: 0px 0px 8px rgba(0, 50, 150, 0.9);
}

.router i {
    font-size: 24px;
    color: white;
}

.HeatmapRow {
    display: none; /* Initially hide the HeatmapRow */
    flex-wrap: wrap; /* Allow elements to wrap in the row */
    margin-top: 20px;
}
</style>
