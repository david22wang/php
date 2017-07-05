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
use think\Config;
class Prosort extends Controller
{
    use \app\portal\traits\controller\Controller;

  //protected static $blacklist = ['delete', 'clear', 'deleteforever', 'recyclebin', 'recycle'];

/*批量新建帐号*/
	
	
    
    protected function filter(&$map)
    {
             
    }

	

	public function index()
	{		
		$client_info_id=CLIENTID;
	
		//$this->iniwallet($client_info_id);



	
		 $tbl_pre="tp_";
			
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
			
				$search=['main.status'  =>'1','main.isdelete'  =>'0','main.sortid'=>$this->request->param('prosortid'),"upload_time"=>["ELT","now()"],"down_time"=>["EGT","now()"] ] ;
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

   


/*新手产品,包括竞猜和组合*/
public function newie()
	{		
		$client_info_id=CLIENTID;
		$sortid=36;//新手产品 竞猜 使用积分支付
		$tbl_pre="tp_";
		 $sortid = Config::get("conf.newiesortid");//文件名，后面是变量名	
			
			
			
			$sql="select main.* from ".$tbl_pre."product_info   main";
		
			$sql1="select main.id from ".$tbl_pre."product_info   main";

			$sql=$sql." where  main.status=1 and main.isdelete=0 and main.upload_time<now() and main.down_time>now() and main.sortid=$sortid  order by main.create_time desc";
		
			$sql1=$sql1." where  main.status=1 and main.isdelete=0 and main.upload_time<now() and main.down_time>now() and main.sortid=$sortid";

			//
			
			

			$list=Db::query($sql);
			
			


			$sql1="select proid,price from  ".$tbl_pre."product_price_table pricetable where proid in(".$sql1.")   ";
			$sql1=$sql1." and pricetable.custom_tp=0  and pricetable.status=1 and pricetable.isdelete=0 and pricetable.quan=1 and pricetable.bgtime<=now() and pricetable.endtime>=now() ";
			

			$pricelist=Db::query($sql1);
			
			$this->view->assign('pricelist', $pricelist);//价格表
			$rs=[];
			foreach($list as $row)//查询价格表，生成新的产品价格表
			{
				$node['id']=$row['id'];
				$node['title']=$row['title'];
				$node['quan']=$row['quan'];

				$node['oldprice']=$row['price'];
				$newprice=$row['price'];//出售价格
				foreach($pricelist as $pl)//查询价格表
				{
					if($pl['proid']==$row['id'])//比较价格表
					{
						$newprice=$pl['price'];//
						break;
					}
				}
				$node['price']=$newprice;
				
				$rs[]=$node;
			
			}
			$this->view->assign('list', $rs);



           
		 return $this->view->fetch();
	}
public function newiegroup()
	{//组合		
		$client_info_id=CLIENTID;
		$sortid=33;//新手产品不要钱
    	 $tbl_pre="tp_";
		 $sortid = Config::get("conf.newiegroupsortid");//文件名，后面是变量名		
			
			
			
			$sql="select main.* from ".$tbl_pre."product_info   main";
		
			$sql1="select main.id from ".$tbl_pre."product_info   main";

			$sql=$sql." where  main.status=1 and main.isdelete=0 and main.upload_time<now() and main.down_time>now() and main.sortid=$sortid  order by main.create_time desc";
		
			$sql1=$sql1." where  main.status=1 and main.isdelete=0 and main.upload_time<now() and main.down_time>now() and main.sortid=$sortid";

			//
			
	
			$list=Db::query($sql);
			
			


			$sql1="select proid,price from  ".$tbl_pre."product_price_table pricetable where proid in(".$sql1.")   ";
			$sql1=$sql1." and pricetable.custom_tp=0  and pricetable.status=1 and pricetable.isdelete=0 and pricetable.quan=1 and pricetable.bgtime<=now() and pricetable.endtime>=now() ";
			

			$pricelist=Db::query($sql1);
			
			$this->view->assign('pricelist', $pricelist);//价格表
			$rs=[];
			foreach($list as $row)//查询价格表，生成新的产品价格表
			{
				$node['id']=$row['id'];
				$node['title']=$row['title'];
				$node['quan']=$row['quan'];

				$node['oldprice']=$row['price'];
				$newprice=$row['price'];//出售价格
				foreach($pricelist as $pl)//查询价格表
				{
					if($pl['proid']==$row['id'])//比较价格表
					{
						$newprice=$pl['price'];//
						break;
					}
				}
				$node['price']=$newprice;
				
				$rs[]=$node;
			
			}
			$this->view->assign('list', $rs);



           
		 return $this->view->fetch();
	}

//======================================

//=====================================高级交易


public function advancedgroup()
	{//组合		
		$client_info_id=CLIENTID;
		$sortid=34;//组合区
    	 $tbl_pre="tp_";
			
		 $sortid = Config::get("conf.advancedgroupid");//文件名，后面是变量名	
			
			
			
			$sql="select main.* from ".$tbl_pre."product_info   main";
		
			$sql1="select main.id from ".$tbl_pre."product_info   main";

			$sql=$sql." where  main.status=1 and main.isdelete=0 and main.upload_time<now() and main.down_time>now() and main.sortid=$sortid  order by main.create_time desc";
		
			$sql1=$sql1." where  main.status=1 and main.isdelete=0 and main.upload_time<now() and main.down_time>now() and main.sortid=$sortid";

			//
			
	
			$list=Db::query($sql);
			
			


			$sql1="select proid,price from  ".$tbl_pre."product_price_table pricetable where proid in(".$sql1.")   ";
			$sql1=$sql1." and pricetable.custom_tp=0  and pricetable.status=1 and pricetable.isdelete=0 and pricetable.quan=1 and pricetable.bgtime<=now() and pricetable.endtime>=now() ";
			

			$pricelist=Db::query($sql1);
			
			$this->view->assign('pricelist', $pricelist);//价格表
			$rs=[];
			foreach($list as $row)//查询价格表，生成新的产品价格表
			{
				$node['id']=$row['id'];
				$node['title']=$row['title'];
				$node['quan']=$row['quan'];

				$node['oldprice']=$row['price'];
				$newprice=$row['price'];//出售价格
				foreach($pricelist as $pl)//查询价格表
				{
					if($pl['proid']==$row['id'])//比较价格表
					{
						$newprice=$pl['price'];//
						break;
					}
				}
				$node['price']=$newprice;
				
				$rs[]=$node;
			
			}
			$this->view->assign('list', $rs);



           
		 return $this->view->fetch();
	}
	//================================================================================
	//这个是会员区的任务区
	public function advtask()
	{		
		$client_info_id=CLIENTID;
		$sortid=35;//会员区任务表
    	 $tbl_pre="tp_";
		 $sortid = Config::get("conf.advancedprosortid");//文件名，后面是变量名		
		
			$sql="select main.* from ".$tbl_pre."product_info   main";
		
			$sql1="select main.id from ".$tbl_pre."product_info   main";

			$sql=$sql." where  main.status=1 and main.isdelete=0 and main.upload_time<now() and main.down_time>now() and main.sortid=$sortid  order by main.create_time desc";
		
			$sql1=$sql1." where  main.status=1 and main.isdelete=0 and main.upload_time<now() and main.down_time>now() and main.sortid=$sortid";
			//查询产品分类

			$list=Db::query($sql);
			
			


			$sql1="select proid,price from  ".$tbl_pre."product_price_table pricetable where proid in(".$sql1.")   ";
			$sql1=$sql1." and pricetable.custom_tp=0  and pricetable.status=1 and pricetable.isdelete=0 and pricetable.quan=1 and pricetable.bgtime<=now() and pricetable.endtime>=now() ";
			

			$pricelist=Db::query($sql1);
			
			$this->view->assign('pricelist', $pricelist);//价格表
			$rs=[];
			foreach($list as $row)//查询价格表，生成新的产品价格表
			{
				$node['id']=$row['id'];
				$node['title']=$row['title'];
				$node['quan']=$row['quan'];

				$node['oldprice']=$row['price'];
				$newprice=$row['price'];//出售价格
				foreach($pricelist as $pl)//查询价格表
				{
					if($pl['proid']==$row['id'])//比较价格表
					{
						$newprice=$pl['price'];//
						break;
					}
				}
				$node['price']=$newprice;
				
				$rs[]=$node;
			
			}
			$this->view->assign('list', $rs);



           
		 return $this->view->fetch("advanced");
	}
   
}

