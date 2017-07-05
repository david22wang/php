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
// 产品分类模型
//-------------------------

namespace app\common\model;

use think\Model;
use think\Db;
class OrderInfo extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'datetime';

    //自动完成
 

   /**
     更新订单状态
	 同时，要修改产品库存
     */
    public function updateOrderPayed( $fid)
    {	//第一步修改主订单状态
		$data=["substatus"=>1,"update_time"=>array('exp', 'NOW()')];
		
		//第二步修改库存
		$flag= Db::name("OrderInfo")->where("id", $fid)->update($data);
		$orderproarray=Db::name("OrderInfoDetail")->where("orderid",$fid)->field(["proid","quan"])->select();

		
		foreach($orderproarray as $orderpro)
		{//
			$proid=$orderpro['proid'];//产品号
			$quan=$orderpro['quan'];//数量
			$sql="update tp_product_info set quan=quan-$quan where id=$proid";
			
			Db::execute($sql);
		}
		
        return 1;
    }

 /**
     更新订单明细状态，提取收益
	
     */
    public function updateOrderExtracted($fid)
    {	//第一步修改主订单状态
		$data=["substatus"=>1,"update_time"=>array('exp', 'NOW()')];
		
		
		$flag= Db::name("OrderInfoDetail")->where("id", $fid)->update($data);
		
		
        return 1;
    }

/*
自定义自己增加新增

**/
	 public function save($data = [], $where = [], $sequence = null)
    {
		
		return(parent::save($data));
	}

}