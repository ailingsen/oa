<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "oa_notice".
 *
 * @property integer $notice_id
 * @property integer $u_id
 * @property string $title
 * @property string $content
 * @property integer $create_time
 * @property integer $is_del
 */
class NoticeModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oa_notice';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['u_id', 'title', 'content'], 'required'],
            [['u_id', 'create_time', 'is_del'], 'integer'],
            [['title', 'content'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'notice_id' => 'Notice ID',
            'u_id' => 'U ID',
            'title' => 'Title',
            'content' => 'Content',
            'create_time' => 'Create Time',
            'is_del' => 'Is Del',
        ];
    }

    public function getAtt()
    {
        // 第一个参数为要关联的子表模型类名，
        // 第二个参数指定 通过子表的customer_id，关联主表的id字段
        return $this->hasMany(NoticeAttachmentModel::className(), ['notice_id' => 'notice_id']);
    }

}
