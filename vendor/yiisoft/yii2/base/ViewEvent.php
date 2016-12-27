<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ViewEvent represents events triggered by the [[View]] component.
 * ViewEvent表示[[View]]组件触发的事件。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ViewEvent extends Event
{
    /**
     * @var string the view file being rendered.
     * 变量 字符串 被渲染的视图文件。
     */
    public $viewFile;
    /**
     * @var array the parameter array passed to the [[View::render()]] method.
     * 变量 数组 传递给[[View::render()]]方法的参数数组
     */
    public $params;
    /**
     * @var string the rendering result of [[View::renderFile()]].
     * 变量 字符串 [[View::renderFile()]]的渲染结果。
     *
     * Event handlers may modify this property and the modified output will be
     * returned by [[View::renderFile()]]. This property is only used
     * by [[View::EVENT_AFTER_RENDER]] event.
     * 事件处理程序可以改变该属性，已经更改过的输出可以通过[[View::renderFile()]]返回。该属性只在[[View::EVENT_AFTER_RENDER]]事件中使用。
     */
    public $output;
    /**
     * @var boolean whether to continue rendering the view file. Event handlers of
     * [[View::EVENT_BEFORE_RENDER]] may set this property to decide whether
     * to continue rendering the current view file.
     * 变量 boolean 是否继续渲染视图文件。事件处理程序[[View::EVENT_BEFORE_RENDER]]可以设置该属性并决定是否继续渲染当前视图文件。
     */
    public $isValid = true;
}
