<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class SubscriptionPlanController extends Controller
{
    public function index(Request $request)
    {
        $plans = SubscriptionPlan::when($request->search, function ($q) use ($request) {
                return $q->where('name', 'LIKE', "%{$request->search}%");
            })
            ->orderBy('sort_order')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $plans,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
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
        ]);

        $plan = SubscriptionPlan::create([
            'plan_uuid' => (string) Str::uuid(),
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'price' => $request->price,
            'currency' => $request->currency ?? 'PHP',
            'base_credits' => $request->base_credits,
            'bonus_credits' => $request->bonus_credits ?? 0,
            'total_credits' => $request->base_credits + ($request->bonus_credits ?? 0),
            'icon' => $request->icon,
            'badge' => $request->badge,
            'tagline' => $request->tagline,
            'star_rating' => $request->star_rating ?? 3,
            'sort_order' => $request->sort_order ?? 0,
            'is_popular' => $request->is_popular ?? false,
            'is_active' => $request->is_active ?? true,
            'features' => $request->features ?? [],
        ]);

        return response()->json([
            'success' => true,
            'data' => $plan,
            'message' => 'Plan created successfully.',
        ]);
    }

    public function show($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $plan,
        ]);
    }

    public function update(Request $request, $id)
    {
        $plan = SubscriptionPlan::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'base_credits' => 'sometimes|integer|min:1',
            'bonus_credits' => 'nullable|integer|min:0',
            'icon' => 'nullable|string|max:50',
            'badge' => 'nullable|string|max:50',
            'tagline' => 'nullable|string|max:50',
            'star_rating' => 'nullable|integer|min:1|max:5',
            'sort_order' => 'nullable|integer',
            'is_popular' => 'boolean',
            'is_active' => 'boolean',
            'features' => 'nullable|array',
        ]);

        $plan->update($request->except(['plan_uuid', 'slug']));

        if ($request->has('name')) {
            $plan->slug = Str::slug($request->name);
        }

        // Recompute totals
        $plan->total_credits = $plan->base_credits + $plan->bonus_credits;
        if ($plan->total_credits > 0) {
            $plan->cost_per_credit = round($plan->price / $plan->total_credits, 2);
        }

        $plan->save();

        return response()->json([
            'success' => true,
            'data' => $plan,
            'message' => 'Plan updated successfully.',
        ]);
    }

    public function destroy($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);

        // Prevent deleting a plan that has been used
        if ($plan->transactions()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete plan that has transactions. Deactivate it instead.',
            ], 422);
        }

        $plan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Plan deleted successfully.',
        ]);
    }

    public function toggleStatus($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);
        $plan->is_active = !$plan->is_active;
        $plan->save();

        return response()->json([
            'success' => true,
            'data' => $plan,
            'message' => $plan->is_active ? 'Plan activated.' : 'Plan deactivated.',
        ]);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'plans' => 'required|array',
            'plans.*.plan_id' => 'required|exists:subscription_plans,plan_id',
            'plans.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($request->plans as $item) {
            SubscriptionPlan::where('plan_id', $item['plan_id'])
                ->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Plans reordered.',
        ]);
    }
}