@extends('layouts.admin')

@section('title', 'Broadcast Notifications')
{{-- @section('page-title', 'Broadcast Notifications')
@section('page-description', 'Send notifications to all or specific groups of users') --}}

@section('content')

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Total Broadcasts</span>
                    <i class="fas fa-bullhorn text-purple"></i>
                </div>
                <h3 class="stat-value">{{ number_format($stats['total']) }}</h3>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Sent</span>
                    <i class="fas fa-paper-plane text-success"></i>
                </div>
                <h3 class="stat-value text-success">{{ number_format($stats['sent']) }}</h3>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Total Recipients</span>
                    <i class="fas fa-users text-info"></i>
                </div>
                <h3 class="stat-value text-info">{{ number_format($stats['total_recipients']) }}</h3>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Total Reads</span>
                    <i class="fas fa-eye text-warning"></i>
                </div>
                <h3 class="stat-value text-danger">{{ number_format($stats['total_reads']) }}</h3>
            </div>
        </div>
    </div>

    {{-- Header actions --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                placeholder="Search broadcasts..." style="min-width: 240px;">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="all">All Status</option>
                @foreach (['draft' => 'Draft', 'sending' => 'Sending', 'sent' => 'Sent', 'failed' => 'Failed'] as $val => $label)
                    <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>
                        {{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-light"><i class="fas fa-filter"></i></button>
        </form>

        <a href="{{ route('admin.broadcasts.create') }}" class="btn btn-purple">
            <i class="fas fa-plus me-1"></i> New Broadcast
        </a>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Audience</th>
                        <th>Status</th>
                        <th>Recipients</th>
                        <th>Read Rate</th>
                        <th>Sent At</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($broadcasts as $b)
                        @php $badge = $b->status_badge; @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $b->title }}</div>
                                <small class="text-muted text-truncate" style="max-width: 300px; display: block;">
                                    {{ Str::limit($b->body, 60) }}
                                </small>
                            </td>
                            <td>
                                <span class="badge-soft badge-soft-primary">
                                    {{ ucfirst(str_replace('_', ' ', $b->audience)) }}
                                </span>
                            </td>
                            <td>
                                <span
                                    class="badge-soft badge-soft-{{ $badge['color'] === 'info' ? 'info' : $badge['color'] }}">
                                    {{ $badge['label'] }}
                                </span>
                            </td>
                            <td>
                                <strong>{{ number_format($b->recipients_count) }}</strong>
                                @if ($b->delivered_count !== $b->recipients_count && $b->status === 'sent')
                                    <small class="text-muted">({{ $b->delivered_count }} delivered)</small>
                                @endif
                            </td>
                            <td>
                                @if ($b->status === 'sent' && $b->recipients_count > 0)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px; min-width: 60px;">
                                            <div class="progress-bar bg-success" style="width: {{ $b->read_rate }}%"></div>
                                        </div>
                                        <small class="fw-bold">{{ $b->read_rate }}%</small>
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($b->sent_at)
                                    <div class="small">{{ $b->sent_at->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $b->sent_at->format('H:i') }}</small>
                                @else
                                    <span class="text-muted">Not sent</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.broadcasts.show', $b->broadcast_id) }}"
                                    class="btn btn-sm btn-light">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if (in_array($b->status, ['draft', 'failed']))
                                    <form method="POST" action="{{ route('admin.broadcasts.send', $b->broadcast_id) }}"
                                        class="d-inline"
                                        onsubmit="return confirm('Send this broadcast to {{ $b->recipients_count ?: 'all matched' }} users?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.broadcasts.destroy', $b->broadcast_id) }}"
                                        class="d-inline" onsubmit="return confirm('Delete this draft?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-bullhorn text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3 mb-2">No broadcasts yet</p>
                                <a href="{{ route('admin.broadcasts.create') }}" class="btn btn-purple">
                                    <i class="fas fa-plus me-1"></i> Create First Broadcast
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($broadcasts->hasPages())
            <div class="card-footer bg-white">
                {{ $broadcasts->withQueryString()->links() }}
            </div>
        @endif
    </div>

@endsection
