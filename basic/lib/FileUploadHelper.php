<?php
/**
 * Created by PhpStorm.
 * User: pengyanzhang
 * Date: 2016/6/29
 * Time: 11:01
 * 文件上传类
 *
 */

namespace app\lib;

use yii;
use yii\web\UploadedFile;
use app\lib\FResponse;

class FileUploadHelper
{
    public static function fileUpload($path, $size=50, $ext=[])
    {
        $file = UploadedFile::getInstanceByName('file');
        if ($file == null) {
            FResponse::output(['code' => 0,'msg' => '没有文件被上传或超过了服务器大小限制']);
        }
        if ($file->error == 1) {
            FResponse::output(['code' => 0,'msg' => '文件大小超过限制']);
        }
        if ($file->size <= 0 ) {
            FResponse::output(['code' => 0,'msg' => '文件大小必须大于0']);
        }
        if ($file->size > $size * 1024 * 1024) {
            FResponse::output(['code' => 0,'msg' => '文件大小超过限制']);
        }
        if(count($ext)>0){
            if(!in_array($file->extension,$ext)){
                $strext = implode(',',$ext);
                FResponse::output(['code' => 0,'msg' => '文件类型不符,只能上传'.$strext.'类型的文件']);
            }
        }
        $uploadPath = self::savePath($file->extension,$path);
        if (!$file->saveAs($uploadPath['save_path'].$uploadPath['save_name'])){
            FResponse::output(['code' => 0,'msg' => '上传失败，请重试']);
        }

        //处理上传附件图标
        $file_type='moren';
        $attIcon = include(FILE_ROOT.'/config/atticon.php');
        foreach($attIcon as $key=>$val){
            if(in_array($file->extension,$val)){
                $file_type=$key;
                break;
            }
        }

        return [
            'code'=>1,
            'data'=>[
                'file_name' => $file->name,
                'real_name' => $uploadPath['save_name'],
                'full_path' => $uploadPath['save_path'],
                'file_path' => $uploadPath['file_path'],
                'file_size' => $file->size,
                'file_type' => $file_type
            ]
        ];
    }

    /*
     * 递归创建目录
     * @param $dir文件路径
     */
    public static function createDir($dir)
    {
        if(!is_dir($dir)){
            if(!self::createDir(dirname($dir))){
                return false;
            }
            if(!mkdir($dir, 0777)){
                return false;
            }
        }
        return $dir;
    }

    public static function savePath($ext,$path)
    {
        $arrPath = explode('/',$path);
        $tmp_path = date('Ymd');
        $dirPath = $path.'/'.$tmp_path.'/';
        $fileName = self::createFileName($ext);
        if($savePath = self::createDir($dirPath)){
            return ['save_path' => $savePath ,'save_name' => $fileName,'file_path' => end($arrPath).'/'.$tmp_path];
        }
        return false;
    }
    public static function createFileName($ext)
    {
        $fileNameHash = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz1234567890'), 0, 5);
        return sprintf('%s%s.%s', $fileNameHash, date('YmdHis'), $ext);
    }
}