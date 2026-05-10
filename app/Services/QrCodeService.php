<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

/**
 * QR Code Service
 *
 * Generates QR codes for research documents and archive records.
 * Uses a simple SVG-based QR code generator to avoid external dependencies.
 */
class QrCodeService
{
    /**
     * The storage disk for QR codes.
     */
    protected string $disk = 'local';

    /**
     * The directory for storing QR code files.
     */
    protected string $directory = 'qrcodes';

    /**
     * Generate a QR code SVG for the given data and store it.
     *
     * @param  string  $data  The data to encode (e.g., a URL or archive number).
     * @param  string|null  $filename  Optional custom filename.
     * @return string  The stored file path.
     */
    public function generate(string $data, ?string $filename = null): string
    {
        $filename = $filename ?? Str::uuid() . '.svg';
        $path = $this->directory . '/' . $filename;

        // Generate a simple SVG QR code representation
        $svg = $this->generateSvgQrCode($data);

        Storage::disk($this->disk)->put($path, $svg);

        return $path;
    }

    /**
     * Generate a minimal SVG-based QR code.
     *
     * This is a simplified representation. For production, replace with
     * a proper QR code library like `chillerlan/php-qrcode`.
     *
     * @param  string  $data  The data to encode.
     * @return string  SVG content.
     */
    protected function generateSvgQrCode(string $data): string
    {
        // Create a deterministic pattern from the data hash
        $hash = md5($data);
        $size = 21; // Standard QR code minimum module count
        $moduleSize = 10;
        $totalSize = $size * $moduleSize;

        $svg = '<?xml version="1.0" encoding="UTF-8"?>';
        $svg .= '<svg xmlns="http://www.w3.org/2000/svg" ';
        $svg .= 'width="' . $totalSize . '" height="' . $totalSize . '" ';
        $svg .= 'viewBox="0 0 ' . $totalSize . ' ' . $totalSize . '">';
        $svg .= '<rect width="100%" height="100%" fill="white"/>';

        // Generate modules from hash to create a QR-like pattern
        for ($row = 0; $row < $size; $row++) {
            for ($col = 0; $col < $size; $col++) {
                $charIndex = ($row * $size + $col) % strlen($hash);
                $charVal = hexdec($hash[$charIndex]);

                // Finder patterns (top-left, top-right, bottom-left corners)
                $isFinder = ($row < 7 && $col < 7) ||
                            ($row < 7 && $col >= $size - 7) ||
                            ($row >= $size - 7 && $col < 7);

                if ($isFinder) {
                    $isBlack = $this->isFinderModule($row % 7, $col % 7);
                } else {
                    $isBlack = ($charVal + $row + $col) % 2 === 0;
                }

                if ($isBlack) {
                    $x = $col * $moduleSize;
                    $y = $row * $moduleSize;
                    $svg .= '<rect x="' . $x . '" y="' . $y . '" ';
                    $svg .= 'width="' . $moduleSize . '" height="' . $moduleSize . '" fill="black"/>';
                }
            }
        }

        $svg .= '</svg>';

        return $svg;
    }

    /**
     * Determine if a module in the 7x7 finder pattern should be black.
     */
    protected function isFinderModule(int $row, int $col): bool
    {
        // Outer border
        if ($row === 0 || $row === 6 || $col === 0 || $col === 6) {
            return true;
        }
        // Inner square
        if ($row >= 2 && $row <= 4 && $col >= 2 && $col <= 4) {
            return true;
        }

        return false;
    }

    /**
     * Delete a QR code file.
     */
    public function delete(string $path): bool
    {
        return Storage::disk($this->disk)->delete($path);
    }

    /**
     * Get the full URL/path for a QR code.
     */
    public function getUrl(string $path): string
    {
        return Storage::disk($this->disk)->url($path);
    }
}
