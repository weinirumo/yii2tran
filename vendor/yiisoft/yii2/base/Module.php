<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;
use yii\di\ServiceLocator;

/**
 * Module is the base class for module and application classes.
 * Module是模块和应用类的基类。
 *
 * A module represents a sub-application which contains MVC elements by itself, such as
 * models, views, controllers, etc.
 * 模块是带有自身mvc元素的子应用，例如models,views,controllers等
 *
 * A module may consist of [[modules|sub-modules]].
 * 模块可以包含模块或子模块
 *
 * [[components|Components]] may be registered with the module so that they are globally
 * accessible within the module.
 * 组件注册模块后，就可以在整个模块中访问了
 *
 * @property array $aliases List of path aliases to be defined. The array keys are alias names (must start
 * with `@`) and the array values are the corresponding paths or aliases. See [[setAliases()]] for an example.
 * This property is write-only.
 * 属性 数组 将要被定义的路径别名。数组的键是别名，必须以@符开始，数组的值是相应的路径或别名。setAliases方法提供了示例。该属性只读
 * @property string $basePath The root directory of the module.
 * 属性 字符串 模块的根路径
 * @property string $controllerPath The directory that contains the controller classes. This property is
 * read-only.
 * 属性 字符串 包含控制器类的目录，该属性只读
 * @property string $layoutPath The root directory of layout files. Defaults to "[[viewPath]]/layouts".
 * 属性 字符串 布局文件的根目录。默认是视图文件夹下layouts目录
 * @property array $modules The modules (indexed by their IDs).
 * 属性 数组 模块（用他们的id区分）
 * @property string $uniqueId The unique ID of the module. This property is read-only.
 * 属性 字符串 模块的唯一id，该属性只读
 * @property string $viewPath The root directory of view files. Defaults to "[[basePath]]/views".
 * 属性 字符串 视图文件的根目录，默认是[[basePath]]/views
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Module extends ServiceLocator
{
    /**
     * @event ActionEvent an event raised before executing a controller action.
     * 事件 执行控制器动作以前的执行的事件
     * You may set [[ActionEvent::isValid]] to be `false` to cancel the action execution.
     * 你可以设置[[ActionEvent::isValid]]为false，这样就取消执行动作了
     */
    const EVENT_BEFORE_ACTION = 'beforeAction';
    /**
     * @event ActionEvent an event raised after executing a controller action.
     * 事件 执行完控制器动作以后触发的事件动作。
     */
    const EVENT_AFTER_ACTION = 'afterAction';

    /**
     * @var array custom module parameters (name => value).
     * 属性 数组 自定义的模块参数（键值对）
     */
    public $params = [];
    /**
     * @var string an ID that uniquely identifies this module among other modules which have the same [[module|parent]].
     * 属性 字符串 唯一的id，用以区分拥有相同父模块的其他模块
     */
    public $id;
    /**
     * @var Module the parent module of this module. `null` if this module does not have a parent.
     * 属性 该模块的父模块，如果没有父模块就是null
     */
    public $module;
    /**
     * @var string|boolean the layout that should be applied for views within this module. This refers to a view name
     * relative to [[layoutPath]]. If this is not set, it means the layout value of the [[module|parent module]]
     * will be taken. If this is `false`, layout will be disabled within this module.
     * 属性 字符串|boolean 该模块用于视图的布局文件。这个是指布局目录下的视图名。如果没有设置，默认会采用父模块的布局，如果是false，该模块就不再
     * 使用布局
     */
    public $layout;
    /**
     * @var array mapping from controller ID to controller configurations.
     * 属性 数组 从控制id到控制器配置的列表
     * Each name-value pair specifies the configuration of a single controller.
     * 每一个键值对都表示了一个控制器的配置
     * A controller configuration can be either a string or an array.
     * 控制器的配置项可以是字符串或者数组
     * If the former, the string should be the fully qualified class name of the controller.
     * 如果是前者，字符串应该是控制器类名的全称
     * If the latter, the array must contain a `class` element which specifies
     * the controller's fully qualified class name, and the rest of the name-value pairs
     * in the array are used to initialize the corresponding controller properties. For example,
     * 如果是后者，数组必须包含一个指定控制器全名的类元素，数组中其他的键值对用来初始化相应的控制器属性，例如，
     *
     * ```php
     * [
     *   'account' => 'app\controllers\UserController',
     *   'article' => [
     *      'class' => 'app\controllers\PostController',
     *      'pageTitle' => 'something new',
     *   ],
     * ]
     * ```
     */
    public $controllerMap = [];
    /**
     * @var string the namespace that controller classes are in.
     * 属性 字符串 控制器所在的命名空间
     * This namespace will be used to load controller classes by prepending it to the controller
     * class name.
     * 该命名空间用于在加载控制器时先把它和一个控制器名拼接
     *
     * If not set, it will use the `controllers` sub-namespace under the namespace of this module.
     * For example, if the namespace of this module is `foo\bar`, then the default
     * controller namespace would be `foo\bar\controllers`.
     * 如果没有设置，就会使用该模块下默认的控制器命名空间
     * 例如，如果该模块的命名空间是foo\bar，默认的命名空间就是foo\bar\controller
     *
     * See also the [guide section on autoloading](guide:concept-autoloading) to learn more about
     * defining namespaces and how classes are loaded.
     * 参考手册[guide section on autoloading]，获取更多关于定义命名空间和类如何加载
     */
    public $controllerNamespace;
    /**
     * @var string the default route of this module. Defaults to `default`.
     * 属性 字符串 该模块的默认路由，默认是default
     * The route may consist of child module ID, controller ID, and/or action ID.
     * 该路由可以由子模块ID，控制器ID，和（或）动作ID组成
     * For example, `help`, `post/create`, `admin/post/create`.
     * 例如， `help`, `post/create`, `admin/post/create`.
     * If action ID is not given, it will take the default value as specified in
     * [[Controller::defaultAction]].
     * 如果没有设置动作id，将会采用控制器里边设置默认动作
     */
    public $defaultRoute = 'default';

    /**
     * @var string the root directory of the module.
     * 属性 字符串 模块的根路径
     */
    private $_basePath;
    /**
     * @var string the root directory that contains view files for this module
     * 属性 字符串 该模块的视图文件夹所在的位置
     */
    private $_viewPath;
    /**
     * @var string the root directory that contains layout view files for this module.
     * 属性 字符串 该模块的布局文件所在的目录
     */
    private $_layoutPath;
    /**
     * @var array child modules of this module
     * 属性 数组 该模块的子模块
     */
    private $_modules = [];


    /**
     * Constructor.
     * 构造函数
     * @param string $id the ID of this module.
     * 参数 字符串 模块ID
     * @param Module $parent the parent module (if any).
     * 参数 父模块
     * @param array $config name-value pairs that will be used to initialize the object properties.
     * 参数 数组 初始化对象时用到的配置数组
     */
    public function __construct($id, $parent = null, $config = [])
    {
        $this->id = $id;
        $this->module = $parent;
        parent::__construct($config);
    }

    /**
     * Returns the currently requested instance of this module class.
     * 返回当前请求的模块类的实例
     * If the module class is not currently requested, `null` will be returned.
     * 如果当前模块类没有被请求，就会返回null
     * This method is provided so that you access the module instance from anywhere within the module.
     * 通过此方法，你可以在该模块的任何地方访问该模块的实例
     * @return static|null the currently requested instance of this module class, or `null` if the module class is not requested.
     * 返回值 当前请求的模块实例，如果没有模块被请求，就会返回null
     */
    public static function getInstance()
    {
        $class = get_called_class();
        return isset(Yii::$app->loadedModules[$class]) ? Yii::$app->loadedModules[$class] : null;
    }

    /**
     * Sets the currently requested instance of this module class.
     * 设置该模块类的当前请求实例
     * @param Module|null $instance the currently requested instance of this module class.
     * If it is `null`, the instance of the calling class will be removed, if any.
     * 参数 该模块类的当前请求实例。
     * 如果为null，调用的存在的类实例就会被删除
     */
    public static function setInstance($instance)
    {
        if ($instance === null) {
            unset(Yii::$app->loadedModules[get_called_class()]);
        } else {
            Yii::$app->loadedModules[get_class($instance)] = $instance;
        }
    }

    /**
     * Initializes the module.
     * 初始化该模块
     *
     * This method is called after the module is created and initialized with property values
     * given in configuration. The default implementation will initialize [[controllerNamespace]]
     * if it is not set.
     * 该方法会在模块创建以后并且采用给定的配置初始化属性以后调用。默认的实现是初始化控制器命名空间，如果它还没被设置
     *
     * If you override this method, please make sure you call the parent implementation.
     * 如果你重写此方法，请确保你调用了父类的实现
     */
    public function init()
    {
        if ($this->controllerNamespace === null) {
            $class = get_class($this);
            if (($pos = strrpos($class, '\\')) !== false) {
                $this->controllerNamespace = substr($class, 0, $pos) . '\\controllers';
            }
        }
    }

    /**
     * Returns an ID that uniquely identifies this module among all modules within the current application.
     * 返回该模块的唯一id，跟当前应用的其他模块做为区分
     * Note that if the module is an application, an empty string will be returned.
     * 注意，如果模块是一个应用的话，就会返回空字符串
     * @return string the unique ID of the module.
     * 返回值 字符串 模块的唯一id
     */
    public function getUniqueId()
    {
        return $this->module ? ltrim($this->module->getUniqueId() . '/' . $this->id, '/') : $this->id;
    }

    /**
     * Returns the root directory of the module.
     * 返回该模块的根目录
     * It defaults to the directory containing the module class file.
     * 默认是包含模块类文件的目录
     * @return string the root directory of the module.
     * 返回值 字符串 模块的根目录
     */
    public function getBasePath()
    {
        if ($this->_basePath === null) {
            $class = new \ReflectionClass($this);
            $this->_basePath = dirname($class->getFileName());
        }

        return $this->_basePath;
    }

    /**
     * Sets the root directory of the module.
     * 设置模块的根目录
     * This method can only be invoked at the beginning of the constructor.
     * 该方法只能在构造函数的开头调用
     * @param string $path the root directory of the module. This can be either a directory name or a path alias.
     * 参数 字符串 模块的根目录。该参数可以是目录名或者一个路径别名
     * @throws InvalidParamException if the directory does not exist.
     * 当陌路不存在的时候抛出非法的参数异常
     */
    public function setBasePath($path)
    {
        $path = Yii::getAlias($path);
        $p = strncmp($path, 'phar://', 7) === 0 ? $path : realpath($path);
        if ($p !== false && is_dir($p)) {
            $this->_basePath = $p;
        } else {
            throw new InvalidParamException("The directory does not exist: $path");
        }
    }

    /**
     * Returns the directory that contains the controller classes according to [[controllerNamespace]].
     * 根据[[controllerNamespace]]属性返回控制器类的目录
     * Note that in order for this method to return a value, you must define
     * an alias for the root namespace of [[controllerNamespace]].
     * 注意，为了让该方法返回值，你必须为[[controllerNamespace]]定义一个路径别名
     * @return string the directory that contains the controller classes.
     * 返回值 字符串 包含控制器类的目录
     * @throws InvalidParamException if there is no alias defined for the root namespace of [[controllerNamespace]].
     * 当没有为[[controllerNamespace]]的根命名空间设置别名的时候，抛出非法的参数异常
     */
    public function getControllerPath()
    {
        return Yii::getAlias('@' . str_replace('\\', '/', $this->controllerNamespace));
    }

    /**
     * Returns the directory that contains the view files for this module.
     * 返回该模块的视图文件所在的目录
     * @return string the root directory of view files. Defaults to "[[basePath]]/views".
     * 返回值 字符串 视图文件的根目录，默认是[[basePath]]/views
     */
    public function getViewPath()
    {
        if ($this->_viewPath === null) {
            $this->_viewPath = $this->getBasePath() . DIRECTORY_SEPARATOR . 'views';
        }
        return $this->_viewPath;
    }

    /**
     * Sets the directory that contains the view files.
     * 设置视图文件的目录
     * @param string $path the root directory of view files.
     * 参数 字符串 视图文件的根目录
     * @throws InvalidParamException if the directory is invalid.
     * 当目录不合法时，抛出不合法的参数异常
     */
    public function setViewPath($path)
    {
        $this->_viewPath = Yii::getAlias($path);
    }

    /**
     * Returns the directory that contains layout view files for this module.
     * 返回该模块的布局文件所在的目录
     * @return string the root directory of layout files. Defaults to "[[viewPath]]/layouts".
     * 返回值 字符串 布局文件的根目录，默认是[[viewPath]]/layouts
     */
    public function getLayoutPath()
    {
        if ($this->_layoutPath === null) {
            $this->_layoutPath = $this->getViewPath() . DIRECTORY_SEPARATOR . 'layouts';
        }

        return $this->_layoutPath;
    }

    /**
     * Sets the directory that contains the layout files.
     * 设置布局文件所在的目录
     * @param string $path the root directory or path alias of layout files.
     * 参数 字符串 布局文件的根目录或者路径别名
     * @throws InvalidParamException if the directory is invalid
     * 如果目录不合法，抛出不合法的参数异常
     */
    public function setLayoutPath($path)
    {
        $this->_layoutPath = Yii::getAlias($path);
    }

    /**
     * Defines path aliases.
     * 定义路径别名
     * This method calls [[Yii::setAlias()]] to register the path aliases.
     * 该方法调用了[[Yii::setAlias()]]去注册路径别名
     * This method is provided so that you can define path aliases when configuring a module.
     * 使用该方法，你可以配置模块的路径别名
     * @property array list of path aliases to be defined. The array keys are alias names
     * (must start with `@`) and the array values are the corresponding paths or aliases.
     * See [[setAliases()]] for an example.
     * 属性 数组 要被定义的路径别名。数组的键是别名，必须以@作为开头，数组的值是相应的路径或者别名。请参考setAliases的示例
     * @param array $aliases list of path aliases to be defined. The array keys are alias names
     * (must start with `@`) and the array values are the corresponding paths or aliases.
     * 参数 数组 被设置的路径别名列表。数组的键是别名，必须以@作为开头，数组的值是相应的路径或者别名
     * For example,
     * 例如，
     *
     * ```php
     * [
     *     '@models' => '@app/models', // an existing alias 已经存在的别名
     *     '@backend' => __DIR__ . '/../backend',  // a directory 目录
     * ]
     * ```
     */
    public function setAliases($aliases)
    {
        foreach ($aliases as $name => $alias) {
            Yii::setAlias($name, $alias);
        }
    }

    /**
     * Checks whether the child module of the specified ID exists.
     * 检测该模块是否有规定ID的子模块
     * This method supports checking the existence of both child and grand child modules.
     * 该方法支持检测子模块或者孙模块
     * @param string $id module ID. For grand child modules, use ID path relative to this module (e.g. `admin/content`).
     * 参数 字符串 如果是孙模块，使用该模块的ID相对路径（例如 admin/content）
     * @return boolean whether the named module exists. Both loaded and unloaded modules
     * are considered.
     * 返回值 boolean 给定的模块是否存在，不论模块是否被加载都在范围之内
     */
    public function hasModule($id)
    {
        if (($pos = strpos($id, '/')) !== false) {
            // sub-module
            $module = $this->getModule(substr($id, 0, $pos));

            return $module === null ? false : $module->hasModule(substr($id, $pos + 1));
        } else {
            return isset($this->_modules[$id]);
        }
    }

    /**
     * Retrieves the child module of the specified ID.
     * 获取指定模块的子模块
     * This method supports retrieving both child modules and grand child modules.
     * 该方法支持获取子模块和孙模块
     * @param string $id module ID (case-sensitive). To retrieve grand child modules,
     * use ID path relative to this module (e.g. `admin/content`).
     * 参数 字符串 模块ID（大小写敏感），需要获取孙模块的实例，使用ID的相对路径写法，（例如admin/content）
     * @param boolean $load whether to load the module if it is not yet loaded.
     * 参数 boolean 如果模块没有被加载，是否加载它
     * @return Module|null the module instance, `null` if the module does not exist.
     * 返回值 模块示例，如果模块不存在返回null
     * @see hasModule()
     */
    public function getModule($id, $load = true)
    {
        if (($pos = strpos($id, '/')) !== false) {
            // sub-module
            $module = $this->getModule(substr($id, 0, $pos));

            return $module === null ? null : $module->getModule(substr($id, $pos + 1), $load);
        }

        if (isset($this->_modules[$id])) {
            if ($this->_modules[$id] instanceof Module) {
                return $this->_modules[$id];
            } elseif ($load) {
                Yii::trace("Loading module: $id", __METHOD__);
                /* @var $module Module */
                $module = Yii::createObject($this->_modules[$id], [$id, $this]);
                $module->setInstance($module);
                return $this->_modules[$id] = $module;
            }
        }

        return null;
    }

    /**
     * Adds a sub-module to this module.
     * 给给模块添加在模块
     * @param string $id module ID.
     * 参数 字符串 模块ID
     * @param Module|array|null $module the sub-module to be added to this module. This can
     * be one of the following:
     * 参数 要被加到给模块的子模块，该值可以是以下几种：
     *
     * - a [[Module]] object
     * - 一个模块对象
     * - a configuration array: when [[getModule()]] is called initially, the array
     *   will be used to instantiate the sub-module
     * - 一个配置数组，当初始化时调用getModule时，该数组会被用于实例化子模块
     * - `null`: the named sub-module will be removed from this module
     * - null：给定的子模块将会被从该模块移除
     */
    public function setModule($id, $module)
    {
        if ($module === null) {
            unset($this->_modules[$id]);
        } else {
            $this->_modules[$id] = $module;
        }
    }

    /**
     * Returns the sub-modules in this module.
     * 返回该模块中的子模块
     * @param boolean $loadedOnly whether to return the loaded sub-modules only. If this is set `false`,
     * then all sub-modules registered in this module will be returned, whether they are loaded or not.
     * Loaded modules will be returned as objects, while unloaded modules as configuration arrays.
     * 参数 boolean 是否只返回加载过的子模块。如果设置为false，该模块注册的所有子模块都会被返回，不管他们有没有被加载。
     * 被夹在的模块会被作为对象返回，没有被加载的模块会返回配置数组
     * @return array the modules (indexed by their IDs).
     * 返回值 数组 模块，索引是ID
     */
    public function getModules($loadedOnly = false)
    {
        if ($loadedOnly) {
            $modules = [];
            foreach ($this->_modules as $module) {
                if ($module instanceof Module) {
                    $modules[] = $module;
                }
            }

            return $modules;
        } else {
            return $this->_modules;
        }
    }

    /**
     * Registers sub-modules in the current module.
     * 在当前模块下注册子模块
     *
     * Each sub-module should be specified as a name-value pair, where
     * name refers to the ID of the module and value the module or a configuration
     * array that can be used to create the module. In the latter case, [[Yii::createObject()]]
     * will be used to create the module.
     * 每一个子模块都要指定为一个键值对，键代表模块的ID，值代表模块或者可以创建模块的数组，在第二种情况下，会调用[[Yii::createObject()]]创建
     * 模块
     *
     * If a new sub-module has the same ID as an existing one, the existing one will be overwritten silently.
     * 如果新模块跟已经存在的模块重名，已经存在的子模块会被静默覆盖
     *
     * The following is an example for registering two sub-modules:
     * 如下是注册子模块的示例：
     *
     * ```php
     * [
     *     'comment' => [
     *         'class' => 'app\modules\comment\CommentModule',
     *         'db' => 'db',
     *     ],
     *     'booking' => ['class' => 'app\modules\booking\BookingModule'],
     * ]
     * ```
     *
     * @param array $modules modules (id => module configuration or instances).
     */
    public function setModules($modules)
    {
        foreach ($modules as $id => $module) {
            $this->_modules[$id] = $module;
        }
    }

    /**
     * Runs a controller action specified by a route.
     * 运行路由中指定的动作
     * This method parses the specified route and creates the corresponding child module(s), controller and action
     * instances. It then calls [[Controller::runAction()]] to run the action with the given parameters.
     * 该方法解析路由，并且创建相应的子模块，控制器，动作的实例，然后调用Controller::runAction方法
     * If the route is empty, the method will use [[defaultRoute]].
     * 如果路由为空，该方法会使用默认路由
     * @param string $route the route that specifies the action.
     * 参数 字符串 指定动作的路由
     * @param array $params the parameters to be passed to the action
     * 参数 数组 传递给动作的参数
     * @return mixed the result of the action.
     * 返回值 混合型 动作执行的结果
     * @throws InvalidRouteException if the requested route cannot be resolved into an action successfully.
     * 当请求的路由无法解析成动作的时候，会抛出不合法的路由异常
     */
    public function runAction($route, $params = [])
    {
        $parts = $this->createController($route);
        if (is_array($parts)) {
            /* @var $controller Controller */
            list($controller, $actionID) = $parts;
            $oldController = Yii::$app->controller;
            Yii::$app->controller = $controller;
            $result = $controller->runAction($actionID, $params);
            Yii::$app->controller = $oldController;

            return $result;
        } else {
            $id = $this->getUniqueId();
            throw new InvalidRouteException('Unable to resolve the request "' . ($id === '' ? $route : $id . '/' . $route) . '".');
        }
    }

    /**
     * Creates a controller instance based on the given route.
     * 根据给定的路由创建控制器实例
     *
     * The route should be relative to this module. The method implements the following algorithm
     * to resolve the given route:
     * 路由应该跟该模块相关，该方法通过如下步骤，解析给定路由：
     *
     * 1. If the route is empty, use [[defaultRoute]];
     * 1. 如果路由为空，使用默认路由
     * 2. If the first segment of the route is a valid module ID as declared in [[modules]],
     *    call the module's `createController()` with the rest part of the route;
     * 2. 如果路由的第一部分不是合法的模块ID，根据路由剩余的部分调用模块的`createController()`方法
     * 3. If the first segment of the route is found in [[controllerMap]], create a controller
     *    based on the corresponding configuration found in [[controllerMap]];
     * 3. 如果路由的第一部分在[[controllerMap]]中找到，就根据找到的相应的配置创建控制器
     * 4. The given route is in the format of `abc/def/xyz`. Try either `abc\DefController`
     *    or `abc\def\XyzController` class within the [[controllerNamespace|controller namespace]].
     * 4. 给定的路由是`abc/def/xyz`格式，就会在控制器命名空间下，尝试`abc\DefController`或者`abc\def\XyzController`
     *
     * If any of the above steps resolves into a controller, it is returned together with the rest
     * part of the route which will be treated as the action ID. Otherwise, `false` will be returned.
     * 如果上述的任何一个步骤产生了控制器，就会把剩余的部分当做动作id返回，否则就返回false
     *
     * @param string $route the route consisting of module, controller and action IDs.
     * 参数 字符串 由模块，控制器，动作组成的路由
     * @return array|boolean If the controller is created successfully, it will be returned together
     * with the requested action ID. Otherwise `false` will be returned.
     * 返回值 数组|boolean 如果控制器成功创建，就会将请求的ID一并返回，否则返回的就是false
     * @throws InvalidConfigException if the controller class and its file do not match.
     * 当控制器和它的文件不匹配的时候，抛出不合法的配置异常
     */
    public function createController($route)
    {
        if ($route === '') {
            $route = $this->defaultRoute;
        }

        // double slashes or leading/ending slashes may cause substr problem
        // 双斜杠或者开头/结尾的斜杠可能会导致子字符串问题
        $route = trim($route, '/');
        if (strpos($route, '//') !== false) {
            return false;
        }

        if (strpos($route, '/') !== false) {
            list ($id, $route) = explode('/', $route, 2);
        } else {
            $id = $route;
            $route = '';
        }

        // module and controller map take precedence
        // 优先考虑模块和控制器地图
        if (isset($this->controllerMap[$id])) {
            $controller = Yii::createObject($this->controllerMap[$id], [$id, $this]);
            return [$controller, $route];
        }
        $module = $this->getModule($id);
        if ($module !== null) {
            return $module->createController($route);
        }

        if (($pos = strrpos($route, '/')) !== false) {
            $id .= '/' . substr($route, 0, $pos);
            $route = substr($route, $pos + 1);
        }

        $controller = $this->createControllerByID($id);
        if ($controller === null && $route !== '') {
            $controller = $this->createControllerByID($id . '/' . $route);
            $route = '';
        }

        return $controller === null ? false : [$controller, $route];
    }

    /**
     * Creates a controller based on the given controller ID.
     * 根据给定的控制器ID创建一个控制器
     *
     * The controller ID is relative to this module. The controller class
     * should be namespaced under [[controllerNamespace]].
     * 控制器ID跟该模块相关。该控制器类应该在[[controllerNamespace]]定义的命名空间之中。
     *
     * Note that this method does not check [[modules]] or [[controllerMap]].
     * 请注意，该方法不会检测[[modules]] 和 [[controllerMap]]
     *
     * @param string $id the controller ID.
     * 参数 字符串 控制器ID
     * @return Controller the newly created controller instance, or `null` if the controller ID is invalid.
     * 返回值 新创建的控制器实例，如果控制器ID不合法，就返回null
     * @throws InvalidConfigException if the controller class and its file name do not match.
     * This exception is only thrown when in debug mode.
     * 当控制器类和它的文件名不匹配时，抛出不合法的配置异常。该异常只在调试模式下抛出
     */
    public function createControllerByID($id)
    {
        $pos = strrpos($id, '/');
        if ($pos === false) {
            $prefix = '';
            $className = $id;
        } else {
            $prefix = substr($id, 0, $pos + 1);
            $className = substr($id, $pos + 1);
        }

        if (!preg_match('%^[a-z][a-z0-9\\-_]*$%', $className)) {
            return null;
        }
        if ($prefix !== '' && !preg_match('%^[a-z0-9_/]+$%i', $prefix)) {
            return null;
        }

        $className = str_replace(' ', '', ucwords(str_replace('-', ' ', $className))) . 'Controller';
        $className = ltrim($this->controllerNamespace . '\\' . str_replace('/', '\\', $prefix)  . $className, '\\');
        if (strpos($className, '-') !== false || !class_exists($className)) {
            return null;
        }

        if (is_subclass_of($className, 'yii\base\Controller')) {
            $controller = Yii::createObject($className, [$id, $this]);
            return get_class($controller) === $className ? $controller : null;
        } elseif (YII_DEBUG) {
            throw new InvalidConfigException("Controller class must extend from \\yii\\base\\Controller.");
        } else {
            return null;
        }
    }

    /**
     * This method is invoked right before an action within this module is executed.
     * 该方法会在该模块内的动作执行前调用
     *
     * The method will trigger the [[EVENT_BEFORE_ACTION]] event. The return value of the method
     * will determine whether the action should continue to run.
     * 该方法会触发[[EVENT_BEFORE_ACTION]]事件，该方法的返回值会决定是否继续执行此方法
     *
     * In case the action should not run, the request should be handled inside of the `beforeAction` code
     * by either providing the necessary output or redirecting the request. Otherwise the response will be empty.
     * 为了防止动作不执行。请求应该在beforeAction方法里提供必须的输出或者重定向别的请求。否则相应内容就会为空
     *
     * If you override this method, your code should look like the following:
     * 如果你重写了此方法，你的代码可以参考如下：
     *
     * ```php
     * public function beforeAction($action)
     * {
     *     if (!parent::beforeAction($action)) {
     *         return false;
     *     }
     *
     *     // your custom code here
     *
     *     return true; // or false to not run the action
     * }
     * ```
     *
     * @param Action $action the action to be executed.
     * 参数 将要执行的动作
     * @return boolean whether the action should continue to be executed.
     * 返回值 boolean 该方法是否需要继续执行
     */
    public function beforeAction($action)
    {
        $event = new ActionEvent($action);
        $this->trigger(self::EVENT_BEFORE_ACTION, $event);
        return $event->isValid;
    }

    /**
     * This method is invoked right after an action within this module is executed.
     * 该模块内的动作执行完毕以后会立即调用此方法。
     *
     * The method will trigger the [[EVENT_AFTER_ACTION]] event. The return value of the method
     * will be used as the action return value.
     * 该方法会触发[[EVENT_AFTER_ACTION]]事件，该方法的返回值会作为动作的返回值
     *
     * If you override this method, your code should look like the following:
     * 如果你重写此方法，你可以参考如下的写法：
     *
     * ```php
     * public function afterAction($action, $result)
     * {
     *     $result = parent::afterAction($action, $result);
     *     // your custom code here
     *     return $result;
     * }
     * ```
     *
     * @param Action $action the action just executed.
     * 参数 刚才执行过的动作
     * @param mixed $result the action return result.
     * 参数 混合型 动作返回的结果
     * @return mixed the processed action result.
     * 返回值 混合型 被处理过的动作的结果
     */
    public function afterAction($action, $result)
    {
        $event = new ActionEvent($action);
        $event->result = $result;
        $this->trigger(self::EVENT_AFTER_ACTION, $event);
        return $event->result;
    }
}
