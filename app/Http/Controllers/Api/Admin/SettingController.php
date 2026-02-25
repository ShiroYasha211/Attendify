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

        foreach ($settings as $setting) {
            if ($request->has($setting->key)) {
                $value = $setting->type === 'boolean'
                    ? ($request->boolean($setting->key) ? '1' : '0')
                    : $request->input($setting->key);

                $setting->update(['value' => $value]);
            }
        }

        Cache::flush();

        ActivityLog::log('update', 'Setting', null, null, 'تم تحديث إعدادات النظام');

        return $this->success(Setting::all()->groupBy('group'), 'تم تحديث الإعدادات بنجاح');
    }
}
