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

class ProductinfoPromotion extends Controller
{
    use \app\desktop\traits\controller\Controller;

    //protected static $blacklist = ['delete', 'clear', 'deleteforever', 'recyclebin', 'recycle'];

/*批量新建帐号*/
	/*新建帐号*/
	
/*新建帐号*/
	public function edit()
    {
				
		if ( $this->request->isPost()) {//$this->request->isAjax() &&
			//处理保存
			
						$data = $this->request->post();
						
						
						
						$validate = Loader::validate('ProductinfoRuleValidate');
						 
						if (!$validate->scene('add')->check($data)) {
							return ajax_return_adv_error($validate->getError());
						}

						
						
						$data=['id' => $data['id'],"remark"=>$data["remark"],"proid"=>$data["proid"],"tp"=>$data["customtp"],"operid"=>OPERID,"status"=>$data["status"],"ruletitle"=>$data["ruletitle"],"title"=>$data["title"],"precondition"=>$data["precondition"],"preconditiontag"=>$data["preconditiontag"],"dis_result"=>$data["dis_result"],"dis_resulttag"=>$data["dis_resulttag"],"giftid"=>$data["giftid"],"subtp"=>0,"dis_resultoption"=>$data["dis_resultoption"],"bgtime"=>$data["bgtime"],"endtime"=>$data["endtime"],];
							
						
						
						$flag=Loader::model('ProductinfoPromotion')->isUpdate(true)->save($data, ['id' => $data['id']]);
						//下面是更新价格表


						//生成新的价格表
								 $proid=$data["proid"];//产品号
								 $propromotionid=$data["id"];//产品促销号
								 $pro_info=Db::name("product_info")->field("price")->where(["id"=>$proid])->find();
								    
									$oldprice=(float)$pro_info['price'];//产品的原价
									$newprice=$oldprice;
									
									$dis_resulttag=$data['dis_resulttag'];//<option value="1">比率<option value="2">直减<option value="3">礼品
									
									$precondition=$data['precondition'];//前提条件数据

									$dis_result=(float)$data['dis_result'];
									$remark='';//备注
									if($dis_resulttag==1)
									{//百分比
										$newprice=$oldprice*(1+$dis_result);
										$remark='单价直减'.strval($dis_result).'金额'.strval($oldprice*($dis_result));
									
									}
									if($dis_resulttag==2)
									{//直减
										$newprice=$oldprice+$dis_result;
										$remark='单价直减'.strval($dis_result).'金额';
									
									
									}
								//生成数组
								$data=["proid"=>$proid,"propromotionid"=>$propromotionid,"oldprice"=>$oldprice,"price"=>$newprice,"quan"=>$precondition,"bgtime"=>$data["bgtime"],"endtime"=>$data["endtime"],"remark"=>$remark,"custom_tp"=>$data["tp"],"operid"=>OPERID,"status"=>$data["status"]];
								
								$flag=Db::name("ProductPriceTable")->where('propromotionid', $propromotionid)->update(['status'=>0,'isdelete'=>1]);
								
								
								$flag=Loader::model('ProductPriceTable')->save($data);	//重新生成一条记录表

						
					
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

								$list=db::table($tbl_pre."productinfo_Promotion")->field("*")
								->where(['id'=>$this->request->param('id')])
								
								->find();
							
								
								 $this->view->assign('vo', $list);

								 $custom=db::table($tbl_pre."admin_ini")->field("id,title")->where(["status"=>"1","parenttitle"=>"会员等级"])->select();
			     			 
								 $this->view->assign('custom', $custom);//会员类型

								


								return $this->view->fetch("edittime");


							
							    
		}
				
			
	}



/*新建产品限制*/
	public function add()
    { $tbl_pre="tp_";
				
		if ($this->request->isAjax() && $this->request->isPost()) {//
			//处理保存
			
						$data = $this->request->post();
						
						if($data["promo_tp"]=="quan")
						{//数量优惠开始
				
								$validate = Loader::validate('ProductinfoPromotionValidate');
								 
								if (!$validate->scene('add')->check($data)) {
									return ajax_return_adv_error($validate->getError());
								}
								$data1=["remark"=>$data["remark"],"proid"=>$data["proid"],"tp"=>$data["customtp"],"operid"=>OPERID,"status"=>$data["status"],"ruletitle"=>$data["ruletitle"],"title"=>$data["title"],"precondition"=>$data["precondition"],"preconditiontag"=>$data["preconditiontag"],"dis_result"=>$data["dis_result"],"dis_resulttag"=>$data["dis_resulttag"],"giftid"=>$data["giftid"],"subtp"=>0,"dis_resultoption"=>$data["dis_resultoption"]];
								
								
								$flag=Loader::model('ProductinfoPromotion')->save($data1);	
								$proinfoproid=Loader::model('ProductinfoPromotion')->id;
								$proid=$data["proid"];
						
								//生成新的价格表
								 
								 $pro_info=Db::name("product_info")->field("price")->where(["id"=>$proid])->find();
								
								

								if($flag==1)
								{
									return ajax_return_adv('创建成功！', '');
									
								}
								else
								{
									 return ajax_return_adv_error("创建失败");
								}
								//数量优惠
						}
						if($data["promo_tp"]=="time")
						{//时间优惠开始
				
								$validate = Loader::validate('ProductinfoPromotionValidate');
								 
								if (!$validate->scene('add')->check($data)) {
									return ajax_return_adv_error($validate->getError());
								}
								$data1=["remark"=>$data["remark"],"proid"=>$data["proid"],"tp"=>$data["customtp"],"operid"=>OPERID,"status"=>$data["status"],"ruletitle"=>$data["ruletitle"],"title"=>$data["title"],"precondition"=>$data["precondition"],"preconditiontag"=>$data["preconditiontag"],"dis_result"=>$data["dis_result"],"dis_resulttag"=>$data["dis_resulttag"],"giftid"=>$data["giftid"],"subtp"=>1,"bgtime"=>$data["bgtime"],"endtime"=>$data["endtime"],"dis_resultoption"=>$data["dis_resultoption"]];
								
								
								$flag=Loader::model('ProductinfoPromotion')->save($data1);	
								
									
								$proinfoproid=Loader::model('ProductinfoPromotion')->id;
								$proid=$data["proid"];
								
						
								//生成新的价格表
								 
								 $pro_info=Db::name("product_info")->field("price")->where(["id"=>$proid])->find();
								    
									$oldprice=(float)$pro_info['price'];//产品的原价
									$newprice=$oldprice;
									
									$dis_resulttag=$data['dis_resulttag'];//<option value="1">比率<option value="2">直减<option value="3">礼品
									
									$precondition=$data['precondition'];//前提条件数据

									$dis_result=(float)$data['dis_result'];
									$remark='';//备注
									if($dis_resulttag==1)
									{//百分比
										$newprice=$oldprice*(1+$dis_result);
										$remark='单价直减'.strval($dis_result).'金额'.strval($oldprice*($dis_result));
									
									}
									if($dis_resulttag==2)
									{//直减
										$newprice=$oldprice+$dis_result;
										$remark='单价直减'.strval($dis_result).'金额';
									
									
									}
								//生成数组
								$data=["proid"=>$proid,"propromotionid"=>$proinfoproid,"oldprice"=>$oldprice,"price"=>$newprice,"quan"=>$precondition,"bgtime"=>$data["bgtime"],"endtime"=>$data["endtime"],"remark"=>$remark,"custom_tp"=>$data["customtp"],"operid"=>OPERID,"status"=>$data["status"]];



							
								$flag=Loader::model('ProductPriceTable')->save($data);	
								
									

								if($flag==1)
								{
									return ajax_return_adv('创建成功！', '');
									
								}
								else
								{
									 return ajax_return_adv_error("创建失败");
								}
								//数量优惠
						}
					

						
						
			
				
			}
		else
		{
							//下面是会员等级
							
								$custom=db::table($tbl_pre."admin_ini")->field("id,title")->where(["status"=>"1","parenttitle"=>"会员等级"])->select();
			     			 
								 $this->view->assign('custom', $custom);//会员类型

								 $data = $this->request->param();
								 $tp=$data["tp"];
								
								  if($tp==1)
								{
									return $this->view->fetch("add");
								 }
								  if($tp==2)
								{
									return $this->view->fetch("addtime");
								 }

							
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
					
			
				$list=db::table($tbl_pre."productinfo_promotion")
				->alias(['main'=>$tbl_pre."productinfo_promotion",'oper'=>$tbl_pre.'desktop_user'])
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
					
				$list=db::table($tbl_pre."productinfo_promotion")
				->alias(['main'=>$tbl_pre."productinfo_promotion",'oper'=>$tbl_pre.'desktop_user'])
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