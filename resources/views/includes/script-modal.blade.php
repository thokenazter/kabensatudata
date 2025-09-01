@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('memberDetailModal');
        const modalContent = document.getElementById('modalContent');
        const modalTitle = document.getElementById('modalTitle');
        const closeModal = document.getElementById('closeModal');
        const modalOverlay = document.getElementById('modalOverlay');
        const detailButtons = document.querySelectorAll('.show-member-detail');

        // Fungsi untuk membuka modal
        function openModal() {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        // Fungsi untuk menutup modal
        function closeModalFunction() {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        // Tambahkan event listener ke setiap tombol detail
        detailButtons.forEach(button => {
            button.addEventListener('click', function() {
                const memberId = this.getAttribute('data-member-id');
                openModal();
                
                // Tampilkan loading state
                modalContent.innerHTML = `
                    <div class="flex justify-center items-center h-52">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
                    </div>
                `;

                // Ambil data anggota keluarga via AJAX
                fetch(`/api/family-members/${memberId}`)
                    .then(response => response.json())
                    .then(data => {
                        // Update modal content dengan data
                        renderMemberDetail(data);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        modalContent.innerHTML = `
                            <div class="text-center text-red-500">
                                <p>Terjadi kesalahan saat memuat data.</p>
                                <p class="text-sm mt-2">Silakan coba lagi nanti.</p>
                            </div>
                        `;
                    });
            });
        });

        // Fungsi untuk menampilkan detail anggota keluarga
        function renderMemberDetail(member) {
            // Update judul modal
            modalTitle.textContent = member.name;

            // Format data sesuai status login
            const isLoggedIn = {{ auth()->check() ? 'true' : 'false' }};
            const nameDisplay = isLoggedIn ? member.name : member.name.substring(0, 3) + '***';
            const nikDisplay = isLoggedIn ? member.nik : 'XXXX-XXXX-XXXX-' + member.nik.substring(member.nik.length - 4);
            
            // Render content HTML
            modalContent.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Informasi Dasar -->
                    <div>
                        <h4 class="font-medium text-gray-900 mb-3">Informasi Dasar</h4>
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm font-medium text-gray-500">Nama:</span>
                                <div class="mt-1 ${!isLoggedIn ? 'blur-sm hover:blur-none transition-all cursor-pointer' : ''}">
                                    ${nameDisplay}
                                </div>
                            </div>
                            
                            <div>
                                <span class="text-sm font-medium text-gray-500">NIK:</span>
                                <div class="mt-1 ${!isLoggedIn ? 'blur-sm hover:blur-none transition-all cursor-pointer' : ''}">
                                    ${nikDisplay}
                                </div>
                            </div>
                            
                            <div>
                                <span class="text-sm font-medium text-gray-500">Hubungan dalam Keluarga:</span>
                                <div class="mt-1">${member.relationship}</div>
                            </div>
                            
                            <div>
                                <span class="text-sm font-medium text-gray-500">Tempat Lahir:</span>
                                <div class="mt-1 ${!isLoggedIn ? 'blur-sm hover:blur-none transition-all cursor-pointer' : ''}">
                                    ${isLoggedIn ? member.birth_place : member.birth_place.substring(0, 3) + '***'}
                                </div>
                            </div>
                            
                            <div>
                                <span class="text-sm font-medium text-gray-500">Tanggal Lahir:</span>
                                <div class="mt-1 ${!isLoggedIn ? 'blur-sm hover:blur-none transition-all cursor-pointer' : ''}">
                                    ${isLoggedIn ? member.birth_date : (member.birth_date ? member.birth_date.substring(0, 4) : '')}
                                </div>
                            </div>
                            
                            <div>
                                <span class="text-sm font-medium text-gray-500">Umur:</span>
                                <div class="mt-1">${member.age} tahun</div>
                            </div>
                            
                            <div>
                                <span class="text-sm font-medium text-gray-500">Jenis Kelamin:</span>
                                <div class="mt-1">${member.gender}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Informasi Tambahan -->
                    <div>
                        <h4 class="font-medium text-gray-900 mb-3">Informasi Tambahan</h4>
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm font-medium text-gray-500">Agama:</span>
                                <div class="mt-1">${member.religion || '-'}</div>
                            </div>
                            
                            <div>
                                <span class="text-sm font-medium text-gray-500">Pendidikan:</span>
                                <div class="mt-1">${member.education || '-'}</div>
                            </div>
                            
                            <div>
                                <span class="text-sm font-medium text-gray-500">Status Pernikahan:</span>
                                <div class="mt-1">${member.marital_status || '-'}</div>
                            </div>
                            
                            <div>
                                <span class="text-sm font-medium text-gray-500">Pekerjaan:</span>
                                <div class="mt-1 ${!isLoggedIn ? 'blur-sm hover:blur-none transition-all cursor-pointer' : ''}">
                                    ${isLoggedIn ? (member.occupation || '-') : (member.occupation ? member.occupation.substring(0, 3) + '***' : '-')}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Informasi Kesehatan -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h4 class="font-medium text-gray-900 mb-3">Informasi Kesehatan</h4>
                    
                    <div class="${!isLoggedIn ? 'blur-sm hover:blur-none cursor-pointer transition-all relative' : ''}">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Status JKN -->
                            <div class="bg-gray-50 overflow-hidden shadow-sm rounded-lg">
                                <div class="px-4 py-4">
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Status JKN
                                    </dt>
                                    <dd class="mt-1 text-lg font-semibold">
                                        ${member.has_jkn 
                                            ? '<span class="text-green-600">Ada</span>' 
                                            : '<span class="text-red-600">Tidak Ada</span>'}
                                    </dd>
                                </div>
                            </div>
                            
                            <!-- Status Merokok -->
                            <div class="bg-gray-50 overflow-hidden shadow-sm rounded-lg">
                                <div class="px-4 py-4">
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Status Merokok
                                    </dt>
                                    <dd class="mt-1 text-lg font-semibold">
                                        ${member.is_smoker 
                                            ? '<span class="text-yellow-600">Ya</span>' 
                                            : '<span class="text-green-600">Tidak</span>'}
                                    </dd>
                                </div>
                            </div>
                            
                            <!-- Status TB -->
                            <div class="bg-gray-50 overflow-hidden shadow-sm rounded-lg">
                                <div class="px-4 py-4">
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Status TB
                                    </dt>
                                    <dd class="mt-1 text-lg font-semibold">
                                        ${member.has_tuberculosis 
                                            ? '<span class="text-red-600">Positif</span>' 
                                            : '<span class="text-green-600">Negatif</span>'}
                                    </dd>
                                </div>
                            </div>
                        </div>
                        
                        ${!isLoggedIn ? `
                            <div class="absolute inset-0 flex items-center justify-center">
                                <a href="/admin" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Login untuk melihat data kesehatan
                                </a>
                            </div>
                        ` : ''}
                    </div>
                </div>
                
                ${isLoggedIn ? `
                    <div class="mt-6 text-right">
                        <a href="/admin/resources/family-members/${member.id}/edit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                            Edit Data
                        </a>
                    </div>
                ` : ''}
            `;
        }

        // Event listeners untuk menutup modal
        closeModal.addEventListener('click', closeModalFunction);
        modalOverlay.addEventListener('click', closeModalFunction);
        
        // Tutup modal dengan tombol Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModalFunction();
            }
        });
    });
</script>
@endpush