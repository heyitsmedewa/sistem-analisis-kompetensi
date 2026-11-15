
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk | Sistem Analisis Kompetensi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-page">

    <main class="login-wrapper">
        <div class="login-glow login-glow-top"></div>
        <div class="login-glow login-glow-bottom"></div>

        <section class="login-content">

            <header class="login-header">
                <div class="login-brand-mark">PM</div>

                <h1>Selamat Datang</h1>
                <p>Sistem Analisis Kesesuaian Kompetensi Mahasiswa</p>
            </header>

            <div class="login-card">
                <div class="login-card-heading">
                    <h2>Masuk ke akun</h2>
                    <p>Masukkan email dan password untuk melanjutkan.</p>
                </div>

                @if ($errors->any())
                    <div class="login-alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}">
                    @csrf

                    <div class="login-field">
                        <label for="email">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="nama@email.com"
                            autocomplete="username"
                            required
                            autofocus
                            class="@error('email') login-input-error @enderror"
                        >

                        @error('email')
                            <span class="login-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="login-field">
                        <label for="password">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                            required
                        >
                    </div>

                    <label class="login-remember">
                        <input
                            type="checkbox"
                            id="remember"
                            name="remember"
                            value="1"
                        >
                        <span>Ingat saya</span>
                    </label>

                    <button type="submit" class="login-submit">
                        Masuk ke Akun
                    </button>
                </form>

                <div class="login-footer">
                    <span>Belum punya akun?</span>
                    <a href="{{ route('register') }}">Daftar di sini</a>
                </div>
            </div>

            <footer class="login-copyright">
                Sistem Analisis Kesesuaian Kompetensi Mahasiswa
            </footer>

        </section>
    </main>

</body>
</html>
