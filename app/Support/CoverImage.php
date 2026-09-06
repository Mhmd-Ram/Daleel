<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Stores an event cover, shrinking it first if it is larger than the site
 * ever displays.
 *
 * Any image is accepted; the point is that nothing downstream has to cope with
 * a 4000px photo. Cards and the event hero both crop with `object-cover`, so
 * the stored file only needs to be large enough for the biggest slot on the
 * page - beyond that it is bytes the reader pays for and never sees.
 */
class CoverImage
{
    /**
     * The largest the stored file gets. Comfortably sharper than the widest
     * place a cover appears (the event hero, around 1280px), while cutting a
     * phone photo down by an order of magnitude.
     */
    public const MAX_WIDTH = 1600;

    public const MAX_HEIGHT = 1000;

    /**
     * Store the upload and return its path on the `public` disk.
     */
    public static function store(UploadedFile $file, string $directory = 'events'): string
    {
        $path = $file->store($directory, 'public');

        try {
            self::downscaleInPlace(Storage::disk('public')->path($path));
        } catch (Throwable $e) {
            // A cover that is merely too big is not worth failing an event save
            // over. Keep the original: it still displays correctly, because
            // every slot crops rather than stretches.
            report($e);
        }

        return $path;
    }

    /**
     * Shrink the file at $path to fit the maximum box, preserving its aspect
     * ratio and format. Does nothing if it already fits.
     */
    private static function downscaleInPlace(string $path): void
    {
        $info = @getimagesize($path);

        if ($info === false) {
            return;
        }

        [$width, $height] = $info;
        $mime = $info['mime'] ?? '';

        // GD expands an image to roughly 4 bytes per pixel regardless of how
        // small the compressed file is, so a 2 MB JPEG can still be far too big
        // to decode. Bail out rather than let the request die: an unshrunk
        // cover is a nuisance, a fatal error loses the whole event save.
        if (! self::canDecode($width, $height)) {
            return;
        }

        $scale = min(self::MAX_WIDTH / $width, self::MAX_HEIGHT / $height);

        // Only ever shrink. Enlarging a small cover would add bytes and blur.
        if ($scale >= 1) {
            return;
        }

        $source = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => @imagecreatefromwebp($path),
            default => false,
        };

        if ($source === false) {
            return;
        }

        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));

        $target = imagecreatetruecolor($targetWidth, $targetHeight);

        // PNG and WebP can carry alpha; without this a transparent cover comes
        // back with a black rectangle behind it.
        if ($mime !== 'image/jpeg') {
            imagealphablending($target, false);
            imagesavealpha($target, true);
        }

        imagecopyresampled(
            $target, $source,
            0, 0, 0, 0,
            $targetWidth, $targetHeight, $width, $height,
        );

        match ($mime) {
            'image/jpeg' => imagejpeg($target, $path, 82),
            'image/png' => imagepng($target, $path, 6),
            'image/webp' => imagewebp($target, $path, 82),
            default => null,
        };

        imagedestroy($source);
        imagedestroy($target);
    }

    /**
     * Whether there is headroom to decode an image of this size.
     */
    private static function canDecode(int $width, int $height): bool
    {
        $limit = self::memoryLimitInBytes();

        // No limit configured: trust the host.
        if ($limit <= 0) {
            return true;
        }

        // Four bytes a pixel for the source, plus a margin for the resized copy
        // and everything else the request is already holding.
        $needed = (int) ($width * $height * 4 * 1.3);

        return $needed < ($limit - memory_get_usage(true));
    }

    private static function memoryLimitInBytes(): int
    {
        $value = trim((string) ini_get('memory_limit'));

        if ($value === '' || $value === '-1') {
            return 0;
        }

        $unit = strtolower(substr($value, -1));
        $number = (int) $value;

        return match ($unit) {
            'g' => $number * 1024 ** 3,
            'm' => $number * 1024 ** 2,
            'k' => $number * 1024,
            default => $number,
        };
    }
}
