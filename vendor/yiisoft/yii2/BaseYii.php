<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii;

use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\base\UnknownClassException;
use yii\log\Logger;
use yii\di\Container;

/**
 * Gets the application start timestamp.
 * 获取应用启动时的时间戳
 */
defined('YII_BEGIN_TIME') or define('YII_BEGIN_TIME', microtime(true));
/**
 * This constant defines the framework installation directory.
 * 该常量定义了框架安装的目录。
 */
defined('YII2_PATH') or define('YII2_PATH', __DIR__);
/**
 * This constant defines whether the application should be in debug mode or not. Defaults to false.
 * 该常量定义了应用是否处于调试模式。
 */
defined('YII_DEBUG') or define('YII_DEBUG', false);
/**
 * This constant defines in which environment the application is running. Defaults to 'prod', meaning production environment.
 * 该常量定义了应用程序运行的环境。默认是prod，代表是生产环境。
 * You may define this constant in the bootstrap script. The value could be 'prod' (production), 'dev' (development), 'test', 'staging', etc.
 * 你可以在引导脚本里定义这个常量，可以定义为prod(生产环境)，dev(开发环境)，test，staging等
 */
defined('YII_ENV') or define('YII_ENV', 'prod');
/**
 * Whether the the application is running in production environment
 * 应用是否在生产环境下运行。
 */
defined('YII_ENV_PROD') or define('YII_ENV_PROD', YII_ENV === 'prod');
/**
 * Whether the the application is running in development environment
 * 应用是否在开发环境下运行
 */
defined('YII_ENV_DEV') or define('YII_ENV_DEV', YII_ENV === 'dev');
/**
 * Whether the the application is running in testing environment
 * 应用是否在测试环境下运行
 */
defined('YII_ENV_TEST') or define('YII_ENV_TEST', YII_ENV === 'test');

/**
 * This constant defines whether error handling should be enabled. Defaults to true.
 * 该常量定义错误处理是否开启，默认开启
 */
defined('YII_ENABLE_ERROR_HANDLER') or define('YII_ENABLE_ERROR_HANDLER', true);

