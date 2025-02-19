<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    /**
     * The disk where files will be stored.
     *
     * @var string
     */
    protected $disk;

    /**
     * Create a new FileUploadService instance.
     *
     * @param string $disk
     */
    public function __construct(string $disk = 'private_uploads')
    {
        $this->disk = $disk;
    }

    /**
     * Upload the given file to a specified folder.
     *
     * @param UploadedFile $file
     * @param string|null  $folder (defaults to 'uploads')
     * @return array
     */
    public function upload(UploadedFile $file, ?string $folder = 'uploads'): array
    {
        // Generate a unique filename
        $filename = (string) Str::uuid() . '.' . $file->getClientOriginalExtension();

        // Build the storage path (folder/filename)
        $path = $folder ? $folder . '/' . $filename : $filename;

        // Store the file on the specified disk
        $file->storeAs($folder, $filename, $this->disk);

        return [
            'filename' => $filename,
            'path'     => $path,
        ];
    }

    /**
     * Delete a file from storage.
     *
     * @param string $path The relative path to the file (e.g., "uploads/uuid.ext")
     * @return bool
     */
    public function delete(string $path): bool
    {
        return Storage::disk($this->disk)->delete($path);
    }

    /**
     * Get the full access URL for a given file path.
     *
     * Simply pass a relative file path (e.g. "logo/1235645646.jpg") and this
     * method returns a complete URL that points to your file-serving endpoint.
     *
     * @param string $path
     * @return string
     */
    public function getFullUrl(string $path): string
    {
        // Remove any leading slashes from the path.
        $path = ltrim($path, '/');

        // Assuming you have created a symbolic link so that the files in storage/app/uploads are available at public/uploads:
        return url('storage/' . $path);
    }
}
