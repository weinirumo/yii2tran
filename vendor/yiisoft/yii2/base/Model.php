<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;
use ArrayAccess;
use ArrayObject;
use ArrayIterator;
use ReflectionClass;
use IteratorAggregate;
use yii\helpers\Inflector;
use yii\validators\RequiredValidator;
use yii\validators\Validator;

/**
 * Model is the base class for data models.
 * Model是数据模型的基类
 *
 * Model implements the following commonly used features:
 * model实现了如下常用的特性：
 *
 * - attribute declaration: by default, every public class member is considered as
 *   a model attribute
 * - 属性声明：默认情况下，每一个公共的类成员都被当做一个模型属性
 * - attribute labels: each attribute may be associated with a label for display purpose
 * - 属性标签：可以为每一个属性关联一个显示标签
 * - massive attribute assignment
 * - 批量属性赋值
 * - scenario-based validation
 * - 基于场景的验证
 *
 * Model also raises the following events when performing data validation:
 * 当数据验证时，模型会触发如下的事件：
 *
 * - [[EVENT_BEFORE_VALIDATE]]: an event raised at the beginning of [[validate()]]
 * - [[EVENT_BEFORE_VALIDATE]]：在validate方法开始触发的事件
 * - [[EVENT_AFTER_VALIDATE]]: an event raised at the end of [[validate()]]
 * - [[EVENT_AFTER_VALIDATE]]：在validate方法结束触发的事件
 *
 * You may directly use Model to store model data, or extend it with customization.
 * 你可以直接使用模型存储模型数据，或者自定义扩展它
 *
 * @property \yii\validators\Validator[] $activeValidators The validators applicable to the current
 * [[scenario]]. This property is read-only.
 * 属性 适用于当前场景的验证器。该属性只读
 *
 * @property array $attributes Attribute values (name => value).
 * 属性 数组 属性值
 *
 * @property array $errors An array of errors for all attributes. Empty array is returned if no error. The
 * result is a two-dimensional array. See [[getErrors()]] for detailed description. This property is read-only.
 * 属性 数组 所有属性的错误数组集。如果没有错误会返回空数组，结果是二位数组。参考getErrors方法获取更多信息。该属性只读
 *
 * @property array $firstErrors The first errors. The array keys are the attribute names, and the array values
 * are the corresponding error messages. An empty array will be returned if there is no error. This property is
 * read-only.
 * 属性 数组 第一个错误数组的键是属性名，数组的值是对应的错误信息。如果没有错误就会返回空数组。该属性只读
 *
 * @property ArrayIterator $iterator An iterator for traversing the items in the list. This property is
 * read-only.
 * 属性 遍历列表中项目的迭代器 该属性只读
 *
 * @property string $scenario The scenario that this model is in. Defaults to [[SCENARIO_DEFAULT]].
 * 属性 字符串 该模型处于的场景。默认是[[SCENARIO_DEFAULT]]
 *
 * @property ArrayObject|\yii\validators\Validator[] $validators All the validators declared in the model.
 * This property is read-only.
 * 属性 数组对象|\yii\validators\Validator 所有在模型中声明的验证器
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Model extends Component implements IteratorAggregate, ArrayAccess, Arrayable
{
    use ArrayableTrait;

    /**
     * The name of the default scenario.
     * 默认场景的名称
     */
    const SCENARIO_DEFAULT = 'default';
    /**
     * @event ModelEvent an event raised at the beginning of [[validate()]]. You may set
     * [[ModelEvent::isValid]] to be false to stop the validation.
     * validate方法执行以前的事件。你可以设置[[ModelEvent::isValid]]为false来取消验证
     */
    const EVENT_BEFORE_VALIDATE = 'beforeValidate';
    /**
     * @event Event an event raised at the end of [[validate()]]
     * 事件 [[validate()]]方法的最后触发的事件
     */
    const EVENT_AFTER_VALIDATE = 'afterValidate';

    /**
     * @var array validation errors (attribute name => array of errors)
     * 属性 数组 验证错误(属性名 => 错误信息组成的数组)
     */
    private $_errors;
    /**
     * @var ArrayObject list of validators
     * 属性 数组对象 验证器列表
     */
    private $_validators;
    /**
     * @var string current scenario
     * 属性 字符串 当前的场景
     */
    private $_scenario = self::SCENARIO_DEFAULT;


    /**
     * Returns the validation rules for attributes.
     * 返回属性的验证的规则。
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * 验证规则被用于[[validate()]]方法来检测属性值是否合法。
     *
     * Child classes may override this method to declare different validation rules.
     * 子类可以重写该方法类声明不同的验证规则。
     *
     * Each rule is an array with the following structure:
     * 每一个规则都是如下格式的数组：
     *
     * ```php
     * [
     *     ['attribute1', 'attribute2'],
     *     'validator type',
     *     'on' => ['scenario1', 'scenario2'],
     *     //...other parameters...
     * ]
     * ```
     *
     * where
     *
     *  - attribute list: required, specifies the attributes array to be validated, for single attribute you can pass a string;
     *  - 属性列表：必须，指定被验证的属性数组，单属性使用字符串也可以；
     *
     *  - validator type: required, specifies the validator to be used. It can be a built-in validator name,
     *    a method name of the model class, an anonymous function, or a validator class name.
     *  - 验证器类型，必须，指定采用何种验证器，可以是一个内建的验证器名称，模型类的一个方法名，或者一个匿名函数，或者一个验证器类名。
     *
     *  - on: optional, specifies the [[scenario|scenarios]] array in which the validation
     *    rule can be applied. If this option is not set, the rule will apply to all scenarios.
     *  - on：可选，指定[[scenario|scenarios]]哪种场景下可以使用验证规则。如果该属性没有设置，该规则就会适用于所有场景。
     *
     *  - additional name-value pairs can be specified to initialize the corresponding validator properties.
     *    Please refer to individual validator class API for possible properties.
     *  - 也可以指定额外的键值对来初始化相应的验证器属性。可能的属性请参考验证器类的API
     *
     * A validator can be either an object of a class extending [[Validator]], or a model class method
     * (called *inline validator*) that has the following signature:
     * 验证器可以是继承[[Validator]]的一个对象，也可以是一个有用如下特征的模型类的方法(称为行内验证器)
     *
     * ```php
     * // $params refers to validation parameters given in the rule
     * // $params 是指规则中传递的验证参数。
     * function validatorName($attribute, $params)
     * ```
     *
     * In the above `$attribute` refers to the attribute currently being validated while `$params` contains an array of
     * validator configuration options such as `max` in case of `string` validator. The value of the attribute currently being validated
     * can be accessed as `$this->$attribute`. Note the `$` before `attribute`; this is taking the value of the variable
     * `$attribute` and using it as the name of the property to access.
     * 在上边的例子中`$attribute`是指当前被验证的属性，而`$params`包含验证器配置项的数组，例如string验证器的时候max。当前验证属性的值可以通过
     * `$this->$attribute`来访问。请注意属性前边的$符；这样做可以获取`$attribute`变量的值并使用它作为属性名来访问。
     *
     * Yii also provides a set of [[Validator::builtInValidators|built-in validators]].
     * Each one has an alias name which can be used when specifying a validation rule.
     * Yii也提供了一系列内置的验证器[[Validator::builtInValidators|built-in validators]]。
     * 每一个验证器都有一个别名，可以在指定验证规则的时候使用。
     *
     * Below are some examples:
     * 请看下面的例子：
     *
     * ```php
     * [
     *     // built-in "required" validator
     *     // 内置的 "required" 验证器
     *     [['username', 'password'], 'required'],
     *
     *     // built-in "string" validator customized with "min" and "max" properties
     *     // 内置的 "string" 验证器带有自定义的"最小值" 和 "最大值" 属性
     *     ['username', 'string', 'min' => 3, 'max' => 12],
     *
     *     // built-in "compare" validator that is used in "register" scenario only
     *     // 内置的 "compare" 验证器 只在register场景中使用
     *     ['password', 'compare', 'compareAttribute' => 'password2', 'on' => 'register'],
     *
     *     // an inline validator defined via the "authenticate()" method in the model class
     *     // 通过模型类中"authenticate()" 方法定义的一个行内验证器
     *     ['password', 'authenticate', 'on' => 'login'],
     *
     *     // a validator of class "DateRangeValidator"
     *     // 验证器类"DateRangeValidator"
     *     ['dateRange', 'DateRangeValidator'],
     * ];
     * ```
     *
     * Note, in order to inherit rules defined in the parent class, a child class needs to
     * merge the parent rules with child rules using functions such as `array_merge()`.
     * 请注意，为了继承父类的中定义的规则，需要在子类中合并父类和子类的规则，使用函数array_merge。
     *
     * @return array validation rules
     * 返回值 数组 验证规则。
     * @see scenarios()
     */
    public function rules()
    {
        return [];
    }

    /**
     * Returns a list of scenarios and the corresponding active attributes.
     * 返回场景的集合和相应的激活属性。
     *
     * An active attribute is one that is subject to validation in the current scenario.
     * 激活属性是指从属于当前场景验证的那个属性。
     *
     * The returned array should be in the following format:
     * 返回的数组应该以如下的格式出现：
     *
     * ```php
     * [
     *     'scenario1' => ['attribute11', 'attribute12', ...],
     *     'scenario2' => ['attribute21', 'attribute22', ...],
     *     ...
     * ]
     * ```
     *
     * By default, an active attribute is considered safe and can be massively assigned.
     * 默认情况下，一个活动属性被当做安全并且可以大量赋值的。
     *
     * If an attribute should NOT be massively assigned (thus considered unsafe),
     * please prefix the attribute with an exclamation character (e.g. `'!rank'`).
     * 如果一个属性不应该被大量赋值（被当不安全），请在属性前边加上一个前缀感叹号!(例如，`'!rank'`)。
     *
     * The default implementation of this method will return all scenarios found in the [[rules()]]
     * declaration. A special scenario named [[SCENARIO_DEFAULT]] will contain all attributes
     * found in the [[rules()]]. Each scenario will be associated with the attributes that
     * are being validated by the validation rules that apply to the scenario.
     * 该方法的默认实现将会返回rules里边定义的所有场景。一个名为[[SCENARIO_DEFAULT]]的特殊场景将会包含rules里边包含的所有属性。
     * 每一个场景都将会跟属性关联，这些属性将会被跟该场景相关的验证规则验证。
     *
     * @return array a list of scenarios and the corresponding active attributes.
     * 返回值 数组 场景的列表和相应的活动属性。
     */
    public function scenarios()
    {
        $scenarios = [self::SCENARIO_DEFAULT => []];
        foreach ($this->getValidators() as $validator) {
            foreach ($validator->on as $scenario) {
                $scenarios[$scenario] = [];
            }
            foreach ($validator->except as $scenario) {
                $scenarios[$scenario] = [];
            }
        }
        $names = array_keys($scenarios);

        foreach ($this->getValidators() as $validator) {
            if (empty($validator->on) && empty($validator->except)) {
                foreach ($names as $name) {
                    foreach ($validator->attributes as $attribute) {
                        $scenarios[$name][$attribute] = true;
                    }
                }
            } elseif (empty($validator->on)) {
                foreach ($names as $name) {
                    if (!in_array($name, $validator->except, true)) {
                        foreach ($validator->attributes as $attribute) {
                            $scenarios[$name][$attribute] = true;
                        }
                    }
                }
            } else {
                foreach ($validator->on as $name) {
                    foreach ($validator->attributes as $attribute) {
                        $scenarios[$name][$attribute] = true;
                    }
                }
            }
        }

        foreach ($scenarios as $scenario => $attributes) {
            if (!empty($attributes)) {
                $scenarios[$scenario] = array_keys($attributes);
            }
        }

        return $scenarios;
    }

    /**
     * Returns the form name that this model class should use.
     * 返回该模型类所使用的表单名。
     *
     * The form name is mainly used by [[\yii\widgets\ActiveForm]] to determine how to name
     * the input fields for the attributes in a model. If the form name is "A" and an attribute
     * name is "b", then the corresponding input name would be "A[b]". If the form name is
     * an empty string, then the input name would be "b".
     * 该表单名主要被用于[[\yii\widgets\ActiveForm]]来决定如何命名模型里边属性的input域。如果表单名为A，属性名是b，那么相应的input name就
     * 应该是A[b],如果表单名为空，那么input里边的name就应该是b
     *
     * The purpose of the above naming schema is that for forms which contain multiple different models,
     * the attributes of each model are grouped in sub-arrays of the POST-data and it is easier to
     * differentiate between them.
     * 上述命名的模式是为了包含多个不同的模型，每个模型的属性都分组存放在POST-data的子数组，并且易于区分它们。
     *
     * By default, this method returns the model class name (without the namespace part)
     * as the form name. You may override it when the model is used in different forms.
     * 默认情况下，该方法返回模型的类名（去掉命名空间部分）作为表单名。当模型被用于不同的表单之中时，你可以重写它。
     *
     * @return string the form name of this model class.
     * 返回值 字符串 该模型的表单名。
     *
     * @see load()
     */
    public function formName()
    {
        $reflector = new ReflectionClass($this);
        return $reflector->getShortName();
    }

    /**
     * Returns the list of attribute names.
     * 返回属性名称的列表。
     *
     * By default, this method returns all public non-static properties of the class.
     * 默认情况下，该方法会返回类里边所有公共非静态的属性。
     *
     * You may override this method to change the default behavior.
     * 你可以重写此方法来改变默认的行为。
     *
     * @return array list of attribute names.
     * 返回值 数组 属性名称的列表。
     */
    public function attributes()
    {
        $class = new ReflectionClass($this);
        $names = [];
        foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if (!$property->isStatic()) {
                $names[] = $property->getName();
            }
        }

        return $names;
    }

    /**
     * Returns the attribute labels.
     * 返回属性标签。
     *
     * Attribute labels are mainly used for display purpose. For example, given an attribute
     * `firstName`, we can declare a label `First Name` which is more user-friendly and can
     * be displayed to end users.
     * 属性标签主要用来显示。例如，给定的一个属性`firstName`，可以声明一个对用户更加友好的标签`First Name`来显示给终端用户。
     *
     * By default an attribute label is generated using [[generateAttributeLabel()]].
     * This method allows you to explicitly specify attribute labels.
     * 默认情况下，一个属性标签可以使用[[generateAttributeLabel()]]方法生成。
     * 该方法允许你显式地指定属性标签。
     *
     * Note, in order to inherit labels defined in the parent class, a child class needs to
     * merge the parent labels with child labels using functions such as `array_merge()`.
     * 注意，为了继承父类中定义的标签，需要在子类中合并父类和子类的标签，使用array_merge函数。
     *
     * @return array attribute labels (name => label)
     * 返回值 数组 属性标签（属性名=>标签）
     *
     * @see generateAttributeLabel()
     */
    public function attributeLabels()
    {
        return [];
    }

    /**
     * Returns the attribute hints.
     * 返回属性提示。
     *
     * Attribute hints are mainly used for display purpose. For example, given an attribute
     * `isPublic`, we can declare a hint `Whether the post should be visible for not logged in users`,
     * which provides user-friendly description of the attribute meaning and can be displayed to end users.
     * 属性提示主要用来显示。例如，一个给定的属性`isPublic`，我们可以声明一个提示`邮件是否对没有登陆的用户可见`，提供了一个对用户友好的属性描述并
     * 展示给终端用户。
     *
     * Unlike label hint will not be generated, if its explicit declaration is omitted.
     * 不同于标签提示的不会自动生成，如果属性的提示声明被忽略（会自动生成提示）。
     *
     * Note, in order to inherit hints defined in the parent class, a child class needs to
     * merge the parent hints with child hints using functions such as `array_merge()`.
     * 请注意，为了集成父类中定义的提示，需要在子类合并父类和子类的提示，使用类似array_merge函数。
     *
     * @return array attribute hints (name => hint)
     * 返回值 数组 属性提示（属性名=>提示）
     *
     * @since 2.0.4
     */
    public function attributeHints()
    {
        return [];
    }

    /**
     * Performs the data validation.
     * 执行数据验证。
     *
     * This method executes the validation rules applicable to the current [[scenario]].
     * The following criteria are used to determine whether a rule is currently applicable:
     * 该方法执行适用于当前场景的验证规则。下面的条件用来检验一个条件当前是否适用：
     *
     * - the rule must be associated with the attributes relevant to the current scenario;
     * - 规则必须跟当前的场景下的属性相关；
     * - the rules must be effective for the current scenario.
     * - 规则必须对当前的场景生效。
     *
     * This method will call [[beforeValidate()]] and [[afterValidate()]] before and
     * after the actual validation, respectively. If [[beforeValidate()]] returns false,
     * the validation will be cancelled and [[afterValidate()]] will not be called.
     * 验证之前该方法会调用[[beforeValidate()]]，相应的，验证完成之后，该方法会调用[[afterValidate()]]。
     * 如果[[beforeValidate()]]方法返回了false，那么验证就会被取消，[[afterValidate()]]方法就不再被调用了。
     *
     * Errors found during the validation can be retrieved via [[getErrors()]],
     * [[getFirstErrors()]] and [[getFirstError()]].
     * 验证期间遇到的错误，可以通过[[getErrors()]]，[[getFirstErrors()]] 和 [[getFirstError()]]等三个方法获取。
     *
     * @param array $attributeNames list of attribute names that should be validated.
     * If this parameter is empty, it means any attribute listed in the applicable
     * validation rules should be validated.
     * 参数 数组 被验证的属性名称列表。如果该参数为空，意味着使用验证规则的属性都会被验证。
     *
     * @param boolean $clearErrors whether to call [[clearErrors()]] before performing validation
     * 参数 boolean 验证执行以前是否调用clearErrors方法
     *
     * @return boolean whether the validation is successful without any error.
     * 返回值 boolean 验证是否成功没有任何错误。
     *
     * @throws InvalidParamException if the current scenario is unknown.
     * 当前场景未知的情况下，返回不合法的参数异常。
     */
    public function validate($attributeNames = null, $clearErrors = true)
    {
        if ($clearErrors) {
            $this->clearErrors();
        }

        if (!$this->beforeValidate()) {
            return false;
        }

        $scenarios = $this->scenarios();
        $scenario = $this->getScenario();
        if (!isset($scenarios[$scenario])) {
            throw new InvalidParamException("Unknown scenario: $scenario");
        }

        if ($attributeNames === null) {
            $attributeNames = $this->activeAttributes();
        }

        foreach ($this->getActiveValidators() as $validator) {
            $validator->validateAttributes($this, $attributeNames);
        }
        $this->afterValidate();

        return !$this->hasErrors();
    }

    /**
     * This method is invoked before validation starts.
     * 该方法在验证开始之前调用。
     *
     * The default implementation raises a `beforeValidate` event.
     * 默认的实现是调用了一个`验证之前`事件
     *
     * You may override this method to do preliminary checks before validation.
     * 你可以重写此方法来做一些验证之前的初步检测。
     *
     * Make sure the parent implementation is invoked so that the event can be raised.
     * 确保父类的实现被调用，这样才可以触发事件。
     *
     * @return boolean whether the validation should be executed. Defaults to true.
     * If false is returned, the validation will stop and the model is considered invalid.
     * 返回值 boolean 是否继续执行验证。默认是true。如果返回了false,验证终止，模型会被认为不合法。
     */
    public function beforeValidate()
    {
        $event = new ModelEvent;
        $this->trigger(self::EVENT_BEFORE_VALIDATE, $event);

        return $event->isValid;
    }

    /**
     * This method is invoked after validation ends.
     * 验证结束以后就会触发该方法。
     *
     * The default implementation raises an `afterValidate` event.
     * 默认的实现是调用`验证之后`事件。
     *
     * You may override this method to do postprocessing after validation.
     * 你可以重写此方法来做验证以后的后续处理。
     *
     * Make sure the parent implementation is invoked so that the event can be raised.
     * 确保父类的实现被调用过，这样该事件才可以被触发。
     */
    public function afterValidate()
    {
        $this->trigger(self::EVENT_AFTER_VALIDATE);
    }

    /**
     * Returns all the validators declared in [[rules()]].
     * 返回所有在rules方法里边声明的验证器。
     *
     * This method differs from [[getActiveValidators()]] in that the latter
     * only returns the validators applicable to the current [[scenario]].
     * 该方法跟[[getActiveValidators()]]的不同之处在于，后者只返回适用于当前场景的验证器。
     *
     * Because this method returns an ArrayObject object, you may
     * manipulate it by inserting or removing validators (useful in model behaviors).
     * 因为该方法返回一个数组对象，你可以通过插入或者移除验证器处理它（在模型行为中比较有用）
     *
     * For example,
     * （例如，）
     *
     * ```php
     * $model->validators[] = $newValidator;
     * ```
     *
     * @return ArrayObject|\yii\validators\Validator[] all the validators declared in the model.
     * 返回值 模型中声明的所有验证器。
     */
    public function getValidators()
    {
        if ($this->_validators === null) {
            $this->_validators = $this->createValidators();
        }
        return $this->_validators;
    }

    /**
     * Returns the validators applicable to the current [[scenario]].
     * 返回适用于当前场景的验证器。
     *
     * @param string $attribute the name of the attribute whose applicable validators should be returned.
     * If this is null, the validators for ALL attributes in the model will be returned.
     * 参数 字符串 适用的验证器应当被返回的属性名。如果为null，该模型下的所有属性的验证器都会被返回。
     *
     * @return \yii\validators\Validator[] the validators applicable to the current [[scenario]].
     * 返回值 适用于当前场景的验证器。
     */
    public function getActiveValidators($attribute = null)
    {
        $validators = [];
        $scenario = $this->getScenario();
        foreach ($this->getValidators() as $validator) {
            if ($validator->isActive($scenario) && ($attribute === null || in_array($attribute, $validator->attributes, true))) {
                $validators[] = $validator;
            }
        }
        return $validators;
    }

    /**
     * Creates validator objects based on the validation rules specified in [[rules()]].
     * 基于rules方法里边指定的验证规则创建验证器对象。
     *
     * Unlike [[getValidators()]], each time this method is called, a new list of validators will be returned.
     * 跟[[getValidators()]]方法不同，每次调用该方法，一个新的验证器列表就会被返回。
     *
     * @return ArrayObject validators
     * 返回值 数组对象 验证器
     *
     * @throws InvalidConfigException if any validation rule configuration is invalid
     * 当任何验证规则配置不合法的时候抛出不合法的配置异常。
     */
    public function createValidators()
    {
        $validators = new ArrayObject;
        foreach ($this->rules() as $rule) {
            if ($rule instanceof Validator) {
                $validators->append($rule);
            } elseif (is_array($rule) && isset($rule[0], $rule[1])) { // attributes, validator type
                $validator = Validator::createValidator($rule[1], $this, (array) $rule[0], array_slice($rule, 2));
                $validators->append($validator);
            } else {
                throw new InvalidConfigException('Invalid validation rule: a rule must specify both attribute names and validator type.');
            }
        }
        return $validators;
    }

    /**
     * Returns a value indicating whether the attribute is required.
     * 返回一个值表示该属性是否必需。
     *
     * This is determined by checking if the attribute is associated with a
     * [[\yii\validators\RequiredValidator|required]] validation rule in the
     * current [[scenario]].
     * 根据属性是否跟当前场景下[[\yii\validators\RequiredValidator|required]]规则相关联来判断的。
     *
     * Note that when the validator has a conditional validation applied using
     * [[\yii\validators\RequiredValidator::$when|$when]] this method will return
     * `false` regardless of the `when` condition because it may be called be
     * before the model is loaded with data.
     * 请注意，当验证器拥有一个条件验证采用了[[\yii\validators\RequiredValidator::$when|$when]]规则的时候，该方法会忽略where条件而直接返回
     * false，因为它可能会在模型载入数据之前调用。
     *
     * @param string $attribute attribute name
     * 参数 字符串 属性名
     *
     * @return boolean whether the attribute is required
     * 返回值 boolean 当前属性是否必需
     */
    public function isAttributeRequired($attribute)
    {
        foreach ($this->getActiveValidators($attribute) as $validator) {
            if ($validator instanceof RequiredValidator && $validator->when === null) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns a value indicating whether the attribute is safe for massive assignments.
     * 返回一个值表示该属性大量赋值时是否安全。
     *
     * @param string $attribute attribute name
     * 参数 字符串 属性名
     *
     * @return boolean whether the attribute is safe for massive assignments
     * 返回值 boolean 该属性大量赋值是否安全。
     *
     * @see safeAttributes()
     */
    public function isAttributeSafe($attribute)
    {
        return in_array($attribute, $this->safeAttributes(), true);
    }

    /**
     * Returns a value indicating whether the attribute is active in the current scenario.
     * 返回一个值表示该属性在当前场景下是否处于激活状态。
     *
     * @param string $attribute attribute name
     * 参数 字符串 属性名
     *
     * @return boolean whether the attribute is active in the current scenario
     * 返回值 boolean 当前场景下属性是否处于活动状态。
     *
     * @see activeAttributes()
     */
    public function isAttributeActive($attribute)
    {
        return in_array($attribute, $this->activeAttributes(), true);
    }

    /**
     * Returns the text label for the specified attribute.
     * 返回指定的属性的文本标签。
     *
     * @param string $attribute the attribute name
     * 参数 字符串 属性名
     *
     * @return string the attribute label
     * 返回值 字符串 属性标签
     *
     * @see generateAttributeLabel()
     * @see attributeLabels()
     */
    public function getAttributeLabel($attribute)
    {
        $labels = $this->attributeLabels();
        return isset($labels[$attribute]) ? $labels[$attribute] : $this->generateAttributeLabel($attribute);
    }

    /**
     * Returns the text hint for the specified attribute.
     * 返回指定属性的文字提示。
     *
     * @param string $attribute the attribute name
     * 参数 字符串 属性名
     *
     * @return string the attribute hint
     * 返回值 字符串 属性提示
     *
     * @see attributeHints()
     * @since 2.0.4
     */
    public function getAttributeHint($attribute)
    {
        $hints = $this->attributeHints();
        return isset($hints[$attribute]) ? $hints[$attribute] : '';
    }

    /**
     * Returns a value indicating whether there is any validation error.
     * 返回一个表示是否有验证错误的值
     *
     * @param string|null $attribute attribute name. Use null to check all attributes.
     * 参数 字符串|null 属性名，传null可以获取所有的属性
     *
     * @return boolean whether there is any error.
     * 返回值 boolean 是否有错误
     */
    public function hasErrors($attribute = null)
    {
        return $attribute === null ? !empty($this->_errors) : isset($this->_errors[$attribute]);
    }

    /**
     * Returns the errors for all attribute or a single attribute.
     * 返回一个属性或者所有属性的错误信息。
     *
     * @param string $attribute attribute name. Use null to retrieve errors for all attributes.
     * 参数 字符串 属性名。要获取所有属性的错误信息，得使用null
     *
     * @property array An array of errors for all attributes. Empty array is returned if no error.
     * The result is a two-dimensional array. See [[getErrors()]] for detailed description.
     * 属性 数组 一个所有属性的错误信息组成的数组。如果没有错误返回空数组。结果是一个二维数组。更多详细描述请参考方法[[getErrors()]]
     *
     * @return array errors for all attributes or the specified attribute. Empty array is returned if no error.
     * 返回值 数组 特定的或所有的属性的错误信息组成的数组。如果没有错误就返回空数组
     *
     * Note that when returning errors for all attributes, the result is a two-dimensional array, like the following:
     * 请注意，当返回所有属性的错误信息时，结果就是一个二维数组，格式如下：
     *
     * ```php
     * [
     *     'username' => [
     *         'Username is required.',
     *         'Username must contain only word characters.',
     *     ],
     *     'email' => [
     *         'Email address is invalid.',
     *     ]
     * ]
     * ```
     *
     * @see getFirstErrors()
     * @see getFirstError()
     */
    public function getErrors($attribute = null)
    {
        if ($attribute === null) {
            return $this->_errors === null ? [] : $this->_errors;
        } else {
            return isset($this->_errors[$attribute]) ? $this->_errors[$attribute] : [];
        }
    }

    /**
     * Returns the first error of every attribute in the model.
     * 返回模型中的每个属性的第一个错误
     *
     * @return array the first errors. The array keys are the attribute names, and the array
     * values are the corresponding error messages. An empty array will be returned if there is no error.
     * 返回值 数组 第一个错误。数组的键数属性名，数组的值是相应的错误西你想。如果没有错误，就返回空数组
     *
     * @see getErrors()
     * @see getFirstError()
     */
    public function getFirstErrors()
    {
        if (empty($this->_errors)) {
            return [];
        } else {
            $errors = [];
            foreach ($this->_errors as $name => $es) {
                if (!empty($es)) {
                    $errors[$name] = reset($es);
                }
            }

            return $errors;
        }
    }

    /**
     * Returns the first error of the specified attribute.
     * 返回指定标签的第一个错误。
     *
     * @param string $attribute attribute name.
     * 参数 字符串 标签名
     *
     * @return string the error message. Null is returned if no error.
     * 返回值 字符串 错误信息，如果没有错误信息就返回null
     *
     * @see getErrors()
     * @see getFirstErrors()
     */
    public function getFirstError($attribute)
    {
        return isset($this->_errors[$attribute]) ? reset($this->_errors[$attribute]) : null;
    }

    /**
     * Adds a new error to the specified attribute.
     * 给指定的属性添加一个错误信息。
     *
     * @param string $attribute attribute name
     * 参数 字符串 属性名
     *
     * @param string $error new error message
     * 参数 字符串 新的错误信息
     */
    public function addError($attribute, $error = '')
    {
        $this->_errors[$attribute][] = $error;
    }

    /**
     * Adds a list of errors.
     * 添加一个错误列表
     *
     * @param array $items a list of errors. The array keys must be attribute names.
     * The array values should be error messages. If an attribute has multiple errors,
     * these errors must be given in terms of an array.
     * 参数 数组 错误列表。该数组的键必须是属性名。数组的值应该是错误信息。如果一个属性有多个错误，这些错误
     * 需要通过数组的方式传入。
     *
     * You may use the result of [[getErrors()]] as the value for this parameter.
     * 你可以使用[[getErrors()]]的结果作为参数
     *
     * @since 2.0.2
     */
    public function addErrors(array $items)
    {
        foreach ($items as $attribute => $errors) {
            if (is_array($errors)) {
                foreach ($errors as $error) {
                    $this->addError($attribute, $error);
                }
            } else {
                $this->addError($attribute, $errors);
            }
        }
    }

    /**
     * Removes errors for all attributes or a single attribute.
     * 为所有或一个属性移除错误信息。
     * @param string $attribute attribute name. Use null to remove errors for all attribute.
     * 参数 字符串 属性名。传入null，可以删除所有属性的错误信息
     */
    public function clearErrors($attribute = null)
    {
        if ($attribute === null) {
            $this->_errors = [];
        } else {
            unset($this->_errors[$attribute]);
        }
    }

    /**
     * Generates a user friendly attribute label based on the give attribute name.
     * 根据给定的属性名生成一个对用户友好的属性标签。
     *
     * This is done by replacing underscores, dashes and dots with blanks and
     * changing the first letter of each word to upper case.
     * 通过把下换线，斜线和点替换成空格，并把每一个单词的首字母改成大写来实现。
     *
     * For example, 'department_name' or 'DepartmentName' will generate 'Department Name'.
     * 例如，'department_name' 或 'DepartmentName'将会生成'Department Name'。
     *
     * @param string $name the column name
     * 参数 字符串 列名
     * @return string the attribute label
     * 返回值 字符串 属性标签
     */
    public function generateAttributeLabel($name)
    {
        return Inflector::camel2words($name, true);
    }

    /**
     * Returns attribute values.
     * 返回属性值。
     * @param array $names list of attributes whose value needs to be returned.
     * Defaults to null, meaning all attributes listed in [[attributes()]] will be returned.
     * 参数 数组 需要返回值的属性列表。默认是null，意味着所有[[attributes()]]方法里列出的属性值都会被返回。
     *
     * If it is an array, only the attributes in the array will be returned.
     * 如果是一个数组，只有在数组中的属性值才会被返回。
     *
     * @param array $except list of attributes whose value should NOT be returned.
     * 参数 数组 不需要返回的属性列表。
     *
     * @return array attribute values (name => value).
     * 返回值 数组 属性值（键值对）
     */
    public function getAttributes($names = null, $except = [])
    {
        $values = [];
        if ($names === null) {
            $names = $this->attributes();
        }
        foreach ($names as $name) {
            $values[$name] = $this->$name;
        }
        foreach ($except as $name) {
            unset($values[$name]);
        }

        return $values;
    }

    /**
     * Sets the attribute values in a massive way.
     * 使用批量的方式设置属性值。
     *
     * @param array $values attribute values (name => value) to be assigned to the model.
     * 参数 数组 被分配到模型的属性值(名称 => 值)
     *
     * @param boolean $safeOnly whether the assignments should only be done to the safe attributes.
     * A safe attribute is one that is associated with a validation rule in the current [[scenario]].
     * 参数 boolean 赋值是否只针对安全属性。安全属性是当前场景下跟验证规则相关联的属性。
     *
     * @see safeAttributes()
     * @see attributes()
     */
    public function setAttributes($values, $safeOnly = true)
    {
        if (is_array($values)) {
            $attributes = array_flip($safeOnly ? $this->safeAttributes() : $this->attributes());
            foreach ($values as $name => $value) {
                if (isset($attributes[$name])) {
                    $this->$name = $value;
                } elseif ($safeOnly) {
                    $this->onUnsafeAttribute($name, $value);
                }
            }
        }
    }

    /**
     * This method is invoked when an unsafe attribute is being massively assigned.
     * 当一个不安全的属性被批量赋值的时候调用该方法。
     *
     * The default implementation will log a warning message if YII_DEBUG is on.
     * 默认的实现是，如果YII_DEBUTG开启的时候，会记录一个警告信息。
     *
     * It does nothing otherwise.
     * 否则它什么也不做。
     *
     * @param string $name the unsafe attribute name
     * 参数 字符串 不安全的属性名
     *
     * @param mixed $value the attribute value
     * 参数 混合型 属性值。
     */
    public function onUnsafeAttribute($name, $value)
    {
        if (YII_DEBUG) {
            Yii::trace("Failed to set unsafe attribute '$name' in '" . get_class($this) . "'.", __METHOD__);
        }
    }

    /**
     * Returns the scenario that this model is used in.
     * 返回该模型正在使用中的场景。
     *
     * Scenario affects how validation is performed and which attributes can
     * be massively assigned.
     * 场景影响验证是如何执行的,以及哪些属性可以大规模分配。
     *
     * @return string the scenario that this model is in. Defaults to [[SCENARIO_DEFAULT]].
     * 返回值 字符串 模型正在使用的场景。默认是[[SCENARIO_DEFAULT]]
     */
    public function getScenario()
    {
        return $this->_scenario;
    }

    /**
     * Sets the scenario for the model.
     * 为模型设置场景。
     *
     * Note that this method does not check if the scenario exists or not.
     * 请注意该方法不会检测被设置的场景是否存在。
     *
     * The method [[validate()]] will perform this check.
     * validate方法将会执行该检测。
     *
     * @param string $value the scenario that this model is in.
     * 参数 字符串 该模型所处的模型。
     */
    public function setScenario($value)
    {
        $this->_scenario = $value;
    }

    /**
     * Returns the attribute names that are safe to be massively assigned in the current scenario.
     * 返回当前场景下可以安全地大量赋值的属性名
     *
     * @return string[] safe attribute names
     * 返回值 字符串 安全的属性名
     */
    public function safeAttributes()
    {
        $scenario = $this->getScenario();
        $scenarios = $this->scenarios();
        if (!isset($scenarios[$scenario])) {
            return [];
        }
        $attributes = [];
        foreach ($scenarios[$scenario] as $attribute) {
            if ($attribute[0] !== '!' && !in_array('!' . $attribute, $scenarios[$scenario])) {
                $attributes[] = $attribute;
            }
        }

        return $attributes;
    }

    /**
     * Returns the attribute names that are subject to validation in the current scenario.
     * 返回当前场景下从属于验证的属性名
     *
     * @return string[] safe attribute names
     * 返回值 字符串 安全的属性名
     */
    public function activeAttributes()
    {
        $scenario = $this->getScenario();
        $scenarios = $this->scenarios();
        if (!isset($scenarios[$scenario])) {
            return [];
        }
        $attributes = $scenarios[$scenario];
        foreach ($attributes as $i => $attribute) {
            if ($attribute[0] === '!') {
                $attributes[$i] = substr($attribute, 1);
            }
        }

        return $attributes;
    }

    /**
     * Populates the model with input data.
     * 使用input数据填充模型。
     *
     * This method provides a convenient shortcut for:
     * 该方法为下面的代码提供了一个方便的快捷方式：
     *
     * ```php
     * if (isset($_POST['FormName'])) {
     *     $model->attributes = $_POST['FormName'];
     *     if ($model->save()) {
     *         // handle success
     *     }
     * }
     * ```
     *
     * which, with `load()` can be written as:
     * 然而，使用load方法可以这样写：
     *
     * ```php
     * if ($model->load($_POST) && $model->save()) {
     *     // handle success
     * }
     * ```
     *
     * `load()` gets the `'FormName'` from the model's [[formName()]] method (which you may override), unless the
     * `$formName` parameter is given. If the form name is empty, `load()` populates the model with the whole of `$data`,
     * instead of `$data['FormName']`.
     * 如果`$formName`参数没有提供，`load()`方法通过模型的[[formName()]]（你可以重写formName方法）方法来获取表单名。如果表单名为空，`load()`
     * 方法使用全部的`$data`来填充模型，而不是`$data['FormName']`。
     *
     * Note, that the data being populated is subject to the safety check by [[setAttributes()]].
     * 请注意，被填充的数据的安全性受[[setAttributes()]]支配。
     *
     * @param array $data the data array to load, typically `$_POST` or `$_GET`.
     * 参数 数组 被加载的数据，通常是`$_POST` 或 `$_GET`
     *
     * @param string $formName the form name to use to load the data into the model.
     * If not set, [[formName()]] is used.
     * 参数 字符串 用来加载数据到模型的表单名
     *
     * @return boolean whether `load()` found the expected form in `$data`.
     * 返回值 boolean 是否把数据加载到指定的的表单。
     */
    public function load($data, $formName = null)
    {
        $scope = $formName === null ? $this->formName() : $formName;
        if ($scope === '' && !empty($data)) {
            $this->setAttributes($data);

            return true;
        } elseif (isset($data[$scope])) {
            $this->setAttributes($data[$scope]);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Populates a set of models with the data from end user.
     * 使用终端用户的数据填充一系列模型。
     *
     * This method is mainly used to collect tabular data input.
     * 该方法主要用来收集表格数据输入。
     *
     * The data to be loaded for each model is `$data[formName][index]`, where `formName`
     * refers to the value of [[formName()]], and `index` the index of the model in the `$models` array.
     * 加载到每个model的数据格式是`$data[formName][index]`,`formName`指的是[[formName()]]方法的值，`index`指的是模型数组中模型的索引。
     *
     * If [[formName()]] is empty, `$data[index]` will be used to populate each model.
     * 如果[[formName()]]为空，`$data[index]`将会被填充到每一个模型之中。
     *
     * The data being populated to each model is subject to the safety check by [[setAttributes()]].
     * 被填充到每一个模型的数据安全受[[setAttributes()]]方法支配。
     *
     * @param array $models the models to be populated. Note that all models should have the same class.
     * 参数 数组 被填充的模型。请注意，所有的模型都应该拥有相同的类。
     *
     * @param array $data the data array. This is usually `$_POST` or `$_GET`, but can also be any valid array
     * supplied by end user.
     * 参数 数组 数据数组。通常是`$_POST` 或者 `$_GET`，但也可以是任何终端用户提供的合法数组。
     *
     * @param string $formName the form name to be used for loading the data into the models.
     * If not set, it will use the [[formName()]] value of the first model in `$models`.
     * This parameter is available since version 2.0.1.
     * 参数 字符串 用来载入数据到模型中用到的表单名。如果没有设置，将会使用`$models`里边的第一个model的[[formName()]]值，该参数在版本2.0.1之后
     * 可用。
     *
     * @return boolean whether at least one of the models is successfully populated.
     * 返回值 boolean 是否至少一个模型成功填充。
     */
    public static function loadMultiple($models, $data, $formName = null)
    {
        if ($formName === null) {
            /* @var $first Model */
            $first = reset($models);
            if ($first === false) {
                return false;
            }
            $formName = $first->formName();
        }

        $success = false;
        foreach ($models as $i => $model) {
            /* @var $model Model */
            if ($formName == '') {
                if (!empty($data[$i])) {
                    $model->load($data[$i], '');
                    $success = true;
                }
            } elseif (!empty($data[$formName][$i])) {
                $model->load($data[$formName][$i], '');
                $success = true;
            }
        }

        return $success;
    }

    /**
     * Validates multiple models.
     * 验证多个模型。
     *
     * This method will validate every model. The models being validated may
     * be of the same or different types.
     * 该方法将会验证每一个model。被验证的模型的类型可以相同，也可以不同
     *
     * @param array $models the models to be validated
     * 参数 数组 被验证的模型
     *
     * @param array $attributeNames list of attribute names that should be validated.
     * If this parameter is empty, it means any attribute listed in the applicable
     * validation rules should be validated.
     * 参数 数组 需要被验证的属性列表。如果该参数为空，意味着任何适用验证规则的属性都会被验证。
     *
     * @return boolean whether all models are valid. False will be returned if one
     * or multiple models have validation error.
     * 返回值 boolean 所有的模型验证是否都通过。如果一个或者多个模型验证不成功，将会返回false。
     */
    public static function validateMultiple($models, $attributeNames = null)
    {
        $valid = true;
        /* @var $model Model */
        foreach ($models as $model) {
            $valid = $model->validate($attributeNames) && $valid;
        }

        return $valid;
    }

    /**
     * Returns the list of fields that should be returned by default by [[toArray()]] when no specific fields are specified.
     * 返回默认没有指定字段，应该被[[toArray()]]方法返回的字段列表。
     *
     * A field is a named element in the returned array by [[toArray()]].
     * 字段就是一个[[toArray()]]方法返回的数组中的命名的元素。
     *
     * This method should return an array of field names or field definitions.
     * 该方法应该返回字段名或者字段定义的数组。
     *
     * If the former, the field name will be treated as an object property name whose value will be used
     * as the field value. If the latter, the array key should be the field name while the array value should be
     * the corresponding field definition which can be either an object property name or a PHP callable
     * returning the corresponding field value. The signature of the callable should be:
     * 如果是前者，字段名会被当做对象的属性名，对象的属性值会被当做字段的值。如果是后者，数组的键应该是字段名，数组的值应该是相应的字段定义，可以是
     * 一个对象属性名或者一个php回调返回相应的字段值。回调的特征如下：
     *
     * ```php
     * function ($model, $field) {
     *     // return field value
     *     // 返回字段值
     * }
     * ```
     *
     * For example, the following code declares four fields:
     * 例如，如下的代码声明了四个字段：
     *
     * - `email`: the field name is the same as the property name `email`;
     * - `email`: 字段名跟属性名一样是`email`；
     *
     * - `firstName` and `lastName`: the field names are `firstName` and `lastName`, and their
     *   values are obtained from the `first_name` and `last_name` properties;
     * - `firstName` 和 `lastName`: 字段名是`firstName` 和 `lastName`，他们的值从first_name和last_name属性获取。
     *
     * - `fullName`: the field name is `fullName`. Its value is obtained by concatenating `first_name`
     *   and `last_name`.
     * - `fullName`: 字段名是`fullName`，它的值`first_name`和`last_name`拼接组成的。
     *
     * ```php
     * return [
     *     'email',
     *     'firstName' => 'first_name',
     *     'lastName' => 'last_name',
     *     'fullName' => function ($model) {
     *         return $model->first_name . ' ' . $model->last_name;
     *     },
     * ];
     * ```
     *
     * In this method, you may also want to return different lists of fields based on some context
     * information. For example, depending on [[scenario]] or the privilege of the current application user,
     * you may return different sets of visible fields or filter out some fields.
     * 在此方法中，根据上下文信息，你可能需要返回不同的字段。例如，依据场景和当前应用用户的授权，你可以返回不同的可见字段或者过滤掉一些字段。
     *
     * The default implementation of this method returns [[attributes()]] indexed by the same attribute names.
     * 该方法的默认实现是返回[[attributes()]]根据相同的属性名索引
     *
     * @return array the list of field names or field definitions.
     * 返回值 数组 字段名或者字段定义列表。
     * @see toArray()
     */
    public function fields()
    {
        $fields = $this->attributes();

        return array_combine($fields, $fields);
    }

    /**
     * Returns an iterator for traversing the attributes in the model.
     * 返回一个用来遍历模型中属性的迭代器。
     *
     * This method is required by the interface [[\IteratorAggregate]].
     * 该方法被接口[[\IteratorAggregate]]需要。
     *
     * @return ArrayIterator an iterator for traversing the items in the list.
     * 返回值 遍历列表中条目的迭代器。
     */
    public function getIterator()
    {
        $attributes = $this->getAttributes();
        return new ArrayIterator($attributes);
    }

    /**
     * Returns whether there is an element at the specified offset.
     * 返回指定的offset是否有元素。
     *
     * This method is required by the SPL interface [[\ArrayAccess]].
     * 该方法被SPL接口[[\ArrayAccess]]需要。
     *
     * It is implicitly called when you use something like `isset($model[$offset])`.
     * 当你使用一些类似`isset($model[$offset])`的语句时，隐式的调用
     *
     * @param mixed $offset the offset to check on.
     * 参数 混合型 需要检测的offset
     *
     * @return boolean whether or not an offset exists.
     * 返回值 boolean offset是否存在。
     */
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    /**
     * Returns the element at the specified offset.
     * 返回指定offset的元素。
     *
     * This method is required by the SPL interface [[\ArrayAccess]].
     * 该方法因为SPL接口[[\ArrayAccess]]的需要。
     *
     * It is implicitly called when you use something like `$value = $model[$offset];`.
     * 当你使用一些类似`$value = $model[$offset];`的时候，隐式调用。
     *
     * @param mixed $offset the offset to retrieve element.
     * 参数 混合型 检索元素的offset
     *
     * @return mixed the element at the offset, null if no element is found at the offset
     * 返回值 混合型 在offset处的元素。如果在指定的偏移处没找到元素就返回null
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    /**
     * Sets the element at the specified offset.
     * 在特定的offset设置元素。
     *
     * This method is required by the SPL interface [[\ArrayAccess]].
     * 该方法因为SPL接口[[\ArrayAccess]]的需要。
     *
     * It is implicitly called when you use something like `$model[$offset] = $item;`.
     * 当你使用一些类似于`$model[$offset] = $item;`的时候隐式调用。
     *
     * @param integer $offset the offset to set element
     * 参数 整型 设置元素的offset
     *
     * @param mixed $item the element value
     * 参数 混合型 元素值。
     */
    public function offsetSet($offset, $item)
    {
        $this->$offset = $item;
    }

    /**
     * Sets the element value at the specified offset to null.
     * 把元素指定offset的值设置为null
     *
     * This method is required by the SPL interface [[\ArrayAccess]].
     * 该方法因为SPL接口[[\ArrayAccess]]的需要。
     *
     * It is implicitly called when you use something like `unset($model[$offset])`.
     * 当你使用`unset($model[$offset])`等方法的时候，隐式调用。
     *
     * @param mixed $offset the offset to unset element
     * 参数 混合型 需要unset的元素的offset。
     */
    public function offsetUnset($offset)
    {
        $this->$offset = null;
    }
}
