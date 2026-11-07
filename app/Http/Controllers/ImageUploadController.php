<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ImageUploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp,svg|max:10240',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            
            // Store directly in storage/app/public/images/ and copy/link to public/storage/images/
            $destinationPath = storage_path('app/public/images');
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            $file->move($destinationPath, $filename);

            // Also ensure it is in public/storage/images/ for immediate direct serving
            $publicImagesPath = public_path('storage/images');
            if (!is_dir($publicImagesPath)) {
                mkdir($publicImagesPath, 0777, true);
            }
            copy($destinationPath . '/' . $filename, $publicImagesPath . '/' . $filename);

            return response()->json([
                'url' => asset('storage/images/' . $filename),
                'path' => 'storage/images/' . $filename,
            ]);
        }

        return response()->json(['error' => 'Échec du téléversement.'], 400);
    }
}
