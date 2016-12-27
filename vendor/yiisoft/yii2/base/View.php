<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;
use yii\helpers\FileHelper;
use yii\widgets\Block;
use yii\widgets\ContentDecorator;
use yii\widgets\FragmentCache;

/**
 * View represents a view object in the MVC pattern.
 * View代表mvc模式下的视图对象
 *
 * View provides a set of methods (e.g. [[render()]]) for rendering purpose.
 * 视图提供了用于渲染的一些方法，例如render
 *
 * @property string|boolean $viewFile The view file currently being rendered. False if no view file is being
 * rendered. This property is read-only.
 * 属性 字符串或者boolean 当前被渲染的视图文件。如果没有渲染视图文件，就为false。该属性只读。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class View extends Component
{
    /**
     * @event Event an event that is triggered by [[beginPage()]].
     * 事件 被beginPage方法触发的事件
     */
    const EVENT_BEGIN_PAGE = 'beginPage';
    /**
     * @event Event an event that is triggered by [[endPage()]].
     * 被endPage方法触发的事件
     */
    const EVENT_END_PAGE = 'endPage';
    /**
     * @event ViewEvent an event that is triggered by [[renderFile()]] right before it renders a view file.
     * renderFile方法渲染视图文件以前触发的事件
     */
    const EVENT_BEFORE_RENDER = 'beforeRender';
    /**
     * @event ViewEvent an event that is triggered by [[renderFile()]] right after it renders a view file.
     * renderFile方式渲染视图以后触发的事件
     */
    const EVENT_AFTER_RENDER = 'afterRender';

    /**
     * @var ViewContextInterface the context under which the [[renderFile()]] method is being invoked.
     * renderFile方法被调用的时候的上下文环境。
     */
    public $context;
    /**
     * @var mixed custom parameters that are shared among view templates.
     * 变量 混合型 视图模板中公用的自定义参数
     */
    public $params = [];
    /**
     * @var array a list of available renderers indexed by their corresponding supported file extensions.
     * 变量 数组 以它们支持的文件类型为索引的可用渲染器的列表
     *
     * Each renderer may be a view renderer object or the configuration for creating the renderer object.
     * 每一个渲染器可以是一个视图渲染器对象或者创建渲染器对象的配置。
     *
     * For example, the following configuration enables both Smarty and Twig view renderers:
     * 例如，如下配置启用了Smarty和Twig两种视图渲染器：
     *
     * ```php
     * [
     *     'tpl' => ['class' => 'yii\smarty\ViewRenderer'],
     *     'twig' => ['class' => 'yii\twig\ViewRenderer'],
     * ]
     * ```
     *
     * If no renderer is available for the given view file, the view file will be treated as a normal PHP
     * and rendered via [[renderPhpFile()]].
     * 如果给定的视图文件没有可用的渲染器，视图文件将会被当做一个普通的PHP文件并通过[[renderPhpFile()]]方法渲染。
     */
    public $renderers;
    /**
     * @var string the default view file extension. This will be appended to view file names if they don't have file extensions.
     * 变量 字符串 默认的视图文件扩展名。如果视图文件没有扩展名，该属性就会追加到它们的后边。
     */
    public $defaultExtension = 'php';
    /**
     * @var Theme|array|string the theme object or the configuration for creating the theme object.
     * If not set, it means theming is not enabled.
     * 变量 主题|数组|字符串 主题对象或者创建主题对象的配置。如果没有设置，意味着没有启用主题。
     */
    public $theme;
    /**
     * @var array a list of named output blocks. The keys are the block names and the values
     * are the corresponding block content. You can call [[beginBlock()]] and [[endBlock()]]
     * to capture small fragments of a view. They can be later accessed somewhere else
     * through this property.
     * 变量 数组 已命名的输出区块列表。键是区块的名称，值是相应区块的内容。你可以调用[[beginBlock()]] 和 [[endBlock()]]方法去捕获视图的一个小片段。
     * 他们可以稍后通过该属性访问。
     */
    public $blocks;
    /**
     * @var array a list of currently active fragment cache widgets. This property
     * is used internally to implement the content caching feature. Do not modify it directly.
     * 变量 数组 当前激活的片段缓存小部件的列表。该属性内部使用，用来实现内容缓存特性，不要直接修改它。
     * @internal
     */
    public $cacheStack = [];
    /**
     * @var array a list of placeholders for embedding dynamic contents. This property
     * is used internally to implement the content caching feature. Do not modify it directly.
     * 变量 数组 用来嵌入动态内容的占位符列表。该属性在内部使用，用来实现内容缓存特性。不要直接修改它。
     * @internal
     */
    public $dynamicPlaceholders = [];

    /**
     * @var array the view files currently being rendered. There may be multiple view files being
     * rendered at a moment because one view may be rendered within another.
     * 变量 数组 当前正在被渲染的视图文件。可能会有多个文件同时被渲染，因为一个视图可能会在另外一个视图中渲染。
     */
    private $_viewFiles = [];


    /**
     * Initializes the view component.
     * 初始化视图组件。
     */
    public function init()
    {
        parent::init();
        if (is_array($this->theme)) {
            if (!isset($this->theme['class'])) {
                $this->theme['class'] = 'yii\base\Theme';
            }
            $this->theme = Yii::createObject($this->theme);
        } elseif (is_string($this->theme)) {
            $this->theme = Yii::createObject($this->theme);
        }
    }

    /**
     * Renders a view.
     * 渲染一个视图文件。
     *
     * The view to be rendered can be specified in one of the following formats:
     * 被渲染的视图文件可以通过一下的几种格式声明：
     *
     * - path alias (e.g. "@app/views/site/index");
     * - 路径别名（例如 "@app/views/site/index"）
     *
     * - absolute path within application (e.g. "//site/index"): the view name starts with double slashes.
     *   The actual view file will be looked for under the [[Application::viewPath|view path]] of the application.
     * - 在应用下的绝对路径（例如 "//site/index"）：视图名使用双斜线开始。将会在当前应用的[[Application::viewPath|view path]]目录下查找实际的视图文件
     *
     * - absolute path within current module (e.g. "/site/index"): the view name starts with a single slash.
     *   The actual view file will be looked for under the [[Module::viewPath|view path]] of the [[Controller::module|current module]].
     * - 当前模块的绝对路径（例如 "/site/index"）：视图文件使用单斜线开头。将会在[[Controller::module|current module]]模块下的[[Module::viewPath|view path]]
     *   模块视图路径下去查找实际的视图文件。
     *
     * - relative view (e.g. "index"): the view name does not start with `@` or `/`. The corresponding view file will be
     *   looked for under the [[ViewContextInterface::getViewPath()|view path]] of the view `$context`.
     *   If `$context` is not given, it will be looked for under the directory containing the view currently
     *   being rendered (i.e., this happens when rendering a view within another view).
     * - 相对路径（例如 "index"）：视图文件没有以`@` 或 `/`开头，相应的视图文件将会在`$context`的[[ViewContextInterface::getViewPath()|view path]]
     *   视图目录下查找。如果`$context`没有设置，它会查找当前正在被渲染的视图文件所在的目录（例如，这种情况发生在一个视图文件中渲染另外一个文件）。
     *
     * @param string $view the view name.
     * 参数 字符串 视图文件名
     *
     * @param array $params the parameters (name-value pairs) that will be extracted and made available in the view file.
     * 参数 数组 被提取并在视图文件中可用的参数（键值对）。
     *
     * @param object $context the context to be assigned to the view and can later be accessed via [[context]]
     * in the view. If the context implements [[ViewContextInterface]], it may also be used to locate
     * the view file corresponding to a relative view name.
     * 参数 对象 分配到视图的上下文，并稍后可以在视图中通过[[context]]访问。如果上下文实现了[[ViewContextInterface]]接口，它也可以根据相对路径
     * 去定位视图文件。
     *
     * @return string the rendering result
     * 返回值 字符串 渲染结果
     *
     * @throws ViewNotFoundException if the view file does not exist.
     * 当视图文件不存在时，抛出视图文件未找到异常。
     *
     * @throws InvalidCallException if the view cannot be resolved.
     * 当视图名无法理解时，抛出不合法的调用异常。
     *
     * @see renderFile()
     */
    public function render($view, $params = [], $context = null)
    {
        $viewFile = $this->findViewFile($view, $context);
        return $this->renderFile($viewFile, $params, $context);
    }

    /**
     * Finds the view file based on the given view name.
     * 根据给定的视图名超找视图文件。
     *
     * @param string $view the view name or the path alias of the view file. Please refer to [[render()]]
     * on how to specify this parameter.
     * 参数 字符串 视图文件名或者视图文件的别名。如何指定该参数，请参考[[render()]]方法
     *
     * @param object $context the context to be assigned to the view and can later be accessed via [[context]]
     * in the view. If the context implements [[ViewContextInterface]], it may also be used to locate
     * the view file corresponding to a relative view name.
     * 参数 对象 分配给视图文件的上下文，并且稍后可以通过[[context]]在视图文件访问。如果上下文实现了[[ViewContextInterface]]接口，它也可以
     * 根据视图的相对路径来定位视图文件
     *
     * @return string the view file path. Note that the file may not exist.
     * 返回值 字符串 视图文件的路径。请注意，视图文件有可能不存在。
     *
     * @throws InvalidCallException if a relative view name is given while there is no active context to
     * determine the corresponding view file.
     * 如果提供的相对文件名没有激活的上下文环境来决定相应的视图文件，就会抛出不合法的调用异常。
     */
    protected function findViewFile($view, $context = null)
    {
        if (strncmp($view, '@', 1) === 0) {
            // e.g. "@app/views/main"
            $file = Yii::getAlias($view);
        } elseif (strncmp($view, '//', 2) === 0) {
            // e.g. "//layouts/main"
            $file = Yii::$app->getViewPath() . DIRECTORY_SEPARATOR . ltrim($view, '/');
        } elseif (strncmp($view, '/', 1) === 0) {
            // e.g. "/site/index"
            if (Yii::$app->controller !== null) {
                $file = Yii::$app->controller->module->getViewPath() . DIRECTORY_SEPARATOR . ltrim($view, '/');
            } else {
                throw new InvalidCallException("Unable to locate view file for view '$view': no active controller.");
            }
        } elseif ($context instanceof ViewContextInterface) {
            $file = $context->getViewPath() . DIRECTORY_SEPARATOR . $view;
        } elseif (($currentViewFile = $this->getViewFile()) !== false) {
            $file = dirname($currentViewFile) . DIRECTORY_SEPARATOR . $view;
        } else {
            throw new InvalidCallException("Unable to resolve view file for view '$view': no active view context.");
        }

        if (pathinfo($file, PATHINFO_EXTENSION) !== '') {
            return $file;
        }
        $path = $file . '.' . $this->defaultExtension;
        if ($this->defaultExtension !== 'php' && !is_file($path)) {
            $path = $file . '.php';
        }

        return $path;
    }

    /**
     * Renders a view file.
     * 渲染一个视图文件。
     *
     * If [[theme]] is enabled (not null), it will try to render the themed version of the view file as long
     * as it is available.
     * 如果[[theme]]已开启（不为null），它就会在主题化的视图文件可用的情况下渲染主题视图文件。
     *
     * The method will call [[FileHelper::localize()]] to localize the view file.
     * 该方法会调用[[FileHelper::localize()]]来局部化视图文件。
     *
     * If [[renderers|renderer]] is enabled (not null), the method will use it to render the view file.
     * 如果[[renderers|renderer]]已开启（不为null），该方法会使用它来渲染一个视图文件。
     *
     * Otherwise, it will simply include the view file as a normal PHP file, capture its output and
     * return it as a string.
     * 否则，它仅把视图文件当做一个普通的PHP文件去包含，捕获它的输出并把捕获内容作为字符串返回。
     *
     * @param string $viewFile the view file. This can be either an absolute file path or an alias of it.
     * 参数 字符串 视图文件。可以是绝对路径或它的路径别名。
     *
     * @param array $params the parameters (name-value pairs) that will be extracted and made available in the view file.
     * 参数 数组 被提取并在视图文件中可用的参数（键值对）。
     *
     * @param object $context the context that the view should use for rendering the view. If null,
     * existing [[context]] will be used.
     * 参数 对象 渲染视图的时候使用的上下文。如果为null，就会使用已经存在的[[context]]
     *
     * @return string the rendering result
     * 返回值 字符串 渲染的结果
     *
     * @throws ViewNotFoundException if the view file does not exist
     * 如果视图文件不存在，抛出视图文件没有找到的异常
     */
    public function renderFile($viewFile, $params = [], $context = null)
    {
        $viewFile = Yii::getAlias($viewFile);

        if ($this->theme !== null) {
            $viewFile = $this->theme->applyTo($viewFile);
        }
        if (is_file($viewFile)) {
            $viewFile = FileHelper::localize($viewFile);
        } else {
            throw new ViewNotFoundException("The view file does not exist: $viewFile");
        }

        $oldContext = $this->context;
        if ($context !== null) {
            $this->context = $context;
        }
        $output = '';
        $this->_viewFiles[] = $viewFile;

        if ($this->beforeRender($viewFile, $params)) {
            Yii::trace("Rendering view file: $viewFile", __METHOD__);
            $ext = pathinfo($viewFile, PATHINFO_EXTENSION);
            if (isset($this->renderers[$ext])) {
                if (is_array($this->renderers[$ext]) || is_string($this->renderers[$ext])) {
                    $this->renderers[$ext] = Yii::createObject($this->renderers[$ext]);
                }
                /* @var $renderer ViewRenderer */
                $renderer = $this->renderers[$ext];
                $output = $renderer->render($this, $viewFile, $params);
            } else {
                $output = $this->renderPhpFile($viewFile, $params);
            }
            $this->afterRender($viewFile, $params, $output);
        }

        array_pop($this->_viewFiles);
        $this->context = $oldContext;

        return $output;
    }

    /**
     * @return string|boolean the view file currently being rendered. False if no view file is being rendered.
     * 返回值 字符串|boolean 当前增在渲染的视图文件。如果没有渲染文件，就会返回false。
     */
    public function getViewFile()
    {
        return end($this->_viewFiles);
    }

    /**
     * This method is invoked right before [[renderFile()]] renders a view file.
     * 该方法会在[[renderFile()]]方法渲染视图文件以前调用。
     *
     * The default implementation will trigger the [[EVENT_BEFORE_RENDER]] event.
     * 默认的实现会触发[[EVENT_BEFORE_RENDER]]（渲染前事件）事件。
     *
     * If you override this method, make sure you call the parent implementation first.
     * 如果你重写此方法，确保首先调用了父类的实现。
     *
     * @param string $viewFile the view file to be rendered.
     * 参数 字符串 被渲染的视图文件。
     *
     * @param array $params the parameter array passed to the [[render()]] method.
     * 参数 数组 传递给[[render()]]方法的参数数组。
     *
     * @return boolean whether to continue rendering the view file.
     * 返回值 boolean 是否继续渲染视图文件。
     */
    public function beforeRender($viewFile, $params)
    {
        $event = new ViewEvent([
            'viewFile' => $viewFile,
            'params' => $params,
        ]);
        $this->trigger(self::EVENT_BEFORE_RENDER, $event);

        return $event->isValid;
    }

    /**
     * This method is invoked right after [[renderFile()]] renders a view file.
     * 该方法会在[[renderFile()]]方法渲染视图文件以后立即调用。
     *
     * The default implementation will trigger the [[EVENT_AFTER_RENDER]] event.
     * 默认的实现会触发[[EVENT_AFTER_RENDER]]（渲染结束事件）事件
     *
     * If you override this method, make sure you call the parent implementation first.
     * 如果你重写此方法，需要确保首先调用了父类的实现
     *
     * @param string $viewFile the view file being rendered.
     * 参数 字符串 被渲染的视图文件。
     *
     * @param array $params the parameter array passed to the [[render()]] method.
     * 参数 数组 传递给[[render()]]方法的参数数组。
     *
     * @param string $output the rendering result of the view file. Updates to this parameter
     * will be passed back and returned by [[renderFile()]].
     * 参数 字符串 视图文件的渲染结果。对该参数的更新将会被传递回并被[[renderFile()]]方法返回。
     */
    public function afterRender($viewFile, $params, &$output)
    {
        if ($this->hasEventHandlers(self::EVENT_AFTER_RENDER)) {
            $event = new ViewEvent([
                'viewFile' => $viewFile,
                'params' => $params,
                'output' => $output,
            ]);
            $this->trigger(self::EVENT_AFTER_RENDER, $event);
            $output = $event->output;
        }
    }

    /**
     * Renders a view file as a PHP script.
     * 渲染视图文件中的PHP脚本。
     *
     * This method treats the view file as a PHP script and includes the file.
     * 该方法把视图文件当做PHP脚本并引入该文件。
     *
     * It extracts the given parameters and makes them available in the view file.
     * 它提取给定的参数并使他们在视图文件中可用。
     *
     * The method captures the output of the included view file and returns it as a string.
     * 该方法捕获引入文件的输出并把它当做一个字符串返回。
     *
     * This method should mainly be called by view renderer or [[renderFile()]].
     * 该方法应该主要被视图渲染器或者[[renderFile()]]方法调用。
     *
     * @param string $_file_ the view file.
     * 参数 字符串 视图文件
     *
     * @param array $_params_ the parameters (name-value pairs) that will be extracted and made available in the view file.
     * 参数 数组 被提取并在视图文件中可用的参数（键值对）。
     *
     * @return string the rendering result
     * 返回值 字符串 渲染结果。
     */
    public function renderPhpFile($_file_, $_params_ = [])
    {
        ob_start();
        ob_implicit_flush(false);
        extract($_params_, EXTR_OVERWRITE);
        require($_file_);

        return ob_get_clean();
    }

    /**
     * Renders dynamic content returned by the given PHP statements.
     * 渲染给定的PHP语句返回的动态内容。
     *
     * This method is mainly used together with content caching (fragment caching and page caching)
     * when some portions of the content (called *dynamic content*) should not be cached.
     * 当某些部分的内容（称作动态内容）不应该被缓存时，该方法主要配合内容缓存（片段缓存和页面缓存）使用。
     *
     * The dynamic content must be returned by some PHP statements.
     * 动态内容必须是由某些PHP语句返回的。
     *
     * @param string $statements the PHP statements for generating the dynamic content.
     * 参数 字符串 用来生成动态内容的PHP语句。
     *
     * @return string the placeholder of the dynamic content, or the dynamic content if there is no
     * active content cache currently.
     * 返回值 字符串 动态内容的占位符，或者当前没有活动内容缓存时，返回动态内容。
     */
    public function renderDynamic($statements)
    {
        if (!empty($this->cacheStack)) {
            $n = count($this->dynamicPlaceholders);
            $placeholder = "<![CDATA[YII-DYNAMIC-$n]]>";
            $this->addDynamicPlaceholder($placeholder, $statements);

            return $placeholder;
        } else {
            return $this->evaluateDynamicContent($statements);
        }
    }

    /**
     * Adds a placeholder for dynamic content.
     * 为动态内容添加一个占位符。
     *
     * This method is internally used.
     * 该方法是内部使用的。
     *
     * @param string $placeholder the placeholder name
     * 参数 字符串 占位符的名称。
     *
     * @param string $statements the PHP statements for generating the dynamic content
     * 参数 字符串 生成动态内容的PHP语句。
     */
    public function addDynamicPlaceholder($placeholder, $statements)
    {
        foreach ($this->cacheStack as $cache) {
            $cache->dynamicPlaceholders[$placeholder] = $statements;
        }
        $this->dynamicPlaceholders[$placeholder] = $statements;
    }

    /**
     * Evaluates the given PHP statements.
     * 运行给定的PHP语句。
     *
     * This method is mainly used internally to implement dynamic content feature.
     * 该方法主要在内容使用，用来实现动态内容特性。
     *
     * @param string $statements the PHP statements to be evaluated.
     * 参数 字符串 要被执行的PHP语句
     *
     * @return mixed the return value of the PHP statements.
     * 返回值 混合型 PHP语句返回值
     */
    public function evaluateDynamicContent($statements)
    {
        return eval($statements);
    }

    /**
     * Begins recording a block.
     * 开始记录一个区块
     *
     * This method is a shortcut to beginning [[Block]]
     * 该方法是开启[[Block]]的一个快捷方法。
     *
     * @param string $id the block ID.
     * 参数 字符串 区块ID
     *
     * @param boolean $renderInPlace whether to render the block content in place.
     * Defaults to false, meaning the captured block will not be displayed.
     * 参数 boolean 是否在恰当的位置渲染区块。默认是false，表示当前区块不会被展示。
     *
     * @return Block the Block widget instance
     * 返回值 区块小部件实例。
     */
    public function beginBlock($id, $renderInPlace = false)
    {
        return Block::begin([
            'id' => $id,
            'renderInPlace' => $renderInPlace,
            'view' => $this,
        ]);
    }

    /**
     * Ends recording a block.
     * 结束对一个区块的记录
     */
    public function endBlock()
    {
        Block::end();
    }

    /**
     * Begins the rendering of content that is to be decorated by the specified view.
     * 开始渲染被指定视图修饰的内容
     *
     * This method can be used to implement nested layout. For example, a layout can be embedded
     * in another layout file specified as '@app/views/layouts/base.php' like the following:
     * 该方法可以用来实现嵌套布局。例如，一个布局文件可以嵌套到其他布局文件，指定方式'@app/views/layouts/base.php'，如下：
     *
     * ```php
     * <?php $this->beginContent('@app/views/layouts/base.php'); ?>
     * //...layout content here...
     * //...在这里输出布局内容...
     *
     * <?php $this->endContent(); ?>
     * ```
     *
     * @param string $viewFile the view file that will be used to decorate the content enclosed by this widget.
     * This can be specified as either the view file path or path alias.
     * 参数 字符串 用来修饰附到小部件的内容的视图文件。可以通过视图文件路径或者路径别名指定。
     *
     * @param array $params the variables (name => value) to be extracted and made available in the decorative view.
     * 参数 数组 被提取并在被渲染的视图可用的变量（键值对）
     *
     * @return ContentDecorator the ContentDecorator widget instance
     * 返回值 页面装饰者的挂件实例。
     *
     * @see ContentDecorator
     */
    public function beginContent($viewFile, $params = [])
    {
        return ContentDecorator::begin([
            'viewFile' => $viewFile,
            'params' => $params,
            'view' => $this,
        ]);
    }

    /**
     * Ends the rendering of content.
     * 结束对内容的渲染。
     */
    public function endContent()
    {
        ContentDecorator::end();
    }

    /**
     * Begins fragment caching.
     * 开启片段缓存。
     *
     * This method will display cached content if it is available.
     * 如果缓存可用，该方法会显示已经缓存过的数据。
     *
     * If not, it will start caching and would expect an [[endCache()]]
     * call to end the cache and save the content into cache.
     * 如果不可用，它就会开启缓存并需要开启[[endCache()]]关闭缓存，并把内容保存到缓存之中。
     *
     * A typical usage of fragment caching is as follows,
     * 经典的片段缓存用法如下，
     *
     * ```php
     * if ($this->beginCache($id)) {
     *     // ...generate content here
     *     // ...在这里生成内容
     *
     *     $this->endCache();
     * }
     * ```
     *
     * @param string $id a unique ID identifying the fragment to be cached.
     * 参数 字符串 区分片段缓存的唯一ID
     *
     * @param array $properties initial property values for [[FragmentCache]]
     * 参数 数组 片段缓存的初始属性值。
     *
     * @return boolean whether you should generate the content for caching.
     * False if the cached version is available.
     * 返回值 boolean 你是否应该生成缓存内容。如果缓存的版本还可用，就返回false。
     */
    public function beginCache($id, $properties = [])
    {
        $properties['id'] = $id;
        $properties['view'] = $this;
        /* @var $cache FragmentCache */
        $cache = FragmentCache::begin($properties);
        if ($cache->getCachedContent() !== false) {
            $this->endCache();

            return false;
        } else {
            return true;
        }
    }

    /**
     * Ends fragment caching.
     * 结束片段缓存
     */
    public function endCache()
    {
        FragmentCache::end();
    }

    /**
     * Marks the beginning of a page.
     * 标记一个页面的开始
     */
    public function beginPage()
    {
        ob_start();
        ob_implicit_flush(false);

        $this->trigger(self::EVENT_BEGIN_PAGE);
    }

    /**
     * Marks the ending of a page.
     * 标记一个页面的结束
     */
    public function endPage()
    {
        $this->trigger(self::EVENT_END_PAGE);
        ob_end_flush();
    }
}
