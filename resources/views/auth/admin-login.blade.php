<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin Login - {{ config('app.name', 'Laravel') }}</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet">

    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #eef2ff;
            --text-dark: #111827;
            --text-muted: #6b7280;
            --border: #e5e7eb;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            color: var(--text-dark);
        }

        .login-page {
            min-height: 100vh;
            display: flex;
        }

        /* ========================================
           LEFT PANEL
        ======================================== */

        .login-brand-panel {
            width: 48%;
            min-height: 100vh;
            position: relative;
            overflow: hidden;

            background:
                radial-gradient(circle at 20% 20%,
                    rgba(255, 255, 255, 0.15),
                    transparent 30%),
                radial-gradient(circle at 80% 80%,
                    rgba(255, 255, 255, 0.10),
                    transparent 30%),
                linear-gradient(145deg,
                    #4f46e5 0%,
                    #6366f1 45%,
                    #7c3aed 100%);

            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px;
        }

        .brand-content {
            max-width: 520px;
            position: relative;
            z-index: 2;
        }

        .brand-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 58px;
            height: 58px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.20);
            backdrop-filter: blur(10px);
            margin-bottom: 28px;
            font-size: 26px;
        }

        .brand-title {
            font-size: clamp(2.2rem, 4vw, 3.5rem);
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -1.5px;
            margin-bottom: 20px;
        }

        .brand-description {
            font-size: 1.05rem;
            line-height: 1.8;
            color: rgba(255, 255, 255, 0.78);
            margin-bottom: 35px;
        }

        .brand-features {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .brand-feature {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.95rem;
        }

        .brand-feature-icon {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.12);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Decorative circles */

        .decoration {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.06);
        }

        .decoration-1 {
            width: 350px;
            height: 350px;
            top: -150px;
            right: -120px;
        }

        .decoration-2 {
            width: 500px;
            height: 500px;
            bottom: -280px;
            left: -220px;
        }

        .decoration-3 {
            width: 180px;
            height: 180px;
            bottom: 100px;
            right: 50px;
        }

        /* ========================================
           RIGHT LOGIN PANEL
        ======================================== */

        .login-form-panel {
            flex: 1;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 30px;
            background: #ffffff;
        }

        .login-wrapper {
            width: 100%;
            max-width: 430px;
        }

        .mobile-logo {
            display: none;
        }

        .login-heading {
            margin-bottom: 32px;
        }

        .login-heading h1 {
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: -0.5px;
            margin-bottom: 8px;
            color: #111827;
        }

        .login-heading p {
            margin: 0;
            color: var(--text-muted);
            font-size: 0.92rem;
        }

        /* Alerts */

        .login-alert {
            border: 0;
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 22px;
            font-size: 0.88rem;
        }

        .login-alert.alert-danger {
            background: #fef2f2;
            color: #991b1b;
        }

        .login-alert.alert-success {
            background: #f0fdf4;
            color: #166534;
        }

        .login-alert ul {
            margin: 0;
            padding-left: 20px;
        }

        /* Form */

        .form-label {
            font-size: 0.86rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .input-group-custom {
            position: relative;
        }

        .input-group-custom .form-control {
            height: 52px;
            border: 1px solid #d1d5db;
            border-radius: 11px !important;
            padding-left: 46px;
            padding-right: 46px;
            font-size: 0.92rem;
            color: #111827;
            background: #fff;
            transition: all 0.2s ease;
        }

        .input-group-custom .form-control::placeholder {
            color: #9ca3af;
        }

        .input-group-custom .form-control:hover {
            border-color: #9ca3af;
        }

        .input-group-custom .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.10);
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            z-index: 5;
            font-size: 17px;
            pointer-events: none;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: #9ca3af;
            z-index: 5;
            width: 32px;
            height: 32px;
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .password-toggle:hover {
            background: #f3f4f6;
            color: #4b5563;
        }

        .invalid-feedback {
            font-size: 0.78rem;
            margin-top: 6px;
        }

        /* Options */

        .login-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 6px;
            margin-bottom: 25px;
        }

        .form-check-label {
            font-size: 0.85rem;
            color: #4b5563;
        }

        .form-check-input {
            width: 17px;
            height: 17px;
            margin-top: 0;
            border-color: #d1d5db;
        }

        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .forgot-password {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--primary-dark);
            text-decoration: none;
        }

        .forgot-password:hover {
            color: #4338ca;
            text-decoration: underline;
        }

        /* Login Button */

        .btn-login {
            height: 52px;
            width: 100%;
            border: 0;
            border-radius: 11px;
            background: linear-gradient(135deg,
                    var(--primary),
                    var(--primary-dark));
            color: #fff;
            font-size: 0.92rem;
            font-weight: 600;
            box-shadow: 0 6px 18px rgba(79, 70, 229, 0.20);
            transition: all 0.2s ease;
        }

        .btn-login:hover {
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 9px 24px rgba(79, 70, 229, 0.28);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login:disabled {
            opacity: 0.7;
            transform: none;
            box-shadow: none;
        }

        /* Footer */

        .login-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #f0f0f0;
        }

        .login-footer p {
            margin: 0;
            color: #6b7280;
            font-size: 0.84rem;
        }

        .login-footer a {
            color: var(--primary-dark);
            font-weight: 600;
            text-decoration: none;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        .copyright {
            text-align: center;
            margin-top: 25px;
            color: #9ca3af;
            font-size: 0.75rem;
        }

        /* Loading */

        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
            border-width: 2px;
        }

        /* ========================================
           RESPONSIVE
        ======================================== */

        @media (max-width: 991.98px) {
            .login-brand-panel {
                width: 42%;
                padding: 40px;
            }

            .brand-title {
                font-size: 2.3rem;
            }

            .brand-description {
                font-size: 0.95rem;
            }
        }

        @media (max-width: 767.98px) {
            .login-page {
                display: block;
            }

            .login-brand-panel {
                display: none;
            }

            .login-form-panel {
                min-height: 100vh;
                padding: 35px 22px;
            }

            .mobile-logo {
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: 35px;
            }

            .mobile-logo-icon {
                width: 45px;
                height: 45px;
                border-radius: 12px;
                background: var(--primary-light);
                color: var(--primary);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 20px;
            }

            .mobile-logo-name {
                font-size: 1rem;
                font-weight: 700;
                color: #111827;
            }

            .login-heading h1 {
                font-size: 1.65rem;
            }
        }

        @media (max-width: 400px) {
            .login-form-panel {
                padding: 28px 18px;
            }

            .login-options {
                align-items: flex-start;
                gap: 12px;
                flex-direction: column;
            }
        }
    </style>
