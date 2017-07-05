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
// 订单列表
//
//-------------------------

namespace app\desktop\controller;

\think\Loader::import('controller/Controller', \think\Config::get('traits_path') , EXT);

use app\desktop\Controller;
use think\Exception;
use think\Loader;
use think\Db;

class OrderInfo extends Controller
{
    use \app\desktop\traits\controller\Controller;

    //protected static $blacklist = ['delete', 'clear', 'deleteforever', 'recyclebin', 'recycle'];
/*新建帐号*/
	
	//===============================
	//编辑订单
	
		/*编辑联系人信息*/
	public function edit()
    {
				
		if ($this->request->isAjax() && $this->request->isPost()) {//
			//处理保存
			
						$data = $this->request->post();
							
				
						
					
						
						
						$flag=Loader::model('OrderInfo')->isUpdate(true)->save($data, ['id' => $data['id']]);
					
						if($flag==1)
						{
							return ajax_return_adv('更新成功！', '');
							
						}
						else
						{
							 return ajax_return_adv_error("更新失败");
						}
				

						
						
			
				
			}
		else
		{

				$tbl_pre="tp_";
								
				$list=db::table($tbl_pre."order_info")
				->alias(['main'=>$tbl_pre."order_info",'oper'=>$tbl_pre.'desktop_user','client'=>$tbl_pre."client_passport"])
				->field("main.*,client.account")	
				->join($tbl_pre."client_passport","main.clientid = client.id","LEFT")
				->where(['main.id'=>$this->request->param('id')])
				->find();

				$listdetail=db::table($tbl_pre."order_info_detail")->field("*")->where(['orderid'=>$this->request->param('id')])->select();
							   
				
								
		   $this->view->assign('vo', $list);
			$this->view->assign('detail', $listdetail);//订单明细

			return $this->view->fetch("edit");
		}
				
			
	}



    protected function filter(&$map)
    {
        //不查询管理员
      
		 if ($this->request->param('orderid')) {
            $map['id'] = ["=",  $this->request->param('orderid')];
        }
       
       


      
    }

		public function index()
	{		
		
		
		 $tbl_pre="tp_";
			
			 $listRows = $this->request->param('numPerPage') ?: 10;
				
		//查询
				$search=[] ;      

			
			 if ($this->request->param('orderid')) {
				$search=['main.id'  =>  $this->request->param('orderid')] ;
			}

			if ($this->request->param('ordertp')) {
				$search=array_merge($search, ['ordertp'  => $this->request->param('ordertp')]) ;
			}
				

			 // 接受 sort参数 0 表示倒序 非0都 表示正序
			

				if($this->request->param()!=null)
				{
					$order=($this->request->param('_order'));
					$sort=($this->request->param('_sort'));
					$order_by = $order ? "{$order} {$sort}" : false;
					
			
				$list=db::table($tbl_pre."order_info")
				->alias(['main'=>$tbl_pre."order_info",'oper'=>$tbl_pre.'desktop_user','client'=>$tbl_pre."client_passport"])
				->field("main.*,client.account")	
				->join($tbl_pre."client_passport","main.clientid = client.id","LEFT")

				
				->where($search)
				->order($order_by)
				//->select()
				->paginate($listRows, false, ['query' => $this->request->get()]);
				
				}
				else
				{
				//这个不要排序
					
				$list=db::table($tbl_pre."order_info")
				->alias(['main'=>$tbl_pre."order_info",'oper'=>$tbl_pre.'desktop_user','client'=>$tbl_pre."client_passport"])
				->field("main.*,client.account")	
				->join($tbl_pre."client_passport","main.clientid = client.id","LEFT")

				
				->where($search)
				
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