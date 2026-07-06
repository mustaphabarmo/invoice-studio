<?php

namespace App\Http\Controllers\API\V1\Member;

use App\Http\Controllers\Controller;
use App\Models\ResourceDocument;
use Illuminate\Http\JsonResponse;

class ResourceController extends Controller
{
    /**
     * List published member resources.
     *
     * Returns institutional documents and resources available to members.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => ResourceDocument::where('is_published', true)->latest('published_at')->paginate(20),
        ]);
    }
}
