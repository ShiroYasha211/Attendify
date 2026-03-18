<?php

namespace App\Http\Controllers\Api\Student;

use Illuminate\Http\Request;
use App\Models\Transaction;

class FinancialController extends StudentApiController
{
    /**
     * Show the user's financial ledger (account statement).
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
     * Export the account statement to PDF. (Return a URL or basic info since API clients might download it differently)
     * For full PDF generation, typically the client either constructs it or calls a dedicated web/export route.
     * We can return a direct download link that the client can hit with the token.
     */
    public function exportPdf(Request $request)
    {
        // For API, returning the URL to the web route might be easiest, 
        // or a signed URL, but here we can just return a success indicating the feature is available via the matching Web route or generate base64 if needed.
        // A common pattern is returning a direct URL that is auth-protected.
        
        $url = route('student.ledger.export');

        return $this->success([
            'export_url' => $url,
            'message' => 'يرجى استخدام هذا الرابط مع إرسال التوكن (Bearer Token) لتحميل كشف الحساب كملف PDF.',
        ]);
    }
}
