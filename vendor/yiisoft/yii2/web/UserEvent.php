<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use yii\base\Event;

/**
 * This event class is used for Events triggered by the [[User]] class.
 * 该事件类用于被User类触发的事件
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class UserEvent extends Event
{
    /**
     * @var IdentityInterface the identity object associated with this event
     * 跟该事件相关的认证对象。
     */
    public $identity;
    /**
     * @var boolean whether the login is cookie-based. This property is only meaningful
     * for [[User::EVENT_BEFORE_LOGIN]] and [[User::EVENT_AFTER_LOGIN]] events.
     * 登陆动作是否基于cookie。 该属性只对[[User::EVENT_BEFORE_LOGIN]] 和 [[User::EVENT_AFTER_LOGIN]]事件有意义
     */
    public $cookieBased;
    /**
     * @var integer $duration number of seconds that the user can remain in logged-in status.
     * 用户可以保持登陆状态的秒数
     *
     * If 0, it means login till the user closes the browser or the session is manually destroyed.
     * 如果是0，意味着登陆会持续到用户关闭浏览器或者session被手动销毁。
     */
    public $duration;
    /**
     * @var boolean whether the login or logout should proceed.
     * 登陆或退出是否继续进行
     *
     * Event handlers may modify this property to determine whether the login or logout should proceed.
     * 事件处理程序可以修改该属性来决定登陆或退出的操作是否继续。
     *
     * This property is only meaningful for [[User::EVENT_BEFORE_LOGIN]] and [[User::EVENT_BEFORE_LOGOUT]] events.
     * 该属性只对[[User::EVENT_BEFORE_LOGIN]] 和 [[User::EVENT_BEFORE_LOGOUT]]有意义
     */
    public $isValid = true;
}
