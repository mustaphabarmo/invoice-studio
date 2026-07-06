<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\MembershipRenewal;
use App\Models\Payment;
use App\Models\Publication;
use App\Models\PublicationPurchase;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * Get admin dashboard metrics.
     *
     * Returns summary counts for members, payments, publications, and publication purchases.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'members' => [
                    'total' => User::where('role', 'member')->count(),
                    'active' => User::where('role', 'member')->where('status', 'active')->count(),
                    'pending' => User::where('role', 'member')->where('status', 'pending')->count(),
                    'suspended' => User::where('role', 'member')->where('status', 'suspended')->count(),
                ],
                'payments' => [
                    'successful_count' => Payment::where('status', 'successful')->count(),
                    'successful_amount' => Payment::where('status', 'successful')->sum('amount'),
                    'pending_count' => Payment::whereIn('status', ['pending', 'processing'])->count(),
                ],
                'renewals' => [
                    'total' => MembershipRenewal::count(),
                    'active' => MembershipRenewal::where('status', 'active')->count(),
                    'pending' => MembershipRenewal::where('status', 'pending')->count(),
                    'expired' => MembershipRenewal::where('status', 'expired')->count(),
                ],
                'publications' => [
                    'total' => Publication::count(),
                    'published' => Publication::where('status', 'published')->count(),
                    'purchases' => PublicationPurchase::where('status', 'successful')->count(),
                ],
            ],
        ]);
    }
}
