
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar | Sistem Analisis Kompetensi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-page">

    <main class="login-wrapper register-wrapper">
        <div class="login-glow login-glow-top"></div>
        <div class="login-glow login-glow-bottom"></div>

        <section class="login-content register-content">

            <header class="login-header">
                <div class="login-brand-mark">PM</div>

                <h1>Buat Akun Baru</h1>
                <p>
                    Bergabung dengan Sistem Analisis Kesesuaian Kompetensi Mahasiswa
                </p>
            </header>

            <div class="login-card register-card">

                <div class="login-card-heading">
                    <h2>Informasi Akun</h2>
                    <p>Lengkapi informasi berikut untuk membuat akun mahasiswa.</p>
                </div>

                <form method="POST" action="{{ route('register.store') }}">
                    @csrf

                    <div class="register-grid">

                        <div class="login-field register-field-full">
                            <label for="name">Nama lengkap</label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
                                placeholder="Masukkan nama lengkap"
                                autocomplete="name"
                                required
                                class="@error('name') login-input-error @enderror"
                            >
                            @error('name')
                                <span class="login-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="login-field register-field-full">
                            <label for="email">Email</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="nama@email.com"
                                autocomplete="email"
                                required
                                class="@error('email') login-input-error @enderror"
                            >
                            @error('email')
                                <span class="login-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="login-field">
                            <label for="student_number">NIM <span class="register-optional">Opsional</span></label>
                            <input
                                type="text"
                                id="student_number"
                                name="student_number"
                                value="{{ old('student_number') }}"
                                placeholder="Nomor induk mahasiswa"
                                class="@error('student_number') login-input-error @enderror"
                            >
                            @error('student_number')
                                <span class="login-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="login-field">
                            <label for="study_program">Program studi <span class="register-optional">Opsional</span></label>
                            <input
                                type="text"
                                id="study_program"
                                name="study_program"
                                value="{{ old('study_program') }}"
                                placeholder="Contoh: Informatika"
                                class="@error('study_program') login-input-error @enderror"
                            >
                            @error('study_program')
                                <span class="login-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="login-field register-field-full">
                            <label for="institution">Nama perguruan tinggi <span class="register-optional">Opsional</span></label>
                            <input
                                type="text"
                                id="institution"
                                name="institution"
                                value="{{ old('institution') }}"
                                placeholder="Nama universitas atau perguruan tinggi"
                                class="@error('institution') login-input-error @enderror"
                            >
                            @error('institution')
                                <span class="login-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="login-field">
                            <label for="password">Password</label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Minimal 8 karakter"
                                autocomplete="new-password"
                                required
                                class="@error('password') login-input-error @enderror"
                            >
                            @error('password')
                                <span class="login-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="login-field">
                            <label for="password_confirmation">Konfirmasi password</label>
                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                placeholder="Ulangi password"
                                autocomplete="new-password"
                                required
                            >
                        </div>

                    </div>

                    <button type="submit" class="login-submit register-submit">
                        Buat Akun
                    </button>
                </form>

                <div class="login-footer">
                    <span>Sudah punya akun?</span>
                    <a href="{{ route('login') }}">Masuk di sini</a>
                </div>
            </div>

            <footer class="login-copyright">
                Sistem Analisis Kesesuaian Kompetensi Mahasiswa
            </footer>

        </section>
    </main>

</body>
</html>
