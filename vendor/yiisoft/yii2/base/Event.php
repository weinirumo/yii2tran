<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * Event is the base class for all event classes.
 * Event是所有事件类的基类
 *
 * It encapsulates the parameters associated with an event.
 * 它封装了跟事件相关的参数
 *
 * The [[sender]] property describes who raises the event.
 * sender属性描述了谁引发了该事件
 *
 * And the [[handled]] property indicates if the event is handled.
 * handled属性表示该事件是否被处理
 *
 * If an event handler sets [[handled]] to be `true`, the rest of the
 * uninvoked handlers will no longer be called to handle the event.
 * 如果事件处理设置handled为true，那么剩下为调用的处理程序将不会被调用来处理事件
 *
 * Additionally, when attaching an event handler, extra data may be passed
 * and be available via the [[data]] property when the event handler is invoked.
 * 此外，当添加事件处理程序的时候，额外的数据可以在事件处理程序被调用后通过data属性传递。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Event extends Object
{
    /**
     * @var string the event name. This property is set by [[Component::trigger()]] and [[trigger()]].
     * 属性 字符串 事件名 该属性被Component::trigger()和trigger()方法设置
     *
     * Event handlers may use this property to check what event it is handling.
     * 事件处理程序可以使用该属性确定当前正在处理的事件是哪个。
     */
    public $name;
    /**
     * @var object the sender of this event. If not set, this property will be
     * set as the object whose `trigger()` method is called.
     * 属性 对象 该事件的发送者，如果没设置，该属性会被设置为调用trigger方法的对象
     *
     * This property may also be a `null` when this event is a
     * class-level event which is triggered in a static context.
     * 当事件是类级别的的事件并且被静态环境触发，该属性的值可以是null
     */
    public $sender;
    /**
     * @var boolean whether the event is handled. Defaults to `false`.
     * 属性 boolean 事件是否被处理。默认是false
     *
     * When a handler sets this to be `true`, the event processing will stop and
     * ignore the rest of the uninvoked event handlers.
     * 当事件处理把该属性更改为true的时候，事件处理进程会停止，并忽略未被调用的事件处理程序
     */
    public $handled = false;
    /**
     * @var mixed the data that is passed to [[Component::on()]] when attaching an event handler.
     * 属性 混合型 当添加事件处理程序时，传递给的Component::on()的数据
     *
     * Note that this varies according to which event handler is currently executing.
     * 注意，该值会因为当前正在执行的事件处理程序而有所不同
     */
    public $data;

    /**
     * @var array contains all globally registered event handlers.
     * 属性 数组 包含所有全局注册的事件处理程序
     */
    private static $_events = [];


    /**
     * Attaches an event handler to a class-level event.
     * 把事件处理程序绑定为一个类级别的事件
     *
     * When a class-level event is triggered, event handlers attached
     * to that class and all parent classes will be invoked.
     * 当类级别的事件触发的时候，事件处理添加到的类及其父类都会被调用
     *
     * For example, the following code attaches an event handler to `ActiveRecord`'s
     * `afterInsert` event:
     * 例如，下面的代码把一个事件处理程序添加到了ActiveRecord的afterInsert事件：
     *
     * ```php
     * Event::on(ActiveRecord::className(), ActiveRecord::EVENT_AFTER_INSERT, function ($event) {
     *     Yii::trace(get_class($event->sender) . ' is inserted.');
     * });
     * ```
     *
     * The handler will be invoked for EVERY successful ActiveRecord insertion.
     * 该处理程序会被每一个成功执行的ActiveRecord插入操作调用
     *
     * For more details about how to declare an event handler, please refer to [[Component::on()]].
     * 更多关于如何声明事件处理程序的方法，请参考Component::on()方法
     *
     * @param string $class the fully qualified class name to which the event handler needs to attach.
     * 参数 字符串 需要添加事件处理程序的完全限定类名
     *
     * @param string $name the event name.
     * 参数 字符串 事件名
     *
     * @param callable $handler the event handler.
     * 参数 事件处理程序
     *
     * @param mixed $data the data to be passed to the event handler when the event is triggered.
     * When the event handler is invoked, this data can be accessed via [[Event::data]].
     * 参数 混合型 当事件触发时传递给事件处理程序的数据。当事件处理程序被调用时，该数据可以通过Event::data进行访问
     *
     * @param boolean $append whether to append new event handler to the end of the existing
     * handler list. If `false`, the new handler will be inserted at the beginning of the existing
     * handler list.
     * 参数 boolean 是否添加新的事件处理程序到已经存在的事件处理程序列表的最后。如果为false，新的事件处理程序会被添加到事件处理
     * 程序列表的开头
     *
     * @see off()
     */
    public static function on($class, $name, $handler, $data = null, $append = true)
    {
        $class = ltrim($class, '\\');
        if ($append || empty(self::$_events[$name][$class])) {
            self::$_events[$name][$class][] = [$handler, $data];
        } else {
            array_unshift(self::$_events[$name][$class], [$handler, $data]);
        }
    }

    /**
     * Detaches an event handler from a class-level event.
     * 删除一个类级别的事件处理程序
     *
     * This method is the opposite of [[on()]].
     * 该方法的作用跟on相反
     *
     * @param string $class the fully qualified class name from which the event handler needs to be detached.
     * 参数 字符串 需要删除事件处理程序的完全限定类名
     *
     * @param string $name the event name.
     * 参数 字符串 事件名
     *
     * @param callable $handler the event handler to be removed.
     * If it is `null`, all handlers attached to the named event will be removed.
     * 参数 被删除的事件处理程序，如果为null，所有的事件处理程序都会被删除
     *
     * @return boolean whether a handler is found and detached.
     * 返回值 boolean 事件处理程序是否被找到并删除。
     * @see on()
     */
    public static function off($class, $name, $handler = null)
    {
        $class = ltrim($class, '\\');
        if (empty(self::$_events[$name][$class])) {
            return false;
        }
        if ($handler === null) {
            unset(self::$_events[$name][$class]);
            return true;
        } else {
            $removed = false;
            foreach (self::$_events[$name][$class] as $i => $event) {
                if ($event[0] === $handler) {
                    unset(self::$_events[$name][$class][$i]);
                    $removed = true;
                }
            }
            if ($removed) {
                self::$_events[$name][$class] = array_values(self::$_events[$name][$class]);
            }

            return $removed;
        }
    }

    /**
     * Detaches all registered class-level event handlers.
     * 删除所有注册的类级别的事件处理程序
     * @see on()
     * @see off()
     * @since 2.0.10
     */
    public static function offAll()
    {
        self::$_events = [];
    }

    /**
     * Returns a value indicating whether there is any handler attached to the specified class-level event.
     * 返回表示是否有绑定到指定类级别的事件处理程序的值
     *
     * Note that this method will also check all parent classes to see if there is any handler attached
     * to the named event.
     * 注意，该方法会检测所有的父类，确定是否有事件处理程序绑定到了给定的事件
     *
     * @param string|object $class the object or the fully qualified class name specifying the class-level event.
     * 参数 字符串|对象 对象或致命类级别事件的完全限定的类名
     *
     * @param string $name the event name.
     * 参数 字符串 事件名
     *
     * @return boolean whether there is any handler attached to the event.
     * 返回值 boolean 是否有事件处理程序绑定到了该事件
     */
    public static function hasHandlers($class, $name)
    {
        if (empty(self::$_events[$name])) {
            return false;
        }
        if (is_object($class)) {
            $class = get_class($class);
        } else {
            $class = ltrim($class, '\\');
        }

        $classes = array_merge(
            [$class],
            class_parents($class, true),
            class_implements($class, true)
        );

        foreach ($classes as $class) {
            if (!empty(self::$_events[$name][$class])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Triggers a class-level event.
     * 触发一个类级别的事件
     *
     * This method will cause invocation of event handlers that are attached to the named event
     * for the specified class and all its parent classes.
     * 该方法会导致调用指定类名及其父类的，给定事件名的事件处理程序
     *
     * @param string|object $class the object or the fully qualified class name specifying the class-level event.
     * 参数 字符串|对象
     *
     * @param string $name the event name.
     * 参数 字符串 事件名
     *
     * @param Event $event the event parameter. If not set, a default [[Event]] object will be created.
     * 参数 事件 事件参数 ，如果不设置，默认的Event对象将会被创建
     */
    public static function trigger($class, $name, $event = null)
    {
        if (empty(self::$_events[$name])) {
            return;
        }
        if ($event === null) {
            $event = new static;
        }
        $event->handled = false;
        $event->name = $name;

        if (is_object($class)) {
            if ($event->sender === null) {
                $event->sender = $class;
            }
            $class = get_class($class);
        } else {
            $class = ltrim($class, '\\');
        }

        $classes = array_merge(
            [$class],
            class_parents($class, true),
            class_implements($class, true)
        );

        foreach ($classes as $class) {
            if (!empty(self::$_events[$name][$class])) {
                foreach (self::$_events[$name][$class] as $handler) {
                    $event->data = $handler[1];
                    call_user_func($handler[0], $event);
                    if ($event->handled) {
                        return;
                    }
                }
            }
        }
    }
}
