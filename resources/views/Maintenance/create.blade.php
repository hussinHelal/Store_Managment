@extends('layouts.app')

@section('content')
<div class="container py-4" dir="rtl">
    <h1 class="h3 mb-4">إضافة طلب صيانة</h1>
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <form action="{{ route('maintenance.store') }}" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="mb-3"><label for="name" class="form-label">اسم الجهاز</label><input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>@error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="mb-3"><label for="owner" class="form-label">اسم المالك</label><input type="text" class="form-control" id="owner" name="owner" value="{{ old('owner') }}" required>@error('owner')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="mb-3"><label for="address" class="form-label">العنوان</label><input type="text" class="form-control" id="address" name="address" value="{{ old('address') }}" required>@error('address')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="mb-3"><label for="phone" class="form-label">رقم الهاتف</label><input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone') }}" required>@error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="mb-3"><label for="requested_date" class="form-label">تاريخ استلام الجهاز</label><input type="date" class="form-control" id="requested_date" name="requested_date" value="{{ old('requested_date') }}" required>@error('requested_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="mb-3"><label for="status" class="form-label">الحالة</label>
        <select class="form-select" id="status" name="status" required>
          <option value="قيد الانتظار" @selected(old('status', 'قيد الانتظار') === 'قيد الانتظار')>قيد الانتظار</option>
          <option value="مكتمل" @selected(old('status') === 'مكتمل')>مكتمل</option>
          <option value="مرفوض" @selected(old('status') === 'مرفوض')>مرفوض</option>
        </select>@error('status')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
      </div>
      <div class="mb-3"><label for="description" class="form-label">وصف العطل أو العمل المطلوب</label><textarea class="form-control" id="description" name="description" rows="3" required>{{ old('description') }}</textarea>@error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="mb-3"><label for="notes" class="form-label">ملاحظات</label><textarea class="form-control" id="notes" name="notes" rows="3" maxlength="2000">{{ old('notes') }}</textarea>@error('notes')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="mb-3"><label for="image" class="form-label">صورة الجهاز (اختياري)</label><input class="form-control" type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">@error('image')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="d-flex gap-2"><button type="submit" class="btn btn-primary">حفظ طلب الصيانة</button><a href="{{ route('maintenance.index') }}" class="btn btn-outline-secondary">إلغاء</a></div>
    </form>
</div>
@endsection
