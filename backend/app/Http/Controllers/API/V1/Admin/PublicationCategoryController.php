<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\PublicationCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicationCategoryController extends Controller
{
    /**
     * List publication categories.
     *
     * Returns publication categories used for filtering and organizing digital library items.
     */
    public function index(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => PublicationCategory::latest()->paginate(25)]);
    }

    /**
     * Create publication category.
     *
     * Creates a category for grouping NIMN publications.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
        ]);

        $category = PublicationCategory::create([
            ...$data,
            'slug' => Str::slug($data['name']),
        ]);

        return response()->json(['success' => true, 'data' => $category], 201);
    }

    /**
     * Update publication category.
     *
     * Updates a publication category name and description.
     */
    public function update(Request $request, PublicationCategory $publicationCategory): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
        ]);

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $publicationCategory->update($data);

        return response()->json(['success' => true, 'data' => $publicationCategory->fresh()]);
    }

    /**
     * Delete publication category.
     *
     * Removes an unused publication category from the material library.
     */
    public function destroy(PublicationCategory $publicationCategory): JsonResponse
    {
        if ($publicationCategory->publications()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This category is attached to existing publications and cannot be deleted.',
            ], 422);
        }

        $publicationCategory->delete();

        return response()->json(['success' => true, 'message' => 'Category deleted successfully.']);
    }
}
