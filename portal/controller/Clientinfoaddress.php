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
// 个人信息控制器->收货地址
//-------------------------

namespace app\portal\controller;

\think\Loader::import('controller/Controller', \think\Config::get('traits_path') , EXT);

use app\portal\Controller;
use think\Exception;
use think\Loader;
use think\Db;
use think\Session;
use think\Config;

class ClientinfoAddress extends Controller
{
    use \app\portal\traits\controller\Controller;

    //protected static $blacklist = ['delete', 'clear', 'deleteforever', 'recyclebin', 'recycle'];


	

	public function add()
	{	

		
		$client_info_id=Session::get(Config::get('rbac.user_info_key'));
			
		if ( $this->request->isAjax() &&$this->request->isPost()) {//
			//处理保存
			
						$data = $this->request->post();
						
					 
						$validate = Loader::validate('ClientinfoAddressValidate');
						
						if (!$validate->scene('add')->check($data)) {
							return ajax_return_adv_error($validate->getError());
						}
					
						$data=["fid"=>$data['fid'],"status"=>$data['status'],'title'=>$data['title'],"tp"=>$data['tp'],"remark"=>$data['remark'],'defaultadd'=>$data['defaultadd']];	
				
						$flag=Loader::model('ClientinfoAddress')->save($data);	
					
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
	
		$this->view->assign('fid',$client_info_id);//得到父ID
		return $this->view->fetch("edit");
		}
	}


	 public function edit()
	{
		     $tbl_pre = Config::get("database.prefix");
			 $client_info_id=Session::get(Config::get('rbac.user_info_key'));

		if ($this->request->isAjax() && $this->request->isPost()) {//
			//处理保存
			
						$data = $this->request->post();
						
					 
						$validate = Loader::validate('ClientinfoAddressValidate');
						
						if (!$validate->scene('add')->check($data)) {
							return ajax_return_adv_error($validate->getError());
						}
					
						
						$flag=Loader::model('ClientinfoAddress')->isUpdate(true)->save($data, ['id' => $data['id']]);
					
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
	
				$list=db::table($tbl_pre."clientinfo_address")
		
				->field("*")
				->where(['id'=>$this->request->param('id')])
				
				->select();
				
				 $this->view->assign('vo', $list[0]);
				return $this->view->fetch("edit");
		}
	}


    
    protected function filter(&$map)
    {
        //不查询管理员
      
		 if ($this->request->param('nm')) {
            $map['nm'] = ["like", "%" . $this->request->param('nm') . "%"];
        }
       
       


      
    }
	public function index()
	{	
		     $tbl_pre = Config::get("database.prefix");
			
			 $listRows = $this->request->param('numPerPage') ?: 10;

			$client_info_id=Session::get(Config::get('rbac.user_info_key'));
			
						
			
			   
				
		//查询
				$search=[] ;      

				
				$search=['fid'  =>$client_info_id   ] ;
		

			if ($this->request->param('title')) {
				$search=array_merge($search,['title'  =>  ['like','%'.$this->request->param('title').'%']]) ;
			}
				

			 // 接受 sort参数 0 表示倒序 非0都 表示正序
			

				if($this->request->param()!=null)
				{
					$order=($this->request->param('_order'));
					$sort=($this->request->param('_sort'));
					$order_by = $order ? "{$order} {$sort}" : false;
					
			
				$list=db::table($tbl_pre."clientinfo_address")
		
				->field("*")
				->where($search)
				->order($order_by)
				//->select()
				->paginate($listRows, false, ['query' => $this->request->get()]);
				
				}
				else
				{
				//这个不要排序
					
					$list=db::table($tbl_pre."clientinfo_address")
					->field("*")	
					->where($search)
			
				//->select()
				->paginate($listRows, false, ['query' => $this->request->get()]);
		
					


				}
				// 把分页数据赋值给模板变量list
		
		
				$page = $list->render();
				// 模板变量赋值

				 $this->view->assign('fid',$client_info_id);//得到父ID

				 $this->view->assign('list', $list);
                $this->view->assign("count", $list->total());
                $this->view->assign("page", $list->render());
                $this->view->assign('numPerPage', $list->listRows());
			
			return $this->view->fetch();
	}

 
  
   
}