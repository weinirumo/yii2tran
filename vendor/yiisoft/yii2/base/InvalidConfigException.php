<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * InvalidConfigException represents an exception caused by incorrect object configuration.
 * InvalidConfigException代表不正确的对象配置产生的异常
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class InvalidConfigException extends Exception
{
    /**
     * @return string the user-friendly name of this exception
     * 返回值 字符串 用户友好的异常名称
     */
    public function getName()
    {
        return 'Invalid Configuration';
    }
}
