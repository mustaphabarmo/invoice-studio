<?php

namespace App\Http\Controllers\API\V1\Member;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;

class AnnouncementController extends Controller
{
    /**
     * List published member announcements.
     *
     * Returns institutional notices and updates published for members.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Announcement::where('is_published', true)->latest('published_at')->paginate(20),
        ]);
    }
}
