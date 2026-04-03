<?php

namespace App\Http\Controllers\Api\Administrative;

use Illuminate\Http\Request;

class FinancialController extends AdministrativeApiController
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

    public function exportPdf()
    {
        return $this->success([
            'export_url' => route('administrative.ledger.export'),
        ]);
    }
}
