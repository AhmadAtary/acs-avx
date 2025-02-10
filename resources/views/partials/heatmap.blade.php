<div class="heatmap-container" id="heatmap">
    <div class="router"><i class="fa-solid fa-wifi"></i></div>
    <div class="tooltip" id="tooltip"></div>
</div>

<style>
.heatmap-container {
    position: relative;
    width: 500px;
    height: 500px;
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
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    /* background-color: #007bff; Default color */
    transition: transform 0.2s ease;
}

.device-node:hover {
    transform: scale(1.2);
}

.tooltip {
    position: absolute;
    padding: 5px 10px;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    font-size: 12px;
    border-radius: 4px;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.2s ease-in-out;
}

.fa-user {
    font-size: 18px;
    color: white;
}

.HeatmapRow {
    display: none; /* Initially hide the HeatmapRow */
    flex-wrap: wrap; /* Allow elements to wrap in the row */
    margin-top: 20px;
}
</style>

