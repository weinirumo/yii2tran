<?php
/**
 * Yii bootstrap file.
 * Yii引导文件
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

require(__DIR__ . '/BaseYii.php');

/**
 * Yii is a helper class serving common framework functionalities.
 * Yii是一个助手类，提供框架的公共功能。
 *
 * It extends from [[\yii\BaseYii]] which provides the actual implementation.
 * 它继承了[[\yii\BaseYii]]，BaseYii提供了确切的实现。
 * By writing your own Yii class, you can customize some functionalities of [[\yii\BaseYii]].
 * 通一些你自己的Yii类，您可以自定义[[\yii\BaseYii]]的一些功能。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Yii extends \yii\BaseYii
{
}

spl_autoload_register(['Yii', 'autoload'], true, true);
Yii::$classMap = require(__DIR__ . '/classes.php');
Yii::$container = new yii\di\Container();
