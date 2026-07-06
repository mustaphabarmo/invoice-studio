<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Publication;
use App\Models\PublicationDownload;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    /**
     * Get revenue report.
     *
     * Returns successful payment totals grouped by payment purpose with recent revenue records.
     */
    public function revenue(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'total' => Payment::where('status', 'successful')->sum('amount'),
                'by_purpose' => Payment::where('status', 'successful')
                    ->selectRaw('purpose, count(*) as count, sum(amount) as total')
                    ->groupBy('purpose')
                    ->get(),
                'recent' => Payment::with('member')->where('status', 'successful')->latest('paid_at')->limit(20)->get(),
            ],
        ]);
    }

    /**
     * Get publication sales report.
     *
     * Returns publication records with successful purchase counts.
     */
    public function publications(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Publication::withCount([
                'purchases as successful_purchases_count' => fn ($q) => $q->where('status', 'successful'),
            ])->latest()->paginate(25),
        ]);
    }

    /**
     * Get publication download report.
     *
     * Returns publication download activity with member and publication details.
     */
    public function downloads(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => PublicationDownload::with(['member', 'publication'])->latest()->paginate(25),
        ]);
    }
}
