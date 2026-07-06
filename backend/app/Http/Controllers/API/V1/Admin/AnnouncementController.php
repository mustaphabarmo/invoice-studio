<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * List announcements.
     *
     * Returns institutional announcements for administrative management.
     */
    public function index(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => Announcement::latest()->paginate(25)]);
    }

    /**
     * Create announcement.
     *
     * Creates an institutional notice or update for the selected audience.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'body' => ['required', 'string'],
            'audience' => ['nullable', 'in:members,public,admins'],
            'is_published' => ['boolean'],
        ]);

        $announcement = Announcement::create([
            ...$data,
            'created_by_admin_id' => $request->user()->id,
            'published_at' => ($data['is_published'] ?? false) ? now() : null,
        ]);

        return response()->json(['success' => true, 'data' => $announcement], 201);
    }

    /**
     * Update announcement.
     *
     * Updates announcement content, audience, and publication status.
     */
    public function update(Request $request, Announcement $announcement): JsonResponse
    {
        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:180'],
            'body' => ['sometimes', 'string'],
            'audience' => ['nullable', 'in:members,public,admins'],
            'is_published' => ['boolean'],
        ]);

        if (($data['is_published'] ?? false) && ! $announcement->published_at) {
            $data['published_at'] = now();
        }

        $announcement->update($data);

        return response()->json(['success' => true, 'data' => $announcement->fresh()]);
    }
}
