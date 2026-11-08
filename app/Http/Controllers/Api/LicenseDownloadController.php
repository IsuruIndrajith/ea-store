<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\License;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LicenseDownloadController extends Controller
{
    public function __invoke(Request $request, License $license): StreamedResponse
    {
        $license = $request->user()
            ->licenses()
            ->whereKey($license->id)
            ->with('orderItem.order')
            ->firstOrFail();
        abort_unless($license->status === 'active' && $license->orderItem?->order?->status === 'paid', 404);

        $path = $license->orderItem->product_file_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, basename($path));
    }
}
