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
// 订单
//-------------------------

namespace app\portal\controller;

\think\Loader::import('controller/Controller', \think\Config::get('traits_path') , EXT);

use app\portal\Controller;
use think\Exception;
use think\Loader;
use think\Db;
use think\Session;
class Orderinfo extends Controller
{
    use \app\portal\traits\controller\Controller;
	use \traits\controller\Jump;

  //protected static $blacklist = ['delete', 'clear', 'deleteforever', 'recyclebin', 'recycle'];
	//现在是新手bengin line
	//新手标，直接生成订单，直接支付
	public function forbid()
	{
		$orderid=$this->request->param('orderid');
		$client_info_id=CLIENTID;
		$sql ="update tp_order_info set status=0 where status=1 and id=$orderid and clientid=$client_info_id";
		$flag=DB::execute($sql);

		
	}
//新手处理开始============================================================
	public function newie()
	{//生成电子 订单
		//这个函数和下面的函数newiegroup()是一样的，出现问题要同步修改
			
		$tbl_pre ="tp_";
		$client_info_id=CLIENTID;
		$client_level_id=0;//新手订单，

		//return ajax_return_adv($this->request->Post("itemCount"), '');
		
		//下面开始
		//return ajax_return_adv('更新成功！', '');
		 //return ajax_return_adv_error("更新失败");

		
			if ($this->request->isAjax() && $this->request->isPost()) {//
			//处理保存


				$itemCount=(int)$this->request->Post("itemCount");
				$totalamount=0;
				if($itemCount==0)
				{
					 return ajax_return_adv_error("购物车是空的！");
				}

				$arr_pro=[];
				$createorderflag=1;//创建订单
				$totalamount=0;
				//==========================
				for($i=1;$i<=$itemCount;$i++)
		{//	
				$proid=$this->request->Post("item_id_".$i);
				// throw new \think\Exception($proid, 100006);
					
				$pro_quan=(float)$this->request->Post("item_quantity_".$i);//采购数量
				
				$nodes=Loader::model('Orderinfo', 'logic')->OrderProduct($proid,$pro_quan,$client_level_id,$arr_pro);//这是查询产品购买限制条件
				
				if($nodes["status"]==0)
				{//生成订单出错,出现订单限制条件，不能生成订单
					$createorderflag=false;
					if($nodes["errorno"]==4)
					{
						return ajax_return_adv_error("两种产品不能同时购买");
					}
				if($nodes["errorno"]==1)
					{
						return ajax_return_adv_error("库存不足");
					}
				if($nodes["errorno"]==2)
					{
						return ajax_return_adv_error("采购数量小于最少数量");
					}
					if($nodes["errorno"]==3)
					{
						return ajax_return_adv_error("采购数量大于最大数量");
					}
					if($nodes["errorno"]==5)
					{
						return ajax_return_adv_error("产品购买数量已经超出，订单已经存在产品了");
					}

					break;

				}
				else
				{
						$price=$nodes['newprice'];//产品价格
						$proPromotion = Loader::model('Productinfo', 'logic')->ProductPromotionForCreate($proid,$pro_quan,$client_level_id,$price);//这是查询订单的优惠
					
						
						if(count($proPromotion)>0)
						{
							$new_dis_price=$proPromotion[0];//新产品得到最少价格
						
						}
					
							$newprice=(float)$new_dis_price;//新价格

							$oldprice=$price;//老价格
				
							$totalamount=$totalamount+$newprice*$pro_quan;//产品价格
							$nodes['newprice']=$newprice;
							array_push($arr_pro,$nodes);//生成一个新的数组
			

				}
			
			
		}
			//===========================
		//生成订单
		if($createorderflag)
		{//提示订单生成
					$nodes = Loader::model('Orderinfo', 'logic')->OrderInfo('',0,$totalamount);//这是电子订单 查询相关优惠和费用
					$shipprice=$nodes['ship'];//邮递费用
					$orderprice=$nodes['order'];//订单优惠
					$tax=$nodes['tax'];//税
					$orderdis=$nodes['orderdis'];//订单折扣
					$giftid=$nodes['giftid'];//订单礼品号
					if($giftid>0)
					{//礼品号
						$str_giftremark='订单礼品号：'.strval($giftid);
					}
					else
					{
							$str_giftremark='';
					}

					$totalamount=$totalamount+$shipprice+$orderprice+$tax+$orderdis;
					
					$remark='电子订单邮费:'.strval($shipprice).'订单费用'.strval($orderprice).'税费：'.strval($tax).'订单折扣：'.strval($orderdis).' '.$str_giftremark;
					$address_title='电子订单';			
					$data=['clientid'=>$client_info_id,'title'=>'',"substatus"=>0,"ordertp"=>"3",'totalamount'=>$totalamount,'payid'=>0,'address'=>$address_title,'remark'=>$remark];	
				
					$flag=Loader::model('orderInfo')->save($data);
					$orderid=0;
					//下面开始增加产品明细=====================
					if($flag)
					{//得到订单号
						$list=[];
						$orderid=Loader::model('orderInfo')->id;//订单号
						foreach($arr_pro as $pro)
						{
							array_push($list,["orderid"=>$orderid,"proid"=>$pro["proid"],"newprice"=>$pro["newprice"],"price"=>$pro["oldprice"],"quan"=>$pro["quan"],"title"=>$pro["title"],"remark"=>$pro["remark"],'status'=>1,'substatus'=>0]);
						}
						$oldflag=Loader::model('OrderInfoDetail')->saveall($list);
						

					}
					//下面开始调用支付
					if($orderid>0)
				{//有订单号，才调用
					$payflag=$this->paynewie($orderid,$totalamount);
					if($payflag==1)
					{//支付成功，更新产品数量
						return ajax_return_adv('下单成功！', '');
					
					}
					else
					{//出错
						return ajax_return_adv_error("支付失败");
						
					}
				}


			}



			}
			else
		{
			return ajax_return_adv_error("数据来源出错");
			
			}
		

		
		
		

		
	}

public function newiegroup()
	{//生成电子 订单,这个函数和上面的函数newie()是一样的，出现问题要同步修改
			
		$tbl_pre ="tp_";
		$client_info_id=CLIENTID;
		$client_level_id=0;
		
if ($this->request->isAjax() && $this->request->isPost()) {//
			//处理保存



		//下面开始
		$itemCount=(int)$this->request->Post("itemCount");
		if($itemCount==0)
		{
					 return ajax_return_adv_error("购物车是空的！");
		}
		$totalamount=0;
		$arr_pro=[];
		$createorderflag=1;//创建订单
		$totalamount=0;

		for($i=1;$i<=$itemCount;$i++)
		{//
				$proid=$this->request->Post("item_id_".$i);
					
				$pro_quan=(float)$this->request->Post("item_quantity_".$i);//采购数量
				$nodes=Loader::model('Orderinfo', 'logic')->OrderProduct($proid,$pro_quan,$client_level_id,$arr_pro);//这是查询产品限制条件

				if($nodes["status"]==0)
				{//生成订单出错,出现订单限制条件，不能生成订单
					$createorderflag=false;
					if($nodes["errorno"]==4)
					{
						return ajax_return_adv_error("两种产品不能同时购买");
					}
				if($nodes["errorno"]==1)
					{
						return ajax_return_adv_error("库存不足");
					}
				if($nodes["errorno"]==2)
					{
						return ajax_return_adv_error("采购数量小于最少数量");
					}
					if($nodes["errorno"]==3)
					{
						return ajax_return_adv_error("采购数量大于最大数量");
					}
					if($nodes["errorno"]==5)
					{
						return ajax_return_adv_error("产品购买数量已经超出，订单已经存在产品了");
					}

					break;

				}
				else
				{
						$price=$nodes['newprice'];//产品价格
						$proPromotion = Loader::model('Productinfo', 'logic')->ProductPromotionForCreate($proid,$pro_quan,$client_level_id,$price);//这是查询订单的优惠
					
						
						if(count($proPromotion)>0)
						{
							$new_dis_price=$proPromotion[0];//新产品得到最少价格
						
						}
					
							$newprice=(float)$new_dis_price;//新价格

							$oldprice=$price;//老价格
				
							$totalamount=$totalamount+$newprice*$pro_quan;//产品价格
							$nodes['newprice']=$newprice;
							array_push($arr_pro,$nodes);//生成一个新的数组
			

				}
			
			
		}
		
		if($createorderflag)
		{//提示订单生成
					$nodes = Loader::model('Orderinfo', 'logic')->OrderInfo('',0,$totalamount);//这是电子订单 查询相关优惠和费用
					$shipprice=$nodes['ship'];//邮递费用
					$orderprice=$nodes['order'];//订单优惠
					$tax=$nodes['tax'];//税
					$orderdis=$nodes['orderdis'];//订单折扣
					$giftid=$nodes['giftid'];//订单礼品号
					if($giftid>0)
					{//礼品号
						$str_giftremark='订单礼品号：'.strval($giftid);
					}
					else
					{
							$str_giftremark='';
					}

					$totalamount=$totalamount+$shipprice+$orderprice+$tax+$orderdis;
					
					$remark='电子订单邮费:'.strval($shipprice).'订单费用'.strval($orderprice).'税费：'.strval($tax).'订单折扣：'.strval($orderdis).' '.$str_giftremark;
					$address_title='电子订单';			
					$data=['clientid'=>$client_info_id,'title'=>'',"substatus"=>0,"ordertp"=>"4",'totalamount'=>$totalamount,'payid'=>0,'address'=>$address_title,'remark'=>$remark];	
				
					$flag=Loader::model('orderInfo')->save($data);
					$orderid=0;
					//下面开始增加产品明细=====================
					if($flag)
					{//得到订单号
						$list=[];
						$orderid=Loader::model('orderInfo')->id;//订单号
						foreach($arr_pro as $pro)
						{
							array_push($list,["orderid"=>$orderid,"proid"=>$pro["proid"],"newprice"=>$pro["newprice"],"price"=>$pro["oldprice"],"quan"=>$pro["quan"],"title"=>$pro["title"],"remark"=>$pro["remark"],'status'=>1,'substatus'=>0]);
						}
						$oldflag=Loader::model('OrderInfoDetail')->saveall($list);
						

					}
					//下面开始调用支付
					if($orderid>0)
				{//有订单号，才调用
					$payflag=$this->paynewie($orderid,$totalamount);
					
					if($payflag==1)
					{//支付成功，更新产品数量
						return ajax_return_adv('下单成功！', '');
					
					}
					else
					{//出错
						return ajax_return_adv_error("支付失败");
						
					}
				}


		}

	}
	}




