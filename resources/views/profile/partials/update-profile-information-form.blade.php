<section>
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-2">
        @csrf
        @method('patch')

        <div class="mb-3">
            <label class="form-label required" for="name">الاسم</label>
            <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label required" for="email">البريد الإلكتروني</label>
            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username" />
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="text-sm text-muted">
                        بريدك الإلكتروني غير مفعّل.
                        <button form="send-verification" class="btn btn-link p-0 m-0 align-baseline">اضغط هنا لإعادة إرسال رابط التفعيل.</button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <div class="alert alert-success mt-2 mb-0" role="alert">
                            تم إرسال رابط تفعيل جديد إلى بريدك الإلكتروني.
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3 mt-4">
            <button type="submit" class="btn btn-primary">حفظ التغييرات</button>

            @if (session('status') === 'profile-updated')
                <span class="text-success small fw-semibold">تم الحفظ بنجاح.</span>
            @endif
        </div>
    </form>
</section>