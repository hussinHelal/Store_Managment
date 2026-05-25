@extends('layouts.app')
@section('title', ' - الديون')

@section('content')

<div class="page-header">
    <h1>الديون</h1>
    <a href="{{ route('installments.create') }}" class="btn btn-primary btn-round">
        <i class="fas fa-plus me-1"></i> دين جديد
    </a>
</div>

<div class="table-wrapper table-responsive">
    <table class="table table-hover table-striped align-middle mb-0">
      <thead class="table-dark">
        <tr>
          <th scope="col">#</th>
          <th scope="col">الاسم</th>
          <th scope="col">المنتج</th>
          <th scope="col">المبلغ</th>
          <th scope="col">الكمية</th>
          <th scope="col">تاريخ الدفع</th>
          <th scope="col">الدفعه التالية</th>
          <th scope="col">المدفوع</th>
          <th scope="col">المتبقي</th>
          <th scope="col">الحالة</th>
          <th scope="col">الإجراءات</th>
        </tr>
      </thead>
      <tbody>
          @forelse($installments as $installment)
        <tr>
          <th scope="row">{{ $installment->id }}</th>
          <td>{{ $installment->customer }}</td>
          <td>{{ $installment->item_names ?: ($installment->product?->name ?? 'لا يوجد اسم') }}</td>
          <td>{{ $installment->product?->price ?? $installment->product_price ?? 'لا يوجد سعر' }}</td>
          <td>{{ $installment->item_quantity ?: ($installment->quantity ?? 'لا يوجد كمية') }}</td>
          <td>{{ \Carbon\Carbon::parse($installment->payment_date)->format('Y-m-d') }}</td>
          <td>{{ \Carbon\Carbon::parse($installment->next_payment_date)->format('Y-m-d') }}</td>
          <td>{{ $installment->paid_amount }}</td>
          <td>{{ $installment->remaining }}</td>
          <td>{{ $installment->status }}</td>
          <td>
              <a href="{{ route('installments.edit', $installment->id) }}" class="btn btn-sm btn-primary m-1 rounded">تعديل</a>
              <a href="{{ route('installments.showPay', $installment->id) }}" class="btn btn-sm btn-warning m-1 rounded">دفع</a>
              <form action="{{ route('installments.destroy', $installment->id) }}" method="POST" style="display: inline;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger rounded" onclick="return confirm('هل أنت متأكد من حذف هذا الدين؟')">حذف</button>
              </form>
          </td>
        </tr>
        @empty
        <tr>
            <td colspan="11" class="text-center">لا توجد ديون.</td>
        </tr>
        @endforelse
      </tbody>
    </table>

    @include('components.pagination', ['collection' => $installments])
</div>

@endsection
