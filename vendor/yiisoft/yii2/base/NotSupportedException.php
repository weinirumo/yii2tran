<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * NotSupportedException represents an exception caused by accessing features that are not supported.
 * NotSupportedException表示一个访问不支持的特性而导致的异常。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class NotSupportedException extends Exception
{
    /**
     * @return string the user-friendly name of this exception
     * 返回值 字符串 对用户友好的异常名
     */
    public function getName()
    {
        return 'Not Supported';
    }
}
