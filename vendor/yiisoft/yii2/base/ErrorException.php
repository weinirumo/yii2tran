<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;

/**
 * ErrorException represents a PHP error.
 * ErrorException代表php报错
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class ErrorException extends \ErrorException
{
    /**
     * This constant represents a fatal error in the HHVM engine.
     * 该常量表示在HHVM引擎下的致命错误
     *
     * PHP Zend runtime won't call the error handler on fatals, HHVM will, with an error code of 16777217
     * PHP的zend引擎运行时不会调用该错误处理，HHVM会调用，报错代码是16777217
     * We will handle fatal error a bit different on HHVM.
     * 在HHVM上处理致命错误会有少许不同
     * @see https://github.com/facebook/hhvm/blob/master/hphp/runtime/base/runtime-error.h#L62
     * @since 2.0.6
     */
    const E_HHVM_FATAL_ERROR = 16777217; // E_ERROR | (1 << 24)


    /**
     * Constructs the exception.
     * 构造函数
     * @link http://php.net/manual/en/errorexception.construct.php
     * @param $message [optional]
     * @param $code [optional]
     * @param $severity [optional]
     * @param $filename [optional]
     * @param $lineno [optional]
     * @param $previous [optional]
     */
    public function __construct($message = '', $code = 0, $severity = 1, $filename = __FILE__, $lineno = __LINE__, \Exception $previous = null)
    {
        parent::__construct($message, $code, $severity, $filename, $lineno, $previous);

        if (function_exists('xdebug_get_function_stack')) {
            // XDebug trace can't be modified and used directly with PHP 7
            // Xdebug追踪在php7版本下不能更改和直接使用
            // @see https://github.com/yiisoft/yii2/pull/11723
            $xDebugTrace = array_slice(array_reverse(xdebug_get_function_stack()), 3, -1);
            $trace = [];
            foreach ($xDebugTrace as $frame) {
                if (!isset($frame['function'])) {
                    $frame['function'] = 'unknown';
                }

                // XDebug < 2.1.1: http://bugs.xdebug.org/view.php?id=695
                if (!isset($frame['type']) || $frame['type'] === 'static') {
                    $frame['type'] = '::';
                } elseif ($frame['type'] === 'dynamic') {
                    $frame['type'] = '->';
                }

                // XDebug has a different key name
                // XDebug 有一个不同的键名
                if (isset($frame['params']) && !isset($frame['args'])) {
                    $frame['args'] = $frame['params'];
                }
                $trace[] = $frame;
            }

            $ref = new \ReflectionProperty('Exception', 'trace');
            $ref->setAccessible(true);
            $ref->setValue($this, $trace);
        }
    }

    /**
     * Returns if error is one of fatal type.
     * 返回错误是不是致命错误
     *
     * @param array $error error got from error_get_last()
     * 参数 数组 error_get_last()得到的错误
     * @return boolean if error is one of fatal type
     * 返回值 boolean 错误书不是致命类型的
     */
    public static function isFatalError($error)
    {
        return isset($error['type']) && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING, self::E_HHVM_FATAL_ERROR]);
    }

    /**
     * @return string the user-friendly name of this exception
     * 返回值 字符串 用户友好的异常信息
     */
    public function getName()
    {
        static $names = [
            E_COMPILE_ERROR => 'PHP Compile Error',
            E_COMPILE_WARNING => 'PHP Compile Warning',
            E_CORE_ERROR => 'PHP Core Error',
            E_CORE_WARNING => 'PHP Core Warning',
            E_DEPRECATED => 'PHP Deprecated Warning',
            E_ERROR => 'PHP Fatal Error',
            E_NOTICE => 'PHP Notice',
            E_PARSE => 'PHP Parse Error',
            E_RECOVERABLE_ERROR => 'PHP Recoverable Error',
            E_STRICT => 'PHP Strict Warning',
            E_USER_DEPRECATED => 'PHP User Deprecated Warning',
            E_USER_ERROR => 'PHP User Error',
            E_USER_NOTICE => 'PHP User Notice',
            E_USER_WARNING => 'PHP User Warning',
            E_WARNING => 'PHP Warning',
            self::E_HHVM_FATAL_ERROR => 'HHVM Fatal Error',
        ];

        return isset($names[$this->getCode()]) ? $names[$this->getCode()] : 'Error';
    }
}
