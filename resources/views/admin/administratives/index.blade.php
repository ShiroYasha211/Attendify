@extends('layouts.admin')

@section('title', 'Legacy Administrative Accounts')

@section('content')

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary);">Legacy Administrative Accounts</h1>
        <p style="color: var(--text-secondary); margin-top: 0.5rem;">Direct creation is disabled. New administrative access is granted from the Doctors page.</p>
    </div>

    <a href="{{ route('admin.doctors.index') }}"
       class="btn"
       style="background: var(--primary-color); color: white; display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; text-decoration: none; box-shadow: var(--shadow-sm);">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 3h18v18H3z"></path>
            <path d="M8 12h8"></path>
            <path d="M12 8v8"></path>
        </svg>
        Manage Doctors
    </a>
</div>

@if(session('info'))
    <div class="alert alert-info">{{ session('info') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-warning">{{ session('error') }}</div>
@endif

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="alert alert-info">
    New administrative accounts are no longer created directly. To grant this workspace, open a doctor account and enable <code>administrative_access</code>.
</div>

<div class="card">
    <div class="table-container">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>User</th>
                        <th>College</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th style="width: 151px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($administratives as $admin)
                    <tr>
                        <td>{{ $loop->iteration + ($administratives->currentPage() - 1) * $administratives->perPage() }}</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: #1f2937; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                    {{ mb_substr($admin->name, 0, 1) }}
                                </div>
                                <div>
                                    <div style="font-weight: 700; color: var(--text-primary);">{{ $admin->name }}</div>
                                    <div style="font-size: 0.85rem; color: var(--text-secondary);">{{ $admin->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge" style="background: rgba(67, 56, 202, 0.1); color: var(--primary-color); padding: 0.4rem 0.8rem;">
                                {{ $admin->college->name ?? 'Unassigned' }}
                            </span>
                        </td>
                        <td>
                            @if($admin->status == 'active')
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </td>
                        <td style="color: var(--text-secondary); font-size: 0.9rem;">
                            {{ $admin->created_at->format('Y/m/d') }}
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                <a href="{{ route('admin.administratives.edit', $admin->id) }}"
                                   class="btn"
                                   style="padding: 0.4rem; background: #e0e7ff; color: #4338ca; display: flex; align-items: center; justify-content: center;"
                                   title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </a>

                                <form action="{{ route('admin.administratives.destroy', $admin->id) }}" method="POST" onsubmit="return confirm('Delete this legacy administrative account?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn" style="padding: 0.4rem; background: #fee2e2; color: #dc2626;" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                            No legacy administrative accounts found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="padding: 1.5rem; border-top: 1px solid var(--border-color);">
        {{ $administratives->links() }}
    </div>
</div>

@endsection
