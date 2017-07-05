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
// 产品明细
//-------------------------

namespace app\portal\controller;

\think\Loader::import('controller/Controller', \think\Config::get('traits_path') , EXT);

use app\portal\Controller;
use think\Exception;
use think\Loader;
use think\Db;
use think\Session;
class Groupinfo extends Controller
{
    use \app\portal\traits\controller\Controller;


	
	
    
    protected function filter(&$map)
    {
             
    }
public function edit()
{
	if ($this->request->isAjax() && $this->request->isPost()) {//
			//处理保存
			
						$data = $this->request->post();
						
					
				
						
						
						
						$flag=Loader::model('GroupMember')->isUpdate(true)->save($data, ['id' => $data['id']]);
					
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

								$list=db::table($tbl_pre."group_member")
		
								->field("*")
								->where(['id'=>$this->request->param('id')])
								
								->find();
								
								 $this->view->assign('vo', $list);
							return $this->view->fetch("audit");
		}
}
public function mygroup()
{
//我的团队
		$client_info_id=CLIENTID;
		 $tbl_pre ="tp_";
					
			$listRows = $this->request->param('numPerPage') ?: 10;
     		$search=['groupinfo.fid'  =>$client_info_id,'groupinfo.status'=>1 ] ;
				//条件查询
		

				
				$list=db::table($tbl_pre."group_member")
				->alias(['main'=>$tbl_pre."group_member",'groupinfo'=>$tbl_pre."group_info",'passport'=>$tbl_pre."client_passport"])
				->field("main.*,groupinfo.title,passport.account")	

				->join($tbl_pre."group_info","main.groupid = groupinfo.id","LEFT")
				->join($tbl_pre."client_passport","main.fid = passport.id","LEFT")
				
				->where($search)
				//->order($order_by)
				//->select()
				->paginate($listRows, false, ['query' => $this->request->get()]);
	

					$page = $list->render();
				// 模板变量赋值



				 $this->view->assign('list', $list);
                $this->view->assign("count", $list->total());
                $this->view->assign("page", $list->render());
                $this->view->assign('numPerPage', $list->listRows());
			

 			   $this->view->assign('list', $list);
             
		 return $this->view->fetch();

}
public function joingroup()
{//加入团队
	if ($this->request->isAjax() && $this->request->isPost()) {//
			//处理保存
			
						$data = $this->request->post();
						
					 
					
						$data1=["code"=>$data["code"],"status"=>1,"substatus"=>1];
						$vo=Loader::model('GroupInfo')->where($data1)->find();
						if(empty($vo))
						{
							 return ajax_return_adv_error("加入失败");
						}
						else
						{
							$groupid=$vo['id'];
						}
						$data1=["fid"=>CLIENTID,"groupid"=>$groupid,"substatus"=>0,"status"=>1];
						$flag=Loader::model('GroupMember')->save($data1);
						
						if($flag==1)
						{
							return ajax_return_adv('加入申请成功！', '');
							
						}
						else
						{
							 return ajax_return_adv_error("加入申请失败");
						}
					

						
						
			
				
			}
			else
		{
			
			
			
			$groupinfo=Db::name("GroupMember")->where(['fid'=>CLIENTID])->where('substatus=0 or substatus=1')->select();
			//没有申请记录或申请成功

			 if(count($groupinfo)>0)
				{
					 $this->view->assign('list', $groupinfo);
					 return $this->view->fetch("joinmemeberrecord");
					 
				}
				else
			{
				return $this->view->fetch();
			}
			
			
		}



}



public function creategroup()
	{
		if ($this->request->isAjax() && $this->request->isPost()) {//
			//处理保存
			
						$data = $this->request->post();
						
					 
						$validate = Loader::validate('GroupinfoValidate');
						
						if (!$validate->scene('add')->check($data)) {
							return ajax_return_adv_error($validate->getError());
						}
						if($data["id"])
						{	
							  $data1=["id"=>$data["id"],"fid"=>CLIENTID,"title"=>$data["title"],"code"=>$data["code"],"substatus"=>1,"remark"=>$data["remark"]];
						
						      $flag=Loader::model('GroupInfo')->isUpdate(true)->save($data1);
						}
						else
						{//
						  $data1=["fid"=>CLIENTID,"title"=>$data["title"],"code"=>$data["code"],"substatus"=>1,"remark"=>$data["remark"]];
						
						$flag=Loader::model('GroupInfo')->save($data1);
						}
						if($flag==1)
						{
							return ajax_return_adv('创建成功！', '');
							
						}
						else
						{
							 return ajax_return_adv_error("创建失败");
						}
					

						
						
			
				
			}
			else
		{
			
			$groupinfo=Db::name("GroupInfo")->where(['fid'=>CLIENTID,"status"=>1])->find();
			 if(count($groupinfo)>0)
				{
					 $this->view->assign('vo', $groupinfo);
					  return $this->view->fetch("edit");
				}
				else
			{
			
			 return $this->view->fetch();
			}
		}
	}

	public function index()
	{		
		$client_info_id=Session::get('client_info_id');
	
		$client_level_id=Session::get('client_level_id');//客户等级号
		if($client_level_id==null)
		{
			$client_level_id=0;
		}



	
		 $tbl_pre="tp_";
			
			 $listRows = $this->request->param('numPerPage') ?: 10;


			
		
			if ($this->request->param('proid')) {
				//当前分类
				


			
			}
				
				

			 // 接受 sort参数 0 表示倒序 非0都 表示正序 
			
				$search=['main.status'  =>'1','main.isdelete'  =>'0','main.id'=>$this->request->param('proid'),"upload_time"=>["ELT","now()"] ] ;
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
				->find();
				
				}
				else
				{
				//这个不要排序
					
				$list=db::table($tbl_pre."product_info")
				->alias(['main'=>$tbl_pre."product_info"])
				->field("main.*")	
				->where($search)
				->find();
				
					


				}
				// 把分页数据赋值给模板变量list

				 $this->view->assign('proinfo', $list);
              
				//加载优惠
				$proPromotion = Loader::model('Productinfo', 'logic')->ProductPriceTable($this->request->param('proid'),$client_level_id);//这是订单支付，最后是3实物
				 $this->view->assign('proPromotion', $proPromotion);//优惠信息	
			 return $this->view->fetch();
	}

   

   
}