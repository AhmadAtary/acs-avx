@extends('layouts.app')
@section('title', 'AVXAV ACS | Network Analysis')

@section('content')
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Network Analysis</div>
</div>

<div class="card">
    <div class="card-body">
        <div class="mb-3">
            <button id="startAnalysisBtn" class="btn btn-primary">Start Network Analysis</button>
            <!-- <button id="refreshAnalysisBtn" class="btn btn-secondary">Refresh Analysis</button> -->
        </div>

        <div id="resultsSection" style="display: none;">
            <h5>Analysis Results</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Cell ID</th>
                        <th>Device Count</th>
                        <th>Average RSRP</th>
                        <th>Average RSSI</th>
                    </tr>
                </thead>
                <tbody id="resultsTableBody">
                    <!-- Results will be appended here via JS -->
                </tbody>
            </table>
        </div>

        <div id="loading" style="display: none;">
            <p class="text-muted">Analyzing network data... please wait.</p>
        </div>
    </div>
</div>

<script>
    function loadAnalysisResults() {
        const loading = document.getElementById('loading');
        const resultsSection = document.getElementById('resultsSection');
        const resultsTableBody = document.getElementById('resultsTableBody');

        loading.style.display = 'block';
        resultsSection.style.display = 'none';
        resultsTableBody.innerHTML = '';

        fetch('{{ route("analysis.process") }}')
            .then(response => response.json())
            .then(data => {
                loading.style.display = 'none';
                resultsSection.style.display = 'block';

                data.forEach(item => {
                    resultsTableBody.innerHTML += `
                        <tr>
                            <td>${item.cell_id}</td>
                            <td>${item.device_count}</td>
                            <td>${item.avg_rsrp !== null ? item.avg_rsrp : 'N/A'}</td>
                            <td>${item.avg_rssi !== null ? item.avg_rssi : 'N/A'}</td>
                        </tr>
                    `;
                });
            })
            .catch(error => {
                loading.style.display = 'none';
                alert('Error processing network analysis.');
                console.error(error);
            });
    }

    document.getElementById('startAnalysisBtn').addEventListener('click', loadAnalysisResults);

    

    // Initially load results if available
    loadAnalysisResults();
</script>
@endsection
