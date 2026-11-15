
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0b1020">
    <meta name="description" content="Platform analisis kesesuaian kompetensi mahasiswa terhadap kebutuhan lowongan kerja menggunakan metode Profile Matching berbasis AI.">

    <title>Kompeten — Analisis Kesesuaian Kompetensi</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .landing-page {
            min-height: 100vh;
            overflow-x: hidden;
            background: #0b1020;
            color: #f8fafc;
        }

        .landing-container {
            width: 100%;
            max-width: 1160px;
            margin: 0 auto;
            padding-right: 28px;
            padding-left: 28px;
        }

        .landing-navbar {
            position: relative;
            z-index: 10;
            border-bottom: 1px solid #293449;
            background: rgb(11 16 32 / 88%);
            backdrop-filter: blur(16px);
        }

        .landing-navbar-inner {
            display: flex;
            min-height: 76px;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .landing-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #f8fafc;
        }

        .landing-brand:hover {
            color: #f8fafc;
        }

        .landing-brand-mark {
            display: flex;
            width: 42px;
            height: 42px;
            align-items: center;
            justify-content: center;
            border: 1px solid #a78bfa;
            border-radius: 11px;
            background: linear-gradient(145deg, #8b5cf6, #6d28d9);
            color: white;
            font-family: var(--font-display);
            font-size: 17px;
            font-weight: 800;
            box-shadow: 0 4px 20px rgb(124 58 237 / 25%);
        }

        .landing-brand-name {
            font-family: var(--font-display);
            font-size: 16px;
            font-weight: 800;
            letter-spacing: .06em;
        }

        .landing-brand-caption {
            margin-top: 2px;
            color: #94a3b8;
            font-size: 10px;
        }

        .landing-nav-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .landing-nav-login,
        .landing-nav-register,
        .landing-cta-primary,
        .landing-cta-secondary {
            display: inline-flex;
            min-height: 42px;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            padding: 0 17px;
            font-size: 12px;
            font-weight: 700;
            transition: .18s ease;
        }

        .landing-nav-login,
        .landing-cta-secondary {
            border: 1px solid #334155;
            background: #111827;
            color: #e2e8f0;
        }

        .landing-nav-login:hover,
        .landing-cta-secondary:hover {
            border-color: #8b5cf6;
            background: #1a2235;
            color: white;
        }

        .landing-nav-register,
        .landing-cta-primary {
            border: 1px solid #8b5cf6;
            background: #7c3aed;
            color: white;
            box-shadow: 0 5px 20px rgb(124 58 237 / 18%);
        }

        .landing-nav-register:hover,
        .landing-cta-primary:hover {
            background: #6d28d9;
            color: white;
            transform: translateY(-1px);
        }

        .landing-hero {
            position: relative;
            padding: 100px 0 90px;
        }

        .landing-hero-glow {
            position: absolute;
            top: 0;
            left: 50%;
            width: 650px;
            height: 420px;
            border-radius: 50%;
            background: #7c3aed;
            opacity: .10;
            filter: blur(140px);
            transform: translateX(-50%);
            pointer-events: none;
        }

        .landing-hero-grid {
            position: relative;
            display: grid;
            grid-template-columns: 1.05fr .95fr;
            align-items: center;
            gap: 65px;
        }

        .landing-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgb(167 139 250 / 25%);
            border-radius: 30px;
            background: rgb(124 58 237 / 10%);
            padding: 7px 12px;
            color: #c4b5fd;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .06em;
        }

        .landing-eyebrow-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #a78bfa;
            box-shadow: 0 0 10px #a78bfa;
        }

        .landing-hero-title {
            margin-top: 23px;
            color: #f8fafc;
            font-family: var(--font-display);
            font-size: clamp(36px, 4.5vw, 55px);
            font-weight: 800;
            letter-spacing: -.055em;
            line-height: 1.13;
        }

        .landing-hero-title span {
            color: #a78bfa;
        }

        .landing-hero-description {
            max-width: 510px;
            margin-top: 22px;
            color: #94a3b8;
            font-size: 14px;
            line-height: 1.9;
        }

        .landing-hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 11px;
            margin-top: 30px;
        }

        .landing-hero-note {
            margin-top: 20px;
            color: #64748b;
            font-size: 11px;
        }

        /* Preview dashboard */
        .landing-preview {
            position: relative;
            border: 1px solid #35405a;
            border-radius: 15px;
            background: #111827;
            padding: 18px;
            box-shadow: 0 25px 80px rgb(0 0 0 / 35%), 0 0 50px rgb(124 58 237 / 9%);
        }

        .landing-preview-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            border-bottom: 1px solid #293449;
            padding-bottom: 15px;
        }

        .landing-preview-title {
            color: #f8fafc;
            font-size: 12px;
            font-weight: 700;
        }

        .landing-preview-label {
            border: 1px solid #334155;
            border-radius: 5px;
            background: #1a2235;
            padding: 5px 8px;
            color: #a78bfa;
            font-size: 9px;
            font-weight: 700;
        }

        .landing-preview-caption {
            margin-top: 15px;
            color: #94a3b8;
            font-size: 10px;
        }

        .landing-score {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 15px;
            border: 1px solid #293449;
            border-radius: 9px;
            background: #0d1526;
            padding: 13px;
        }

        .landing-score-name {
            color: #e2e8f0;
            font-size: 11px;
            font-weight: 700;
        }

        .landing-score-company {
            margin-top: 4px;
            color: #64748b;
            font-size: 9px;
        }

        .landing-score-value {
            color: #c4b5fd;
            font-family: var(--font-display);
            font-size: 20px;
            font-weight: 800;
        }

        .landing-score-unit {
            color: #64748b;
            font-size: 9px;
            text-align: right;
        }

        .landing-progress {
            height: 5px;
            overflow: hidden;
            margin-top: 10px;
            border-radius: 10px;
            background: #293449;
        }

        .landing-progress span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #7c3aed, #a78bfa);
        }

        .landing-preview-footer {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 13px;
        }

        .landing-preview-stat {
            border: 1px solid #293449;
            border-radius: 8px;
            background: #0d1526;
            padding: 12px;
        }

        .landing-preview-stat-label {
            color: #64748b;
            font-size: 9px;
        }

        .landing-preview-stat-value {
            margin-top: 5px;
            color: #e9d5ff;
            font-size: 16px;
            font-weight: 800;
        }

        /* Features */
        .landing-features {
            border-top: 1px solid #293449;
            border-bottom: 1px solid #293449;
            background: #0d1424;
            padding: 75px 0;
        }

        .landing-section-heading {
            max-width: 600px;
            margin: 0 auto 38px;
            text-align: center;
        }

        .landing-section-eyebrow {
            color: #a78bfa;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .15em;
            text-transform: uppercase;
        }

        .landing-section-title {
            margin-top: 12px;
            color: #f8fafc;
            font-family: var(--font-display);
            font-size: clamp(25px, 3vw, 34px);
            font-weight: 800;
            letter-spacing: -.04em;
        }

        .landing-section-description {
            margin-top: 12px;
            color: #94a3b8;
            font-size: 13px;
            line-height: 1.8;
        }

        .landing-feature-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 17px;
        }

        .landing-feature-card {
            border: 1px solid #293449;
            border-radius: 11px;
            background: #111827;
            padding: 23px;
            transition: .2s ease;
        }

        .landing-feature-card:hover {
            border-color: #6d4ca5;
            transform: translateY(-3px);
        }

        .landing-feature-icon {
            display: flex;
            width: 42px;
            height: 42px;
            align-items: center;
            justify-content: center;
            border: 1px solid rgb(167 139 250 / 25%);
            border-radius: 10px;
            background: rgb(124 58 237 / 12%);
            color: #c4b5fd;
            font-size: 17px;
            font-weight: 800;
        }

        .landing-feature-card h3 {
            margin-top: 19px;
            color: #f1f5f9;
            font-family: var(--font-display);
            font-size: 15px;
            font-weight: 700;
        }

        .landing-feature-card p {
            margin-top: 9px;
            color: #94a3b8;
            font-size: 12px;
            line-height: 1.8;
        }

        /* Method section */
        .landing-method {
            padding: 75px 0;
        }

        .landing-method-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 35px;
            border: 1px solid #3b3157;
            border-radius: 13px;
            background: linear-gradient(110deg, #17152b, #111827);
            padding: 32px;
        }

        .landing-method-copy {
            max-width: 650px;
        }

        .landing-method-copy h2 {
            color: #f8fafc;
            font-family: var(--font-display);
            font-size: 22px;
            font-weight: 800;
        }

        .landing-method-copy p {
            margin-top: 10px;
            color: #94a3b8;
            font-size: 12px;
            line-height: 1.9;
        }

        .landing-method-badge {
            flex-shrink: 0;
            border: 1px solid #6d4ca5;
            border-radius: 9px;
            background: rgb(124 58 237 / 12%);
            padding: 14px 18px;
            color: #c4b5fd;
            font-size: 12px;
            font-weight: 800;
            text-align: center;
        }

        .landing-method-badge span {
            display: block;
            margin-top: 4px;
            color: #94a3b8;
            font-size: 9px;
            font-weight: 500;
        }

        /* Footer */
        .landing-footer {
            border-top: 1px solid #293449;
            padding: 25px 0;
        }

        .landing-footer-inner {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            color: #64748b;
            font-size: 10px;
        }

        @media (max-width: 900px) {
            .landing-hero-grid {
                grid-template-columns: 1fr;
                gap: 45px;
            }

            .landing-hero-copy {
                max-width: 680px;
            }

            .landing-preview {
                width: 100%;
                max-width: 560px;
                margin: 0 auto;
            }
        }

        @media (max-width: 650px) {
            .landing-container {
                padding-right: 19px;
                padding-left: 19px;
            }

            .landing-navbar-inner {
                min-height: 68px;
            }

            .landing-brand-caption {
                display: none;
            }

            .landing-nav-actions {
                gap: 7px;
            }

            .landing-nav-login,
            .landing-nav-register {
                min-height: 37px;
                padding: 0 11px;
                font-size: 10px;
            }

            .landing-hero {
                padding: 70px 0 65px;
            }

            .landing-hero-title {
                font-size: 38px;
            }

            .landing-feature-grid {
                grid-template-columns: 1fr;
            }

            .landing-features,
            .landing-method {
                padding: 58px 0;
            }

            .landing-method-box {
                align-items: flex-start;
                flex-direction: column;
                padding: 23px;
            }

            .landing-footer-inner {
                align-items: flex-start;
                flex-direction: column;
            }
        }

        @media (max-width: 380px) {
            .landing-brand-name {
                font-size: 13px;
            }

            .landing-brand-mark {
                width: 36px;
                height: 36px;
            }

            .landing-nav-login,
            .landing-nav-register {
                padding: 0 8px;
                font-size: 9px;
            }

            .landing-hero-title {
                font-size: 33px;
            }
        }
    </style>
