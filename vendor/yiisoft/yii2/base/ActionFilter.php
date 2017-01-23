<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ActionFilter is the base class for action filters.
 * ActionFilter类是动作过滤器的基类
 *
 * An action filter will participate in the action execution workflow by responding to
 * the `beforeAction` and `afterAction` events triggered by modules and controllers.
 * 当beforeAction和afterAction事件被模块或者控制器触发时，动作过滤器会加入其中
 *
 * Check implementation of [[\yii\filters\AccessControl]], [[\yii\filters\PageCache]] and [[\yii\filters\HttpCache]] as examples on how to use it.
 * 如何使用，请参考这三个类实现的方法
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActionFilter extends Behavior
{
    /**
     * @var array list of action IDs that this filter should apply to. If this property is not set,
     * then the filter applies to all actions, unless they are listed in [[except]].
     * 属性 数组 该过滤器需要用到的动作id集合，如果这个属性没有设置，那么过滤器适用于所有的动作，除非有明确的except指定，将其排除在外
     *
     * If an action ID appears in both [[only]] and [[except]], this filter will NOT apply to it.
     * 如果一个动作id同时出现在了only配置和except配置中，那么该过滤器就会无法生效
     *
     * Note that if the filter is attached to a module, the action IDs should also include child module IDs (if any)
     * and controller IDs.
     * 请注意，如果过滤器被绑定到了一个模块，那么动作id也应该包含子类的模块id（如果存在）和控制器id
     *
     * Since version 2.0.9 action IDs can be specified as wildcards, e.g. `site/*`.
     * 从版本2.0.9以后，动作id可以使用通配符指定，例如`site/*`
     *
     * @see except
     */
    public $only;
    /**
     * @var array list of action IDs that this filter should not apply to.
     * 属性 数组 该过滤器不运用到的动作id集合
     * @see only
     */
    public $except = [];


    /**
     * @inheritdoc
     */
    public function attach($owner)
    {
        $this->owner = $owner;
        $owner->on(Controller::EVENT_BEFORE_ACTION, [$this, 'beforeFilter']);
    }

    /**
     * @inheritdoc
     */
    public function detach()
    {
        if ($this->owner) {
            $this->owner->off(Controller::EVENT_BEFORE_ACTION, [$this, 'beforeFilter']);
            $this->owner->off(Controller::EVENT_AFTER_ACTION, [$this, 'afterFilter']);
            $this->owner = null;
        }
    }

    /**
     * @param ActionEvent $event
     */
    public function beforeFilter($event)
    {
        if (!$this->isActive($event->action)) {
            return;
        }

        $event->isValid = $this->beforeAction($event->action);
        if ($event->isValid) {
            // call afterFilter only if beforeFilter succeeds
            // 只有beforeFilter方法调用成功才会调用afterFilter
            // beforeFilter and afterFilter should be properly nested
            // 注意合理的采用beforeFilter和afterFilter嵌套
            $this->owner->on(Controller::EVENT_AFTER_ACTION, [$this, 'afterFilter'], null, false);
        } else {
            $event->handled = true;
        }
    }

    /**
     * @param ActionEvent $event
     */
    public function afterFilter($event)
    {
        $event->result = $this->afterAction($event->action, $event->result);
        $this->owner->off(Controller::EVENT_AFTER_ACTION, [$this, 'afterFilter']);
    }

    /**
     * This method is invoked right before an action is to be executed (after all possible filters.)
     * 该方法会在所有可能的过滤器执行结束以后，动作执行以前被调用
     *
     * You may override this method to do last-minute preparation for the action.
     * 你可以重写此方法，为动作执行前执行最后的准备
     *
     * @param Action $action the action to be executed.
     * 参数 动作 将要执行的动作
     *
     * @return boolean whether the action should continue to be executed.
     * 返回值 boolean 动作是否继续执行
     */
    public function beforeAction($action)
    {
        return true;
    }

    /**
     * This method is invoked right after an action is executed.
     * 该方法会在一个动作执行结束以后调用
     *
     * You may override this method to do some postprocessing for the action.
     * 你可以重写此方法，给动作完成一些后续处理工作
     *
     * @param Action $action the action just executed.
     * 参数 动作 刚执行过的动作
     *
     * @param mixed $result the action execution result
     * 参数 混合类型 动作执行的结果
     *
     * @return mixed the processed action result.
     * 返回值 混合类型 处理过的动作结果
     */
    public function afterAction($action, $result)
    {
        return $result;
    }

    /**
     * Returns an action ID by converting [[Action::$uniqueId]] into an ID relative to the module
     * 把动作的唯一id转化成一个跟模块相关的id，并返回一个动作id
     *
     * @param Action $action
     * @return string
     * @since 2.0.7
     */
    protected function getActionId($action)
    {
        if ($this->owner instanceof Module) {
            $mid = $this->owner->getUniqueId();
            $id = $action->getUniqueId();
            if ($mid !== '' && strpos($id, $mid) === 0) {
                $id = substr($id, strlen($mid) + 1);
            }
        } else {
            $id = $action->id;
        }

        return $id;
    }

    /**
     * Returns a value indicating whether the filter is active for the given action.
     * 返回表示过滤器是否对给动作生效的值
     *
     * @param Action $action the action being filtered
     * 参数 动作 将被过滤的动作
     *
     * @return boolean whether the filter is active for the given action.
     * 返回值 boolean 过滤器是否对给定的动作生效
     */
    protected function isActive($action)
    {
        $id = $this->getActionId($action);

        if (empty($this->only)) {
            $onlyMatch = true;
        } else {
            $onlyMatch = false;
            foreach ($this->only as $pattern) {
                if (fnmatch($pattern, $id)) {
                    $onlyMatch = true;
                    break;
                }
            }
        }

        $exceptMatch = false;
        foreach ($this->except as $pattern) {
            if (fnmatch($pattern, $id)) {
                $exceptMatch = true;
                break;
            }
        }

        return !$exceptMatch && $onlyMatch;
    }
}
