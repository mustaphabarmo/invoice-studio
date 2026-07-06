<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Publication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PublicationController extends Controller
{
    /**
     * List publications.
     *
     * Returns all publications for administrative management, including draft, published, and archived records.
     */
    public function index(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => Publication::with('category')->latest()->paginate(25)]);
    }

    /**
     * Upload publication.
     *
     * Creates a publication record with document file, metadata, pricing, and optional cover image.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'publication_category_id' => ['nullable', 'integer', 'exists:publication_categories,id'],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'subject' => ['nullable', 'string', 'max:120'],
            'edition' => ['nullable', 'string', 'max:80'],
            'publication_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'department' => ['nullable', 'string', 'max:120'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'status' => ['nullable', 'in:draft,published,archived'],
            'is_featured' => ['boolean'],
            'file' => ['required', 'file', 'max:102400'],
            'cover_image' => ['nullable', 'image', 'max:10240'],
        ]);

        $file = $request->file('file');
        $cover = $request->file('cover_image');
        $status = $data['status'] ?? 'draft';

        $publication = Publication::create([
            ...$data,
            'uploaded_by_admin_id' => $request->user()->id,
            'slug' => $this->uniqueSlug($data['title']),
            'price' => $data['price'] ?? 0,
            'currency' => $data['currency'] ?? 'NGN',
            'status' => $status,
            'file_path' => $file->store('publications'),
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'cover_image_path' => $cover?->store('publication-covers'),
            'published_at' => $status === 'published' ? now() : null,
        ]);

        return response()->json(['success' => true, 'data' => $publication], 201);
    }

    /**
     * Get publication record.
     *
     * Returns full publication metadata for administrative review.
     */
    public function show(Publication $publication): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $publication->load('category')]);
    }

    /**
     * Read publication file inline.
     *
     * Streams the PDF for authenticated administrators without forcing a download.
     */
    public function read(Publication $publication)
    {
        return Storage::response(
            $publication->file_path,
            $publication->file_name,
            ['Content-Type' => $publication->mime_type ?? 'application/pdf'],
            'inline',
        );
    }

    /**
     * Update publication.
     *
     * Updates publication metadata, pricing, featured status, and publish/archive status.
     */
    public function update(Request $request, Publication $publication): JsonResponse
    {
        $data = $request->validate([
            'publication_category_id' => ['nullable', 'integer', 'exists:publication_categories,id'],
            'title' => ['sometimes', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'subject' => ['nullable', 'string', 'max:120'],
            'edition' => ['nullable', 'string', 'max:80'],
            'publication_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'department' => ['nullable', 'string', 'max:120'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'status' => ['nullable', 'in:draft,published,archived'],
            'is_featured' => ['boolean'],
        ]);

        if (isset($data['title'])) {
            $data['slug'] = $this->uniqueSlug($data['title'], $publication->id);
        }

        if (($data['status'] ?? null) === 'published' && ! $publication->published_at) {
            $data['published_at'] = now();
        }

        $publication->update($data);

        return response()->json(['success' => true, 'data' => $publication->fresh('category')]);
    }

    /**
     * Delete publication.
     *
     * Removes the publication record and its stored PDF/cover files when present.
     */
    public function destroy(Publication $publication): JsonResponse
    {
        Storage::delete(array_filter([
            $publication->file_path,
            $publication->cover_image_path,
        ]));

        $publication->delete();

        return response()->json(['success' => true, 'message' => 'Publication deleted successfully']);
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $count = 2;

        while (Publication::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$base}-{$count}";
            $count++;
        }

        return $slug;
    }
}
