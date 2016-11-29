<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;
use ReflectionClass;

/**
 * Widget is the base class for widgets.
 * Widget是小部件的基类。
 *
 * @property string $id ID of the widget.
 * 属性 字符串 小部件ID
 * @property \yii\web\View $view The view object that can be used to render views or view files. Note that the
 * type of this property differs in getter and setter. See [[getView()]] and [[setView()]] for details.
 * 属性 用来渲染视图或视图文件的视图对象。请注意，该属性的类型不同于getter和setter，详情请看[[getView()]] 和 [[setView()]]
 * @property string $viewPath The directory containing the view files for this widget. This property is
 * read-only.
 * 属性 字符串 包含该小部件所使用的视图文件的目录。该属性只读。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Widget extends Component implements ViewContextInterface
{
    /**
     * @var integer a counter used to generate [[id]] for widgets.
     * 成员变量 整型  用来生成小部件的计数器。
     * @internal
     */
    public static $counter = 0;
    /**
     * @var string the prefix to the automatically generated widget IDs.
     * 成员变量 字符串 自动成成小部件ID的前缀
     * @see getId()
     */
    public static $autoIdPrefix = 'w';
    /**
     * @var Widget[] the widgets that are currently being rendered (not ended). This property
     * is maintained by [[begin()]] and [[end()]] methods.
     * 成员变量 当前正在渲染的小部件（尚未结束），该属性在[[begin()]] 和 [[end()]]进行管理
     * @internal
     */
    public static $stack = [];


    /**
     * Begins a widget.
     * 开始一个小部件
     * This method creates an instance of the calling class. It will apply the configuration
     * to the created instance. A matching [[end()]] call should be called later.
     * 该方法会创建调用类的实例。它将会把配置应用到创建好的实例上。成对使用的[[end()]]方法应该在稍后调用。
     * As some widgets may use output buffering, the [[end()]] call should be made in the same view
     * to avoid breaking the nesting of output buffers.
     * 由于一些小部件会使用输出缓冲，应该在相同视图中调用[[end()]]方法以避免打乱输出缓冲的嵌套。
     * @param array $config name-value pairs that will be used to initialize the object properties
     * 参数 数组 初始化对象属性的时候使用键值对。
     * @return static the newly created widget instance
     * 返回值 新创建的小部件实例
     * @see end()
     */
    public static function begin($config = [])
    {
        $config['class'] = get_called_class();
        /* @var $widget Widget */
        $widget = Yii::createObject($config);
        static::$stack[] = $widget;

        return $widget;
    }

    /**
     * Ends a widget.
     * 结束一个小部件
     * Note that the rendering result of the widget is directly echoed out.
     * 请注意，渲染结果会直接输出的
     * @return static the widget instance that is ended.
     * 返回值 已经结束了的小小部件实例
     * @throws InvalidCallException if [[begin()]] and [[end()]] calls are not properly nested
     * 当[[begin()]] 和 [[end()]]没有正确嵌套的时候，抛出不合法的调用异常。
     * @see begin()
     */
    public static function end()
    {
        if (!empty(static::$stack)) {
            $widget = array_pop(static::$stack);
            if (get_class($widget) === get_called_class()) {
                echo $widget->run();
                return $widget;
            } else {
                throw new InvalidCallException('Expecting end() of ' . get_class($widget) . ', found ' . get_called_class());
            }
        } else {
            throw new InvalidCallException('Unexpected ' . get_called_class() . '::end() call. A matching begin() is not found.');
        }
    }

    /**
     * Creates a widget instance and runs it.
     * 创建一个小部件实例并运行。
     * The widget rendering result is returned by this method.
     * 小部件渲染的结果就是通过该方法返回。
     * @param array $config name-value pairs that will be used to initialize the object properties
     * 参数 数组 用以初始化对象属性的键值对。
     * @return string the rendering result of the widget.
     * 返回值 字符串 小部件的渲染结果
     * @throws \Exception
     */
    public static function widget($config = [])
    {
        ob_start();
        ob_implicit_flush(false);
        try {
            /* @var $widget Widget */
            $config['class'] = get_called_class();
            $widget = Yii::createObject($config);
            $out = $widget->run();
        } catch (\Exception $e) {
            // close the output buffer opened above if it has not been closed already
            // 如果关闭上边打开的输出缓存，就在这里关掉。
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            throw $e;
        }

        return ob_get_clean() . $out;
    }

    private $_id;

    /**
     * Returns the ID of the widget.
     * 返回小部件的ID
     * @param boolean $autoGenerate whether to generate an ID if it is not set previously
     * 参数 boolean 如果之前没有设置，是否生成一个ID
     * @return string ID of the widget.
     * 返回值 字符串 小部件的ID
     */
    public function getId($autoGenerate = true)
    {
        if ($autoGenerate && $this->_id === null) {
            $this->_id = static::$autoIdPrefix . static::$counter++;
        }

        return $this->_id;
    }

    /**
     * Sets the ID of the widget.
     * 设置小部件的ID
     * @param string $value id of the widget.
     * 参数 字符串 小部件id
     */
    public function setId($value)
    {
        $this->_id = $value;
    }

    private $_view;

    /**
     * Returns the view object that can be used to render views or view files.
     * 返回渲染视图文件的视图对象。
     * The [[render()]] and [[renderFile()]] methods will use
     * this view object to implement the actual view rendering.
     * [[render()]] 和 [[renderFile()]]方法将会使用该对象实现真正的视图渲染。
     * If not set, it will default to the "view" application component.
     * 如果没有设置，就会默认是view应用组件。
     * @return \yii\web\View the view object that can be used to render views or view files.
     * 返回值 用来渲染视图或视图文件的视图对象。
     */
    public function getView()
    {
        if ($this->_view === null) {
            $this->_view = Yii::$app->getView();
        }

        return $this->_view;
    }

    /**
     * Sets the view object to be used by this widget.
     * 设置该小部件使用的视图对象
     * @param View $view the view object that can be used to render views or view files.
     * 参数 可以用来渲染视图或视图文件的视图对象。
     */
    public function setView($view)
    {
        $this->_view = $view;
    }

    /**
     * Executes the widget.
     * 执行小部件
     * @return string the result of widget execution to be outputted.
     * 返回值 字符串 将要输出的小部件执行的结果
     */
    public function run()
    {
    }

    /**
     * Renders a view.
     * 渲染一个视图。
     * The view to be rendered can be specified in one of the following formats:
     * 可以通过如下的方式指定视图：
     *
     * - path alias (e.g. "@app/views/site/index");
     * - 路径别名（例如"@app/views/site/index"）
     * - absolute path within application (e.g. "//site/index"): the view name starts with double slashes.
     *   The actual view file will be looked for under the [[Application::viewPath|view path]] of the application.
     * - 该应用下的绝对路径（例如，"//site/index"）：视图名使用双斜线开头。实际的视图文件在应用的[[Application::viewPath|view path]]下
     * - absolute path within module (e.g. "/site/index"): the view name starts with a single slash.
     *   The actual view file will be looked for under the [[Module::viewPath|view path]] of the currently
     *   active module.
     * - 该模块下的绝对路径（例如，"/site/index"）：视图名以一个斜线开头。实际的视图文件在当前模块的[[Module::viewPath|view path]]下。
     * - relative path (e.g. "index"): the actual view file will be looked for under [[viewPath]].
     * - 相对路径（例如，"index"）：实际的视图文件在[[viewPath]]下。
     *
     * If the view name does not contain a file extension, it will use the default one `.php`.
     * 如果视图文件不包含文件扩展名，默认使用`.php`
     *
     * @param string $view the view name.
     * 参数 字符串 视图名
     * @param array $params the parameters (name-value pairs) that should be made available in the view.
     * 参数 数组 视图中可以的参数（键值对）
     * @return string the rendering result.
     * 返回值 字符串 渲染结果
     * @throws InvalidParamException if the view file does not exist.
     * 当视图文件不存在的时候抛出不合法的参数异常。
     */
    public function render($view, $params = [])
    {
        return $this->getView()->render($view, $params, $this);
    }

    /**
     * Renders a view file.
     * 渲染一个视图文件
     * @param string $file the view file to be rendered. This can be either a file path or a path alias.
     * 参数 字符串 被渲染的视图文件。可以是一个文件目录或者路径别名。
     * @param array $params the parameters (name-value pairs) that should be made available in the view.
     * 参数 数组 在视图中可用的参数（键值对）
     * @return string the rendering result.
     * 返回值 字符串 渲染结果
     * @throws InvalidParamException if the view file does not exist.
     * 当视图文件不存在时，抛出不合法的参数异常。
     */
    public function renderFile($file, $params = [])
    {
        return $this->getView()->renderFile($file, $params, $this);
    }

    /**
     * Returns the directory containing the view files for this widget.
     * 返回当前包含小部件视图文件的目录
     * The default implementation returns the 'views' subdirectory under the directory containing the widget class file.
     * 默认返回包含是该小部件类目录下的views子目录
     * @return string the directory containing the view files for this widget.
     * 返回值 字符串 包含该小部件视图文件的目录。
     */
    public function getViewPath()
    {
        $class = new ReflectionClass($this);

        return dirname($class->getFileName()) . DIRECTORY_SEPARATOR . 'views';
    }
}