	private function paynewie($orderid,$amount)
	{//支付新手电子 
		
					$client_info_id=CLIENTID;
					$moneytp=10;//固化
					$csum=0;
					$flag = Loader::model('WalletInfo', 'logic')->pay($client_info_id,$amount,$csum,$moneytp,$orderid,3,'新手产品支付');//这是订单支付，最后是3实物
					if($flag==1)
					{//支付成功更新订单
							
							$flag=Loader::model('OrderInfo')->updateOrderPayed($orderid);//更新支付成功，更新产品数量
								
							
					}
					else
					{
						//这个要更新订单的订单的下架数量
						return -1;
					
					}

		return 1;
	}

	public function index_newie()
	{	//新手订单列表	
		$client_info_id=CLIENTID;
		 $tbl_pre ="tp_";
					
			$listRows = $this->request->param('numPerPage') ?: 10;
     		$search=['main.isdelete'  =>0,'main.clientid'=>$client_info_id,'main.ordertp'=>3 ] ;
				//条件查询
	
			
				$list=db::table($tbl_pre."order_info")
				->alias(['main'=>$tbl_pre."order_info"])
				->field("main.*")	
				->where($search)
				->order('create_time desc')
				//->fetchSql(true)
				->paginate($listRows, false, ['query' => $this->request->get()]);
				// 把分页数据赋值给模板变量list
				
				$page = $list->render();
				// 模板变量赋值
			

 			   $this->view->assign('list', $list);
                $this->view->assign("count", $list->total());
                $this->view->assign("page", $list->render());
                $this->view->assign('numPerPage', $list->listRows());

		 return $this->view->fetch();
	}
	public function index_newiebypro()
	{	//新手订单产品列表，可以直接提取	
		$client_info_id=CLIENTID;
		 $tbl_pre ="tp_";
					
			$listRows = $this->request->param('numPerPage') ?: 10;
     		$search=['main.isdelete'  =>0,'main.clientid'=>$client_info_id,'main.ordertp'=>3,'main.substatus'=>1,'main.status'=>1 ] ;
				//条件查询,只列出支付成功的项目
	
			
				$list=db::table($tbl_pre."order_info_detail")
				->alias(['main'=>$tbl_pre."order_info","orderdetail"=>$tbl_pre."order_info_detail","proinfo"=>$tbl_pre."product_info"])
				->join($tbl_pre."order_info","main.id = orderdetail.orderid","INNER")
				->join($tbl_pre."product_info","proinfo.id = orderdetail.proid","LEFT")
				->field("main.*,".$tbl_pre."order_info_detail.title as protitle,".$tbl_pre."order_info_detail.substatus as depositstatus,".$tbl_pre."product_info.id as  proid,".$tbl_pre."product_info.substatus as  winstatus")	
				->where($search)
				->order($tbl_pre."order_info.create_time desc")
				
				->paginate($listRows, false, ['query' => $this->request->get()]);
				// 把分页数据赋值给模板变量list
				
				$page = $list->render();
				// 模板变量赋值
				
 			   $this->view->assign('list', $list);
                $this->view->assign("count", $list->total());
                $this->view->assign("page", $list->render());
                $this->view->assign('numPerPage', $list->listRows());

		 return $this->view->fetch();
	}

