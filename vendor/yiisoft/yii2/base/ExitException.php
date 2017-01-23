<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ExitException represents a normal termination of an application.
 * 退出异常表示一个应用的终结
 *
 * Do not catch ExitException. Yii will handle this exception to terminate the application gracefully.
 * 不必捕捉退出异常，Yii会自动处理该异常，并优雅地终止应用
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ExitException extends \Exception
{
    /**
     * @var integer the exit status code
     * 变量 整型 退出状态码
     */
    public $statusCode;


    /**
     * Constructor.
     * 构造函数
     *
     * @param integer $status the exit status code
     * 参数 整型 退出状态吗
     *
     * @param string $message error message
     * 参数 字符串 错误信息
     *
     * @param integer $code error code
     * 参数 整型 错误代码
     *
     * @param \Exception $previous The previous exception used for the exception chaining.
     * 参数 用于异常链的上一个异常
     */
    public function __construct($status = 0, $message = null, $code = 0, \Exception $previous = null)
    {
        $this->statusCode = $status;
        parent::__construct($message, $code, $previous);
    }
}
