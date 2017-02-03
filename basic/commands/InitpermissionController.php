<?php

/**
 * Created by PhpStorm.
 * User: liaoshuochao
 * Date: 2016/05/24
 * Time: 17:35
 */

namespace app\commands;

use app\models\ApptabSetModel;
use app\models\MembersModel;
use app\models\PermissionGroupModel;
use app\models\PermissionModel;
use yii\console\Controller;
use Yii;
use dict;

class InitpermissionController extends Controller
{
    /**
     * 入口
     * @param $action
     */
    public function actionIndex()
    {
        //获取所有用户信息
        $memInfo = MembersModel::find()->where('is_del=0')->asArray()->all();
        //获取角色信息
        $roleInfo = PermissionGroupModel::find()->asArray()->all();
        foreach($memInfo as $key=>$val){
            $temp=0;
            foreach($roleInfo as $k=>$v){
                if($val['perm_groupid'] == $v['group_id']){
                    $arrUserPerm = [];
                    if($v['group_id']==1){//超级管理员
                        $arrPerm = PermissionModel::find()->select('pid')->where('is_use=1')->asArray()->all();
                        if(count($arrPerm)>0){
                                foreach($arrPerm as $permKey=>$permVal){
                                    $arrUserPerm[$permKey]['pid'] = $permVal['pid'];
                                    $arrUserPerm[$permKey]['u_id'] = $val['u_id'];
                                }
                        }
                    }else{
                        $arrPerm = json_decode($v['permission']);
                        if(count($arrPerm)>0){
                            foreach($arrPerm as $permKey=>$permVal){
                                $arrUserPerm[$permKey]['pid'] = $permVal;
                                $arrUserPerm[$permKey]['u_id'] = $val['u_id'];
                            }
                        }
                    }
                    if(count($arrUserPerm)>0){
                        Yii::$app->db->createCommand()->batchInsert('oa_permission_member',['pid','u_id'],$arrUserPerm)->execute();
                    }
                    $temp=1;
                    break;
                }
            }
            if($temp==0){
                foreach($roleInfo as $k=>$v){
                    if($v['group_id'] == 3){
                        $arrUserPerm = [];
                        $arrPerm = json_decode($v['permission']);
                        if(count($arrPerm)>0){
                            foreach($arrPerm as $permKey=>$permVal){
                                $arrUserPerm[$permKey]['pid'] = $permVal;
                                $arrUserPerm[$permKey]['u_id'] = $val['u_id'];
                            }
                        }
                        if(count($arrUserPerm)>0){
                            Yii::$app->db->createCommand()->batchInsert('oa_permission_member',['pid','u_id'],$arrUserPerm)->execute();
                        }
                        break;
                    }
                }
            }
        }

        //添加所有人移动端tab设置初始数据
        $arrMemInfo = MembersModel::find()->select('u_id')->asArray()->all();
        foreach($arrMemInfo as $key=>$val){
            $apptab = new ApptabSetModel();
            $apptab->u_id = $val['u_id'];
            $isApptab = $apptab->save(false);
        }

    }


}