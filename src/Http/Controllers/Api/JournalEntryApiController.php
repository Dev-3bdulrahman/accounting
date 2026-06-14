<?php

namespace Dev3bdulrahman\Accounting\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dev3bdulrahman\Accounting\Models\JournalEntry;
use Dev3bdulrahman\Accounting\Models\JournalEntryLine;
use Dev3bdulrahman\Accounting\Http\Resources\JournalEntryResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class JournalEntryApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $companyId = session('active_company_id') ?: auth()->user()->company_id;
        $perPage = (int)$request->get('per_page', 15);

        $entries = JournalEntry::where('company_id', $companyId)
            ->with('lines.account')
            ->when($request->get('status'), function($q) use ($request) {
                $q->where('status', $request->get('status'));
            })
            ->when($request->get('entry_date'), function($q) use ($request) {
                $q->whereDate('entry_date', $request->get('entry_date'));
            })
            ->orderBy('entry_date', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => __('Journal entries retrieved successfully'),
            'data' => JournalEntryResource::collection($entries->items()),
            'meta' => [
                'current_page' => $entries->currentPage(),
                'last_page' => $entries->lastPage(),
                'per_page' => $entries->perPage(),
                'total' => $entries->total(),
            ],
            'errors' => []
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'entry_number' => 'required|string|max:50',
            'entry_date' => 'required|date',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:draft,posted',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|integer|exists:accounting_accounts,id',
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
            'lines.*.description' => 'nullable|string',
        ]);

        $companyId = session('active_company_id') ?: auth()->user()->company_id;

        // Verify total debits match total credits
        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($validated['lines'] as $line) {
            $totalDebit += (float)($line['debit'] ?? 0);
            $totalCredit += (float)($line['credit'] ?? 0);
        }

        if (abs($totalDebit - $totalCredit) > 0.0001) {
            return response()->json([
                'success' => false,
                'message' => __('Journal entry is unbalanced'),
                'errors' => ['lines' => [__('Total Debit (:debit) must equal Total Credit (:credit)', ['debit' => $totalDebit, 'credit' => $totalCredit])]]
            ], 422);
        }

        $entry = DB::transaction(function() use ($validated, $companyId) {
            $journalEntry = JournalEntry::create([
                'company_id' => $companyId,
                'branch_id' => auth()->user()->branch_id,
                'entry_number' => $validated['entry_number'],
                'entry_date' => $validated['entry_date'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'] ?? 'draft',
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['lines'] as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $line['account_id'],
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                    'description' => $line['description'] ?? null,
                ]);
            }

            return $journalEntry;
        });

        $entry->load('lines.account');

        return response()->json([
            'success' => true,
            'message' => __('Journal entry created successfully'),
            'data' => new JournalEntryResource($entry),
            'errors' => []
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $companyId = session('active_company_id') ?: auth()->user()->company_id;
        $entry = JournalEntry::where('company_id', $companyId)
            ->with('lines.account')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => __('Journal entry retrieved successfully'),
            'data' => new JournalEntryResource($entry),
            'errors' => []
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $companyId = session('active_company_id') ?: auth()->user()->company_id;
        $entry = JournalEntry::where('company_id', $companyId)->findOrFail($id);
        
        $entry->delete();

        return response()->json([
            'success' => true,
            'message' => __('Journal entry deleted successfully'),
            'data' => null,
            'errors' => []
        ]);
    }
}
