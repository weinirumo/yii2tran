<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;

/**
 * Application is the base class for all application classes.
 * Application类是所有应用类的基类
 *
 * @property \yii\web\AssetManager $assetManager The asset manager application component. This property is
 * read-only.
 * 属性 \yii\web\AssetManager 静态资源管理应用组件，该属性只读
 *
 * @property \yii\rbac\ManagerInterface $authManager The auth manager application component. Null is returned
 * if auth manager is not configured. This property is read-only.
 * 属性 \yii\rbac\ManagerInterface 权限管理应用组件，如果权限控制组件没有配置，返回值为null。该属性只读
 *
 * @property string $basePath The root directory of the application.
 * 属性 字符串 应用的根目录
 *
 * @property \yii\caching\Cache $cache The cache application component. Null if the component is not enabled.
 * This property is read-only.
 * 属性 \yii\caching\Cache $cache 缓存应用组件。如果该组件没有开启，将会返回null，该属性只读
 *
 * @property \yii\db\Connection $db The database connection. This property is read-only.
 * 属性 \yii\db\Connection $db 数据库连接。 该属性只读
 *
 * @property \yii\web\ErrorHandler|\yii\console\ErrorHandler $errorHandler The error handler application
 * component. This property is read-only.
 * 属性 \yii\web\ErrorHandler|\yii\console\ErrorHandler $errorHandler 错误处理组件，该属性只读
 *
 * @property \yii\i18n\Formatter $formatter The formatter application component. This property is read-only.
 * 属性 \yii\i18n\Formatter $formatter 格式化应用组件，该属性只读
 *
 * @property \yii\i18n\I18N $i18n The internationalization application component. This property is read-only.
 * 属性 \yii\i18n\I18N $i18n 国际化应用组件，该属性只读（数一下internationlization中间有几个字母，就知道什么是i18n了）
 *
 * @property \yii\log\Dispatcher $log The log dispatcher application component. This property is read-only.
 * 属性 $log 日志调度应用组件，该属性只读
 *
 * @property \yii\mail\MailerInterface $mailer The mailer application component. This property is read-only.
 * 属性 $mailer 邮件发送应用组件。该属性只读
 *
 * @property \yii\web\Request|\yii\console\Request $request The request component. This property is read-only.
 * 属性 $request 请求组件，该属性只读
 *
 * @property \yii\web\Response|\yii\console\Response $response The response component. This property is
 * read-only.
 * 属性 $response 响应组件，该属性只读
 *
 * @property string $runtimePath The directory that stores runtime files. Defaults to the "runtime"
 * subdirectory under [[basePath]].
 * 属性 字符串 $runtimePath 保存运行时产生文件的目录。默认保存到basePath下的runtime子目录里
 *
 * @property \yii\base\Security $security The security application component. This property is read-only.
 * 属性 $security 安全应用组件， 该属性只读
 *
 * @property string $timeZone The time zone used by this application.
 * 属性 字符串 $timeZone 应用使用的时区
 *
 * @property string $uniqueId The unique ID of the module. This property is read-only.
 * 属性 字符串 模块的唯一id，该属性只读
 *
 * @property \yii\web\UrlManager $urlManager The URL manager for this application. This property is read-only.
 * 属性 $urlManager 该应用的url管理组件，该属性只读
 *
 * @property string $vendorPath The directory that stores vendor files. Defaults to "vendor" directory under
 * [[basePath]].
 * 属性 字符串 $vendorPath 保存框架和其他扩展的目录。默认是basePath下的vendor目录
 *
 * @property View|\yii\web\View $view The view application component that is used to render various view
 * files. This property is read-only.
 * 属性 $view 用来渲染很多页面文件的视图应用组件，该属性只读
 *
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class Application extends Module
{
    /**
     * @event Event an event raised before the application starts to handle a request.
     * 事件在应用开始处理请求之前调用
     */
    const EVENT_BEFORE_REQUEST = 'beforeRequest';
    /**
     * @event Event an event raised after the application successfully handles a request (before the response is sent out).
     * 事件在应用成功处理请求以后，发送响应之前调用
     */
    const EVENT_AFTER_REQUEST = 'afterRequest';
    /**
     * Application state used by [[state]]: application just started.
     * 应用的状态 0 表示应用刚开启
     */
    const STATE_BEGIN = 0;
    /**
     * Application state used by [[state]]: application is initializing.
     * 1表示应用正在初始化
     */
    const STATE_INIT = 1;
    /**
     * Application state used by [[state]]: application is triggering [[EVENT_BEFORE_REQUEST]].
     * 2表示应用正在触发请求前的事件
     */
    const STATE_BEFORE_REQUEST = 2;
    /**
     * Application state used by [[state]]: application is handling the request.
     * 3表示应用正在处理请求
     */
    const STATE_HANDLING_REQUEST = 3;
    /**
     * Application state used by [[state]]: application is triggering [[EVENT_AFTER_REQUEST]]..
     * 4表示应用触发了请求执行后的事件
     */
    const STATE_AFTER_REQUEST = 4;
    /**
     * Application state used by [[state]]: application is about to send response.
     * 5表示应用准备发送响应
     */
    const STATE_SENDING_RESPONSE = 5;
    /**
     * Application state used by [[state]]: application has ended.
     * 6表示响应已经发出
     */
    const STATE_END = 6;

    /**
     * @var string the namespace that controller classes are located in.
     * 属性 字符串 控制器类的命名空间
     * This namespace will be used to load controller classes by prepending it to the controller class name.
     * The default namespace is `app\controllers`.
     * 该命名空间将会在加载控制器的时候伪装类名，默认的命名空间是app\controllers
     *
     *
     * Please refer to the [guide about class autoloading](guide:concept-autoloading.md) for more details.
     * 要了解更多，请参考类的自动加载
     */
    public $controllerNamespace = 'app\\controllers';
    /**
     * @var string the application name.
     * 属性 字符串 应用的名称
     */
    public $name = 'My Application';
    /**
     * @var string the version of this application.
     * 属性 字符串 应用版本号
     */
    public $version = '1.0';
    /**
     * @var string the charset currently used for the application.
     * 属性 字符串 当前应用的字符集
     */
    public $charset = 'UTF-8';
    /**
     * @var string the language that is meant to be used for end users. It is recommended that you
     * use [IETF language tags](http://en.wikipedia.org/wiki/IETF_language_tag). For example, `en` stands
     * for English, while `en-US` stands for English (United States).
     * 属性 字符串 将要显示给终端用户的语言。推荐您采用IETE语言标识。例如，en代表英语，en-US代表美式英语
     * @see sourceLanguage
     */
    public $language = 'en-US';
    /**
     * @var string the language that the application is written in. This mainly refers to
     * the language that the messages and view files are written in.
     * 属性 字符串 应用采用何种语言开发，主要是指信息和师徒文件采用的语言
     * @see language
     */
    public $sourceLanguage = 'en-US';
    /**
     * @var Controller the currently active controller instance
     * 属性 控制器 当前实例化的控制器实例
     */
    public $controller;
    /**
     * @var string|boolean the layout that should be applied for views in this application. Defaults to 'main'.
     * If this is false, layout will be disabled.
     * 属性 字符串 或者 boolean值，指的是该应用采用的布局文件，默认是main，如果设置为false，布局将不可用
     */
    public $layout = 'main';
    /**
     * @var string the requested route
     * 属性 请求的路由
     */
    public $requestedRoute;
    /**
     * @var Action the requested Action. If null, it means the request cannot be resolved into an action.
     * 属性 动作，请求的动作，如果为null，意味着请求不能被动作处理
     */
    public $requestedAction;
    /**
     * @var array the parameters supplied to the requested action.
     * 属性 数组 提供给动作的参数
     */
    public $requestedParams;
    /**
     * @var array list of installed Yii extensions. Each array element represents a single extension
     * with the following structure:
     * 属性 数组 已经安装的yii扩展，每一个数组元素都代表着一个单独的扩展，扩展结构如下：
     *
     *
     * ```php
     * [
     *     'name' => 'extension name',
     *     'version' => 'version number',
     *     'bootstrap' => 'BootstrapClassName',  // optional, may also be a configuration array 可选，也可以是一个配置数组
     *     'alias' => [
     *         '@alias1' => 'to/path1',
     *         '@alias2' => 'to/path2',
     *     ],
     * ]
     * ```
     *
     * The "bootstrap" class listed above will be instantiated during the application
     * [[bootstrap()|bootstrapping process]]. If the class implements [[BootstrapInterface]],
     * its [[BootstrapInterface::bootstrap()|bootstrap()]] method will be also be called.
     * 上例中bootstrap类会在应用执行bootstrap的时候被安装。如果累实现了bootstrap接口，纳闷bootstrap方法也会被调用
     *
     * If not set explicitly in the application config, this property will be populated with the contents of
     * `@vendor/yiisoft/extensions.php`.
     * 如果没有在应用配置中明确指定，该属性将会默认采用@vendor/yiisoft/extensions.php的内容
     *
     */
    public $extensions;
    /**
     * @var array list of components that should be run during the application [[bootstrap()|bootstrapping process]].
     * 属性 数组 在应用执行过程中，将要执行的组件
     *
     * Each component may be specified in one of the following formats:
     * 每个组件都可以采用下边的格式指定：
     *
     * - an application component ID as specified via [[components]].
     * - 通过component指定的应用组件id
     * - a module ID as specified via [[modules]].
     * - 在模块中指定的模块id
     * - a class name.
     * - 一个类名
     * - a configuration array.
     * - 一个配置数组
     *
     * During the bootstrapping process, each component will be instantiated. If the component class
     * implements [[BootstrapInterface]], its [[BootstrapInterface::bootstrap()|bootstrap()]] method
     * will be also be called.
     * 在应用自举的时候，每一个组件都会被实例化。如果组件的类实现bootstrap接口，他的bootstrap方法也会被调用
     *
     */
    public $bootstrap = [];
    /**
     * @var integer the current application state during a request handling life cycle.
     * This property is managed by the application. Do not modify this property.
     * 属性 整数 当前应用在处理请求生命周期中的状态值，该属性由应用自动管理，别改这个属性
     *
     */
    public $state;
    /**
     * @var array list of loaded modules indexed by their class names.
     * 属性 数组 已经加载的模块类名为索引的模块名
     */
    public $loadedModules = [];


    /**
     * Constructor. 构造函数
     * @param array $config name-value pairs that will be used to initialize the object properties.
     * Note that the configuration must contain both [[id]] and [[basePath]].
     * 参数 数组 实例化类属性时候用到的键值对
     *
     * @throws InvalidConfigException if either [[id]] or [[basePath]] configuration is missing.
     * 当id或者basepath配置丢失抛出异常
     */
    public function __construct($config = [])
    {
        Yii::$app = $this;
        static::setInstance($this);

        $this->state = self::STATE_BEGIN;

        $this->preInit($config);

        $this->registerErrorHandler($config);

        Component::__construct($config);
    }

    /**
     * Pre-initializes the application.
     * 预初始化应用
     *
     * This method is called at the beginning of the application constructor.
     * 这个方法会在应用构造函数执行之初进行调用
     *
     * It initializes several important application properties.
     * 他会实例化几个重要的应用属性
     *
     * If you override this method, please make sure you call the parent implementation.
     * 如果你要重写此方法，务必要调用父类的实现
     *
     * @param array $config the application configuration
     * 参数 数组 应用配置项
     *
     * @throws InvalidConfigException if either [[id]] or [[basePath]] configuration is missing.
     * 配置丢失时抛出
     *
     */
    public function preInit(&$config)
    {
        if (!isset($config['id'])) {
            throw new InvalidConfigException('The "id" configuration for the Application is required.');
        }
        if (isset($config['basePath'])) {
            $this->setBasePath($config['basePath']);
            unset($config['basePath']);
        } else {
            throw new InvalidConfigException('The "basePath" configuration for the Application is required.');
        }

        if (isset($config['vendorPath'])) {
            $this->setVendorPath($config['vendorPath']);
            unset($config['vendorPath']);
        } else {
            // set "@vendor"
            $this->getVendorPath();
        }
        if (isset($config['runtimePath'])) {
            $this->setRuntimePath($config['runtimePath']);
            unset($config['runtimePath']);
        } else {
            // set "@runtime"
            $this->getRuntimePath();
        }

        if (isset($config['timeZone'])) {
            $this->setTimeZone($config['timeZone']);
            unset($config['timeZone']);
        } elseif (!ini_get('date.timezone')) {
            $this->setTimeZone('UTC');
        }

        // merge core components with custom components
        // 合并核心组件和自定义组件
        foreach ($this->coreComponents() as $id => $component) {
            if (!isset($config['components'][$id])) {
                $config['components'][$id] = $component;
            } elseif (is_array($config['components'][$id]) && !isset($config['components'][$id]['class'])) {
                $config['components'][$id]['class'] = $component['class'];
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->state = self::STATE_INIT;
        $this->bootstrap();
    }

    /**
     * Initializes extensions and executes bootstrap components.
     * 初始化扩展，执行自举组件
     * This method is called by [[init()]] after the application has been fully configured.
     * 该方法会在init执行完全配置以后调用
     * If you override this method, make sure you also call the parent implementation.
     * 如果你重写了此方法，确保调用了父类的实现
     *
     */
    protected function bootstrap()
    {
        if ($this->extensions === null) {
            $file = Yii::getAlias('@vendor/yiisoft/extensions.php');
            $this->extensions = is_file($file) ? include($file) : [];
        }
        foreach ($this->extensions as $extension) {
            if (!empty($extension['alias'])) {
                foreach ($extension['alias'] as $name => $path) {
                    Yii::setAlias($name, $path);
                }
            }
            if (isset($extension['bootstrap'])) {
                $component = Yii::createObject($extension['bootstrap']);
                if ($component instanceof BootstrapInterface) {
                    Yii::trace('Bootstrap with ' . get_class($component) . '::bootstrap()', __METHOD__);
                    $component->bootstrap($this);
                } else {
                    Yii::trace('Bootstrap with ' . get_class($component), __METHOD__);
                }
            }
        }

        foreach ($this->bootstrap as $class) {
            $component = null;
            if (is_string($class)) {
                if ($this->has($class)) {
                    $component = $this->get($class);
                } elseif ($this->hasModule($class)) {
                    $component = $this->getModule($class);
                } elseif (strpos($class, '\\') === false) {
                    throw new InvalidConfigException("Unknown bootstrapping component ID: $class");
                }
            }
            if (!isset($component)) {
                $component = Yii::createObject($class);
            }

            if ($component instanceof BootstrapInterface) {
                Yii::trace('Bootstrap with ' . get_class($component) . '::bootstrap()', __METHOD__);
                $component->bootstrap($this);
            } else {
                Yii::trace('Bootstrap with ' . get_class($component), __METHOD__);
            }
        }
    }

    /**
     * Registers the errorHandler component as a PHP error handler.
     * 注册错误处理组件，用来处理php报错
     * @param array $config application config
     * 参数 数组 应用配置
     *
     */
    protected function registerErrorHandler(&$config)
    {
        if (YII_ENABLE_ERROR_HANDLER) {
            if (!isset($config['components']['errorHandler']['class'])) {
                echo "Error: no errorHandler component is configured.\n";
                exit(1);
            }
            $this->set('errorHandler', $config['components']['errorHandler']);
            unset($config['components']['errorHandler']);
            $this->getErrorHandler()->register();
        }
    }

    /**
     * Returns an ID that uniquely identifies this module among all modules within the current application.
     * Since this is an application instance, it will always return an empty string.
     * 返回当前应用中，当前模块区别其他模块的唯一id，因为这是一个应用的实例，所以它总是返回空字符串
     *
     * @return string the unique ID of the module.
     * 返回值 字符串 模块的唯一id
     */
    public function getUniqueId()
    {
        return '';
    }

    /**
     * Sets the root directory of the application and the @app alias.
     * This method can only be invoked at the beginning of the constructor.
     * 设置应用的根目录和app别名，该方法只能在构造函数的开始调用
     *
     * @param string $path the root directory of the application.
     * 参数 字符串 应用根目录
     *
     * @property string the root directory of the application.
     * 属性 字符串 应用的根路径
     *
     * @throws InvalidParamException if the directory does not exist.
     * 当目录不存在的时候抛出异常
     *
     */
    public function setBasePath($path)
    {
        parent::setBasePath($path);
        Yii::setAlias('@app', $this->getBasePath());
    }

    /**
     * Runs the application.
     * 运行该应用
     * This is the main entrance of an application.
     * 这个是一个应用的主要入口方法
     * @return integer the exit status (0 means normal, non-zero values mean abnormal)
     * 返回值 退出时的状态 0表示正常，非零的数字表示不正常
     *
     */
    public function run()
    {
        try {

            $this->state = self::STATE_BEFORE_REQUEST;
            $this->trigger(self::EVENT_BEFORE_REQUEST);

            $this->state = self::STATE_HANDLING_REQUEST;
            $response = $this->handleRequest($this->getRequest());

            $this->state = self::STATE_AFTER_REQUEST;
            $this->trigger(self::EVENT_AFTER_REQUEST);

            $this->state = self::STATE_SENDING_RESPONSE;
            $response->send();

            $this->state = self::STATE_END;

            return $response->exitStatus;

        } catch (ExitException $e) {

            $this->end($e->statusCode, isset($response) ? $response : null);
            return $e->statusCode;

        }
    }

    /**
     * Handles the specified request.
     * 处理指定的请求
     *
     * This method should return an instance of [[Response]] or its child class
     * which represents the handling result of the request.
     * 该方法会返回一个请求或其子类的实例，该实例表示处理请求的结果
     *
     * @param Request $request the request to be handled
     * 参数 请求 将要被处理的请求
     * @return Response the resulting response
     * 返回值 响应结果
     *
     */
    abstract public function handleRequest($request);

    private $_runtimePath;

    /**
     * Returns the directory that stores runtime files.
     * 返回保存运行时产生的文件
     * @return string the directory that stores runtime files.
     * 返回值 字符串 保存运行时产生的文件的目录名
     *
     * Defaults to the "runtime" subdirectory under [[basePath]].
     * 默认的runtime目录在basePath下
     */
    public function getRuntimePath()
    {
        if ($this->_runtimePath === null) {
            $this->setRuntimePath($this->getBasePath() . DIRECTORY_SEPARATOR . 'runtime');
        }

        return $this->_runtimePath;
    }

    /**
     * Sets the directory that stores runtime files.
     * 设置runtime文件的位置
     * @param string $path the directory that stores runtime files.
     * 参数 保存运行文件的目录
     *
     */
    public function setRuntimePath($path)
    {
        $this->_runtimePath = Yii::getAlias($path);
        Yii::setAlias('@runtime', $this->_runtimePath);
    }

    private $_vendorPath;

    /**
     * Returns the directory that stores vendor files.
     * 返回yii和扩展的保存目录
     * @return string the directory that stores vendor files.
     * 返回值 保存yii和扩展的目录
     * Defaults to "vendor" directory under [[basePath]].
     * 默认值为basePath下的vendor目录
     */
    public function getVendorPath()
    {
        if ($this->_vendorPath === null) {
            $this->setVendorPath($this->getBasePath() . DIRECTORY_SEPARATOR . 'vendor');
        }

        return $this->_vendorPath;
    }

    /**
     * Sets the directory that stores vendor files.
     * 设置vendor的文件路径
     * @param string $path the directory that stores vendor files.
     * 参数 保存vendor的文件目录
     */
    public function setVendorPath($path)
    {
        $this->_vendorPath = Yii::getAlias($path);
        Yii::setAlias('@vendor', $this->_vendorPath);
        Yii::setAlias('@bower', $this->_vendorPath . DIRECTORY_SEPARATOR . 'bower');
        Yii::setAlias('@npm', $this->_vendorPath . DIRECTORY_SEPARATOR . 'npm');
    }

    /**
     * Returns the time zone used by this application.
     * 返回当前应用的时区
     * This is a simple wrapper of PHP function date_default_timezone_get().
     * 只是简单的对PHP原生函数date_default_timezone_get进行了包装
     *
     * If time zone is not configured in php.ini or application config,
     * it will be set to UTC by default.
     * 如果php.ini没有配置时区，那么采用默认时区UTC
     * @return string the time zone used by this application.
     * 返回值 应用程序使用的时区
     * @see http://php.net/manual/en/function.date-default-timezone-get.php
     */
    public function getTimeZone()
    {
        return date_default_timezone_get();
    }

    /**
     * Sets the time zone used by this application.
     * 为当前应用设置时区
     * This is a simple wrapper of PHP function date_default_timezone_set().
     * 该方法只是简单包装了一下php函数date_default_timezone_set
     * Refer to the [php manual](http://www.php.net/manual/en/timezones.php) for available timezones.
     * 到上边那个url去查看可用的时区
     * @param string $value the time zone used by this application.
     * 参数 字符串 应用采用的时区
     * @see http://php.net/manual/en/function.date-default-timezone-set.php
     */
    public function setTimeZone($value)
    {
        date_default_timezone_set($value);
    }

    /**
     * Returns the database connection component.
     * 返回数据库连接组件
     * @return \yii\db\Connection the database connection.
     * 返回值 数据库连接
     */
    public function getDb()
    {
        return $this->get('db');
    }

    /**
     * Returns the log dispatcher component.
     * 返回日志调度组件
     * @return \yii\log\Dispatcher the log dispatcher application component.
     * 返回值 日子调度应用组件
     */
    public function getLog()
    {
        return $this->get('log');
    }

    /**
     * Returns the error handler component.
     * 返回错误处理组件
     * @return \yii\web\ErrorHandler|\yii\console\ErrorHandler the error handler application component.
     * 返回值 错误处理组件
     */
    public function getErrorHandler()
    {
        return $this->get('errorHandler');
    }

    /**
     * Returns the cache component.
     * 返回缓存组件
     * @return \yii\caching\Cache the cache application component. Null if the component is not enabled.
     * 返回值 缓存组件 如果没有开启返回null
     */
    public function getCache()
    {
        return $this->get('cache', false);
    }

    /**
     * Returns the formatter component.
     * 返回格式化组件
     * @return \yii\i18n\Formatter the formatter application component.
     * 返回值 格式化应用组件
     */
    public function getFormatter()
    {
        return $this->get('formatter');
    }

    /**
     * Returns the request component.
     * 返回请求组件
     * @return \yii\web\Request|\yii\console\Request the request component.
     * 返回值 请求组件
     */
    public function getRequest()
    {
        return $this->get('request');
    }

    /**
     * Returns the response component.
     * 返回响应组件
     * @return \yii\web\Response|\yii\console\Response the response component.
     * 返回组 响应组件
     */
    public function getResponse()
    {
        return $this->get('response');
    }

    /**
     * Returns the view object.
     * 返回view对象
     * @return View|\yii\web\View the view application component that is used to render various view files.
     * 返回值 用于渲染视图页面的视图组件
     */
    public function getView()
    {
        return $this->get('view');
    }

    /**
     * Returns the URL manager for this application.
     * 返回url管理组件
     * @return \yii\web\UrlManager the URL manager for this application.
     * 返回值 url管理组件
     */
    public function getUrlManager()
    {
        return $this->get('urlManager');
    }

    /**
     * Returns the internationalization (i18n) component
     * 返回国际化组件
     * @return \yii\i18n\I18N the internationalization application component.
     * 返回值 国际化组件
     */
    public function getI18n()
    {
        return $this->get('i18n');
    }

    /**
     * Returns the mailer component.
     * 返回邮件发送组件
     * @return \yii\mail\MailerInterface the mailer application component.
     * 返回 邮件发送组件
     */
    public function getMailer()
    {
        return $this->get('mailer');
    }

    /**
     * Returns the auth manager for this application.
     * 返回权限管理组件
     * @return \yii\rbac\ManagerInterface the auth manager application component.
     * Null is returned if auth manager is not configured.
     * 返回值 权限管理组件 当权限管理没有配置的时候会返回null
     */
    public function getAuthManager()
    {
        return $this->get('authManager', false);
    }

    /**
     * Returns the asset manager.
     * 返回资源管理组件
     * @return \yii\web\AssetManager the asset manager application component.
     * 返回值 资源管理组件
     */
    public function getAssetManager()
    {
        return $this->get('assetManager');
    }

    /**
     * Returns the security component.
     * 返回安全组件
     * @return \yii\base\Security the security application component.
     * 返回值 安全组件
     */
    public function getSecurity()
    {
        return $this->get('security');
    }

    /**
     * Returns the configuration of core application components.
     * 返回核心应用组件的配置
     * @see set()
     */
    public function coreComponents()
    {
        return [
            'log' => ['class' => 'yii\log\Dispatcher'],
            'view' => ['class' => 'yii\web\View'],
            'formatter' => ['class' => 'yii\i18n\Formatter'],
            'i18n' => ['class' => 'yii\i18n\I18N'],
            'mailer' => ['class' => 'yii\swiftmailer\Mailer'],
            'urlManager' => ['class' => 'yii\web\UrlManager'],
            'assetManager' => ['class' => 'yii\web\AssetManager'],
            'security' => ['class' => 'yii\base\Security'],
        ];
    }

    /**
     * Terminates the application.
     * 结束应用
     * This method replaces the `exit()` function by ensuring the application life cycle is completed
     * before terminating the application.
     * 该方法代替了exit函数，以确保终止应用之前完成应用的生命周期
     * @param integer $status the exit status (value 0 means normal exit while other values mean abnormal exit).
     * 参数 整数  应用退出的状态（0表示正常，其他值表示不正常）
     * @param Response $response the response to be sent. If not set, the default application [[response]] component will be used.
     * 参数 响应 将要发出去的响应。如果没有设置，将会默认的response组件
     * @throws ExitException if the application is in testing mode
     * 在测试模式下抛出异常
     */
    public function end($status = 0, $response = null)
    {
        if ($this->state === self::STATE_BEFORE_REQUEST || $this->state === self::STATE_HANDLING_REQUEST) {
            $this->state = self::STATE_AFTER_REQUEST;
            $this->trigger(self::EVENT_AFTER_REQUEST);
        }

        if ($this->state !== self::STATE_SENDING_RESPONSE && $this->state !== self::STATE_END) {
            $this->state = self::STATE_END;
            $response = $response ? : $this->getResponse();
            $response->send();
        }

        if (YII_ENV_TEST) {
            throw new ExitException($status);
        } else {
            exit($status);
        }
    }
}
