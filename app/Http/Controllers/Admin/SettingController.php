<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * عرض صفحة الإعدادات
     */
    public function index()
    {
        $settings = Setting::all()->groupBy('group');
        return view('admin.settings.index', compact('settings'));
    }

    /**
     * تحديث الإعدادات
     */
    public function update(Request $request)
    {
        $updated = 0;

        foreach ($request->all() as $key => $value) {
            // Handle file uploads
            if ($request->hasFile($key)) {
                $setting = Setting::where('key', $key)->first();
                if ($setting) {
                    $oldValue = $setting->value;
                    $path = $request->file($key)->store('settings', 'public');
                    $setting->update(['value' => $path]);
                    
                    // Clear cache
                    \Cache::forget("setting.{$key}");
                    $updated++;
                    continue;
                }
            }

            $setting = Setting::where('key', $key)->first();

            if ($setting) {
                $oldValue = $setting->value;

                // Handle checkbox (boolean) values
                if ($setting->type === 'boolean') {
                    $value = $request->has($key) ? '1' : '0';
                }

                if ($oldValue !== $value) {
                    $setting->update(['value' => $value]);

                    // Clear cache
                    \Cache::forget("setting.{$key}");

                    $updated++;
                }
            }
        }

        // Log activity
        if ($updated > 0) {
            ActivityLog::log(
                'update',
                'Settings',
                null,
                null,
                "تحديث {$updated} إعداد في النظام"
            );
        }

        return back()->with(
            'success',
            $updated > 0
                ? "تم تحديث {$updated} إعداد بنجاح."
                : 'لم يتم تغيير أي إعدادات.'
        );
    }
}
