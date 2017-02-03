<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * UnknownClassException represents an exception caused by using an unknown class.
 * UnknownClassException表示因使用一个未知的类引发的异常。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class UnknownClassException extends Exception
{
    /**
     * @return string the user-friendly name of this exception
     * 返回值 字符串 对用户友好的异常信息。
     */
    public function getName()
    {
        return 'Unknown Class';
    }
}
