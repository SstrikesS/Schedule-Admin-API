<?php

namespace App\Models\V1\Image;

use Illuminate\Support\Facades\File;
use Intervention\Image\Constraint;
use Intervention\Image\ImageManagerStatic;

class ImageModel
{
    private const RESIZE
        = [
            'r',
            'rc',
            'rc_top',
            'rc_bottom',
            'o',
        ];

    public static function resize($filename, $width = 100, $height = 100, $type = 'r', $fill = false)
    {
        if (str_starts_with($filename, 'http')) {
            return $filename;
        }

        if (!self::isFile($filename)) {
            return '';
        }

        if (!in_array($type, self::RESIZE)) {
            $type = 'r';
        }

        if ($type == 'o') {
            return self::getOrig($filename);
        }
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $old_image = $filename;
        $new_image = 'cache/' . substr($filename, 0, strrpos($filename, '.')) . '-' . $width . 'x' . $height . '-' . $type . '.' . $extension;
        if (!is_file(self::pathImage($new_image)) || (filectime(self::pathImage($old_image)) > filectime(self::pathImage($new_image)))) {
            $path = '';

            $directories = explode('/', dirname(str_replace('../', '', $new_image)));
            foreach ($directories as $directory) {
                $path = $path . '/' . $directory;

                if (!is_dir(self::pathImage($path))) {
                    File::makeDirectory(self::pathImage($path));
                }
            }

            [$width_orig, $height_orig] = getimagesize(self::pathImage($old_image));

            if ($width_orig != $width || $height_orig != $height) {
                if ($fill) {
                    $dimension = $width_orig > $height_orig ? 'h' : 'w';
                } else {
                    $dimension = '';
                }

                $path = '';

                $directories = explode('/', dirname(str_replace('../', '', $new_image)));

                foreach ($directories as $directory) {
                    $path = $path . '/' . $directory;

                    if (!is_dir(self::pathImage($path))) {
                        File::makeDirectory(self::pathImage($path));
                    }
                }

                $image = ImageManagerStatic::make(self::pathImage($old_image));

                switch ($type) {
                    case 'rc':
                    case 'rc_top':
                    case 'rc_bottom':
                        $image->fit($width, $height, function (Constraint $constraint) {
                            $constraint->upsize();
                        });

                        break;
                    default:
                        $image->resize($width, $height, function (Constraint $constraint) {
                            $constraint->aspectRatio();
                        });

                        break;
                }

                $image->save(self::pathImage($new_image), 90);
            } else {
                File::copy(self::pathImage($old_image), self::pathImage($new_image));
            }
        }
        return self::urlImage($new_image);
    }

    public static function noImage($width = 100, $height = 100, $type = 'r')
    {
        return self::resize('image/no_image.png', $width, $height, $type);
    }

    public static function noAvatar($width = 200, $height = 200)
    {
        return self::resize('profile/no_avatar.jpg', $width, $height);
    }

    public static function noBackground($width = 960, $height = 540)
    {
        return self::resize('background/no_background.jpg', $width, $height);
    }

    public static function getOrig($filename)
    {
        if (str_starts_with($filename, 'http')) {
            return $filename;
        }

        return self::urlImage($filename);
    }

    public static function isFile($filename): bool
    {
        if (str_starts_with($filename, 'http')) {
            return true;
        } else {
            return is_file(self::pathImage($filename));
        }
    }

    public static function pathImage($filename): string
    {
        return storage_path("app/$filename");
    }

    public static function urlImage($filename): string
    {

        return asset($filename);
    }
}
