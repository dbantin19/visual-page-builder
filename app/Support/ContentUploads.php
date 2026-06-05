<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ContentUploads
{
    public const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'];
    public const VIDEO_EXTENSIONS = ['mp4', 'webm', 'mov', 'm4v', 'ogg', 'ogv'];
    public const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'mp4', 'webm', 'mov', 'm4v', 'ogg', 'ogv'];

    private const MAX_FILES = 50;
    private const MAX_FILE_KILOBYTES = 51200;

    public static function all(): Collection
    {
        File::ensureDirectoryExists(self::directory());

        return collect(File::files(self::directory()))
            ->filter(fn ($file) => in_array(strtolower($file->getExtension()), self::ALLOWED_EXTENSIONS, true))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->map(function ($file) {
                $extension = strtolower($file->getExtension());

                return [
                    'name' => $file->getFilename(),
                    'url' => self::publicUrl($file->getFilename()),
                    'size' => $file->getSize(),
                    'updated_at' => $file->getMTime(),
                    'extension' => $extension,
                    'type' => self::typeForExtension($extension),
                ];
            })
            ->values();
    }

    public static function store(UploadedFile $file): array
    {
        File::ensureDirectoryExists(self::directory());

        $extension = strtolower($file->getClientOriginalExtension());
        $baseName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'media';
        $filename = self::uniqueFilename($baseName, $extension);

        $file->move(self::directory(), $filename);

        return [
            'name' => $filename,
            'url' => self::publicUrl($filename),
            'size' => File::size(self::directory().DIRECTORY_SEPARATOR.$filename),
            'extension' => $extension,
            'type' => self::typeForExtension($extension),
        ];
    }

    public static function delete(string $filename): string
    {
        $safeName = self::safeFilename($filename);
        $path = self::directory().DIRECTORY_SEPARATOR.$safeName;

        abort_unless(File::exists($path), 404, 'Upload not found.');

        File::delete($path);

        return $safeName;
    }

    public static function uploadConfig(): array
    {
        $maxFileBytes = min(
            self::MAX_FILE_KILOBYTES * 1024,
            self::iniBytes('upload_max_filesize', self::MAX_FILE_KILOBYTES * 1024),
        );
        $maxPostBytes = self::iniBytes('post_max_size', 512 * 1024 * 1024);
        $maxFiles = min(self::MAX_FILES, max(1, (int) ini_get('max_file_uploads') ?: self::MAX_FILES));

        return [
            'max_files' => $maxFiles,
            'max_file_bytes' => $maxFileBytes,
            'max_file_kilobytes' => max(1, (int) floor($maxFileBytes / 1024)),
            'max_file_label' => self::formatBytes($maxFileBytes),
            'max_post_bytes' => $maxPostBytes,
            'max_post_label' => self::formatBytes($maxPostBytes),
            'accepted_mime_types' => 'image/jpeg,image/png,image/gif,image/webp,image/avif,video/mp4,video/webm,video/quicktime,video/x-m4v,video/ogg',
            'accepted_extension_label' => 'JPG, PNG, GIF, WebP, AVIF, MP4, WebM, MOV, M4V, or OGG',
        ];
    }

    public static function validationMimeRule(): string
    {
        return 'mimes:'.implode(',', self::ALLOWED_EXTENSIONS);
    }

    public static function typeForExtension(string $extension): string
    {
        return in_array(strtolower($extension), self::VIDEO_EXTENSIONS, true) ? 'video' : 'image';
    }

    private static function uniqueFilename(string $baseName, string $extension): string
    {
        do {
            $filename = $baseName.'-'.Str::lower(Str::random(8)).'.'.$extension;
        } while (File::exists(self::directory().DIRECTORY_SEPARATOR.$filename));

        return $filename;
    }

    private static function safeFilename(string $filename): string
    {
        $safeName = basename($filename);
        $extension = strtolower(pathinfo($safeName, PATHINFO_EXTENSION));

        abort_unless($safeName === $filename && $safeName !== '', 422, 'Invalid upload filename.');
        abort_unless(in_array($extension, self::ALLOWED_EXTENSIONS, true), 422, 'Invalid upload filename.');

        return $safeName;
    }

    private static function directory(): string
    {
        return public_path('uploads/content');
    }

    private static function publicUrl(string $filename): string
    {
        return asset('uploads/content/'.$filename);
    }

    private static function iniBytes(string $key, int $fallback): int
    {
        $value = trim((string) ini_get($key));
        if ($value === '') {
            return $fallback;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        $bytes = match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };

        return $bytes > 0 ? (int) $bytes : $fallback;
    }

    private static function formatBytes(int $bytes): string
    {
        if ($bytes >= 1024 * 1024) {
            return rtrim(rtrim(number_format($bytes / 1024 / 1024, 1), '0'), '.').' MB';
        }

        return rtrim(rtrim(number_format($bytes / 1024, 1), '0'), '.').' KB';
    }
}
