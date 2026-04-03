<?php

namespace App\Http\Controllers\Api\Doctor;

use Illuminate\Http\Request;

class FinancialController extends DoctorApiController
{
    public function ledger(Request $request)
    {
        $transactions = $request->user()
            ->transactions()
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return $this->success([
            'balance' => $request->user()->balance,
            'transactions' => $transactions,
        ]);
    }

    public function exportPdf(Request $request)
    {
        return $this->success([
            'export_url' => route('doctor.ledger.export'),
            'message' => 'يرجى استخدام هذا الرابط لتحميل كشف الحساب كملف PDF.',
        ]);
    }
}
