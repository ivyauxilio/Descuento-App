<?php
// app/Http/Controllers/Admin/SystemSettingController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    public function payment()
    {
        $settings = $this->getGroupSettings('payment');
        return view('admin.settings.payment', compact('settings'));
    }

    public function credits()
    {
        $settings = $this->getGroupSettings('credits');
        return view('admin.settings.credits', compact('settings'));
    }

    public function updatePayment(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($request->settings as $key => $value) {
            $setting = SystemSetting::where('key', $key)->first();
            if ($setting) {
                SystemSetting::set($key, $value, $setting->type, $setting->group);
            }
        }

        return back()->with('success', 'Payment settings updated.');
    }

    public function updateCredits(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($request->settings as $key => $value) {
            $setting = SystemSetting::where('key', $key)->first();
            if ($setting) {
                SystemSetting::set($key, $value, $setting->type, $setting->group);
            }
        }

        return back()->with('success', 'Credit settings updated.');
    }

    private function getGroupSettings(string $group)
    {
        return SystemSetting::where('group', $group)
            ->orderBy('key')
            ->get()
            ->mapWithKeys(function ($item) {
                $value = match($item->type) {
                    'boolean' => (bool) $item->value,
                    'number' => (float) $item->value,
                    'json' => json_decode($item->value, true),
                    default => $item->value,
                };
                return [$item->key => [
                    'value' => $value,
                    'type' => $item->type,
                    'label' => $item->label,
                    'description' => $item->description,
                ]];
            });
    }
}