/**
 * BaseYii is the core helper class for the Yii framework.
 * BaseYii是Yii框架的核心助手类
 *
 * Do not use BaseYii directly. Instead, use its child class [[\Yii]] which you can replace to
 * customize methods of BaseYii.
 * 不要直接使用BaseYii类。可以使用它的子类Yii来代替，在子类里自定义一些方法替换原有的方法即可。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class BaseYii
{
    /**
     * @var array class map used by the Yii autoloading mechanism.
     * Yii框架自动加载机制需要使用的类列表
     * The array keys are the class names (without leading backslashes), and the array values
     * are the corresponding class file paths (or path aliases). This property mainly affects
     * how [[autoload()]] works.
     * 数组的键是类名（开头没有反斜杠），数组的值相关类文件的路径（或者路径的别名）
     * @see autoload()
     */
    public static $classMap = [];
    /**
     * @var \yii\console\Application|\yii\web\Application the application instance
     * 属性 应用的实例
     */
    public static $app;
    /**
     * @var array registered path aliases
     * 属性 数组 注册的路径别名
     * @see getAlias()
     * @see setAlias()
     */
    public static $aliases = ['@yii' => __DIR__];
    /**
     * @var Container the dependency injection (DI) container used by [[createObject()]].
     * createOject方法使用的依赖注入容器
     * You may use [[Container::set()]] to set up the needed dependencies of classes and
     * their initial property values.
     * 你可以使用Container::set方法设置类需要的依赖关系和他们初始的属性值
     * @see createObject()
     * @see Container
     */
    public static $container;


    /**
     * Returns a string representing the current version of the Yii framework.
     * 返回Yii框架的版本号。
     * @return string the version of Yii framework
     * 返回值 字符串 Yii框架的版本号
     */
    public static function getVersion()
    {
        return '2.0.10-dev';
    }

    /**
     * Translates a path alias into an actual path.
     * 把路径别名转化成一个真实的路径
     *
     * The translation is done according to the following procedure:
     * 转化的步骤如下：
     *
     * 1. If the given alias does not start with '@', it is returned back without change;
     * 1. 如果提供的别名没有以@开始，不做任何处理直接返回；
     * 2. Otherwise, look for the longest registered alias that matches the beginning part
     *    of the given alias. If it exists, replace the matching part of the given alias with
     *    the corresponding registered path.
     * 2. 否则，查找匹配给定别名开头的最长注册的别名。如果存在，根据相应的注册路径替换
     * 给定的别名的匹配部分
     * 
     * 3. Throw an exception or return false, depending on the `$throwException` parameter.
     * 3 抛出异常或者返回false，取决于$throwException的参数
     *
     * For example, by default '@yii' is registered as the alias to the Yii framework directory,
     * say '/path/to/yii'. The alias '@yii/web' would then be translated into '/path/to/yii/web'.
     * 例如，默认@yii被注册为Yii框架目录的别名，就是/path/to/yii.别名@yii/web就会被转化为
     * /path/to/yii/web
     *
     * If you have registered two aliases '@foo' and '@foo/bar'. Then translating '@foo/bar/config'
     * would replace the part '@foo/bar' (instead of '@foo') with the corresponding registered path.
     * This is because the longest alias takes precedence.
     * 若果你注册了两个别名@foo和@foo/bar，转化@foo/bar/config的时候会使用@foo/bar,而不是@foo,
     * 因为最长的路径别名优先。
     *
     * However, if the alias to be translated is '@foo/barbar/config', then '@foo' will be replaced
     * instead of '@foo/bar', because '/' serves as the boundary character.
     * 还有，如果将要被转化的路径是@foo/bar/config,那么@foo将会被替换，而不是@foo/bar，
     * 因为/代表着最上一级
     *
     * Note, this method does not check if the returned path exists or not.
     * 注意，该方法不会检测返回的路径是否存在。
     *
     * @param string $alias the alias to be translated.
     * 参数 字符串 将要被转换的别名
     * @param boolean $throwException whether to throw an exception if the given alias is invalid.
     * 参数 boolean 如果给定的路径别名不合法，是否抛出异常
     * If this is false and an invalid alias is given, false will be returned by this method.
     * 如果该值为false，并且提供了不合法的别名，该方法会返回false
     * @return string|boolean the path corresponding to the alias, false if the root alias is not previously registered.
     * 返回值 字符串或boolean 根据别名产生的路径，如果根别名当前没有注册，就会返回false
     * @throws InvalidParamException if the alias is invalid while $throwException is true.
     * 当$throwException为true，并且别名不合法
     * @see setAlias()
     */
    public static function getAlias($alias, $throwException = true)
    {
        if (strncmp($alias, '@', 1)) {
            // not an alias
            //不是一个别名
            return $alias;
        }

        $pos = strpos($alias, '/');
        $root = $pos === false ? $alias : substr($alias, 0, $pos);

        if (isset(static::$aliases[$root])) {
            if (is_string(static::$aliases[$root])) {
                return $pos === false ? static::$aliases[$root] : static::$aliases[$root] . substr($alias, $pos);
            } else {
                foreach (static::$aliases[$root] as $name => $path) {
                    if (strpos($alias . '/', $name . '/') === 0) {
                        return $path . substr($alias, strlen($name));
                    }
                }
            }
        }

        if ($throwException) {
            throw new InvalidParamException("Invalid path alias: $alias");
        } else {
            return false;
        }
    }

    /**
     * Returns the root alias part of a given alias.
     * 返回给定别名的根别名部分
     * A root alias is an alias that has been registered via [[setAlias()]] previously.
     * 根别名是之前使用setAlias注册过的别名。
     * If a given alias matches multiple root aliases, the longest one will be returned.
     * 如果给定的别名匹配了多个根别名，返回最长的那一个
     * @param string $alias the alias
     * 参数 字符串 别名
     * @return string|boolean the root alias, or false if no root alias is found
     * 返回值 字符串或boolean 根别名，当根别名没有找到时返回false
     */
    public static function getRootAlias($alias)
    {
        $pos = strpos($alias, '/');
        $root = $pos === false ? $alias : substr($alias, 0, $pos);

        if (isset(static::$aliases[$root])) {
            if (is_string(static::$aliases[$root])) {
                return $root;
            } else {
                foreach (static::$aliases[$root] as $name => $path) {
                    if (strpos($alias . '/', $name . '/') === 0) {
                        return $name;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Registers a path alias.
     * 注册路径别名
     *
     * A path alias is a short name representing a long path (a file path, a URL, etc.)
     * For example, we use '@yii' as the alias of the path to the Yii framework directory.
     * 路径别名是使用一个短路径表示一个长路径（文件路径，url等）
     * 例如，我们使用@yii作为yii框架目录的别名
     *
     * A path alias must start with the character '@' so that it can be easily differentiated
     * from non-alias paths.
     * 路径别名一定要以字符@开始，跟非路径别名以示区分。
     *
     * Note that this method does not check if the given path exists or not. All it does is
     * to associate the alias with the path.
     * 注意该方法不会检测给定的路径是否存在。它所有的功能就是把路径和别名联系在一起
     *
     * Any trailing '/' and '\' characters in the given path will be trimmed.
     * 给定路径后面的/和\将会被去掉。
     *
     * @param string $alias the alias name (e.g. "@yii"). It must start with a '@' character.
     * 参数 字符串 别名（例如@yii），必须以@开头
     * It may contain the forward slash '/' which serves as boundary character when performing
     * alias translation by [[getAlias()]].
     *它可以包含/,getAlias方法转化路径的时候/可以当做分隔符
     * @param string $path the path corresponding to the alias. If this is null, the alias will
     * be removed. Trailing '/' and '\' characters will be trimmed. This can be
     * 参数 字符串 跟别名相关的路径。如果是null，将会把别名删除。会去掉最后的/和\，可以是：
     *
     * - a directory or a file path (e.g. `/tmp`, `/tmp/main.txt`)
     * - 目录或者文件路径，例如/tmp,/tmp/main.txt
     * - a URL (e.g. `http://www.yiiframework.com`)
     * - url (例如http://www.yiiframework.com )
     * - a path alias (e.g. `@yii/base`). In this case, the path alias will be converted into the
     *   actual path first by calling [[getAlias()]].
     * - 路径别名。这种情况下，路径别名首先将会被getAlias方法转化为实际路径。
     *
     * @throws InvalidParamException if $path is an invalid alias.
     * 抛出不合法参数异常， 如果别名不合法
     * @see getAlias()
     */
    public static function setAlias($alias, $path)
    {
        if (strncmp($alias, '@', 1)) {
            $alias = '@' . $alias;
        }
        $pos = strpos($alias, '/');
        $root = $pos === false ? $alias : substr($alias, 0, $pos);
        if ($path !== null) {
            $path = strncmp($path, '@', 1) ? rtrim($path, '\\/') : static::getAlias($path);
            if (!isset(static::$aliases[$root])) {
                if ($pos === false) {
                    static::$aliases[$root] = $path;
                } else {
                    static::$aliases[$root] = [$alias => $path];
                }
            } elseif (is_string(static::$aliases[$root])) {
                if ($pos === false) {
                    static::$aliases[$root] = $path;
                } else {
                    static::$aliases[$root] = [
                        $alias => $path,
                        $root => static::$aliases[$root],
                    ];
                }
            } else {
                static::$aliases[$root][$alias] = $path;
                krsort(static::$aliases[$root]);
            }
        } elseif (isset(static::$aliases[$root])) {
            if (is_array(static::$aliases[$root])) {
                unset(static::$aliases[$root][$alias]);
            } elseif ($pos === false) {
                unset(static::$aliases[$root]);
            }
        }
    }

    /**
     * Class autoload loader.
     * 自动加载类的方法
     * This method is invoked automatically when PHP sees an unknown class.
     * 当PHP遇到未知的类的时候，自动调用此方法
     * The method will attempt to include the class file according to the following procedure:
     * 该方法会根据如下步骤尝试包含类文件：
     *
     * 1. Search in [[classMap]];
     * 1. 在类列表里搜索
     * 2. If the class is namespaced (e.g. `yii\base\Component`), it will attempt
     *    to include the file associated with the corresponding path alias
     *    (e.g. `@yii/base/Component.php`);
     * 2. 如果类包含命名空间（例如 yii\base\Component），它将会尝试包含跟路径别名相关的文件
     * 例如 @yii/base/Component.php
     *
     * This autoloader allows loading classes that follow the [PSR-4 standard](http://www.php-fig.org/psr/psr-4/)
     * and have its top-level namespace or sub-namespaces defined as path aliases.
     * 该自动加载可以加载符合[PSR-4 标准的类]，并且用于路径别名定义的最高级别的命名空间或者子命名
     *
     * Example: When aliases `@yii` and `@yii/bootstrap` are defined, classes in the `yii\bootstrap` namespace
     * will be loaded using the `@yii/bootstrap` alias which points to the directory where bootstrap extension
     * files are installed and all classes from other `yii` namespaces will be loaded from the yii framework directory.
     * 例如，当别名@yii和@yii/bootstrap被定义过，在yii\bootstrap命名空间下的类将会被使用指向bootstrap扩展安装目录@yii/bootstrap别名
     * 并且yii命名空间下的其他类将会从yii框架目录加载
     *
     * Also the [guide section on autoloading](guide:concept-autoloading).
     *还有[自动加载指导部分](guide:concept-autoloading)
     *
     * @param string $className the fully qualified class name without a leading backslash "\"
     * 参数 字符串 没有\斜线开头的完整的合格的类名
     * @throws UnknownClassException if the class does not exist in the class file
     * 抛出未知类异常。 如果类在类文件中不存在
     */
    public static function autoload($className)
    {
        if (isset(static::$classMap[$className])) {
            $classFile = static::$classMap[$className];
            if ($classFile[0] === '@') {
                $classFile = static::getAlias($classFile);
            }
        } elseif (strpos($className, '\\') !== false) {
            $classFile = static::getAlias('@' . str_replace('\\', '/', $className) . '.php', false);
            if ($classFile === false || !is_file($classFile)) {
                return;
            }
        } else {
            return;
        }

        include($classFile);

        if (YII_DEBUG && !class_exists($className, false) && !interface_exists($className, false) && !trait_exists($className, false)) {
            throw new UnknownClassException("Unable to find '$className' in file: $classFile. Namespace missing?");
        }
    }

    /**
     * Creates a new object using the given configuration.
     *
     * You may view this method as an enhanced version of the `new` operator.
     * The method supports creating an object based on a class name, a configuration array or
     * an anonymous function.
     *
     * Below are some usage examples:
     *
     * ```php
     * // create an object using a class name
     * $object = Yii::createObject('yii\db\Connection');
     *
     * // create an object using a configuration array
     * $object = Yii::createObject([
     *     'class' => 'yii\db\Connection',
     *     'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
     *     'username' => 'root',
     *     'password' => '',
     *     'charset' => 'utf8',
     * ]);
     *
     * // create an object with two constructor parameters
     * $object = \Yii::createObject('MyClass', [$param1, $param2]);
     * ```
     *
     * Using [[\yii\di\Container|dependency injection container]], this method can also identify
     * dependent objects, instantiate them and inject them into the newly created object.
     *
     * @param string|array|callable $type the object type. This can be specified in one of the following forms:
     *
     * - a string: representing the class name of the object to be created
     * - a configuration array: the array must contain a `class` element which is treated as the object class,
     *   and the rest of the name-value pairs will be used to initialize the corresponding object properties
     * - a PHP callable: either an anonymous function or an array representing a class method (`[$class or $object, $method]`).
     *   The callable should return a new instance of the object being created.
     *
     * @param array $params the constructor parameters
     * @return object the created object
     * @throws InvalidConfigException if the configuration is invalid.
     * @see \yii\di\Container
     */
    public static function createObject($type, array $params = [])
    {
        if (is_string($type)) {
            return static::$container->get($type, $params);
        } elseif (is_array($type) && isset($type['class'])) {
            $class = $type['class'];
            unset($type['class']);
            return static::$container->get($class, $params, $type);
        } elseif (is_callable($type, true)) {
            return static::$container->invoke($type, $params);
        } elseif (is_array($type)) {
            throw new InvalidConfigException('Object configuration must be an array containing a "class" element.');
        } else {
            throw new InvalidConfigException('Unsupported configuration type: ' . gettype($type));
        }
    }

    private static $_logger;

    /**
     * @return Logger message logger
     * 返回值 信息日志
     */
    public static function getLogger()
    {
        if (self::$_logger !== null) {
            return self::$_logger;
        } else {
            return self::$_logger = static::createObject('yii\log\Logger');
        }
    }

    /**
     * Sets the logger object.
     * @param Logger $logger the logger object.
     */
    public static function setLogger($logger)
    {
        self::$_logger = $logger;
    }

    /**
     * Logs a trace message.
     * Trace messages are logged mainly for development purpose to see
     * the execution work flow of some code.
     * @param string $message the message to be logged.
     * @param string $category the category of the message.
     */
    public static function trace($message, $category = 'application')
    {
        if (YII_DEBUG) {
            static::getLogger()->log($message, Logger::LEVEL_TRACE, $category);
        }
    }

    /**
     * Logs an error message.
     * An error message is typically logged when an unrecoverable error occurs
     * during the execution of an application.
     * @param string $message the message to be logged.
     * @param string $category the category of the message.
     */
    public static function error($message, $category = 'application')
    {
        static::getLogger()->log($message, Logger::LEVEL_ERROR, $category);
    }

    /**
     * Logs a warning message.
     * A warning message is typically logged when an error occurs while the execution
     * can still continue.
     * @param string $message the message to be logged.
     * @param string $category the category of the message.
     */
    public static function warning($message, $category = 'application')
    {
        static::getLogger()->log($message, Logger::LEVEL_WARNING, $category);
    }

    /**
     * Logs an informative message.
     * An informative message is typically logged by an application to keep record of
     * something important (e.g. an administrator logs in).
     * @param string $message the message to be logged.
     * @param string $category the category of the message.
     */
    public static function info($message, $category = 'application')
    {
        static::getLogger()->log($message, Logger::LEVEL_INFO, $category);
    }

    /**
     * Marks the beginning of a code block for profiling.
     * This has to be matched with a call to [[endProfile]] with the same category name.
     * The begin- and end- calls must also be properly nested. For example,
     *
     * ```php
     * \Yii::beginProfile('block1');
     * // some code to be profiled
     *     \Yii::beginProfile('block2');
     *     // some other code to be profiled
     *     \Yii::endProfile('block2');
     * \Yii::endProfile('block1');
     * ```
     * @param string $token token for the code block
     * @param string $category the category of this log message
     * @see endProfile()
     */
    public static function beginProfile($token, $category = 'application')
    {
        static::getLogger()->log($token, Logger::LEVEL_PROFILE_BEGIN, $category);
    }

    /**
     * Marks the end of a code block for profiling.
     * This has to be matched with a previous call to [[beginProfile]] with the same category name.
     * @param string $token token for the code block
     * @param string $category the category of this log message
     * @see beginProfile()
     */
    public static function endProfile($token, $category = 'application')
    {
        static::getLogger()->log($token, Logger::LEVEL_PROFILE_END, $category);
    }

    /**
     * Returns an HTML hyperlink that can be displayed on your Web page showing "Powered by Yii Framework" information.
     * @return string an HTML hyperlink that can be displayed on your Web page showing "Powered by Yii Framework" information
     */
    public static function powered()
    {
        return \Yii::t('yii', 'Powered by {yii}', [
            'yii' => '<a href="http://www.yiiframework.com/" rel="external">' . \Yii::t('yii',
                    'Yii Framework') . '</a>'
        ]);
    }

    /**
     * Translates a message to the specified language.
     *
     * This is a shortcut method of [[\yii\i18n\I18N::translate()]].
     *
     * The translation will be conducted according to the message category and the target language will be used.
     *
     * You can add parameters to a translation message that will be substituted with the corresponding value after
     * translation. The format for this is to use curly brackets around the parameter name as you can see in the following example:
     *
     * ```php
     * $username = 'Alexander';
     * echo \Yii::t('app', 'Hello, {username}!', ['username' => $username]);
     * ```
     *
     * Further formatting of message parameters is supported using the [PHP intl extensions](http://www.php.net/manual/en/intro.intl.php)
     * message formatter. See [[\yii\i18n\I18N::translate()]] for more details.
     *
     * @param string $category the message category.
     * @param string $message the message to be translated.
     * @param array $params the parameters that will be used to replace the corresponding placeholders in the message.
     * @param string $language the language code (e.g. `en-US`, `en`). If this is null, the current
     * [[\yii\base\Application::language|application language]] will be used.
     * @return string the translated message.
     */
    public static function t($category, $message, $params = [], $language = null)
    {
        if (static::$app !== null) {
            return static::$app->getI18n()->translate($category, $message, $params, $language ?: static::$app->language);
        } else {
            $p = [];
            foreach ((array) $params as $name => $value) {
                $p['{' . $name . '}'] = $value;
            }

            return ($p === []) ? $message : strtr($message, $p);
        }
    }

    /**
     * Configures an object with the initial property values.
     * @param object $object the object to be configured
     * @param array $properties the property initial values given in terms of name-value pairs.
     * @return object the object itself
     */
    public static function configure($object, $properties)
    {
        foreach ($properties as $name => $value) {
            $object->$name = $value;
        }

        return $object;
    }

    /**
     * Returns the public member variables of an object.
     * This method is provided such that we can get the public member variables of an object.
     * It is different from "get_object_vars()" because the latter will return private
     * and protected variables if it is called within the object itself.
     * @param object $object the object to be handled
     * @return array the public member variables of the object
     */
    public static function getObjectVars($object)
    {
        return get_object_vars($object);
    }
}
