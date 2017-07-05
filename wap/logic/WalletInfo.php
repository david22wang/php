<?php
/**
 * tpAdmin [a web admin based ThinkPHP5]
 *
 * @author    yuan1994 <tianpian0805@gmail.com>
 * @link      http://tpadmin.yuan1994.com/
 * @copyright 2016 yuan1994 all rights reserved.
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

namespace app\wap\logic;

use think\Db;
use think\Config;
use think\Loader;

class WalletInfo
{
    	public function pay($client_info_id,$amount,$csum,$moneytp,$fid,$walletid=3,$remark='前台')
	{//支出最后一个参数walletid 没有使用用
		//$client_info_id=Session::get('client_info_id');
	
		//$amount = $this->request->param('amount');
		//$csum = $this->request->param('csum');
		//$moneytp = $this->request->param('moneytp');
		//$fid=0;
		$data=['remark'=>$remark,'amount'=>$amount,'money_tp'=>$moneytp,'clientid'=>$client_info_id,'fid'=>$fid,'status'=>'0','totalamount'=>'0','trade_type'=>'-1','walletid'=>$walletid];
		//$walletid=3;
		$flag=Loader::model('ClientwalletDetail')->updateWallet_pay($data,$walletid);//支出
		
		if($flag==1)
		{
			return(1);
		}
		else
		{
			return(0);
		}
	
	}
	public function deposit($client_info_id,$amount,$csum,$moneytp,$fid,$walletid=3,$remark='前台')
	{//收入最后一个参数walletid 没有使用用
		//$client_info_id=Session::get('client_info_id');
	
		//$amount = $this->request->param('amount');
		//$csum = $this->request->param('csum');
		//$moneytp = $this->request->param('moneytp');
		//$fid=0;
		$data=['remark'=>$remark,'amount'=>$amount,'money_tp'=>$moneytp,'clientid'=>$client_info_id,'fid'=>$fid,'status'=>'0','totalamount'=>'0','trade_type'=>'1','walletid'=>$walletid];
		//$walletid=3;
		$flag=Loader::model('ClientwalletDetail')->updateWallet_deposit($data,$walletid);//支出
		
		if($flag==1)
		{
			return(1);
		}
		else
		{
			return(0);
		}
	
	}
	
    
}