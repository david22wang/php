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
// 产品信息控制器
//-------------------------

namespace app\desktop\controller;

\think\Loader::import('controller/Controller', \think\Config::get('traits_path') , EXT);

use app\desktop\Controller;
use think\Exception;
use think\Loader;
use think\Db;
class ProductInfo extends Controller
{
    use \app\desktop\traits\controller\Controller;

    //protected static $blacklist = ['delete', 'clear', 'deleteforever', 'recyclebin', 'recycle'];
	
	public function xcopy()
    {
			
			$proid=$this->request->param('id');
			
			$data=Loader::model('ProductInfo')->where(["id"=>$proid])->find();
			
			$data1=["sortid"=>$data["sortid"],"title"=>$data["title"],"price"=>$data["price"],"quan"=>$data["quan"],"upload_time"=>$data["upload_time"],"down_time"=>$data["down_time"],"substatus"=>"0","remark"=>$data["remark"],"operid"=>OPERID,"status"=>"1","pro_tp"=>$data["pro_tp"],"scid"=>$data["scid"],"moneytp"=>$data["moneytp"]];
			$flag=Loader::model('ProductInfo')->isUpdate(false)->save($data1);	
			$newproid=Loader::model('ProductInfo')->id;
			//上面复制产品主体
			//现在复制产品价格
			
		
			$promdata=Loader::model('ProductinfoPromotion')->where(["proid"=>$proid])->select();//查询产品的优惠
			$thirdflag=0;
			$firstflag=0;
			foreach($promdata as $row)
			{
				
				
				$data=["id"=>null,"remark"=>$row["remark"],"proid"=>$newproid,"tp"=>$row["tp"],"operid"=>OPERID,"status"=>$row["status"],"ruletitle"=>$row["ruletitle"],"title"=>$row["title"],"precondition"=>$row["precondition"],"preconditiontag"=>$row["preconditiontag"],"dis_result"=>$row["dis_result"],"dis_resulttag"=>$row["dis_resulttag"],"giftid"=>$row["giftid"],"subtp"=>$row["subtp"],"dis_resultoption"=>$row["dis_resultoption"],"bgtime"=>$row["bgtime"],"endtime"=>$row["endtime"]];		

				$promotionid=$row["id"];//得到促销号
				if($firstflag==0)
				{	$firstflag=1;
					Loader::model('ProductinfoPromotion')->save($data,true,"id");
				}
				else
				{
					
					Loader::model('ProductinfoPromotion')->isUpdate(false)->save($data,true,"id");
				}
				$newpromotionid=Loader::model('ProductinfoPromotion')->id;//得到促销号
				
				
				//
				//现在开始复制价格表
				$pricedata=Loader::model('ProductPriceTable')->where(["proid"=>$proid,"propromotionid"=>$promotionid])->select();//查询价格表,根据订单号,旧促销号
				$secflag=0;
				foreach($pricedata as $data1)
				{
					$vdata=["id"=>null,"proid"=>$newproid,"propromotionid"=>$newpromotionid,"oldprice"=>$data1["oldprice"],"price"=>$data1["price"],"quan"=>$data1["quan"],"bgtime"=>$data1["bgtime"],"endtime"=>$data1["endtime"],"remark"=>$data1["remark"],"custom_tp"=>$data1["custom_tp"],"operid"=>OPERID,"status"=>$data1["status"]];

					
					if($secflag==0)
					{	$secflag=1;
						Loader::model('ProductPriceTable')->save($vdata,true);
					}
					else
					{
						
						Loader::model('ProductPriceTable')->isUpdate(false)->save($vdata,true);
					}
				
				}
				//复制产品限制
				$promdata=Loader::model('ProductinfoRule')->field("*")->where(["proid"=>$proid])->select();//查询产品的限制
			
				
				foreach($promdata as $data)
				{
					$province=$data["province"];
					$customtp=$data["customtp"];
					$minquan=$data["minquan"];
					$maxquan=$data["maxquan"];
					$remark=$data["remark"];
					$operid=OPERID;
					$status=$data["status"];
					$title=$data["title"];
					$otherproid=$data["otherproid"];


					$sql="INSERT INTO `tp_productinfo_rule` (`customtp`, `minquan`, `maxquan`, `province`, `otherproid`, `proid`, `title`, `remark`, `status`, `isdelete`, `operid`, `create_time`, `update_time`, `id`)";
					$sql=$sql."VALUES ('$customtp', '$minquan', '$maxquan', '$province', '$otherproid', '$newproid', '$title', '$remark', '$status', '0',$operid, now(), now(), NULL);";
				    db::execute($sql);
					
				}
				
				
				
				
				



		
			}
			//=======================================

			 return $this->view->fetch("xcopy");

			

	}

