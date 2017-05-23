<?php

namespace common\models;

use yii\web\UploadedFile;
use yii\base\Model;

class Image extends Model
{
    public $upfile;

    public function rules()
    {
        return [
            [['upfile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg'],
        ];
    }

    public function upload()
    {
        if ($this->validate()) {
            $this->upfile->saveAs('uploads/' . $this->upfile->baseName . '.' . $this->upfile->extension);
            return true;
        } else {
            return false;
        }
    }

}