<?php

namespace App\Traits\UploadFiles;

use App\Traits\UploadFiles\UploadFIle;

trait MorphUpload
{
    use UploadFIle;
    /**
     * @param array $image_fillable, model $model
     * @param model $model
     * USAGE: 
     * (self:: or $this->)insertMorphFile($file_or_image , $models)
     * Insert single file
     */
    public static function  insertMorphFile($file_or_image_fillables, $model)
    {
        // *** Insert into morph image by model provided
        $images = self::pushImages($file_or_image_fillables);
        foreach ($images as $image) :
            $model->files()->create($image);
        endforeach;
    }

    /**
     * @param array $image_fillable, model $model
     * @param model $model
     * USAGE: 
     * (self:: or $this->)updateMorphFiles($file_or_image , $models)
     * Update single file
     */
    public static function updateMorphFiles($file_or_image_fillables, $models)
    {
        foreach ($file_or_image_fillables as $img) {
            $file_ex_name = (new class
            {
                use UploadFIle;
            })->multiUpload($img, request());
            if (request()->hasFile($img)) {
                $models->files()->where('type', $img)->forceDelete();
            }
            foreach ($file_ex_name as $f_name) {
                $models->files()->create(['url' => $f_name, 'type' => $img]);
            }
        }
    }
    // *** Insert multiple files
    public static function  insertMultiMorphFile($image_fillable, $model)
    {
        $images = self::pushMultiImages($image_fillable);
        foreach ($images as $image) :
            $model->files()->create($image);
        endforeach;
    }
    // *** Update multiple files
    public static function updateMultiMorphFiles($image_fillable, $models)
    {
        foreach ($image_fillable as $img) {
            $file_ex_name = (new class
            {
                use UploadFIle;
            })->multiUpload($img, request());
            if (request()->hasFile($img)) {
                $file_found = $models->files()->where('type', $img);
                $file_found->forceDelete();
                // ! Need to check to improve file upload perform 
                foreach ($file_found as $item) {
                    (new class
                    {
                        use UploadFIle;
                    })->deleteFiel($item->url);
                }
            }
            foreach ($file_ex_name as $f_name) {
                $models->files()->create(['url' => $f_name, 'type' => $img]);
            }
        }
    }
    // *** Push single image
    public static function pushImages(array $images): array
    {
        $get_image = [];
        foreach ($images as $img) :
            $file_ex_name = (new class
            {
                use UploadFIle;
            })->singleUpload($img, request());
            if ($file_ex_name) :
                array_push($get_image, ['url' =>  $file_ex_name, 'type' => $img]);
            endif;
        endforeach;

        return $get_image;
    }
    // *** Push multiple images
    public static function pushMultiImages(array $images): array
    {
        $get_image = [];
        foreach ($images as $img) :
            $file_ex_name = (new class
            {
                use UploadFIle;
            })->multiUpload($img, request());
            foreach ($file_ex_name as $item) {
                if ($file_ex_name) :
                    array_push($get_image, ['url' =>  $item, 'type' => $img]);
                endif;
            }
        endforeach;
        return $get_image;
    }

    public static function base64MorphOneFile($field_file, $model)
    {
        if (request()->has($field_file)) {
            $file = self::base64UploadFile(request()->{$field_file}, true);
            $model->fileOne()->create(['url' => $file['file_name'], 'type' => $field_file, 'mime_type' => $file['mime_type']]);
        }
    }

    // *** Base64 Upload MorphMany one by one file given
    public static function base64MorphManyByGiven($filed_name, $file_base64, $model)
    {
        if (!empty($file_base64)) {
            $file = self::base64UploadFile($file_base64, true);
            $model->fileOne()->create(['url' => $file['file_name'], 'type' => $filed_name, 'mime_type' => $file['mime_type']]);
        }
    }
    // *** Base64 Upload MorphMany Files
    public static function base64MorphManyFiles($image_fillable, $model)
    {
        foreach ($image_fillable as $file) {
            if (request()->has($file)) {
                foreach (request()->$file as $img) {
                    $file = self::base64UploadFile($img, true);
                    if ($file['file_name']) {
                        $model->fileMany()->create(['url' => $file['file_name'], 'type' => $file, 'mime_type' => $file['mime_type']]);
                    }
                }
            }
        }
    }
}