	public function detailnewie()
	{//这个订单是明细
		$tbl_pre ="tp_";
		$client_info_id=CLIENTID;
		
		
			$orderid = $this->request->param('orderid');

			//查询收货地址：
			$sql="SELECT * FROM ".$tbl_pre."order_info WHERE ordertp=3 and id=$orderid and isdelete=0 and clientid =$client_info_id";
			
			$orderinfo=db::query($sql);
			
			
			$this->view->assign('orderinfo', $orderinfo[0]);	//查询订单
			
			
			$sql="select ".$tbl_pre."order_info_detail.*,".$tbl_pre."product_info.substatus as resultstatus from ".$tbl_pre."order_info_detail inner join ".$tbl_pre."product_info on ".$tbl_pre."product_info.id=".$tbl_pre."order_info_detail.proid where orderid=$orderid";
			$orderdetailinfo=db::query($sql);
			
			
			$this->view->assign('orderdetailinfo', $orderdetailinfo);	//查询订单产品明细




			 return $this->view->fetch();	
		

		

	}
	public function extractionnewie()
	{//提取收益
		$tbl_pre ="tp_";
		$client_info_id=CLIENTID;
		$orderid = $this->request->param('orderid');
	
		$sql_order="SELECT id FROM ".$tbl_pre."order_info WHERE ordertp=3 and id=$orderid and isdelete=0 and clientid =$client_info_id";//只能提取自己的订单的收益
			
			
		$sql="select ".$tbl_pre."order_info_detail.* from ".$tbl_pre."order_info_detail inner join ".$tbl_pre."product_info on ".$tbl_pre."product_info.id=".$tbl_pre."order_info_detail.proid where ".$tbl_pre."product_info.substatus=1 and ".$tbl_pre."order_info_detail.substatus=0 and ".$tbl_pre."order_info_detail.orderid in(".$sql_order.")";//product_info.substatus=1 表示 产品已赢 
		//order_info_detail.substatus=0 表示 没有提取收益   
		$orderdetailinfo=db::query($sql);
		//dump($orderdetailinfo);
		foreach($orderdetailinfo as $row)
		{//查询已经赢的产品
			$price=(float)$row['price'];
			$quan=(float)$row['quan'];
			$amount=$price*$quan;//收益

			$detailid=$row['id'];//产品明细
			$moneytp=10;
			$csum=0;
			$remark='订单号'.strval($orderid).'产品号'.strval($detailid);
			
			$flag=Loader::model('OrderInfo')->updateOrderExtracted($detailid);//更新订单明细状态
			if($flag==1)
			{//更新订单明细 后，才开始增加收入
					$flag = Loader::model('WalletInfo', 'logic')->deposit(CLIENTID,$amount,$csum,$moneytp,$orderid,3,$remark);//这是订单支付，最后是3实物
							
			
			}
		
		}
			return("操作成功");
	
	}

public function extractionnewiebypro()
	{//提取收益
		$tbl_pre ="tp_";
		$client_info_id=CLIENTID;
		$orderid = $this->request->param('orderid');
		$proid = $this->request->param('proid');
	
		$sql_order="SELECT id FROM ".$tbl_pre."order_info WHERE ordertp=3 and id=$orderid and isdelete=0 and clientid =$client_info_id";//只能提取自己的订单的收益
			
			
		$sql="select ".$tbl_pre."order_info_detail.* from ".$tbl_pre."order_info_detail inner join ".$tbl_pre."product_info on ".$tbl_pre."product_info.id=".$tbl_pre."order_info_detail.proid where ".$tbl_pre."product_info.id=$proid and ".$tbl_pre."product_info.substatus=1 and ".$tbl_pre."order_info_detail.substatus=0 and ".$tbl_pre."order_info_detail.orderid in(".$sql_order.")";
		
		//product_info.substatus=1 表示 产品已赢 
		//order_info_detail.substatus=0 表示 没有提取收益   
		//条件，提取专门的产品，产品状态已赢，订单的提取状态没有提取

		
		$orderdetailinfo=db::query($sql);
		//dump($orderdetailinfo);
		foreach($orderdetailinfo as $row)
		{//查询已经赢的产品
			$price=(float)$row['price'];
			$quan=(float)$row['quan'];
			$amount=$price*$quan;//收益

			$detailid=$row['id'];//产品明细
			$moneytp=10;
			$csum=0;
			$remark='订单号'.strval($orderid).'产品号'.strval($detailid);
			
			$flag=Loader::model('OrderInfo')->updateOrderExtracted($detailid);//更新订单明细状态
			if($flag==1)
			{//更新订单明细 后，才开始增加收入
					$flag = Loader::model('WalletInfo', 'logic')->deposit(CLIENTID,$amount,$csum,$moneytp,$orderid,3,$remark);//这是订单支付，最后是3实物
							
			
			}
		
		}
	
		 return("提取成功");
	}
public function index_newiegroup()
	{	//新手订单组合列表	
		$client_info_id=CLIENTID;
		 $tbl_pre ="tp_";
					
			$listRows = $this->request->param('numPerPage') ?: 10;
     		$search=['main.isdelete'  =>0,'main.clientid'=>$client_info_id,'main.ordertp'=>4 ] ;
				//条件查询
	
			
				$list=db::table($tbl_pre."order_info")
				->alias(['main'=>$tbl_pre."order_info"])
				->field("main.*")	
				->where($search)
				->order('create_time desc')
				//->fetchSql(true)
				->paginate($listRows, false, ['query' => $this->request->get()]);
				// 把分页数据赋值给模板变量list
				
				$page = $list->render();
				// 模板变量赋值
			

 			   $this->view->assign('list', $list);
                $this->view->assign("count", $list->total());
                $this->view->assign("page", $list->render());
                $this->view->assign('numPerPage', $list->listRows());

		 return $this->view->fetch();
	}

public function detailnewiegroup()
	{//这个订单是明细是查看组合明细
		$tbl_pre ="tp_";
		$client_info_id=CLIENTID;
		
		
			$orderid = $this->request->param('orderid');

			//查询收货地址：
			$sql="SELECT * FROM ".$tbl_pre."order_info WHERE ordertp=4 and id=$orderid and isdelete=0 and clientid =$client_info_id";
			
			$orderinfo=db::query($sql);
			if(count($orderinfo)==0)
			{return;}
				
				$this->view->assign('orderinfo', $orderinfo[0]);	//查询订单
				
				
				$sql="select ".$tbl_pre."order_info_detail.*,".$tbl_pre."product_info.substatus as resultstatus from ".$tbl_pre."order_info_detail inner join ".$tbl_pre."product_info on ".$tbl_pre."product_info.id=".$tbl_pre."order_info_detail.proid where orderid=$orderid";
				$orderdetailinfo=db::query($sql);
				
				
				$this->view->assign('orderdetailinfo', $orderdetailinfo);	//查询订单产品明细
				$arrdate=getdate();
			    $dy=$arrdate['year'];
				$dm=$arrdate['mon'];
				$dd=$arrdate['mday'];

				//$sql="SELECT * FROM ".$tbl_pre."order_group_extract WHERE  clientid=$client_info_id and status=1 and dy=$dy and dm=$dm  order by dd ASC";
				$sql="SELECT * FROM ".$tbl_pre."order_group_extract WHERE orderid=$orderid and  clientid=$client_info_id and status=1 and dy=$dy and dm=$dm  order by dd ASC";
			
				$moneyfetchrecord=db::query($sql);//收益提取记录
					
				$this->view->assign('moneyrecord', $moneyfetchrecord);	//查询 

				$this->view->assign('arrdate', $arrdate);	//查询订单产品明细

			 return $this->view->fetch();	
		
	
		

	}


