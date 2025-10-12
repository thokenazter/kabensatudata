@extends('layouts.pegawai')

@section('pegawai-content')
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
        @if($isSuper)
            <div class="card p-5">
                <div class="label">Total Pegawai</div>
                <div class="text-3xl font-semibold">{{ number_format($stats['total_pegawai']) }}</div>
            </div>
            <div class="card p-5">
                <div class="label">Total Dokumen</div>
                <div class="text-3xl font-semibold">{{ number_format($stats['total_dokumen']) }}</div>
            </div>
            <div class="card p-5">
                <div class="label">Ringkasan Jenis Dokumen</div>
                <div class="text-sm mt-2 text-slate-300">SK: {{ $stats['dokumen_sk'] }} | KTP: {{ $stats['dokumen_ktp'] }}</div>
            </div>
        @else
            <div class="card p-5 md:col-span-2">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xl font-semibold">Selamat datang</div>
                        <div class="text-slate-300">Kelola profil dan dokumen Anda.</div>
                    </div>
                    @if(!$myPegawai)
                        <a class="btn btn-primary" href="{{ route('pegawai.employees.create') }}">Lengkapi Profil</a>
                    @else
                        <a class="btn" href="{{ route('pegawai.employees.edit', $myPegawai) }}">Edit Profil</a>
                    @endif
                </div>
                <div class="mt-4 grid sm:grid-cols-3 gap-3">
                    <div class="card p-4">
                        <div class="label">Dokumen Saya</div>
                        <div class="text-2xl font-semibold">{{ $stats['dokumen_saya'] }}</div>
                    </div>
                    @if($myPegawai)
                    <div class="card p-4 flex items-center justify-center">
                        <a href="{{ route('pegawai.employees.documents.index', $myPegawai) }}" class="btn btn-primary">Kelola Dokumen</a>
                    </div>
                    @endif
                </div>
            </div>
            <div class="card p-5">
                <div class="label">Aksi Cepat</div>
                <div class="mt-3 flex flex-col gap-2">
                    <a class="btn" href="{{ $myPegawai ? route('pegawai.employees.documents.index', $myPegawai) : route('pegawai.employees.create') }}">Upload Dokumen</a>
                    <a class="btn" href="{{ $myPegawai ? route('pegawai.employees.edit', $myPegawai) : route('pegawai.employees.create') }}">Perbarui Profil</a>
                </div>
            </div>
        @endif
    </div>
    @if($isSuper && $charts)
        <div class="mt-6 grid lg:grid-cols-3 gap-3 sm:gap-4">
            <!-- Gender Donut -->
            <div class="card p-5">
                <div class="label mb-2">Komposisi Gender</div>
                <div id="chart-gender" class="h-56 sm:h-64"></div>
            </div>
            <!-- Education Bars -->
            <div class="card p-5 lg:col-span-2">
                <div class="label mb-2">Pendidikan Terakhir</div>
                <div id="chart-education" class="h-56 sm:h-64"></div>
            </div>
            <!-- Profession Bars -->
            <div class="card p-5 lg:col-span-3">
                <div class="label mb-2">Profesi</div>
                <div id="chart-profession" class="h-64 sm:h-72"></div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function(){
                const charts = @json($charts);
                // Gender donut
                const genderOpts = {
                    chart: { type: 'donut', height: 260, background: 'transparent', foreColor: '#cbd5e1' },
                    labels: ['Laki-laki','Perempuan'],
                    series: [charts.gender.L, charts.gender.P],
                    colors: ['#38bdf8','#f472b6'],
                    legend: { position: 'bottom' },
                    dataLabels: { enabled: true }
                };
                if (document.querySelector('#chart-gender')) new ApexCharts(document.querySelector('#chart-gender'), genderOpts).render();

                // Education bar
                const eduLabels = Object.keys(charts.education);
                const eduSeries = Object.values(charts.education);
                const eduOpts = {
                    chart: { type: 'bar', height: 260, background:'transparent', foreColor:'#cbd5e1' },
                    series: [{ name: 'Jumlah', data: eduSeries }],
                    xaxis: { categories: eduLabels },
                    plotOptions: { bar: { borderRadius: 6 }},
                    colors: ['#22d3ee']
                };
                if (document.querySelector('#chart-education')) new ApexCharts(document.querySelector('#chart-education'), eduOpts).render();

                // Profession horizontal bar
                const profLabels = Object.keys(charts.profession);
                const profSeries = Object.values(charts.profession);
                const profOpts = {
                    chart: { type: 'bar', height: 360, background:'transparent', foreColor:'#cbd5e1' },
                    series: [{ name: 'Jumlah', data: profSeries }],
                    xaxis: { categories: profLabels },
                    plotOptions: { bar: { horizontal: true, borderRadius: 6 }},
                    colors: ['#a855f7']
                };
                if (document.querySelector('#chart-profession')) new ApexCharts(document.querySelector('#chart-profession'), profOpts).render();
            });
        </script>
    @endif
@endsection
