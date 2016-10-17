<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * InvalidValueException represents an exception caused by a function returning a value of unexpected type.
 * InvalidValueException代表函数返回未知类型的值产生的异常
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class InvalidValueException extends \UnexpectedValueException
{
    /**
     * @return string the user-friendly name of this exception
     * 返回值 字符串 对用户友好的异常名
     */
    public function getName()
    {
        return 'Invalid Return Value';
    }
}
