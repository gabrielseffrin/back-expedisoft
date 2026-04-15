<?php

namespace App\Http\Controllers;

use App\Models\ChecklistEntry;
use App\Models\LoadingOrder;
use App\Services\CheckListEntryService;
use Illuminate\Http\Request;

class CheckListEntryController extends Controller
{
    public function __construct(private readonly CheckListEntryService $checklistEntry)
    {
    }

    public function store(Request $request, $orderId): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        try {
            $checklistEntry = $this->checklistEntry->store(
                auth()->user(),
                $orderId,
                $request->input('qr_code')
            );

            return response()->json([
                'success' => true,
                'message' => 'Pacote conferido com sucesso',
                'data' => $checklistEntry,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
