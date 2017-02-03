<?php
namespace lib\errors;

use yii\base\Model;

/**
 * ValidateException represents errors of validation.
 *
 * @author liaoshuochao
 * @since 1.0
 */
class ValidateException extends BaseException
{
    /**
     * @var array
     */
    private $_errors = [];

    /**
     * @var \yii\base\Model
     */
    private $_model;

    /**
     * @inheritdoc
     */
    public function __construct(Model $model, $message, $code, \Exception $previous = null)
    {
        $this->_model = $model;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        if (empty($this->_errors)) {
            foreach ($this->_model->getFirstErrors() as $name => $message) {
                $this->_errors[] = [
                    'field' => $name,
                    'message' => $message,
                ];
            }
        }

        return $this->_errors;
    }
}
