<?php

namespace app\modules\permission\helper;

//控制器辅助类
use app\models\PermissionModel;

class PermissionHelper {
   //获取所有的方法列表
   public static function getControllerActionList($pathFile)
   {
        $listFile = self::dir2array($pathFile, true);
        $allController = array();
        foreach ($listFile['files'] as $key => $value) {
            $is_controller = strstr($value, 'Controller');
            $is_bak = strstr($value, 'bak');
            if ($is_controller && !$is_bak) {
                $allController[] = $value;
            }
        }

        $allAction = array();

        foreach ($allController as $key => $value) {

            $fileContent = file($pathFile . $value);

            $se = explode("Controller.php", $value);
            $allAction[] = $se[0]; // . "/";

            foreach ($fileContent as $k => $val) {
                if (strstr($val, "public function action")) {
                    if (!strstr($val, "actions()") && !strstr($val, "/*") && !strstr($val, 'if (strstr($val, "public function action")) {')) {
                        $a = explode("action", $val);
                        $b = explode("(", $a[1]);
                        if ($b[0] != "Error") {
                            $allAction[] = $se[0] . '/' . $b[0]; // "/" . $b[0];
                        }
                    }
                }
            }
        }
        return $allAction;
    }

    public static function getAllControllerActionList()
    {
        $listFile = self::dir2array( __DIR__ . '/../../../modules/', false);
        $allAction = [];
        foreach ($listFile['folders'] as $item) {
            $allAction = array_merge($allAction, self::getControllerActionList(__DIR__ . '/../../../modules/' . $item . '/controllers/'));
        }
        return $allAction;
    }

    public static function getParentList($parentId)
    {
        return PermissionModel::search(['parent_id' => $parentId]);
    }
    
    //取文件列表放到数组里面
    public static function dir2array($dir, $subdirs = true)
    {
        $dirData = array();
        if (!@is_dir($dir)) {
            die("This directory does not exist ($dir)");
        }
        if (!$dirHandle = @opendir($dir)) {
            die("Unable to open directory ($dir)");
        }
        while ($file = @readdir($dirHandle)) {
            if (@filetype($dir . $file) !== '' && $file !== '.') {
                if (@filetype($dir . $file) == 'dir' && $file !== '..') {

                    $dirData['folders'][$file] = $file;
                    if ($subdirs) {
                        $dirFiles = self::dir2array($dir . '/' . $file . '/', true);
                        $dirData['folders'][$file] = $dirFiles;
                    }
                } else if ($file !== '..' && $file !== '.htaccess') {
                    if (substr($file, -14) == 'Controller.php')
                    $dirData['files'][$file] = $file;
                }
            }
        }
        return $dirData;
//        return self::arrayMulti2single($dirData);
    }

    public static function arrayMulti2single($array)
    {
        static $resultArray = array();
        foreach ($array as $value) {
            if (is_array($value)) {
                self::arrayMulti2single($value);
            }
            else if($value && !is_array($value) && !in_array($value, $resultArray)) {
                $resultArray[] = $value;
            }
        }
        return $resultArray;
    }
    
    public static function doPermission($permissionList, $permissionMemberList)
    {
        foreach ($permissionList as $key => $value) {
            if (in_array($value['pid'], $permissionMemberList)) {
                $permissionList[$key]['is_selected'] = true;;
            } else {
                $permissionList[$key]['is_selected'] = false;
                $test[] = $value['pid'];
            }
        }
        return $permissionList;
    }
    
}