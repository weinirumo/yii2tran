<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\InlineAction;
use yii\helpers\Url;

/**
 * Controller is the base class of web controllers.
 * Controller是web控制器的基类
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Controller extends \yii\base\Controller
{
    /**
     * @var boolean whether to enable CSRF validation for the actions in this controller.
     * 属性 boolean 是否在该控制器里开启csrf验证
     * CSRF validation is enabled only when both this property and [[\yii\web\Request::enableCsrfValidation]] are true.
     * 当该属性和[[\yii\web\Request::enableCsrfValidation]]都为true的时候，csrf验证才会启用
     */
    public $enableCsrfValidation = true;
    /**
     * @var array the parameters bound to the current action.
     * 属性 数组 绑定到当前动作的参数
     */
    public $actionParams = [];


    /**
     * Renders a view in response to an AJAX request.
     * 在ajax请求中渲染视图。
     *
     * This method is similar to [[renderPartial()]] except that it will inject into
     * the rendering result with JS/CSS scripts and files which are registered with the view.
     * 该方法跟renderPartial相似，除了它会把视图注册的js和css脚本添加到渲染结果中
     * For this reason, you should use this method instead of [[renderPartial()]] to render
     * a view to respond to an AJAX request.
     * 因此，你应该使用该方法，而不是renderPartial，去渲染ajax请求的视图
     *
     * @param string $view the view name. Please refer to [[render()]] on how to specify a view name.
     * 参数 字符串 视图名，请参考render方法查看如何指定视图名
     * @param array $params the parameters (name-value pairs) that should be made available in the view.
     * 参数 数组 在视图中可用的参数键值对
     * @return string the rendering result.
     * 返回值 字符串 渲染结果
     */
    public function renderAjax($view, $params = [])
    {
        return $this->getView()->renderAjax($view, $params, $this);
    }

    /**
     * Binds the parameters to the action.
     * 给动作绑定参数
     * This method is invoked by [[\yii\base\Action]] when it begins to run with the given parameters.
     * 该方法被调用，当[[\yii\base\Action]]开始使用给定参数运行的时候。
     * This method will check the parameter names that the action requires and return
     * the provided parameters according to the requirement. If there is any missing parameter,
     * an exception will be thrown.
     * 该方法会检测动作所需的参数名，根据需要返回提供的参数。如果有任何的参数疏漏，就会抛出异常
     * @param \yii\base\Action $action the action to be bound with parameters
     * 参数 参数被绑定的动作
     * @param array $params the parameters to be bound to the action
     * 参数 数组 被绑定的参数
     * @return array the valid parameters that the action can run with.
     * 返回值 数组 该动作可以使用的合法参数
     * @throws BadRequestHttpException if there are missing or invalid parameters.
     * 抛出 异常请求异常 如果有参数丢失或者不合法的时候
     */
    public function bindActionParams($action, $params)
    {
        if ($action instanceof InlineAction) {
            $method = new \ReflectionMethod($this, $action->actionMethod);
        } else {
            $method = new \ReflectionMethod($action, 'run');
        }

        $args = [];
        $missing = [];
        $actionParams = [];
        foreach ($method->getParameters() as $param) {
            $name = $param->getName();
            if (array_key_exists($name, $params)) {
                if ($param->isArray()) {
                    $args[] = $actionParams[$name] = (array) $params[$name];
                } elseif (!is_array($params[$name])) {
                    $args[] = $actionParams[$name] = $params[$name];
                } else {
                    throw new BadRequestHttpException(Yii::t('yii', 'Invalid data received for parameter "{param}".', [
                        'param' => $name,
                    ]));
                }
                unset($params[$name]);
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $actionParams[$name] = $param->getDefaultValue();
            } else {
                $missing[] = $name;
            }
        }

        if (!empty($missing)) {
            throw new BadRequestHttpException(Yii::t('yii', 'Missing required parameters: {params}', [
                'params' => implode(', ', $missing),
            ]));
        }

        $this->actionParams = $actionParams;

        return $args;
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            if ($this->enableCsrfValidation && Yii::$app->getErrorHandler()->exception === null && !Yii::$app->getRequest()->validateCsrfToken()) {
                throw new BadRequestHttpException(Yii::t('yii', 'Unable to verify your data submission.'));
            }
            return true;
        }
        
        return false;
    }

    /**
     * Redirects the browser to the specified URL.
     * 把浏览器重定向到指定url
     * This method is a shortcut to [[Response::redirect()]].
     * 该方法是Response::redirect()的快捷方式。
     *
     * You can use it in an action by returning the [[Response]] directly:
     * 你可以在动作中使用此方法，直接返回response
     *
     * ```php
     * // stop executing this action and redirect to login page
     * // 停止执行动作，并重定向到登陆页面
     * return $this->redirect(['login']);
     * ```
     *
     * @param string|array $url the URL to be redirected to. This can be in one of the following formats:
     * 参数 字符串或数组 重定向的url链接。可以是如下的格式：
     *
     * - a string representing a URL (e.g. "http://example.com")
     * - 一个字符串表示的url，例如"http://example.com"
     * - a string representing a URL alias (e.g. "@example.com")
     * - 字符串表示的url别名，例如"@example.com"
     * - an array in the format of `[$route, ...name-value pairs...]` (e.g. `['site/index', 'ref' => 1]`)
     *   [[Url::to()]] will be used to convert the array into a URL.
     * - 形如[$route, ...name-value pairs...]格式的数组，Url::to()方法会把数组转化成一个url
     *
     * Any relative URL will be converted into an absolute one by prepending it with the host info
     * of the current request.
     * 通过添加当前请求的主机信息，会吧相对url转化成绝对url
     *
     * @param integer $statusCode the HTTP status code. Defaults to 302.
     * 参数 整型 http响应状态码，默认是302
     * See <http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html>
     * for details about HTTP status code
     * 访问<http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html>查看更多的http响应状态码
     * @return Response the current response object
     * 返回值 当前的响应对象
     */
    public function redirect($url, $statusCode = 302)
    {
        return Yii::$app->getResponse()->redirect(Url::to($url), $statusCode);
    }

    /**
     * Redirects the browser to the home page.
     * 把浏览器重定向到home页面
     *
     * You can use this method in an action by returning the [[Response]] directly:
     * 你可以在动作中使用此方法，直接返回response
     *
     * ```php
     * // stop executing this action and redirect to home page
     * // 停止执行该动作，并且重定向到home页面
     * return $this->goHome();
     * ```
     *
     * @return Response the current response object
     * 返回值 当前响应对象
     */
    public function goHome()
    {
        return Yii::$app->getResponse()->redirect(Yii::$app->getHomeUrl());
    }

    /**
     * Redirects the browser to the last visited page.
     * 把浏览器的页面重定向到上一次访问的页面
     *
     * You can use this method in an action by returning the [[Response]] directly:
     * 你可以在动作使用此方法，直接返回[[Response]]
     *
     * ```php
     * // stop executing this action and redirect to last visited page
     * // 停止执行当前动作，并且跳转到前一个页面
     * return $this->goBack();
     * ```
     *
     * For this function to work you have to [[User::setReturnUrl()|set the return URL]] in appropriate places before.
     * 使用此方法以前，你需要在合适的位置调用User::setReturnUrl()方法，设置返回的链接。
     *
     * @param string|array $defaultUrl the default return URL in case it was not set previously.
     * 参数 字符串或数组 如果之前没有设置url时的默认返回链接
     * If this is null and the return URL was not set previously, [[Application::homeUrl]] will be redirected to.
     * 如果该项为空，并且没有设置返回链接，那么将会重定向到Application::homeUrl
     * Please refer to [[User::setReturnUrl()]] on accepted format of the URL.
     * 请参考User::setReturnUrl()查看可用的url格式
     * @return Response the current response object
     * 返回值 当前的response对象
     * @see User::getReturnUrl()
     */
    public function goBack($defaultUrl = null)
    {
        return Yii::$app->getResponse()->redirect(Yii::$app->getUser()->getReturnUrl($defaultUrl));
    }

    /**
     * Refreshes the current page.
     * 刷新当前页面
     * This method is a shortcut to [[Response::refresh()]].
     * 该方法是Response::refresh()方法的快捷方式
     *
     * You can use it in an action by returning the [[Response]] directly:
     * 你可以在动作中调用此方法，直接返回[[Response]]
     *
     * ```php
     * // stop executing this action and refresh the current page
     * // 停止执行当前动作，并且刷新当前页面
     * return $this->refresh();
     * ```
     *
     * @param string $anchor the anchor that should be appended to the redirection URL.
     * 参数 字符串 添加到重定向url的锚点
     * Defaults to empty. Make sure the anchor starts with '#' if you want to specify it.
     * 默认是空。如果需要指定的时候，需要在锚点前边加上#号
     * @return Response the response object itself
     * 返回值 response对象本身
     */
    public function refresh($anchor = '')
    {
        return Yii::$app->getResponse()->redirect(Yii::$app->getRequest()->getUrl() . $anchor);
    }
}
