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
use think\Db;
class ClientwalletDetail extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'datetime';

    //不同帐户之间的转变
 public function updateWallet_trans($data,$walletid)
{
	

}

   /**
		收入
     */
    public function updateWallet_deposit($data,$walletid)
    {
		
      
		parent::save($data);
		$pk=$this->id;
		
		$amount=(float)$data['amount'];//价格
		$money_tp=$data['money_tp'];//货币种类
		$clientid=$data['clientid'];//客户号

			Db::startTrans();
			try{
				

				$wallinfo=Db::table('tp_client_wallet')
				->field(['totalamount'])
				->where(['clientid'=>$clientid,'moneytp'=>$money_tp])
				->find();
				 $taotalamount=(float)$wallinfo['totalamount']+(float)$amount;
				 //更新明细
				 $sql="update tp_clientwallet_detail set  status='1' ,totalamount= " .$taotalamount." where id=".$pk;
				 Db::execute($sql);
				 //更新主钱包
				 $sql="update tp_client_wallet set  status='1' ,totalamount= " .$taotalamount." where  moneytp=".$money_tp." and clientid=".$clientid;;
				 Db::execute($sql);
				
				// 提交事务
				Db::commit();    
			} catch (\Exception $e) {
				// 回滚事务
				Db::rollback();
				return(-1);
			}
			return(1);

    }
   /**
		支出
     */
    public function updateWallet_pay($data,$walletid)
    {
		
		
		$amount=(float)$data['amount'];//价格
		if($amount<0)
		{//支付必须是大于
			return(-2);
		}
		$money_tp=$data['money_tp'];//货币种类
		$clientid=$data['clientid'];//客户号
		

		  $wallinfo=Db::table('tp_client_wallet')
				->field(['totalamount'])
				->where(['clientid'=>$clientid,'moneytp'=>$money_tp])
				->find();

			$bankamount=(float)$wallinfo['totalamount'];//银行帐号

			if($bankamount<$amount)
			{//帐号钱不多
				return(-1);
			
			}

			/**/
			parent::save($data);
			$pk=$this->id;

			Db::startTrans();
			try{
				
				 $taotalamount=(float)$wallinfo['totalamount']-(float)$amount;//
				 //更新明细
				 $sql="update tp_clientwallet_detail set  status='1' ,totalamount= " .$taotalamount." where id=".$pk;
				 Db::execute($sql);
				 //更新主钱包
				 $sql="update tp_client_wallet set  status='1' ,totalamount= " .$taotalamount." where  moneytp=".$money_tp." and clientid=".$clientid;;
				 Db::execute($sql);
				
				// 提交事务
				Db::commit();    
			} catch (\Exception $e) {
				// 回滚事务
				return(-1);
				Db::rollback();
			}
			return(1);
       
    }

	  public function updateWallet_freeze($data,$walletid)
    {
		
		
		$amount=(float)$data['amount'];//价格
		if($amount<0)
		{//支付必须是大于
			return(-2);
		}
		$money_tp=$data['money_tp'];//货币种类
		$clientid=$data['clientid'];//客户号
		

		  $wallinfo=Db::table('tp_client_wallet')
				->field(['totalamount'])
				->where(['clientid'=>$clientid,'moneytp'=>$money_tp])
				->find();

			$bankamount=(float)$wallinfo['totalamount'];//银行帐号

			if($bankamount<$amount)
			{//帐号钱不多
				return(-1);
			
			}

			/**/
			parent::save($data);
			$pk=$this->id;

			Db::startTrans();
			try{
				
				 $taotalamount=(float)$wallinfo['totalamount']-(float)$amount;//
				 //更新明细
				 $sql="update tp_clientwallet_detail set  status='1' ,totalamount= " .$taotalamount." where id=".$pk;
				 Db::execute($sql);
				 //更新主钱包
				 $sql="update tp_client_wallet set  status='1' ,totalamount= " .$taotalamount." where  moneytp=".$money_tp." and clientid=".$clientid;;
				 Db::execute($sql);
				
				// 提交事务
				Db::commit();    
			} catch (\Exception $e) {
				// 回滚事务
				return(-1);
				Db::rollback();
			}
			return(1);
       
    }

	 public function updateWallet_unfreeze($data,$walletid)
    {
		
      
		parent::save($data);
		$pk=$this->id;
		
		$amount=(float)$data['amount'];//价格
		$money_tp=$data['money_tp'];//货币种类
		$clientid=$data['clientid'];//客户号

			Db::startTrans();
			try{
				

				$wallinfo=Db::table('tp_client_wallet')
				->field(['totalamount'])
				->where(['clientid'=>$clientid,'moneytp'=>$money_tp])
				->find();
				 $taotalamount=(float)$wallinfo['totalamount']+(float)$amount;
				 //更新明细
				 $sql="update tp_clientwallet_detail set  status='1' ,totalamount= " .$taotalamount." where id=".$pk;
				 Db::execute($sql);
				 //更新主钱包
				 $sql="update tp_client_wallet set  status='1' ,totalamount= " .$taotalamount." where  moneytp=".$money_tp." and clientid=".$clientid;;
				 Db::execute($sql);
				
				// 提交事务
				Db::commit();    
			} catch (\Exception $e) {
				// 回滚事务
				Db::rollback();
				return(-1);
			}
			return(1);

    }

/**
		支出
     */
    public function updateWallet_paybyoper($data,$walletid)
    {//这系统 管理员来调帐使用
		
		
		$amount=(float)$data['amount'];//价格
		if($amount<0)
		{//支付必须是大于
			return(-2);
		}
		$money_tp=$data['money_tp'];//货币种类
		$clientid=$data['clientid'];//客户号
		

		  $wallinfo=Db::table('tp_client_wallet')
				->field(['totalamount'])
				->where(['clientid'=>$clientid,'moneytp'=>$money_tp])
				->find();

			$bankamount=(float)$wallinfo['totalamount'];//银行帐号

			if($bankamount<$amount)
			{//帐号钱不多，这个是系统管理员，使用
				
			
			}

			/**/
			parent::save($data);
			$pk=$this->id;

			Db::startTrans();
			try{
				

				
				

				 $taotalamount=(float)$wallinfo['totalamount']-(float)$amount;//
				 //更新明细
				 $sql="update tp_clientwallet_detail set  status='1' ,totalamount= " .$taotalamount." where id=".$pk;
				 Db::execute($sql);
				 //更新主钱包
				 $sql="update tp_client_wallet set  status='1' ,totalamount= " .$taotalamount." where  moneytp=".$money_tp." and clientid=".$clientid;;
				 Db::execute($sql);
				
				// 提交事务
				Db::commit();    
			} catch (\Exception $e) {
				// 回滚事务
				return(-1);
				Db::rollback();
			}
			return(1);
       
    }

/*
自定义自己增加新增

**/
	 public function save($data = [], $where = [], $sequence = null)
    {
		
		return(parent::save($data));
	}

}