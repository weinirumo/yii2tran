<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ViewRenderer is the base class for view renderer classes.
 * ViewRenderer是视图渲染器类的基类
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class ViewRenderer extends Component
{
    /**
     * Renders a view file.
     * 渲染一个视图文件。
     *
     * This method is invoked by [[View]] whenever it tries to render a view.
     * Child classes must implement this method to render the given view file.
     * 当[[View]]尝试渲染视图的时候调用此方法。子类必须实现该方法才能渲染给定的视图文件。
     *
     * @param View $view the view object used for rendering the file.
     * 参数 用来渲染文件的视图对象
     *
     * @param string $file the view file.
     * 参数 字符串 视图文件
     *
     * @param array $params the parameters to be passed to the view file.
     * 参数 数组 传递到视图的参数
     *
     * @return string the rendering result
     * 返回值 字符串 渲染的结果
     */
    abstract public function render($view, $file, $params);
}
