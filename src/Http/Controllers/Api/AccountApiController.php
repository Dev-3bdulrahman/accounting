<?php

namespace Dev3bdulrahman\Accounting\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\HasApiResponse;
use Dev3bdulrahman\Accounting\Models\Account;
use Dev3bdulrahman\Accounting\Http\Resources\AccountResource;
use Dev3bdulrahman\Accounting\Http\Requests\Api\StoreAccountApiRequest;
use Dev3bdulrahman\Accounting\Http\Requests\Api\UpdateAccountApiRequest;
use Dev3bdulrahman\Accounting\Services\AccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountApiController extends Controller
{
    use HasApiResponse;

    public function __construct(
        private AccountService $accountService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Account::class);

        $companyId = session('active_company_id') ?: auth()->user()->company_id;

        $accounts = $this->accountService->listAccounts([
            'search' => $request->get('search'),
            'type' => $request->get('type'),
            'is_active' => $request->get('is_active'),
            'company_id' => $companyId,
        ], (int) $request->get('per_page', 15));

        return $this->success(
            data: AccountResource::collection($accounts->items()),
            message: 'Accounts retrieved successfully',
            meta: [
                'current_page' => $accounts->currentPage(),
                'last_page' => $accounts->lastPage(),
                'per_page' => $accounts->perPage(),
                'total' => $accounts->total(),
            ]
        );
    }

    public function store(StoreAccountApiRequest $request): JsonResponse
    {
        $this->authorize('create', Account::class);

        try {
            $account = $this->accountService->create($request->validated());

            return $this->success(
                data: new AccountResource($account),
                message: 'Account created successfully',
                code: 201
            );
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function show(Account $account): JsonResponse
    {
        $this->authorize('view', $account);

        return $this->success(
            data: new AccountResource($account),
            message: 'Account retrieved successfully'
        );
    }

    public function update(UpdateAccountApiRequest $request, Account $account): JsonResponse
    {
        $this->authorize('update', $account);

        try {
            $account = $this->accountService->update($account, $request->validated());

            return $this->success(
                data: new AccountResource($account),
                message: 'Account updated successfully'
            );
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function destroy(Account $account): JsonResponse
    {
        $this->authorize('delete', $account);

        try {
            $this->accountService->delete($account);

            return $this->success(
                data: null,
                message: 'Account deleted successfully'
            );
        } catch (\LogicException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }
}
