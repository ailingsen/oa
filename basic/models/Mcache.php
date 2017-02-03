<?php

namespace app\models;

use Yii;

class Mcache extends \yii\db\ActiveRecord
{
    //���û���
    public static function setCache($key,$value,$time=99999999)
    {
        Yii::$app->memCache->set($key,$value,$time);
    }

    //��ȡ����
    public static function getCache($key)
    {
        return  Yii::$app->memCache->get($key);
    }

    //ɾ���
    public static function deleteCache($key)
    {
        return  Yii::$app->memCache->delete($key);
    }

    //����
    public static function flushCache()
    {
        return  Yii::$app->memCache->flush();
    }


}
