<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ViewNotFoundException represents an exception caused by view file not found.
 * ViewNotFoundException表示因为视图文件没有找到而发生的异常。
 *
 * @author Alexander Makarov
 * @since 2.0.10
 */
class ViewNotFoundException extends InvalidParamException
{
    /**
     * @return string the user-friendly name of this exception
     * 返回值 字符串 对用户友好的该异常内容。
     */
    public function getName()
    {
        return 'View not Found';
    }
}
