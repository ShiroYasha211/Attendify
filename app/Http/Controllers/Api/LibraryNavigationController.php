<?php

namespace App\Http\Controllers\Api;

use App\Services\Library\LibraryNavigationService;
use App\Support\Library\LibraryContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LibraryNavigationController extends BaseController
{
    public function __construct(private readonly LibraryNavigationService $navigation) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['nullable', Rule::in(LibraryContract::TYPES)],
            'node' => ['nullable', 'string', 'max:255'],
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $data = $this->navigation->navigate(
            $request->user(),
            $validated['type'] ?? LibraryContract::TYPE_SHARED,
            $validated['node'] ?? null,
            $validated['search'] ?? null,
            (int) ($validated['per_page'] ?? 20),
        );

        return $this->success($data, 'تم جلب محتوى المكتبة بنجاح.');
    }
}
