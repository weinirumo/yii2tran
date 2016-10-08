<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * Arrayable is the interface that should be implemented by classes who want to support customizable representation of their instances.
 * Arrayable是一个接口，实现它的类可以支持自定义该类实例的展示
 *
 * For example, if a class implements Arrayable, by calling [[toArray()]], an instance of this class
 * can be turned into an array (including all its embedded objects) which can then be further transformed easily
 * into other formats, such as JSON, XML.
 * 例如，如果一个类实现了Arrayable，调用toArray的时候，类的实例就会被转化成数组（包括其所有嵌入的对象），进而可以轻松地实现其他格式的转换，例如json或者xml
 *
 * The methods [[fields()]] and [[extraFields()]] allow the implementing classes to customize how and which of their data
 * should be formatted and put into the result of [[toArray()]].
 * 方法[[fields()]] 和 [[extraFields()]] 可以让实现的类决定哪些他们的数据以及如何格式化，并把处理后的结果放到[[toArray()]]方法中
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
interface Arrayable
{
    /**
     * Returns the list of fields that should be returned by default by [[toArray()]] when no specific fields are specified.
     * 没有指定字段时，会返回默认的字段到[[toArray()]]
     *
     * A field is a named element in the returned array by [[toArray()]].
     * 字段是指一个[[toArray()]]返回的一个属性
     *
     * This method should return an array of field names or field definitions.
     * 该方法应该返回字段名或字段定义的数组
     *
     * If the former, the field name will be treated as an object property name whose value will be used
     * as the field value. If the latter, the array key should be the field name while the array value should be
     * the corresponding field definition which can be either an object property name or a PHP callable
     * returning the corresponding field value. The signature of the callable should be:
     * 如果返回的是前者，属性名做字段名，属性值做字段值。如果是后者，数组的键是字段名，数组的值是可以是相应的字段定义，比如对象的属性名或者一个处理字段值
     * php回调函数。回调函数的特征如下：
     *
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
     * - fullName：字段名是fullName，字段值由`firstName` 和 `lastName` 拼接而成
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
     * @return array the list of field names or field definitions.
     * 返回值 数组 字段名或者字段定义的数组
     * @see toArray()
     */
    public function fields();

    /**
     * Returns the list of additional fields that can be returned by [[toArray()]] in addition to those listed in [[fields()]].
     * 返回除了[[fields()]]方法里边声明过的其他可以调用[[toArray()]]的字段
     *
     * This method is similar to [[fields()]] except that the list of fields declared
     * by this method are not returned by default by [[toArray()]]. Only when a field in the list
     * is explicitly requested, will it be included in the result of [[toArray()]].
     * 该方法跟[[fields()]]类似，除了该方法声明的字段不会默认被[[toArray()]]返回。这个方法中，只有显示的声明过的字段才能被添加到[[toArray()]]
     *
     * @return array the list of expandable field names or field definitions. Please refer
     * to [[fields()]] on the format of the return value.
     * 返回值 数组 可扩展的字段名的数组或字段定义数组。需要格式化返回值，请参考[[fields()]]
     * @see toArray()
     * @see fields()
     */
    public function extraFields();

    /**
     * Converts the object into an array.
     * 把对象转化成数组
     *
     * @param array $fields the fields that the output array should contain. Fields not specified
     * in [[fields()]] will be ignored. If this parameter is empty, all fields as specified in [[fields()]] will be returned.
     * 参数 数组 输出数组需要包含的字段，没有在[[fields()]]方法中指定的字段会被忽略。如果该参数为空，所有[[fields()]]方法指定的的字段都会被返回
     *
     * @param array $expand the additional fields that the output array should contain.
     * Fields not specified in [[extraFields()]] will be ignored. If this parameter is empty, no extra fields
     * will be returned.
     * 参数 数组 额外需要返回的字段。没有在[[extraFields()]]方法中指定的字段会被忽略。如果此参数为空，就不会返回额外的字段
     * @param boolean $recursive whether to recursively return array representation of embedded objects.
     * 参数 boolean 是否递归的内层嵌套对象
     * @return array the array representation of the object
     * 返回值 数组 代表对象的数组
     */
    public function toArray(array $fields = [], array $expand = [], $recursive = true);
}