</head>

<body class="landing-page">

    <!-- NAVBAR -->
    <nav class="landing-navbar">
        <div class="landing-container landing-navbar-inner">

            <a href="{{ url('/') }}" class="landing-brand">
                <div class="landing-brand-mark">PM</div>
                <div>
                    <div class="landing-brand-name">KOMPETEN</div>
                    <div class="landing-brand-caption">Competency Analysis Platform</div>
                </div>
            </a>

            <div class="landing-nav-actions">
                @auth
                    <a href="{{ url('/dashboard') }}" class="landing-nav-register">
                        Dashboard
                    </a>
                @else
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="landing-nav-login">
                            Masuk
                        </a>
                    @endif

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="landing-nav-register">
                            Daftar Sekarang
                        </a>
                    @endif
                @endauth
            </div>

        </div>
    </nav>

    <!-- HERO -->
    <main>
        <section class="landing-hero">
            <div class="landing-hero-glow"></div>

            <div class="landing-container landing-hero-grid">

                <div class="landing-hero-copy">
                    <div class="landing-eyebrow">
                        <span class="landing-eyebrow-dot"></span>
                        STUDENT COMPETENCY ANALYSIS
                    </div>

                    <h1 class="landing-hero-title">
                        Kenali Potensimu.
                        <br>
                        Temukan <span>Peluang Kariermu.</span>
                    </h1>

                    <p class="landing-hero-description">
                        Platform untuk menganalisis kesesuaian kompetensi mahasiswa
                        dengan kebutuhan lowongan kerja. Kenali kemampuan yang sudah
                        dimiliki, temukan kesenjangan kompetensi, dan susun langkah
                        pengembangan karier secara lebih terarah.
                    </p>

                    <div class="landing-hero-actions">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="landing-cta-primary">
                                Buka Dashboard
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="landing-cta-primary">
                                Mulai Sekarang
                            </a>
                            <a href="{{ route('login') }}" class="landing-cta-secondary">
                                Masuk ke Akun
                            </a>
                        @endauth
                    </div>

                    <p class="landing-hero-note">
                        Analisis kompetensi berbasis Profile Matching dan dukungan AI.
                    </p>
                </div>

                <!-- Dashboard Preview -->
                <div class="landing-preview">
                    <div class="landing-preview-top">
                        <div class="landing-preview-title">Preview Hasil Analisis</div>
                        <div class="landing-preview-label">PROFILE MATCHING</div>
                    </div>

                    <p class="landing-preview-caption">
                        Ilustrasi hasil kesesuaian kompetensi
                    </p>

                    <div class="landing-score">
                        <div>
                            <div class="landing-score-name">Web Developer</div>
                            <div class="landing-score-company">Contoh perusahaan</div>
                        </div>
                        <div>
                            <div class="landing-score-value">85.6</div>
                            <div class="landing-score-unit">Skor ilustrasi</div>
                        </div>
                    </div>
                    <div class="landing-progress">
                        <span style="width: 85.6%"></span>
                    </div>

                    <div class="landing-score">
                        <div>
                            <div class="landing-score-name">UI/UX Designer</div>
                            <div class="landing-score-company">Contoh perusahaan</div>
                        </div>
                        <div>
                            <div class="landing-score-value">76.2</div>
                            <div class="landing-score-unit">Skor ilustrasi</div>
                        </div>
                    </div>
                    <div class="landing-progress">
                        <span style="width: 76.2%"></span>
                    </div>

                    <div class="landing-preview-footer">
                        <div class="landing-preview-stat">
                            <div class="landing-preview-stat-label">Metode Penilaian</div>
                            <div class="landing-preview-stat-value">60 : 40</div>
                        </div>
                        <div class="landing-preview-stat">
                            <div class="landing-preview-stat-label">Dukungan Sistem</div>
                            <div class="landing-preview-stat-value">AI</div>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- FEATURES -->
        <section class="landing-features">
            <div class="landing-container">

                <div class="landing-section-heading">
                    <div class="landing-section-eyebrow">Fitur Utama</div>
                    <h2 class="landing-section-title">
                        Satu Platform untuk Memahami Kompetensimu
                    </h2>
                    <p class="landing-section-description">
                        Membantu mahasiswa memahami posisi kompetensinya dan
                        mengidentifikasi hal yang perlu dikembangkan untuk menghadapi
                        kebutuhan dunia kerja.
                    </p>
                </div>

                <div class="landing-feature-grid">

                    <article class="landing-feature-card">
                        <div class="landing-feature-icon">01</div>
                        <h3>Profil Kompetensi</h3>
                        <p>
                            Kelola data diri, CV, hard skill, dan kompetensi yang
                            dimiliki sebagai dasar proses analisis.
                        </p>
                    </article>

                    <article class="landing-feature-card">
                        <div class="landing-feature-icon">02</div>
                        <h3>Analisis Profile Matching</h3>
                        <p>
                            Bandingkan tingkat kompetensi mahasiswa dengan
                            persyaratan lowongan kerja menggunakan perhitungan gap.
                        </p>
                    </article>

                    <article class="landing-feature-card">
                        <div class="landing-feature-icon">03</div>
                        <h3>Rekomendasi Pengembangan</h3>
                        <p>
                            Identifikasi kesenjangan kompetensi dan dapatkan arahan
                            pengembangan berdasarkan hasil analisis.
                        </p>
                    </article>

                </div>
            </div>
        </section>

        <!-- METHOD -->
        <section class="landing-method">
            <div class="landing-container">
                <div class="landing-method-box">

                    <div class="landing-method-copy">
                        <div class="landing-section-eyebrow">Metode Analisis</div>
                        <h2>Profile Matching dengan Dukungan AI</h2>
                        <p>
                            Sistem menggunakan metode Profile Matching untuk
                            menghitung kesesuaian kompetensi berdasarkan selisih
                            antara profil mahasiswa dan profil kebutuhan pekerjaan.
                            AI digunakan sebagai pendukung pengolahan informasi
                            dan penyusunan rekomendasi pengembangan.
                        </p>
                    </div>

                    <div class="landing-method-badge">
                        Core Factor 60%
                        <span>Secondary Factor 40%</span>
                    </div>

                </div>
            </div>
        </section>
    </main>

    <!-- FOOTER -->
    <footer class="landing-footer">
        <div class="landing-container landing-footer-inner">
            <span>© {{ date('Y') }} Kompeten — Sistem Analisis Kesesuaian Kompetensi Mahasiswa</span>
            <span>Platform Analisis Kompetensi dan Karier</span>
        </div>
    </footer>

</body>
</html>
