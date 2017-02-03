<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * UnknownPropertyException represents an exception caused by accessing unknown object properties.
 * UnknownPropertyException表示访问一个未知的对象属性而产生的异常。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class UnknownPropertyException extends Exception
{
    /**
     * @return string the user-friendly name of this exception
     * 返回值 字符串 对用户友好的异常信息。
     */
    public function getName()
    {
        return 'Unknown Property';
    }
}
