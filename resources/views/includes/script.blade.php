<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gender Chart
        const genderCtx = document.getElementById('genderChart').getContext('2d');
        new Chart(genderCtx, {
            type: 'doughnut',
            data: {
                labels: ['Laki-laki', 'Perempuan'],
                datasets: [{
                    data: [{{ $genderStats['male'] ?? 0 }}, {{ $genderStats['female'] ?? 0 }}],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 99, 132, 0.7)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
            }
        });

        // Age Chart
        const ageCtx = document.getElementById('ageChart').getContext('2d');
        new Chart(ageCtx, {
            type: 'bar',
            data: {
                labels: ['0-5', '6-12', '13-17', '18-30', '31-50', '>50'],
                datasets: [{
                    label: 'Jumlah',
                    data: [
                        {{ $ageStats['0-5'] ?? 0 }}, 
                        {{ $ageStats['6-12'] ?? 0 }}, 
                        {{ $ageStats['13-17'] ?? 0 }}, 
                        {{ $ageStats['18-30'] ?? 0 }}, 
                        {{ $ageStats['31-50'] ?? 0 }}, 
                        {{ $ageStats['>50'] ?? 0 }}
                    ],
                    backgroundColor: 'rgba(75, 192, 192, 0.7)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Education Chart
        const educationCtx = document.getElementById('educationChart').getContext('2d');
        new Chart(educationCtx, {
            type: 'pie',
            data: {
                labels: {!! json_encode(isset($educationStats) ? array_keys($educationStats) : []) !!},
                datasets: [{
                    data: {!! json_encode(isset($educationStats) ? array_values($educationStats) : []) !!},
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(255, 159, 64, 0.7)',
                        'rgba(199, 199, 199, 0.7)',
                        'rgba(83, 102, 255, 0.7)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
            }
        });

        // Health Chart
        const healthCtx = document.getElementById('healthChart').getContext('2d');
        new Chart(healthCtx, {
            type: 'bar',
            data: {
                labels: ['Pernah TBC', 'Hipertensi', 'Batuk Kronis', 'Gangguan Jiwa', 'Pasung'],
                datasets: [{
                    label: 'Jumlah Kasus',
                    data: [
                        {{ $stats['tbc_count'] }}, 
                        {{ $stats['hypertension_count'] }}, 
                        {{ $stats['chronic_cough_count'] }}, 
                        {{ $stats['mental_illness_count'] }}, 
                        {{ $stats['restrained_count'] }}
                    ],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
    // Water Distribution Chart
        const waterCtx = document.getElementById('waterChart').getContext('2d');
        new Chart(waterCtx, {
            type: 'pie',
            data: {
                labels: ['Memiliki Air Bersih', 'Tidak Memiliki Air Bersih', 'Air Bersih Terlindungi', 'Air Bersih Tidak Terlindungi'],
                datasets: [{
                    data: [
                        {{ $sanitationStats['clean_water_count'] }},
                        {{ $stats['families'] - $sanitationStats['clean_water_count'] }},
                        {{ $sanitationStats['protected_water_count'] }},
                        {{ $sanitationStats['clean_water_count'] - $sanitationStats['protected_water_count'] }}
                    ],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(255, 206, 86, 0.7)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
            }
        });

        // Toilet Distribution Chart
        const toiletCtx = document.getElementById('toiletChart').getContext('2d');
        new Chart(toiletCtx, {
            type: 'pie',
            data: {
                labels: ['Memiliki Jamban', 'Tidak Memiliki Jamban', 'Jamban Saniter', 'Jamban Tidak Saniter'],
                datasets: [{
                    data: [
                        {{ $sanitationStats['toilet_count'] }},
                        {{ $stats['families'] - $sanitationStats['toilet_count'] }},
                        {{ $sanitationStats['sanitary_toilet_count'] }},
                        {{ $sanitationStats['toilet_count'] - $sanitationStats['sanitary_toilet_count'] }}
                    ],
                    backgroundColor: [
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(255, 159, 64, 0.7)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
            }
        });
        const mobileMenuButton = document.querySelector('.mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

        // Script Untuk Togle Penduduk Terakhir di Input -->
        document.addEventListener('DOMContentLoaded', function() {
            const tableToggle = document.getElementById('tableToggle');
            const tableContainer = document.getElementById('tableContainer');
            
            tableToggle.addEventListener('change', function() {
                if (this.checked) {
                    tableContainer.style.maxHeight = tableContainer.scrollHeight + 'px';
                    tableContainer.style.opacity = '1';
                    tableContainer.style.visibility = 'visible';
                } else {
                    tableContainer.style.maxHeight = '0';
                    tableContainer.style.opacity = '0';
                    tableContainer.style.visibility = 'hidden';
                }
            });

            // Initialize table state
            tableContainer.style.maxHeight = tableContainer.scrollHeight + 'px';
            tableContainer.style.opacity = '1';
            tableContainer.style.visibility = 'visible';
        });

        <!-- Tambahkan script untuk chart di bagian scripts -->
        // Maternal Health Chart
        const maternalCtx = document.getElementById('maternalChart').getContext('2d');
        new Chart(maternalCtx, {
            type: 'bar',
            data: {
                labels: ['Menggunakan KB', 'Tidak Menggunakan KB', 'Ibu Hamil', 'Bersalin di Faskes'],
                datasets: [{
                    label: 'Jumlah',
                    data: [
                        {{ $maternalStats['kb_count'] }},
                        {{ $maternalStats['no_kb_count'] }},
                        {{ $maternalStats['pregnant_count'] }},
                        {{ $maternalStats['health_facility_birth_count'] }}
                    ],
                    backgroundColor: [
                        'rgba(233, 30, 99, 0.7)',
                        'rgba(244, 67, 54, 0.7)',
                        'rgba(156, 39, 176, 0.7)',
                        'rgba(76, 175, 80, 0.7)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Child Health Chart
        const childCtx = document.getElementById('childChart').getContext('2d');
        new Chart(childCtx, {
            type: 'bar',
            data: {
                labels: ['ASI Eksklusif', 'Imunisasi Lengkap', 'Pemantauan Pertumbuhan'],
                datasets: [{
                    label: 'Jumlah',
                    data: [
                        {{ $childStats['exclusive_breastfeeding_count'] }},
                        {{ $childStats['complete_immunization_count'] }},
                        {{ $childStats['growth_monitoring_count'] }}
                    ],
                    backgroundColor: [
                        'rgba(33, 150, 243, 0.7)',
                        'rgba(76, 175, 80, 0.7)',
                        'rgba(63, 81, 181, 0.7)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Inisialisasi chart JKN by Village
        const jknByVillageCtx = document.getElementById('jknByVillageChart').getContext('2d');
        const jknByVillageChart = new Chart(jknByVillageCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(isset($jknByVillage) ? $jknByVillage->pluck('name') : []) !!},
                datasets: [{
                    label: 'Memiliki JKN',
                    data: {!! json_encode(isset($jknByVillage) ? $jknByVillage->pluck('jkn_count') : []) !!},
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Inisialisasi chart JKN Overview
        const jknOverviewCtx = document.getElementById('jknOverviewChart').getContext('2d');
        const jknOverviewChart = new Chart(jknOverviewCtx, {
            type: 'pie',
            data: {
                labels: ['Memiliki JKN', 'Tidak Memiliki JKN'],
                datasets: [{
                    data: [
                        {{ $jknStats['jkn_count'] }}, 
                        {{ $jknStats['members'] - $jknStats['jkn_count'] }}
                    ],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(255, 99, 132, 0.6)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
</script>