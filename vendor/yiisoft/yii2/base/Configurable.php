<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * Configurable is the interface that should be implemented by classes who support configuring
 * its properties through the last parameter to its constructor.
 * Configurable是一个接口，实现该接口的类都需要支持通过构造函数的最后一个参数配置其属性（最后一个参数是配置数组）
 *
 * The interface does not declare any method. Classes implementing this interface must declare their constructors
 * like the following:
 * 该接口没有声明任何方法。实现该接口的类必须按照下面的格式声明构造函数：
 *
 * ```php
 * public function __constructor($param1, $param2, ..., $config = [])
 * ```
 *
 * That is, the last parameter of the constructor must accept a configuration array.
 * 也就是说，构造函数的最后一个参数必须传递一个数组
 *
 * This interface is mainly used by [[\yii\di\Container]] so that it can pass object configuration as the
 * last parameter to the implementing class' constructor.
 * 该接口主要是[[\yii\di\Container]]使用，所以它可以传递最后一个参数作为对象配置给类的构造函数。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0.3
 */
interface Configurable
{
}
