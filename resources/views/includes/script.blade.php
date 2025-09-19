@php
    // Provide safe defaults so views that don't define these variables won't error
    $genderStats = $genderStats ?? ['male' => 0, 'female' => 0];
    $ageStats = $ageStats ?? ['0-5' => 0, '6-12' => 0, '13-17' => 0, '18-30' => 0, '31-50' => 0, '>50' => 0];
    $educationStats = $educationStats ?? [];
    $sanitationStats = $sanitationStats ?? [
        'clean_water_count' => 0,
        'protected_water_count' => 0,
        'toilet_count' => 0,
        'sanitary_toilet_count' => 0,
    ];
    $maternalStats = $maternalStats ?? [
        'kb_count' => 0,
        'no_kb_count' => 0,
        'pregnant_count' => 0,
        'health_facility_birth_count' => 0,
    ];
    $childStats = $childStats ?? [
        'exclusive_breastfeeding_count' => 0,
        'complete_immunization_count' => 0,
        'growth_monitoring_count' => 0,
    ];
    $jknStats = $jknStats ?? ['jkn_count' => 0, 'members' => 0];
    $jknByVillage = $jknByVillage ?? collect([]);
    $stats = $stats ?? [
        'families' => 0,
        'tbc_count' => 0,
        'hypertension_count' => 0,
        'chronic_cough_count' => 0,
        'mental_illness_count' => 0,
        'restrained_count' => 0,
    ];
    $canViewSensitiveHealth = auth()->check() && auth()->user()->hasAnyRole(['nakes', 'super_admin']);
    $sanitizedStats = $stats;
    if (!$canViewSensitiveHealth) {
        $sanitizedStats['tbc_count'] = 0;
        $sanitizedStats['hypertension_count'] = 0;
    }
@endphp
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gender Chart
        const genderEl = document.getElementById('genderChart');
        if (genderEl) new Chart(genderEl.getContext('2d'), {
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
        const ageEl = document.getElementById('ageChart');
        if (ageEl) new Chart(ageEl.getContext('2d'), {
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
        const educationEl = document.getElementById('educationChart');
        if (educationEl) new Chart(educationEl.getContext('2d'), {
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
        const healthEl = document.getElementById('healthChart');
        if (healthEl) new Chart(healthEl.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Pernah TBC', 'Hipertensi', 'Batuk Kronis', 'Gangguan Jiwa', 'Pasung'],
                datasets: [{
                    label: 'Jumlah Kasus',
                data: [
                        {{ $sanitizedStats['tbc_count'] ?? 0 }}, 
                        {{ $sanitizedStats['hypertension_count'] ?? 0 }}, 
                        {{ $sanitizedStats['chronic_cough_count'] ?? 0 }}, 
                        {{ $sanitizedStats['mental_illness_count'] ?? 0 }}, 
                        {{ $sanitizedStats['restrained_count'] ?? 0 }}
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
        const waterEl = document.getElementById('waterChart');
        if (waterEl) new Chart(waterEl.getContext('2d'), {
            type: 'pie',
            data: {
                labels: ['Memiliki Air Bersih', 'Tidak Memiliki Air Bersih', 'Air Bersih Terlindungi', 'Air Bersih Tidak Terlindungi'],
                datasets: [{
                    data: [
                        {{ $sanitationStats['clean_water_count'] ?? 0 }},
                        {{ ($stats['families'] ?? 0) - ($sanitationStats['clean_water_count'] ?? 0) }},
                        {{ $sanitationStats['protected_water_count'] ?? 0 }},
                        {{ ($sanitationStats['clean_water_count'] ?? 0) - ($sanitationStats['protected_water_count'] ?? 0) }}
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
        const toiletEl = document.getElementById('toiletChart');
        if (toiletEl) new Chart(toiletEl.getContext('2d'), {
            type: 'pie',
            data: {
                labels: ['Memiliki Jamban', 'Tidak Memiliki Jamban', 'Jamban Saniter', 'Jamban Tidak Saniter'],
                datasets: [{
                    data: [
                        {{ $sanitationStats['toilet_count'] ?? 0 }},
                        {{ ($stats['families'] ?? 0) - ($sanitationStats['toilet_count'] ?? 0) }},
                        {{ $sanitationStats['sanitary_toilet_count'] ?? 0 }},
                        {{ ($sanitationStats['toilet_count'] ?? 0) - ($sanitationStats['sanitary_toilet_count'] ?? 0) }}
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
        if (mobileMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
            });
        }

        // Script Untuk Togle Penduduk Terakhir di Input -->
        document.addEventListener('DOMContentLoaded', function() {
            const tableToggle = document.getElementById('tableToggle');
            const tableContainer = document.getElementById('tableContainer');
            if (tableToggle && tableContainer) {
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
            }
        });

        <!-- Tambahkan script untuk chart di bagian scripts -->
        // Maternal Health Chart
        const maternalEl = document.getElementById('maternalChart');
        if (maternalEl) new Chart(maternalEl.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Menggunakan KB', 'Tidak Menggunakan KB', 'Ibu Hamil', 'Bersalin di Faskes'],
                datasets: [{
                    label: 'Jumlah',
                    data: [
                        {{ $maternalStats['kb_count'] ?? 0 }},
                        {{ $maternalStats['no_kb_count'] ?? 0 }},
                        {{ $maternalStats['pregnant_count'] ?? 0 }},
                        {{ $maternalStats['health_facility_birth_count'] ?? 0 }}
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
        const childEl = document.getElementById('childChart');
        if (childEl) new Chart(childEl.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['ASI Eksklusif', 'Imunisasi Lengkap', 'Pemantauan Pertumbuhan'],
                datasets: [{
                    label: 'Jumlah',
                    data: [
                        {{ $childStats['exclusive_breastfeeding_count'] ?? 0 }},
                        {{ $childStats['complete_immunization_count'] ?? 0 }},
                        {{ $childStats['growth_monitoring_count'] ?? 0 }}
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
        const jknByVillageEl = document.getElementById('jknByVillageChart');
        if (jknByVillageEl) new Chart(jknByVillageEl.getContext('2d'), {
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
        const jknOverviewEl = document.getElementById('jknOverviewChart');
        if (jknOverviewEl) new Chart(jknOverviewEl.getContext('2d'), {
            type: 'pie',
            data: {
                labels: ['Memiliki JKN', 'Tidak Memiliki JKN'],
                datasets: [{
                    data: [
                        {{ $jknStats['jkn_count'] ?? 0 }}, 
                        {{ ($jknStats['members'] ?? 0) - ($jknStats['jkn_count'] ?? 0) }}
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
