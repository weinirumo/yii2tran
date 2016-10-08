<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ActionEvent represents the event parameter used for an action event.
 * 动作事件代表用于一个动作事件的事件参数
 *
 * By setting the [[isValid]] property, one may control whether to continue running the action.
 * 通过设置[[isValid]]属性，可以控制是否继续执行动作
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActionEvent extends Event
{
    /**
     * @var Action the action currently being executed
     * 属性 动作 当前被执行的动作
     */
    public $action;
    /**
     * @var mixed the action result. Event handlers may modify this property to change the action result.
     * 属性 混合类型 动作执行的结果。事件处理可以改变这个属性，进而改变动作的结果
     */
    public $result;
    /**
     * @var boolean whether to continue running the action. Event handlers of
     * [[Controller::EVENT_BEFORE_ACTION]] may set this property to decide whether
     * to continue running the current action.
     * 属性 boolean 是否继续执行动作。 [[Controller::EVENT_BEFORE_ACTION]]的事件处理可以设置这个属性，然后决定是否继续执行此动作
     */
    public $isValid = true;


    /**
     * Constructor.
     * 构造函数
     * @param Action $action the action associated with this action event.
     * 参数 动作 跟事件相关的动作
     * @param array $config name-value pairs that will be used to initialize the object properties
     * 参数 数组 初始化对象属性的使用使用的键值对
     */
    public function __construct($action, $config = [])
    {
        $this->action = $action;
        parent::__construct($config);
    }
}
