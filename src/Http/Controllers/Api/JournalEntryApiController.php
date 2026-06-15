<?php

namespace Dev3bdulrahman\Accounting\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\HasApiResponse;
use Dev3bdulrahman\Accounting\Models\JournalEntry;
use Dev3bdulrahman\Accounting\Http\Resources\JournalEntryResource;
use Dev3bdulrahman\Accounting\Http\Requests\Api\StoreJournalEntryApiRequest;
use Dev3bdulrahman\Accounting\Services\JournalEntryService;
use Dev3bdulrahman\Accounting\Events\JournalEntryPosted;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JournalEntryApiController extends Controller
{
    use HasApiResponse;

    public function __construct(
        private JournalEntryService $journalEntryService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', JournalEntry::class);

        $companyId = session('active_company_id') ?: auth()->user()->company_id;

        $entries = $this->journalEntryService->listEntries([
            'search' => $request->get('search'),
            'status' => $request->get('status'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'company_id' => $companyId,
        ], (int) $request->get('per_page', 15));

        return $this->success(
            data: JournalEntryResource::collection($entries->items()),
            message: 'Journal entries retrieved successfully',
            meta: [
                'current_page' => $entries->currentPage(),
                'last_page' => $entries->lastPage(),
                'per_page' => $entries->perPage(),
                'total' => $entries->total(),
            ]
        );
    }

    public function store(StoreJournalEntryApiRequest $request): JsonResponse
    {
        $this->authorize('create', JournalEntry::class);

        try {
            $entry = $this->journalEntryService->create($request->validated());

            return $this->success(
                data: new JournalEntryResource($entry),
                message: 'Journal entry created successfully',
                code: 201
            );
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function show(JournalEntry $journalEntry): JsonResponse
    {
        $this->authorize('view', $journalEntry);

        $journalEntry->load('lines.account');

        return $this->success(
            data: new JournalEntryResource($journalEntry),
            message: 'Journal entry retrieved successfully'
        );
    }

    public function post(JournalEntry $journalEntry): JsonResponse
    {
        $this->authorize('post', $journalEntry);

        try {
            $entry = $this->journalEntryService->post($journalEntry);

            JournalEntryPosted::dispatch($entry, auth()->id(), $entry->company_id);

            return $this->success(
                data: new JournalEntryResource($entry),
                message: 'Journal entry posted successfully'
            );
        } catch (\InvalidArgumentException|\LogicException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function destroy(JournalEntry $journalEntry): JsonResponse
    {
        $this->authorize('delete', $journalEntry);

        try {
            $this->journalEntryService->delete($journalEntry);

            return $this->success(
                data: null,
                message: 'Journal entry deleted successfully'
            );
        } catch (\LogicException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }
}
