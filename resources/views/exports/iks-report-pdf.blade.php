{{-- resources/views/exports/iks-report-pdf.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Indeks Keluarga Sehat</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        
        .container {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #4B5563;
        }
        
        .logo {
            max-height: 60px;
            margin-bottom: 10px;
        }
        
        h1 {
            font-size: 18px;
            margin: 0 0 5px;
            color: #1F2937;
        }
        
        h2 {
            font-size: 16px;
            margin: 15px 0 10px;
            color: #1F2937;
            padding-bottom: 5px;
            border-bottom: 1px solid #E5E7EB;
        }
        
        h3 {
            font-size: 14px;
            margin: 10px 0;
            color: #1F2937;
        }
        
        .meta-info {
            margin-bottom: 5px;
            font-size: 11px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        table, th, td {
            border: 1px solid #D1D5DB;
        }
        
        th, td {
            padding: 8px;
            text-align: left;
        }
        
        th {
            background-color: #F3F4F6;
            font-weight: bold;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .summary-box {
            background-color: #F9FAFB;
            border: 1px solid #E5E7EB;
            padding: 10px;
            margin-bottom: 20px;
        }
        
        .summary-item {
            display: inline-block;
            margin-right: 20px;
            margin-bottom: 10px;
        }
        
        .summary-label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .summary-value {
            font-size: 16px;
        }
        
        .status-healthy {
            color: #059669;
        }
        
        .status-pre-healthy {
            color: #D97706;
        }
        
        .status-unhealthy {
            color: #DC2626;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #6B7280;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        .progress-bar-container {
            width: 100%;
            background-color: #E5E7EB;
            height: 10px;
            margin-top: 5px;
            position: relative;
        }
        
        .progress-bar {
            height: 10px;
            position: absolute;
            left: 0;
            top: 0;
        }
        
        .progress-text {
            position: absolute;
            right: 5px;
            top: -3px;
            font-size: 10px;
            color: #1F2937;
        }
        
        .notice {
            background-color: #FEF3C7;
            border: 1px solid #F59E0B;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <!-- Check apakah logo ada, jika tidak tampilkan text saja -->
            @if(file_exists(public_path('images/logo-puskesmas.png')))
                <img src="{{ public_path('images/logo-puskesmas.png') }}" alt="Logo Puskesmas" class="logo">
            @endif
            <h1>LAPORAN INDEKS KELUARGA SEHAT</h1>
            <div>PUSKESMAS {{ strtoupper(config('app.name', 'PUSKESMAS')) }}</div>
            <div>{{ $selectedPeriod }}</div>
        </div>
        
        <!-- Meta Information -->
        <div class="meta-info">
            <p><strong>Desa/Kelurahan:</strong> {{ $selectedVillage }}</p>
            <p><strong>Dicetak pada:</strong> {{ $generatedAt }}</p>
        </div>
        
        <!-- Overall Summary -->
        <h2>Ringkasan IKS</h2>
        <div class="summary-box">
            @php
                $hasData = false;
                $totalFamilies = 0;
                $healthyTotal = 0;
                $preHealthyTotal = 0;
                $unhealthyTotal = 0;
                $averageIks = 0;
                $healthyPercentage = 0;
                $preHealthyPercentage = 0;
                $unhealthyPercentage = 0;
                
                // Cek jika ada data valid
                if ($reportData->isNotEmpty()) {
                    $hasData = true;
                    $totalFamilies = $reportData->sum('total_families');
                    $healthyTotal = $reportData->sum('healthy_count');
                    $preHealthyTotal = $reportData->sum('pre_healthy_count');
                    $unhealthyTotal = $reportData->sum('unhealthy_count');
                    
                    $weightedSum = 0;
                    foreach ($reportData as $item) {
                        if (is_numeric($item->average_iks) && is_numeric($item->total_families) && $item->total_families > 0) {
                            $weightedSum += $item->average_iks * $item->total_families;
                        }
                    }
                    
                    $averageIks = $totalFamilies > 0 ? $weightedSum / $totalFamilies : 0;
                    
                    $healthyPercentage = $totalFamilies > 0 ? ($healthyTotal / $totalFamilies) * 100 : 0;
                    $preHealthyPercentage = $totalFamilies > 0 ? ($preHealthyTotal / $totalFamilies) * 100 : 0;
                    $unhealthyPercentage = $totalFamilies > 0 ? ($unhealthyTotal / $totalFamilies) * 100 : 0;
                }
            @endphp
            
            @if($hasData && $totalFamilies > 0)
                <div class="summary-item">
                    <div class="summary-label">Total Keluarga</div>
                    <div class="summary-value">{{ number_format($totalFamilies) }}</div>
                </div>
                
                <div class="summary-item">
                    <div class="summary-label">Rata-rata IKS</div>
                    <div class="summary-value">{{ number_format($averageIks * 100, 1) }}%</div>
                </div>
                
                <div class="summary-item">
                    <div class="summary-label">Keluarga Sehat</div>
                    <div class="summary-value status-healthy">{{ number_format($healthyTotal) }} ({{ number_format($healthyPercentage, 1) }}%)</div>
                </div>
                
                <div class="summary-item">
                    <div class="summary-label">Keluarga Pra-Sehat</div>
                    <div class="summary-value status-pre-healthy">{{ number_format($preHealthyTotal) }} ({{ number_format($preHealthyPercentage, 1) }}%)</div>
                </div>
                
                <div class="summary-item">
                    <div class="summary-label">Keluarga Tidak Sehat</div>
                    <div class="summary-value status-unhealthy">{{ number_format($unhealthyTotal) }} ({{ number_format($unhealthyPercentage, 1) }}%)</div>
                </div>
            @else
                <div class="notice">
                    Belum ada data keluarga yang dihitung IKS untuk periode dan desa yang dipilih. 
                    Silakan pilih periode berbeda atau pastikan perhitungan IKS telah dilakukan.
                </div>
            @endif
        </div>
        
        <!-- Village Breakdown -->
        <h2>Rekapitulasi Per Desa/Kelurahan</h2>
        @if($reportData->isNotEmpty() && $totalFamilies > 0)
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Desa/Kelurahan</th>
                        <th class="text-center">Jumlah KK</th>
                        <th class="text-center">Rata-rata IKS</th>
                        <th class="text-center">Keluarga Sehat</th>
                        <th class="text-center">Keluarga Pra-Sehat</th>
                        <th class="text-center">Keluarga Tidak Sehat</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportData as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->village_name }}</td>
                            <td class="text-center">{{ number_format($row->total_families) }}</td>
                            <td class="text-center">{{ number_format($row->average_iks * 100, 1) }}%</td>
                            <td class="text-center">{{ number_format($row->healthy_count) }} ({{ number_format(($row->total_families > 0 ? ($row->healthy_count / $row->total_families) * 100 : 0), 1) }}%)</td>
                            <td class="text-center">{{ number_format($row->pre_healthy_count) }} ({{ number_format(($row->total_families > 0 ? ($row->pre_healthy_count / $row->total_families) * 100 : 0), 1) }}%)</td>
                            <td class="text-center">{{ number_format($row->unhealthy_count) }} ({{ number_format(($row->total_families > 0 ? ($row->unhealthy_count / $row->total_families) * 100 : 0), 1) }}%)</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="notice">
                Belum ada data rekapitulasi per desa. Silakan lakukan perhitungan IKS terlebih dahulu.
            </div>
        @endif
        
        <!-- Page Break -->
        <div class="page-break"></div>
        
        <!-- 12 Indicators -->
        <h2>Capaian 12 Indikator</h2>
        @php
            $hasIndicatorData = !empty($indicatorData) && array_sum(array_column($indicatorData, 'relevant')) > 0;
        @endphp
        
        @if($hasIndicatorData)
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 40%;">Indikator</th>
                        <th style="width: 15%;" class="text-center">Jumlah Relevan</th>
                        <th style="width: 15%;" class="text-center">Jumlah Terpenuhi</th>
                        <th style="width: 25%;" class="text-center">Capaian</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // Sort indicators by percentage
                        $sortedIndicators = collect($indicatorData)->sortBy('percentage')->toArray();
                        $index = 1;
                    @endphp
                    
                    @foreach($sortedIndicators as $key => $indicator)
                        <tr>
                            <td>{{ $index++ }}</td>
                            <td>{{ $indicator['label'] }}</td>
                            <td class="text-center">{{ number_format($indicator['relevant']) }}</td>
                            <td class="text-center">{{ number_format($indicator['fulfilled']) }}</td>
                            <td>
                                <div style="position: relative;">
                                    {{ number_format($indicator['percentage'], 1) }}%
                                    <div class="progress-bar-container">
                                        <div class="progress-bar" style="width: {{ min($indicator['percentage'], 100) }}%; background-color: {{ $indicator['percentage'] > 80 ? '#059669' : ($indicator['percentage'] > 50 ? '#D97706' : '#DC2626') }};"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <!-- Indicator Analysis -->
            <h2>Analisis Indikator IKS</h2>
            <h3>Indikator dengan Capaian Tertinggi</h3>
            @php
                $highestIndicators = collect($indicatorData)
                    ->filter(function($item) { return $item['relevant'] > 0; })
                    ->sortByDesc('percentage')
                    ->take(3);
            @endphp
            
            @if($highestIndicators->isNotEmpty())
                <ul>
                    @foreach($highestIndicators as $indicator)
                        <li><strong>{{ $indicator['label'] }}</strong> ({{ number_format($indicator['percentage'], 1) }}%) - {{ $indicator['fulfilled'] }} dari {{ $indicator['relevant'] }} keluarga memenuhi</li>
                    @endforeach
                </ul>
            @else
                <div class="notice">Data indikator tertinggi tidak tersedia.</div>
            @endif
            
            <h3>Indikator dengan Capaian Terendah</h3>
            @php
                $lowestIndicators = collect($indicatorData)
                    ->filter(function($item) { return $item['relevant'] > 0; })
                    ->sortBy('percentage')
                    ->take(3);
            @endphp
            
            @if($lowestIndicators->isNotEmpty())
                <ul>
                    @foreach($lowestIndicators as $indicator)
                        <li><strong>{{ $indicator['label'] }}</strong> ({{ number_format($indicator['percentage'], 1) }}%) - Hanya {{ $indicator['fulfilled'] }} dari {{ $indicator['relevant'] }} keluarga memenuhi</li>
                    @endforeach
                </ul>
                
                <div style="margin-top: 20px;">
                    <h3>Rekomendasi Intervensi</h3>
                    <p>Berdasarkan hasil analisis, intervensi prioritas sebaiknya difokuskan pada:</p>
                    <ol>
                        @foreach($lowestIndicators as $indicator)
                            <li><strong>{{ $indicator['label'] }}</strong> - perlu dibuat program khusus untuk meningkatkan capaian indikator ini.</li>
                        @endforeach
                    </ol>
                </div>
            @else
                <div class="notice">Data indikator terendah tidak tersedia.</div>
            @endif
        @else
            <div class="notice">
                Belum ada data indikator IKS yang tersedia. Silakan lakukan perhitungan IKS terlebih dahulu.
            </div>
        @endif
        
        <!-- Footer -->
        <div class="footer">
            <p>Laporan Indeks Keluarga Sehat {{ config('app.name', 'Puskesmas') }} | {{ $generatedAt }}</p>
            <p>Halaman 2 dari 2</p>
        </div>
    </div>
</body>
</html>