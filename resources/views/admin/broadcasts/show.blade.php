@extends('layouts.admin')

@section('title', 'Broadcast Details')
{{-- @section('page-title', $broadcast->title)
@section('page-description', 'Broadcast ID: ' . $broadcast->broadcast_uuid) --}}

@section('content')

    <div class="d-flex justify-content-between mb-4">
        <a href="{{ route('admin.broadcasts.index') }}" class="text-decoration-none">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>

        <div class="d-flex gap-2">
            @if (in_array($broadcast->status, ['draft', 'failed']))
                <form method="POST" action="{{ route('admin.broadcasts.send', $broadcast->broadcast_id) }}"
                    onsubmit="return confirm('Send this broadcast now?')">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-paper-plane me-1"></i> Send Now
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.broadcasts.destroy', $broadcast->broadcast_id) }}"
                    onsubmit="return confirm('Delete this broadcast?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="fas fa-trash me-1"></i> Delete
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <span class="text-muted small">Recipients</span>
                <h3 class="stat-value">{{ number_format($broadcast->recipients_count) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <span class="text-muted small">Delivered</span>
                <h3 class="stat-value text-info">{{ number_format($broadcast->delivered_count) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <span class="text-muted small">Read</span>
                <h3 class="stat-value text-success">{{ number_format($broadcast->read_count) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <span class="text-mute small">Read Rate</span>
                <h3 class="stat-value text-danger">{{ $broadcast->read_rate }}%</h3>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            {{-- Preview --}}
            <div class="card mb-4">
                <div class="card-header">Message Preview</div>
                <div class="card-body">
                    <div class="p-3 rounded" style="background: #f5f3ff; border: 1px solid #e9d5ff;">
                        <div class="d-flex gap-2">
                            <div
                                style="width: 40px; height: 40px; border-radius: 20px; background: #ede9fe;
                                    display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-bell text-purple"></i>
                            </div>
                            <div style="flex: 1;">
                                <div class="fw-bold">{{ $broadcast->title }}</div>
                                @if ($broadcast->body)
                                    <div class="text-muted mt-1" style="font-size: 13px;">{{ $broadcast->body }}</div>
                                @endif
                                @if ($broadcast->action_label)
                                    <div class="mt-2">
                                        <span class="badge bg-purple-light text-purple">
                                            {{ $broadcast->action_label }}
                                        </span>
                                    </div>
                                @endif
                                <div class="text-muted mt-1" style="font-size: 10px;">Just now</div>
                            </div>
                        </div>
                    </div>

                    @if ($broadcast->image_url)
                        <div class="mt-3">
                            <small class="text-muted d-block mb-1">Attached Image</small>
                            <img src="{{ $broadcast->image_url }}" class="rounded"
                                style="max-width: 100%; height: 160px; object-fit: cover;">
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">Details</div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted">Status</dt>
                        <dd class="col-7">
                            @php $badge = $broadcast->status_badge; @endphp
                            <span
                                class="badge-soft badge-soft-{{ $badge['color'] === 'info' ? 'info' : $badge['color'] }}">
                                {{ $badge['label'] }}
                            </span>
                        </dd>

                        <dt class="col-5 text-muted">Audience</dt>
                        <dd class="col-7">{{ ucfirst(str_replace('_', ' ', $broadcast->audience)) }}</dd>

                        <dt class="col-5 text-muted">Priority</dt>
                        <dd class="col-7">{{ ucfirst($broadcast->priority) }}</dd>

                        <dt class="col-5 text-muted">Type</dt>
                        <dd class="col-7">{{ ucfirst($broadcast->type) }}</dd>

                        <dt class="col-5 text-muted">Sent By</dt>
                        <dd class="col-7">{{ $broadcast->sender->firstname ?? '—' }}</dd>

                        <dt class="col-5 text-muted">Created</dt>
                        <dd class="col-7">{{ $broadcast->created_at->format('M d, Y H:i') }}</dd>

                        @if ($broadcast->sent_at)
                            <dt class="col-5 text-muted">Sent At</dt>
                            <dd class="col-7">{{ $broadcast->sent_at->format('M d, Y H:i') }}</dd>
                        @endif

                        @if ($broadcast->action_url)
                            <dt class="col-5 text-muted">Action URL</dt>
                            <dd class="col-7 font-monospace small">{{ $broadcast->action_url }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <style>
        .bg-purple-light {
            background-color: #ede9fe !important;
        }

        .text-purple {
            color: #6d28d9 !important;
        }
    </style>

@endsection
