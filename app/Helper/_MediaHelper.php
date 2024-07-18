<?php

namespace App\Helper;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class _MediaHelper
{

    public static function upload($file, $file_name)
    {

        $path = public_path('storage/product-knowledge');
        if (!File::exists($path)) {
            File::makeDirectory($path, $mode = 0777, true, true);
        }
        Image::make($file->getRealPath())->save($path . '/' . $file_name);
        return true;
    }

    public static function uploadVideo($video, $filename)
    {
        return $video->move(public_path('storage/product-knowledge/'), $filename);
    }

    public static function delete($publicID, $type)
    {
        $path = asset('storage/product-knowledge/' . $publicID);
        if (File::exists($path)) {
            // Delete the file
            File::delete($path);
            return true;
        }
        return null;
    }


}
