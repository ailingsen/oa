<?php
/**
 * Created by PhpStorm.
 * User: liaoshuochao
 * Date: 2016/7/13
 * Time: 14:55
 */
namespace app\lib;

use yii\base\Behavior;

/**
 * Attach events for class.
 *
 * @author liaoshuochao
 * @since 1.0
 */
class EventBehavior extends Behavior
{
    /**
     * @var array eventKey => callable
     */
    public $events;

    public function events()
    {
        return $this->events;
    }
}