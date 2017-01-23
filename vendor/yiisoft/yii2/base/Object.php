<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;

/**
 * Object is the base class that implements the *property* feature.
 * Object是实现*property*特性的一个基类。
 *
 * A property is defined by a getter method (e.g. `getLabel`), and/or a setter method (e.g. `setLabel`). For example,
 * the following getter and setter methods define a property named `label`:
 * 可以通过getter方法(例如 `getLabel`)或者setter方法(例如 `setLabel`)来定义一个属性。例如，下面的setter，getter方法定义了一个名为`label`的属性
 *
 * ```php
 * private $_label;
 *
 * public function getLabel()
 * {
 *     return $this->_label;
 * }
 *
 * public function setLabel($value)
 * {
 *     $this->_label = $value;
 * }
 * ```
 *
 * Property names are *case-insensitive*.
 * 属性名不区分大小写
 *
 * A property can be accessed like a member variable of an object. Reading or writing a property will cause the invocation
 * of the corresponding getter or setter method. For example,
 * 属性的访问就像访问一个对象的变量一样。读取或写入一个属性将会调用相应的getter或setter方法。例如，
 *
 * ```php
 * // equivalent to $label = $object->getLabel();
 * // 等价于 $label = $object->getLabel();
 * $label = $object->label;
 * // equivalent to $object->setLabel('abc');
 * // 等价于 $object->setLabel('abc');
 * $object->label = 'abc';
 * ```
 *
 * If a property has only a getter method and has no setter method, it is considered as *read-only*. In this case, trying
 * to modify the property value will cause an exception.
 * 如果一个属性只有getter方法，而没有setter方法，那么该属性就是只读的。这种情况下，尝试改变该属性值会导致异常。
 *
 * One can call [[hasProperty()]], [[canGetProperty()]] and/or [[canSetProperty()]] to check the existence of a property.
 * 可以调用[[hasProperty()]], [[canGetProperty()]] 或 [[canSetProperty()]]等方法来检测一个属性是否存在。
 *
 * Besides the property feature, Object also introduces an important object initialization life cycle. In particular,
 * creating an new instance of Object or its derived class will involve the following life cycles sequentially:
 * 除了属性特性，Object也引入了一种重要的对象初始化生命周期机制。特别地，创建一个Object或其子类的新实例时，将会循序地调用下面的生命周期：
 *
 * 1. the class constructor is invoked;
 * 1. 类的构造函数被调用;
 *
 * 2. object properties are initialized according to the given configuration;
 * 2. 根据给定的配置项初始化对象属性;
 *
 * 3. the `init()` method is invoked.
 * 3. `init()`方法被调用.
 *
 * In the above, both Step 2 and 3 occur at the end of the class constructor. It is recommended that
 * you perform object initialization in the `init()` method because at that stage, the object configuration
 * is already applied.
 * 在上面的周期中，第二步和第三步都发生在类构造函数的最后。推荐你调用对象的初始化方法init，因为在哪种情况下，对象的配置已经生效了。
 *
 * In order to ensure the above life cycles, if a child class of Object needs to override the constructor,
 * it should be done like the following:
 * 为了确保上面的生命周期，如果一个Object的子类需要重写构造函数，应该参照下面的代码：
 *
 * ```php
 * public function __construct($param1, $param2, ..., $config = [])
 * {
 *     ...
 *     parent::__construct($config);
 * }
 * ```
 *
 * That is, a `$config` parameter (defaults to `[]`) should be declared as the last parameter
 * of the constructor, and the parent implementation should be called at the end of the constructor.
 * 也就是，默认为空数组的配置参数应该在构造函数的最后一个参数位置声明，并且父类的实现应该在构造函数的结尾处调用
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Object implements Configurable
{
    /**
     * Returns the fully qualified name of this class.
     * 返回该类完全合格的名字
     * @return string the fully qualified name of this class.
     * 返回值 字符串 该类完全合格的名字。
     */
    public static function className()
    {
        return get_called_class();
    }

    /**
     * Constructor.
     * 构造函数
     *
     * The default implementation does two things:
     * 默认的实现做了两件事情：
     *
     * - Initializes the object with the given configuration `$config`.
     * - 使用$config数组初始化对象
     * - Call [[init()]].
     * - 调用[[init()]]方法
     *
     * If this method is overridden in a child class, it is recommended that
     * 如果该方法在子类中被重写，推荐：
     *
     * - the last parameter of the constructor is a configuration array, like `$config` here.
     * - 构造函数的最后一个参数为配置项，就像这里的`$config`那样。
     *
     * - call the parent implementation at the end of the constructor.
     * - 在构造函数的末尾调用父类的实现。
     *
     * @param array $config name-value pairs that will be used to initialize the object properties
     * 参数 数组 用来初始化对象属性的键值对。
     */
    public function __construct($config = [])
    {
        if (!empty($config)) {
            Yii::configure($this, $config);
        }
        $this->init();
    }

    /**
     * Initializes the object.
     * 初始化对象
     *
     * This method is invoked at the end of the constructor after the object is initialized with the
     * given configuration.
     * 该方法会在构造函数的末尾，对象被给定的配置项初始化以后调用。
     */
    public function init()
    {
    }

    /**
     * Returns the value of an object property.
     * 返回一个对象属性的值。
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `$value = $object->property;`.
     * 不要直接调用该方法，因为它是一个PHP的魔术方法，会在执行`$value = $object->property;`的时候自动调用。
     *
     * @param string $name the property name
     * 参数 字符串 属性名
     *
     * @return mixed the property value
     * 返回值 混合型 属性值
     *
     * @throws UnknownPropertyException if the property is not defined
     * 当属性没有定义的时候抛出未知的属性异常。
     *
     * @throws InvalidCallException if the property is write-only
     * 当属性只写的时候抛出不合法的调用异常。
     * @see __set()
     */
    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        } elseif (method_exists($this, 'set' . $name)) {
            throw new InvalidCallException('Getting write-only property: ' . get_class($this) . '::' . $name);
        } else {
            throw new UnknownPropertyException('Getting unknown property: ' . get_class($this) . '::' . $name);
        }
    }

    /**
     * Sets value of an object property.
     * 设置一个对象属性的值。
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `$object->property = $value;`.
     * 不要直接调用此方法，因为这个是PHP的魔术方法，会在执行`$object->property = $value;`的时候自动调用。
     *
     * @param string $name the property name or the event name
     * 参数 字符串 属性名或者事件名
     *
     * @param mixed $value the property value
     * 参数 混合型 属性值
     *
     * @throws UnknownPropertyException if the property is not defined
     * 当属性没有被定义的时候抛出未知的属性异常
     *
     * @throws InvalidCallException if the property is read-only
     * 当属性只读的时候，抛出不合法的调用异常
     * @see __get()
     */
    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } elseif (method_exists($this, 'get' . $name)) {
            throw new InvalidCallException('Setting read-only property: ' . get_class($this) . '::' . $name);
        } else {
            throw new UnknownPropertyException('Setting unknown property: ' . get_class($this) . '::' . $name);
        }
    }

    /**
     * Checks if a property is set, i.e. defined and not null.
     * 检测一个属性是否设置，例如已经定义或者不为null
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `isset($object->property)`.
     * 不要直接调用此方法，因为该方法是PHP的魔术方法，会在执行`isset($object->property)`的时候自动调用
     *
     * Note that if the property is not defined, false will be returned.
     * 请注意，如果该属性没有被定义，就会返回false
     *
     * @param string $name the property name or the event name
     * 参数 字符串 属性名或者事件名
     *
     * @return boolean whether the named property is set (not null).
     * 返回值 boolean 给定的属性是否被设置（不为空）。
     *
     * @see http://php.net/manual/en/function.isset.php
     */
    public function __isset($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter() !== null;
        } else {
            return false;
        }
    }

    /**
     * Sets an object property to null.
     * 把一个对象属性设置为null
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `unset($object->property)`.
     * 不要直接调用此方法，因为这是PHP的模式方法，会在执行`unset($object->property)`的时候自动调用。
     *
     * Note that if the property is not defined, this method will do nothing.
     * 请注意，如果该属性没有被定义，该方法就什么也不做。
     *
     * If the property is read-only, it will throw an exception.
     * 如果该属性只读，就会抛出一个异常。
     *
     * @param string $name the property name
     * 参数 字符串 属性名
     *
     * @throws InvalidCallException if the property is read only.
     * 当属性只读的时候抛出不合法的调用异常。
     *
     * @see http://php.net/manual/en/function.unset.php
     */
    public function __unset($name)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter(null);
        } elseif (method_exists($this, 'get' . $name)) {
            throw new InvalidCallException('Unsetting read-only property: ' . get_class($this) . '::' . $name);
        }
    }

    /**
     * Calls the named method which is not a class method.
     * 调用的方法不是类的方法。
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when an unknown method is being invoked.
     * 不要直接调用此方法，因为这个是PHP的魔术方法，当你调用一个位置的方法时候，会自动执行。
     *
     * @param string $name the method name
     * 参数 字符串 方法名
     *
     * @param array $params method parameters
     * 参数 数组 调用方法传递的参数
     *
     * @throws UnknownMethodException when calling unknown method
     * 当调用未知的方法时，抛出未知的方法异常。
     *
     * @return mixed the method return value
     * 返回值 混合型 调用方法的返回值。
     */
    public function __call($name, $params)
    {
        throw new UnknownMethodException('Calling unknown method: ' . get_class($this) . "::$name()");
    }

    /**
     * Returns a value indicating whether a property is defined.
     * 返回一个值表示一个属性是否被定义。
     *
     * A property is defined if:
     * 如下情况表示一个属性已定义：
     *
     * - the class has a getter or setter method associated with the specified name
     *   (in this case, property name is case-insensitive);
     * - 该类拥有一个跟指定名相关的getter或者setter方法（这种情况下，属性名是不区分大小写的）
     * - the class has a member variable with the specified name (when `$checkVars` is true);
     * - 该类有用指定名称（当`$checkVars`为true的时候）的成员变量
     *
     * @param string $name the property name
     * 参数 字符串 属性名
     *
     * @param boolean $checkVars whether to treat member variables as properties
     * 参数 boolean 是否把成员变量当属性
     *
     * @return boolean whether the property is defined
     * 返回值 boolean 属性是否被定义
     *
     * @see canGetProperty()
     * @see canSetProperty()
     */
    public function hasProperty($name, $checkVars = true)
    {
        return $this->canGetProperty($name, $checkVars) || $this->canSetProperty($name, false);
    }

    /**
     * Returns a value indicating whether a property can be read.
     * 返回一个值表示一个属性是否可读。
     *
     * A property is readable if:
     * 一个属性在以下情况可读：
     *
     * - the class has a getter method associated with the specified name
     *   (in this case, property name is case-insensitive);
     * - 该类拥有一个跟给定名称相关联的getter方法（这种情况下，属性名是不区分大小写的）
     * - the class has a member variable with the specified name (when `$checkVars` is true);
     * - 类名拥有一个给定名称（当`$checkVars`为true的时候）的成员变量时。
     *
     * @param string $name the property name
     * 参数 字符串 属性名
     *
     * @param boolean $checkVars whether to treat member variables as properties
     * 参数 boolean 是否吧成员变量当做属性
     *
     * @return boolean whether the property can be read
     * 返回值 boolean 属性是否可读
     *
     * @see canSetProperty()
     */
    public function canGetProperty($name, $checkVars = true)
    {
        return method_exists($this, 'get' . $name) || $checkVars && property_exists($this, $name);
    }

    /**
     * Returns a value indicating whether a property can be set.
     * 返回一个值表示一个属性是否可以设置：
     *
     * A property is writable if:
     * 一个很属性在以下情况下可以设置：
     *
     * - the class has a setter method associated with the specified name
     *   (in this case, property name is case-insensitive);
     * - 此类拥有一个跟给定名相关的setter方法（这种情况下，属性名是不区分大小写的）
     * - the class has a member variable with the specified name (when `$checkVars` is true);
     * - 此类拥有一个跟定名称的成员变量（当`$checkVars`为true的时候）
     *
     * @param string $name the property name
     * 参数 字符串 属性名
     *
     * @param boolean $checkVars whether to treat member variables as properties
     * 参数 boolean 是否把成员变量当做属性
     *
     * @return boolean whether the property can be written
     * 返回值 boolean 该属性是否可写
     *
     * @see canGetProperty()
     */
    public function canSetProperty($name, $checkVars = true)
    {
        return method_exists($this, 'set' . $name) || $checkVars && property_exists($this, $name);
    }

    /**
     * Returns a value indicating whether a method is defined.
     * 返回一个值表示某个方法是否被定义。
     *
     * The default implementation is a call to php function `method_exists()`.
     * 默认的实现是调用了php的函数`method_exists()`
     *
     * You may override this method when you implemented the php magic method `__call()`.
     * 当你实现了PHP的魔术方法`__call()`的时候，你可以重写此方法。
     *
     * @param string $name the method name
     * 参数 字符串 方法名
     *
     * @return boolean whether the method is defined
     * 返回值 boolean 给定方法是否定义
     */
    public function hasMethod($name)
    {
        return method_exists($this, $name);
    }
}
