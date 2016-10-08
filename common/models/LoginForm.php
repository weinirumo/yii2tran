<?php
namespace common\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 * 登陆表单
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * 验证密码
     * This method serves as the inline validation for password.
     * 此方法用以密码的内联验证
     *
     * @param string $attribute the attribute currently being validated
     * 参数 字符串 将要被验证的属性
     * @param array $params the additional name-value pairs given in the rule
     * 参数 数组 rule额外提供的用于验证的键值对
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * 根据用户提供的用户名和密码进行登陆
     *
     * @return boolean whether the user is logged in successfully
     * 返回值  boolean 登陆是否成功
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        } else {
            return false;
        }
    }

    /**
     * Finds user by [[username]]
     * 根据username在数据库查找用户名
     *
     * @return User|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }
}
