<section>
    <p class="text-muted mb-3">
        بمجرد حذف حسابك، سيتم حذف جميع الموارد والبيانات المرتبطة به نهائياً.
    </p>

    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmUserDeletionModal">
        حذف الحساب
    </button>

    <!-- Modal تأكيد الحذف بأسلوب Tabler -->
    <div class="modal fade" id="confirmUserDeletionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')

                    <div class="modal-header">
                        <h5 class="modal-title">هل أنت تأكد من رغبتك في حذف الحساب؟</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <p class="text-muted">
                            يرجى إدخال كلمة المرور لتأكيد رغبتك في حذف الحساب نهائياً.
                        </p>

                        <div class="mb-3">
                            <label for="password" class="form-label required">كلمة المرور</label>
                            <input id="password" name="password" type="password" class="form-control @error('password', 'userDeletion') is-invalid @enderror" placeholder="كلمة المرور" />
                            @error('password', 'userDeletion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-danger">تأكيد حذف الحساب</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>