<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HelloController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     */
    public function actionIndex($message = 'hello world')
    {
        echo $message . "\n";
    }

    public function actionEmailTest() {
        $mail = Yii::$app->mailer->compose();
//        $subject = mb_substr($subject, 0, 14);
        $mail->setFrom(['hnoa@supernano.com' => 'admin'])
            ->setTo('nielixin@supernano.com')
            ->setSubject('testtest')
            ->setHtmlBody('testtest');
        $res = $mail->send();
        var_dump($res);
    }
}
