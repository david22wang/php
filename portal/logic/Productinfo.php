<?php
/**
 * tpAdmin [a web admin based ThinkPHP5]
 *
 * @author    yuan1994 <tianpian0805@gmail.com>
 * @link      http://tpadmin.yuan1994.com/
 * @copyright 2016 yuan1994 all rights reserved.
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

namespace app\portal\logic;

use think\Db;
use think\Config;
use think\Loader;

class Productinfo
{


	 /**
     * 计算订单中产品的购买优惠$proid产品号,$buyquan购买数量 ,$customlevel 会员等级 $proprice 产品价格
	 这个函数将来要增加风控线
	 多个优惠以最大优惠计算
     */
    public function ProductPromotionForCreate($proid,$buyquan,$customlevel=1,$proprice)
    {
        
             $tbl_pre ="tp_";/*tp=0表示全部*/
         	if($customlevel==null){$customlevel=0;}
		
			
			
			$sql="select * from ".$tbl_pre."product_price_table where (custom_tp=0 or custom_tp=$customlevel) and status=1 and isdelete=0 and proid=$proid and quan<=$buyquan and bgtime<=now() and  endtime>now() order by custom_tp desc limit 0,1";
			
			$nodes=db::query($sql);
			
			$pricenode=[];
			if( empty($nodes))
			{//如果没有优惠数据的话，直接引用原来的价
				$pricenode[]=$proprice;//
			}
			else
			{
				foreach($nodes as $node)
				{//开始查询价格表
					
					$pricenode[]=$node['price'];//价格

				}
			}
			//=============================================================
			//
				

			
			
			
		  return $pricenode;

    }


    /**
     * 查询订单中产品的价格表,参数：产品号，会员等级
     */
    public function ProductPriceTable($proid,$client_level_id)
    {
        
             $tbl_pre ="tp_";

           
			$sql="select * from ".$tbl_pre."product_price_table"." where  (custom_tp=0 or custom_tp=$client_level_id) and status=1 and isdelete=0 and  proid=$proid order by price asc";

			 $nodes=db::query($sql);

		  return $nodes;
    }
	 /**
     * 计算订单中产品的购买优惠$proid产品号,$buyquan购买数量 ,$customlevel 会员等级 $proprice 产品价格
	 这个函数将来要增加风控线
	 多个优惠以最大优惠计算
     */
    public function ProductPromotionCal($proid,$buyquan,$customlevel=1,$proprice)
    {
        




             $tbl_pre ="tp_";/*tp=0表示全部*/
         	if($customlevel==null){$customlevel=0;}
		
			
			
			$sql="select * from ".$tbl_pre."product_price_table where (custom_tp=0 or custom_tp=$customlevel) and status=1 and isdelete=0 and proid=$proid and quan<=$buyquan and bgtime<=now() and  endtime>now()";
			
			$nodes=db::query($sql);
			
			$pricenode=[];
			if( empty($nodes))
			{//如果没有优惠数据的话，直接引用原来的价
				$pricenode[]=$proprice;//
			}
			else
			{
					
				foreach($nodes as $node)
				{//开始计算
					
					$pricenode[]=$node['price'];//价格
				//=============================================================
				//
					

				
				}
			}

		  return $pricenode;
    }
	 /**
     * 查询订单中，$addr是邮费，订单其它费用等其它优惠
	 这是下面计算总费用
	 $totalamount=$totalamount+$shipprice+$orderprice+$tax;
     */
    public function OrderInfo($addr,$ordertp,$totalm,$customlevle=0)
    {
			
		
        $nodes=["giftid"=>$giftid,"orderdis"=>$orderdis,"tax"=>$tax,"ship"=>$ship,"order"=>$order,"remark"=>$remark];

        return $nodes;
    }

    
}