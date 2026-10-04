@extends('layouts.app')

@section('content')
<div class="page-header d-print-none mb-4">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">قائمة المنتجات والمخزون</h2>
            </div>
            <div class="col-auto ms-auto">
                <a href="{{ route('products.create') }}" class="btn btn-primary">+ إضافة منتج جديد</a>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        <div class="d-flex">
            <div>{{ session('success') }}</div>
        </div>
        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
    </div>
@endif

<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>المنتج</th>
                    <th>التصنيف</th>
                    <th>الباركود</th>
                    <th>سعر الشراء</th>
                    <th>سعر البيع</th>
                    <th>المخزون المتوفر</th>
                    <th class="w-1 text-center">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td class="fw-bold">{{ $product->name }}</td>
                        <td><span class="badge bg-gray-200 text-dark">{{ $product->category->name ?? 'بدون تصنيف' }}</span></td>
                        <td><code>{{ $product->barcode ?? 'N/A' }}</code></td>
                        <td>{{ number_format($product->cost_price, 2) }}</td>
                        <td class="fw-bold text-success">{{ number_format($product->selling_price, 2) }}</td>
                        <td>
                            @php
                                $totalQty = $product->branches->sum('pivot.quantity');
                                $alertQty = $product->branches->first()->pivot->alert_quantity ?? 5;
                            @endphp
                            <span class="badge {{ $totalQty <= $alertQty ? 'bg-danger' : 'bg-blue' }} fs-6">
                                {{ $totalQty }} {{ $product->unit }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-list flex-nowrap justify-content-center">
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-primary">
                                    تعديل
                                </a>
                                <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('هل أنت تأكد من رغبتك في حذف هذا المنتج؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        حذف
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">لا توجد منتجات مضافة حتى الآن</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
        <div class="card-footer d-flex align-items-center">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection