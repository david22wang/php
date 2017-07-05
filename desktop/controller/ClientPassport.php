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
// 用户控制器
//-------------------------

namespace app\desktop\controller;

\think\Loader::import('controller/Controller', \think\Config::get('traits_path') , EXT);

use app\desktop\Controller;
use think\Exception;
use think\Loader;
use think\Db;
class ClientPassport extends Controller
{
    use \app\desktop\traits\controller\Controller;

  //protected static $blacklist = ['delete', 'clear', 'deleteforever', 'recyclebin', 'recycle'];

/*批量新建帐号*/
	public function batchadd()
    {
				
		//
				
			if ($this->request->isAjax() && $this->request->isPost()) {//批量处理
						 
				$data = $this->request->post();
				$num=$data["num"];
				$account_pre=$data["account2"];
				$pwd=$data["password"];
				$remark=$data["remark"];
				
				for ($x=0; $x<intval($num); ) {
						$account=$account_pre.$x;
						$data1=array("account2"=>$account,"password"=>$pwd,"status"=>"1","remark"=>$remark,"operid"=>OPERID,"regsource"=>"1","parentid"=>"0","reglevel"=>"0000");
						
						$validate = Loader::validate('ClientPassportValidate');
						 
						if (!$validate->scene('opercreate')->check($data1)) {
							return ajax_return_adv_error($validate->getError());
						}

						$data1=array("account"=>$account,"password"=>$pwd,"status"=>"1","remark"=>$remark,"operid"=>OPERID,"regsource"=>"1","parentid"=>"0","reglevel"=>"0000");
						

						$flag=Loader::model('ClientPassport')->savebatch($data1);	
						if($flag==1)
						{
							$x++;
							
						}
						else
						{	return ajax_return_adv_error("创建失败");
							break;
						}
						
						
				}
				return ajax_return_adv('创建成功！', '');
							
			
				
				}
				else
				{
						return $this->view->fetch();
				}
	}
	/*新建帐号*/
	public function add()
    {
				
		if ($this->request->isAjax() && $this->request->isPost()) {//
			//处理保存
			
						$data = $this->request->post();
						
						$account=$data["account2"];
						$pwd=$data["password"];
						$remark=$data["remark"];
				
						$validate = Loader::validate('ClientPassportValidate');
						 
						if (!$validate->scene('opercreate')->check($data)) {
							return ajax_return_adv_error($validate->getError());
						}
						$fid=$data["fid"];
						
						$data1=array("account"=>$account,"password"=>$pwd,"status"=>"1","remark"=>$remark,"operid"=>OPERID,"regsource"=>"1","parentid"=>"0","reglevel"=>"0000");
						
						$flag=Loader::model('ClientPassport')->save($data1);
						$pk=Loader::model('ClientPassport')->id;

						
						if($flag==1)
						{
									if(intval($fid)>0)
									{//如果￥fid大于0，表示是CLINETINFO已经存在
										$pk=Loader::model('ClientPassport')->id;
										
										$flag=Loader::model('ClientInfo')->updateClientinfoaccpk($fid,$pk);


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
										return ajax_return_adv('创建成功！', '');
									
									}

						}
						
						else
						{
							return ajax_return_adv_error("创建失败");
						}
					

						
						
			
				
			}
		else
		{

				$data = $this->request->param();
				
				if(isset($data['fid']))
				{
					$fid=$data['fid'];
				}
				else
				{
					$fid=0;

				}
				 $this->view->assign('fid', $fid);

				return $this->view->fetch("edit");
		}
				
			
	}
/*新建帐号*/
	public function read()
    {
		  
			$data = $this->request->param();
				
				if(isset($data['id']))
				{
					$id=$data['id'];
				}
				else
				{
					$fid=0;

				}


		   $vo =Loader::model('ClientPassport')->find($id);
		  $this->view->assign("vo", $vo);

		return $this->view->fetch();
	}
    
    protected function filter(&$map)
    {
        //不查询管理员
      
		 if ($this->request->param('account')) {
            $map['account'] = ["like", "%" . $this->request->param('account') . "%"];
        }
       
       


      
    }
	public function index()
	{		
		
		
		 $tbl_pre="tp_";
			
			 $listRows = $this->request->param('numPerPage') ?: 10;


			
						
			
			   
				
		//查询
				$search=[] ;      

			
			 if ($this->request->param('account')) {
				$search=['main.account'  =>  ['like','%'.$this->request->param('account').'%']] ;
			}
				
				

			 // 接受 sort参数 0 表示倒序 非0都 表示正序
			

				if($this->request->param()!=null)
				{
					$order=($this->request->param('_order'));
					$sort=($this->request->param('_sort'));
					$order_by = $order ? "{$order} {$sort}" : false;
					
			
				$list=db::table($tbl_pre."client_passport")
				->alias(['main'=>$tbl_pre."client_passport",'w'=>$tbl_pre."client_info",'oper'=>$tbl_pre.'desktop_user'])
				->field("main.*,w.mobile,w.nm,oper.realname")	
				->join($tbl_pre."client_info","main.id = w.fid","LEFT")
				->join($tbl_pre."desktop_user","main.operid = oper.id","LEFT")

				->where($search)
				->order($order_by)
				//->select()
				->paginate($listRows, false, ['query' => $this->request->get()]);
				
				}
				else
				{
				//这个不要排序
					
					$list=db::table($tbl_pre."client_passport")
				->alias(['main'=>$tbl_pre."client_passport",'w'=>$tbl_pre."client_info",'oper'=>$tbl_pre.'desktop_user'])
				->field("main.*,w.mobile,w.nm,oper.realname")	
				->join($tbl_pre."client_info","main.id = w.fid","LEFT")
				->join($tbl_pre."desktop_user","main.operid = oper.id","LEFT")

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
     * 修改密码
     */
    public function password()
    {
        $id = $this->request->param('id/d');
        if ($this->request->isPost()) {
            //禁止修改管理员的密码，管理员id为1
           

            $password = $this->request->post('password');
            if (!$password) {
                return ajax_return_adv_error("密码不能为空");
            }
            if (false === Loader::model('ClientPassport')->updatePassword($id, $password)) {
                return ajax_return_adv_error("密码修改失败");
            }
            return ajax_return_adv("密码已修改为{$password}", '');
        } else {
            // 禁止修改管理员的密码，管理员 id 为 1
            if ($id < 2) {
                throw new Exception("缺少必要参数");
            }

            return $this->view->fetch();
        }
    }
/**
     * 修改密码
     */
    public function more()
    {
        $id = $this->request->param('id/d');//会员号
        if ($this->request->isPost()) {
            //禁止修改管理员的密码，管理员id为1
            if ($id < 2) {
                return ajax_return_adv_error("缺少必要参数");
            }

            $realname = $this->request->post('realname');
			$mobile = $this->request->post('mobile');

            if (!$password) {
                return ajax_return_adv_error("密码不能为空");
            }
            if (false === Loader::model('ClientInfo')->updatePassword($id, $password)) {
                return ajax_return_adv_error("密码修改失败");
            }
            return ajax_return_adv("信息修改成功", '');
        } else {
           
            return $this->view->fetch();
        }
    }

   
}