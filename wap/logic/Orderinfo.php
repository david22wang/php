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

class Orderinfo
{
	private function getOrderProductQuan($proid)
	{
		$clientid=CLIENTID;
		 $tbl_pre ="tp_";
		$sql1="SELECT orderid FROM ".$tbl_pre."order_info WHERE clientid=$clientid and (status=1)";//查询自己的订单号
		$sql="SELECT sum(quan) as totalquan FROM ".$tbl_pre."order_info_detail WHERE proid=$proid and orderid IN (".$sql1.")";//查询产品明细
		$tbl_quan=DB::query($sql);
		$totalquan=0;
		foreach($tbl_quan as $rowquan)
		{
			$totalquan=$rowquan['totalquan'];//采购数量
		}
		return($totalquan);//返回总数
		

		
	}
    /**
     * 查询订单中产品的购买限制 ，和优惠
     */
    public function OrderProduct($proid,$buyquan,$customlevel=0,$arr_pro)
    {
        
             $tbl_pre ="tp_";
            $proinfo=db::table($tbl_pre."product_info")->field("*")->where(["id"=>$proid,"status"=>"1","isdelete"=>"0","upload_time"=>["ELT","now()"] ])->find();

			$price=$proinfo["price"];//产品价格
			$quan=(float)$proinfo["quan"];//库存产品数量
			$title=$proinfo["title"];//产品标题
			$pro_buy_flag=true;//产品购买
			$status=1;//产品购买状态
			$errorno=0;
			$otherproid=0;//如果大于0，表示此产品不能与另一个产品不能其它产品
		   if($buyquan>$quan)
			{//采购小于库存
					 $pro_buy_flag=false;
					 $errorno=1;
					 $status=0;
			}
			//上面是查询库存

			if($pro_buy_flag)
			{//会员限制
			 	$totalquanbypro=$this->getOrderProductQuan($proid);//得到该产品购买数量
				 
     			$sql="select * from ".$tbl_pre."productinfo_rule where proid=$proid and status=1 and (customtp=0 or customtp=$customlevel) order by customtp desc limit 0,1 ";
				$list=DB::query($sql);

				

				foreach($list as $row)
				{
					$minquan=(float)$row['minquan'];//最小数量
					$maxquan=(float)$row['maxquan'];//最大数量
					$otherproid=(int)$row['otherproid'];//和其它产品，产品组中，只能买一个
					if($buyquan<$minquan)
					{//采购数量大于最小数量
						 $pro_buy_flag=false;
						  $status=0;
						  $errorno=2;
						 break;
								
					}
					if($buyquan>$maxquan)
					{//采购数量 小于最大数量
							
						 $pro_buy_flag=false;
						 $status=0;
						 $errorno=3;
						 break;
					}
					//下面开始计算订单中数量
					if($totalquanbypro+$buyquan>$maxquan)
					{//表示已经下过订单，
						$pro_buy_flag=false;
						 $status=0;
						 $errorno=5;
						 break;				

					}

					foreach($arr_pro as $proitem)
					{
						$otherproids=$proitem['otherproid'];//查询是不是有组合
						if($otherproids>0)
						{//大于0，表示 有产品组合
							if($otherproids==$proid)
							{//如果出现组合，同时出现，就出错
								$pro_buy_flag=false;
								$errorno=4;
								 $status=0;
								 break;
							}
						}
				
					}//查询产品组合，异或
					if($pro_buy_flag)
					{//如果出现，就退出
						break;
					}

				}
			
			}//会员 限制

		
			
		$remark="";
        $nodes=["errorno"=>$errorno,"proid"=>$proid,"title"=>$title,"oldprice"=>$price,"newprice"=>$price,"status"=>$status,"quan"=>$buyquan,"remark"=>$remark,'otherproid'=>$otherproid];

        return $nodes;
    }
	 /**
     * 查询订单中，$addr是邮费，订单其它费用等其它优惠
	 这是下面计算总费用
	 $totalamount=$totalamount+$shipprice+$orderprice+$tax;
     */
    public function OrderInfo($addr,$ordertp,$totalm,$customlevle=0)
    {		$tbl_pre ="tp_";
			$ship=0;//快递费
			if($ordertp==1)
			{//订单类型 1 表示实物订单
			
			
					//快递
				$sql="select price from ".$tbl_pre."order_shipfee  where (province='全国' or province like '%$addr%') and  (custom_tp=0 or custom_tp=$customlevle) and  status=1 and isdelete=0 ";//订单总钱
				$arr_order_ship=db::query($sql);
				if( empty($arr_order_ship))
				{
					$ship=0;//如果是没有记录，没有相关快递 费
				}
				else
				{
					$ship=(float)$arr_order_ship[0]['tax'];//税收比
					

				}
				//==================================
			}
			if($ordertp==0)
			{//订单类型 0 表示虚拟订单
							
				$nodes=["giftid"=>0,"orderdis"=>0,"tax"=>0,"ship"=>0,"order"=>0,"remark"=>'电子订单'];
				return $nodes;
			
			}
			$order=0;
			$remark="";
			$tax=0;
			
			//开始计算税费
			
           
			$sql="select tax from ".$tbl_pre."order_tax  where $totalm>quan and status=1 and isdelete=0 ";//订单总钱
			$arr_order_tax=db::query($sql);
			if( empty($arr_order_tax))
			{
				$tax=0;//如果是没有记录，税费为0
			}
			else
			{
				$taxratio=(float)$arr_order_tax[0]['tax'];//税收比
				$tax=$totalm*$taxratio;

			}
			
			//开始计算
		
           
			$sql="select * from ".$tbl_pre."order_info_promotion"." where precondition<$totalm and (tp=0 or tp=$customlevle) and status=1 and delflag=0 order by precondition desc";

			 $order_info_promotion=db::query($sql);
			
			$orderdis=0;//订单没有折扣
			$giftid=0;
			$nodes=[];
			if( empty($order_info_promotion))
			{//如果没有优惠数据的话，直接引用原来的价
				$nodes=["giftid"=>0,"orderdis"=>0,"tax"=>$tax,"ship"=>$ship,"order"=>$order,"remark"=>$remark];
			}
			else{//#11

					$order_info_promotion=$order_info_promotion[0];
					
					if(count($order_info_promotion)>0)
					{
						$preconditiontag=$order_info_promotion['preconditiontag'];//前提条件<option value="1">大于	<option value="2">小于	<option value="3">大于等于<option value="4">小于等于
						$dis_resulttag=$order_info_promotion['dis_resulttag'];//<option value="1">比率<option value="2">直减<option value="3">礼品
						$giftid=$order_info_promotion['giftid'];
						$precondition=$order_info_promotion['precondition'];//前提条件数据
						$dis_result=(float)$order_info_promotion['dis_result'];
						if($dis_resulttag==1)
						{//百分比
							$orderdis=$totalm*$dis_result;
						
						}
						if($dis_resulttag==2)
						{//百分比
							$orderdis=$dis_result;
						
						}
						
					}
					  $nodes=["giftid"=>$giftid,"orderdis"=>$orderdis,"tax"=>$tax,"ship"=>$ship,"order"=>$order,"remark"=>$remark];

			}//#11
            
		
      
        return $nodes;
    }
	//这个是，检查订单，正常返回金额，用来支付

	 public function CheckOrderInfoforpay($orderid,$clientid=0)
    {	
		$tbl_pre ="tp_";
		//30分钟可以支付
		$sql="select * from ".$tbl_pre."order_info  where id=$orderid and clientid=$clientid and substatus=0 and DATE_ADD(create_time, INTERVAL 3000 MINUTE)>now()";//订单总钱
		$arr_order=db::query($sql);	
		$node=[];
		if(!empty($arr_order))
		{
			$node["status"]=1;
			$node["orderid"]=$orderid;	
			$node["amount"]=(float)$arr_order[0]["totalamount"];
		}
		else
		{
			$node["status"]=0;
			$node["orderid"]=0;
			$node["amount"]=0;
		}
		return $node;


	}
    
}