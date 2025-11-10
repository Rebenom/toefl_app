/*
 * File: js/test.js
 * Deskripsi: Otak dari halaman pengerjaan tes.
 * Mengatur timer, navigasi, dan tampilan soal.
 */

document.addEventListener('DOMContentLoaded', () => {

    // --- 1. Ambil Elemen DOM ---
    const timerDisplay = document.getElementById('timer-display');
    const sectionTitle = document.getElementById('section-title');
    const mediaContainer = document.getElementById('media-container');
    const questionContainer = document.getElementById('question-container');
    const questionText = document.getElementById('question-text');
    const optionsContainer = document.getElementById('options-container');
    const questionList = document.getElementById('question-list');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const submitBtn = document.getElementById('submit-btn');
    const testForm = document.getElementById('test-form');

    // --- 2. Inisialisasi State (Status) ---
    let flatQuestions = [];     // Array 1-dimensi berisi semua soal
    let userAnswers = {};       // Objek untuk menyimpan jawaban: { q_id: 'a' }
    let currentIndex = 0;       // Indeks soal yang sedang ditampilkan
    let currentMedia = { type: null, content: null }; // Cache media
    let timerInterval;

    /**
     * Inisialisasi Tes
     */
    function initTest() {
        // 2a. Ratakan (flatten) data soal dari PHP
        // Kita ubah dari {listening: [...], ...} menjadi [...]
        let globalIndex = 0;
        ['listening', 'structure', 'reading'].forEach(section => {
            allQuestionsData[section].forEach(q => {
                q.globalIndex = globalIndex++;
                flatQuestions.push(q);
            });
        });

        // 2b. Render daftar nomor soal di sidebar
        renderQuestionList();

        // 2c. Tampilkan soal pertama
        showQuestion(currentIndex);

        // 2d. Mulai timer
        startTimer(TOTAL_TIME_SECONDS);

        // 2e. Pasang event listeners
        prevBtn.addEventListener('click', showPreviousQuestion);
        nextBtn.addEventListener('click', showNextQuestion);
        
        // Peringatan sebelum submit
        submitBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const confirmed = confirm('Apakah Anda yakin ingin menyelesaikan tes ini?');
            if (confirmed) {
                testForm.submit();
            }
        });
    }

    /**
     * Menampilkan soal berdasarkan indeks
     * @param {number} index - Indeks soal di array `flatQuestions`
     */
    function showQuestion(index) {
        // 1. Dapatkan data soal
        const q = flatQuestions[index];
        if (!q) return; // Jaga-jaga
        
        currentIndex = index; // Update state

        // 2. Update Judul Section
        sectionTitle.textContent = q.section.charAt(0).toUpperCase() + q.section.slice(1);

        // 3. Update Media (Audio/Passage)
        // Kita cek agar tidak me-render ulang media yang sama
        if (q.audio) {
            if (currentMedia.type !== 'audio' || currentMedia.content !== q.audio) {
                currentMedia = { type: 'audio', content: q.audio };
                mediaContainer.innerHTML = `<audio controls src="${q.audio}" id="audio-player"></audio>`;
            }
        } else if (q.passage) {
            if (currentMedia.type !== 'passage' || currentMedia.content !== q.passage) {
                currentMedia = { type: 'passage', content: q.passage };
                mediaContainer.innerHTML = `<div class="passage-container">${q.passage}</div>`;
            }
        } else {
            // Jika soal structure, kosongkan media
            currentMedia = { type: null, content: null };
            mediaContainer.innerHTML = '';
        }

        // 4. Update Teks Soal
        questionText.textContent = `${index + 1}. ${q.text}`;

        // 5. Render Opsi Jawaban
        optionsContainer.innerHTML = ''; // Kosongkan dulu
        ['a', 'b', 'c', 'd'].forEach(opt => {
            const optionText = q.options[opt];
            
            // Buat elemen radio button
            const input = document.createElement('input');
            input.type = 'radio';
            input.id = `q${q.id}_opt${opt}`;
            // PENTING: name="answers[question_id]" 
            // Ini agar PHP bisa membacanya sebagai array
            input.name = `answers[${q.id}]`; 
            input.value = opt;
            
            // Cek apakah jawaban sudah ada di 'userAnswers'
            if (userAnswers[q.id] === opt) {
                input.checked = true;
            }
            
            // Buat label untuk opsi
            const label = document.createElement('label');
            label.htmlFor = `q${q.id}_opt${opt}`;
            label.textContent = `(${opt.toUpperCase()}) ${optionText}`;
            
            const optionDiv = document.createElement('div');
            optionDiv.className = 'option-item';
            optionDiv.appendChild(input);
            optionDiv.appendChild(label);
            
            optionsContainer.appendChild(optionDiv);
        });

        // 6. Update status tombol navigasi
        prevBtn.disabled = (index === 0);
        nextBtn.disabled = (index === flatQuestions.length - 1);
        
        // 7. Update highlight di daftar soal
        updateQuestionListHighlight();
        
        // 8. Tambahkan listener untuk menyimpan jawaban
        optionsContainer.addEventListener('change', (e) => {
            if (e.target.type === 'radio') {
                const questionId = e.target.name.match(/\[(\d+)\]/)[1];
                userAnswers[questionId] = e.target.value;
                
                // Tandai di sidebar bahwa soal sudah dijawab
                document.getElementById(`q-nav-${questionId}`).classList.add('answered');
            }
        });
    }

    /**
     * Render daftar nomor soal di sidebar
     */
    function renderQuestionList() {
        questionList.innerHTML = '';
        flatQuestions.forEach((q, index) => {
            const qItem = document.createElement('div');
            qItem.id = `q-nav-${q.id}`;
            qItem.className = 'q-item';
            qItem.textContent = index + 1;
            
            // Tambahkan listener agar bisa diklik untuk navigasi
            qItem.addEventListener('click', () => {
                showQuestion(index);
            });
            
            questionList.appendChild(qItem);
        });
    }

    /**
     * Meng-highlight nomor soal yang aktif di sidebar
     */
    function updateQuestionListHighlight() {
        // Hapus highlight lama
        const oldActive = document.querySelector('.q-item.active');
        if (oldActive) {
            oldActive.classList.remove('active');
        }
        
        // Tambah highlight baru
        const currentQuestionId = flatQuestions[currentIndex].id;
        const currentNavItem = document.getElementById(`q-nav-${currentQuestionId}`);
        if (currentNavItem) {
            currentNavItem.classList.add('active');
        }
    }

    // --- Fungsi Navigasi ---
    function showNextQuestion() {
        if (currentIndex < flatQuestions.length - 1) {
            showQuestion(currentIndex + 1);
        }
    }
    function showPreviousQuestion() {
        if (currentIndex > 0) {
            showQuestion(currentIndex - 1);
        }
    }

    // --- Fungsi Timer ---
    function startTimer(duration) {
        let remainingTime = duration;

        timerInterval = setInterval(() => {
            remainingTime--;

            const hours = Math.floor(remainingTime / 3600);
            const minutes = Math.floor((remainingTime % 3600) / 60);
            const seconds = remainingTime % 60;

            // Format: HH:MM:SS
            timerDisplay.textContent = 
                `${String(hours).padStart(2, '0')}:` +
                `${String(minutes).padStart(2, '0')}:` +
                `${String(seconds).padStart(2, '0')}`;

            if (remainingTime <= 0) {
                clearInterval(timerInterval);
                alert('Waktu Habis! Tes akan dikumpulkan secara otomatis.');
                testForm.submit();
            }
        }, 1000); // Update setiap 1 detik
    }

    // --- Jalankan Inisialisasi Tes ---
    initTest();
});