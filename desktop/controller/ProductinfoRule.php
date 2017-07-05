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
// 产品限制控制器
//
//-------------------------

namespace app\desktop\controller;

\think\Loader::import('controller/Controller', \think\Config::get('traits_path') , EXT);

use app\desktop\Controller;
use think\Exception;
use think\Loader;
use think\Db;

class ProductinfoRule extends Controller
{
    use \app\desktop\traits\controller\Controller;

    //protected static $blacklist = ['delete', 'clear', 'deleteforever', 'recyclebin', 'recycle'];

/*批量新建帐号*/
	/*新建帐号*/
	
/*新建帐号*/
	public function edit()
    {
				
		if ($this->request->isAjax() && $this->request->isPost()) {//
			//处理保存
			
						$data = $this->request->post();
						
					
						
						$validate = Loader::validate('ProductinfoRuleValidate');
						 
						if (!$validate->scene('add')->check($data)) {
							return ajax_return_adv_error($validate->getError());
						}

						
							$str_prov=implode("@",$data["prov"] );//将省份转为字府串

						$data=["id"=>$data["id"],"province"=>$str_prov,"customtp"=>$data["customtp"],"minquan"=>$data["minquan"],"maxquan"=>$data["maxquan"],"remark"=>$data["remark"],"operid"=>OPERID,"status"=>$data["status"],"title"=>$data["title"],"otherproid"=>$data["otherproid"]];
						
						
						
						$flag=Loader::model('ProductinfoRule')->isUpdate(true)->save($data, ['id' => $data['id']]);
					
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

	


							    $tbl_pre="tp_";

								$list=db::table($tbl_pre."productinfo_rule")->field("*")
								->where(['id'=>$this->request->param('id')])
								
								->find();
								$array_prov=explode("@",$list["province"]);
								$this->view->assign('array_prov', $array_prov);
								
								 $this->view->assign('vo', $list);

								$custom=db::table($tbl_pre."admin_ini")->field("id,title")->where(["status"=>"1","parenttitle"=>"会员等级"])->select();
			     			 
							     $this->view->assign('custom', $custom);//会员类型

							
							    return $this->view->fetch("edit");
		}
				
			
	}



/*新建产品限制*/
	public function add()
    {
		 $tbl_pre="tp_";		
		if ($this->request->isPost()) {//$this->request->isAjax() && 
			//处理保存
			
						$data = $this->request->post();
						
						
				
						$validate = Loader::validate('ProductinfoRuleValidate');
						 
						if (!$validate->scene('add')->check($data)) {
							return ajax_return_adv_error($validate->getError());
						}
						
						$str_prov=implode("@",$data["prov"] );//将省份转为字府串



						$data1=["province"=>$str_prov,"customtp"=>$data["customtp"],"minquan"=>$data["minquan"],"maxquan"=>$data["maxquan"],"remark"=>$data["remark"],"proid"=>$data["proid"],"operid"=>OPERID,"status"=>$data["status"],"title"=>$data["title"],"otherproid"=>$data["otherproid"]];
						
						
						$flag=Loader::model('ProductinfoRule')->save($data1);	
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
		{					$custom=db::table($tbl_pre."admin_ini")->field("id,title")->where(["status"=>"1","parenttitle"=>"会员等级"])->select();
			     			 
							$this->view->assign('custom', $custom);//会员类型

							
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

			
			  if ($this->request->param('fid')) {
				$search=['proid'  =>  $this->request->param('fid')] ;
			}
		
		
				

			 // 接受 sort参数 0 表示倒序 非0都 表示正序
			

				if($this->request->param()!=null)
				{
					$order=($this->request->param('_order'));
					$sort=($this->request->param('_sort'));
					$order_by = $order ? "{$order} {$sort}" : false;
					
			
				$list=db::table($tbl_pre."productinfo_rule")
				->alias(['main'=>$tbl_pre."productinfo_rule",'oper'=>$tbl_pre.'desktop_user'])
				->field("main.*,oper.realname")	
				->join($tbl_pre."desktop_user","main.operid = oper.id","LEFT")

				
				->where($search)
				->order($order_by)
				//->select()
				->paginate($listRows, false, ['query' => $this->request->get()]);
				
				}
				else
				{
				//这个不要排序
					
				$list=db::table($tbl_pre."productinfo_rule")
				->alias(['main'=>$tbl_pre."productinfo_rule",'oper'=>$tbl_pre.'desktop_user'])
				->field("main.*,oper.realname")	
				->join($tbl_pre."desktop_user","main.operid = oper.id","LEFT")

				
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