	public function extractionnewiegroup()
	{//提取从组合收益
		$tbl_pre ="tp_";
		$client_info_id=CLIENTID;
		$clienttp=0;
		$orderid = $this->request->param('orderid');//订单号
		$proid = $this->request->param('proid');//产品号
		$dy = $this->request->param('dy');
		$dm = $this->request->param('dm');
		$dd = $this->request->param('dd');
		//===这要加上验证
		
		$dt = mktime(0,0,0,$dm ,$dd,$dy); //提取时间
		if($dt>getdate())
		{//不能当天前一天的
			return("只能提取当天以前的收益");
		
		}
		
		//验证是不是存在订单组合
		$sql_order="SELECT id FROM ".$tbl_pre."order_info WHERE create_time<'$dy-$dm-$dd' and ordertp=4 and id=$orderid and status=1 and substatus=1 and isdelete=0 and clientid =$client_info_id";//只能提取自己的订单的收益
		
		$orderinfo=db::query($sql_order);
		
		if(empty($orderinfo))
		{	
			 return("只能提取组合建立以后的订单");
		}
		//现在开始验证是不是提取记录
		$sql_record="SELECT id FROM ".$tbl_pre."order_group_extract WHERE dy=$dy and dm=$dm and dd=$dd and orderid=$orderid and proid=$proid and status=1  and isdelete=0 and clientid =$client_info_id";//只能提取自己的订单的收益

		//dump($sql_record);

		
		$orderinfo=db::query($sql_record);
		
		if(!empty($orderinfo))//如果不为空
		{	
			 return("已经提取过");
		}
		//==============================================
		//现在开始查询组合
		$sql_order_ratio="select  price from ".$tbl_pre."order_group_ratio where status=1  and isdelete=0 and proid=$proid and (customtp=0 or customtp=$clienttp) order by price limit 0,1";
		$ordergroup_ratio=db::query($sql_order_ratio);//查询订单组合，每天提取的数量 
		$amount=0.001;//如果没有记录，0.001是
		$moneytp=9;
		$csum=0;//验证，还没有开始用
		$remark="提取$dy 年 $dm 月 $dd 日 $amount 到钱包 $moneytp";
		if(!empty($ordergroup_ratio))//如果不为空
		{	
			$amount=(double)$ordergroup_ratio[0]["price"];
		}
		//==============================
		//现在开始保存提取记录

		
			$data=["clientid"=>$client_info_id,"status"=>"1","dy"=>$dy,"dm"=>$dm,"dd"=>$dd,"proid"=>$proid,"orderid"=>$orderid,"price"=>$amount,"title"=>'','remark'=>''];
			$flag=Loader::model('OrderGroupExtract')->save($data);
			
			if($flag==1)
			{//更新提取 后，才开始增加收入
					$flag = Loader::model('WalletInfo', 'logic')->deposit(CLIENTID,$amount,$csum,$moneytp,$orderid,3,$remark);//这是订单支付，最后是3实物
							
			}
			else
			{
				 return("提取失败");
			}
		
		if($flag==1)
		{
			 return("提取成功");
		}
	
	
	}
	//上面是新手end line =======================
	//新手处理结束============================================================

//组合开始 直接从余
public function advancedgroup()
	{//生成电子 订单,这个函数和上面的函数newie()是一样的，出现问题要同步修改
			
		$tbl_pre ="tp_";
		$client_info_id=CLIENTID;
		$client_level_id=0;
		
		if ($this->request->isAjax() && $this->request->isPost()) {//
			//处理保存
		//下面开始
		$itemCount=(int)$this->request->Post("itemCount");
		if($itemCount==0)
				{
					 return ajax_return_adv_error("购物车是空的！");
				}

		$totalamount=0;
		$arr_pro=[];
		$createorderflag=1;//创建订单
		$totalamount=0;

		for($i=1;$i<=$itemCount;$i++)
		{//
				$proid=$this->request->Post("item_id_".$i);
					
				$pro_quan=(float)$this->request->Post("item_quantity_".$i);//采购数量
				$nodes=Loader::model('Orderinfo', 'logic')->OrderProduct($proid,$pro_quan,$client_level_id,$arr_pro);//这是查询产品限制条件

				if($nodes["status"]==0)
				{//生成订单出错,出现订单限制条件，不能生成订单
					$createorderflag=false;
					if($nodes["errorno"]==4)
					{
						return ajax_return_adv_error("两种产品不能同时购买");
					}
				if($nodes["errorno"]==1)
					{
						return ajax_return_adv_error("库存不足");
					}
				if($nodes["errorno"]==2)
					{
						return ajax_return_adv_error("采购数量小于最少数量");
					}
					if($nodes["errorno"]==3)
					{
						return ajax_return_adv_error("采购数量大于最大数量");
					}
					if($nodes["errorno"]==5)
					{
						return ajax_return_adv_error("产品购买数量已经超出，订单已经存在产品了");
					}

					break;

				}
				else
				{
						$price=$nodes['newprice'];//产品价格
						$proPromotion = Loader::model('Productinfo', 'logic')->ProductPromotionForCreate($proid,$pro_quan,$client_level_id,$price);//这是查询订单的优惠
					
						
						if(count($proPromotion)>0)
						{
							$new_dis_price=$proPromotion[0];//新产品得到最少价格
						
						}
					
							$newprice=(float)$new_dis_price;//新价格

							$oldprice=$price;//老价格
				
							$totalamount=$totalamount+$newprice*$pro_quan;//产品价格
							$nodes['newprice']=$newprice;
							array_push($arr_pro,$nodes);//生成一个新的数组
			

				}
			
			
		}
		
		if($createorderflag)
		{//提示订单生成
					$nodes = Loader::model('Orderinfo', 'logic')->OrderInfo('',0,$totalamount);//这是电子订单 查询相关优惠和费用
					$shipprice=$nodes['ship'];//邮递费用
					$orderprice=$nodes['order'];//订单优惠
					$tax=$nodes['tax'];//税
					$orderdis=$nodes['orderdis'];//订单折扣
					$giftid=$nodes['giftid'];//订单礼品号
					if($giftid>0)
					{//礼品号
						$str_giftremark='订单礼品号：'.strval($giftid);
					}
					else
					{
							$str_giftremark='';
					}

					$totalamount=$totalamount+$shipprice+$orderprice+$tax+$orderdis;
					
					$remark='电子订单邮费:'.strval($shipprice).'订单费用'.strval($orderprice).'税费：'.strval($tax).'订单折扣：'.strval($orderdis).' '.$str_giftremark;
					$address_title='电子订单';			
					$data=['clientid'=>$client_info_id,'title'=>'',"substatus"=>0,"ordertp"=>"5",'totalamount'=>$totalamount,'payid'=>0,'address'=>$address_title,'remark'=>$remark];	
				
					$flag=Loader::model('orderInfo')->save($data);
					$orderid=0;
					//下面开始增加产品明细=====================
					if($flag)
					{//得到订单号
						$list=[];
						$orderid=Loader::model('orderInfo')->id;//订单号
						foreach($arr_pro as $pro)
						{
							array_push($list,["orderid"=>$orderid,"proid"=>$pro["proid"],"newprice"=>$pro["newprice"],"price"=>$pro["oldprice"],"quan"=>$pro["quan"],"title"=>$pro["title"],"remark"=>$pro["remark"],'status'=>1,'substatus'=>0]);
						}
						$oldflag=Loader::model('OrderInfoDetail')->saveall($list);
						

					}
					//下面开始调用支付
					if($orderid>0)
				{//有订单号，才调用
					$payflag=$this->payadvanced($orderid,$totalamount);
					if($payflag==1)
					{//支付成功，更新产品数量
						return ajax_return_adv('下单成功！', '');
					
					}
					else
					{//出错
						return ajax_return_adv_error("支付失败");
						
					}
				}


		}

		}
	
	}

	private function payadvanced($orderid,$amount)
	{//支付新手电子 
		
					$client_info_id=CLIENTID;
					$moneytp=11;//固化
					$csum=0;
					$flag = Loader::model('WalletInfo', 'logic')->pay($client_info_id,$amount,$csum,$moneytp,$orderid,3,'组合产品支付');//这是订单支付，最后是3实物
					if($flag==1)
					{//支付成功更新订单
							
							$flag=Loader::model('OrderInfo')->updateOrderPayed($orderid);//更新支付成功，更新产品数量
								
							
					}
					else
					{
						//这个要更新订单的订单的下架数量
						return -1;
					
					}

		return 1;
	}


