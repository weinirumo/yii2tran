<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * InvalidRouteException represents an exception caused by an invalid route.
 * InvalidRouteException代表因为不合法的路由产生的异常
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class InvalidRouteException extends UserException
{
    /**
     * @return string the user-friendly name of this exception
     * 返回值 字符串 对用户友好的异常名称
     */
    public function getName()
    {
        return 'Invalid Route';
    }
}
