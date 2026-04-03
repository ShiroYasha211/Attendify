@extends('layouts.delegate')

@section('title', 'ط·آ¥ط·آ¶ط·آ§ط¸ظ¾ط·آ© ط¸â€¦ط¸ث†ط·آ¹ط·آ¯ ط¸â€‍ط¸â€‍ط·آ¬ط·آ¯ط¸ث†ط¸â€‍')

@section('content')
<div class="container" style="max-width: 800px;">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary);">ط·آ¥ط·آ¶ط·آ§ط¸ظ¾ط·آ© ط¸â€¦ط¸ث†ط·آ¹ط·آ¯ ط·آ¬ط·آ¯ط¸ظ¹ط·آ¯</h1>
        <a href="{{ route('delegate.schedules.index') }}" class="btn btn-secondary" style="display: flex; align-items: center; gap: 0.5rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            ط·آ¥ط¸â€‍ط·ط›ط·آ§ط·طŒ ط¸ث†ط·آ¹ط¸ث†ط·آ¯ط·آ©
        </a>
    </div>

    <div class="card">
        <form action="{{ route('delegate.schedules.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="subject_id" class="form-label">ط·آ§ط¸â€‍ط¸â€¦ط·آ§ط·آ¯ط·آ© ط·آ§ط¸â€‍ط·آ¯ط·آ±ط·آ§ط·آ³ط¸ظ¹ط·آ©</label>
                <select name="subject_id" id="subject_id" class="form-control" required style="font-size: 1rem; padding: 0.8rem;">
                    <option value="">ط·آ§ط·آ®ط·ع¾ط·آ± ط·آ§ط¸â€‍ط¸â€¦ط·آ§ط·آ¯ط·آ©...</option>
                    @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}">
                        {{ $subject->name }} - {{ $subject->doctor->name ?? 'ط·ط›ط¸ظ¹ط·آ± ط¸â€¦ط·آ­ط·آ¯ط·آ¯' }} ({{ $subject->code }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label for="day_of_week" class="form-label">ط·آ§ط¸â€‍ط¸ظ¹ط¸ث†ط¸â€¦</label>
                    <select name="day_of_week" id="day_of_week" class="form-control" required style="font-size: 1rem; padding: 0.8rem;">
                        <option value="1">ط·آ§ط¸â€‍ط·آ¥ط·آ«ط¸â€ ط¸ظ¹ط¸â€ </option>
                        <option value="2">ط·آ§ط¸â€‍ط·آ«ط¸â€‍ط·آ§ط·آ«ط·آ§ط·طŒ</option>
                        <option value="3">ط·آ§ط¸â€‍ط·آ£ط·آ±ط·آ¨ط·آ¹ط·آ§ط·طŒ</option>
                        <option value="4">ط·آ§ط¸â€‍ط·آ®ط¸â€¦ط¸ظ¹ط·آ³</option>
                        <option value="5">ط·آ§ط¸â€‍ط·آ¬ط¸â€¦ط·آ¹ط·آ©</option>
                        <option value="6">ط·آ§ط¸â€‍ط·آ³ط·آ¨ط·ع¾</option>
                        <option value="7">ط·آ§ط¸â€‍ط·آ£ط·آ­ط·آ¯</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="hall_name" class="form-label">ط·آ§ط¸â€‍ط¸â€ڑط·آ§ط·آ¹ط·آ© ط·آ§ط¸â€‍ط·آ¯ط·آ±ط·آ§ط·آ³ط¸ظ¹ط·آ©</label>
                    <input type="text" name="hall_name" id="hall_name" class="form-control" placeholder="مثال: القاعة الكبرى، قاعة 101">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label for="start_time" class="form-label">ط¸ث†ط¸â€ڑط·ع¾ ط·آ§ط¸â€‍ط·آ¨ط·آ¯ط·طŒ</label>
                    <input type="time" name="start_time" id="start_time" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="end_time" class="form-label">ط¸ث†ط¸â€ڑط·ع¾ ط·آ§ط¸â€‍ط·آ§ط¸â€ ط·ع¾ط¸â€،ط·آ§ط·طŒ</label>
                    <input type="time" name="end_time" id="end_time" class="form-control" required>
                </div>
            </div>

            <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary" style="padding: 0.8rem 2rem; font-size: 1rem; font-weight: 700;">
                    ط·آ­ط¸ظ¾ط·آ¸ ط·آ§ط¸â€‍ط¸â€¦ط¸ث†ط·آ¹ط·آ¯
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