	public function index_advanced()
	{	//新手订单组合列表	
		$client_info_id=CLIENTID;
		 $tbl_pre ="tp_";
					
			$listRows = $this->request->param('numPerPage') ?: 10;
     		$search=['main.isdelete'  =>0,'main.clientid'=>$client_info_id,'main.ordertp'=>5 ] ;
				//条件查询
	
			
				$list=db::table($tbl_pre."order_info")
				->alias(['main'=>$tbl_pre."order_info"])
				->field("main.*")	
				->where($search)
				->order('create_time desc')
				//->fetchSql(true)
				->paginate($listRows, false, ['query' => $this->request->get()]);
				// 把分页数据赋值给模板变量list
				
				$page = $list->render();
				// 模板变量赋值
			

 			   $this->view->assign('list', $list);
                $this->view->assign("count", $list->total());
                $this->view->assign("page", $list->render());
                $this->view->assign('numPerPage', $list->listRows());

		 return $this->view->fetch();
	}





	

public function detailadvancedgroup()
	{//这个订单是明细是查看组合明细
		$tbl_pre ="tp_";
		$client_info_id=CLIENTID;
		
		
			$orderid = $this->request->param('orderid');

			//查询收货地址：
			$sql="SELECT * FROM ".$tbl_pre."order_info WHERE ordertp=5 and id=$orderid and isdelete=0 and clientid =$client_info_id";
			
			$orderinfo=db::query($sql);
			if(count($orderinfo)==0)
			{return;}
				
				$this->view->assign('orderinfo', $orderinfo[0]);	//查询订单
				
				
				$sql="select ".$tbl_pre."order_info_detail.*,".$tbl_pre."product_info.substatus as resultstatus from ".$tbl_pre."order_info_detail inner join ".$tbl_pre."product_info on ".$tbl_pre."product_info.id=".$tbl_pre."order_info_detail.proid where orderid=$orderid";
				$orderdetailinfo=db::query($sql);
				
				
				$this->view->assign('orderdetailinfo', $orderdetailinfo);	//查询订单产品明细
				$arrdate=getdate();
			    $dy=$arrdate['year'];
				$dm=$arrdate['mon'];
				$dd=$arrdate['mday'];

				$sql="SELECT * FROM ".$tbl_pre."order_group_extract WHERE orderid=$orderid and  clientid=$client_info_id and status=1 and dy=$dy and dm=$dm  order by dd ASC";
				$moneyfetchrecord=db::query($sql);//收益提取记录
					
				$this->view->assign('moneyrecord', $moneyfetchrecord);	//查询 

				$this->view->assign('arrdate', $arrdate);	//查询订单产品明细

			 return $this->view->fetch();	
		

		

	}


public function extractionadvancedgroup()
	{//提取从组合收益
		$tbl_pre ="tp_";
		$client_info_id=CLIENTID;
		$clienttp=0;
		$orderid = $this->request->param('orderid');//订单号
		$proid = $this->request->param('proid');//产品号
		$dy = $this->request->param('dy');
		$dm = $this->request->param('dm');
		$dd = $this->request->param('dd');
		//===这要加上验证
		
		$dt = mktime(0,0,0,$dm ,$dd,$dy); //提取时间
		if($dt>getdate())
		{//不能当天前一天的
			return("只能提取当天以前的收益");
		
		}
		
		//验证是不是存在订单组合
		$sql_order="SELECT id FROM ".$tbl_pre."order_info WHERE create_time<'$dy-$dm-$dd' and ordertp=5 and id=$orderid and status=1 and substatus=1 and isdelete=0 and clientid =$client_info_id";//只能提取自己的订单的收益
		//dump($sql_order);
		
		$orderinfo=db::query($sql_order);
		
		if(empty($orderinfo))
		{	
			 return("只能提取组合建立以后的订单");
		}
		//现在开始验证是不是提取记录
		$sql_record="SELECT id FROM ".$tbl_pre."order_group_extract WHERE dy=$dy and dm=$dm and dd=$dd and orderid=$orderid and proid=$proid and status=1  and isdelete=0 and clientid =$client_info_id";//只能提取自己的订单的收益

		//dump($sql_record);

		
		$orderinfo=db::query($sql_record);
		
		if(!empty($orderinfo))//如果不为空
		{	
			 return("已经提取过");
		}
		//==============================================
		//现在开始查询组合
		$sql_order_ratio="select  price from ".$tbl_pre."order_group_ratio where status=1  and isdelete=0 and proid=$proid and (customtp=0 or customtp=$clienttp) order by price limit 0,1";
		$ordergroup_ratio=db::query($sql_order_ratio);//查询订单组合，每天提取的数量 
		$amount=0.001;//如果没有记录，0.001是
		$moneytp=9;
		$csum=0;//验证，还没有开始用
		$remark="提取$dy 年 $dm 月 $dd 日 $amount 到钱包 $moneytp";
		if(!empty($ordergroup_ratio))//如果不为空
		{	
			$amount=(double)$ordergroup_ratio[0]["price"];
		}
		//==============================
		//现在开始保存提取记录

		
			$data=["clientid"=>$client_info_id,"status"=>"1","dy"=>$dy,"dm"=>$dm,"dd"=>$dd,"proid"=>$proid,"orderid"=>$orderid,"price"=>$amount,"title"=>'','remark'=>''];
			$flag=Loader::model('OrderGroupExtract')->save($data);
			
			if($flag==1)
			{//更新提取 后，才开始增加收入
					$flag = Loader::model('WalletInfo', 'logic')->deposit(CLIENTID,$amount,$csum,$moneytp,$orderid,3,$remark);//这是订单支付，最后是3实物
							
			}
			else
			{
				 return("提取失败");
			}
		
		if($flag==1)
		{
			 return("提取成功");
		}
	
	
	}

//组合结束 =================================会员组合

//现在是任务区
public function advanced()
	{//生成电子 订单
		//这个函数和下面的函数newiegroup()是一样的，出现问题要同步修改
			
		$tbl_pre ="tp_";
		$client_info_id=CLIENTID;
		$client_level_id=0;
		
		if ($this->request->isAjax() && $this->request->isPost()) {//
			//处理保存

		//下面开始
		$itemCount=(int)$this->request->Post("itemCount");
		if($itemCount==0)
				{
					 return ajax_return_adv_error("购物车是空的！");
				}
		$totalamount=0;
		$arr_pro=[];
		$createorderflag=1;//创建订单
		$totalamount=0;

		for($i=1;$i<=$itemCount;$i++)
		{//
				$proid=$this->request->Post("item_id_".$i);
					
				$pro_quan=(float)$this->request->Post("item_quantity_".$i);//采购数量
				$nodes=Loader::model('Orderinfo', 'logic')->OrderProduct($proid,$pro_quan,$client_level_id,$arr_pro);//这是查询产品限制条件

				if($nodes["status"]==0)
				{//生成订单出错,出现订单限制条件，不能生成订单
					$createorderflag=false;
					if($nodes["errorno"]==4)
					{
						return ajax_return_adv_error("两种产品不能同时购买");
					}
				if($nodes["errorno"]==1)
					{
						return ajax_return_adv_error("库存不足");
					}
				if($nodes["errorno"]==2)
					{
						return ajax_return_adv_error("采购数量小于最少数量");
					}
					if($nodes["errorno"]==3)
					{
						return ajax_return_adv_error("采购数量大于最大数量");
					}
					if($nodes["errorno"]==5)
					{
						return ajax_return_adv_error("产品购买数量已经超出，订单已经存在产品了");
					}


					break;

				}
				else
				{
						$price=$nodes['newprice'];//产品价格
						$proPromotion = Loader::model('Productinfo', 'logic')->ProductPromotionForCreate($proid,$pro_quan,$client_level_id,$price);//这是查询订单的优惠
					
						
						if(count($proPromotion)>0)
						{
							$new_dis_price=$proPromotion[0];//新产品得到最少价格
						
						}
					
							$newprice=(float)$new_dis_price;//新价格

							$oldprice=$price;//老价格
				
							$totalamount=$totalamount+$newprice*$pro_quan;//产品价格
							$nodes['newprice']=$newprice;
							array_push($arr_pro,$nodes);//生成一个新的数组
			

				}
			
			
		}
		
		if($createorderflag)
		{//提示订单生成
					$nodes = Loader::model('Orderinfo', 'logic')->OrderInfo('',0,$totalamount);//这是电子订单 查询相关优惠和费用
					$shipprice=$nodes['ship'];//邮递费用
					$orderprice=$nodes['order'];//订单优惠
					$tax=$nodes['tax'];//税
					$orderdis=$nodes['orderdis'];//订单折扣
					$giftid=$nodes['giftid'];//订单礼品号
					if($giftid>0)
					{//礼品号
						$str_giftremark='订单礼品号：'.strval($giftid);
					}
					else
					{
							$str_giftremark='';
					}

					$totalamount=$totalamount+$shipprice+$orderprice+$tax+$orderdis;
					
					$remark='电子订单邮费:'.strval($shipprice).'订单费用'.strval($orderprice).'税费：'.strval($tax).'订单折扣：'.strval($orderdis).' '.$str_giftremark;
					$address_title='电子订单';			
					$data=['clientid'=>$client_info_id,'title'=>'',"substatus"=>0,"ordertp"=>"6",'totalamount'=>$totalamount,'payid'=>0,'address'=>$address_title,'remark'=>$remark];	
				
					$flag=Loader::model('orderInfo')->save($data);
					$orderid=0;
					//下面开始增加产品明细=====================
					if($flag)
					{//得到订单号
						$list=[];
						$orderid=Loader::model('orderInfo')->id;//订单号
						foreach($arr_pro as $pro)
						{
							array_push($list,["orderid"=>$orderid,"proid"=>$pro["proid"],"newprice"=>$pro["newprice"],"price"=>$pro["oldprice"],"quan"=>$pro["quan"],"title"=>$pro["title"],"remark"=>$pro["remark"],'status'=>1,'substatus'=>0]);
						}
						$oldflag=Loader::model('OrderInfoDetail')->saveall($list);
						

					}
					//下面开始调用支付
					if($orderid>0)
				{//有订单号，才调用
					$payflag=$this->pay_advanced($orderid,$totalamount);
					
					if($payflag==1)
					{//支付成功，更新产品数量
						return ajax_return_adv('下单成功！', '');
					
					}
					else
					{//出错
						return ajax_return_adv_error("支付失败");
						
					}
				}


		}
		}
	
	}

private function pay_advanced($orderid,$amount)
	{//支付 交易区
		
					$client_info_id=CLIENTID;
					$moneytp=9;//固化交易
					$csum=0;
					$flag = Loader::model('WalletInfo', 'logic')->pay($client_info_id,$amount,$csum,$moneytp,$orderid,3,'交易产品支付');//这是订单支付，最后是3实物
					if($flag==1)
					{//支付成功更新订单
							
							$flag=Loader::model('OrderInfo')->updateOrderPayed($orderid);//更新支付成功，更新产品数量
								
							
					}
					else
					{
						//这个要更新订单的订单的下架数量
						return -1;
					
					}

		return 1;
	}


public function index_advancedtask()
	{
	//新手订单组合列表	
		$client_info_id=CLIENTID;
		 $tbl_pre ="tp_";
					
			$listRows = $this->request->param('numPerPage') ?: 10;
     		$search=['main.isdelete'  =>0,'main.clientid'=>$client_info_id,'main.ordertp'=>6 ] ;
				//条件查询
	
			
				$list=db::table($tbl_pre."order_info")
				->alias(['main'=>$tbl_pre."order_info"])
				->field("main.*")	
				->where($search)
				->order('create_time desc')
				//->fetchSql(true)
				->paginate($listRows, false, ['query' => $this->request->get()]);
				// 把分页数据赋值给模板变量list
				
				$page = $list->render();
				// 模板变量赋值
			

 			   $this->view->assign('list', $list);
                $this->view->assign("count", $list->total());
                $this->view->assign("page", $list->render());
                $this->view->assign('numPerPage', $list->listRows());

		 return $this->view->fetch();
	
	
	
	}
public function index_advancedtaskbypro()
	{
	
		//正常订单产品列表，可以直接提取，不是订单模式	
		$client_info_id=CLIENTID;
		 $tbl_pre ="tp_";
					
			$listRows = $this->request->param('numPerPage') ?: 10;
     		$search=['main.isdelete'  =>0,'main.clientid'=>$client_info_id,'main.ordertp'=>6,'main.substatus'=>1,'main.status'=>1 ] ;
				//条件查询,只列出支付成功的项目
	
			
				$list=db::table($tbl_pre."order_info_detail")
				->alias(['main'=>$tbl_pre."order_info","orderdetail"=>$tbl_pre."order_info_detail","proinfo"=>$tbl_pre."product_info"])
				->join($tbl_pre."order_info","main.id = orderdetail.orderid","INNER")
				->join($tbl_pre."product_info","proinfo.id = orderdetail.proid","LEFT")
				->field("main.*,".$tbl_pre."order_info_detail.title as protitle,".$tbl_pre."order_info_detail.substatus,".$tbl_pre."order_info_detail.quan,".$tbl_pre."order_info_detail.quan*(".$tbl_pre."order_info_detail.price-".$tbl_pre."order_info_detail.newprice) as totalrenvue," .$tbl_pre."order_info_detail.substatus as depositstatus,".$tbl_pre."product_info.id as  proid,".$tbl_pre."product_info.substatus as  winstatus")	
				->where($search)
				->order($tbl_pre."order_info.create_time desc")
				
				->paginate($listRows, false, ['query' => $this->request->get()]);
				// 把分页数据赋值给模板变量list
				
				$page = $list->render();
				// 模板变量赋值
				
 			   $this->view->assign('list', $list);
                $this->view->assign("count", $list->total());
                $this->view->assign("page", $list->render());
                $this->view->assign('numPerPage', $list->listRows());

		 return $this->view->fetch();
	
	
	
	}

public function detailadvanced()
	{//这个订单是明细
		$tbl_pre ="tp_";
		$client_info_id=CLIENTID;
		
		
			$orderid = $this->request->param('orderid');

			//查询收货地址：
			$sql="SELECT * FROM ".$tbl_pre."order_info WHERE ordertp=6 and id=$orderid and isdelete=0 and clientid =$client_info_id";
			
			$orderinfo=db::query($sql);
			
			
			$this->view->assign('orderinfo', $orderinfo[0]);	//查询订单
			
			
			$sql="select ".$tbl_pre."order_info_detail.*,".$tbl_pre."product_info.substatus as resultstatus from ".$tbl_pre."order_info_detail inner join ".$tbl_pre."product_info on ".$tbl_pre."product_info.id=".$tbl_pre."order_info_detail.proid where orderid=$orderid";
			$orderdetailinfo=db::query($sql);
			
			
			$this->view->assign('orderdetailinfo', $orderdetailinfo);	//查询订单产品明细




			 return $this->view->fetch();	
		

		

	}
public function extractionadvanced()
	{//提取收益
		$tbl_pre ="tp_";
		$client_info_id=CLIENTID;
		$orderid = $this->request->param('orderid');
	
		$sql_order="SELECT id FROM ".$tbl_pre."order_info WHERE ordertp=6 and id=$orderid and isdelete=0 and clientid =$client_info_id";//只能提取自己的订单的收益
			
			
		$sql="select ".$tbl_pre."order_info_detail.* from ".$tbl_pre."order_info_detail inner join ".$tbl_pre."product_info on ".$tbl_pre."product_info.id=".$tbl_pre."order_info_detail.proid where ".$tbl_pre."product_info.substatus=1 and ".$tbl_pre."order_info_detail.substatus=0 and ".$tbl_pre."order_info_detail.orderid in(".$sql_order.")";//product_info.substatus=1 表示 产品已赢 
		//order_info_detail.substatus=0 表示 没有提取收益   
		$orderdetailinfo=db::query($sql);
		//dump($orderdetailinfo);
		foreach($orderdetailinfo as $row)
		{//查询已经赢的产品
			$price=(float)$row['price'];
			$quan=(float)$row['quan'];
			$amount=$price*$quan;//收益

			$detailid=$row['id'];//产品明细
			$moneytp=11;//直接进入余额
			$csum=0;
			$remark='订单号'.strval($orderid).'产品号'.strval($detailid);
			
			$flag=Loader::model('OrderInfo')->updateOrderExtracted($detailid);//更新订单明细状态
			if($flag==1)
			{//更新订单明细 后，才开始增加收入
					$flag = Loader::model('WalletInfo', 'logic')->deposit(CLIENTID,$amount,$csum,$moneytp,$orderid,3,$remark);//这是订单支付，最后是3实物
							
			
			}
		
		}
	
	
	}
public function extractionadvancedypro()
	{//提取收益 通过产品号
		$tbl_pre ="tp_";
		$client_info_id=CLIENTID;
		$orderid = $this->request->param('orderid');
		$proid = $this->request->param('proid');
		$sql_order="SELECT id FROM ".$tbl_pre."order_info WHERE ordertp=6 and id=$orderid and isdelete=0 and clientid =$client_info_id";//只能提取自己的订单的收益
			
			
		$sql="select ".$tbl_pre."order_info_detail.* from ".$tbl_pre."order_info_detail inner join ".$tbl_pre."product_info on ".$tbl_pre."product_info.id=".$tbl_pre."order_info_detail.proid where ".$tbl_pre."product_info.id=$proid and ".$tbl_pre."product_info.substatus=1 and ".$tbl_pre."order_info_detail.substatus=0 and ".$tbl_pre."order_info_detail.orderid in(".$sql_order.")";//product_info.substatus=1 表示 产品已赢 
		//order_info_detail.substatus=0 表示 没有提取收益   
		$orderdetailinfo=db::query($sql);
		//dump($orderdetailinfo);
		foreach($orderdetailinfo as $row)
		{//查询已经赢的产品
			$price=(float)$row['price'];
			$quan=(float)$row['quan'];
			$amount=$price*$quan;//收益

			$detailid=$row['id'];//产品明细
			$moneytp=11;//直接进入余额
			$csum=0;
			$remark='订单号'.strval($orderid).'产品号'.strval($detailid);
			
			$flag=Loader::model('OrderInfo')->updateOrderExtracted($detailid);//更新订单明细状态
			if($flag==1)
			{//更新订单明细 后，才开始增加收入
					$flag = Loader::model('WalletInfo', 'logic')->deposit(CLIENTID,$amount,$csum,$moneytp,$orderid,3,$remark);//这是订单支付，最后是3实物
							
			
			}
		
		}
		 return("提取成功");
	
	
	}
//==================================任务区



