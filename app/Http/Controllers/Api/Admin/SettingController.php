<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Setting;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingController extends AdminApiController
{
    public function index()
    {
        $settings = Setting::all()->groupBy('group');
        return $this->success($settings);
    }

    public function update(Request $request)
    {
        $settings = Setting::all();
        $updated = 0;

        foreach ($settings as $setting) {
            $key = $setting->key;

            // Handle file uploads (Logo/Favicon)
            if ($request->hasFile($key)) {
                $path = $request->file($key)->store('settings', 'public');
                $setting->update(['value' => $path]);
                Cache::forget("setting.{$key}");
                $updated++;
                continue;
            }

            if ($request->has($key)) {
                $value = $setting->type === 'boolean'
                    ? ($request->boolean($key) ? '1' : '0')
                    : $request->input($key);

                if ($setting->value !== $value) {
                    $setting->update(['value' => $value]);
                    Cache::forget("setting.{$key}");
                    $updated++;
                }
            }
        }

        if ($updated > 0) {
            ActivityLog::log('update', 'Setting', null, null, "تحديث {$updated} إعداد عبر الـ API");
        }

        return $this->success(Setting::all()->groupBy('group'), "تم تحديث {$updated} إعداد بنجاح");
    }
}
