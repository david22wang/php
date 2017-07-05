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
// 个人信息控制器
//-------------------------

namespace app\desktop\controller;

\think\Loader::import('controller/Controller', \think\Config::get('traits_path') , EXT);

use app\desktop\Controller;
use think\Exception;
use think\Loader;
use think\Db;
class ClientInfo extends Controller
{
    use \app\desktop\traits\controller\Controller;

    //protected static $blacklist = ['delete', 'clear', 'deleteforever', 'recyclebin', 'recycle'];


	
		/*编辑联系人信息*/
	public function edit()
    {
				
		if ($this->request->isAjax() && $this->request->isPost()) {//
			//处理保存
			
						$data = $this->request->post();
						
					
				
						$validate = Loader::validate('ClientInfoValidate');
						 
						if (!$validate->scene('add')->check($data)) {
							return ajax_return_adv_error($validate->getError());
						}
					
						
						
						$flag=Loader::model('ClientInfo')->isUpdate(true)->save($data, ['id' => $data['id']]);
					
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

								$list=db::table($tbl_pre."client_info")
		
								->field("*")
								->where(['id'=>$this->request->param('id')])
								
								->find();
								
								 $this->view->assign('vo', $list);
							return $this->view->fetch("edit");
		}
				
			
	}


	/*新建帐号*/
	public function add()
    {
				
		if ($this->request->isAjax() && $this->request->isPost()) {//
			//处理保存
			
						$data = $this->request->post();
						
					
				
						$validate = Loader::validate('ClientInfoValidate');
						 
						if (!$validate->scene('add')->check($data)) {
							return ajax_return_adv_error($validate->getError());
						}
					
						$data=array_merge($data,["fid"=>0,"status"=>"1"]);	
						
						$flag=Loader::model('ClientInfo')->save($data);	
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
							return $this->view->fetch("add");
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
		
		
		 $tbl_pre="tp_";
			
			 $listRows = $this->request->param('numPerPage') ?: 10;


			
						
			
			   
				
		//查询
				$search=[] ;      

			
			 if ($this->request->param('nm')) {
				$search=['nm'  =>  ['like','%'.$this->request->param('nm').'%']] ;
			}
			if ($this->request->param('nickname')) {
				$search=array_merge($search,['nickname'  =>  ['like','%'.$this->request->param('nickname').'%']]) ;
			}
			if ($this->request->param('substatus')) {
				$search=array_merge($search, ['substatus'  => $this->request->param('substatus')]) ;
			}
				
				

			 // 接受 sort参数 0 表示倒序 非0都 表示正序
			

				if($this->request->param()!=null)
				{
					$order=($this->request->param('_order'));
					$sort=($this->request->param('_sort'));
					$order_by = $order ? "{$order} {$sort}" : false;
					
			
				$list=db::table($tbl_pre."client_info")
		
				->field("*")
				->where($search)
				->order($order_by)
				//->select()
				->paginate($listRows, false, ['query' => $this->request->get()]);
				
				}
				else
				{
				//这个不要排序
					
					$list=db::table($tbl_pre."client_info")
					->field("*")	
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

 
   
/**
     * 列表动作
     */
    public function more()
    {
        
		return $this->view->fetch();
    }


	/**
     * 联系人
     */
    public function add_c()
    {
        $id = $this->request->param('id/d');//会员号
	
		$action= $this->request->param('action');
		 return $this->view->fetch();
    }

   
}