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
// 钱包收支明细
//-------------------------

namespace app\portal\controller;

\think\Loader::import('controller/Controller', \think\Config::get('traits_path') , EXT);

use app\portal\Controller;
use think\Exception;
use think\Loader;
use think\Db;
use think\Session;
class Wallettrade extends Controller
{
    use \app\portal\traits\controller\Controller;

  //protected static $blacklist = ['delete', 'clear', 'deleteforever', 'recyclebin', 'recycle'];

/*批量新建帐号*/
	
	
    
    protected function filter(&$map)
    {
             
    }
	//===============================
	//新手支付
	public function payfornewie($orderid)
	{//新手支付
	
		
		//第一步检查订单的合法性
		$client_info_id=CLIENTID;
		$node = Loader::model('Orderinfo', 'logic')->CheckOrderInfoforpay($orderid,$client_info_id);//这是订单支付，最后是3实物
		if($node['status']==1)
		{//支付
					$amount=$node['amount'];
			
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

		
		}
		else
		{
				
		}
	
	
	}
	//新手支付===============================end

	public function deposit()
	{//收入
		
			$orderid=$this->request->param('orderid');//订单号
					if($orderid==null)
					{
						$orderid=0;
					}

					$amount=$this->request->param('amount');//变动数量

					$moneytp=8;
					$csum=0;
					$flag = Loader::model('WalletInfo', 'logic')->deposit(CLIENTID,$amount,$csum,$moneytp,$orderid,3,'前台收入');//这是订单支付，最后是3实物
		
	
					
		
	
	}
	public function pay()
	{//收入
		
					$orderid=$this->request->param('orderid');//订单号
					if($orderid==null)
					{
						$orderid=0;
					}

					$amount=$this->request->param('amount');//变动数量

					$moneytp=8;
					$csum=0;
					$flag = Loader::model('WalletInfo', 'logic')->pay(CLIENTID,$amount,$csum,$moneytp,$orderid,3,'前台支出');//这是订单支付，最后是3实物
		

		
	
	}


	public function index()
	{		
		$client_info_id=Session::get('client_info_id');
	
		//$this->iniwallet($client_info_id);



		$client_info_id=Session::get('client_info_id');
	
		
		 $tbl_pre="tp_";
			
			 $listRows = $this->request->param('numPerPage') ?: 10;


			$search=['main.clientid'  =>$client_info_id   ] ;
		
				 if ($this->request->param('account')) {
				//$search=['main.account'  =>  ['like','%'.$this->request->param('account').'%']] ;
			}
				
				

			 // 接受 sort参数 0 表示倒序 非0都 表示正序
			

				if($this->request->param()!=null)
				{
					$order=($this->request->param('_order'));
					$sort=($this->request->param('_sort'));
					$order_by = $order ? "{$order} {$sort}" : false;
					
			
				$list=db::table($tbl_pre."client_wallet")
				->alias(['main'=>$tbl_pre."client_wallet",'ini'=>$tbl_pre."admin_ini"])
				->field("main.*,ini.title,ini.val")	
				->join($tbl_pre."admin_ini","main.moneytp = ini.id","LEFT")
			
				->where($search)
				//->order($order_by)
				//->select()
				->paginate($listRows, false, ['query' => $this->request->get()]);
				
				}
				else
				{
				//这个不要排序
					
					$list=db::table($tbl_pre."client_wallet")
				
				->alias(['main'=>$tbl_pre."client_wallet",'ini'=>$tbl_pre."admin_ini"])
				->field("main.*,ini.title")	
				->join($tbl_pre."admin_ini","main.moneytp = ini.id","LEFT")
			
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

   

   
}