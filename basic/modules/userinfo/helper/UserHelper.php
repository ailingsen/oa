<?php

namespace app\modules\userinfo\helper;

use app\lib\FResponse;
use app\models\MembersModel;
use app\models\OrgMemberModel;
use app\models\SkillLevelModel;
use yii\imagine\Image;
use Yii;
use yii\web\UploadedFile;

class UserHelper
{
    /**
     * 上传用户头像，uid作为图片名字，放在static/head-img/uploads/下
     * @param $userInfo
     */
    public static function uploadUserHead($userInfo)
    {
        $data = json_decode(file_get_contents("php://input"));
        $img = str_replace('data:image/png;base64,', '', $data->data);
        $img = str_replace(' ', '+', $img);
        $data = base64_decode($img);
        $time = time();
        //$filename = '/static/head-img/uploads/' . $userInfo['u_id']. '.jpg';
        $filename = '/static/head-img/uploads/' . $userInfo['u_id'].'_'.$time. '.jpg';
        //$file = WEB_ROOT . '/static/head-img/uploads/' . $userInfo['u_id']. '.jpg';
        $file = WEB_ROOT . '/static/head-img/uploads/' . $userInfo['u_id'].'_'.$time . '.jpg';
        $success = file_put_contents($file, $data);

        $member = MembersModel::findOne($userInfo['u_id']);
        //$member->head_img = $userInfo['u_id'];
        $old_file_name = $member->head_img;
        $member->head_img = $userInfo['u_id'].'_'.$time;

        $member->save(false);
        if ($success && $member->save(false)) {
            //删除旧头像
            if(strlen($old_file_name)>0){
                if(file_exists(WEB_ROOT .'/static/head-img/uploads/' . $old_file_name. '.jpg')){
                    unlink(WEB_ROOT .'/static/head-img/uploads/' . $old_file_name. '.jpg');
                }
            }
            FResponse::output(['code' => 0, 'msg' => 'ok', 'data' => Yii::getAlias('static/head-img/uploads/' . $userInfo['u_id'] .'_'.$time. '.jpg')]);
        } else {
            FResponse::output(['code' => -1, 'msg' => 'upload failed']);
        }

    }

    public static function doSkill($skillList)
    {
        if (!is_array($skillList)) {
            return;
        }
        $levelConf = SkillLevelModel::find()->orderBy(['point' => SORT_ASC])->asArray()->all();
        $count = count($levelConf);
        foreach($skillList as $key => $val){
            foreach($levelConf as $k => $v){
                if($k == $count - 1){
                    if($val['point'] >= $v['point']){
                        $skillList[$key]['level'] = $k + 1;
                        $skillList[$key]['title'] = $v['title'];
                        $skillList[$key]['left_point'] = 0;
                        $skillList[$key]['nextlevel'] = $v['level'];
                        $skillList[$key]['nexttitle'] = $v['title'];
                        $skillList[$key]['nextpoint'] = $v['point'];
                        break;
                    }
                } else {
                    if ($val['point'] >= $v['point'] && $val['point'] < $levelConf[$k + 1]['point']) {
                        $skillList[$key]['level'] =  $k + 1;;
                        $skillList[$key]['title'] = $v['title'];
                        $skillList[$key]['left_point'] = $levelConf[$k + 1]['point'] - $val['point'];
                        $skillList[$key]['nextlevel'] = $k + 2;
                        $skillList[$key]['nexttitle'] = $levelConf[$k + 1]['title'];
                        $skillList[$key]['nextpoint'] = $levelConf[$k + 1]['point'];
                        break;
                    }
                }
            }
        }
        return $skillList;
    }

    /**
     * 是否orgId的管理员
     * @param $uid
     * @param $orgId
     * @return bool
     */
    public static function isManager($uid, $orgId)
    {
        $orgMember = OrgMemberModel::findOne(['u_id' => $uid]);
        if ($orgId == $orgMember->org_id && 1 == $orgMember->is_manager) {
            return true;
        }
        return false;
    }

}