<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CardController extends Controller
{
    public function index(Request $request)
    {
        $query = Card::with('user')->whereNull('generated_by_id')->latest();

        if ($request->has('status')) {
            if ($request->status == 'used') {
                $query->where('is_used', true);
            } elseif ($request->status == 'unused') {
                $query->where('is_used', false);
            }
        }

        $cards = $query->paginate(30);
        return view('admin.cards.index', compact('cards'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'count' => 'required|integer|min:1|max:100',
            'amount' => 'required|numeric|min:1',
        ]);

        $count = $request->count;
        $amount = $request->amount;
        $generated = 0;

        while ($generated < $count) {
            $code = strtoupper(Str::random(12));
            
            // Ensure uniqueness
            if (!Card::where('code', $code)->exists()) {
                Card::create([
                    'code' => $code,
                    'amount' => $amount,
                    'is_used' => false,
                ]);
                $generated++;
            }
        }

        return redirect()->route('admin.cards.index')->with('success', "تم توليد {$count} كرت بنجاح بقيمة {$amount} ريال.");
    }

    public function destroy(Card $card)
    {
        if ($card->is_used) {
            return back()->with('error', 'لا يمكن حذف كرت تم استخدامه بالفعل.');
        }

        $card->delete();
        return back()->with('success', 'تم حذف الكرت بنجاح.');
    }
}
