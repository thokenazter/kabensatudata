{{-- resources/views/reports/iks-pdf.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Indeks Keluarga Sehat</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            line-height: 1.5;
            color: #333;
        }
        .container {
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            font-size: 18pt;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 10pt;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 14pt;
            margin-bottom: 10px;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .stats-container {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .stats-box {
            width: 30%;
            margin-right: 3%;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .stats-box h3 {
            font-size: 12pt;
            margin-top: 0;
            margin-bottom: 5px;
        }
        .stats-box p {
            font-size: 18pt;
            font-weight: bold;
            margin: 0;
        }
        .stats-box small {
            font-size: 9pt;
            color: #666;
        }
        .village-section {
            page-break-inside: avoid;
            margin-bottom: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .village-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        .village-name {
            font-size: 14pt;
            font-weight: bold;
        }
        .village-stats {
            text-align: right;
        }
        .village-stats .iks-value {
            font-size: 16pt;
            font-weight: bold;
        }
        .village-stats .status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 8pt;
            margin-left: 5px;
        }
        .status-sehat {
            background-color: #dcfce7;
            color: #166534;
        }
        .status-prasehat {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-tidaksehat {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        .progress-container {
            height: 10px;
            background-color: #e5e7eb;
            border-radius: 5px;
            margin-top: 5px;
        }
        .progress-bar {
            height: 10px;
            border-radius: 5px;
        }
        .progress-green {
            background-color: #10b981;
        }
        .progress-yellow {
            background-color: #f59e0b;
        }
        .progress-red {
            background-color: #ef4444;
        }
        .page-break {
            page-break-after: always;
        }
        .indicator-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
        }
        .indicator-box {
            padding: 8px;
            background-color: #f2f2f2;
            border-radius: 5px;
        }
        .indicator-box p {
            margin: 0 0 5px 0;
            font-size: 9pt;
        }
        footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8pt;
            color: #666;
            padding: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>LAPORAN INDEKS KELUARGA SEHAT (IKS)</h1>
            <p>Tanggal: {{ $exportDate }}</p>
        </div>
        
        <div class="section">
            <h2 class="section-title">Ringkasan IKS</h2>
            
            <div class="stats-container">
                <div class="stats-box">
                    <h3>Rata-rata IKS</h3>
                    <p>{{ number_format($overallData['avg_iks'], 2) }}%</p>
                    <small>{{ $overallData['health_status'] }}</small>
                </div>
                
                <div class="stats-box">
                    <h3>Jumlah Keluarga</h3>
                    <p>{{ number_format($overallData['total_families']) }}</p>
                    <small>&nbsp;</small>
                </div>
                
                <div class="stats-box">
                    <h3>Keluarga Sehat</h3>
                    <p>{{ number_format($overallData['healthy_percentage'], 1) }}%</p>
                    <small>{{ number_format($overallData['healthy_count']) }} keluarga</small>
                </div>
                
                <div class="stats-box">
                    <h3>Keluarga Pra-Sehat</h3>
                    <p>{{ number_format($overallData['pre_healthy_percentage'], 1) }}%</p>
                    <small>{{ number_format($overallData['pre_healthy_count']) }} keluarga</small>
                </div>
                
                <div class="stats-box">
                    <h3>Keluarga Tidak Sehat</h3>
                    <p>{{ number_format($overallData['unhealthy_percentage'], 1) }}%</p>
                    <small>{{ number_format($overallData['unhealthy_count']) }} keluarga</small>
                </div>
            </div>
            
            <h3>Capaian 12 Indikator IKS</h3>
            <table>
                <thead>
                    <tr>
                        <th>Indikator</th>
                        <th>Keluarga Relevan</th>
                        <th>Keluarga Terpenuhi</th>
                        <th>Persentase</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($overallData['indicators'] as $key => $indicator)
                        <tr>
                            <td>{{ $indicator['label'] }}</td>
                            <td align="center">{{ number_format($indicator['relevant_count']) }}</td>
                            <td align="center">{{ number_format($indicator['fulfilled_count']) }}</td>
                            <td align="center">
                                <div>{{ number_format($indicator['percentage'], 1) }}%</div>
                                <div class="progress-container">
                                    <div class="progress-bar {{ $indicator['percentage'] > 80 ? 'progress-green' : ($indicator['percentage'] > 50 ? 'progress-yellow' : 'progress-red') }}" style="width: {{ min($indicator['percentage'], 100) }}%"></div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="page-break"></div>
        
        <div class="section">
            <h2 class="section-title">Laporan IKS per Desa</h2>
            
            @foreach ($villageData as $village)
                <div class="village-section">
                    <div class="village-header">
                        <div>
                            <div class="village-name">{{ $village['village']['name'] }}</div>
                            <div>{{ $village['village']['district'] }}, {{ $village['village']['regency'] }}</div>
                        </div>
                        <div class="village-stats">
                            <div>
                                <span class="iks-value">{{ number_format($village['avg_iks'], 2) }}%</span>
                                <span class="status {{ $village['avg_iks'] > 80 ? 'status-sehat' : ($village['avg_iks'] > 50 ? 'status-prasehat' : 'status-tidaksehat') }}">
                                    {{ $village['health_status'] }}
                                </span>
                            </div>
                            <div>{{ $village['total_families'] }} keluarga</div>
                        </div>
                    </div>
                    
                    <table>
                        <tr>
                            <td width="33%">
                                <strong>Keluarga Sehat:</strong> {{ $village['healthy_count'] }} keluarga
                                ({{ $village['total_families'] > 0 ? number_format(($village['healthy_count'] / $village['total_families']) * 100, 1) : 0 }}%)
                            </td>
                            <td width="33%">
                                <strong>Keluarga Pra-Sehat:</strong> {{ $village['pre_healthy_count'] }} keluarga
                                ({{ $village['total_families'] > 0 ? number_format(($village['pre_healthy_count'] / $village['total_families']) * 100, 1) : 0 }}%)
                            </td>
                            <td width="33%">
                                <strong>Keluarga Tidak Sehat:</strong> {{ $village['unhealthy_count'] }} keluarga
                                ({{ $village['total_families'] > 0 ? number_format(($village['unhealthy_count'] / $village['total_families']) * 100, 1) : 0 }}%)
                            </td>
                        </tr>
                    </table>
                    
                    <h4>Persentase Indikator Terpenuhi:</h4>
                    <div class="indicator-grid">
                        @foreach ($village['indicators'] as $key => $indicator)
                            @if ($indicator['relevant_count'] > 0)
                                <div class="indicator-box">
                                    <p><strong>{{ $indicator['label'] }}</strong></p>
                                    <div class="progress-container">
                                        <div class="progress-bar {{ $indicator['percentage'] > 80 ? 'progress-green' : ($indicator['percentage'] > 50 ? 'progress-yellow' : 'progress-red') }}" style="width: {{ min($indicator['percentage'], 100) }}%"></div>
                                    </div>
                                    <p align="right">{{ number_format($indicator['percentage'], 1) }}%</p>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                
                @if (!$loop->last)
                    <div style="margin-bottom: 20px;"></div>
                @endif
            @endforeach
        </div>
    </div>
    
    <footer>
        <p>Laporan Indeks Keluarga Sehat (IKS) - {{ $exportDate }}</p>
    </footer>
</body>
</html>