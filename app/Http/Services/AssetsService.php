<?php

namespace App\Http\Services;

use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AssetsService
{
    use ResponseTrait;

    /**
     * Validation of image
     * @param mixed $file
     * @return array
     */
    public function storeImage($file)
    {
        $filePath = "";
        try {
            $originalName = $file->getClientOriginalName();

            // Ensure the file extension is valid and there is no path traversal in the file name
            if (preg_match('/\.[^.]+\./', $originalName)) {
                return [
                    'status' => false,
                    'msg' => 'Avatar -> Action Not Allowed',
                    'code' => 403
                ];
            }

            // Check for path traversal attack (e.g., using ../ or ..\ or / to go up directories)
            if (strpos($originalName, '..') !== false || strpos($originalName, '/') !== false || strpos($originalName, '\\') !== false) {
                return [
                    'status' => false,
                    'msg' => 'Avatar -> Path Traversal Detected',
                    'code' => 403
                ];
            }

            $maxFileSize = 5 * 1024 * 1024; // 5 ميجابايت
            if ($file->getSize() > $maxFileSize) {
                return [
                    'status' => false,
                    'msg' => 'Avatar -> File Too Large',
                    'code' => 403
                ];
            }

            // Validate the MIME type to ensure it's an image
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $mime_type = $file->getClientMimeType();

            if (!in_array($mime_type, $allowedMimeTypes)) {
                return [
                    'status' => false,
                    'msg' => 'Avatar -> Invalid File Type',
                    'code' => 403
                ];
            }

            // Generate a safe, random file name
            $fileName = Str::random(32);

            $extension = $file->getClientOriginalExtension(); // Safe way to get file extension
            $filePath = "Images/{$fileName}.{$extension}";

            // Store the file securely
            $path = Storage::disk('local')->putFileAs('public/Images', $file, $fileName . '.' . $extension);

            // Get the full URL path of the stored file
            $url = Storage::disk('local')->url($path);

            // Return the URL of the uploaded image
            return [
                'status' => true,
                'url' => $url
            ];

        } catch (Exception $e) {
            // حذف الصورة في حالة الفشل
            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            return [
                'status' => false,
                'msg' => $e->getMessage(),
                'code' => 500
            ];
        }

    }
}
