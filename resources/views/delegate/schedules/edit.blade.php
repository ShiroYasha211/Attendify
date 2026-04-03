@extends('layouts.delegate')

@section('title', 'طھط¹ط¯ظٹظ„ ظ…ظˆط¹ط¯')

@section('content')
<div class="container" style="max-width: 800px;">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary);">طھط¹ط¯ظٹظ„ ظ…ظˆط¹ط¯</h1>
        <a href="{{ route('delegate.schedules.index') }}" class="btn btn-secondary" style="display: flex; align-items: center; gap: 0.5rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            ط¥ظ„ط؛ط§ط، ظˆط¹ظˆط¯ط©
        </a>
    </div>

    <div class="card">
        <form action="{{ route('delegate.schedules.update', $schedule->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="subject_id" class="form-label">ط§ظ„ظ…ط§ط¯ط© ط§ظ„ط¯ط±ط§ط³ظٹط©</label>
                <select name="subject_id" id="subject_id" class="form-control" required style="font-size: 1rem; padding: 0.8rem;">
                    <option value="">ط§ط®طھط± ط§ظ„ظ…ط§ط¯ط©...</option>
                    @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" {{ $schedule->subject_id == $subject->id ? 'selected' : '' }}>
                        {{ $subject->name }} - {{ $subject->doctor->name ?? 'ط؛ظٹط± ظ…ط­ط¯ط¯' }} ({{ $subject->code }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label for="day_of_week" class="form-label">ط§ظ„ظٹظˆظ…</label>
                    <select name="day_of_week" id="day_of_week" class="form-control" required style="font-size: 1rem; padding: 0.8rem;">
                        <option value="1" {{ $schedule->day_of_week == 1 ? 'selected' : '' }}>ط§ظ„ط¥ط«ظ†ظٹظ†</option>
                        <option value="2" {{ $schedule->day_of_week == 2 ? 'selected' : '' }}>ط§ظ„ط«ظ„ط§ط«ط§ط،</option>
                        <option value="3" {{ $schedule->day_of_week == 3 ? 'selected' : '' }}>ط§ظ„ط£ط±ط¨ط¹ط§ط،</option>
                        <option value="4" {{ $schedule->day_of_week == 4 ? 'selected' : '' }}>ط§ظ„ط®ظ…ظٹط³</option>
                        <option value="5" {{ $schedule->day_of_week == 5 ? 'selected' : '' }}>ط§ظ„ط¬ظ…ط¹ط©</option>
                        <option value="6" {{ $schedule->day_of_week == 6 ? 'selected' : '' }}>ط§ظ„ط³ط¨طھ</option>
                        <option value="7" {{ $schedule->day_of_week == 7 ? 'selected' : '' }}>ط§ظ„ط£ط­ط¯</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="hall_name" class="form-label">ط§ظ„ظ‚ط§ط¹ط© ط§ظ„ط¯ط±ط§ط³ظٹط©</label>
                    <input type="text" name="hall_name" id="hall_name" class="form-control" value="{{ old('hall_name', $schedule->hall_name) }}">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label for="start_time" class="form-label">ظˆظ‚طھ ط§ظ„ط¨ط¯ط،</label>
                    <input type="time" name="start_time" id="start_time" class="form-control" value="{{ old('start_time', $schedule->start_time) }}" required>
                </div>

                <div class="form-group">
                    <label for="end_time" class="form-label">ظˆظ‚طھ ط§ظ„ط§ظ†طھظ‡ط§ط،</label>
                    <input type="time" name="end_time" id="end_time" class="form-control" value="{{ old('end_time', $schedule->end_time) }}" required>
                </div>
            </div>

            <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary" style="padding: 0.8rem 2rem; font-size: 1rem; font-weight: 700;">
                    ط­ظپط¸ ط§ظ„طھط؛ظٹظٹط±ط§طھ
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
