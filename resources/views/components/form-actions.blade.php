<div class="form-actions d-flex flex-wrap align-items-center gap-2 mt-3">
    <button type="submit" class="{{ $submitClass ?? 'btn btn-primary' }}">{{ $submitLabel }}</button>
    <a href="{{ $backUrl }}" class="{{ $backClass ?? 'btn btn-outline-secondary' }}">{{ $backLabel ?? 'رجوع' }}</a>
</div>