<?php
/**
 * Created by PhpStorm.
 * User: chenxiaogang
 * Date: 2018/1/22
 * Time: 下午4:36
 */

namespace backend\modules\rbac\forms;

use backend\common\helper\key\RedisKey;
use backend\common\helper\key\SessionKey;
use yii;
use backend\modules\rbac\models\Admin;
use yii\base\Model;

class LoginForm extends Model
{
    /**
     * @var [账号]
     */
    public $login_name;

    /**
     * @var [密码]
     */
    public $login_pass;

    /**
     * @var [验证码]
     */
    public $verify_code;

    /**
     * @var [管理员实例]
     */
    private $_admin;

    public function rules()
    {
        $rules = [
            [ [ 'login_name', 'login_pass' ], 'required' ],
            [ 'login_pass', 'validatePassword' ],//密码校验
        ];
        //如果启用验证码，才需要验证验证码
        if(Yii::$app->params['LOGIN_VERIFY_CODE_ENABLED']) {
            $rules = array_merge( $rules, [
                [ 'verify_code', 'required' ],
                [ 'verify_code', 'captcha', 'captchaAction' => '/rbac/site/captcha' ],
            ] );
        }
        return $rules;
    }

    public function validatePassword($attribute, $params)
    {
        if(!$this->hasErrors()) {
            $user = $this->getAdmin();
            //如果启用验证码，才需要验证密码
            if(!$user || !$user->validateLoginPass( $this->login_pass )) {
                $this->addError( $attribute, '用户名或密码错误' );
            }
        }
    }

    public function attributeLabels()
    {
        return [
            'login_name' => '账户',
            'login_pass' => '密码',
            'verify_code' => '验证码',
        ];

    }


    /**
     * @use          [登录]
     * @author       chenxiaogang
     */
    public function login()
    {
        if(!$this->validate()) {
            return false;
        }
        $admin = $this->getAdmin();
        //判断该用户是否存在
        //1、登录成功时，要记录最后登录时间、IP和将登录信息存入
        $admin->setScenario( $admin::SCENARIO_LOGIN );
        if(!$admin->save()) {
            $this->addErrors( $admin->getErrors() );
            return false;
        }
        //2、登录时，将之前的权限信息清空
        Yii::$app->redis->del(
            RedisKey::BUTTON_AUTHS . Yii::$app->user->id,
            RedisKey::MENU_AUTHS . Yii::$app->user->id,
            RedisKey::ADMIN_ROLES_IDS . Yii::$app->user->id
        );
        //2、将身份信息存入用户组件
        return Yii::$app->user->login( $admin, SessionKey::LOGIN_DURATION );//设置过期时间
    }

    /**
     * @use     [获取管理员模型]
     * @return mixed
     */
    public function getAdmin()
    {
        if($this->_admin === null) {
            $this->_admin = Admin::findByLoginName( $this->login_name );
        }
        return $this->_admin;
    }

}