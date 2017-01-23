<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use yii\validators\Validator;

/**
 * DynamicModel is a model class primarily used to support ad hoc data validation.
 * 动态模型主要用于支持临时数据验证的一个模型类
 *
 * The typical usage of DynamicModel is as follows,
 * 动态模型的典型使用方法如下：
 *
 * ```php
 * public function actionSearch($name, $email)
 * {
 *     $model = DynamicModel::validateData(compact('name', 'email'), [
 *         [['name', 'email'], 'string', 'max' => 128],
 *         ['email', 'email'],
 *     ]);
 *     if ($model->hasErrors()) {
 *         // validation fails
 *         // 验证失败
 *     } else {
 *         // validation succeeds
 *         // 验证成功
 *     }
 * }
 * ```
 *
 * The above example shows how to validate `$name` and `$email` with the help of DynamicModel.
 * 上边的例子展示了如何使用动态模型去验证`$name` 和 `$email`
 *
 * The [[validateData()]] method creates an instance of DynamicModel, defines the attributes
 * using the given data (`name` and `email` in this example), and then calls [[Model::validate()]].
 * validateData()方法创建了动态模型的实例，使用给定的数据（这个例子中是使用`name` 和 `email`）定义属性，然后调用
 * [[Model::validate()]]方法
 *
 * You can check the validation result by [[hasErrors()]], like you do with a normal model.
 * 你可以使用[[hasErrors()]]检测验证结果，跟使用普通的模型一样。
 *
 * You may also access the dynamic attributes defined through the model instance, e.g.,
 * 你也可以通过动态模型的实例去调用动态属性，例如：
 * `$model->name` and `$model->email`.
 *
 * Alternatively, you may use the following more "classic" syntax to perform ad-hoc data validation:
 * 还有一种选择，你可以使用以下更“经典”的语法来执行特定数据验证
 *
 * ```php
 * $model = new DynamicModel(compact('name', 'email'));
 * $model->addRule(['name', 'email'], 'string', ['max' => 128])
 *     ->addRule('email', 'email')
 *     ->validate();
 * ```
 *
 * DynamicModel implements the above ad-hoc data validation feature by supporting the so-called
 * 动态模型通过支持所谓的动态属性，从而实现了上边的特定数据的验证特性。
 *
 * "dynamic attributes". It basically allows an attribute to be defined dynamically through its constructor
 * or [[defineAttribute()]].
 * 它允许通过其构造函数或[[defineAttribute()]]方法动态定义一个属性
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DynamicModel extends Model
{
    private $_attributes = [];


    /**
     * Constructors.
     * 构造函数
     *
     * @param array $attributes the dynamic attributes (name-value pairs, or names) being defined
     * 参数 数组 被定义的动态属性键值对
     *
     * @param array $config the configuration array to be applied to this object.
     * 参数 数组 应用于该对象的配置数组
     */
    public function __construct(array $attributes = [], $config = [])
    {
        foreach ($attributes as $name => $value) {
            if (is_int($name)) {
                $this->_attributes[$value] = null;
            } else {
                $this->_attributes[$name] = $value;
            }
        }
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->_attributes)) {
            return $this->_attributes[$name];
        } else {
            return parent::__get($name);
        }
    }

    /**
     * @inheritdoc
     */
    public function __set($name, $value)
    {
        if (array_key_exists($name, $this->_attributes)) {
            $this->_attributes[$name] = $value;
        } else {
            parent::__set($name, $value);
        }
    }

    /**
     * @inheritdoc
     */
    public function __isset($name)
    {
        if (array_key_exists($name, $this->_attributes)) {
            return isset($this->_attributes[$name]);
        } else {
            return parent::__isset($name);
        }
    }

    /**
     * @inheritdoc
     */
    public function __unset($name)
    {
        if (array_key_exists($name, $this->_attributes)) {
            unset($this->_attributes[$name]);
        } else {
            parent::__unset($name);
        }
    }

    /**
     * Defines an attribute.
     * 定义一个属性
     *
     * @param string $name the attribute name
     * 参数 字符串 属性名
     *
     * @param mixed $value the attribute value
     * 参数 混合型 属性值
     */
    public function defineAttribute($name, $value = null)
    {
        $this->_attributes[$name] = $value;
    }

    /**
     * Undefines an attribute.
     * 删除一个属性
     *
     * @param string $name the attribute name
     * 参数 字符串 属性名
     */
    public function undefineAttribute($name)
    {
        unset($this->_attributes[$name]);
    }

    /**
     * Adds a validation rule to this model.
     * 给该模型添加验证规则。
     *
     * You can also directly manipulate [[validators]] to add or remove validation rules.
     * This method provides a shortcut.
     * 你也可以直接使用[[validators]]增加或删除验证规则，该方法提供了一个快捷方式。
     *
     * @param string|array $attributes the attribute(s) to be validated by the rule
     * 参数 字符串|数组 被验证的属性名
     *
     * @param mixed $validator the validator for the rule.This can be a built-in validator name,
     * a method name of the model class, an anonymous function, or a validator class name.
     * 参数 混合型 规则的验证器。可以是内建的验证名，模型类的方法名，匿名函数 或者验证器的类名
     *
     * @param array $options the options (name-value pairs) to be applied to the validator
     * 参数 数组 应用于验证器选项
     *
     * @return $this the model itself
     * 返回值 模型自身
     */
    public function addRule($attributes, $validator, $options = [])
    {
        $validators = $this->getValidators();
        $validators->append(Validator::createValidator($validator, $this, (array) $attributes, $options));

        return $this;
    }

    /**
     * Validates the given data with the specified validation rules.
     * 使用指定的验证规则验证给定的数据
     *
     * This method will create a DynamicModel instance, populate it with the data to be validated,
     * create the specified validation rules, and then validate the data using these rules.
     * 该方法会创建一个动态模型实例，填充验证的数据，创建指定的验证规则，然后使用这些规则验证数据。
     *
     * @param array $data the data (name-value pairs) to be validated
     * 参数 数组 将要被验证的数据（键值对）
     *
     * @param array $rules the validation rules. Please refer to [[Model::rules()]] on the format of this parameter.
     * 参数 数组 验证规则。 请参考[[Model::rules()]]查看参数的格式
     *
     * @return static the model instance that contains the data being validated
     * 返回值 包含被验证数据的模型实例
     *
     * @throws InvalidConfigException if a validation rule is not specified correctly.
     * 当验证规则指定不正确时，抛出不合法的配置异常
     */
    public static function validateData(array $data, $rules = [])
    {
        /* @var $model DynamicModel */
        /* @var $model 动态模型 */
        $model = new static($data);
        if (!empty($rules)) {
            $validators = $model->getValidators();
            foreach ($rules as $rule) {
                if ($rule instanceof Validator) {
                    $validators->append($rule);
                } elseif (is_array($rule) && isset($rule[0], $rule[1])) { // attributes, validator type
                    $validator = Validator::createValidator($rule[1], $model, (array) $rule[0], array_slice($rule, 2));
                    $validators->append($validator);
                } else {
                    throw new InvalidConfigException('Invalid validation rule: a rule must specify both attribute names and validator type.');
                }
            }
        }

        $model->validate();

        return $model;
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return array_keys($this->_attributes);
    }
}
