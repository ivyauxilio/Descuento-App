@php
    $enabledKey = "payment.{$id}_enabled";
    $publicKey = "payment.{$id}_public_key";
    $secretKey = "payment.{$id}_secret_key";

    $colorMap = [
        'info' => ['bg-info-subtle', 'text-info'],
        'primary' => ['bg-primary-subtle', 'text-primary'],
        'success' => ['bg-success-subtle', 'text-success'],
    ];
    [$bgColor, $textColor] = $colorMap[$color] ?? $colorMap['info'];
@endphp

<div class="card mb-4" id="gateway-{{ $id }}">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center gap-3">
                <div class="{{ $bgColor }} rounded d-flex align-items-center justify-content-center"
                    style="width: 44px; height: 44px;">
                    <i class="fas {{ $icon }} {{ $textColor }}"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold">{{ $title }}</h6>
                    <small class="text-muted">Configure {{ $title }} payment gateway</small>
                </div>
            </div>

            {{-- Toggle --}}
            <div class="form-check form-switch">
                <input type="hidden" name="settings[{{ $enabledKey }}]" value="0">
                <input type="checkbox" class="form-check-input" name="settings[{{ $enabledKey }}]" value="1"
                    id="toggle-{{ $id }}" {{ $settings[$enabledKey]['value'] ?? false ? 'checked' : '' }}
                    onchange="toggleGateway('{{ $id }}')">
            </div>
        </div>

        <div id="fields-{{ $id }}" class="{{ $settings[$enabledKey]['value'] ?? false ? '' : 'd-none' }}">
            <div class="mb-3">
                <label class="form-label">Public Key</label>
                <input type="text" name="settings[{{ $publicKey }}]"
                    value="{{ $settings[$publicKey]['value'] ?? '' }}" class="form-control" placeholder="pk_live_...">
            </div>

            <div class="mb-3">
                <label class="form-label">Secret Key</label>
                <input type="password" name="settings[{{ $secretKey }}]"
                    value="{{ $settings[$secretKey]['value'] ?? '' }}" class="form-control" placeholder="sk_live_...">
                <small class="text-muted">
                    <i class="fas fa-shield-alt"></i> Stored securely. Never exposed to clients.
                </small>
            </div>
        </div>

        <div id="disabled-msg-{{ $id }}"
            class="text-center py-3 {{ $settings[$enabledKey]['value'] ?? false ? 'd-none' : '' }}">
            <small class="text-muted">Enable to configure {{ $title }}</small>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function toggleGateway(id) {
            const checked = document.getElementById('toggle-' + id).checked;
            document.getElementById('fields-' + id).classList.toggle('d-none', !checked);
            document.getElementById('disabled-msg-' + id).classList.toggle('d-none', checked);
        }
    </script>
@endpush
