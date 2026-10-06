<div class="row g-3 mb-4">
    <div class="col-md-6">
        <label for="name" class="form-label">اسم المورد <span class="text-danger">*</span></label>
        <input id="name" name="name" value="{{ old('name', $supplier?->name) }}" class="form-control @error('name') is-invalid @enderror" required maxlength="255">
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="phone" class="form-label">رقم الهاتف</label>
        <input id="phone" name="phone" value="{{ old('phone', $supplier?->phone) }}" class="form-control @error('phone') is-invalid @enderror" maxlength="20" dir="ltr">
        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="email" class="form-label">البريد الإلكتروني</label>
        <input id="email" name="email" type="email" value="{{ old('email', $supplier?->email) }}" class="form-control @error('email') is-invalid @enderror" maxlength="255" dir="ltr">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="opening_balance" class="form-label">الرصيد الافتتاحي (ج.م)</label>
        <input id="opening_balance" name="opening_balance" type="number" step="0.01" min="0" value="{{ old('opening_balance', $supplier?->opening_balance ?? '0.00') }}" class="form-control @error('opening_balance') is-invalid @enderror" inputmode="decimal">
        @error('opening_balance')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label for="address" class="form-label">العنوان</label>
        <input id="address" name="address" value="{{ old('address', $supplier?->address) }}" class="form-control @error('address') is-invalid @enderror" maxlength="255">
        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label for="notes" class="form-label">ملاحظات</label>
        <textarea id="notes" name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror" maxlength="2000">{{ old('notes', $supplier?->notes) }}</textarea>
        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
