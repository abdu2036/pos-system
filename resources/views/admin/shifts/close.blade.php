@extends('layouts.app')

@section('content')
    <div class="container-xl">
        <div class="page-header mb-3">
            <h2 class="page-title fw-bold">🔒 جرد وإغلاق الوردية الحالية</h2>
            <div class="text-muted mt-1">مقارنة مبيعات النظام بالمبلغ الفعلي الموجود في الدرج.</div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <form action="{{ route('shifts.close', $shift->id) }}" method="POST">
                        @csrf

                        <div class="card-body">
                            <div class="row g-3 mb-4">
                                <div class="col-sm-6">
                                    <div class="p-3 bg-light rounded">
                                        <div class="text-muted small">رصيد عهدة البداية:</div>
                                        <div class="fs-4 fw-bold text-dark">{{ number_format($shift->opening_balance, 2) }}
                                            د.ل</div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="p-3 bg-light rounded">
                                        <div class="text-muted small">إجمالي المبيعات النقدية بالوردية:</div>
                                        <div class="fs-4 fw-bold text-success">{{ number_format($cashSales, 2) }} د.ل</div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="p-3 bg-green-lt rounded">
                                        <div class="text-muted small">المبلغ المتوقع في الدرج (بداية + مبيعات نقدية):</div>
                                        <div class="fs-3 fw-bold text-primary">{{ number_format($expectedBalance, 2) }} د.ل
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="p-3 bg-red-lt rounded">
                                        <div class="text-muted small">مبيعات البطاقات المصرفية (الكتروني):</div>
                                        <div class="fs-4 fw-bold text-red">{{ number_format($cardSales ?? 0, 2) }} د.ل</div>
                                    </div>
                                </div>


                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">المبلغ الفعلي الموجود في الدرج (نتيجة الجرد اليدوي) <span
                                        class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="closing_balance" id="closing_balance"
                                    class="form-control form-control-lg" placeholder="أدخل المبلغ بعد عد النقود..."
                                    required>
                            </div>

                            <div id="diff-box" class="alert d-none">
                                <strong>الفرق: </strong> <span id="diff-text">0.00 د.ل</span> (<span
                                    id="diff-status"></span>)
                            </div>

                            <div class="mb-3">
                                <label class="form-label">ملاحظات الإغلاق أو أسباب الفروقات (إن وجدت)</label>
                                <textarea name="notes" class="form-control" placeholder="ملاحظات..."></textarea>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <button type="submit" class="btn btn-danger w-100">تأكيد الجرد وإغلاق الوردية 🛑</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // حساب الفرق فوراً كجرد تفاعلي للمستخدم
        document.getElementById('closing_balance').addEventListener('input', function () {
            let expected = {{ $expectedBalance }};
            let actual = parseFloat(this.value) || 0;
            let diff = actual - expected;
            let diffBox = document.getElementById('diff-box');
            let diffText = document.getElementById('diff-text');
            let diffStatus = document.getElementById('diff-status');

            diffBox.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning');

            if (diff === 0) {
                diffBox.classList.add('alert-success');
                diffStatus.innerText = 'متطابق تماماً ✅';
            } else if (diff > 0) {
                diffBox.classList.add('alert-warning');
                diffStatus.innerText = 'زيادة في الدرج (موجب) 📈';
            } else {
                diffBox.classList.add('alert-danger');
                diffStatus.innerText = 'عجز في الدرج (سالب) 📉';
            }
            diffText.innerText = diff.toFixed(2) + ' د.ل';
        });
    </script>
@endsection