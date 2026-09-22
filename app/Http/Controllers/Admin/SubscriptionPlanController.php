<?php
// app/Http/Controllers/Admin/SubscriptionPlanController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubscriptionPlanController extends Controller
{
    public function index(Request $request)
    {
        $plans = SubscriptionPlan::when($request->search, function ($q) use ($request) {
                return $q->where('name', 'LIKE', "%{$request->search}%");
            })
            ->orderBy('sort_order')
            ->paginate(20);

        return view('admin.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.plans.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'base_credits' => 'required|integer|min:1',
            'bonus_credits' => 'nullable|integer|min:0',
            'icon' => 'nullable|string|max:50',
            'badge' => 'nullable|string|max:50',
            'tagline' => 'nullable|string|max:50',
            'star_rating' => 'nullable|integer|min:1|max:5',
            'sort_order' => 'nullable|integer',
            'is_popular' => 'boolean',
            'is_active' => 'boolean',
            'features' => 'nullable|array',
            'features.*' => 'string|max:255',
        ]);

        $validated['plan_uuid'] = (string) Str::uuid();
        $validated['slug'] = Str::slug($validated['name']);
        $validated['bonus_credits'] = $validated['bonus_credits'] ?? 0;
        $validated['total_credits'] = $validated['base_credits'] + $validated['bonus_credits'];
        $validated['is_popular'] = $request->boolean('is_popular');
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['star_rating'] = $validated['star_rating'] ?? 3;

        SubscriptionPlan::create($validated);

        return redirect()
            ->route('admin.plans.index')
            ->with('success', 'Plan created successfully.');
    }

    public function edit(SubscriptionPlan $plan)
    {
        return view('admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, SubscriptionPlan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'base_credits' => 'required|integer|min:1',
            'bonus_credits' => 'nullable|integer|min:0',
            'icon' => 'nullable|string|max:50',
            'badge' => 'nullable|string|max:50',
            'tagline' => 'nullable|string|max:50',
            'star_rating' => 'nullable|integer|min:1|max:5',
            'sort_order' => 'nullable|integer',
            'features' => 'nullable|array',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['bonus_credits'] = $validated['bonus_credits'] ?? 0;
        $validated['total_credits'] = $validated['base_credits'] + $validated['bonus_credits'];
        $validated['is_popular'] = $request->boolean('is_popular');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['star_rating'] = $validated['star_rating'] ?? 3;

        $plan->update($validated);

        return redirect()
            ->route('admin.plans.index')
            ->with('success', 'Plan updated successfully.');
    }

    public function destroy(SubscriptionPlan $plan)
    {
        if ($plan->transactions()->exists()) {
            return back()->with('error', 'Cannot delete plan with existing transactions. Deactivate instead.');
        }

        $plan->delete();

        return redirect()
            ->route('admin.plans.index')
            ->with('success', 'Plan deleted successfully.');
    }

    public function toggleStatus(SubscriptionPlan $plan)
    {
        $plan->update(['is_active' => !$plan->is_active]);

        return back()->with('success', $plan->is_active ? 'Plan activated.' : 'Plan deactivated.');
    }
}