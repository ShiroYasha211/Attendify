<?php

namespace App\Http\Controllers\Administrative;

use App\Http\Controllers\Api\Administrative\AdministrativeApiController;
use Illuminate\Http\Request;

class ApiCollegeSettingsController extends AdministrativeApiController
{
    public function show()
    {
        $college = $this->college();

        return $this->success([
            'id' => $college->id,
            'name' => $college->name,
            'absence_deprivation_percentage' => $college->absence_deprivation_percentage,
            'excuses_deadline_days' => $college->excuses_deadline_days,
            'excuse_receiver' => $college->excuse_receiver,
            'qr_rotation_seconds' => $college->qr_rotation_seconds,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'absence_deprivation_percentage' => 'required|integer|min:1|max:100',
            'excuses_deadline_days' => 'required|integer|min:1|max:30',
            'excuse_receiver' => 'required|in:administrative,doctor',
            'qr_rotation_seconds' => 'required|integer|min:5|max:300',
        ]);

        $college = $this->college();
        $college->update($validated);

        return $this->success([
            'id' => $college->id,
            'absence_deprivation_percentage' => $college->absence_deprivation_percentage,
            'excuses_deadline_days' => $college->excuses_deadline_days,
            'excuse_receiver' => $college->excuse_receiver,
            'qr_rotation_seconds' => $college->qr_rotation_seconds,
        ], 'College settings updated successfully.');
    }
}
