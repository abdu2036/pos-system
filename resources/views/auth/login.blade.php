<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
    <title>تسجيل الدخول - منظومة المبيعات</title>
    
    <!-- Tabler CSS Core & Flags -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler-flags.rtl.min.css">
    
    <style>
        @import url('https://rsms.me/inter/inter.css');
        :root {
            --tblr-font-sans-serif: 'Inter', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
        }
        body {
            font-feature-settings: "cv03", "cv04", "cv11";
        }
    </style>
</head>
<body class="d-flex flex-column bg-light">
    <div class="page page-center">
        <div class="container container-tight py-4">
            <div class="text-center mb-4">
                <a href="#" class="navbar-brand navbar-brand-autodark">
                    <span class="text-primary fw-bold fs-1">منظومة المبيعات</span>
                </a>
            </div>

            <div class="card card-md border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h2 text-center mb-4">تسجيل الدخول إلى حسابك</h2>

                    <!-- حالة الجلسة (Session Status) -->
                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    <form method="POST" action="{{ route('login') }}" autocomplete="off" novalidate>
                        @csrf

                        <!-- البريد الإلكتروني -->
                        <div class="mb-3">
                            <label class="form-label">البريد الإلكتروني</label>
                            <input type="email" 
                                   name="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   placeholder="your@email.com" 
                                   value="{{ old('email') }}" 
                                   required 
                                   autofocus 
                                   autocomplete="username">
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- كلمة المرور -->
                        <div class="mb-2">
                            <label class="form-label">
                                كلمة المرور
                                @if (Route::has('password.request'))
                                    <span class="form-label-description">
                                        <a href="{{ route('password.request') }}">نسيت كلمة المرور؟</a>
                                    </span>
                                @endif
                            </label>
                            <input type="password" 
                                   name="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   placeholder="••••••••" 
                                   required 
                                   autocomplete="current-password">
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <!-- خيار تذكرني -->
                        <div class="mb-3">
                            <label class="form-check">
                                <input type="checkbox" name="remember" class="form-check-input" id="remember_me"/>
                                <span class="form-check-label">تذكرني على هذا الجهاز</span>
                            </label>
                        </div>

                        <!-- زر الإرسال -->
                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary w-100 py-2">تسجيل الدخول</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="text-center text-muted mt-3">
                جميع الحقوق محفوظة &copy; {{ date('Y') }} منظومة المبيعات وإدارة الفروع.
            </div>
        </div>
    </div>

    <!-- Tabler Core JS -->
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/js/tabler.min.js" defer></script>
</body>
</html>