@php $isEdit = isset($plan); @endphp

<div class="row g-4">
    {{-- Main Form --}}
    <div class="col-lg-8">
        {{-- Basic Info --}}
        <div class="card mb-4">
            <div class="card-header">Basic Information</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Plan Name *</label>
                    <input type="text" name="name" value="{{ old('name', $plan->name ?? '') }}" required
                        class="form-control @error('name') is-invalid @enderror" placeholder="e.g., Starter Boost">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="3" class="form-control" placeholder="Brief description of the plan">{{ old('description', $plan->description ?? '') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Pricing --}}
        <div class="card mb-4">
            <div class="card-header">Pricing & Credits</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Price (₱) *</label>
                        <input type="number" name="price" step="0.01" min="0" required
                            value="{{ old('price', $plan->price ?? '') }}"
                            class="form-control @error('price') is-invalid @enderror">
                        @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Base Credits *</label>
                        <input type="number" name="base_credits" min="1" required
                            value="{{ old('base_credits', $plan->base_credits ?? '') }}"
                            class="form-control @error('base_credits') is-invalid @enderror">
                        @error('base_credits')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Bonus Credits</label>
                        <input type="number" name="bonus_credits" min="0"
                            value="{{ old('bonus_credits', $plan->bonus_credits ?? 0) }}" class="form-control">
                    </div>
                </div>
            </div>
        </div>

        {{-- Display Options --}}
        <div class="card mb-4">
            <div class="card-header">Display Options</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Icon</label>
                        <select name="icon" class="form-select">
                            @foreach (['rocket' => '🚀 Rocket (Starter)', 'chart' => '📈 Chart (Growth)', 'target' => '🎯 Target (Pro)', 'crown' => '👑 Crown (Premium)'] as $value => $label)
                                <option value="{{ $value }}"
                                    {{ old('icon', $plan->icon ?? '') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Star Rating</label>
                        <select name="star_rating" class="form-select">
                            @for ($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}"
                                    {{ old('star_rating', $plan->star_rating ?? 3) == $i ? 'selected' : '' }}>
                                    {{ $i }} {{ str_repeat('★', $i) }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tagline</label>
                        <input type="text" name="tagline" value="{{ old('tagline', $plan->tagline ?? '') }}"
                            class="form-control" placeholder="e.g., BEST VALUE">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Badge</label>
                        <input type="text" name="badge" value="{{ old('badge', $plan->badge ?? '') }}"
                            class="form-control" placeholder="e.g., MOST POPULAR">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" min="0"
                            value="{{ old('sort_order', $plan->sort_order ?? 0) }}" class="form-control">
                    </div>
                </div>
            </div>
        </div>

        {{-- Features --}}
        <div class="card mb-4" x-data="featuresManager({{ json_encode(old('features', $plan->features ?? [])) }})">
            <div class="card-header">Features</div>
            <div class="card-body">
                <div class="input-group mb-3">
                    <input type="text" x-model="newFeature" @keydown.enter.prevent="addFeature" class="form-control"
                        placeholder="Add a feature (e.g., Priority support)">
                    <button type="button" class="btn btn-purple" @click="addFeature">Add</button>
                </div>

                <div class="space-y-2">
                    <template x-for="(feature, index) in features" :key="index">
                        <div class="d-flex align-items-center gap-2 bg-light rounded px-3 py-2 mb-2">
                            <input type="text" :name="`features[${index}]`" x-model="features[index]"
                                class="form-control form-control-sm border-0 bg-transparent">
                            <button type="button" @click="removeFeature(index)"
                                class="btn btn-link btn-sm text-danger p-0">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </template>

                    <p x-show="features.length === 0" class="text-center text-muted small py-3">
                        No features added yet
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="col-lg-4">
        {{-- Status --}}
        <div class="card mb-4">
            <div class="card-header">Status</div>
            <div class="card-body">
                <div class="form-check mb-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input"
                        {{ old('is_active', $plan->is_active ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>

                <div class="form-check">
                    <input type="hidden" name="is_popular" value="0">
                    <input type="checkbox" name="is_popular" value="1" id="is_popular"
                        class="form-check-input" {{ old('is_popular', $plan->is_popular ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_popular">Mark as Popular</label>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="card">
            <div class="card-body">
                <button type="submit" class="btn btn-purple w-100 mb-2">
                    <i class="fas fa-save me-1"></i>
                    {{ $isEdit ? 'Save Changes' : 'Create Plan' }}
                </button>
                <a href="{{ route('admin.plans.index') }}" class="btn btn-outline-secondary w-100">
                    Cancel
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function featuresManager(initialFeatures = []) {
            return {
                features: Array.isArray(initialFeatures) ? initialFeatures : [],
                newFeature: '',
                addFeature() {
                    const val = this.newFeature.trim();
                    if (val && !this.features.includes(val)) {
                        this.features.push(val);
                        this.newFeature = '';
                    }
                },
                removeFeature(index) {
                    this.features.splice(index, 1);
                },
            };
        }
    </script>
@endpush
