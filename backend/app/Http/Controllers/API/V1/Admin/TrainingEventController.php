<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrainingEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TrainingEventController extends Controller
{
    /**
     * List training and event posts.
     *
     * Returns upcoming and past training/event records for website management.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => TrainingEvent::latest('starts_at')->latest()->paginate(50),
        ]);
    }

    /**
     * Create training or event post.
     *
     * Creates a website training/event entry with optional uploaded event images.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        unset($data['images']);
        $status = $data['status'] ?? 'draft';

        $event = TrainingEvent::create([
            ...$data,
            'created_by_admin_id' => $request->user()->id,
            'slug' => $this->uniqueSlug($data['title']),
            'image_paths' => $this->storeImages($request),
            'status' => $status,
            'published_at' => $status === 'published' ? now() : null,
        ]);

        return response()->json(['success' => true, 'data' => $event], 201);
    }

    /**
     * Update training or event post.
     *
     * Updates website event metadata, publication state, and optionally replaces uploaded images.
     */
    public function update(Request $request, TrainingEvent $trainingEvent): JsonResponse
    {
        $data = $this->validated($request, true);
        unset($data['images']);

        if (isset($data['title'])) {
            $data['slug'] = $this->uniqueSlug($data['title'], $trainingEvent->id);
        }

        if ($request->hasFile('images')) {
            $data['image_paths'] = [
                ...($trainingEvent->image_paths ?? []),
                ...$this->storeImages($request),
            ];
        }

        if (($data['status'] ?? null) === 'published' && ! $trainingEvent->published_at) {
            $data['published_at'] = now();
        }

        $trainingEvent->update($data);

        return response()->json(['success' => true, 'data' => $trainingEvent->fresh()]);
    }

    /**
     * Delete training or event post.
     */
    public function destroy(TrainingEvent $trainingEvent): JsonResponse
    {
        $trainingEvent->delete();

        return response()->json(['success' => true, 'message' => 'Training event deleted.']);
    }

    private function validated(Request $request, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'title' => [$required, 'string', 'max:180'],
            'tag' => ['nullable', 'string', 'max:80'],
            'event_type' => ['nullable', 'in:upcoming,past'],
            'date_label' => ['nullable', 'string', 'max:120'],
            'starts_at' => ['nullable', 'date'],
            'time_label' => ['nullable', 'string', 'max:120'],
            'location' => ['nullable', 'string', 'max:180'],
            'venue' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'register_url' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:draft,published,archived'],
            'is_featured' => ['boolean'],
            'images' => ['nullable', 'array'],
            'images.*' => ['file', 'image', 'max:10240'],
        ]);
    }

    private function storeImages(Request $request): array
    {
        return collect($request->file('images', []))
            ->map(fn ($image) => $image->store('training-events', 'public'))
            ->values()
            ->all();
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $count = 2;

        while (TrainingEvent::where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = "{$base}-{$count}";
            $count++;
        }

        return $slug;
    }
}
