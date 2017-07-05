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
// 用户控制器-钱包明细
//-------------------------

namespace app\desktop\controller;

\think\Loader::import('controller/Controller', \think\Config::get('traits_path') , EXT);

use app\desktop\Controller;
use think\Exception;
use think\Loader;
use think\Db;
class ClientwalletDetail extends Controller
{
    use \app\desktop\traits\controller\Controller;

  //protected static $blacklist = ['delete', 'clear', 'deleteforever', 'recyclebin', 'recycle'];

/*批量新建帐号*/
	
	/*新建帐号*/
	public function add()
    {
				
		if ($this->request->isAjax() && $this->request->isPost()) {//
			//处理保存
			
						$data = $this->request->post();
						
						$amount=(float)$data["amount"];
						if($amount>=0)
						{//收入，增加
							
							$csum = '';
							$client_info_id=$data['clientid'];
							$moneytp = $data['montytp'];
							$remark = $data['remark'];
							$fid=0;
							$data=['operid'=>OPERID,'amount'=>$amount,'money_tp'=>$moneytp,'clientid'=>$client_info_id,'fid'=>$fid,'status'=>'0','totalamount'=>'0','trade_type'=>'1','walletid'=>0,'remark'=>$remark];
							$walletid=3;
							$flag=Loader::model('ClientwalletDetail')->updateWallet_deposit($data,$walletid);//收入
							
						
						
						}
						else
						{//
							$amount=abs($amount);//
							$client_info_id=$data['clientid'];
							$moneytp = $data['montytp'];
							$remark = $data['remark'];
							$csum = '';
							$fid=0;
							$data=['operid'=>OPERID,'remark'=>$remark,'amount'=>$amount,'money_tp'=>$moneytp,'clientid'=>$client_info_id,'fid'=>$fid,'status'=>'0','totalamount'=>'0','trade_type'=>'-1','walletid'=>0];
							$walletid=3;
							$flag=Loader::model('ClientwalletDetail')->updateWallet_paybyoper($data,$walletid);//收入
		
						
						}

						if($flag==1)
						{
							return ajax_return_adv('调整成功！', '');
							
						}
						else
						{
							 return ajax_return_adv_error("调整失败");
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
				if(isset($data['montytp']))
				{
					$montytp=$data['montytp'];
				}
				else
				{
					$montytp=0;

				}
				 $this->view->assign('clientid', $fid);
				 $this->view->assign('montytp', $montytp);


				 

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
      
		 if ($this->request->param('fid')) {
            $map['fid'] =  $this->request->param('fid') ;
        }
       
       
       


      
    }

	public function index2()
	{	//这个是根据列出钱包，消费明细
		 $tbl_pre="tp_";
		
		$fid=$this->request->param('fid');
		$walletinfo=db::table($tbl_pre."client_wallet")->where(["id"=>$fid])->find();
	
		$moneytp=$walletinfo["moneytp"];//钱包
		$clientid=$walletinfo["clientid"];//帐户号
		 $listRows = $this->request->param('numPerPage') ?: 10;
				
				$search=['main.clientid'=>$clientid,'main.money_tp'=>$moneytp] ;      

			
			 if ($this->request->param('nm')) {
				
			}
			if ($this->request->param('nm')) {
				//$search=array_merge($search=['clientinfo.nm'  =>  ['like','%'.$this->request->param('nm').'%']]); ;
			}
				

			 // 接受 sort参数 0 表示倒序 非0都 表示正序
			

				if($this->request->param()!=null)
				{
					$order=($this->request->param('_order'));
					$sort=($this->request->param('_sort'));
					$order_by = $order ? "{$order} {$sort}" : false;
					


			$list=db::table($tbl_pre."clientwallet_detail")
				->alias(['main'=>$tbl_pre."clientwallet_detail",'ini'=>$tbl_pre."admin_ini",'cp'=>$tbl_pre."client_passport","oper"=>$tbl_pre."desktop_user","clientinfo"=>$tbl_pre."client_info"])

				->field("main.*,ini.title,ini.val,cp.account,oper.realname")	
				->join($tbl_pre."admin_ini","main.money_tp = ini.id","LEFT")
	
				->join($tbl_pre."client_passport","main.clientid = cp.id","LEFT")
			

				->join($tbl_pre."desktop_user","main.operid = oper.id","LEFT")

				

				->where($search)
				->order($order_by)
				//->select()
				->paginate($listRows, false, ['query' => $this->request->get()]);
				
				}
				else
				{
				//这个不要排序
					
			
			
			$list=db::table($tbl_pre."clientwallet_detail")
				->alias(['main'=>$tbl_pre."clientwallet_detail",'ini'=>$tbl_pre."admin_ini",'cp'=>$tbl_pre."client_passport","oper"=>$tbl_pre."desktop_user","clientinfo"=>$tbl_pre."client_info"])

				->field("main.*,ini.title,ini.val,cp.account,oper.realname")	
				->join($tbl_pre."admin_ini","main.money_tp = ini.id","LEFT")
	
				->join($tbl_pre."client_passport","main.clientid = cp.id","LEFT")
			

				->join($tbl_pre."desktop_user","main.operid = oper.id","LEFT")

				

				->where($search)
				//->order($order_by)
				//->select()
				->paginate($listRows, false, ['query' => $this->request->get()]);
		
					


				}
				// 把分页数据赋值给模板变量list
		
				

		
				$page = $list->render();
				// 模板变量赋值

					
				$this->view->assign('walletinfo', $walletinfo);//
				 $this->view->assign('list', $list);
                $this->view->assign("count", $list->total());
                $this->view->assign("page", $list->render());
                $this->view->assign('numPerPage', $list->listRows());

		 return $this->view->fetch();
		
	}

	public function index()
	{//钱包明细	
		
		
		 $tbl_pre="tp_";
			
		 $listRows = $this->request->param('numPerPage') ?: 10;
		  $moneytp=db::table($tbl_pre."admin_ini")->where(['parenttitle'=>'钱包'])->select();
			
				
		//查询
				$search=[] ;      

			
			 if ($this->request->param('moneytp')) {
				 if(strlen(trim($this->request->param('moneytp')))>0)
				 {
					$search=['main.money_tp'  =>  $this->request->param('moneytp')] ;
				 }

				
			}
			 if ($this->request->param('account')) {//会员号
				 if(strlen(trim($this->request->param('account')))>0)
				 {
					$search=['main.clientid'  =>  $this->request->param('account')] ;
				 }

				
			}
				
			if ($this->request->param('status')<>'') {
				
					$search=array_merge($search,['main.status'  =>  $this->request->param('status')]);
				
				
			}
			
			
			
			 // 接受 sort参数 0 表示倒序 非0都 表示正序
			

				if($this->request->param()!=null)
				{
					$order=($this->request->param('_order'));
					$sort=($this->request->param('_sort'));
					$order_by = $order ? "{$order} {$sort}" : false;
					


			$list=db::table($tbl_pre."clientwallet_detail")
				->alias(['main'=>$tbl_pre."clientwallet_detail",'ini'=>$tbl_pre."admin_ini"])
				->field("main.*,ini.title,ini.val")	
				->join($tbl_pre."admin_ini","main.money_tp = ini.id","LEFT")
	
				

				->where($search)
				->order($order_by)
				//->select()
				->paginate($listRows, false, ['query' => $this->request->get()]);
				
				}
				else
				{
				//这个不要排序
					
			
			$list=db::table($tbl_pre."clientwallet_detail")
				->alias(['main'=>$tbl_pre."clientwallet_detail",'ini'=>$tbl_pre."admin_ini"])
				->field("main.*,ini.title,ini.val")	
				->join($tbl_pre."admin_ini","main.money_tp = ini.id","LEFT")

				->where($search)
				//->order($order_by)
			
				//->select()
				->paginate($listRows, false, ['query' => $this->request->get()]);
		
					


				}
				// 把分页数据赋值给模板变量list
		
		
				$page = $list->render();
				// 模板变量赋值

					//dump($list);
			$this->view->assign('moneytp', $moneytp);
				 $this->view->assign('list', $list);
                $this->view->assign("count", $list->total());
                $this->view->assign("page", $list->render());
                $this->view->assign('numPerPage', $list->listRows());

		 return $this->view->fetch();
	}

    /**
     * 修改密码
     */
  
	

   
}