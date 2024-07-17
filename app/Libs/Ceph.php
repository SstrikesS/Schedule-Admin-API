<?php

namespace App\Libs;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;
use League\Flysystem\StorageAttributes;

class Ceph
{
    public static function directories(string $path): array
    {
        return Storage::disk('ceph')->listContents($path)
            ->filter(fn(StorageAttributes $attributes) => $attributes->isDir())
            ->map(fn(DirectoryAttributes $attributes) => [
                'path'       => $attributes->path(),
                'type'       => $attributes->type(),
                'has_folder' => (bool)count(Storage::disk('ceph')
                    ->listContents($attributes->path())
                    ->filter(fn(StorageAttributes $attributes) => $attributes->isDir())
                    ->toArray())
            ])
            ->toArray();
    }

    public static function files(string $path): array
    {
        return Storage::disk('ceph')->listContents($path)
            ->filter(fn(StorageAttributes $attributes) => $attributes->isFile())
            ->map(fn(FileAttributes $attributes) => [
                'path'          => $attributes->path(),
                'size'          => $attributes->fileSize(),
                'mime_type'     => $attributes->mimeType(),
                'last_modified' => $attributes->lastModified(),
                'type'          => $attributes->type(),
            ])
            ->toArray();
    }

    public static function all(string $path): array
    {
        return Storage::disk('ceph')->listContents($path)
            ->toArray();
    }

    public static function size(string $path): string
    {
        $byte_size = Storage::disk('ceph')->size($path);

        return formatByte($byte_size);
    }

    public static function mimeType(string $path): false|string
    {
        return Storage::disk('ceph')->mimeType($path);
    }

    public static function lastModified(string $path, ?string $format = "d-m-Y H:i:s"): int|string
    {
        $timestamp = Storage::disk('ceph')->lastModified($path);

        if (is_null($format)) {
            return $timestamp;
        }

        if ($format === 'iso') {
            return Carbon::parse($timestamp)->toISOString();
        }

        return Carbon::parse($timestamp)->format($format);
    }

}
