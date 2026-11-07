<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ImageService
{
    /**
     * Store an uploaded image in both public/storage/images and storage/app/public/images
     *
     * @param mixed $uploadedFile Instance of UploadedFile or Livewire TemporaryUploadedFile
     * @param string $prefix Prefix for the filename
     * @return string Relative path stored in database (e.g. 'storage/images/filename.ext')
     */
    public static function saveImage($uploadedFile, string $prefix = 'img'): string
    {
        $publicDir = public_path('storage/images');
        $storageDir = storage_path('app/public/images');

        File::ensureDirectoryExists($publicDir);
        File::ensureDirectoryExists($storageDir);

        $ext = $uploadedFile->getClientOriginalExtension() ?: 'jpg';
        $filename = $prefix . '_' . time() . '_' . Str::random(6) . '.' . strtolower($ext);

        $tempPath = $uploadedFile->getRealPath();

        // Copy directly from temp file to both destinations
        if ($tempPath && file_exists($tempPath)) {
            copy($tempPath, $publicDir . DIRECTORY_SEPARATOR . $filename);
            copy($tempPath, $storageDir . DIRECTORY_SEPARATOR . $filename);
        } else {
            // Fallback for objects that support storeAs with public disk
            $uploadedFile->storeAs('images', $filename, 'public');
            @copy(storage_path('app/public/images/' . $filename), $publicDir . DIRECTORY_SEPARATOR . $filename);
        }

        return 'storage/images/' . $filename;
    }
}
