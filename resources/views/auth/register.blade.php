<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
    <title>إنشاء حساب جديد - منظومة المبيعات</title>
    
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
                    <h2 class="h2 text-center mb-4">إنشاء حساب جديد</h2>

                    <form method="POST" action="{{ route('register') }}" autocomplete="off" novalidate>
                        @csrf

                        <!-- الاسم -->
                        <div class="mb-3">
                            <label class="form-label">الاسم الكامل</label>
                            <input type="text" 
                                   name="name" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   placeholder="أدخل اسمك الكامل" 
                                   value="{{ old('name') }}" 
                                   required 
                                   autofocus 
                                   autocomplete="name">
                            <x-input-error :messages="$errors->get('name')" class="mt-2 text-danger" />
                        </div>

                        <!-- البريد الإلكتروني -->
                        <div class="mb-3">
                            <label class="form-label">البريد الإلكتروني</label>
                            <input type="email" 
                                   name="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   placeholder="your@email.com" 
                                   value="{{ old('email') }}" 
                                   required 
                                   autocomplete="username">
                            <x-input-error :messages="$errors->get('email')" class="mt-2 text-danger" />
                        </div>

                        <!-- كلمة المرور -->
                        <div class="mb-3">
                            <label class="form-label">كلمة المرور</label>
                            <input type="password" 
                                   name="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   placeholder="••••••••" 
                                   required 
                                   autocomplete="new-password">
                            <x-input-error :messages="$errors->get('password')" class="mt-2 text-danger" />
                        </div>

                        <!-- تأكيد كلمة المرور -->
                        <div class="mb-3">
                            <label class="form-label">تأكيد كلمة المرور</label>
                            <input type="password" 
                                   name="password_confirmation" 
                                   class="form-control @error('password_confirmation') is-invalid @enderror" 
                                   placeholder="••••••••" 
                                   required 
                                   autocomplete="new-password">
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-danger" />
                        </div>

                        <!-- زر الإرسال -->
                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary w-100 py-2">إنشاء الحساب</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- رابط تسجيل الدخول -->
            <div class="text-center text-muted mt-3">
                لديك حساب بالفعل؟ <a href="{{ route('login') }}" class="fw-bold text-primary">تسجيل الدخول</a>
            </div>
        </div>
    </div>

    <!-- Tabler Core JS -->
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/js/tabler.min.js" defer></script>
</body>
</html>