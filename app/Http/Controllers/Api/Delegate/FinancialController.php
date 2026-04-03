<?php

namespace App\Http\Controllers\Api\Delegate;

use App\Http\Controllers\Api\Delegate\DelegateApiController;
use Illuminate\Http\Request;
use App\Models\Transaction;

class FinancialController extends DelegateApiController
{
    /**
     * Show the delegate's financial ledger (account statement).
     */
    public function ledger(Request $request)
    {
        $user = $request->user();
        
        $transactions = $user->transactions()
            ->latest()
            ->paginate(15);

        return $this->success([
            'balance' => $user->balance,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Export the account statement to PDF.
     * Returns a URL that the client can hit to download the PDF.
     */
    public function exportPdf(Request $request)
    {
        // For API, returning the URL to the web route.
        // The mobile app can open this URL in a browser or webview with the session/token.
        $url = route('delegate.ledger.export');

        return $this->success([
            'export_url' => $url,
            'message' => 'يرجى استخدام هذا الرابط لتحميل كشف الحساب كملف PDF.',
        ]);
    }
}
