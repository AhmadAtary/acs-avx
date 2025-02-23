<div class="heatmap-container" id="heatmap">
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
}

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
.device-node[data-signal="strong"] {
    background-color: rgba(251, 253, 251, 0.9); /* Dark Green - Strong Signal */
}

.device-node[data-signal="medium"] {
    background-color: rgba(251, 253, 251, 0.9); /* Dark Yellow - Medium Signal */
}

.device-node[data-signal="weak"] {
    background-color: rgba(251, 253, 251, 0.9); /* Dark Red - Weak Signal */
}

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
    box-shadow: 0px 0px 8px rgba(0, 50, 150, 0.9);
}

.router i {
    font-size: 24px;
    color: white;
}

.HeatmapRow {
    display: none; /*Initially hide the HeatmapRow*/
    flex-wrap: wrap; /* Allow elements to wrap in the row */
    margin-top: 20px;
}
</style>
