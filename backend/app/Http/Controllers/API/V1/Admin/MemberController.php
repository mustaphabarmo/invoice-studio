<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    /**
     * List member records.
     *
     * Returns searchable and filterable member records for administrative review.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query()->where('role', 'member');

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(fn ($q) => $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('membership_number', 'like', "%{$search}%"));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return response()->json(['success' => true, 'data' => $query->latest()->paginate(25)]);
    }

    /**
     * Create member record.
     *
     * Allows an administrator to create a member account manually.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8'],
            'membership_number' => ['nullable', 'string', 'max:80', 'unique:users,membership_number'],
            'membership_grade' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', 'in:pending,active,suspended,inactive'],
            'organization' => ['nullable', 'string', 'max:150'],
            'job_title' => ['nullable', 'string', 'max:150'],
            'address' => ['nullable', 'string'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Member created successfully',
            'data' => User::create([
                ...$data,
                'role' => 'member',
            ]),
        ], 201);
    }

    /**
     * Get member record.
     *
     * Returns a member profile with renewals, payments, receipts, and publication purchases.
     */
    public function show(User $member): JsonResponse
    {
        abort_unless($member->isMember(), 404);

        return response()->json([
            'success' => true,
            'data' => $member->load(['renewals.plan', 'payments.receipt', 'receipts', 'publicationPurchases.publication']),
        ]);
    }

    /**
     * Update member record.
     *
     * Allows an administrator to update membership number, profile details, grade, and account status.
     */
    public function update(Request $request, User $member): JsonResponse
    {
        abort_unless($member->isMember(), 404);

        $data = $request->validate([
            'first_name' => ['sometimes', 'string', 'max:100'],
            'last_name' => ['sometimes', 'string', 'max:100'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'membership_number' => ['nullable', 'string', 'max:80', 'unique:users,membership_number,' . $member->id],
            'membership_grade' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', 'in:pending,active,suspended,inactive'],
            'organization' => ['nullable', 'string', 'max:150'],
            'job_title' => ['nullable', 'string', 'max:150'],
            'address' => ['nullable', 'string'],
        ]);

        $member->update($data);

        return response()->json(['success' => true, 'message' => 'Member updated successfully', 'data' => $member->fresh()]);
    }

    /**
     * Delete member record.
     *
     * Removes a member account and related cascade-enabled member records.
     */
    public function destroy(User $member): JsonResponse
    {
        abort_unless($member->isMember(), 404);

        $member->delete();

        return response()->json(['success' => true, 'message' => 'Member deleted successfully']);
    }

    /**
     * Activate member account.
     *
     * Marks a member account as active.
     */
    public function activate(User $member): JsonResponse
    {
        abort_unless($member->isMember(), 404);

        $member->update(['status' => 'active']);

        return response()->json(['success' => true, 'message' => 'Member activated successfully']);
    }

    /**
     * Suspend member account.
     *
     * Marks a member account as suspended and prevents normal member access.
     */
    public function suspend(User $member): JsonResponse
    {
        abort_unless($member->isMember(), 404);

        $member->update(['status' => 'suspended']);

        return response()->json(['success' => true, 'message' => 'Member suspended successfully']);
    }
}
