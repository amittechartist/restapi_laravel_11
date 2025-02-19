<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\FileUploadService;

class FileController extends Controller
{
    protected $uploadService;

    /**
     * Inject the FileUploadService.
     *
     * @param FileUploadService $uploadService
     */
    public function __construct(FileUploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    /**
     * Upload a file.
     *
     * Expects a multipart/form-data request with:
     * - file: The file to upload.
     * - folder (optional): The folder name where the file should be stored.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        // Validate that a file is provided (max size: 10MB)
        $request->validate([
            'file'   => 'required|file|max:10240',
            'folder' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $folder = $request->input('folder', 'uploads');

        // Upload the file using our service
        $result = $this->uploadService->upload($file, $folder);

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully.',
            'data'    => $result,
        ]);
    }

    /**
     * Delete a file.
     *
     * Expects a JSON payload with:
     * - path: The relative file path (as returned by the upload API) to delete.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        // Validate that the file path is provided
        $request->validate([
            'path' => 'required|string',
        ]);

        $path = $request->input('path');
        $deleted = $this->uploadService->delete($path);
        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'File not found or could not be deleted.',
        ], 404);
    }

    /**
     * Get full access URL for a given file path.
     *
     * Expects a JSON payload like:
     * {
     *    "path": "logo/1235645646.jpg"
     * }
     *
     * The helper in our service generates the complete URL.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUrl(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $path = $request->input('path');

        // Use our helper to generate the full URL.
        $fullUrl = $this->uploadService->getFullUrl($path);

        return response()->json([
            'success'  => true,
            'full_url' => $fullUrl,
        ]);
    }
}
