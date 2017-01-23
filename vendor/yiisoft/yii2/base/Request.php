<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;

/**
 * Request represents a request that is handled by an [[Application]].
 * Request代表了被[[Application]]处理的请求。
 *
 * @property boolean $isConsoleRequest The value indicating whether the current request is made via console.
 * 属性 boolean 代表当前请求是否来自控制台的一个值。
 *
 * @property string $scriptFile Entry script file path (processed w/ realpath()).
 * 属性 字符串 入口脚本文件的位置
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class Request extends Component
{
    private $_scriptFile;
    private $_isConsoleRequest;


    /**
     * Resolves the current request into a route and the associated parameters.
     * 把当前的请求解析成路由和相关的参数
     *
     * @return array the first element is the route, and the second is the associated parameters.
     * 返回值 数组 第一个元素是路由，第二个是相关参数。
     */
    abstract public function resolve();

    /**
     * Returns a value indicating whether the current request is made via command line
     * 返回一个值表示当前的请求是不是通过命令行发出的。
     *
     * @return boolean the value indicating whether the current request is made via console
     * 返回值 boolean 表示当前请求是否来自控制台的值。
     */
    public function getIsConsoleRequest()
    {
        return $this->_isConsoleRequest !== null ? $this->_isConsoleRequest : PHP_SAPI === 'cli';
    }

    /**
     * Sets the value indicating whether the current request is made via command line
     * 设置一个值来表示当前的请示是否是命令行发出的。
     *
     * @param boolean $value the value indicating whether the current request is made via command line
     * 参数 boolean 比欧式当前请求是否通过命令行发出的。
     */
    public function setIsConsoleRequest($value)
    {
        $this->_isConsoleRequest = $value;
    }

    /**
     * Returns entry script file path.
     * 返回入口脚本文件的路径。
     *
     * @return string entry script file path (processed w/ realpath())
     * 返回值 字符串 入口脚本文件的路径
     *
     * @throws InvalidConfigException if the entry script file path cannot be determined automatically.
     * 当入口脚本文件无法自动加载的时候抛出不合法的配置异常。
     */
    public function getScriptFile()
    {
        if ($this->_scriptFile === null) {
            if (isset($_SERVER['SCRIPT_FILENAME'])) {
                $this->setScriptFile($_SERVER['SCRIPT_FILENAME']);
            } else {
                throw new InvalidConfigException('Unable to determine the entry script file path.');
            }
        }

        return $this->_scriptFile;
    }

    /**
     * Sets the entry script file path.
     * 设置入口脚本的路径。
     *
     * The entry script file path can normally be determined based on the `SCRIPT_FILENAME` SERVER variable.
     * 通常入口脚本文件的路径可以通过SERVER变量`SCRIPT_FILENAME`来获取。
     *
     * However, for some server configurations, this may not be correct or feasible.
     * 然而，因为一些服务端配置，这样的获取可能不正确或者不可行。
     *
     * This setter is provided so that the entry script file path can be manually specified.
     * 提供该方法就可以手动的指定入口脚本文件的路径。
     *
     * @param string $value the entry script file path. This can be either a file path or a path alias.
     * 参数 字符串 入口脚本文件的路径。可以是文件路径或者路径别名。
     *
     * @throws InvalidConfigException if the provided entry script file path is invalid.
     * 当提供的入口脚本路径不合法的时候抛出不合法的配置异常。
     */
    public function setScriptFile($value)
    {
        $scriptFile = realpath(Yii::getAlias($value));
        if ($scriptFile !== false && is_file($scriptFile)) {
            $this->_scriptFile = $scriptFile;
        } else {
            throw new InvalidConfigException('Unable to determine the entry script file path.');
        }
    }
}