	/*新建*/
	public function edit()
    {
				
		if ($this->request->isAjax() && $this->request->isPost()) {//
			//处理保存
			
						$data = $this->request->post();
						
					
						$validate = Loader::validate('ProductInfoValidate');
						 
						if (!$validate->scene('add')->check($data)) {
							return ajax_return_adv_error($validate->getError());
						}
						$data1=["down_time"=>$data["down_time"],"sortid"=>$data["sortid"],"title"=>$data["title"],"price"=>$data["price"],"quan"=>$data["quan"],"upload_time"=>$data["upload_time"],"substatus"=>$data["substatus"],"remark"=>$data["remark"],"operid"=>OPERID,"status"=>$data["status"],"pro_tp"=>$data["pro_tp"],"scid"=>$data["scid"],"moneytp"=>$data["moneytp"]];
						
						
						
						$flag=Loader::model('ProductInfo')->isUpdate(true)->save($data, ['id' => $data['id']]);
					
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
								$proid=$this->request->param('id');

								$list=db::table($tbl_pre."product_info")->field("*")
								->where(['id'=>$this->request->param('id')])
								
								->find();
								$sortid=$list['sortid'];
								
								$list_sort=db::table($tbl_pre."product_sort")->field('*')->where(['id'=>$sortid])->find();

								
								 $this->view->assign('vo_sort', $list_sort);

								
								 $this->view->assign('vo', $list);

								 $list=db::table($tbl_pre."admin_ini")->field("id,title")->where(["status"=>"1","parenttitle"=>"钱包"])->select();
			     			 
								 $this->view->assign('wallet', $list);//钱包类型
								
								$productprice_list_table=db::table($tbl_pre."product_price_table")->field("*")->where(["proid"=>$proid,"status"=>"1","isdelete"=>"0"])->select();

			     				if(empty($productprice_list_table))
								{
									$this->view->assign('priceeditflag', 0);//产品价格表

								}
								else
								{
									 $this->view->assign('priceeditflag', 1);//产品价格表
								}
								 
								 $this->view->assign('productprice_list_table', $productprice_list_table);//产品价格表

								
							
							    return $this->view->fetch("edit");
		}
				
			
	}


/*新建产品*/
	public function add()
    {
				
		if ($this->request->isAjax() && $this->request->isPost()) {//
			//处理保存
			
						$data = $this->request->post();
						
					
				
						$validate = Loader::validate('ProductInfoValidate');
						 
						if (!$validate->scene('add')->check($data)) {
							return ajax_return_adv_error($validate->getError());
						}
						$data1=["down_time"=>$data["down_time"],"sortid"=>$data["sortid"],"title"=>$data["title"],"price"=>$data["price"],"quan"=>$data["quan"],"upload_time"=>$data["upload_time"],"substatus"=>"0","remark"=>$data["remark"],"operid"=>OPERID,"status"=>"1","pro_tp"=>$data["pro_tp"],"scid"=>$data["scid"],"moneytp"=>$data["moneytp"]];
						
						
						$flag=Loader::model('ProductInfo')->save($data1);	
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
							 $list=db::table($tbl_pre."product_sort")->field("id,title")->where(["status"=>"1","id"=>$this->request->param('fid')])->find();
			     			 $this->view->assign('list', $list);
							
							
								
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
	{//这个是列出所有产品分类
		
		
		 $tbl_pre="tp_";
			
			 $listRows = $this->request->param('numPerPage') ?: 10;
				
		//查询
				$search=[] ;      

			
			 if ($this->request->param('title')) {
				$search=['title'  =>  ['like','%'.$this->request->param('title').'%']] ;
			}
		
				

			 // 接受 sort参数 0 表示倒序 非0都 表示正序
			

				if($this->request->param()!=null)
				{
					$order=($this->request->param('_order'));
					$sort=($this->request->param('_sort'));
					$order_by = $order ? "{$order} {$sort}" : false;
					
			
				$list=db::table($tbl_pre."product_sort")
				->alias(['main'=>$tbl_pre."product_sort",'oper'=>$tbl_pre.'desktop_user'])
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
					
				$list=db::table($tbl_pre."product_sort")
				->alias(['main'=>$tbl_pre."product_sort",'oper'=>$tbl_pre.'desktop_user'])
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



	public function index2()
	{//从index跳过来，产品分类号，来列出产品号
		
		
		 $tbl_pre="tp_";
			
			 $listRows = $this->request->param('numPerPage') ?: 10;


			
			 $sortlist=db::table($tbl_pre."product_sort")->field("id,title")->where(["status"=>"1","id"=>$this->request->param('fid')])->find();
			   $this->view->assign('sortlist', $sortlist);

			   
						
			
			   
				
		//查询
				$search=['main.sortid'=>$this->request->param('fid')] ;      

			
			 if ($this->request->param('title')) {
				$search=array_merge($search,['title'  =>  ['like','%'.$this->request->param('title').'%']]) ;
			}
		
				

			 // 接受 sort参数 0 表示倒序 非0都 表示正序
			

				if($this->request->param()!=null)
				{
					$order=($this->request->param('_order'));
					$sort=($this->request->param('_sort'));
					$order_by = $order ? "{$order} {$sort}" : false;
					
			
				$list=db::table($tbl_pre."product_info")
				->alias(['main'=>$tbl_pre."product_info",'oper'=>$tbl_pre.'desktop_user'])
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
					
				$list=db::table($tbl_pre."product_info")
				->alias(['main'=>$tbl_pre."product_info",'oper'=>$tbl_pre.'desktop_user'])
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