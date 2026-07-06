<?php

namespace App\Http\Controllers\API\V1\Member;

use App\Http\Controllers\Controller;
use App\Models\Publication;
use App\Services\Publications\PublicationPurchaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class PublicationController extends Controller
{
    /**
     * List published publications.
     *
     * Returns searchable and filterable publications available in the NIMN digital library.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Publication::with('category')->where('status', 'published');

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('subject', 'like', "%{$search}%"));
        }

        if ($request->filled('category_id')) {
            $query->where('publication_category_id', $request->integer('category_id'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->latest('published_at')->paginate(20),
        ]);
    }

    /**
     * Get publication details.
     *
     * Returns metadata, pricing, category, and access information for a published publication.
     */
    public function show(Publication $publication): JsonResponse
    {
        abort_unless($publication->status === 'published', 404);

        return response()->json([
            'success' => true,
            'data' => $publication->load('category'),
        ]);
    }

    /**
     * Initiate publication purchase payment.
     *
     * Creates a purchase record and starts xPouch payment for a paid publication, or grants access to a free publication.
     */
    public function purchase(Request $request, Publication $publication, PublicationPurchaseService $purchases): JsonResponse
    {
        abort_unless($publication->status === 'published', 404);

        try {
            return response()->json([
                'success' => true,
                'message' => 'Publication purchased from wallet',
                'data' => $purchases->purchase($request->user(), $publication),
            ], 201);
        } catch (RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }
}
