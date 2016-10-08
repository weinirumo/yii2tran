<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Link;
use yii\web\Linkable;

/**
 * ArrayableTrait provides a common implementation of the [[Arrayable]] interface.
 * ArrayableTrait提供了一个对[[Arrayable]]接口的广泛实现
 *
 * ArrayableTrait implements [[toArray()]] by respecting the field definitions as declared
 * in [[fields()]] and [[extraFields()]].
 * ArrayableTrait 依照[[fields()]] 和 [[extraFields()]]两个方法定义的字段实现了[[toArray()]]方法
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
trait ArrayableTrait
{
    /**
     * Returns the list of fields that should be returned by default by [[toArray()]] when no specific fields are specified.
     * 没有指定字段时，会返回默认的字段到[[toArray()]]
     *
     * A field is a named element in the returned array by [[toArray()]].
     * 字段是指一个[[toArray()]]返回的一个属性
     *
     * This method should return an array of field names or field definitions.
     * If the former, the field name will be treated as an object property name whose value will be used
     * as the field value. If the latter, the array key should be the field name while the array value should be
     * the corresponding field definition which can be either an object property name or a PHP callable
     * returning the corresponding field value. The signature of the callable should be:
     * 如果返回的是前者，属性名做字段名，属性值做字段值。如果是后者，数组的键是字段名，数组的值是可以是相应的字段定义，比如对象的属性名或者一个处理字段值
     * php回调函数。回调函数的特征如下：
     *
     * ```php
     * function ($model, $field) {
     *     // return field value
     * }
     * ```
     *
     * For example, the following code declares four fields:
     * 例如，下边的代码声明了四个字段：
     *
     * - `email`: the field name is the same as the property name `email`;
     * - `email`: 字段名跟属性名一样也是`email`
     * - `firstName` and `lastName`: the field names are `firstName` and `lastName`, and their
     *   values are obtained from the `first_name` and `last_name` properties;
     * - `firstName` and `lastName`: 来自包含`firstName` 和 `lastName`的属性名
     * - `fullName`: the field name is `fullName`. Its value is obtained by concatenating `first_name`
     *   and `last_name`.
     * fullName：字段名是fullName，字段值由`firstName` 和 `lastName` 拼接而成
     *
     * ```php
     * return [
     *     'email',
     *     'firstName' => 'first_name',
     *     'lastName' => 'last_name',
     *     'fullName' => function () {
     *         return $this->first_name . ' ' . $this->last_name;
     *     },
     * ];
     * ```
     *
     * In this method, you may also want to return different lists of fields based on some context
     * information. For example, depending on the privilege of the current application user,
     * you may return different sets of visible fields or filter out some fields.
     * 你可以根据实际情况返回不同的字段值。例如，根据当前应用用户的权限，你可以有选择的展示一些字段，过滤一些字段
     *
     * The default implementation of this method returns the public object member variables indexed by themselves.
     * 该实现默认返回共有的成员属性
     *
     * @return array the list of field names or field definitions.
     * 返回值 数组 字段名或者字段定义的数组
     * @see toArray()
     */
    public function fields()
    {
        $fields = array_keys(Yii::getObjectVars($this));
        return array_combine($fields, $fields);
    }

    /**
     * Returns the list of fields that can be expanded further and returned by [[toArray()]].
     * 返回可以被拓展的字段，用以在[[toArray()]]中返回
     *
     * This method is similar to [[fields()]] except that the list of fields returned
     * by this method are not returned by default by [[toArray()]]. Only when field names
     * to be expanded are explicitly specified when calling [[toArray()]], will their values
     * be exported.
     * 该方法跟[[fields()]]类似，除了该方法声明的字段不会默认被[[toArray()]]返回。这个方法中，只有显示的声明过的字段才能被添加到[[toArray()]]
     *
     * The default implementation returns an empty array.
     * 该实现默认返回一个空数组
     *
     * You may override this method to return a list of expandable fields based on some context information
     * (e.g. the current application user).
     * 你可以根据一些具体情况重写此方法，返回额外的信息（比如根据当前应用的用户）
     *
     * @return array the list of expandable field names or field definitions. Please refer
     * to [[fields()]] on the format of the return value.
     * 返回值 数组 扩展的字段名或者字段定义。请参考[[fields()]]了解返回值的格式
     * @see toArray()
     * @see fields()
     */
    public function extraFields()
    {
        return [];
    }

    /**
     * Converts the model into an array.
     * 把model转化成数组
     *
     * This method will first identify which fields to be included in the resulting array by calling [[resolveFields()]].
     * It will then turn the model into an array with these fields. If `$recursive` is true,
     * any embedded objects will also be converted into arrays.
     * 该方法首先调用[[resolveFields()]]，决定输出哪些字段。然后再根据这些字段把model转化成数组，如果递归参数为true，还会递归地把嵌套的对象转化成数组
     *
     * If the model implements the [[Linkable]] interface, the resulting array will also have a `_link` element
     * which refers to a list of links as specified by the interface.
     * 如果model实现了[[Linkable]]接口，那么结果数组也会包含一个_link元素，代表了接口指定的链接的集合
     *
     * @param array $fields the fields being requested. If empty, all fields as specified by [[fields()]] will be returned.
     * 参数 数组 被请求输出的数组，如果为空，[[fields()]]方法指定的字段会被使用
     * @param array $expand the additional fields being requested for exporting. Only fields declared in [[extraFields()]]
     * will be considered.
     * 参数 数组 额外需要输出的数组。只有[[extraFields()]]指定的字段会被采用
     * @param boolean $recursive whether to recursively return array representation of embedded objects.
     * 参数 boolean 是否递归的返回嵌套的对象
     * @return array the array representation of the object
     * 返回值 数组 代表对象的数组
     */
    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        $data = [];
        foreach ($this->resolveFields($fields, $expand) as $field => $definition) {
            $data[$field] = is_string($definition) ? $this->$definition : call_user_func($definition, $this, $field);
        }

        if ($this instanceof Linkable) {
            $data['_links'] = Link::serialize($this->getLinks());
        }

        return $recursive ? ArrayHelper::toArray($data) : $data;
    }

    /**
     * Determines which fields can be returned by [[toArray()]].
     * 确定被[[toArray()]]输出的字段
     * This method will check the requested fields against those declared in [[fields()]] and [[extraFields()]]
     * to determine which fields can be returned.
     * 该方法会检测跟[[fields()]]和[[extraFields()]]有冲突的字段，进而决定返回哪些字段
     * @param array $fields the fields being requested for exporting
     * 参数 数组 将要导出的字段
     * @param array $expand the additional fields being requested for exporting
     * 参数 数组 额外需要导出的字段
     *
     * @return array the list of fields to be exported. The array keys are the field names, and the array values
     * are the corresponding object property names or PHP callables returning the field values.
     * 返回值 数组 将要导出的字段。数组键是字段名，值是根据对象名或者php回调返回的字段值
     *
     */
    protected function resolveFields(array $fields, array $expand)
    {
        $result = [];

        foreach ($this->fields() as $field => $definition) {
            if (is_int($field)) {
                $field = $definition;
            }
            if (empty($fields) || in_array($field, $fields, true)) {
                $result[$field] = $definition;
            }
        }

        if (empty($expand)) {
            return $result;
        }

        foreach ($this->extraFields() as $field => $definition) {
            if (is_int($field)) {
                $field = $definition;
            }
            if (in_array($field, $expand, true)) {
                $result[$field] = $definition;
            }
        }

        return $result;
    }
}
