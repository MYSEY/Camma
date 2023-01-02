<?php

namespace App\Traits\UploadFiles;

use App\Helpers\Helper;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

trait UploadFIle
{

    protected $isBase64 = 'data:image';
    public static function funcGlobal(): array
    {
        $data = array();
        $data['upload_disk'] = config('const.upload_disk');
        $data['original_path'] = config('const.filePath.original');
        return $data;
    }

    public function singleUpload($attr, $request, $thumbnail = true)
    {
        $filename = '';
        if ($request->hasFile($attr)) {
            $file = $request->file($attr);
            if ($file->isValid()) {
                $filename = self::generatFileName($file);
                $file->storeAs($this->funcGlobal()['original_path'], $filename, $this->funcGlobal()['upload_disk']);
                if ($thumbnail) {
                    self::createThumbnail($file, $filename);
                }
            }
        }
        return $filename;
    }

    public function multiUpload($attr, $request): array
    {
        if ($request->hasFile($attr)) {
            $file = $request->file($attr);
            $fileArr = [];
            foreach ($file as $f) {
                if ($f->isValid()) {
                    $filename = self::generatFileName($f);
                    $f->storeAs($this->funcGlobal()['original_path'], $filename, $this->funcGlobal()['upload_disk']);
                    array_push($fileArr, $filename);
                }
            }
            return $fileArr;
        }
        return [];
    }
    public function uploadByArrayIndex($file, $thumbnail = true)
    {
        $filename = '';
        if (!empty($file)) {
            $filename = self::generatFileName($file);
            $extension =  $file->getClientOriginalExtension();
            $file->storeAs($this->funcGlobal()['original_path'], $filename, $this->funcGlobal()['upload_disk']);
            if (self::checkImageExtension($extension) && $thumbnail) {
                self::createThumbnail($file, $filename);
            }
        }
        return $filename;
    }
    public function multipleUploads($attr, $request, $thumbnail = true)
    {
        $returnImages = [];
        if ($request->hasFile($attr) && is_array($request->file($attr))) {
            $files = $request->file($attr);
            foreach ($files as $file) {
                if ($file->isValid()) {
                    $filename = self::generatFileName($file);
                    $file->storeAs($this->funcGlobal()['original_path'], $filename, $this->funcGlobal()['upload_disk']);
                    if ($thumbnail) {
                        self::createThumbnail($file, $filename);
                    }
                    $returnImages[] = $filename;
                }
            }
        }
        return json_encode($returnImages);
    }
    public function base64Upload($value, $thumbnail = true, $mainImage = true)
    {
        $filename = '';
        if (starts_with($value, $this->isBase64)) {
            $image = Image::make($value);
            $filename = md5($value . time()) . '.jpg';
            if ($mainImage) {
                Storage::disk($this->funcGlobal()['upload_disk'])
                    ->put($this->funcGlobal()['original_path'] . $filename, $image->stream());
            }
            if ($thumbnail) {
                self::createThumbnail($image, $filename);
            }
        }
        return $filename;
    }
    /**
     * @param string provide string base64 unlimited size
     * @return string|null as filename|null
     *  with all file pdf xlsx xls png jpg.....
     */
    public static function base64UploadFile($base64Image, $mimeType = false,)
    {
        $self = new static;
        // decode string to file system
        if (starts_with($base64Image, $self->isBase64)) :
            @list($type, $fileData) = explode(';', $base64Image);
            @list(, $fileData) = explode(',', $fileData);
            $base64File = base64_decode($fileData);
            // convert base 64 to mime-type Ex: xlsx, pdf ...
            $mime = $self->mimeTypeMap(mime_content_type($base64Image));
            // generate file name after decode
            $fileName = md5($base64Image . time()) . '.' . "$mime";
            Storage::disk($self->funcGlobal()['upload_disk'])
                ->put($self->funcGlobal()['original_path'] . $fileName, $base64File);
        endif;
        if ($mimeType) {
            return [
                "file_name" => $fileName ?? '',
                "mime_type" => $mime ?? ''
            ];
        }

        return $fileName ?? $base64Image;
    }

    public static function mimeTypeMap($mime)
    {
        $mimeMap = config('mimeMap.map');
        return isset($mimeMap[$mime]) === true ? $mimeMap[$mime] : false;
    }

