<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ArrayAccessTrait provides the implementation for [[\IteratorAggregate]], [[\ArrayAccess]] and [[\Countable]].
 * ArrayAccessTrait是对[[\IteratorAggregate]], [[\ArrayAccess]] and [[\Countable]]实现
 *
 * Note that ArrayAccessTrait requires the class using it contain a property named `data` which should be an array.
 * The data will be exposed by ArrayAccessTrait to support accessing the class object like an array.
 * 请注意ArrayAccessTrait需要一个包含属性名为data类型为数组的类
 * ArrayAccessTrait将会公开data数组，使得对类的访问变得跟访问数组的一样
 *
 * @property array $data
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
trait ArrayAccessTrait
{
    /**
     * Returns an iterator for traversing the data.
     * 返回遍历数据的迭代器
     *
     * This method is required by the SPL interface [[\IteratorAggregate]].
     * [[\IteratorAggregate]]需要此方法
     *
     * It will be implicitly called when you use `foreach` to traverse the collection.
     * 当你使用foreach遍历集合的时候，他会被隐式调用
     *
     * @return \ArrayIterator an iterator for traversing the cookies in the collection.
     * 返回值 遍历集合时的迭代器
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    /**
     * Returns the number of data items.
     * 返回数组项目的个数
     *
     * This method is required by Countable interface.
     * Countable接口需要此方法
     *
     * @return integer number of data elements.
     * 返回值 整数 数据元素的个数
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * This method is required by the interface [[\ArrayAccess]].
     * [[\ArrayAccess]]接口需要调用此方法
     *
     * @param mixed $offset the offset to check on
     * 参数 混合类型 检测的偏移量
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * This method is required by the interface [[\ArrayAccess]].
     * [[\ArrayAccess]] 需要此方法
     *
     * @param integer $offset the offset to retrieve element.
     * 参数 整数 到检索元素的偏移量
     *
     * @return mixed the element at the offset, null if no element is found at the offset
     * 返回值 混合类型 处于偏移位置的元素，如果没有返回null
     */
    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    /**
     * This method is required by the interface [[\ArrayAccess]].
     * [[\ArrayAccess]]接口需要此方法
     *
     * @param integer $offset the offset to set element
     * 参数 整型 到选定元素的偏移量
     *
     * @param mixed $item the element value
     * 参数 混合类型 元素的值
     */
    public function offsetSet($offset, $item)
    {
        $this->data[$offset] = $item;
    }

    /**
     * This method is required by the interface [[\ArrayAccess]].
     * [[\ArrayAccess]]接口需要调用此方法
     *
     * @param mixed $offset the offset to unset element
     * 参数 混合 需要删除元素的偏移量
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }
}