	public function paysteptwo()
	{//支付实物
		
		if ( $this->request->isPost()) {//$this->request->isAjax() &&
			//处理保存


					$client_info_id=Session::get('client_info_id');
					$orderid=$this->request->Post('orderid');
					$amount=$this->request->Post('totalamount');
					$moneytp=9;
					$csum=0;
					$flag = Loader::model('WalletInfo', 'logic')->pay($client_info_id,$amount,$csum,$moneytp,$orderid,3);//这是订单支付，最后是3实物
					if($flag==1)
					{//支付成功更新订单
							
							$flag=Loader::model('OrderInfo')->updateOrderPayed($orderid);//更新支付成功，更新产品数量
								if($flag==1)
									{
										return ajax_return_adv('支付成功！', '');
										
									}
									else
									{
										 return ajax_return_adv_error("支付失败");
									}

					}
					else
					{
						//这个要更新订单的订单的下架数量
						return ajax_return_adv_error("支付失败");
					
					}

		}
	}
   public function pay()
	{	
		$client_info_id=Session::get('client_info_id');
		if ($this->request->isGet()) 
		{
				if($this->request->param('orderid'))
				{//这是列出订单号
					
					$orderid=$this->request->param('orderid');
					$orderinfo=Loader::model('orderInfo')->field('*')->where(["id"=>$orderid,"clietid"=>$client_info_id])->find();
					if($orderinfo['id']==$orderid)
					{
						$orderproinfo=Loader::model('orderInfoDetail')->field('*')->where(["orderid"=>$orderid])->select();

						$this->view->assign('orderinfo', $orderinfo);//订单信息

						$this->view->assign('orderproinfo', $orderproinfo);

					}
					
					return $this->view->fetch();	 
						


				}
			
		
		}
   
   
   }
	
	
    
