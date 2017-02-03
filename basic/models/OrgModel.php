<?php

namespace app\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "oa_org".
 *
 * @property integer $org_id
 * @property integer $company_id
 * @property string $org_name
 * @property integer $parent_org_id
 * @property integer $org_points
 * @property integer $org_all_points
 * @property string $all_children_id
 * @property string $all_parent_id
 */
class OrgModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_org';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'parent_org_id', 'org_points', 'org_all_points'], 'integer'],
            [['org_name'], 'required'],
            [['org_name'], 'string', 'max' => 50],
            [['all_children_id', 'all_parent_id'], 'string', 'max' => 500],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'org_id' => 'Org ID',
            'company_id' => 'Company ID',
            'org_name' => 'Org Name',
            'parent_org_id' => 'Parent Org ID',
            'org_points' => 'Org Points',
            'org_all_points' => 'Org All Points',
            'all_children_id' => 'All Children ID',
            'all_parent_id' => 'All Parent ID',
        ];
    }

    /**
     * 获取所有分组
     * @param array $whereArr
     * @return array
     */
    public static function getAllGroups($whereArr = array())
    {
        $where = '1=1';
        if(!empty($whereArr)){
            $where = implode('AND', $whereArr);
        }
        $sql = "select * from oa_org where $where order by parent_org_id";
        $list = Yii::$app->db->createCommand($sql)->queryAll();

        return $list;
    }

    /**
     * @param $orgName
     * @return array|\yii\db\ActiveRecord[]
     * 获取公司所有部门
     */
    public static function getComAllGroups($orgName)
    {
        return self::find()->andFilterWhere(['like','org_name',$orgName])->asArray()->all();
    }
    /**
     * 获取部门组织架构
     * @param $orgId
     * @return mixed
     */
    public static function getOrgGroup($orgId) 
    {
        $orgRes = self::find()->select('all_parent_id')->where(['org_id' => $orgId])->asArray()->one();
        $orgIds = explode(',', $orgRes['all_parent_id']);
        $orgName = self::find()->select('org_name')->where(['org_id' => $orgIds])->column();
        return implode('-', $orgName);
    }

    /**
     * 根据组织ID获取下一级组织
     * @param $orgId
     * @param array $fields
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getParentOrgList($orgId, $fields=array()) {
        return self::find()->select($fields)->where(['parent_org_id' => $orgId])->asArray()->all();
    }

    /**
     * 根据组织ID获取所有下属组织ID(不包括该组织本身)
     * @param $orgId
     * @param array $result
     * @return array
     */
    public static function getAllChildrenOrgId($orgId, $result = array()) 
    {
        $orgIdArr = self::find()->where(['parent_org_id' => $orgId])->column();
        if (empty($orgIdArr)) {
            return $result;
        } else {
            $result = array_merge((array)$orgIdArr, (array)$result);
            return self::getAllChildrenOrgId($orgIdArr,$result);
        }
    }

    /**
     * 获取所有子部门（包括自己）
     * $type  1返回数组否则返回字符串
    */
    public static function getAllChildOrgId($org_id,$type=1)
    {
        $orgInfo = OrgModel::find()->where(['org_id' => $org_id])->asArray()->one();
        if($type==1){
            $res = explode(',',$orgInfo['all_children_id']);
        }else{
            $res = $orgInfo['all_children_id'];
        }
        return $res;
    }

    /**
     * 获取父类组信息
     */
    public static function getAllParentOrgname($org_id) 
    {
        $org_res = (new Query())->select('all_parent_id')->from('oa_org b')->where(['org_id' => $org_id])->one();
        $org_ids = explode(',',$org_res['all_parent_id']);
        $org_name = (new Query())->select('org_name')->from('oa_org')->where(['org_id' => $org_ids])->column();
        if($org_id!=2){
            unset($org_name[0]);
        }
        return ['code' => 1,'data' => implode('-',$org_name)];
    }


    /**
     * 根据组织ID获取组织详情
     * @param $orgId
     * @param array $fields
     * @return array|null|\yii\db\ActiveRecord|\yii\db\ActiveRecord[]\
     */
    public static function getOrgInfo($orgId,$fields=array()) 
    {
        if(is_array($orgId)) {
            return self::find()->select($fields)->where(['org_id' => $orgId])->asArray()->all();
        }else {
            return self::find()->select($fields)->where(['org_id' => $orgId])->asArray()->one();
        }
    }
    /**
     * 获取所有组
     * @return object
     */
    public static function getOrgs($orgName)
    {
        $data = self::find()->select('org_id, org_name')->andFilterWhere(['like','org_name',$orgName])->asArray()->all();
        array_unshift($data, ['org_id'=>0, 'org_name'=>'全部']);//向数组前面插入元素
        return $data;
    }
    /*
     * 获取用户组织名称
     */
    public static function getOrgName($parent_org_ids)
    {
        return self::find()->select('org_name')->where(['org_id' => $parent_org_ids])->asArray()->all();
    }
    
    //获取所有上级组织
    public static function getAllParentOrg($org_id,$result = []) {
        $res = self::find()->where(['org_id' => $org_id])->asArray()->one();
        if($res['parent_org_id'] > 0) {
            $result[] = $res['parent_org_id'];
            return self::getAllParentOrg($res['parent_org_id'],$result);
        }else {
            return $result;
        }
    }

    //更新组织架构的子id
    public static function UpdateOrgChildrenId($orgId){
        //获取所有的子部门id
        $childrenIds = self::getAllChildrenOrgId($orgId);
        $localId = array($orgId);
        $ids = array_merge($childrenIds,$localId);
        $str = implode(',',$ids);
        return self::updateAll(['all_children_id'=>$str], ['org_id'=>$orgId]);
    }

    //更新组织架构的父id
    public static function UpdateOrgParentId($orgId){
        //获取所有的子部门id
        $parentIds =self::getAllParentOrg($orgId);
        $localId = array($orgId);
        $ids = array_merge($parentIds,$localId);
        $str = implode(',',$ids);
        self::updateAll(['all_parent_id' => $str],['org_id'=>$orgId]);

        //获取父id
        $parentId = self::find()->select('all_parent_id')->where(['org_id' => $orgId])->asArray()->one();
        //$parentId = Yii::$app->db->createCommand("select all_parent_id from oa_org where org_id = ".$orgId)->queryOne();
        $id =explode(',',$parentId['all_parent_id']);

        array_pop($id);
        if(!empty($id)){
            //更新父id下的子id
            $updateData = self::find()->select('org_id')->where(['org_id' => $id])->asArray()->all();
            //$updateData = Yii::$app->db->createCommand("select org_id from oa_org where org_id in (".implode(',',$id).")")->queryAll();
            foreach($updateData as $u){
                self::UpdateOrgChildrenId($u['org_id']);
            }
        }
    }

    /**
     * 更新用户信息
     * @param $orgId
     * @param $orgInfo
     * @return bool
     */
    public static function updateOrgInfo($orgId, $orgInfo)
    {
        $model = self::findOne($orgId);
        if (!$orgInfo) {
            return false;
        }
        if (isset($orgInfo['org_points'])) {
            $model->org_points = $model->org_points + $orgInfo['org_points'];
        }
        if (isset($orgInfo['org_all_points'])) {
            $model->org_all_points = $model->org_all_points + $orgInfo['org_all_points'];
        }
        if (isset($orgInfo['org_name'])) {
            $model->org_name = $orgInfo['org_name'];
        }
        if (isset($orgInfo['all_children_id'])) {
            $model->all_children_id = $orgInfo['all_children_id'];
        }
        return $model->save();
    }

    public static function getParentGroup($orgId){
        $parentData=OrgModel::find()->where("org_id=$orgId")->one();
        $name=$parentData['org_name'];
        if($parentData['parent_org_id']!=0){
            $name1=self::getParentGroup($parentData['parent_org_id']);
            $name = $name1.'-'.$name;
        }
        return $name;
    }

    /**
     * 根据关键字查找部门
    */
    public static function getOrgInfoList($keyword='',$field='oa_org.org_id,oa_org.org_name', $type=1, $page=0, $pageSize=100)
    {
        $oM = OrgModel::find()->select($field);
        $oM->where('1');
        if(strlen($keyword)>0){
            $oM->andWhere(['like','org_name',$keyword]);
        }
        if(2 == $type){
            $oM->andWhere(["!=",'org_id',2]);
        }
        if ($page) {
            $oM->offset(($page-1) * $pageSize)->limit($pageSize);
        }
        $res = $oM->asArray()->all();
        return $res;
    }

    public static function getOrgInfoListCount($keyword='', $type=1)
    {
        $oM = OrgModel::find();
        if(strlen($keyword)>0){
            $oM->where(['like','org_name',$keyword]);
            if(2 == $type){
                $oM->andWhere(["!=",'org_id',2]);
            }
        }else{
            if(2 == $type){
                $oM->where(["!=",'org_id',2]);
            }
        }
        $res = $oM->count();
        return $res;
    }
    
    public static function getOrgNameStr($orgId)
    {
        $parent_org_ids = OrgModel::getAllParentOrg($orgId);
        $result = OrgModel::find()->select(['org_name'])->where(['org_id' => $parent_org_ids])->asArray()->all();
        $tmpStr = '';
        if (isset($result[0])) {
            unset($result[0]);
        }
        foreach($result as $k => $v) {
            $tmpStr .= $v['org_name'] .'-';
        }
        return $tmpStr;
    }


    public static function updateLeavePoints($orgId,$uId)
    {
        $orgPoints = self::find()->select('org_points')->where(['org_id'=>$orgId])->asArray()->one()['org_points'];
        MembersModel::updateAll(['leave_points' => $orgPoints],['u_id'=>$uId]);
    }
}
