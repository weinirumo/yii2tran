<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;

/**
 * InlineAction represents an action that is defined as a controller method.
 * InlineAction代表被定义为控制器方法的一个动作。
 *
 * The name of the controller method is available via [[actionMethod]] which
 * is set by the [[controller]] who creates this action.
 * 控制器的方法名通过创建该动作的控制器设置，并通过actionMethod访问
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class InlineAction extends Action
{
    /**
     * @var string the controller method that this inline action is associated with
     * 属性 字符串 跟该内联动作相关的控制器方法
     */
    public $actionMethod;


    /**
     * @param string $id the ID of this action
     * 参数 字符串 动作的id
     *
     * @param Controller $controller the controller that owns this action
     * 参数 控制器 拥有该方法的控制器
     *
     * @param string $actionMethod the controller method that this inline action is associated with
     * 参数 字符串 跟内联动作相关的控制器方法
     *
     * @param array $config name-value pairs that will be used to initialize the object properties
     * 参数 设置 初始化对象属性的时候需要用到的键值对
     */
    public function __construct($id, $controller, $actionMethod, $config = [])
    {
        $this->actionMethod = $actionMethod;
        parent::__construct($id, $controller, $config);
    }

    /**
     * Runs this action with the specified parameters.
     * 使用指定参数运行该动作
     *
     * This method is mainly invoked by the controller.
     * 该方法主要被控制器调用
     *
     * @param array $params action parameters
     * 参数 数组 动作的参数
     *
     * @return mixed the result of the action
     * 返回值 混合型 动作执行的结果
     */
    public function runWithParams($params)
    {
        $args = $this->controller->bindActionParams($this, $params);
        Yii::trace('Running action: ' . get_class($this->controller) . '::' . $this->actionMethod . '()', __METHOD__);
        if (Yii::$app->requestedParams === null) {
            Yii::$app->requestedParams = $args;
        }

        return call_user_func_array([$this->controller, $this->actionMethod], $args);
    }
}
