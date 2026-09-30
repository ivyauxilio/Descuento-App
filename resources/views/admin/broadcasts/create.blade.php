@extends('layouts.admin')

@section('title', 'New Broadcast')
{{-- @section('page-title', 'New Broadcast Notification')
@section('page-description', 'Compose and send a notification to your users') --}}

@section('content')

    <form method="POST" action="{{ route('admin.broadcasts.store') }}" id="broadcastForm">
        @csrf

        <div class="row g-4">
            <div class="col-lg-8">

                {{-- Message --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-edit text-purple me-2"></i>Message
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" name="title" id="inputTitle" value="{{ old('title') }}"
                                class="form-control @error('title') is-invalid @enderror"
                                placeholder="e.g., 🎉 New referral rewards are live!" required maxlength="255">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted"><span id="titleCount">0</span>/255</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Body</label>
                            <textarea name="body" id="inputBody" rows="4" class="form-control @error('body') is-invalid @enderror"
                                placeholder="Write your message..." maxlength="2000">{{ old('body') }}</textarea>
                            @error('body')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted"><span id="bodyCount">0</span>/2000</small>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Notification Type *</label>
                                <select name="type" class="form-select" required>
                                    @foreach ([
            'system' => '🔔 System',
            'promotion' => '🎁 Promotion',
            'referral' => '👥 Referral',
            'order' => '📦 Order',
            'announcement' => '📢 Announcement',
            'maintenance' => '🛠️ Maintenance',
        ] as $val => $label)
                                        <option value="{{ $val }}"
                                            {{ old('type', 'system') === $val ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Priority *</label>
                                <select name="priority" class="form-select" required>
                                    @foreach (['low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent'] as $val => $label)
                                        <option value="{{ $val }}"
                                            {{ old('priority', 'normal') === $val ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Button --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-link text-purple me-2"></i>Action Button (Optional)
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Action URL</label>
                                <input type="text" name="action_url" value="{{ old('action_url') }}"
                                    class="form-control" placeholder="/referrals or /wallet/transactions">
                                <small class="text-muted">Where the app should navigate when tapped</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Action Label</label>
                                <input type="text" name="action_label" value="{{ old('action_label') }}"
                                    class="form-control" placeholder="View Referrals" maxlength="50">
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Image URL (Optional)</label>
                            <input type="url" name="image_url" value="{{ old('image_url') }}" class="form-control"
                                placeholder="https://example.com/banner.jpg">
                        </div>
                    </div>
                </div>

                {{-- Audience --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-users text-purple me-2"></i>Audience
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Send To *</label>
                            <select name="audience" id="audienceSelect" class="form-select" required>
                                <option value="all">🌐 All Users</option>
                                <option value="customers">🛒 Customers Only</option>
                                <option value="merchants">🏪 Merchants Only</option>
                                <option value="active">✅ Active Users</option>
                                <option value="inactive">⚠️ Inactive Users</option>
                                <option value="has_wallet">💰 Users with Wallet</option>
                                <option value="has_referrals">👥 Users with Referrals</option>
                                <option value="custom">🎯 Custom Selection</option>
                            </select>
                        </div>

                        {{-- Preview count --}}
                        <div class="alert alert-info d-flex align-items-center mb-0">
                            <i class="fas fa-users me-2"></i>
                            <div>
                                Estimated recipients:
                                <strong id="recipientCount">Loading...</strong>
                            </div>
                        </div>

                        {{-- Custom user selector (shown only when audience = custom) --}}
                        <div id="customUsersBlock" class="mt-3 d-none">
                            <label class="form-label">Search Users</label>
                            <input type="text" id="userSearch" class="form-control"
                                placeholder="Search by name or email...">

                            <div id="searchResults" class="mt-2"></div>

                            <div id="selectedUsers" class="mt-3 d-flex flex-wrap gap-2"></div>

                            {{-- Hidden inputs for selected user IDs --}}
                            <div id="hiddenUserIds"></div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">

                {{-- Preview --}}
                <div class="card mb-4 position-sticky" style="top: 90px;">
                    <div class="card-header">
                        <i class="fas fa-mobile-alt text-purple me-2"></i>Preview
                    </div>
                    <div class="card-body">
                        <div class="p-3 rounded" style="background: #f5f3ff; border: 1px solid #e9d5ff;">
                            <div class="d-flex gap-2">
                                <div
                                    style="width: 40px; height: 40px; border-radius: 20px; background: #ede9fe;
                                        display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-bell text-purple"></i>
                                </div>
                                <div style="flex: 1;">
                                    <div class="fw-bold" id="previewTitle" style="font-size: 14px;">Notification Title
                                    </div>
                                    <div class="text-muted" id="previewBody" style="font-size: 12px; margin-top: 2px;">
                                        Your message will appear here...
                                    </div>
                                    <div class="text-muted mt-1" style="font-size: 10px;">Just now</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Expiry --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-clock text-purple me-2"></i>Options
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Expires At (Optional)</label>
                            <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}"
                                class="form-control">
                            <small class="text-muted">Auto-hide after this date</small>
                        </div>

                        <div class="form-check">
                            <input type="hidden" name="send_now" value="0">
                            <input type="checkbox" name="send_now" value="1" id="sendNow"
                                class="form-check-input" {{ old('send_now') ? 'checked' : '' }}>
                            <label class="form-check-label" for="sendNow">
                                <strong>Send Immediately</strong>
                                <span class="d-block text-muted small">
                                    If unchecked, saves as draft for later
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-purple w-100 mb-2">
                            <i class="fas fa-paper-plane me-1"></i> Save Broadcast
                        </button>
                        <a href="{{ route('admin.broadcasts.index') }}" class="btn btn-outline-secondary w-100">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const titleInput = document.getElementById('inputTitle');
                const bodyInput = document.getElementById('inputBody');
                const titleCount = document.getElementById('titleCount');
                const bodyCount = document.getElementById('bodyCount');
                const previewTitle = document.getElementById('previewTitle');
                const previewBody = document.getElementById('previewBody');

                // Live counters + preview
                function updatePreview() {
                    const t = titleInput.value || 'Notification Title';
                    const b = bodyInput.value || 'Your message will appear here...';
                    previewTitle.textContent = t;
                    previewBody.textContent = b;
                    titleCount.textContent = titleInput.value.length;
                    bodyCount.textContent = bodyInput.value.length;
                }

                titleInput.addEventListener('input', updatePreview);
                bodyInput.addEventListener('input', updatePreview);
                updatePreview();

                // Audience selector
                const audienceSelect = document.getElementById('audienceSelect');
                const customUsersBlock = document.getElementById('customUsersBlock');
                const recipientCount = document.getElementById('recipientCount');
                const selectedUsers = [];
                const selectedUsersEl = document.getElementById('selectedUsers');
                const hiddenUserIds = document.getElementById('hiddenUserIds');
                const searchResults = document.getElementById('searchResults');
                const userSearch = document.getElementById('userSearch');

                audienceSelect.addEventListener('change', function() {
                    if (this.value === 'custom') {
                        customUsersBlock.classList.remove('d-none');
                    } else {
                        customUsersBlock.classList.add('d-none');
                    }
                    updateRecipientCount();
                });

                // Update recipient count (AJAX)
                function updateRecipientCount() {
                    recipientCount.textContent = 'Loading...';

                    const formData = new FormData();
                    formData.append('audience', audienceSelect.value);
                    selectedUsers.forEach((u, i) => formData.append(`custom_user_ids[${i}]`, u.id));
                    formData.append('_token', '{{ csrf_token() }}');

                    fetch('{{ route('admin.broadcasts.preview-audience') }}', {
                            method: 'POST',
                            body: formData,
                        })
                        .then(r => r.json())
                        .then(data => {
                            recipientCount.textContent = data.count.toLocaleString() + ' users';
                        })
                        .catch(() => {
                            recipientCount.textContent = '—';
                        });
                }

                updateRecipientCount();

                // User search
                let searchTimeout;
                userSearch.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    const q = this.value.trim();
                    if (q.length < 2) {
                        searchResults.innerHTML = '';
                        return;
                    }

                    searchTimeout = setTimeout(() => {
                        fetch(
                                `{{ route('admin.broadcasts.search-users') }}?q=${encodeURIComponent(q)}`
                            )
                            .then(r => r.json())
                            .then(data => {
                                searchResults.innerHTML = '';
                                data.data.forEach(user => {
                                    if (selectedUsers.find(u => u.id === user.id)) return;

                                    const div = document.createElement('div');
                                    div.className =
                                        'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
                                    div.style.cursor = 'pointer';
                                    div.innerHTML = `
                            <div>
                                <div class="fw-semibold small">${user.firstname} ${user.lastname}</div>
                                <div class="text-muted small">${user.email}</div>
                            </div>
                            <span class="badge bg-secondary">${user.role}</span>
                        `;
                                    div.addEventListener('click', () => addUser(user));
                                    searchResults.appendChild(div);
                                });
                            });
                    }, 300);
                });

                function addUser(user) {
                    if (selectedUsers.find(u => u.id === user.id)) return;
                    selectedUsers.push(user);
                    renderSelectedUsers();
                    searchResults.innerHTML = '';
                    userSearch.value = '';
                    updateRecipientCount();
                }

                function removeUser(id) {
                    const idx = selectedUsers.findIndex(u => u.id === id);
                    if (idx >= 0) selectedUsers.splice(idx, 1);
                    renderSelectedUsers();
                    updateRecipientCount();
                }

                function renderSelectedUsers() {
                    selectedUsersEl.innerHTML = '';
                    hiddenUserIds.innerHTML = '';

                    selectedUsers.forEach(user => {
                        const chip = document.createElement('div');
                        chip.className =
                            'badge bg-purple-light text-purple d-flex align-items-center gap-1 px-2 py-1';
                        chip.innerHTML = `
                <span>${user.firstname} ${user.lastname}</span>
                <i class="fas fa-times" style="cursor: pointer;"></i>
            `;
                        chip.querySelector('i').addEventListener('click', () => removeUser(user.id));
                        selectedUsersEl.appendChild(chip);

                        const hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = 'custom_user_ids[]';
                        hidden.value = user.id;
                        hiddenUserIds.appendChild(hidden);
                    });
                }
            });
        </script>
    @endpush

    <style>
        .bg-purple-light {
            background-color: #ede9fe !important;
        }

        .text-purple {
            color: #6d28d9 !important;
        }
    </style>

@endsection