    protected function filter(&$map)
    {
             
    }
	public function create()
	{//创建订单
		
		$tbl_pre ="tp_";

		$client_info_id=CLIENTID;
		$client_level_id=0;
		if ($this->request->isPost()) {//
			
			$addressid=$this->request->Post("defaultadd");//自己的收货地址号
			$address_title=$this->getAddress($addressid);
			$itemCount=(int)$this->request->Post("itemCount");
			
			$arr_pro=[];
			$createorderflag=1;//创建订单
			$totalamount=0;
			for($i=1;$i<=$itemCount;$i++)
			{//
				$proid=$this->request->Post("item_id_".$i);
					
				$pro_quan=(float)$this->request->Post("item_quantity_".$i);//采购数量
				$nodes=Loader::model('Orderinfo', 'logic')->OrderProduct($proid,$pro_quan);//这是查询产品限制条件

			
				if($nodes["status"]==0)
				{//生成订单出错
					$createorderflag=false;
					break;

				}
				else
				{		
					
					   $price=$nodes['newprice'];//产品价格
						$proPromotion = Loader::model('Productinfo', 'logic')->ProductPromotionForCreate($proid,$pro_quan,$client_level_id,$price);//这是查询订单的优惠
					
						
						rsort($proPromotion);//得到最新的最低价格
						if(count($proPromotion)>0)
						{
							$new_dis_price=$proPromotion[0];//新产品得到最少价格
						
						}
					


					$newprice=(float)$new_dis_price;//新价格

					$oldprice=$price;//老价格
		
					$totalamount=$totalamount+$newprice*$pro_quan;//产品价格
					$nodes['newprice']=$newprice;
					array_push($arr_pro,$nodes);//生成一个新的数组
			
				}

			}
		
			if($createorderflag)
			{//开始创建订单
					$nodes = Loader::model('Orderinfo', 'logic')->OrderInfo($address_title,1,$totalamount);//这是实物订单
					$shipprice=$nodes['ship'];//邮递费用
					$orderprice=$nodes['order'];//订单优惠
					$tax=$nodes['tax'];//税
					$orderdis=$nodes['orderdis'];//订单折扣
					$giftid=$nodes['giftid'];//订单礼品号
					if($giftid>0)
					{//礼品号
						$str_giftremark='订单礼品号：'.strval($giftid);
					}
					else
					{
							$str_giftremark='';
					}

					$totalamount=$totalamount+$shipprice+$orderprice+$tax+$orderdis;
					
					$remark='邮费:'.strval($shipprice).'订单费用'.strval($orderprice).'税费：'.strval($tax).'订单折扣：'.strval($orderdis).' '.$str_giftremark;
										
					$data=['clietid'=>$client_info_id,'title'=>'',"substatus"=>0,"ordertp"=>"1",'totalamount'=>$totalamount,'payid'=>0,'address'=>$address_title,'remark'=>$remark];	
				
					$flag=Loader::model('orderInfo')->save($data);
					$orderid=0;
					
					if($flag)
					{//得到订单号
						$list=[];
						$orderid=Loader::model('orderInfo')->id;//订单号
						foreach($arr_pro as $pro)
						{
							array_push($list,["orderid"=>$orderid,"proid"=>$pro["proid"],"newprice"=>$pro["newprice"],"price"=>$pro["oldprice"],"quan"=>$pro["quan"],"title"=>$pro["title"],"remark"=>$pro["remark"],'status'=>1,'substatus'=>0]);
						}
						$oldflag=Loader::model('OrderInfoDetail')->saveall($list);
						

					}
					$this->redirect('Orderinfo/pay', ['orderid' => $orderid]);//重定向


					
			
			}
			
			
		}
	
	}
	/*得到订单收件地址*/
	public function getAddress($addressid)
	{   $tbl_pre ="tp_";
		$addinfo=db::table($tbl_pre."clientinfo_address")->field("*")->where(["id"=>$addressid])->find();

		$address_title=$addinfo["address_city"]." ".$addinfo["title"]."" .$addinfo["address_personname"]." ".$addinfo["address_zipcode"]." ".$addinfo["address_phone"];//收货地址，这要加
		
		return $address_title;

	}

