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

class GroupinfoValidate extends Validate
{
    protected $rule = [
        'title'      => 'require|max:25',
		'code'      => 'require|max:25',
  
        
    ];
	protected $message  =   [
        'title.require' => '名称必须',
        'title.max'     => '名称最多不能超过25个字符',
        'code.require' => '邀请码必须',
        'code.max'     => '邀请码最多不能超过25个字符',
       
    ];


    protected $scene = [
        'add' => ['title','code'],
       
    ];
}