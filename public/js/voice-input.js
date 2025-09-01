// // Inisialisasi ketika DOM selesai dimuat dan setelah navigasi Livewire
// document.addEventListener('DOMContentLoaded', initVoiceInput);
// document.addEventListener('livewire:navigated', initVoiceInput);

// // Hook ke Livewire untuk memastikan skrip berjalan setelah update komponen
// if (window.Livewire) {
//     window.Livewire.hook('commit', () => {
//         setTimeout(initVoiceInput, 200);
//     });
// }

// function initVoiceInput() {
//     console.log("Initializing voice input for Filament v3...");
    
//     // Identifikasi semua jenis input yang didukung
//     const textInputs = document.querySelectorAll('.fi-input, input[type="text"], input[type="number"], input[type="email"], textarea');
//     const selectInputs = document.querySelectorAll('select');
//     const toggleInputs = document.querySelectorAll('.fi-toggle input[type="checkbox"]');
    
//     console.log("Found form elements:", {
//         text: textInputs.length,
//         select: selectInputs.length,
//         toggle: toggleInputs.length
//     });
    
//     // Setup semua jenis input
//     setupInputType(textInputs, 'text');
//     setupInputType(selectInputs, 'select');
//     setupInputType(toggleInputs, 'toggle');
// }

// function setupInputType(inputs, type) {
//     inputs.forEach(input => {
//         // Hindari menambahkan tombol lebih dari sekali
//         const parentEl = input.parentNode;
//         if (!parentEl || parentEl.querySelector('.voice-btn')) return;
        
//         try {
//             // Buat tombol mikrofon
//             const button = document.createElement('button');
//             button.type = 'button';
//             button.className = 'voice-btn';
//             button.innerHTML = '🎤';
//             button.style.position = 'absolute';
//             button.style.right = '10px';
//             button.style.top = '50%';
//             button.style.transform = 'translateY(-50%)';
//             button.style.background = '#2563EB';
//             button.style.color = 'white';
//             button.style.width = '30px';
//             button.style.height = '30px';
//             button.style.borderRadius = '50%';
//             button.style.border = 'none';
//             button.style.display = 'flex';
//             button.style.alignItems = 'center';
//             button.style.justifyContent = 'center';
//             button.style.zIndex = '10';
//             button.style.cursor = 'pointer';
            
//             // Cari kontainer yang tepat
//             let container = null;
            
//             // Untuk Filament v3
//             container = input.closest('.fi-input-wrp') || 
//                        input.closest('.fi-select-wrp') || 
//                        input.closest('.fi-toggle-wrp') ||
//                        input.parentNode;
            
//             // Tambahkan style position:relative ke container jika belum ada
//             if (container && window.getComputedStyle(container).position !== 'relative') {
//                 container.style.position = 'relative';
//             }
            
//             // Tambahkan tombol ke container
//             if (container) {
//                 container.appendChild(button);
                
//                 // Listener untuk tombol
//                 button.addEventListener('click', function(e) {
//                     e.preventDefault();
//                     e.stopPropagation();
//                     startVoiceRecognition(input, type, button);
//                     return false;
//                 });
//             }
//         } catch (error) {
//             console.error("Error setting up voice input:", error);
//         }
//     });
// }

// function startVoiceRecognition(inputField, type, button) {
//     // Periksa dukungan browser
//     if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
//         alert('Maaf, browser tidak mendukung fitur pengenalan suara.');
//         return;
//     }
    
//     // Inisialisasi API
//     const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
//     const recognition = new SpeechRecognition();
    
//     // Konfigurasi
//     recognition.lang = 'id-ID';
//     recognition.interimResults = false;
//     recognition.maxAlternatives = 1;
//     recognition.continuous = false;
    
//     // Tampilkan indikator
//     const originalColor = button.style.background;
//     button.style.background = '#EF4444';
//     button.classList.add('animate-pulse');
    
//     // Event handlers untuk debugging
//     recognition.onaudiostart = () => console.log("Mulai rekam audio");
//     recognition.onspeechstart = () => console.log("Suara terdeteksi");
//     recognition.onsoundend = () => console.log("Tidak ada suara terdeteksi");
//     recognition.onspeechend = () => console.log("Berhenti mendeteksi suara");
    
//     // Event saat hasil terdeteksi
//     recognition.onresult = function(event) {
//         const speechResult = event.results[0][0].transcript;
//         console.log("Hasil ucapan:", speechResult);
        
//         if (type === 'select') {
//             processSelectInput(inputField, speechResult);
//         } else if (type === 'toggle') {
//             processToggleInput(inputField, speechResult);
//         } else {
//             // Untuk input teks
//             inputField.value = speechResult;
            
//             // Trigger events
//             inputField.dispatchEvent(new Event('input', { bubbles: true }));
//             inputField.dispatchEvent(new Event('change', { bubbles: true }));
            
//             // Jika menggunakan Livewire
//             if (window.Livewire) {
//                 const wireEl = inputField.closest('[wire\\:id]');
//                 if (wireEl) {
//                     const model = inputField.getAttribute('wire:model') || 
//                                  inputField.getAttribute('wire:model.live') || 
//                                  inputField.getAttribute('wire:model.defer');
                    
