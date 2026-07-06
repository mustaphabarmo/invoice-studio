<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\MembershipPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MembershipPlanController extends Controller
{
    /**
     * List membership plans.
     *
     * Returns all configured membership renewal plans and dues amounts.
     */
    public function index(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => MembershipPlan::latest()->paginate(25)]);
    }

    /**
     * Create membership plan.
     *
     * Creates a renewal plan with grade, amount, currency, and duration.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'grade' => ['nullable', 'string', 'max:80'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'duration_months' => ['required', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ]);

        return response()->json(['success' => true, 'data' => MembershipPlan::create($data)], 201);
    }

    /**
     * Update membership plan.
     *
     * Updates pricing, duration, grade, and active status for a membership plan.
     */
    public function update(Request $request, MembershipPlan $membershipPlan): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:150'],
            'grade' => ['nullable', 'string', 'max:80'],
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'duration_months' => ['sometimes', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ]);

        $membershipPlan->update($data);

        return response()->json(['success' => true, 'data' => $membershipPlan->fresh()]);
    }

    /**
     * Delete membership plan.
     *
     * Removes a membership plan when no member renewal records are attached.
     */
    public function destroy(MembershipPlan $membershipPlan): JsonResponse
    {
        if ($membershipPlan->renewals()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This membership plan is attached to renewal records and cannot be deleted.',
            ], 422);
        }

        $membershipPlan->delete();

        return response()->json(['success' => true, 'message' => 'Membership plan deleted successfully.']);
    }
}
