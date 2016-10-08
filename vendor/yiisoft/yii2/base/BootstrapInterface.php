<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * BootstrapInterface is the interface that should be implemented by classes who want to participate in the application bootstrap process.
 * BootstrapInterface接口是需要加入应用引导流程的类去实现的。
 *
 * The main method [[bootstrap()]] will be invoked by an application at the beginning of its `init()` method.
 * [[bootstrap()]]方法会在应用程序执行init方法以前调用
 *
 * Bootstrapping classes can be registered in two approaches.
 * 引导类可以通过两种方式注册
 *
 * The first approach is mainly used by extensions and is managed by the Composer installation process.
 * You mainly need to list the bootstrapping class of your extension in the `composer.json` file like following,
 * 第一种，使用扩展，这些扩展在安装yii的时候被Composer管理
 * 你可以在composer.json文件中列出你的扩展引导类
 *
 * ```json
 * {
 *     // ...
 *     "extra": {
 *         "bootstrap": "path\\to\\MyBootstrapClass"
 *     }
 * }
 * ```
 *
 * If the extension is installed, the bootstrap information will be saved in [[Application::extensions]].
 * 如果扩展已经被安装，引导信息会被保存在[[Application::extensions]]里
 *
 * The second approach is used by application code which needs to register some code to be run during
 * the bootstrap process. This is done by configuring the [[Application::bootstrap]] property:
 * 第二种方式是使用的应用程序代码，在引导过程中注册的一些代码。可以通过配置[[Application::bootstrap]]属性来实现：
 *
 * ```php
 * return [
 *     // ...
 *     'bootstrap' => [
 *         "path\\to\\MyBootstrapClass1",
 *         [
 *             'class' => "path\\to\\MyBootstrapClass2",
 *             'prop1' => 'value1',
 *             'prop2' => 'value2',
 *         ],
 *     ],
 * ];
 * ```
 *
 * As you can see, you can register a bootstrapping class in terms of either a class name or a configuration class.
 * 根据以上的例子可以看出，您可以依据类名或者配置文件注册引导类
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
interface BootstrapInterface
{
    /**
     * Bootstrap method to be called during application bootstrap stage.
     * 应用程序引导阶段调用的引导方法
     * @param Application $app the application currently running
     * 参数 应用 当前正在运行的应用
     */
    public function bootstrap($app);
}
