<?php


namespace App\Traits;


use App\Models\Media;

trait Mediable
{
    public function images()
    {
        return $this->morphMany(Media::class, 'mediable')->where('mime_type', 'image');
    }

    public function Thumbnail()
    {
        return $this->morphMany(Media::class, 'mediable')->where('mime_type', 'image')->where('type', 'thumbnail');
    }

    public function Cover()
    {
        return $this->morphMany(Media::class, 'mediable')->where('mime_type', 'image')->where('type', 'cover')->select(['url', 'type', 'thumb_url']);
    }

    public function Gallery()
    {
        return $this->morphMany(Media::class, 'mediable')->where('mime_type', 'image')->where('type', 'gallery')->select(['url', 'type', 'thumb_url']);
    }

    public function videos()
    {
        return $this->morphMany(Media::class, 'mediable')->where('mime_type', 'video');
    }

}
