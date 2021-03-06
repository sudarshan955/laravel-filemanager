<?php

namespace UniSharp\LaravelFilemanager;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class LfmItem
{
    private $lfm_path;
    private $lfm;

    private $columns = ['name', 'path', 'time', 'icon', 'is_file', 'is_image', 'thumb_url'];
    public $attributes = [];

    public function __construct(LfmPath $lfm_path, Lfm $lfm)
    {
        $this->lfm_path = $lfm_path->thumb(false);
        $this->lfm = $lfm;
    }

    public function __get($var_name)
    {
        if (!array_key_exists($var_name, $this->attributes)) {
            $function_name = camel_case($var_name);
            $this->attributes[$var_name] = $this->$function_name();
        }

        return $this->attributes[$var_name];
    }

    public function fill()
    {
        foreach ($this->columns as $column) {
            $this->__get($column);
        }

        return $this;
    }

    public function name()
    {
        return $this->lfm_path->getName();
    }

    public function absolutePath()
    {
        return $this->lfm_path->path('absolute');
    }

    public function isDirectory()
    {
        return $this->lfm_path->isDirectory();
    }

    public function isFile()
    {
        return ! $this->isDirectory();
    }

    /**
     * Check a file is image or not.
     *
     * @param  mixed  $file  Real path of a file or instance of UploadedFile.
     * @return bool
     */
    public function isImage()
    {
        return starts_with($this->mimeType(), 'image');
    }

    /**
     * Get mime type of a file.
     *
     * @param  mixed  $file  Real path of a file or instance of UploadedFile.
     * @return string
     */
    // TODO: uploaded file
    public function mimeType()
    {
        // if ($file instanceof UploadedFile) {
        //     return $file->getMimeType();
        // }

        return $this->lfm_path->mimeType();
    }

    public function extension()
    {
        return $this->lfm_path->extension();
    }

    public function path()
    {
        if ($this->isDirectory()) {
            return $this->lfm_path->path('working_dir');
        }

        return $this->lfm_path->url();
    }

    public function size()
    {
        return $this->isFile() ? $this->humanFilesize($this->lfm_path->size()) : '';
    }

    public function time()
    {
        return $this->lfm_path->lastModified();
    }

    public function thumbUrl()
    {
        if ($this->isDirectory()) {
            return asset('vendor/' . Lfm::PACKAGE_NAME . '/img/folder.png');
        }

        if ($this->isImage()) {
            return $this->lfm_path->thumb($this->hasThumb())->url(true);
        }

        return null;
    }

    public function icon()
    {
        if ($this->isDirectory()) {
            return 'fa-folder-o';
        }

        if ($this->isImage()) {
            return 'fa-image';
        }

        return $this->lfm->getFileIcon($this->extension());
    }

    public function type()
    {
        if ($this->isDirectory()) {
            return trans(Lfm::PACKAGE_NAME . '::lfm.type-folder');
        }

        if ($this->isImage()) {
            return $this->mimeType();
        }

        return $this->lfm->getFileType($this->extension());
    }

    public function hasThumb()
    {
        if (!$this->isImage()) {
            return false;
        }

        if (in_array($this->mimeType(), ['image/gif', 'image/svg+xml'])) {
            return false;
        }

        if (!$this->lfm_path->thumb()->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Make file size readable.
     *
     * @param  int  $bytes     File size in bytes.
     * @param  int  $decimals  Decimals.
     * @return string
     */
    public function humanFilesize($bytes, $decimals = 2)
    {
        $size = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$decimals}f %s", $bytes / pow(1024, $factor), @$size[$factor]);
    }
}
