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
use think\Session;
use think\Db;


class ClientWallet extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'datetime';

    //注册时，初始化钱包
    public function iniWalletbyReg($data)
	{
		
		return(parent::saveAll($data));
	}

   /**
		收入
     
	 */
    public function updateWallet_deposit($data)
    {			 $tbl_pre="tp_";
		// 启动事务
			
			$data=['amount'=>$data['amount'],'money_tp'=>$data['money_tp'],'uid'=>$data['uid'],'fid'=>$data['fid'],'status'=>'0','totalamount'=>'0'];
			
			
	
	}
   /**
		支出
     */
    public function updateWallet_pay($uid, $fid)
    {
		
        return $this->where("id", $uid)->update(['fid' =>$fid]);
    }

	 public function iniWallet($data)
    {/*初始化钱包*/

		parent::isUpdate(false);
		parent::data($data);
		
		return(parent::save());

	}

/*
自定义自己增加新增

**/
	 public function save($data = [], $where = [], $sequence = null)
    {
		
		return(parent::save($data));
	}

}