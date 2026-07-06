<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResourceDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    /**
     * List resource documents.
     *
     * Returns uploaded institutional resources for administrative management.
     */
    public function index(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => ResourceDocument::latest()->paginate(25)]);
    }

    /**
     * Upload resource document.
     *
     * Uploads a document or resource file and controls member/public visibility.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'visibility' => ['nullable', 'in:public,members,admins'],
            'is_published' => ['boolean'],
            'file' => ['required', 'file', 'max:51200'],
        ]);

        $file = $request->file('file');
        $path = $file->store('resources');

        $resource = ResourceDocument::create([
            ...$data,
            'uploaded_by_admin_id' => $request->user()->id,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'published_at' => ($data['is_published'] ?? false) ? now() : null,
        ]);

        return response()->json(['success' => true, 'data' => $resource], 201);
    }

    /**
     * Update resource document.
     *
     * Updates resource metadata, visibility, and publication status.
     */
    public function update(Request $request, ResourceDocument $resource): JsonResponse
    {
        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'visibility' => ['nullable', 'in:public,members,admins'],
            'is_published' => ['boolean'],
        ]);

        if (($data['is_published'] ?? false) && ! $resource->published_at) {
            $data['published_at'] = now();
        }

        $resource->update($data);

        return response()->json(['success' => true, 'data' => $resource->fresh()]);
    }
}
