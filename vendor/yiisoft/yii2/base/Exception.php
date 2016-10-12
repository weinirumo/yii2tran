<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * Exception represents a generic exception for all purposes.
 * Exception(异常)代表所有目的的通用异常。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Exception extends \Exception
{
    /**
     * @return string the user-friendly name of this exception
     * 返回值 字符串 对用户友好的异常名称
     */
    public function getName()
    {
        return 'Exception';
    }
}
