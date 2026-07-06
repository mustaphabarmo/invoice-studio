<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\TrainingEvent;
use Illuminate\Http\JsonResponse;

class TrainingEventController extends Controller
{
    /**
     * List published training and events for the website.
     */
    public function index(): JsonResponse
    {
        $events = TrainingEvent::where('status', 'published')
            ->orderByRaw("event_type = 'upcoming' desc")
            ->orderBy('starts_at')
            ->latest()
            ->get();

        return response()->json(['success' => true, 'data' => $events]);
    }

    /**
     * Get published training/event detail for the website.
     */
    public function show(string $slug): JsonResponse
    {
        $event = TrainingEvent::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        return response()->json(['success' => true, 'data' => $event]);
    }
}
