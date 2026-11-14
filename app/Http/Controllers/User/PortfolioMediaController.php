<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PortfolioMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PortfolioMediaController extends Controller
{
    public function index(Request $request)
    {
        $media = PortfolioMedia::where('user_id', $request->user()->id)
            ->latest()
            ->limit(100)
            ->get();

        return response()->json(['media' => $media]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => ['required', 'image', 'max:5120'],
            'site_id' => ['nullable', 'integer'],
            'alt_text' => ['nullable', 'string', 'max:200'],
        ]);

        $file = $request->file('file');
        $path = $file->store('portfolio-builder/media', 'public');
        $url = '/storage/' . $path;

        $media = PortfolioMedia::create([
            'user_id' => $request->user()->id,
            'portfolio_site_id' => $request->input('site_id'),
            'filename' => $file->getClientOriginalName(),
            'path' => $path,
            'url' => $url,
            'mime_type' => $file->getClientMimeType(),
            'size_bytes' => $file->getSize() ?: 0,
        ]);

        return response()->json([
            'media' => $media,
            'message' => 'Média importé.',
        ], 201);
    }

    public function destroy(PortfolioMedia $media, Request $request)
    {
        if ($media->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($media->path && Storage::disk('public')->exists($media->path)) {
            Storage::disk('public')->delete($media->path);
        }

        $media->delete();

        return response()->json(['message' => 'Média supprimé.']);
    }
}
