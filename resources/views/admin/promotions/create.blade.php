@extends('layouts.admin')

@section('title', 'Create Promotion')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Create Promotion</h1>
                <p class="text-muted small">Add a new promotion for a merchant</p>
            </div>
            <a href="{{ route('admin.promotions.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> Please fix the following errors:
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-header bg-white">
                <i class="fas fa-tags"></i> Promotion Information
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.promotions.store') }}">
                    @csrf

                    <div class="row">
                        <!-- Merchant -->
                        {{-- <div class="col-md-6 mb-3">
                            <label for="merchant_id" class="form-label fw-bold">Merchant <span
                                    class="text-danger">*</span></label>
                            <select name="merchant_id" id="merchant_id"
                                class="form-select @error('merchant_id') is-invalid @enderror" required>
                                <option value="">Select Merchant</option>
                                @foreach ($merchants as $merchant)
                                    <option value="{{ $merchant->merchant_id }}"
                                        {{ old('merchant_id') == $merchant->merchant_id ? 'selected' : '' }}>
                                        {{ $merchant->business_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('merchant_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div> --}}
                        <div class="col-md-6 mb-3">
                            <label for="merchant_search" class="form-label fw-bold">
                                Merchant <span class="text-danger">*</span>
                            </label>

                            <!-- Search Input -->
                            <div class="position-relative">
                                <input type="text" id="merchant_search"
                                    class="form-control @error('merchant_id') is-invalid @enderror"
                                    placeholder="Type to search for a merchant..." autocomplete="off"
                                    value="{{ old('business_name') }}">

                                <!-- Loading indicator -->
                                <div id="search_loading" class="position-absolute"
                                    style="right: 10px; top: 50%; transform: translateY(-50%); display: none;">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>

                                <!-- Suggestions Dropdown -->
                                <div id="merchant_suggestions" class="dropdown-menu w-100"
                                    style="display: none; max-height: 350px; overflow-y: auto; position: absolute; z-index: 9999;
                                        background: white; border: 1px solid #dee2e6; border-radius: 0.375rem; 
                                        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);">
                                </div>
                            </div>

                            <!-- Hidden input for form submission -->
                            <input type="hidden" name="merchant_id" id="merchant_id" value="{{ old('merchant_id') }}">
                            <input type="hidden" name="business_name" id="business_name_hidden"
                                value="{{ old('business_name') }}">

                            @error('merchant_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i>
                                Type to search by business name, owner name, or email
                            </small>
                        </div>
                        <!-- Title -->
                        <div class="col-md-6 mb-3">
                            <label for="title" class="form-label fw-bold">Title <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="title" id="title"
                                class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}"
                                placeholder="e.g., Summer Sale 2024" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Promo Type -->
                        <div class="col-md-6 mb-3">
                            <label for="promo_type" class="form-label fw-bold">Promotion Type <span
                                    class="text-danger">*</span></label>
                            <select name="promo_type" id="promo_type"
                                class="form-select @error('promo_type') is-invalid @enderror" required>
                                <option value="">Select Type</option>
                                @foreach ($promoTypes as $type)
                                    <option value="{{ $type }}" {{ old('promo_type') == $type ? 'selected' : '' }}>
                                        {{ ucfirst($type) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('promo_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Value -->
                        <div class="col-md-6 mb-3">
                            <label for="value" class="form-label fw-bold">Value <span
                                    class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="value" id="value"
                                class="form-control @error('value') is-invalid @enderror" value="{{ old('value', 0) }}"
                                required>
                            @error('value')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">For percentage: e.g., 20 | For fixed: e.g., 100 | For BOGO: 0</small>
                        </div>

                        <!-- Min Order Amount -->
                        <div class="col-md-6 mb-3">
                            <label for="min_order_amount" class="form-label fw-bold">Minimum Order Amount</label>
                            <input type="number" step="0.01" name="min_order_amount" id="min_order_amount"
                                class="form-control @error('min_order_amount') is-invalid @enderror"
                                value="{{ old('min_order_amount') }}" placeholder="Optional">
                            @error('min_order_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Min Quantity -->
                        <div class="col-md-6 mb-3">
                            <label for="min_quantity" class="form-label fw-bold">Minimum Quantity</label>
                            <input type="number" name="min_quantity" id="min_quantity"
                                class="form-control @error('min_quantity') is-invalid @enderror"
                                value="{{ old('min_quantity') }}" placeholder="Optional">
                            @error('min_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Start Date -->
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label fw-bold">Start Date <span
                                    class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="start_date"
                                class="form-control @error('start_date') is-invalid @enderror"
                                value="{{ old('start_date', now()->format('Y-m-d')) }}" required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- End Date -->
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label fw-bold">End Date</label>
                            <input type="date" name="end_date" id="end_date"
                                class="form-control @error('end_date') is-invalid @enderror"
                                value="{{ old('end_date') }}">
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Leave blank for no expiry</small>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label fw-bold">Status <span
                                    class="text-danger">*</span></label>
                            <select name="status" id="status"
                                class="form-select @error('status') is-invalid @enderror" required>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}"
                                        {{ old('status', 'active') == $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Category -->
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label fw-bold">Category</label>
                            <select name="category_id" id="category_id"
                                class="form-select @error('category_id') is-invalid @enderror">
                                <option value="">Select Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->category_id }}"
                                        {{ old('category_id') == $category->category_id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Free Menu Item -->
                        <div class="col-md-6 mb-3">
                            <label for="free_menu_item_id" class="form-label fw-bold">Free Menu Item</label>
                            <select name="free_menu_item_id" id="free_menu_item_id"
                                class="form-select @error('free_menu_item_id') is-invalid @enderror">
                                <option value="">Select Free Item</option>
                                @foreach ($menuItems as $item)
                                    <option value="{{ $item->menu_item_id }}"
                                        {{ old('free_menu_item_id') == $item->menu_item_id ? 'selected' : '' }}>
                                        {{ $item->name }} ({{ $item->merchant->business_name ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('free_menu_item_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">For BOGO promotions - the free item</small>
                        </div>

                        <!-- Required Menu Item -->
                        <div class="col-md-6 mb-3">
                            <label for="required_menu_item_id" class="form-label fw-bold">Required Menu Item</label>
                            <select name="required_menu_item_id" id="required_menu_item_id"
                                class="form-select @error('required_menu_item_id') is-invalid @enderror">
                                <option value="">Select Required Item</option>
                                @foreach ($menuItems as $item)
                                    <option value="{{ $item->menu_item_id }}"
                                        {{ old('required_menu_item_id') == $item->menu_item_id ? 'selected' : '' }}>
                                        {{ $item->name }} ({{ $item->merchant->business_name ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('required_menu_item_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">For BOGO promotions - the required item to buy</small>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Promotion
                        </button>
                        <a href="{{ route('admin.promotions.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Update value label based on type
            document.getElementById('promo_type').addEventListener('change', function() {
                const valueLabel = document.querySelector('label[for="value"]');
                const valueInput = document.getElementById('value');

                if (this.value === 'percentage') {
                    valueLabel.textContent = 'Value (Percentage) *';
                    valueInput.placeholder = 'e.g., 20';
                    valueInput.step = '0.01';
                } else if (this.value === 'fixed') {
                    valueLabel.textContent = 'Value (Amount) *';
                    valueInput.placeholder = 'e.g., 100';
                    valueInput.step = '0.01';
                } else if (this.value === 'bogo') {
                    valueLabel.textContent = 'Value (BOGO) *';
                    valueInput.placeholder = '0';
                    valueInput.step = '1';
                } else {
                    valueLabel.textContent = 'Value *';
                    valueInput.placeholder = '0';
                }
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ============================================
            // MERCHANT SEARCH AUTOCOMPLETE
            // ============================================
            const searchInput = document.getElementById('merchant_search');
            const merchantIdInput = document.getElementById('merchant_id');
            const businessNameHidden = document.getElementById('business_name_hidden');
            const suggestionsDiv = document.getElementById('merchant_suggestions');
            const loadingDiv = document.getElementById('search_loading');

            // All merchants data (passed from controller)
            const allMerchants = @json($merchants);

            let selectedMerchantId = null;
            let searchTimeout = null;
            let currentResults = [];

            // ============================================
            // FILTER MERCHANTS
            // ============================================
            function filterMerchants(query) {
                if (!query || query.length < 1) {
                    return [];
                }

                const lowerQuery = query.toLowerCase();

                return allMerchants.filter(merchant => {
                    // Search by business name
                    if (merchant.business_name && merchant.business_name.toLowerCase().includes(
                            lowerQuery)) {
                        return true;
                    }

                    // Search by owner name
                    if (merchant.owner_name && merchant.owner_name.toLowerCase().includes(lowerQuery)) {
                        return true;
                    }

                    // Search by email
                    if (merchant.email && merchant.email.toLowerCase().includes(lowerQuery)) {
                        return true;
                    }

                    // Search by owner email
                    if (merchant.owner_email && merchant.owner_email.toLowerCase().includes(lowerQuery)) {
                        return true;
                    }

                    // Search by city
                    if (merchant.city && merchant.city.toLowerCase().includes(lowerQuery)) {
                        return true;
                    }

                    return false;
                });
            }

            // ============================================
            // DISPLAY SUGGESTIONS
            // ============================================
            function showSuggestions(results) {
                currentResults = results;

                if (results.length === 0) {
                    suggestionsDiv.innerHTML = `
                <div class="px-3 py-3 text-center text-muted">
                    <i class="fas fa-search"></i> No merchants found
                </div>
            `;
                    suggestionsDiv.style.display = 'block';
                    return;
                }

                let html = '';
                results.forEach((merchant, index) => {
                    const highlightedName = highlightText(merchant.business_name, searchInput.value);
                    const highlightedOwner = merchant.owner_name ? highlightText(merchant.owner_name,
                        searchInput.value) : '';

                    html += `
                <a href="#" class="dropdown-item merchant-item ${index === 0 ? 'active' : ''}" 
                   data-id="${merchant.merchant_id}" 
                   data-name="${merchant.business_name}"
                   data-index="${index}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>${highlightedName}</strong>
                            ${merchant.owner_name ? `<br><small class="text-muted">
                                                        <i class="fas fa-user"></i> Owner: ${highlightedOwner}
                                                    </small>` : ''}
                            ${merchant.email ? `<br><small class="text-muted">
                                                        <i class="fas fa-envelope"></i> ${merchant.email}
                                                    </small>` : ''}
                            ${merchant.city ? `<br><small class="text-muted">
                                                        <i class="fas fa-map-marker-alt"></i> ${merchant.city}
                                                    </small>` : ''}
                        </div>
                        ${merchant.status ? `<span class="badge bg-${merchant.status === 'active' ? 'success' : 'secondary'}">
                                                    ${merchant.status}
                                                </span>` : ''}
                    </div>
                </a>
            `;
                });

                suggestionsDiv.innerHTML = html;
                suggestionsDiv.style.display = 'block';

                // Add click event to items
                document.querySelectorAll('.merchant-item').forEach(item => {
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        const id = this.dataset.id;
                        const name = this.dataset.name;
                        selectMerchant(id, name);
                    });

                    // Hover effect
                    item.addEventListener('mouseenter', function() {
                        document.querySelectorAll('.merchant-item').forEach(i => i.classList.remove(
                            'active'));
                        this.classList.add('active');
                    });
                });
            }

            // ============================================
            // HIGHLIGHT MATCHING TEXT
            // ============================================
            function highlightText(text, query) {
                if (!text || !query) return text;
                const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
                return text.replace(regex, '<mark>$1</mark>');
            }

            // ============================================
            // SELECT MERCHANT
            // ============================================
            function selectMerchant(id, name) {
                selectedMerchantId = id;
                merchantIdInput.value = id;
                businessNameHidden.value = name;
                searchInput.value = name;
                searchInput.classList.remove('is-invalid');
                searchInput.classList.add('is-valid');
                suggestionsDiv.style.display = 'none';

                // Trigger change event for any listeners
                searchInput.dispatchEvent(new Event('change'));

                // Update any other fields that might depend on merchant selection
                console.log('Selected merchant:', id, name);
            }

            // ============================================
            // CLEAR SELECTION
            // ============================================
            function clearSelection() {
                selectedMerchantId = null;
                merchantIdInput.value = '';
                businessNameHidden.value = '';
                searchInput.value = '';
                searchInput.classList.remove('is-valid', 'is-invalid');
                suggestionsDiv.style.display = 'none';
            }

            // ============================================
            // EVENT LISTENERS
            // ============================================

            // Input handler with debounce
            searchInput.addEventListener('input', function() {
                const query = this.value.trim();

                // Clear previous timeout
                clearTimeout(searchTimeout);

                // If input is empty, clear selection and hide suggestions
                if (!query) {
                    // Only clear if not selected
                    if (!selectedMerchantId) {
                        merchantIdInput.value = '';
                        businessNameHidden.value = '';
                    }
                    suggestionsDiv.style.display = 'none';
                    searchInput.classList.remove('is-invalid');
                    return;
                }

                // If the current value matches a selected merchant, keep it
                if (selectedMerchantId) {
                    const selectedMerchant = allMerchants.find(m => m.merchant_id === selectedMerchantId);
                    if (selectedMerchant && selectedMerchant.business_name.toLowerCase() === query
                        .toLowerCase()) {
                        suggestionsDiv.style.display = 'none';
                        return;
                    }
                }

                // Show loading
                loadingDiv.style.display = 'block';

                // Debounce search
                searchTimeout = setTimeout(() => {
                    const results = filterMerchants(query);
                    loadingDiv.style.display = 'none';

                    if (results.length > 0) {
                        showSuggestions(results);
                        // Clear selected if not in results
                        if (selectedMerchantId) {
                            const stillExists = results.some(m => m.merchant_id ===
                                selectedMerchantId);
                            if (!stillExists) {
                                clearSelection();
                            }
                        }
                    } else {
                        suggestionsDiv.innerHTML = `
                    <div class="px-3 py-3 text-center text-muted">
                        <i class="fas fa-search"></i> No merchants found
                    </div>
                `;
                        suggestionsDiv.style.display = 'block';
                        // Clear selection
                        if (selectedMerchantId) {
                            clearSelection();
                        }
                    }
                }, 300);
            });

            // Keyboard navigation
            searchInput.addEventListener('keydown', function(e) {
                const items = document.querySelectorAll('.merchant-item');
                if (items.length === 0) return;

                let currentIndex = -1;
                items.forEach((item, index) => {
                    if (item.classList.contains('active')) {
                        currentIndex = index;
                        item.classList.remove('active');
                    }
                });

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    const nextIndex = Math.min(currentIndex + 1, items.length - 1);
                    items[nextIndex].classList.add('active');
                    items[nextIndex].scrollIntoView({
                        block: 'nearest'
                    });
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    const prevIndex = Math.max(currentIndex - 1, 0);
                    items[prevIndex].classList.add('active');
                    items[prevIndex].scrollIntoView({
                        block: 'nearest'
                    });
                } else if (e.key === 'Enter') {
                    const activeItem = document.querySelector('.merchant-item.active');
                    if (activeItem) {
                        e.preventDefault();
                        activeItem.click();
                    }
                } else if (e.key === 'Escape') {
                    suggestionsDiv.style.display = 'none';
                    searchInput.blur();
                }
            });

            // Focus handler - show suggestions if there's text
            searchInput.addEventListener('focus', function() {
                if (this.value.trim()) {
                    const results = filterMerchants(this.value.trim());
                    if (results.length > 0) {
                        showSuggestions(results);
                    }
                }
            });

            // Blur handler - hide suggestions after delay
            searchInput.addEventListener('blur', function() {
                setTimeout(() => {
                    suggestionsDiv.style.display = 'none';
                }, 200);
            });

            // If there's a preselected merchant
            @if (old('merchant_id') || (isset($promotion) && $promotion->merchant_id))
                var merchantId = '{{ old('merchant_id', $promotion->merchant_id ?? '') }}';
                var merchantName = '{{ old('business_name', $promotion->merchant->business_name ?? '') }}';
                if (merchantId && merchantName) {
                    selectMerchant(merchantId, merchantName);
                }
            @endif

            // ============================================
            // CLOSE DROPDOWN ON OUTSIDE CLICK
            // ============================================
            document.addEventListener('click', function(e) {
                const container = document.querySelector('.col-md-6.mb-3 .position-relative');
                if (container && !container.contains(e.target)) {
                    suggestionsDiv.style.display = 'none';
                }
            });

            // ============================================
            // FORM VALIDATION (Optional)
            // ============================================
            document.getElementById('promotionForm').addEventListener('submit', function(e) {
                if (!merchantIdInput.value) {
                    e.preventDefault();
                    searchInput.classList.add('is-invalid');
                    alert('Please select a merchant from the suggestions.');
                    searchInput.focus();
                }
            });
        });
    </script>

    <style>
        /* Custom styles for merchant suggestions */
        .dropdown-item.merchant-item {
            padding: 10px 15px;
            border-bottom: 1px solid #f8f9fa;
            cursor: pointer;
        }

        .dropdown-item.merchant-item:last-child {
            border-bottom: none;
        }

        .dropdown-item.merchant-item:hover,
        .dropdown-item.merchant-item.active {
            background-color: #f0f7ff;
        }

        .dropdown-item.merchant-item mark {
            background-color: #ffeb3b;
            padding: 0 2px;
            border-radius: 2px;
        }

        .dropdown-item.merchant-item .badge {
            font-size: 10px;
            padding: 3px 8px;
        }
    </style>
@endsection
