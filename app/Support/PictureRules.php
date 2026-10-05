<?php

namespace App\Support;

class PictureRules
{
    public static function upload(): array
    {
        return [
            'nullable',
            'file',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'mimetypes:image/jpeg,image/png,image/webp',
            'max:2048',
            'dimensions:min_width=1,min_height=1,max_width=6000,max_height=6000',
        ];
    }
}