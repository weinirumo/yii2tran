<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;

/**
 * Controller is the base class for classes containing controller logic.
 * Controller 是包含控制器逻辑的类的基类
 *
 * @property Module[] $modules All ancestor modules that this controller is located within. This property is
 * read-only.
 * 属性 Moudule 控制器位于的所有祖先模块 该属性只读
 *
 * @property string $route The route (module ID, controller ID and action ID) of the current request. This
 * property is read-only.
 * 属性 字符串 $route 当前请求的路由（模块ID，控制器ID和动作ID），该属性只读
 *
 * @property string $uniqueId The controller ID that is prefixed with the module ID (if any). This property is
 * read-only.
 * 属性 字符串 $uniqueId 带有以模块（如果存在）为前缀的控制器ID，该属性只读
 *
 * @property View|\yii\web\View $view The view object that can be used to render views or view files.
 * 属性 可以用来渲染视图或视图文件的view对象
 *
 * @property string $viewPath The directory containing the view files for this controller.
 * 属性 字符串 当前控制器的视图文件夹
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Controller extends Component implements ViewContextInterface
{
    /**
     * @event ActionEvent an event raised right before executing a controller action.
     * 执行控制器动作之前的事件
     *
     * You may set [[ActionEvent::isValid]] to be false to cancel the action execution.
     * 你可以设置[[ActionEvent::isValid]]为false来取消动作的执行。
     */
    const EVENT_BEFORE_ACTION = 'beforeAction';
    /**
     * @event ActionEvent an event raised right after executing a controller action.
     * 执行完控制器动作之后的事件
     */
    const EVENT_AFTER_ACTION = 'afterAction';

    /**
     * @var string the ID of this controller.
     * 控制器的ID
     */
    public $id;
    /**
     * @var Module $module the module that this controller belongs to.
     * 控制器所属的模块
     */
    public $module;
    /**
     * @var string the ID of the action that is used when the action ID is not specified
     * in the request. Defaults to 'index'.
     * 当请求中没有指定动作ID时，默认调用的动作是index
     */
    public $defaultAction = 'index';
    /**
     * @var null|string|false the name of the layout to be applied to this controller's views.
     * This property mainly affects the behavior of [[render()]].
     * 应用于当前控制器的视图的布局文件名，该属性主要影响[[render()]]方法。
     *
     * Defaults to null, meaning the actual layout value should inherit that from [[module]]'s layout value.
     * 默认为null，表示布局文件继承[[module]]的布局文件
     *
     * If false, no layout will be applied.
     * 如果设置为false，那么就不再使用布局文件了
     */
    public $layout;
    /**
     * @var Action the action that is currently being executed. This property will be set
     * by [[run()]] when it is called by [[Application]] to run an action.
     * 当前正在执行的动作。当[[Application]]运行动作的时候会设置该属性。
     */
    public $action;

    /**
     * @var View the view object that can be used to render views or view files.
     * 用于渲染视图或视图文件的视图对象
     */
    private $_view;
    /**
     * @var string the root directory that contains view files for this controller.
     * 视图文件的文件夹
     */
    private $_viewPath;


    /**
     * @param string $id the ID of this controller.
     * 参数 字符串 $id 控制器的ID
     *
     * @param Module $module the module that this controller belongs to.
     * 控制器所在的模块名
     *
     * @param array $config name-value pairs that will be used to initialize the object properties.
     * 初始化对象属性的时候使用的键值对
     */
    public function __construct($id, $module, $config = [])
    {
        $this->id = $id;
        $this->module = $module;
        parent::__construct($config);
    }

    /**
     * Declares external actions for the controller.
     * 声明控制器的外部动作
     *
     * This method is meant to be overwritten to declare external actions for the controller.
     * 重写该方法，可以给控制器添加外部的动作
     *
     * It should return an array, with array keys being action IDs, and array values the corresponding
     * action class names or action configuration arrays. For example,
     * 它应当返回数组，数组的键是动作ID，值是相关动作类名或者动作配置数组，例如，
     *
     * ```php
     * return [
     *     'action1' => 'app\components\Action1',
     *     'action2' => [
     *         'class' => 'app\components\Action2',
     *         'property1' => 'value1',
     *         'property2' => 'value2',
     *     ],
     * ];
     * ```
     *
     * [[\Yii::createObject()]] will be used later to create the requested action
     * using the configuration provided here.
     * [[\Yii::createObject()]]稍后将会采用这里提供的配置创建请求动作。
     */
    public function actions()
    {
        return [];
    }

    /**
     * Runs an action within this controller with the specified action ID and parameters.
     * 根据指定的动作id和参数运行该动作
     *
     * If the action ID is empty, the method will use [[defaultAction]].
     * 如果动作id为空，该方法会使用默认的动作
     *
     * @param string $id the ID of the action to be executed.
     * 参数 字符串 将要被执行的动作的ID。
     *
     * @param array $params the parameters (name-value pairs) to be passed to the action.
     * 参数 数组 即将传递给动作的键值对参数。
     *
     * @return mixed the result of the action.
     * 返回值 混合型 动作的结果
     *
     * @throws InvalidRouteException if the requested action ID cannot be resolved into an action successfully.
     * 抛出 无效的路由 请求无法根据动作id成功调用动作
     * @see createAction()
     */
    public function runAction($id, $params = [])
    {
        $action = $this->createAction($id);
        if ($action === null) {
            throw new InvalidRouteException('Unable to resolve the request: ' . $this->getUniqueId() . '/' . $id);
        }

        Yii::trace('Route to run: ' . $action->getUniqueId(), __METHOD__);

        if (Yii::$app->requestedAction === null) {
            Yii::$app->requestedAction = $action;
        }

        $oldAction = $this->action;
        $this->action = $action;

        $modules = [];
        $runAction = true;

        // call beforeAction on modules
        // 调用模块下的beforeAction方法
        foreach ($this->getModules() as $module) {
            if ($module->beforeAction($action)) {
                array_unshift($modules, $module);
            } else {
                $runAction = false;
                break;
            }
        }

        $result = null;

        if ($runAction && $this->beforeAction($action)) {
            // run the action
            // 运行动作
            $result = $action->runWithParams($params);

            $result = $this->afterAction($action, $result);

            // call afterAction on modules
            // 调用模块下的afterAction
            foreach ($modules as $module) {
                /* @var $module Module */
                $result = $module->afterAction($action, $result);
            }
        }

        $this->action = $oldAction;

        return $result;
    }

    /**
     * Runs a request specified in terms of a route.
     * 运行一个请求中指定的路由
     *
     * The route can be either an ID of an action within this controller or a complete route consisting
     * of module IDs, controller ID and action ID. If the route starts with a slash '/', the parsing of
     * the route will start from the application; otherwise, it will start from the parent module of this controller.
     * 路由可以是动作的id或者包含完整的模块id控制器id和动作id。如果该路由以一个斜线"/"开始，将会从应用开始解析路由，否则的话
     * 就从控制器的模块开始解析
     *
     * @param string $route the route to be handled, e.g., 'view', 'comment/view', '/admin/comment/view'.
     * 参数 字符串 将要处理的路由 。例如 view ， 'comment/view', '/admin/comment/view'。
     *
     * @param array $params the parameters to be passed to the action.
     * 参数 数组 传递给该动作的参数
     *
     * @return mixed the result of the action.
     * 返回值 混合型 动作执行的结果
     * @see runAction()
     */
    public function run($route, $params = [])
    {
        $pos = strpos($route, '/');
        if ($pos === false) {
            return $this->runAction($route, $params);
        } elseif ($pos > 0) {
            return $this->module->runAction($route, $params);
        } else {
            return Yii::$app->runAction(ltrim($route, '/'), $params);
        }
    }

    /**
     * Binds the parameters to the action.
     * 给动作绑定参数
     *
     * This method is invoked by [[Action]] when it begins to run with the given parameters.
     * 当[[Action]]运行时带有参数，就会调用此方法
     *
     * @param Action $action the action to be bound with parameters.
     * 参数 动作 将要绑定的动作
     *
     * @param array $params the parameters to be bound to the action.
     * 参数 数组 将要被绑定的参数
     *
     * @return array the valid parameters that the action can run with.
     * 返回值 数组 该方法可以运行的参数
     */
    public function bindActionParams($action, $params)
    {
        return [];
    }

    /**
     * Creates an action based on the given action ID.
     * 根据给定的动作id创建动作
     *
     * The method first checks if the action ID has been declared in [[actions()]]. If so,
     * it will use the configuration declared there to create the action object.
     * 该方法首先检测动作id是否在[[actions()]]中声明，如果声明过，它将会采用声明时配置创建动作对象
     *
     * If not, it will look for a controller method whose name is in the format of `actionXyz`
     * where `Xyz` stands for the action ID. If found, an [[InlineAction]] representing that
     * method will be created and returned.
     * 如果没有，它将会寻找控制器中是否含有action的动作。如果找到，代表那个方法的[[InlineAction]]将会被创建并返回
     *
     * @param string $id the action ID.
     * 动作ID
     *
     * @return Action the newly created action instance. Null if the ID doesn't resolve into any action.
     * 创建的动作实例。如果动作id不存在，将会返回null
     */
    public function createAction($id)
    {
        if ($id === '') {
            $id = $this->defaultAction;
        }

        $actionMap = $this->actions();
        if (isset($actionMap[$id])) {
            return Yii::createObject($actionMap[$id], [$id, $this]);
        } elseif (preg_match('/^[a-z0-9\\-_]+$/', $id) && strpos($id, '--') === false && trim($id, '-') === $id) {
            $methodName = 'action' . str_replace(' ', '', ucwords(implode(' ', explode('-', $id))));
            if (method_exists($this, $methodName)) {
                $method = new \ReflectionMethod($this, $methodName);
                if ($method->isPublic() && $method->getName() === $methodName) {
                    return new InlineAction($id, $this, $methodName);
                }
            }
        }

        return null;
    }

    /**
     * This method is invoked right before an action is executed.
     * 该方法会在动作执行前调用
     *
     * The method will trigger the [[EVENT_BEFORE_ACTION]] event. The return value of the method
     * will determine whether the action should continue to run.
     * 该方法会触发[[EVENT_BEFORE_ACTION]]事件。该方法的返回值将决定是否继续运行动作
     *
     * In case the action should not run, the request should be handled inside of the `beforeAction` code
     * by either providing the necessary output or redirecting the request. Otherwise the response will be empty.
     * 为了防止该动作不执行，该方法里应该提供必要的输出，或者重定向到其他请求。否则响应为空。
     *
     * If you override this method, your code should look like the following:
     * 如果你重写此方法，您的代码可以参考如下示例：
     *
     * ```php
     * public function beforeAction($action)
     * {
     *     // your custom code here, if you want the code to run before action filters,
     *     // 您自定义的代码，如果您需要这些代码在[[EVENT_BEFORE_ACTION]]触发的动作过滤之前运行，例如PageCache 或者 AccessControl
     *     // which are triggered on the [[EVENT_BEFORE_ACTION]] event, e.g. PageCache or AccessControl
     *
     *     if (!parent::beforeAction($action)) {
     *         return false;
     *     }
     *
     *     // other custom code here
     *     // 其他自定义代码
     *
     *     return true; // or false to not run the action //或者返回false阻止动作的执行
     * }
     * ```
     *
     * @param Action $action the action to be executed.
     * 参数 即将执行的动作
     *
     * @return boolean whether the action should continue to run.
     * 返回值 boolean 是否继续执行动作
     */
    public function beforeAction($action)
    {
        $event = new ActionEvent($action);
        $this->trigger(self::EVENT_BEFORE_ACTION, $event);
        return $event->isValid;
    }

    /**
     * This method is invoked right after an action is executed.
     * 该方法会在动作执行结束以后，立即调用
     *
     * The method will trigger the [[EVENT_AFTER_ACTION]] event. The return value of the method
     * will be used as the action return value.
     * 该方法会触发[[EVENT_AFTER_ACTION]]事件，该方法的返回值将会当做动作的返回值
     *
     * If you override this method, your code should look like the following:
     * 如果您重写此方法，可以参考如下代码：
     *
     * ```php
     * public function afterAction($action, $result)
     * {
     *     $result = parent::afterAction($action, $result);
     *     // your custom code here
     *     // 您的自定义代码
     *     return $result;
     * }
     * ```
     *
     * @param Action $action the action just executed.
     * 执行过的动作
     *
     * @param mixed $result the action return result.
     * 动作的返回值
     *
     * @return mixed the processed action result.
     * 处理过后的返回值
     */
    public function afterAction($action, $result)
    {
        $event = new ActionEvent($action);
        $event->result = $result;
        $this->trigger(self::EVENT_AFTER_ACTION, $event);
        return $event->result;
    }

    /**
     * Returns all ancestor modules of this controller.
     * 返回该控制器的所有祖先模块
     *
     * The first module in the array is the outermost one (i.e., the application instance),
     * while the last is the innermost one.
     * 该数组中的第一个元素是最外层的。例如，应用实例。最后一个是最内层的。
     *
     * @return Module[] all ancestor modules that this controller is located within.
     * 返回值 ，该控制器所在的所有祖先模块
     */
    public function getModules()
    {
        $modules = [$this->module];
        $module = $this->module;
        while ($module->module !== null) {
            array_unshift($modules, $module->module);
            $module = $module->module;
        }
        return $modules;
    }

    /**
     * Returns the unique ID of the controller.
     * 返回控制器的唯一id
     *
     * @return string the controller ID that is prefixed with the module ID (if any).
     * 返回值 字符串 带有模块id（如果存在）的控制器id
     */
    public function getUniqueId()
    {
        return $this->module instanceof Application ? $this->id : $this->module->getUniqueId() . '/' . $this->id;
    }

    /**
     * Returns the route of the current request.
     * 返回当前请求的路由
     *
     * @return string the route (module ID, controller ID and action ID) of the current request.
     * 返回值，字符串  路由请求参数，当前请求的模块id，控制器id和动作id
     */
    public function getRoute()
    {
        return $this->action !== null ? $this->action->getUniqueId() : $this->getUniqueId();
    }

    /**
     * Renders a view and applies layout if available.
     * 渲染视图，可能的话，并应用布局
     *
     * The view to be rendered can be specified in one of the following formats:
     * 要渲染的视图可以通过如下的方式指定：
     *
     * - path alias (e.g. "@app/views/site/index");
     * - 路径别名，例如"@app/views/site/index"
     *
     * - absolute path within application (e.g. "//site/index"): the view name starts with double slashes.
     * - 该应用下的绝对路径，例如"//site/index"：视图名使用双斜线开始
     *
     *   The actual view file will be looked for under the [[Application::viewPath|view path]] of the application.
     *   实际的视图文件路径将会在应用的视图文件目录去找。
     *
     * - absolute path within module (e.g. "/site/index"): the view name starts with a single slash.
     * - 相对模块的绝对路径 例如 "/site/index" 视图名使用单斜线开始
     *
     *   The actual view file will be looked for under the [[Module::viewPath|view path]] of [[module]].
     *   实际的视图文件将会在模块的[[Module::viewPath|view path]]路径下寻找
     *
     * - relative path (e.g. "index"): the actual view file will be looked for under [[viewPath]].
     * - 相对路径 ， 例如 "index" ， 将会在[[viewPath]]下边寻找视图文件
     *
     * To determine which layout should be applied, the following two steps are conducted:
     * 为了确认该使用哪里的布局文件，根据以下两个步骤：
     *
     * 1. In the first step, it determines the layout name and the context module:
     * 1. 第一步，确定布局名称和上下文模块名。
     *
     * - If [[layout]] is specified as a string, use it as the layout name and [[module]] as the context module;
     * - 如果布局文件是字符串，使用它当做布局文件名，并采用当前模块。
     *
     * - If [[layout]] is null, search through all ancestor modules of this controller and find the first
     *   module whose [[Module::layout|layout]] is not null. The layout and the corresponding module
     *   are used as the layout name and the context module, respectively. If such a module is not found
     *   or the corresponding layout is not a string, it will return false, meaning no applicable layout.
     * - 如果布局文件为null，查找该控制器最近的祖先模块[[Module::layout|layout]]不为null的模块，分别使用祖先的布局文件和当前模块
     *   名，如果没有找到符合条件的模块，或者相应的布局文件不是字符串，就会返回false，表示没有可用的布局文件。
     *
     * 2. In the second step, it determines the actual layout file according to the previously found layout name
     *    and context module. The layout name can be:
     * 2 第二步，根据先前找到的布局文件和当前模块，决定采用的布局文件。布局文件名可以是：
     *
     * - a path alias (e.g. "@app/views/layouts/main");
     * - 路径别名 例如 "@app/views/layouts/main"
     *
     * - an absolute path (e.g. "/main"): the layout name starts with a slash. The actual layout file will be
     *   looked for under the [[Application::layoutPath|layout path]] of the application;
     * - 绝对路径 例如"/main"：布局文件名以斜线开头。就会在应用的[[Application::layoutPath|layout path]]目录下寻找布局文件
     *
     * - a relative path (e.g. "main"): the actual layout file will be looked for under the
     *   [[Module::layoutPath|layout path]] of the context module.
     * - 相对路径 例如"main" ： 根据当前模块的[[Module::layoutPath|layout path]]下去寻找
     *
     * If the layout name does not contain a file extension, it will use the default one `.php`.
     * 如果布局文件没有扩展名，就会采用默认的.php
     *
     * @param string $view the view name.
     * 参数字符串 视图名
     *
     * @param array $params the parameters (name-value pairs) that should be made available in the view.
     * 参数 数组 视图文件中使用键值对参数
     *
     * These parameters will not be available in the layout.
     * 这些参数在布局文件中是无效的。（视图文件的渲染结果，就是一个php echo语句，从这个角度来讲，的确是不可用的）
     *
     * @return string the rendering result.
     * 返回值 字符串 渲染结果
     *
     * @throws InvalidParamException if the view file or the layout file does not exist.
     * 抛出不可用的参数异常 如果视图文件或者布局文件不存在的时候。
     */
    public function render($view, $params = [])
    {
        $content = $this->getView()->render($view, $params, $this);
        return $this->renderContent($content);
    }

    /**
     * Renders a static string by applying a layout.
     * 采用布局渲染静态一个静态字符串
     *
     * @param string $content the static string being rendered
     * 参数 字符串 被渲染的静态字符串
     *
     * @return string the rendering result of the layout with the given static string as the `$content` variable.
     * If the layout is disabled, the string will be returned back.
     * 返回值 字符串 通过$content输出的，带有布局的渲染结果。如果布局文件被禁用，将会把字符串退回
     * @since 2.0.1
     */
    public function renderContent($content)
    {
        $layoutFile = $this->findLayoutFile($this->getView());
        if ($layoutFile !== false) {
            return $this->getView()->renderFile($layoutFile, ['content' => $content], $this);
        } else {
            return $content;
        }
    }

    /**
     * Renders a view without applying layout.
     * 不采用布局文件渲染视图。
     *
     * This method differs from [[render()]] in that it does not apply any layout.
     * 该方法跟render方法的不同之处在于它不采用任何布局
     *
     * @param string $view the view name. Please refer to [[render()]] on how to specify a view name.
     * 参数 字符串 视图名称 ，请参考render方法确定如何指定视图名
     *
     * @param array $params the parameters (name-value pairs) that should be made available in the view.
     * 参数 数组 在视图文件中使用的参数键值对
     *
     * @return string the rendering result.
     * 返回值 字符串 渲染结果
     *
     * @throws InvalidParamException if the view file does not exist.
     * 抛出 可不用的参数异常 如果视图文件不存在。
     */
    public function renderPartial($view, $params = [])
    {
        return $this->getView()->render($view, $params, $this);
    }

    /**
     * Renders a view file.
     * 渲染一个视图文件
     *
     * @param string $file the view file to be rendered. This can be either a file path or a path alias.
     * 参数 字符串 即将被渲染的视图文件。可以是一个文件路径或者路径别名
     *
     * @param array $params the parameters (name-value pairs) that should be made available in the view.
     * 参数 数组 渲染视图使用的键值对参数
     *
     * @return string the rendering result.
     * 返回值 字符串 渲染结果
     *
     * @throws InvalidParamException if the view file does not exist.
     * 抛出不合法参数异常 当视图文件不存在时
     */
    public function renderFile($file, $params = [])
    {
        return $this->getView()->renderFile($file, $params, $this);
    }

    /**
     * Returns the view object that can be used to render views or view files.
     * 返回渲染视图的视图对象
     *
     * The [[render()]], [[renderPartial()]] and [[renderFile()]] methods will use
     * this view object to implement the actual view rendering.
     * 这[[render()]], [[renderPartial()]] 和 [[renderFile()]]三个方法将会使用该对象实现真正的视图渲染。
     *
     * If not set, it will default to the "view" application component.
     * 如果没有设置，将会默认采用视图应用组件
     *
     * @return View|\yii\web\View the view object that can be used to render views or view files.
     * 返回值 渲染视图文件的视图对象
     */
    public function getView()
    {
        if ($this->_view === null) {
            $this->_view = Yii::$app->getView();
        }
        return $this->_view;
    }

    /**
     * Sets the view object to be used by this controller.
     * 设置该控制器将要使用的视图对象
     *
     * @param View|\yii\web\View $view the view object that can be used to render views or view files.
     * 参数 可以用于渲染视图或者视图文件的视图对象
     */
    public function setView($view)
    {
        $this->_view = $view;
    }

    /**
     * Returns the directory containing view files for this controller.
     * 返回当前控制器的包含视图文件的目录
     *
     * The default implementation returns the directory named as controller [[id]] under the [[module]]'s
     * [[viewPath]] directory.
     * 默认实现的是以模块的视图目录和控制器id拼接的目录
     *
     * @return string the directory containing the view files for this controller.
     * 返回值 字符串 当前控制器的视图文件目录
     */
    public function getViewPath()
    {
        if ($this->_viewPath === null) {
            $this->_viewPath = $this->module->getViewPath() . DIRECTORY_SEPARATOR . $this->id;
        }
        return $this->_viewPath;
    }

    /**
     * Sets the directory that contains the view files.
     * 设置视图文件目录
     *
     * @param string $path the root directory of view files.
     * 参数 字符串 视图文件的根路径
     *
     * @throws InvalidParamException if the directory is invalid
     * 抛出不可用的参数异常，当目录不合法时
     * @since 2.0.7
     */
    public function setViewPath($path)
    {
        $this->_viewPath = Yii::getAlias($path);
    }

    /**
     * Finds the applicable layout file.
     * 查找可用的布局文件
     *
     * @param View $view the view object to render the layout file.
     * 参数 渲染布局的视图对象
     *
     * @return string|boolean the layout file path, or false if layout is not needed.
     * Please refer to [[render()]] on how to specify this parameter.
     * 返回值 字符串或者boolean 布局文件路径，如果不需要布局文件，返回false。
     * 请参考render方法关于如何指定此参数
     *
     * @throws InvalidParamException if an invalid path alias is used to specify the layout.
     * 抛出不可用参数异常 当不合法的路径别名被用于指定布局文件时
     */
    public function findLayoutFile($view)
    {
        $module = $this->module;
        if (is_string($this->layout)) {
            $layout = $this->layout;
        } elseif ($this->layout === null) {
            while ($module !== null && $module->layout === null) {
                $module = $module->module;
            }
            if ($module !== null && is_string($module->layout)) {
                $layout = $module->layout;
            }
        }

        if (!isset($layout)) {
            return false;
        }

        if (strncmp($layout, '@', 1) === 0) {
            $file = Yii::getAlias($layout);
        } elseif (strncmp($layout, '/', 1) === 0) {
            $file = Yii::$app->getLayoutPath() . DIRECTORY_SEPARATOR . substr($layout, 1);
        } else {
            $file = $module->getLayoutPath() . DIRECTORY_SEPARATOR . $layout;
        }

        if (pathinfo($file, PATHINFO_EXTENSION) !== '') {
            return $file;
        }
        $path = $file . '.' . $view->defaultExtension;
        if ($view->defaultExtension !== 'php' && !is_file($path)) {
            $path = $file . '.php';
        }

        return $path;
    }
}
