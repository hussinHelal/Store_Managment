@extends('layouts.app')

@section('content')

    <button class="btn btn-primary"><a href="{{ route('installments.create') }}" class="text-white nav-link"><i class="fas fa-plus"></i> دين جديد</a></button>

    <div class="row justify-content-center">
        <span class="text-center text-bold">العملاء</span>
    </div>

    <table class="table table-borderless table-hover table-striped table-primary">
      <thead>
        <tr>
          <th scope="col">#</th>
          <th scope="col">الاسم</th>
          <th scope="col">المبلغ</th>
          <th scope="col">تاريخ الانشاء</th>
          <th scope="col">تاريخ الدفعة الاولى</th>
          <th scope="col">تاريخ الدفعه التالية</th>
          <th scope="col">المدفوع</th>
          <th scope="col">الإجراءات</th>
        </tr>
      </thead>
      <tbody>
          @foreach($installments as $installment)
        <tr>
          <th scope="row">{{ $installment->id }}</th>
          <td>{{ $installment->customer }}</td>
          <td>{{ $installment->amount }}</td>
          <td>{{ \Carbon\Carbon::parse($installment->due_date)->format('Y-m-d') }}</td>
          <td>{{ \Carbon\Carbon::parse($installment->payment_date)->format('Y-m-d') }}</td>
          <td>{{ \Carbon\Carbon::parse($installment->next_payment_date)->format('Y-m-d') }}</td>
          <td>{{ $installment->paid_amount }}</td>
          <td>
              <a href="{{ route('installments.edit', $installment->id) }}" class="btn btn-sm btn-primary">تعديل</a>
              <form action="{{ route('installments.destroy', $installment->id) }}" method="POST" style="display: inline;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا الدين؟')" >حذف</button>
              </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    
@endsection
              