    public function base64Uploads($values, $thumbnail = true, $mainImage = true)
    {
        $filenames = [];
        if (!empty($values) && is_array($values)) {
            foreach ($values as $value) {
                if (starts_with($value, $this->isBase64)) {
                    $image = Image::make($value);
                    $filename = md5($value . time()) . '.jpg';
                    if ($mainImage) {
                        Storage::disk($this->funcGlobal()['upload_disk'])
                            ->put($this->funcGlobal()['original_path'] . $filename, $image->stream());
                    }
                    if ($thumbnail) {
                        self::createThumbnail($image, $filename);
                    }
                    $filenames[] = $filename;
                }
            }
        }
        return json_encode($filenames);
    }
    static function createThumbnail($file, $filename)
    {
        self::uploadThumbnail($file, config('const.filePath.large') . $filename, 'large');
        self::uploadThumbnail($file, config('const.filePath.medium') . $filename, 'medium');
        self::uploadThumbnail($file, config('const.filePath.small') . $filename, 'small');
    }
    static function uploadThumbnail($file, $path, $size)
    {
        try {
            $image = Image::make($file);
            $originWidth = $image->width();
            if ($size == 'small') {
                $width = $originWidth * 0.5;
            }
            if ($size == 'medium') {
                $width = $originWidth * 0.6;
            }
            if ($size == 'large') {
                $width = $originWidth * 0.7;
            }
            $image->resize($width, null, function ($constraint) {
                $constraint->aspectRatio();
            })->stream();
            Storage::disk(self::funcGlobal()['upload_disk'])->put($path, $image);
        } catch (\Exception $exp) {
            Log::error($exp->getMessage());
        }
    }
    public function getUploadImage($image, $size = 'large', $default = 'default')
    {
        $returnImage = '';
        if (empty($image)) {
            $returnImage = config('const.filePath.' . $default);
        } else {
            $extension = Helper::is_extensions($image);
            if (self::checkImageExtension($extension)) {
                $returnImage = self::switchImageSize($image, $size);
            } else {
                $returnImage = self::switchImageSize($image, 'original');
            }
        }
        return $returnImage;
    }
    static function switchImageSize($images, $size = 'large')
    {
        $result = "";
        switch ($size) {
            case 'small':
                $result = config('const.s3Path.small') . $images;
                break;
            case 'medium':
                $result = config('const.s3Path.medium') . $images;
                break;
            case 'large':
                $result = config('const.s3Path.large') . $images;
                break;
            case 'original':
                $result = config('const.s3Path.original') . $images;
                break;
            default:
                return "";
        }
        return $result;
    }
    static function generatFileName($file)
    {
        // \Log::info($file->getClientOriginalExtension())
        if (!empty($file)) :
            return md5($file->getClientOriginalName() .
                random_int(1, 9999) . time()) . '.' . $file->getClientOriginalExtension();
        endif;
        return null;
    }
    static function checkImageExtension($extension)
    {
        if (!empty($extension) && in_array($extension, ['jpg', 'jpeg', 'jfif', 'pjpeg', 'pjp', 'png', 'ico', 'cur'])) :
            return true;
        endif;
        return false;
    }
    /**
     * checkExtension
     *
     * @param string $value
     * @return string
     */
    static function checkBase64Extension($value)
    {
        $allExtensions = [
            'jpg', 'png', 'jpeg', 'pdf', 'docx', 'docm', 'dotx', 'dotm',
            'xlsx', 'xlsm', 'xltx', 'xltm', 'xlsb', 'xlam', 'pptx', 'pptm',
            'potx', 'potm', 'ppam', 'ppsx', 'ppsm', 'sldx', 'sldm', 'thmx'
        ];
        $extension = explode(";", explode("/", $value)[1])[0];
        if (in_array($extension, $allExtensions)) {
            return '.' . $extension;
        }
        return '.jpg';
    }
    public function uploadLoopSingleFile($attr, $request, $key, $thumbnail = true)
    {
        $filename = '';
        if ($request->hasFile($attr)) {
            $file = $request->file($attr)[$key] ?? null;
            if ($file) {
                $filename = self::generatFileName($file);
                $file->storeAs($this->funcGlobal()['original_path'], $filename, $this->funcGlobal()['upload_disk']);
                if ($thumbnail) {
                    self::createThumbnail($file, $filename);
                }
            }
        }
        return $filename;
    }
    public function deleteFiel($file)
    {
        Storage::disk($this->funcGlobal()['upload_disk'])->delete(config('const.filePath.small') . $file);
        Storage::disk($this->funcGlobal()['upload_disk'])->delete(config('const.filePath.medium') . $file);
        Storage::disk($this->funcGlobal()['upload_disk'])->delete(config('const.filePath.large') . $file);
        Storage::disk($this->funcGlobal()['upload_disk'])->delete($this->funcGlobal()['original_path'] . $file);
    }
}
