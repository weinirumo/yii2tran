<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ModelEvent represents the parameter needed by [[Model]] events.
 * ModelEvent代表Model事件需要的参数
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ModelEvent extends Event
{
    /**
     * @var boolean whether the model is in valid status. Defaults to true.
     * 参数 boolean 该模型是否处于合法状态，默认是true
     * A model is in valid status if it passes validations or certain checks.
     * 当模型通过验证或某些检测以后，它就处于合法状态
     */
    public $isValid = true;
}
