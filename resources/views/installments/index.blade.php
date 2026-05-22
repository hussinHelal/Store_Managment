@extends('layouts.app')

@section('content')

    <button class="btn btn-primary"><a href="{{ route('installments.create') }}" class="text-white nav-link"><i class="fas fa-plus"></i> دين جديد</a></button>

    <div class="row justify-content-center">
        <span class="fw-bold text-body text-center">الديون</span>
    </div>

    <table class="table  table-hover table-striped ">
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
          @foreach($installments as $installment)
        <tr>
          <th scope="row">{{ $installment->id }}</th>
          <td>{{ $installment->customer }}</td> 
          <td>{{ $installment->product?->name ?? 'لا يوجد اسم' }}</td>
          <td>{{ $installment->product?->price ?? 'لا يوجد سعر' }}</td>
          <td>{{ $installment->quantity ?? 'لا يوجد كمية' }}</td>
          <td>{{ \Carbon\Carbon::parse($installment->payment_date)->format('Y-m-d') }}</td>
          <td>{{ \Carbon\Carbon::parse($installment->next_payment_date)->format('Y-m-d') }}</td>
          <td>{{ $installment->paid_amount }}</td>
          <td>{{ $installment->remaining }}</td> 
          <td>{{ $installment->status }}</td>
          <td>
              <a href="{{ route('installments.edit', $installment->id) }}" class="btn btn-sm btn-primary m-1 rounded">تعديل</a>
              <form action="{{ route('installments.destroy', $installment->id) }}" method="POST" style="display: inline;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger rounded" onclick="return confirm('هل أنت متأكد من حذف هذا الدين؟')" >حذف</button>
              </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    
@endsection
              