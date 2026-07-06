<?php

namespace App\Http\Controllers\API\V1\Member;

use App\Http\Controllers\Controller;
use App\Models\MembershipPlan;
use App\Services\Membership\MembershipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class MembershipController extends Controller
{
    /**
     * Get member membership summary.
     *
     * Returns active membership plans, the member's renewal history, and the current active membership if available.
     */
    public function index(Request $request): JsonResponse
    {
        $member = $request->user();
        $grade = $member->membership_grade;
        $current = $member->renewals()
            ->with(['plan', 'payment.receipt'])
            ->where('status', 'active')
            ->latest('expires_at')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'plans' => MembershipPlan::where('is_active', true)
                    ->when($grade, fn ($query) => $query->where('grade', $grade))
                    ->orderBy('amount')
                    ->get(),
                'renewals' => $member->renewals()->with(['plan', 'payment.receipt'])->latest()->get(),
                'current' => $current,
            ],
        ]);
    }

    /**
     * Initiate membership renewal payment.
     *
     * Creates a pending membership renewal and starts payment through xPouch.
     */
    public function renew(Request $request, MembershipService $memberships): JsonResponse
    {
        $data = $request->validate([
            'membership_plan_id' => ['required', 'integer', 'exists:membership_plans,id'],
        ]);

        $plan = MembershipPlan::where('is_active', true)->findOrFail($data['membership_plan_id']);
        abort_if(
            $request->user()->membership_grade && $plan->grade !== $request->user()->membership_grade,
            403,
            'This renewal plan does not match your membership category.',
        );

        try {
            return response()->json([
                'success' => true,
                'message' => 'Membership renewed from wallet',
                'data' => $memberships->initiateRenewal($request->user(), $plan),
            ], 201);
        } catch (RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }
}