</head>

<body>

    <div class="login-page">

        <!-- =========================================
         BRAND PANEL
    ========================================== -->

        <div class="login-brand-panel">

            <div class="decoration decoration-1"></div>
            <div class="decoration decoration-2"></div>
            <div class="decoration decoration-3"></div>

            <div class="brand-content">

                <div class="brand-logo">
                    <i class="bi bi-shield-lock"></i>
                </div>

                <h2 class="brand-title">
                    {{ config('app.name', 'Laravel') }}
                    <br>
                    Administration
                </h2>

                <p class="brand-description">
                    Manage your platform, monitor activity, and keep everything
                    running smoothly from one secure administration dashboard.
                </p>

                <div class="brand-features">

                    <div class="brand-feature">
                        <span class="brand-feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </span>
                        Secure administrator access
                    </div>

                    <div class="brand-feature">
                        <span class="brand-feature-icon">
                            <i class="bi bi-speedometer2"></i>
                        </span>
                        Powerful management dashboard
                    </div>

                    <div class="brand-feature">
                        <span class="brand-feature-icon">
                            <i class="bi bi-activity"></i>
                        </span>
                        Real-time platform monitoring
                    </div>

                </div>

            </div>
        </div>


        <!-- =========================================
         LOGIN PANEL
    ========================================== -->

        <div class="login-form-panel">

            <div class="login-wrapper">

                <!-- Mobile Logo -->

                <div class="mobile-logo">

                    <div class="mobile-logo-icon">
                        <i class="bi bi-shield-lock"></i>
                    </div>

                    <div class="mobile-logo-name">
                        {{ config('app.name', 'Laravel') }} Admin
                    </div>

                </div>


                <!-- Heading -->

                <div class="login-heading">

                    <h1>Welcome back</h1>

                    <p>
                        Sign in to your administrator account to continue.
                    </p>

                </div>


                <!-- Validation Errors -->

                @if ($errors->any())

                    <div class="alert alert-danger login-alert">

                        <div class="d-flex gap-2">

                            <i class="bi bi-exclamation-circle-fill"></i>

                            <div>
                                <strong>Unable to sign in</strong>

                                <ul class="mt-1">

                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach

                                </ul>
                            </div>

                        </div>

                    </div>

                @endif


                <!-- Session Status -->

                @if (session('status'))
                    <div class="alert alert-success login-alert">

                        <div class="d-flex align-items-center gap-2">

                            <i class="bi bi-check-circle-fill"></i>

                            <span>{{ session('status') }}</span>

                        </div>

                    </div>
                @endif


                <!-- Login Form -->

                <form method="POST" action="{{ route('admin.login') }}" id="loginForm">

                    @csrf


                    <!-- Email -->

                    <div class="mb-4">

                        <label for="email" class="form-label">
                            Email address
                        </label>

                        <div class="input-group-custom">

                            <i class="bi bi-envelope input-icon"></i>

                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                id="email" name="email" value="{{ old('email') }}"
                                placeholder="admin@example.com" required autofocus autocomplete="email">

                        </div>

                        @error('email')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    <!-- Password -->

                    <div class="mb-3">

                        <label for="password" class="form-label">
                            Password
                        </label>

                        <div class="input-group-custom">

                            <i class="bi bi-lock input-icon"></i>

                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                id="password" name="password" placeholder="Enter your password" required
                                autocomplete="current-password">

                            <button type="button" class="password-toggle" id="togglePassword"
                                aria-label="Show password">
                                <i class="bi bi-eye" id="passwordIcon"></i>
                            </button>

                        </div>

                        @error('password')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    <!-- Remember / Forgot -->

                    <div class="login-options">

                        <div class="form-check">

                            <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                {{ old('remember') ? 'checked' : '' }}>

                            <label class="form-check-label" for="remember">
                                Remember me
                            </label>

                        </div>

                        {{-- <a href="{{ route('admin.password.request') }}" class="forgot-password"> --}}
                        <a href="#" class="forgot-password">
                            Forgot password?
                        </a>

                    </div>


                    <!-- Submit -->

                    <button type="submit" class="btn btn-login" id="loginBtn">

                        <span id="loginText">
                            <i class="bi bi-box-arrow-in-right me-1"></i>
                            Sign in
                        </span>

                        <span id="loginLoading" class="d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>

                            Signing in...
                        </span>

                    </button>

                </form>


                <!-- Footer -->

                {{-- <div class="login-footer">

                    <p>
                        Not an administrator?
                        <a href="{{ route('login') }}">
                            Customer Login
                        </a>
                    </p>

                </div> --}}

                <div class="copyright">

                    &copy; {{ date('Y') }}
                    {{ config('app.name', 'Laravel 12') }}.
                    All rights reserved.

                </div>

            </div>

        </div>

    </div>


    <!-- Bootstrap JS -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const form = document.getElementById('loginForm');

            const btn = document.getElementById('loginBtn');

            const loginText = document.getElementById('loginText');

            const loginLoading = document.getElementById('loginLoading');

            const password = document.getElementById('password');

            const togglePassword = document.getElementById('togglePassword');

            const passwordIcon = document.getElementById('passwordIcon');


            /*
            |--------------------------------------------------------------------------
            | Password Visibility
            |--------------------------------------------------------------------------
            */

            togglePassword.addEventListener('click', function() {

                const isPassword = password.type === 'password';

                password.type = isPassword ?
                    'text' :
                    'password';

                passwordIcon.className = isPassword ?
                    'bi bi-eye-slash' :
                    'bi bi-eye';

                togglePassword.setAttribute(
                    'aria-label',
                    isPassword ?
                    'Hide password' :
                    'Show password'
                );

            });


            /*
            |--------------------------------------------------------------------------
            | Login Loading State
            |--------------------------------------------------------------------------
            */

            form.addEventListener('submit', function(event) {

                const email = document
                    .getElementById('email')
                    .value
                    .trim();

                const passwordValue = password
                    .value
                    .trim();


                if (!email || !passwordValue) {

                    event.preventDefault();

                    return;

                }


                btn.disabled = true;

                loginText.classList.add('d-none');

                loginLoading.classList.remove('d-none');

            });


            /*
            |--------------------------------------------------------------------------
            | Remove validation error while typing
            |--------------------------------------------------------------------------
            */

            document
                .querySelectorAll('.form-control')
                .forEach(function(input) {

                    input.addEventListener('input', function() {

                        this.classList.remove('is-invalid');

                        const feedback = this
                            .closest('.mb-4, .mb-3')
                            ?.querySelector('.invalid-feedback');

                        if (feedback) {
                            feedback.remove();
                        }

                    });

                });

        });
    </script>

</body>

</html>
