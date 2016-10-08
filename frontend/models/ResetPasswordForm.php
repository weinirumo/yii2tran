<?php
namespace frontend\models;

use yii\base\Model;
use yii\base\InvalidParamException;
use common\models\User;

/**
 * Password reset form
 * 密码重置表单
 */
class ResetPasswordForm extends Model
{
    public $password;

    /**
     * @var \common\models\User
     */
    private $_user;


    /**
     * Creates a form model given a token.
     * 创建一个表单，带上一个token
     *
     * @param string $token
     * @param array $config name-value pairs that will be used to initialize the object properties
     * 参数 ， 数组 ，在初始化对象属性的时候使用的键值对
     * @throws \yii\base\InvalidParamException if token is empty or not valid
     * 抛出异常，当token为空或者无效时
     */
    public function __construct($token, $config = [])
    {
        if (empty($token) || !is_string($token)) {
            throw new InvalidParamException('Password reset token cannot be blank.');
        }
        $this->_user = User::findByPasswordResetToken($token);
        if (!$this->_user) {
            throw new InvalidParamException('Wrong password reset token.');
        }
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['password', 'required'],
            ['password', 'string', 'min' => 6],
        ];
    }

    /**
     * Resets password.
     * 执行重置密码操作
     *
     * @return boolean if password was reset.
     * 返回值 boolean 密码重置的结果
     */
    public function resetPassword()
    {
        $user = $this->_user;
        $user->setPassword($this->password);
        $user->removePasswordResetToken();

        return $user->save(false);
    }
}
