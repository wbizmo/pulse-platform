<?php

namespace App\Actions\Media;

use App\Actions\Access\RecordAudit;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StoreMedia
{
    public function __construct(private readonly RecordAudit $audit) {}

    public function execute(UploadedFile $file, User $actor): Media
    {
        $info = @getimagesize($file->getRealPath());
        $mime = $info['mime'] ?? null;
        if (! $info || ! in_array($mime, config('media.mime_types'), true) || ($info[0] * $info[1]) > config('media.max_pixels')) {
            throw ValidationException::withMessages(['files' => 'The upload is not a supported, safely decodable image.']);
        }
        $extension = match ($mime) {
            'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'
        };
        $disk = config('media.disk');
        $fileName = Str::uuid().'.'.$extension;
        $path = trim(config('media.directory'), '/').'/'.$fileName;
        $image = @imagecreatefromstring(file_get_contents($file->getRealPath()));
        if (! $image) {
            throw ValidationException::withMessages(['files' => 'The upload is not a supported, safely decodable image.']);
        }
        $normalized = tempnam(sys_get_temp_dir(), 'pulse-media-');
        try {
            $encoded = match ($mime) {
                'image/jpeg' => imagejpeg($image, $normalized, 90),
                'image/png' => imagepng($image, $normalized, 6),
                'image/webp' => imagewebp($image, $normalized, 90),
                'image/gif' => imagegif($image, $normalized),
            };
            $stream = $encoded ? fopen($normalized, 'rb') : false;
            if (! $stream || ! Storage::disk($disk)->put($path, $stream)) {
                throw ValidationException::withMessages(['files' => 'The image could not be safely processed and stored.']);
            }
        } finally {
            imagedestroy($image);
            if (isset($stream) && is_resource($stream)) {
                fclose($stream);
            }
            @unlink($normalized);
        }
        try {
            $media = Media::create([
                'user_id' => $actor->id, 'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) ?: 'Image',
                'original_name' => basename(str_replace('\\', '/', $file->getClientOriginalName())), 'file_name' => $fileName,
                'mime_type' => $mime, 'extension' => $extension, 'disk' => $disk, 'path' => $path,
                'url' => Storage::disk($disk)->url($path), 'size' => Storage::disk($disk)->size($path), 'width' => $info[0], 'height' => $info[1], 'type' => 'image',
            ]);
            $this->audit->execute($actor, 'media.created', $media, ['mime_type' => $mime, 'size' => $file->getSize()]);

            return $media;
        } catch (\Throwable $exception) {
            Storage::disk($disk)->delete($path);
            throw $exception;
        }
    }
}
