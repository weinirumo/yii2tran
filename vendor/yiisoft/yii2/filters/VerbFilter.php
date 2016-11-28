<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\filters;

use Yii;
use yii\base\ActionEvent;
use yii\base\Behavior;
use yii\web\Controller;
use yii\web\MethodNotAllowedHttpException;

/**
 * VerbFilter is an action filter that filters by HTTP request methods.
 * VerbFilter是一个通过过滤HTTP请求方法的动作过滤器
 *
 * It allows to define allowed HTTP request methods for each action and will throw
 * an HTTP 405 error when the method is not allowed.
 * 它定义了每个动作可被访问的HTTP请求方法，当方法不允许访问时，抛出HTTP405错误
 *
 * To use VerbFilter, declare it in the `behaviors()` method of your controller class.
 * For example, the following declarations will define a typical set of allowed
 * request methods for REST CRUD actions.
 * 要使用VerbFilter,可以在你的控制器类的behaviors方法中声明它。例如，如下的声明就会为REST CURD动作定义一系列经典可被请求的方法。
 *
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'verbs' => [
 *             'class' => \yii\filters\VerbFilter::className(),
 *             'actions' => [
 *                 'index'  => ['get'],
 *                 'view'   => ['get'],
 *                 'create' => ['get', 'post'],
 *                 'update' => ['get', 'put', 'post'],
 *                 'delete' => ['post', 'delete'],
 *             ],
 *         ],
 *     ];
 * }
 * ```
 *
 * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.7
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class VerbFilter extends Behavior
{
    /**
     * @var array this property defines the allowed request methods for each action.
     * 属性 数组 该属性为每一个动作定义可以被请求的方法
     * For each action that should only support limited set of request methods
     * you add an entry with the action id as array key and an array of
     * allowed methods (e.g. GET, HEAD, PUT) as the value.
     * 对于每个只支持有限个请求方式的动作，你可以添加一个条目，使用动作id当做数组的键，一个被允许的方式组成的数组当做值。
     * If an action is not listed all request methods are considered allowed.
     * 如果一个动作没有被列在所有请求的方法里边，会被当做允许访问。（把理解重点放到动作上，就明白这句话的意思了）
     *
     * You can use `'*'` to stand for all actions. When an action is explicitly
     * specified, it takes precedence over the specification given by `'*'`.
     * 你可以使用'*'来代表所有的动作。当一个动作被明确指定，它的优先级就会高于'*'定义的方法。
     *
     * For example,
     * 例如，
     *
     * ```php
     * [
     *   'create' => ['get', 'post'],
     *   'update' => ['get', 'put', 'post'],
     *   'delete' => ['post', 'delete'],
     *   '*' => ['get'],
     * ]
     * ```
     */
    public $actions = [];


    /**
     * Declares event handlers for the [[owner]]'s events.
     * 为owner的事件声明事件处理程序。
     * @return array events (array keys) and the corresponding event handler methods (array values).
     * 返回值 数组 事件（数组的键）和相应的事件处理程序方法（数组的值）
     */
    public function events()
    {
        return [Controller::EVENT_BEFORE_ACTION => 'beforeAction'];
    }

    /**
     * @param ActionEvent $event
     * 参数 动作事件
     * @return boolean
     * 返回值 boolean
     * @throws MethodNotAllowedHttpException when the request method is not allowed.
     * 当请求方式被禁止时，抛出方法不能访问异常
     */
    public function beforeAction($event)
    {
        $action = $event->action->id;
        if (isset($this->actions[$action])) {
            $verbs = $this->actions[$action];
        } elseif (isset($this->actions['*'])) {
            $verbs = $this->actions['*'];
        } else {
            return $event->isValid;
        }

        $verb = Yii::$app->getRequest()->getMethod();
        $allowed = array_map('strtoupper', $verbs);
        if (!in_array($verb, $allowed)) {
            $event->isValid = false;
            // http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.7
            Yii::$app->getResponse()->getHeaders()->set('Allow', implode(', ', $allowed));
            throw new MethodNotAllowedHttpException('Method Not Allowed. This url can only handle the following request methods: ' . implode(', ', $allowed) . '.');
        }

        return $event->isValid;
    }
}
