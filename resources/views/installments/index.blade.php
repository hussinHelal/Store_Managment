@extends('layouts.app')

@section('content')

    <button class="btn btn-primary"><a href="{{ route('installment.create') }}" class="text-white"><i class="fas fa-plus"></i> دين جديد</a></button>
    <table class="table table-borderless">
      <thead>
          <tr colspan="6">الديون</tr>
        <tr>
          <th scope="col">#</th>
          <th scope="col">الاسم</th>
          <th scope="col">المبلغ</th>
          <th scope="col">تاريخ الانشاء</th>
          <th scope="col">تاريخ الدفع </th>
          <th scope="col">الدفعه التالية</th>
          <th scope="col">تاريخ الدفعه التالية</th>
          <th scope="col">الإجراءات</th>
        </tr>
      </thead>
      <tbody>
          @foreach($installments as $installment)
        <tr>
          <th scope="row">{{ $installment->id }}</th>
          <td>{{ $installment->name }}</td>
          <td>{{ $installment->amount }}</td>
          <td>{{ $installment->due_date }}</td>
          <td>{{ $installment->payment_date }}</td>
          <td>{{ $installment->next_payment_date }}</td>
          <td>{{ $installment->payment_date }}</td>
          <td>
              <a href="{{ route('installment.edit', $installment->id) }}" class="btn btn-sm btn-primary">تعديل</a>
              <form action="{{ route('installment.destroy', $installment->id) }}" method="POST" style="display: inline;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger">حذف</button>
              </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    
@endsection
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger">حذف</button>
              </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    
@endsection