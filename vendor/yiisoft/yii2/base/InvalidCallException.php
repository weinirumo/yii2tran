<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * InvalidCallException represents an exception caused by calling a method in a wrong way.
 * InvalidCallException代表因为错误的调用方法产生的异常
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class InvalidCallException extends \BadMethodCallException
{
    /**
     * @return string the user-friendly name of this exception
     * 返回值 字符串 用户友好的异常名称
     */
    public function getName()
    {
        return 'Invalid Call';
    }
}