//                     if (model) {
//                         try {
//                             window.Livewire.find(wireEl.getAttribute('wire:id')).$wire.set(model, speechResult);
//                         } catch (e) {
//                             console.error("Error updating Livewire:", e);
//                         }
//                     }
//                 }
//             }
//         }
//     };
    
//     // Event saat selesai atau error
//     recognition.onend = function() {
//         button.style.background = originalColor;
//         button.classList.remove('animate-pulse');
//         console.log("Pengenalan suara berakhir");
//     };
    
//     recognition.onerror = function(event) {
//         button.style.background = originalColor;
//         button.classList.remove('animate-pulse');
//         console.error('Error pengenalan suara:', event.error);
        
//         if (event.error === 'not-allowed') {
//             alert('Izin mikrofon ditolak. Silakan berikan izin mikrofon untuk menggunakan fitur ini.');
//         } else if (event.error === 'no-speech') {
//             alert('Tidak ada suara yang terdeteksi. Silakan coba lagi.');
//         } else {
//             alert(`Error: ${event.error}. Silakan coba lagi.`);
//         }
//     };
    
//     // Mulai mendengarkan
//     recognition.start();
//     console.log("Mulai mendengarkan...");
// }

// function processSelectInput(selectField, speechResult) {
//     const speechLower = speechResult.toLowerCase();
//     let foundMatch = false;
    
//     console.log("Memproses input select:", speechLower);
//     console.log("Options:", Array.from(selectField.options).map(o => o.text));
    
//     // Loop semua opsi
//     Array.from(selectField.options).forEach(option => {
//         const optionText = option.text.toLowerCase();
        
//         // Cek kecocokan
//         if (optionText.includes(speechLower) || speechLower.includes(optionText)) {
//             console.log(`Match found: "${optionText}" ↔ "${speechLower}"`);
//             selectField.value = option.value;
//             foundMatch = true;
            
//             // Trigger event
//             selectField.dispatchEvent(new Event('change', { bubbles: true }));
            
//             // Update Livewire jika tersedia
//             if (window.Livewire) {
//                 const wireEl = selectField.closest('[wire\\:id]');
//                 if (wireEl) {
//                     const model = selectField.getAttribute('wire:model') || 
//                                 selectField.getAttribute('wire:model.live') || 
//                                 selectField.getAttribute('wire:model.defer');
                    
//                     if (model) {
//                         try {
//                             window.Livewire.find(wireEl.getAttribute('wire:id')).$wire.set(model, selectField.value);
//                         } catch (e) {
//                             console.error("Error updating Livewire for select:", e);
//                         }
//                     }
//                 }
//             }
//         }
//     });
    
//     if (!foundMatch) {
//         console.log("Tidak ditemukan pilihan yang cocok dengan:", speechLower);
//         alert('Tidak ditemukan pilihan yang sesuai. Silakan coba lagi dengan menyebutkan nama opsi yang tersedia.');
//     }
// }

// function processToggleInput(inputField, speechResult) {
//     const speechLower = speechResult.toLowerCase();
//     console.log("Memproses input toggle:", speechLower);
    
//     // Kata-kata positif dalam bahasa Indonesia
//     const positiveWords = ['ya', 'benar', 'iya', 'betul', 'setuju', 'ok', 'oke', 'positif', 'tentu'];
    
//     // Kata-kata negatif
//     const negativeWords = ['tidak', 'bukan', 'belum', 'jangan', 'negatif', 'enggak', 'nggak', 'gak', 'tdk'];
    
//     let newState = null;
    
//     // Cek kata positif
//     for (const word of positiveWords) {
//         if (speechLower.includes(word)) {
//             console.log(`Kata positif terdeteksi: "${word}"`);
//             newState = true;
//             break;
//         }
//     }
    
//     // Cek kata negatif
//     if (newState === null) {
//         for (const word of negativeWords) {
//             if (speechLower.includes(word)) {
//                 console.log(`Kata negatif terdeteksi: "${word}"`);
//                 newState = false;
//                 break;
//             }
//         }
//     }
    
//     // Update toggle jika ada kecocokan
//     if (newState !== null) {
//         console.log(`Mengubah toggle ke: ${newState}`);
//         inputField.checked = newState;
        
//         // Trigger event
//         inputField.dispatchEvent(new Event('change', { bubbles: true }));
        
//         // Update Livewire jika tersedia
//         if (window.Livewire) {
//             const wireEl = inputField.closest('[wire\\:id]');
//             if (wireEl) {
//                 const model = inputField.getAttribute('wire:model') || 
//                            inputField.getAttribute('wire:model.live') || 
//                            inputField.getAttribute('wire:model.defer');
                
//                 if (model) {
//                     try {
//                         window.Livewire.find(wireEl.getAttribute('wire:id')).$wire.set(model, newState);
//                     } catch (e) {
//                         console.error("Error updating Livewire for toggle:", e);
//                     }
//                 }
//             }
//         }
//     } else {
//         console.log("Tidak dapat mengenali kata ya/tidak");
//         alert('Tidak dapat mengenali "ya" atau "tidak". Silakan coba lagi dengan menyebutkan "ya" atau "tidak" dengan jelas.');
//     }
// }