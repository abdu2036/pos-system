<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
    <title>{{ $title ?? 'منظومة المبيعات' }}</title>
    
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
<body class="layout-fluid">
    <div class="page">
        <!-- 1. السايدبار الجانبي (Sidebar) -->
<!-- 1. السايدبار الجانبي (Sidebar) -->
<aside class="navbar navbar-vertical navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <h1 class="navbar-brand navbar-brand-autodark">
            <a href="{{ route('dashboard') }}" class="text-white text-decoration-none fw-bold fs-2">
                منظومة المبيعات
            </a>
        </h1>
        <div class="collapse navbar-collapse show" id="sidebar-menu">
            <ul class="navbar-nav pt-lg-3">

                <!-- الرئيسية -->
                <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <a class="nav-link text-white-50 text-white-hover" href="{{ route('dashboard') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M5 12l-2 0l9 -9l9 9l-2 0" />
                                <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" />
                                <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" />
                            </svg>
                        </span>
                        <span class="nav-link-title text-white">الرئيسية</span>
                    </a>
                </li>

                <!-- نقطة البيع POS -->
                <li class="nav-item {{ request()->routeIs('pos.*') ? 'active' : '' }}">
                    <a class="nav-link text-success fw-bold" href="{{ route('pos.index') }}">
                        <span class="nav-link-icon text-success d-md-none d-lg-inline-block">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                <path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                <path d="M17 17h-11v-14h-2" />
                                <path d="M6 5l14 1l-1 7h-13" />
                            </svg>
                        </span>
                        <span class="nav-link-title">نقطة البيع (POS)</span>
                    </a>
                </li>

                <!-- التصنيفات -->
                <li class="nav-item {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <a class="nav-link text-white-50 text-white-hover" href="{{ route('categories.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M7.859 6h-2.834a2 2 0 0 0 -2 2v2.834a2 2 0 0 0 .586 1.414l7.172 7.172a2 2 0 0 0 2.828 0l2.828 -2.828a2 2 0 0 0 0 -2.828l-7.172 -7.172a2 2 0 0 0 -1.414 -.586z" />
                                <path d="M17.5 7.5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                            </svg>
                        </span>
                        <span class="nav-link-title text-white">التصنيفات</span>
                    </a>
                </li>

                <!-- المنتجات والمخزون -->
                <li class="nav-item {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <a class="nav-link text-white-50 text-white-hover" href="{{ route('products.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9z" />
                                <path d="M12 12l8 -4.5" />
                                <path d="M12 12l0 9" />
                                <path d="M12 12l-8 -4.5" />
                                <path d="M16 5.25l-8 4.5" />
                            </svg>
                        </span>
                        <span class="nav-link-title text-white">المنتجات والمخزون</span>
                    </a>
                </li>

                <!-- إدارة الفروع -->
                <li class="nav-item {{ request()->routeIs('branches.*') ? 'active' : '' }}">
                    <a class="nav-link text-white-50 text-white-hover" href="{{ route('branches.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M3 21l18 0" />
                                <path d="M3 7l18 0" />
                                <path d="M3 7l1 14l16 0l1 -14" />
                                <path d="M9 7v-4a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4" />
                            </svg>
                        </span>
                        <span class="nav-link-title text-white">إدارة الفروع</span>
                    </a>
                </li>

                <!-- إعدادات الشركة -->
                <li class="nav-item {{ request()->routeIs('company-settings.*') ? 'active' : '' }}">
                    <a class="nav-link text-white-50 text-white-hover" href="{{ route('company-settings.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" />
                                <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                            </svg>
                        </span>
                        <span class="nav-link-title text-white">إعدادات الشركة</span>
                    </a>
                </li>

            </ul>
        </div>
    </div>
</aside>

        <div class="page-wrapper">
            <!-- 2. الناف بار العلوي (Header) -->
            <header class="navbar navbar-expand-md navbar-light d-none d-lg-flex d-print-none border-bottom">
                <div class="container-xl">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="navbar-nav flex-row order-md-last ms-auto">
                        <div class="nav-item dropdown">
                            <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
                                <span class="avatar avatar-sm" style="background-image: url(https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'Admin') }}&background=0d6efd&color=fff)"></span>
                                <div class="d-none d-xl-block ps-2">
                                    <div>{{ auth()->user()->name ?? 'المدير العام' }}</div>
                                    <div class="mt-1 small text-muted">{{ auth()->user()->email ?? 'admin@domain.com' }}</div>
                                </div>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                <a href="{{ route('profile.edit') }}" class="dropdown-item">الملف الشخصي</a>
                                <div class="dropdown-divider"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">تسجيل الخروج</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- 3. المحتوى الرئيسي -->
            <div class="page-body py-4">
                <div class="container-xl">
                    @isset($slot)
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endisset
                </div>
            </div>

            <!-- 4. الفوتر (Footer) -->
            <footer class="footer footer-transparent d-print-none border-top py-3">
                <div class="container-xl">
                    <div class="row text-center align-items-center flex-row-reverse">
                        <div class="col-lg-auto ms-lg-auto">
                            <ul class="list-inline list-inline-dots mb-0">
                                <li class="list-inline-item"><a href="#" class="link-secondary">الدعم الفني</a></li>
                            </ul>
                        </div>
                        <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                            <ul class="list-inline list-inline-dots mb-0">
                                <li class="list-inline-item">
                                    جميع الحقوق محفوظة &copy; {{ date('Y') }} منظومة المبيعات وإدارة الفروع.
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <style>
    /* حل مشكلة اختفاء نصوص السايدبار */
    .navbar-dark .navbar-nav .nav-link {
        color: rgba(255, 255, 255, 0.85) !important;
    }
    .navbar-dark .navbar-nav .nav-link .nav-link-icon {
        color: rgba(255, 255, 255, 0.85) !important;
    }
    .navbar-dark .navbar-nav .active > .nav-link,
    .navbar-dark .navbar-nav .nav-link:hover {
        color: #ffffff !important;
    }
</style>

    <!-- Tabler Core JS -->
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/js/tabler.min.js" defer></script>
</body>
</html>