	public function add()
	{//这个订单是增加收货地址,准备生成订单，查询每个产品是不是可以购买，和优惠信息
		$tbl_pre ="tp_";
		$client_info_id=Session::get('client_info_id');
		$client_level_id=Session::get('client_level_id');//客户等级号
		if($client_level_id==null)
		{$client_level_id=0;}

		if ($this->request->isPost()) {//
			//处理保存预处理订单
			
			$itemCount=(int)$this->request->Post("itemCount");
			
			$arr_pro=[];
		
			for($i=1;$i<=$itemCount;$i++)
			{//
					$proid=$this->request->Post("item_id_".$i);
					
					$pro_quan=(float)$this->request->Post("item_quantity_".$i);//采购数量
					//查询限制条件和优惠
					$proinfo=db::table($tbl_pre."product_info")->field("*")->where(["id"=>$proid,"status"=>"1","isdelete"=>"0","upload_time"=>["ELT","now()"] ])->find();
					$price=$proinfo["price"];
					$quan=(float)$proinfo["quan"];
					$title=$proinfo["title"];

					$pro_buy_limit=true;
					$pro_buy_code=1;

					if($pro_quan>$quan)
					{//采购小于库存，库存不足
					 	$pro_buy_limit=false;
						$pro_buy_code=2;
					}
					 //查询限制


					//查询每个订单的优惠信息

					$proPromotion = Loader::model('Productinfo', 'logic')->ProductPromotionCal($proid,$pro_quan,$client_level_id,$price);//这是订单支付，最后是3实物
					
					rsort($proPromotion);//得到最新的最低价格
					if(count($proPromotion)>0)
					{
						$new_price=$proPromotion[0];//新产品得到最少价格
					
					}
					
					
					$buyprice=$new_price;//采购价格
					
				
						if($pro_buy_limit)
						{//通过产品采购限制
								$arr_pro_row=["newprice"=>$new_price,"no"=>$i,"id"=>$proid,"title"=>$title,"price"=>$price,"rprice"=>$buyprice,"maxquan"=>$quan,"quan"=>$pro_quan,"status"=>1,"code"=>$pro_buy_code];
						
						}
						else
						{//不
								$arr_pro_row=["newprice"=>$new_price,"no"=>$i,"id"=>$proid,"title"=>$title,"price"=>$price,"rprice"=>$buyprice,"maxquan"=>$quan,"quan"=>$pro_quan,"status"=>0,"code"=>$pro_buy_code];
						
						}
						
						array_push($arr_pro,$arr_pro_row);//生成一个新的数组
			


				}

				
				$this->view->assign('itemCount', $itemCount);//订单产品数
				$this->view->assign('orderproinfo', $arr_pro);
				//



			//查询收货地址：
			$sql="SELECT * FROM `".$tbl_pre."clientinfo_address` WHERE status=1 and isdelete=0 and fid =$client_info_id";
			$addressinfo=db::query($sql);
			$this->view->assign('addressinfo', $addressinfo);	
			

			 return $this->view->fetch();	
		}

		if ($this->request->isAjax() && $this->request->isPost()) {//
			//处理保存
		
		
		
		
		}

	}
	

	public function index()
	{		
		$client_info_id=Session::get('client_info_id');
	
		//$this->iniwallet($client_info_id);



	
		 $tbl_pre ="tp_";
			
			 $listRows = $this->request->param('numPerPage') ?: 10;


			
		
			if ($this->request->param('prosortid')) {
				//当前分类
				$prosortid=$this->request->param('prosortid');//产品分类号
				
				$prosortls=db::table($tbl_pre."product_sort")->field("id,title,parentid")->where(["id"=>$prosortid,"status"=>"1","isdelete"=>"0"])->find();
				
				$parentid=$prosortls['parentid'];//上一级产品分类
			    
				$prosortls_parent=[];//上一级产品分类
				
					$prosortls_parent=db::table($tbl_pre."product_sort")->field("id,title")->where(["id"=>$parentid,"status"=>"1","isdelete"=>"0"])->find();
				if($parentid>0)
				{}
				
			
				 $this->view->assign('currprosortls', $prosortls);	//当前分类
				 
				 $this->view->assign('prosort_parent', $prosortls_parent);	//上一级产品分类

				$this->view->assign('parentid', $parentid);	//上一级


				 //加载 子一级分类

				$prosortls=db::table($tbl_pre."product_sort")->field("id,title")->where(["parentid"=>$prosortid,"status"=>"1","isdelete"=>"0"])->select();
				
				 $this->view->assign('childprosortls', $prosortls);	
			


			
			}
				
				

			 // 接受 sort参数 0 表示倒序 非0都 表示正序 
			
				$search=['main.isdelete'  =>'0','main.sortid'=>$this->request->param('prosortid'),"upload_time"=>["ELT","now()"] ] ;
				//条件查询

				if($this->request->param()!=null)
				{
					$order=($this->request->param('_order'));
					$sort=($this->request->param('_sort'));
					$order_by = $order ? "{$order} {$sort}" : false;
					
			
				$list=db::table($tbl_pre."product_info")
				->alias(['main'=>$tbl_pre."product_info"])
				->field("main.*")	
			
				->where($search)
				->order($order_by)
				//->select()
				->paginate($listRows, false, ['query' => $this->request->get()]);
				
				}
				else
				{
				//这个不要排序
					
				$list=db::table($tbl_pre."product_info")
				->alias(['main'=>$tbl_pre."product_info"])
				->field("main.*")	
			
			
				->where($search)
			
			
				//->select()
				->paginate($listRows, false, ['query' => $this->request->get()]);
		
					


				}
				// 把分页数据赋值给模板变量list
		
		
				$page = $list->render();
				// 模板变量赋值



				 $this->view->assign('list', $list);
                $this->view->assign("count", $list->total());
                $this->view->assign("page", $list->render());
                $this->view->assign('numPerPage', $list->listRows());

		 return $this->view->fetch();
	}
//新手
   
 //新手 
}