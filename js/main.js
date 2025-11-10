// Tunggu hingga seluruh konten halaman (DOM) dimuat
document.addEventListener('DOMContentLoaded', () => {

    // --- Ambil Elemen Form ---
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');

    // --- Validasi Form Login ---
    if (loginForm) {
        loginForm.addEventListener('submit', (event) => {
            // Hentikan pengiriman form bawaan
            event.preventDefault(); 
            
            // Hapus error lama (jika ada)
            clearErrorMessages(loginForm);

            // Ambil nilai
            const username = loginForm.elements['username'].value.trim();
            const password = loginForm.elements['password'].value.trim();

            let isValid = true;

            if (username === '' || password === '') {
                showErrorMessage(loginForm, 'Semua field wajib diisi.');
                isValid = false;
            }

            // Jika semua valid, kirim form
            if (isValid) {
                loginForm.submit();
            }
        });
    }

    // --- Validasi Form Register ---
    if (registerForm) {
        registerForm.addEventListener('submit', (event) => {
            // Hentikan pengiriman form bawaan
            event.preventDefault(); 

            // Hapus error lama
            clearErrorMessages(registerForm);

            // Ambil nilai
            const username = registerForm.elements['username'].value.trim();
            const email = registerForm.elements['email'].value.trim();
            const password = registerForm.elements['password'].value.trim();
            const confirm_password = registerForm.elements['confirm_password'].value.trim();

            let isValid = true;

            // 1. Cek field kosong
            if (username === '' || email === '' || password === '' || confirm_password === '') {
                showErrorMessage(registerForm, 'Semua field wajib diisi.');
                isValid = false;
            }
            // 2. Cek format email
            else if (!isValidEmail(email)) {
                showErrorMessage(registerForm, 'Format email tidak valid.');
                isValid = false;
            }
            // 3. Cek panjang password
            else if (password.length < 6) {
                showErrorMessage(registerForm, 'Password minimal harus 6 karakter.');
                isValid = false;
            }
            // 4. Cek password cocok
            else if (password !== confirm_password) {
                showErrorMessage(registerForm, 'Password dan Konfirmasi Password tidak cocok.');
                isValid = false;
            }

            // Jika semua valid, kirim form
            if (isValid) {
                registerForm.submit();
            }
        });
    }

    // --- Fungsi Bantuan (Helper Functions) ---

    /**
     * Menampilkan pesan error di dalam form
     * @param {HTMLElement} form - Elemen form yang divalidasi
     * @param {string} message - Pesan error yang akan ditampilkan
     */
    function showErrorMessage(form, message) {
        // Buat elemen div untuk pesan error
        const errorDiv = document.createElement('div');
        errorDiv.className = 'msg error-msg js-error-msg'; // Beri class untuk styling
        errorDiv.textContent = message;

        // Masukkan pesan error sebelum elemen pertama di dalam form
        form.prepend(errorDiv);
    }

    /**
     * Menghapus semua pesan error (dari validasi JS) di dalam form
     * @param {HTMLElement} form - Elemen form
     */
    function clearErrorMessages(form) {
        const errorMessages = form.querySelectorAll('.js-error-msg');
        errorMessages.forEach(msg => msg.remove());
    }

    /**
     * Mengecek format email dengan Regex sederhana
     * @param {string} email - Alamat email
     * @returns {boolean} - True jika valid
     */
    function isValidEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }

});