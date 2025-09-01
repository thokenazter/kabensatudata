<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Data Rekam Medis</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 16px;
            margin: 0;
            font-weight: bold;
        }
        .header p {
            margin: 5px 0;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .page-break {
            page-break-after: always;
        }
        @page {
            margin: 1cm;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN DATA REKAM MEDIS</h1>
        <p>Tanggal Export: {{ $timestamp }}</p>
        <p>Total Data: {{ $records->count() }} rekam medis</p>
        @if(!empty($filters))
            <p>
                Filter: 
                @if(!empty($filters['visit_from'])) Dari {{ \Carbon\Carbon::parse($filters['visit_from'])->format('d/m/Y') }} @endif
                @if(!empty($filters['visit_until'])) Sampai {{ \Carbon\Carbon::parse($filters['visit_until'])->format('d/m/Y') }} @endif
                @if(!empty($filters['patient_gender'])) | Jenis Kelamin: {{ $filters['patient_gender'] }} @endif
                @if(!empty($filters['diagnosis_name'])) | Diagnosis: {{ $filters['diagnosis_name'] }} @endif
            </p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 8%;">Tanggal Kunjungan</th>
                <th style="width: 12%;">NIK</th>
                <th style="width: 15%;">Nama Pasien</th>
                <th style="width: 8%;">No. RM</th>
                <th style="width: 6%;">JK</th>
                <th style="width: 8%;">Tgl Lahir</th>
                <th style="width: 15%;">Alamat</th>
                <th style="width: 12%;">Keluhan Utama</th>
                <th style="width: 16%;">Diagnosis</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
            <tr>
                <td class="text-center">{{ $record->visit_date ? $record->visit_date->format('d/m/Y') : '' }}</td>
                <td>{{ $record->patient_nik ?? '' }}</td>
                <td>{{ $record->patient_name ?? '' }}</td>
                <td class="text-center">{{ $record->patient_rm_number ?? '' }}</td>
                <td class="text-center">{{ $record->patient_gender ?? '' }}</td>
                <td class="text-center">
                    @if($record->patient_birth_date)
                        {{ $record->patient_birth_date->format('d/m/Y') }}
                    @elseif($record->familyMember && $record->familyMember->birth_date)
                        {{ \Carbon\Carbon::parse($record->familyMember->birth_date)->format('d/m/Y') }}
                    @endif
                </td>
                <td>{{ Str::limit($record->patient_address ?? '', 50) }}</td>
                <td>{{ Str::limit($record->chief_complaint ?? '', 40) }}</td>
                <td>{{ $record->diagnosis_name ?? '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($records->count() > 20)
        <div class="page-break"></div>
        
        <div class="header">
            <h1>LAPORAN DATA REKAM MEDIS (DETAIL TANDA VITAL)</h1>
            <p>Tanggal Export: {{ $timestamp }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 15%;">Nama Pasien</th>
                    <th style="width: 8%;">Sistolik</th>
                    <th style="width: 8%;">Diastolik</th>
                    <th style="width: 12%;">Kategori TD</th>
                    <th style="width: 8%;">BB (kg)</th>
                    <th style="width: 8%;">TB (cm)</th>
                    <th style="width: 8%;">HR (bpm)</th>
                    <th style="width: 8%;">Suhu (°C)</th>
                    <th style="width: 10%;">RR (/min)</th>
                    <th style="width: 15%;">Dibuat Oleh</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $record)
                <tr>
                    <td>{{ $record->patient_name ?? '' }}</td>
                    <td class="text-center">{{ $record->systolic ?? '' }}</td>
                    <td class="text-center">{{ $record->diastolic ?? '' }}</td>
                    <td class="text-center">{{ $record->blood_pressure_category ?? '' }}</td>
                    <td class="text-center">{{ $record->weight ?? '' }}</td>
                    <td class="text-center">{{ $record->height ?? '' }}</td>
                    <td class="text-center">{{ $record->heart_rate ?? '' }}</td>
                    <td class="text-center">{{ $record->body_temperature ?? '' }}</td>
                    <td class="text-center">{{ $record->respiratory_rate ?? '' }}</td>
                    <td>{{ $record->creator->name ?? 'Tidak diketahui' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>