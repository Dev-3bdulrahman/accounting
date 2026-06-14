<?php

namespace Dev3bdulrahman\Accounting\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dev3bdulrahman\Accounting\Models\Account;
use Dev3bdulrahman\Accounting\Http\Resources\AccountResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AccountApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $companyId = session('active_company_id') ?: auth()->user()->company_id;
        $perPage = (int)$request->get('per_page', 15);

        $accounts = Account::where('company_id', $companyId)
            ->when($request->get('search'), function($q) use ($request) {
                $q->where(function($sub) use ($request) {
                    $sub->where('name', 'like', '%' . $request->get('search') . '%')
                       ->orWhere('code', 'like', '%' . $request->get('search') . '%');
                });
            })
            ->when($request->get('type'), function($q) use ($request) {
                $q->where('type', $request->get('type'));
            })
            ->orderBy('code', 'asc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => __('Accounts retrieved successfully'),
            'data' => AccountResource::collection($accounts->items()),
            'meta' => [
                'current_page' => $accounts->currentPage(),
                'last_page' => $accounts->lastPage(),
                'per_page' => $accounts->perPage(),
                'total' => $accounts->total(),
            ],
            'errors' => []
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:asset,liability,equity,revenue,expense',
            'is_active' => 'nullable|boolean',
        ]);

        $companyId = session('active_company_id') ?: auth()->user()->company_id;

        // Check uniqueness of code per company
        $exists = Account::where('company_id', $companyId)->where('code', $validated['code'])->exists();
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => __('Account code already exists'),
                'errors' => ['code' => [__('Account code must be unique')]]
            ], 422);
        }

        $validated['company_id'] = $companyId;
        $account = Account::create($validated);

        return response()->json([
            'success' => true,
            'message' => __('Account created successfully'),
            'data' => new AccountResource($account),
            'errors' => []
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $companyId = session('active_company_id') ?: auth()->user()->company_id;
        $account = Account::where('company_id', $companyId)->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => __('Account retrieved successfully'),
            'data' => new AccountResource($account),
            'errors' => []
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $companyId = session('active_company_id') ?: auth()->user()->company_id;
        $account = Account::where('company_id', $companyId)->findOrFail($id);

        $validated = $request->validate([
            'code' => 'sometimes|required|string|max:50',
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string|in:asset,liability,equity,revenue,expense',
            'is_active' => 'nullable|boolean',
        ]);

        if (isset($validated['code']) && $validated['code'] !== $account->code) {
            $exists = Account::where('company_id', $companyId)->where('code', $validated['code'])->exists();
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => __('Account code already exists'),
                    'errors' => ['code' => [__('Account code must be unique')]]
                ], 422);
            }
        }

        $account->update($validated);

        return response()->json([
            'success' => true,
            'message' => __('Account updated successfully'),
            'data' => new AccountResource($account),
            'errors' => []
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $companyId = session('active_company_id') ?: auth()->user()->company_id;
        $account = Account::where('company_id', $companyId)->findOrFail($id);
        
        $account->delete();

        return response()->json([
            'success' => true,
            'message' => __('Account deleted successfully'),
            'data' => null,
            'errors' => []
        ]);
    }
}
