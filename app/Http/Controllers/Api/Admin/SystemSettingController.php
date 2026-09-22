<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    public function index(Request $request)
    {
        $settings = SystemSetting::when($request->group, function ($q) use ($request) {
                return $q->where('group', $request->group);
            })
            ->orderBy('group')
            ->orderBy('key')
            ->get();

        // Group by group
        $grouped = $settings->groupBy('group')->map(function ($items) {
            return $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'key' => $item->key,
                    'value' => $item->type === 'boolean' 
                        ? (bool) $item->value 
                        : ($item->type === 'number' ? (float) $item->value : $item->value),
                    'type' => $item->type,
                    'label' => $item->label,
                    'description' => $item->description,
                    'is_public' => $item->is_public,
                ];
            });
        });

        return response()->json([
            'success' => true,
            'data' => $grouped,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string|exists:system_settings,key',
            'settings.*.value' => 'nullable',
        ]);

        foreach ($request->settings as $item) {
            $setting = SystemSetting::where('key', $item['key'])->first();
            
            if ($setting) {
                SystemSetting::set(
                    $item['key'],
                    $item['value'],
                    $setting->type,
                    $setting->group
                );
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully.',
        ]);
    }

    /**
     * Public settings for frontend (no secrets)
     */
    public function publicSettings()
    {
        $settings = SystemSetting::public()
            ->where('group', 'payment')
            ->orWhere('group', 'credits')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->key => $item->value];
            });

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }
}