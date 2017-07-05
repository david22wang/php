<?php
/**
 * tpAdmin [a web admin based ThinkPHP5]
 *
 * @author yuan1994 <tianpian0805@gmail.com>
 * @link http://tpadmin.yuan1994.com/
 * @copyright 2016 yuan1994 all rights reserved.
 * @license http://www.apache.org/licenses/LICENSE-2.0
 */

//------------------------
// 用户模型
//-------------------------

namespace app\common\model;

use think\Model;

class ClientPassport extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    //自动完成
    protected $auto = ['password'];

    protected function setPasswordAttr($value)
    {
        return password_hash_tp($value);
    }

    /**
     * 修改密码
     */
    public function updatePassword($uid, $password)
    {
		
        return $this->where("id", $uid)->update(['password' => password_hash_tp($password)]);
    }
/*
自定义自己增加新增
会员建
**/
	 public function save($data = [], $where = [], $sequence = null)
    {
		//$data=array_merge($data,array("operid"=>OPERID,"regsource"=>"1","parentid"=>"0","reglevel"=>"0000"));//是注册来源
		return(parent::save($data));
	}
/*
自定义自己增加新增
自己注册
**/
	 public function savereg($data = [], $where = [], $sequence = null)
    {
		$data=array_merge($data,array("operid"=>0));//regsource是注册来源
		$flag=parent::save($data);//上面是
		
		

		return($flag);
	}
	
/*
自定义自己增加新增
自己注册
**/
	 public function savebatch($data = [], $where = [], $sequence = null)
    {
		parent::isUpdate(false);
		parent::data($data);
		return(parent::save());
	}

}