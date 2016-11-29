<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ViewContextInterface is the interface that should implemented by classes who want to support relative view names.
 * ViewContextInterface是所有要支持相对视图名的类应该实现的接口
 *
 * The method [[getViewPath()]] should be implemented to return the view path that may be prefixed to a relative view name.
 * 方法[[getViewPath()]]应该实现返回可能以相对视图名为前缀的视图路径
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
interface ViewContextInterface
{
    /**
     * @return string the view path that may be prefixed to a relative view name.
     * 返回值 字符串 视图路径加上相对视图米国
     */
    public function getViewPath();
}
