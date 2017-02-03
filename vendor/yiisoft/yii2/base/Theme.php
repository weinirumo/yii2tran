<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;
use yii\helpers\FileHelper;

/**
 * Theme represents an application theme.
 * Theme代表了一个应用的主题。
 *
 * When [[View]] renders a view file, it will check the [[View::theme|active theme]]
 * to see if there is a themed version of the view file exists. If so, the themed version will be rendered instead.
 * 当视图渲染一个视图文件时，它会先检测[[View::theme|active theme]]下的是否存在主题版本的视图文件。如果存在，将会使用主题版本去渲染。
 *
 * A theme is a directory consisting of view files which are meant to replace their non-themed counterparts.
 * 主题是一个包含视图文件的目录，这些视图文件代替了对应没有主题的文件。
 *
 * Theme uses [[pathMap]] to achieve the view file replacement:
 * 主题使用[[pathMap]]（路径映射）来达到视图文件的替换：
 *
 * 1. It first looks for a key in [[pathMap]] that is a substring of the given view file path;
 * 1. 它首先查找[[pathMap]]里边的一个key，[[pathMap]]是给定视图文件路径的子字符串；
 *
 * 2. If such a key exists, the corresponding value will be used to replace the corresponding part
 *    in the view file path;
 * 2. 如果该key存在，相应的值将会用来替换视图文件路径中对应的部分；
 *
 * 3. It will then check if the updated view file exists or not. If so, that file will be used
 *    to replace the original view file.
 * 3. 然后会检测更新后的视图文件是否存在。如果存在，那个文件将会被代替原来的视图文件。
 *
 * 4. If Step 2 or 3 fails, the original view file will be used.
 * 4. 如果步骤2或3失败，将会使用原始的视图文件。
 *
 * For example, if [[pathMap]] is `['@app/views' => '@app/themes/basic']`,
 * then the themed version for a view file `@app/views/site/index.php` will be
 * `@app/themes/basic/site/index.php`.
 * 例如，如果[[pathMap]]是`['@app/views' => '@app/themes/basic']`,那么`@app/views/site/index.php`的主题化视图文件的路径就是
 * `@app/themes/basic/site/index.php`
 *
 * It is possible to map a single path to multiple paths. For example,
 * 可以把单个视图文件映射到多个主题路径。例如,
 *
 * ```php
 * 'pathMap' => [
 *     '@app/views' => [
 *         '@app/themes/christmas',
 *         '@app/themes/basic',
 *     ],
 * ]
 * ```
 *
 * In this case, the themed version could be either `@app/themes/christmas/site/index.php` or
 * `@app/themes/basic/site/index.php`. The former has precedence over the latter if both files exist.
 * 这种情况下，主题版本可以是`@app/themes/christmas/site/index.php` 或 `@app/themes/basic/site/index.php`。
 * 如果两个都存在的话，前边的拥有优先权。
 *
 * To use a theme, you should configure the [[View::theme|theme]] property of the "view" application
 * component like the following:
 * 为了使用主题，你需要配置view应用组件的[[View::theme|theme]]属性，格式如下：
 *
 * ```php
 * 'view' => [
 *     'theme' => [
 *         'basePath' => '@app/themes/basic',
 *         'baseUrl' => '@web/themes/basic',
 *     ],
 * ],
 * ```
 *
 * The above configuration specifies a theme located under the "themes/basic" directory of the Web folder
 * that contains the entry script of the application. If your theme is designed to handle modules,
 * you may configure the [[pathMap]] property like described above.
 * 上边的配置指定了位于包含应用入口脚本Web文件夹下的themes/basic目录。如果你的主题用来处理模块，你可以向上边示例一样配置其[[pathMap]]属性。
 *
 * @property string $basePath The root path of this theme. All resources of this theme are located under this
 * directory.
 * 属性 字符串 该主题的根路径。该主题的所有资源文件都在该目录下。
 *
 * @property string $baseUrl The base URL (without ending slash) for this theme. All resources of this theme
 * are considered to be under this base URL.
 * 属性 字符串 该主题的基础URL（不带有结束的斜线）。该主题的所有资源都应该在该基础URL之下。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Theme extends Component
{
    /**
     * @var array the mapping between view directories and their corresponding themed versions.
     * 属性 数组 视图目录和相应的主题版本之间的映射关系。
     *
     * This property is used by [[applyTo()]] when a view is trying to apply the theme.
     * 当视图尝试使用主题的时候，[[applyTo()]]方法会使用该属性
     *
     * Path aliases can be used when specifying directories.
     * 当指定了目录以后，可以使用路径别名。
     *
     * If this property is empty or not set, a mapping [[Application::basePath]] to [[basePath]] will be used.
     * 如果该属性为空，或者没有设置，将会使用[[Application::basePath]]到[[basePath]]的映射。
     */
    public $pathMap;

    private $_baseUrl;


    /**
     * @return string the base URL (without ending slash) for this theme. All resources of this theme are considered
     * to be under this base URL.
     * 返回值 字符串 该主题没有结束斜线的基础URL。推荐该主题的所有的资源都放在此URL下。
     */
    public function getBaseUrl()
    {
        return $this->_baseUrl;
    }

    /**
     * @param string $url the base URL or path alias for this theme. All resources of this theme are considered
     * to be under this base URL.
     * 参数 字符串 该主题的基础URL或者路径别名。推荐该主题的所有的资源都放在此URL下。
     */
    public function setBaseUrl($url)
    {
        $this->_baseUrl = rtrim(Yii::getAlias($url), '/');
    }

    private $_basePath;

    /**
     * @return string the root path of this theme. All resources of this theme are located under this directory.
     * 返回值 字符串 该主题的根路径。该主题的所有资源都要放在此目录。
     * @see pathMap
     */
    public function getBasePath()
    {
        return $this->_basePath;
    }

    /**
     * @param string $path the root path or path alias of this theme. All resources of this theme are located
     * under this directory.
     * 参数 字符串 该主题的根路径或者路径别名。该主题的所有资源都要放在此目录。
     *
     * @see pathMap
     */
    public function setBasePath($path)
    {
        $this->_basePath = Yii::getAlias($path);
    }

    /**
     * Converts a file to a themed file if possible.
     * 如果可行，就把一个文件转化成一个主题文件。
     *
     * If there is no corresponding themed file, the original file will be returned.
     * 如果没有找到相应的主题文件，原始文件就会被返回。
     *
     * @param string $path the file to be themed
     * 参数 字符串 将要使用主题的文件
     *
     * @return string the themed file, or the original file if the themed version is not available.
     * 返回值 字符串 主题化后的文件，或者主题化的版本不可用的时候返回原始的文件。
     *
     * @throws InvalidConfigException if [[basePath]] is not set
     * 当[[basePath]]没有设置的时候，抛出不合法的配置异常。
     */
    public function applyTo($path)
    {
        $pathMap = $this->pathMap;
        if (empty($pathMap)) {
            if (($basePath = $this->getBasePath()) === null) {
                throw new InvalidConfigException('The "basePath" property must be set.');
            }
            $pathMap = [Yii::$app->getBasePath() => [$basePath]];
        }

        $path = FileHelper::normalizePath($path);

        foreach ($pathMap as $from => $tos) {
            $from = FileHelper::normalizePath(Yii::getAlias($from)) . DIRECTORY_SEPARATOR;
            if (strpos($path, $from) === 0) {
                $n = strlen($from);
                foreach ((array) $tos as $to) {
                    $to = FileHelper::normalizePath(Yii::getAlias($to)) . DIRECTORY_SEPARATOR;
                    $file = $to . substr($path, $n);
                    if (is_file($file)) {
                        return $file;
                    }
                }
            }
        }

        return $path;
    }

    /**
     * Converts a relative URL into an absolute URL using [[baseUrl]].
     * 使用[[baseUrl]]把一个相对URL转换成绝对URL
     *
     * @param string $url the relative URL to be converted.
     * 参数 字符串 需要转化的相对URL
     *
     * @return string the absolute URL
     * 返回值 字符串 绝对URL
     *
     * @throws InvalidConfigException if [[baseUrl]] is not set
     * 当[[baseUrl]]没有设置的时候抛出不合法的配置异常。
     */
    public function getUrl($url)
    {
        if (($baseUrl = $this->getBaseUrl()) !== null) {
            return $baseUrl . '/' . ltrim($url, '/');
        } else {
            throw new InvalidConfigException('The "baseUrl" property must be set.');
        }
    }

    /**
     * Converts a relative file path into an absolute one using [[basePath]].
     * 使用[[basePath]]方法把一个相对路径转化成绝对路径。
     *
     * @param string $path the relative file path to be converted.
     * 参数 字符串 被转化的相对路径。
     *
     * @return string the absolute file path
     * 返回值 字符串 文件的绝对路径
     *
     * @throws InvalidConfigException if [[baseUrl]] is not set
     * 当[[baseUrl]]没有设置的时候抛出不合法的配置异常。
     */
    public function getPath($path)
    {
        if (($basePath = $this->getBasePath()) !== null) {
            return $basePath . DIRECTORY_SEPARATOR . ltrim($path, '/\\');
        } else {
            throw new InvalidConfigException('The "basePath" property must be set.');
        }
    }
}
