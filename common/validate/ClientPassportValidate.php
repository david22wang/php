<?php
/**
 * tpAdmin [a web admin based ThinkPHP5]
 *
 * @author yuan1994 <tianpian0805@gmail.com>
 * @link http://tpadmin.yuan1994.com/
 * @copyright 2016 yuan1994 all rights reserved.
 * @license http://www.apache.org/licenses/LICENSE-2.0
 */

namespace app\common\validate;

use think\Validate;

class ClientPassportValidate extends Validate
{
    protected $rule = [
        'account|帐号'      => 'require',
        'password|密码'     => 'require',
        'captcha|验证码'     => 'require|captcha',
        'oldpassword|旧密码' => 'require',
        'repassword|重复密码' => 'require',
		'account1|帐号'      => 'require|unique:client_passport,account',
		'account2|帐号'      => 'require|unique:client_passport,account',
    ];

    protected $scene = [
        'password' => ['password', 'oldpassword', 'repassword'],
        'login'    => ['account', 'password', 'captcha'],
		'reg'=> ['account1', 'password', 'captcha'],
		'opercreate'=>['account2', 'password'],
    ];
}