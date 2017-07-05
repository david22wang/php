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

  //protected static $blacklist = ['delete', 'clear', 'deleteforever', 'recyclebin', 'recycle'];

/*批量新建帐号*/
	
	
    
    protected function filter(&$map)
    {
             
    }
	public function creategroup()
	{
		if ($this->request->isAjax() && $this->request->isPost()) {//
			//处理保存
			
						$data = $this->request->post();
						
					 
						$validate = Loader::validate('ClientInfoContactValidate');
						
						if (!$validate->scene('add')->check($data)) {
							return ajax_return_adv_error($validate->getError());
						}
					
						
						$flag=Loader::model('ClientinfoContact')->isUpdate(true)->save($data, ['id' => $data['id'],'fid'=>$client_info_id]);
					
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

		 return $this->view->fetch();
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