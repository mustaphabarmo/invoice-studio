<?php

namespace App\Http\Controllers\API\V1\Member;

use App\Http\Controllers\Controller;
use App\Models\Publication;
use App\Models\PublicationDownload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LibraryController extends Controller
{
    /**
     * List member digital library.
     *
     * Returns all publications the authenticated member has successfully purchased or accessed.
     */
    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()
                ->publicationPurchases()
                ->where('status', 'successful')
                ->with('publication.category')
                ->latest()
                ->paginate(20),
        ]);
    }

    /**
     * Download purchased publication.
     *
     * Allows a member to download a publication only after successful purchase or free access.
     */
    public function download(Request $request, Publication $publication)
    {
        $hasAccess = $request->user()->publicationPurchases()
            ->where('publication_id', $publication->id)
            ->where('status', 'successful')
            ->exists();

        abort_unless($hasAccess, 403);

        PublicationDownload::create([
            'member_id' => $request->user()->id,
            'publication_id' => $publication->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return Storage::download($publication->file_path, $publication->file_name);
    }

    /**
     * Read purchased publication inline.
     *
     * Streams the PDF for authenticated members with successful publication access.
     */
    public function read(Request $request, Publication $publication)
    {
        $hasAccess = $request->user()->publicationPurchases()
            ->where('publication_id', $publication->id)
            ->where('status', 'successful')
            ->exists();

        abort_unless($hasAccess, 403);

        PublicationDownload::create([
            'member_id' => $request->user()->id,
            'publication_id' => $publication->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return Storage::response(
            $publication->file_path,
            $publication->file_name,
            ['Content-Type' => $publication->mime_type ?? 'application/pdf'],
            'inline',
        );
    }
}
