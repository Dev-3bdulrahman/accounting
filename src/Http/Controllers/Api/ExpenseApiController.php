<?php

namespace Dev3bdulrahman\Accounting\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\HasApiResponse;
use Dev3bdulrahman\Accounting\Models\Expense;
use Dev3bdulrahman\Accounting\Http\Requests\Api\StoreExpenseApiRequest;
use Dev3bdulrahman\Accounting\Http\Requests\Api\UpdateExpenseApiRequest;
use Dev3bdulrahman\Accounting\Services\ExpenseService;
use Dev3bdulrahman\Accounting\Events\ExpenseApproved;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseApiController extends Controller
{
    use HasApiResponse;

    public function __construct(
        private ExpenseService $expenseService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Expense::class);

        $companyId = session('active_company_id') ?: auth()->user()->company_id;

        $expenses = $this->expenseService->listExpenses([
            'search' => $request->get('search'),
            'category_id' => $request->get('category_id'),
            'status' => $request->get('status'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'company_id' => $companyId,
        ], (int) $request->get('per_page', 15));

        return $this->success(
            data: $expenses->items(),
            message: 'Expenses retrieved successfully',
            meta: [
                'current_page' => $expenses->currentPage(),
                'last_page' => $expenses->lastPage(),
                'per_page' => $expenses->perPage(),
                'total' => $expenses->total(),
            ]
        );
    }

    public function store(StoreExpenseApiRequest $request): JsonResponse
    {
        $this->authorize('create', Expense::class);

        $expense = $this->expenseService->create($request->validated());

        return $this->success(
            data: $expense,
            message: 'Expense created successfully',
            code: 201
        );
    }

    public function show(Expense $expense): JsonResponse
    {
        $this->authorize('view', $expense);

        $expense->load(['category', 'creator']);

        return $this->success(
            data: $expense,
            message: 'Expense retrieved successfully'
        );
    }

    public function update(UpdateExpenseApiRequest $request, Expense $expense): JsonResponse
    {
        $this->authorize('update', $expense);

        $expense = $this->expenseService->update($expense, $request->validated());

        return $this->success(
            data: $expense,
            message: 'Expense updated successfully'
        );
    }

    public function destroy(Expense $expense): JsonResponse
    {
        $this->authorize('delete', $expense);

        $this->expenseService->delete($expense);

        return $this->success(
            data: null,
            message: 'Expense deleted successfully'
        );
    }

    public function approve(Expense $expense): JsonResponse
    {
        $this->authorize('approve', $expense);

        try {
            $expense = $this->expenseService->approve($expense);

            ExpenseApproved::dispatch($expense, auth()->id(), $expense->company_id);

            return $this->success(
                data: $expense,
                message: 'Expense approved successfully'
            );
        } catch (\LogicException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }
}
