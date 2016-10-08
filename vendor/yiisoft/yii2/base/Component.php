<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;

/**
 * Component is the base class that implements the *property*, *event* and *behavior* features.
 * Component是实现*property*, *event* 和*behavior*特性的基类。
 *
 * Component provides the *event* and *behavior* features, in addition to the *property* feature which is implemented in
 * its parent class [[Object]].
 * 除了父类[[Object]]实现的*property*属性，Component提供*event* and *behavior*特性。
 *
 * Event is a way to "inject" custom code into existing code at certain places. For example, a comment object can trigger
 * an "add" event when the user adds a comment. We can write custom code and attach it to this event so that when the event
 * is triggered (i.e. comment will be added), our custom code will be executed.
 * 事件是把自定义的代码注入到指定位置的一种方式。例如，当用户添加评论的时候，评论对象可以触发一个add事件。我们就可以把自定义的代码写入事件，当事件触发的时候
 * （例如添加评论的时候）我们自定义的代码就会执行。
 *
 * An event is identified by a name that should be unique within the class it is defined at. Event names are *case-sensitive*.
 * 在定义事件的类中，事件名是不能重复的，并且事件名大小写敏感
 *
 * One or multiple PHP callbacks, called *event handlers*, can be attached to an event. You can call [[trigger()]] to
 * raise an event. When an event is raised, the event handlers will be invoked automatically in the order they were
 * attached.
 * 一个或多个php回调，称作事件处理，可以附加给事件。你可以调用[[trigger()]]调用一个事件。当事件被调用的时候，事件处理会按照绑定的顺序执行。
 *
 * To attach an event handler to an event, call [[on()]]:
 * 为事件绑定事件处理程序，调用[[on()]]:
 *
 * ```php
 * $post->on('update', function ($event) {
 *     // send email notification
 * });
 * ```
 *
 * In the above, an anonymous function is attached to the "update" event of the post. You may attach
 * the following types of event handlers:
 * 在上边的例子中，一个匿名函数被绑定到了update事件上。你可以绑定如下的事件处理类型：
 *
 * - anonymous function: `function ($event) { ... }`
 * - 匿名函数 `function ($event) { ... }`
 * - object method: `[$object, 'handleAdd']`
 * - 对象方法： `[$object, 'handleAdd']`
 * - static class method: `['Page', 'handleAdd']`
 * - 类的静态方法： `['Page', 'handleAdd']`
 * - global function: `'handleAdd'`
 * - 全局函数： `'handleAdd'`
 *
 * The signature of an event handler should be like the following:
 * 事件处理应该有如下的特征：
 *
 * ```php
 * function foo($event)
 * ```
 *
 * where `$event` is an [[Event]] object which includes parameters associated with the event.
 * `$event`是一个包含跟事件相关参数的[[Event]]对象。
 *
 * You can also attach a handler to an event when configuring a component with a configuration array.
 * The syntax is like the following:
 * 你也可以通过组件的配置数组给事件绑定事件处理，语法如下：
 *
 * ```php
 * [
 *     'on add' => function ($event) { ... }
 * ]
 * ```
 *
 * where `on add` stands for attaching an event to the `add` event.
 * `on add`表示给事件add绑定事件处理程序。
 *
 * Sometimes, you may want to associate extra data with an event handler when you attach it to an event
 * and then access it when the handler is invoked. You may do so by
 * 有时，在绑定事件处理程序的时候，你也许想把额外的一些数据传递给事件处理程序，并在处理程序被调用的时候使用它。你可以这样做：
 *
 * ```php
 * $post->on('update', function ($event) {
 *     // the data can be accessed via $event->data
 * }, $data);
 * ```
 *
 * A behavior is an instance of [[Behavior]] or its child class. A component can be attached with one or multiple
 * behaviors. When a behavior is attached to a component, its public properties and methods can be accessed via the
 * component directly, as if the component owns those properties and methods.
 * 行为是[[Behavior]]类或其子类的实例。一个组件可以附加一个或多个行为。当行为被绑定到组件时，它公共的属性和方法可以被组件直接访问，就像组件拥有这些属性
 * 和方法一样
 *
 * To attach a behavior to a component, declare it in [[behaviors()]], or explicitly call [[attachBehavior]]. Behaviors
 * declared in [[behaviors()]] are automatically attached to the corresponding component.
 * 要给组件添加行为，需要在[[behaviors()]]中声明，或者显式调用[[attachBehavior]]。在[[behaviors()]]声明的行为会自动绑定到相应的组件。
 *
 * One can also attach a behavior to a component when configuring it with a configuration array. The syntax is like the
 * following:
 * 你也可以通过配置数组给组件添加行为，语法如下：
 *
 * ```php
 * [
 *     'as tree' => [
 *         'class' => 'Tree',
 *     ],
 * ]
 * ```
 *
 * where `as tree` stands for attaching a behavior named `tree`, and the array will be passed to [[\Yii::createObject()]]
 * to create the behavior object.
 * `as tree`代表给行为命名tree，这个数组会被传递给[[\Yii::createObject()]]，然后创建行为对象
 *
 * @property Behavior[] $behaviors List of behaviors attached to this component. This property is read-only.
 * 属性 绑定到该组件的行为列表，该属性只读
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Component extends Object
{
    /**
     * @var array the attached event handlers (event name => handlers)
     * 属性 数组 绑定的事件处理程序（事件名=>处理程序）
     */
    private $_events = [];
    /**
     * @var Behavior[]|null the attached behaviors (behavior name => behavior). This is `null` when not initialized.
     * 属性 行为或者null，绑定的动作（动作名=>行为），没有初始化的时候这个值是null
     */
    private $_behaviors;


    /**
     * Returns the value of a component property.
     * 返回组件的属性值。
     * This method will check in the following order and act accordingly:
     * 该方法将按照以下顺序检查并采取相应行动
     *
     *  - a property defined by a getter: return the getter result
     *  - 通过getter定义的属性：返回getter的结果
     *  - a property of a behavior: return the behavior property value
     *  - 行为的属性：返回行为的属性值
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `$value = $component->property;`.
     * 不要直接调用此方法，因为此方法是php的魔术方法，会在执行$value = $component->property;的时候自动调用
     * @param string $name the property name
     * 参数 字符串 属性名
     * @return mixed the property value or the value of a behavior's property
     * 返回值 混合类型 属性值或者行为的属性值
     * @throws UnknownPropertyException if the property is not defined
     * 抛出 未知的属性异常 当属性没有定义的时候
     * @throws InvalidCallException if the property is write-only.
     * 抛出 不能调用异常 如果该属性是只写的
     * @see __set()
     */
    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            // read property, e.g. getName()
            // 读取属性，例如getName()
            return $this->$getter();
        } else {
            // behavior property
            // 行为属性
            $this->ensureBehaviors();
            foreach ($this->_behaviors as $behavior) {
                if ($behavior->canGetProperty($name)) {
                    return $behavior->$name;
                }
            }
        }
        if (method_exists($this, 'set' . $name)) {
            throw new InvalidCallException('Getting write-only property: ' . get_class($this) . '::' . $name);
        } else {
            throw new UnknownPropertyException('Getting unknown property: ' . get_class($this) . '::' . $name);
        }
    }

    /**
     * Sets the value of a component property.
     * 设置组件的属性值
     * This method will check in the following order and act accordingly:
     * 该方法将按照以下顺序检查并采取相应行动
     *
     *  - a property defined by a setter: set the property value
     *  - 一个被setter设置的属性值：设置属性值
     *  - an event in the format of "on xyz": attach the handler to the event "xyz"
     *  - "on xyz"这样格式的事件：给事件xyz绑定事件处理程序
     *  - a behavior in the format of "as xyz": attach the behavior named as "xyz"
     *  - "as xyz"这样格式的行为：把行为命名为xyz
     *  - a property of a behavior: set the behavior property value
     *  - 行为的属性：设置行为的属性值
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `$component->property = $value;`.
     * 不要直接调用此方法，因为此方法是php的魔术方法，会在执行$component->property = $value;的时候自动调用
     * @param string $name the property name or the event name
     * 参数 字符串 属性或者事件的名称
     * @param mixed $value the property value
     * 参数 混合型 属性值
     * @throws UnknownPropertyException if the property is not defined
     * 抛出 位置属性的异常 当属性没有定义的时候
     * @throws InvalidCallException if the property is read-only.
     * 抛出 无法调用异常 当属性是只读的时候
     * @see __get()
     */
    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            // set property
            // 设置属性
            $this->$setter($value);

            return;
        } elseif (strncmp($name, 'on ', 3) === 0) {
            // on event: attach event handler
            // 事件：帮定事件处理程序
            $this->on(trim(substr($name, 3)), $value);

            return;
        } elseif (strncmp($name, 'as ', 3) === 0) {
            // as behavior: attach behavior
            // 行为：添加行为
            $name = trim(substr($name, 3));
            $this->attachBehavior($name, $value instanceof Behavior ? $value : Yii::createObject($value));

            return;
        } else {
            // behavior property
            // 行为属性
            $this->ensureBehaviors();
            foreach ($this->_behaviors as $behavior) {
                if ($behavior->canSetProperty($name)) {
                    $behavior->$name = $value;

                    return;
                }
            }
        }
        if (method_exists($this, 'get' . $name)) {
            throw new InvalidCallException('Setting read-only property: ' . get_class($this) . '::' . $name);
        } else {
            throw new UnknownPropertyException('Setting unknown property: ' . get_class($this) . '::' . $name);
        }
    }

    /**
     * Checks if a property is set, i.e. defined and not null.
     * 检测属性是否设置了，例如，定义过或者不为null
     * This method will check in the following order and act accordingly:
     * 该方法将按照以下顺序检查并采取相应行动：
     *
     *  - a property defined by a setter: return whether the property is set
     *  - setter定义的属性：返回属性是否被设置
     *  - a property of a behavior: return whether the property is set
     *  - 行为的属性：返回属性是否设置
     *  - return `false` for non existing properties
     *  - 不存在属性返回false
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `isset($component->property)`.
     * 不要直接调用此方法，因为此方法是php的魔术方法，会在执行isset($component->property)的时候自动调用
     * @param string $name the property name or the event name
     * 参数 字符串 属性名或者事件名
     * @return boolean whether the named property is set
     * 返回值 boolean 给定的属性是否被设置
     * @see http://php.net/manual/en/function.isset.php
     */
    public function __isset($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter() !== null;
        } else {
            // behavior property
            $this->ensureBehaviors();
            foreach ($this->_behaviors as $behavior) {
                if ($behavior->canGetProperty($name)) {
                    return $behavior->$name !== null;
                }
            }
        }
        return false;
    }

    /**
     * Sets a component property to be null.
     * 把一个组件的属性设置为null
     * This method will check in the following order and act accordingly:
     * 该方法将按照以下顺序检查并执行：
     *
     *  - a property defined by a setter: set the property value to be null
     *  - setter定义的属性：把属性值设置为mull
     *  - a property of a behavior: set the property value to be null
     *  - 行为的属性：把属性值设置为null
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `unset($component->property)`.
     * 不要直接调用此方法，因为此方法是php的魔术方法，会在执行unset($component->property)的时候自动调用
     * @param string $name the property name
     * 参数 字符串 属性名
     * @throws InvalidCallException if the property is read only.
     * 抛出 不可调用异常 当属性是只读的时候
     * @see http://php.net/manual/en/function.unset.php
     */
    public function __unset($name)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter(null);
            return;
        } else {
            // behavior property
            // 行为属性
            $this->ensureBehaviors();
            foreach ($this->_behaviors as $behavior) {
                if ($behavior->canSetProperty($name)) {
                    $behavior->$name = null;
                    return;
                }
            }
        }
        throw new InvalidCallException('Unsetting an unknown or read-only property: ' . get_class($this) . '::' . $name);
    }

    /**
     * Calls the named method which is not a class method.
     * 调用不存在的类方法
     *
     * This method will check if any attached behavior has
     * the named method and will execute it if available.
     * 该方法会检测行为方法是否存在，并在可用的情况下执行。
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when an unknown method is being invoked.
     * 不要直接调用此方法，因为此方法是php的魔术方法，会在执行unset($component->property)的时候自动调用
     * @param string $name the method name
     * 参数 字符串 方法的名字
     * @param array $params method parameters
     * 参数 数组 方法的参数
     * @return mixed the method return value
     * 返回值 混合型 方法的返回值
     * @throws UnknownMethodException when calling unknown method
     * 抛出 未知的方法异常 当调用未知的方法时
     */
    public function __call($name, $params)
    {
        $this->ensureBehaviors();
        foreach ($this->_behaviors as $object) {
            if ($object->hasMethod($name)) {
                return call_user_func_array([$object, $name], $params);
            }
        }
        throw new UnknownMethodException('Calling unknown method: ' . get_class($this) . "::$name()");
    }

    /**
     * This method is called after the object is created by cloning an existing one.
     * It removes all behaviors because they are attached to the old object.
     * 当克隆一个存在的对象时，该方法会自动调用。
     * 它会移除所有的行为，因为这些行为被绑定到了原有的对象上。
     */
    public function __clone()
    {
        $this->_events = [];
        $this->_behaviors = null;
    }

    /**
     * Returns a value indicating whether a property is defined for this component.
     * 返回代表该组件是否定义了一个属性
     * A property is defined if:
     * 如下情况，代表属性被定义：
     *
     * - the class has a getter or setter method associated with the specified name
     *   (in this case, property name is case-insensitive);
     * - 类有有一个指定名称的getter或者setter方法（这种情况，属性名是大小写敏感的）
     * - the class has a member variable with the specified name (when `$checkVars` is true);
     * - 类有给定名称的成员变量（`$checkVars`为真的时候）；
     * - an attached behavior has a property of the given name (when `$checkBehaviors` is true).
     * - 被绑定的行为拥有给定的名称时（当$checkBehaviors为真的时候）
     *
     * @param string $name the property name
     * 参数 字符串 属性名
     * @param boolean $checkVars whether to treat member variables as properties
     * 参数 boolean 是否把成员变量当做属性
     * @param boolean $checkBehaviors whether to treat behaviors' properties as properties of this component
     * 参数 boolean 是否把行为当做该组件的属性
     * @return boolean whether the property is defined
     * 返回值 boolean 给定的属性是否被定义
     * @see canGetProperty()
     * @see canSetProperty()
     */
    public function hasProperty($name, $checkVars = true, $checkBehaviors = true)
    {
        return $this->canGetProperty($name, $checkVars, $checkBehaviors) || $this->canSetProperty($name, false, $checkBehaviors);
    }

    /**
     * Returns a value indicating whether a property can be read.
     * 返回属性值是否可读。
     * A property can be read if:
     * 在一下情况下，属性值可读：
     *
     * - the class has a getter method associated with the specified name
     *   (in this case, property name is case-insensitive);
     * - 类有一个跟指定名称相关的getter方法（这种情况下属性名是大小写敏感的）
     * - the class has a member variable with the specified name (when `$checkVars` is true);
     * - 类拥有跟指定名称相同的成员变量（当$checkVars设置为true的时候）
     * - an attached behavior has a readable property of the given name (when `$checkBehaviors` is true).
     * - 被绑定的行为拥有一个给定名称的可读的属性时（$checkBehaviors设置为true的时候）
     *
     * @param string $name the property name
     * 参数 字符串 属性名
     * @param boolean $checkVars whether to treat member variables as properties
     *  参数 boolean 是否把成员变量当做属性
     * @param boolean $checkBehaviors whether to treat behaviors' properties as properties of this component
     * 参数 boolean 是否把行为属性当做该组件的属性
     * @return boolean whether the property can be read
     * 返回值 boolean 该属性是否可读
     * @see canSetProperty()
     */
    public function canGetProperty($name, $checkVars = true, $checkBehaviors = true)
    {
        if (method_exists($this, 'get' . $name) || $checkVars && property_exists($this, $name)) {
            return true;
        } elseif ($checkBehaviors) {
            $this->ensureBehaviors();
            foreach ($this->_behaviors as $behavior) {
                if ($behavior->canGetProperty($name, $checkVars)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Returns a value indicating whether a property can be set.
     * 返回属性是否可设置
     * A property can be written if:
     * 如下情况，属性可设置：
     *
     * - the class has a setter method associated with the specified name
     *   (in this case, property name is case-insensitive);
     * - 类拥有一个给定名称的方法（这种情况下，属性名的区分大小写的）
     * - the class has a member variable with the specified name (when `$checkVars` is true);
     * - 该类拥有一个给定名称的成员变量（当$checkVars为true的时候）
     * - an attached behavior has a writable property of the given name (when `$checkBehaviors` is true).
     * - 一个绑定的行为拥有给定名称的可写属性时（当$checkBehaviors为true的时候）
     *
     * @param string $name the property name
     * 参数 字符串 属性名
     * @param boolean $checkVars whether to treat member variables as properties
     * 参数 boolean 是否把成员变量当做属性
     * @param boolean $checkBehaviors whether to treat behaviors' properties as properties of this component
     * 参数 boolean 是否把行为属性当做组件属性
     * @return boolean whether the property can be written
     * 返回值 boolean 该属性是否可写
     * @see canGetProperty()
     */
    public function canSetProperty($name, $checkVars = true, $checkBehaviors = true)
    {
        if (method_exists($this, 'set' . $name) || $checkVars && property_exists($this, $name)) {
            return true;
        } elseif ($checkBehaviors) {
            $this->ensureBehaviors();
            foreach ($this->_behaviors as $behavior) {
                if ($behavior->canSetProperty($name, $checkVars)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Returns a value indicating whether a method is defined.
     * 检测一个方法是否被定义
     * A method is defined if:
     * 如下情况，表示方法已经被定义：
     *
     * - the class has a method with the specified name
     * - 类拥有一个给定的名称的方法
     * - an attached behavior has a method with the given name (when `$checkBehaviors` is true).
     * - 被绑定的行为拥有给定名称的方法（当$checkBehaviors为true是）
     *
     * @param string $name the property name
     * 参数 字符串 属性名
     * @param boolean $checkBehaviors whether to treat behaviors' methods as methods of this component
     * 参数 boolean 是否把行为的方法当做该组件的方法
     * @return boolean whether the property is defined
     * 返回值 boolean 该属性是否被定义
     */
    public function hasMethod($name, $checkBehaviors = true)
    {
        if (method_exists($this, $name)) {
            return true;
        } elseif ($checkBehaviors) {
            $this->ensureBehaviors();
            foreach ($this->_behaviors as $behavior) {
                if ($behavior->hasMethod($name)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Returns a list of behaviors that this component should behave as.
     * 返回组件拥有的行为列表
     *
     * Child classes may override this method to specify the behaviors they want to behave as.
     * 子类可以重写此方法，指定他们想要执行的动作。
     *
     * The return value of this method should be an array of behavior objects or configurations
     * indexed by behavior names. A behavior configuration can be either a string specifying
     * the behavior class or an array of the following structure:
     * 该方法的返回值应当是一个行为对象的数组或者以行为名称为索引的配置数组。行为配置既可以是指定行为类名的字符串，也可以是如下结构的数组：
     *
     * ```php
     * 'behaviorName' => [
     *     'class' => 'BehaviorClass',
     *     'property1' => 'value1',
     *     'property2' => 'value2',
     * ]
     * ```
     *
     * Note that a behavior class must extend from [[Behavior]]. Behaviors can be attached using a name or anonymously.
     * When a name is used as the array key, using this name, the behavior can later be retrieved using [[getBehavior()]]
     * or be detached using [[detachBehavior()]]. Anonymous behaviors can not be retrieved or detached.
     * 注意，行为类必须继承[[Behavior]]。方法可以使用一个名字或者命名绑定，使用这个名字，行为可以通过[[getBehavior()]]重新获取，或者使用[[detachBehavior()]]
     * 解除。但是匿名的行为不能重新获取或者解除。
     *
     * Behaviors declared in this method will be attached to the component automatically (on demand).
     * 该方法声明的行为将会自动被绑定到组件
     *
     * @return array the behavior configurations.
     * 返回值 行为的配置项
     */
    public function behaviors()
    {
        return [];
    }

    /**
     * Returns a value indicating whether there is any handler attached to the named event.
     * 返回是否有任何处理程序绑定到了给定事件
     * @param string $name the event name
     * 参数 字符串 事件名
     * @return boolean whether there is any handler attached to the event.
     * 返回值 boolean 该事件是否有绑定的处理程序
     */
    public function hasEventHandlers($name)
    {
        $this->ensureBehaviors();
        return !empty($this->_events[$name]) || Event::hasHandlers($this, $name);
    }

    /**
     * Attaches an event handler to an event.
     * 给一个事件绑定事件处理程序
     *
     * The event handler must be a valid PHP callback. The following are
     * some examples:
     * 事件处理程序必须是合法的php回调，如下：
     *
     * ```
     * function ($event) { ... }         // anonymous function
     * [$object, 'handleClick']          // $object->handleClick()
     * ['Page', 'handleClick']           // Page::handleClick()
     * 'handleClick'                     // global function handleClick()
     * ```
     *
     * The event handler must be defined with the following signature,
     * 事件处理程序的定义必须有如下的特性，
     *
     * ```
     * function ($event)
     * ```
     *
     * where `$event` is an [[Event]] object which includes parameters associated with the event.
     * $event是包含跟事件[[Event]]相关参数的对象
     *
     * @param string $name the event name
     * 参数 字符串 事件名
     * @param callable $handler the event handler
     * 参数 事件处理程序
     * @param mixed $data the data to be passed to the event handler when the event is triggered.
     * When the event handler is invoked, this data can be accessed via [[Event::data]].
     * 参数 混合型 当事件被触发时，传递给事件处理程序的参数
     * @param boolean $append whether to append new event handler to the end of the existing
     * handler list. If false, the new handler will be inserted at the beginning of the existing
     * handler list.
     * 参数 boolean 是否添加一个新的事件处理程序到已经存在的事件处理列表的末尾。如果为false，就会把新的事件处理程序添加到程序处理的开头
     * @see off()
     */
    public function on($name, $handler, $data = null, $append = true)
    {
        $this->ensureBehaviors();
        if ($append || empty($this->_events[$name])) {
            $this->_events[$name][] = [$handler, $data];
        } else {
            array_unshift($this->_events[$name], [$handler, $data]);
        }
    }

    /**
     * Detaches an existing event handler from this component.
     * 解除该组件上已经绑定的事件处理程序
     * This method is the opposite of [[on()]].
     * 该方法的作用跟on相反
     * @param string $name event name
     * 参数 字符串 事件名
     * @param callable $handler the event handler to be removed.
     * 参数 将要移除的事件处理程序
     * If it is null, all handlers attached to the named event will be removed.
     * 如果为null，所有绑定的事件处理程序都会被移除
     * @return boolean if a handler is found and detached
     * 返回值 boolean 事件处理程序是否被找到并移除
     * @see on()
     */
    public function off($name, $handler = null)
    {
        $this->ensureBehaviors();
        if (empty($this->_events[$name])) {
            return false;
        }
        if ($handler === null) {
            unset($this->_events[$name]);
            return true;
        } else {
            $removed = false;
            foreach ($this->_events[$name] as $i => $event) {
                if ($event[0] === $handler) {
                    unset($this->_events[$name][$i]);
                    $removed = true;
                }
            }
            if ($removed) {
                $this->_events[$name] = array_values($this->_events[$name]);
            }
            return $removed;
        }
    }

    /**
     * Triggers an event.
     * 触发一个事件
     * This method represents the happening of an event. It invokes
     * 该方法代表事件的发生。
     * all attached handlers for the event including class-level handlers.
     * 它会调用所有绑定的事件处理程序，包括类级别的事件处理程序。
     * @param string $name the event name
     * 参数 字符串 事件名
     * @param Event $event the event parameter. If not set, a default [[Event]] object will be created.
     * 参数 事件 事件参数，如果没有社会自，将会创建默认的[[Event]]对象
     */
    public function trigger($name, Event $event = null)
    {
        $this->ensureBehaviors();
        if (!empty($this->_events[$name])) {
            if ($event === null) {
                $event = new Event;
            }
            if ($event->sender === null) {
                $event->sender = $this;
            }
            $event->handled = false;
            $event->name = $name;
            foreach ($this->_events[$name] as $handler) {
                $event->data = $handler[1];
                call_user_func($handler[0], $event);
                // stop further handling if the event is handled
                // 如果事件已经被处理了，那么就停止进一步的处理
                if ($event->handled) {
                    return;
                }
            }
        }
        // invoke class-level attached handlers
        // 调用绑定过的类级别的事件处理程序
        Event::trigger($this, $name, $event);
    }

    /**
     * Returns the named behavior object.
     * 返回给定的行为对象
     * @param string $name the behavior name
     * 参数 字符串 行为名称
     * @return null|Behavior the behavior object, or null if the behavior does not exist
     * 返回值 null|行为 行为对象，当行为不存在的时候返回null
     */
    public function getBehavior($name)
    {
        $this->ensureBehaviors();
        return isset($this->_behaviors[$name]) ? $this->_behaviors[$name] : null;
    }

    /**
     * Returns all behaviors attached to this component.
     * 返回所有绑定到该组件的行为
     * @return Behavior[] list of behaviors attached to this component
     * 返回值 绑定到组件的行为列表
     */
    public function getBehaviors()
    {
        $this->ensureBehaviors();
        return $this->_behaviors;
    }

    /**
     * Attaches a behavior to this component.
     * 为组建绑定一个行为
     * This method will create the behavior object based on the given
     * configuration. After that, the behavior object will be attached to
     * this component by calling the [[Behavior::attach()]] method.
     * 该干法会创建一个基于给定配置的行为对象。然后，行为对象会通过[[Behavior::attach()]]方法被绑定到组件
     * @param string $name the name of the behavior.
     * 参数 字符串 行为的名称
     * @param string|array|Behavior $behavior the behavior configuration. This can be one of the following:
     * 参数 字符串|数组|行为 行为配置，可以是如下的一种
     *
     *  - a [[Behavior]] object
     *  - 一个[[Behavior]]对象
     *  - a string specifying the behavior class
     *  - 一个指定行为类的字符串
     *  - an object configuration array that will be passed to [[Yii::createObject()]] to create the behavior object.
     *  - 创建行为对象时传递给[[Yii::createObject()]]的一个对象配置数组
     *
     * @return Behavior the behavior object
     * 返回值 行为对象
     * @see detachBehavior()
     */
    public function attachBehavior($name, $behavior)
    {
        $this->ensureBehaviors();
        return $this->attachBehaviorInternal($name, $behavior);
    }

    /**
     * Attaches a list of behaviors to the component.
     * 为组件绑定一组行为
     * Each behavior is indexed by its name and should be a [[Behavior]] object,
     * 每一个行为都通过它的名字索引，并且应该是[[Behavior]]的一个实例
     * a string specifying the behavior class, or an configuration array for creating the behavior.
     * 字符串指定行为类，或者一个配置数组创建行为
     * @param array $behaviors list of behaviors to be attached to the component
     * 参数 数组 将要绑定给组件的行为数组
     * @see attachBehavior()
     */
    public function attachBehaviors($behaviors)
    {
        $this->ensureBehaviors();
        foreach ($behaviors as $name => $behavior) {
            $this->attachBehaviorInternal($name, $behavior);
        }
    }

    /**
     * Detaches a behavior from the component.
     * 解除组件的一个行为
     * The behavior's [[Behavior::detach()]] method will be invoked.
     * 行为的[[Behavior::detach()]]方法会被调用
     * @param string $name the behavior's name.
     * 参数 字符串 行为的名称
     * @return null|Behavior the detached behavior. Null if the behavior does not exist.
     * 返回值 null|行为 被解除的行为，如果行为不存在就会返回null
     */
    public function detachBehavior($name)
    {
        $this->ensureBehaviors();
        if (isset($this->_behaviors[$name])) {
            $behavior = $this->_behaviors[$name];
            unset($this->_behaviors[$name]);
            $behavior->detach();
            return $behavior;
        } else {
            return null;
        }
    }

    /**
     * Detaches all behaviors from the component.
     * 删除该组件的所有行为。
     */
    public function detachBehaviors()
    {
        $this->ensureBehaviors();
        foreach ($this->_behaviors as $name => $behavior) {
            $this->detachBehavior($name);
        }
    }

    /**
     * Makes sure that the behaviors declared in [[behaviors()]] are attached to this component.
     * 确保在[[behaviors()]]中声明的行为被绑定到了该组件
     */
    public function ensureBehaviors()
    {
        if ($this->_behaviors === null) {
            $this->_behaviors = [];
            foreach ($this->behaviors() as $name => $behavior) {
                $this->attachBehaviorInternal($name, $behavior);
            }
        }
    }

    /**
     * Attaches a behavior to this component.
     * 把一个行为绑定到该组件
     * @param string|integer $name the name of the behavior. If this is an integer, it means the behavior
     * is an anonymous one. Otherwise, the behavior is a named one and any existing behavior with the same name
     * will be detached first.
     * 参数 字符串|整型 行为的名称。如果这个值是整型，它代表行为是匿名的。否则，行为是给定名称的，并且任何同名的行为都会先被解除。
     * @param string|array|Behavior $behavior the behavior to be attached
     * 参数 字符串|数组|行为 要绑定的行为
     * @return Behavior the attached behavior.
     * 返回值 绑定的行为。
     */
    private function attachBehaviorInternal($name, $behavior)
    {
        if (!($behavior instanceof Behavior)) {
            $behavior = Yii::createObject($behavior);
        }
        if (is_int($name)) {
            $behavior->attach($this);
            $this->_behaviors[] = $behavior;
        } else {
            if (isset($this->_behaviors[$name])) {
                $this->_behaviors[$name]->detach();
            }
            $behavior->attach($this);
            $this->_behaviors[$name] = $behavior;
        }
        return $behavior;
    }
}
