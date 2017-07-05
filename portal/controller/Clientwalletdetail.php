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

namespace app\portal\controller;

\think\Loader::import('controller/Controller', \think\Config::get('traits_path') , EXT);

use app\portal\Controller;
use think\Exception;
use think\Loader;
use think\Db;
use think\Session;

use think\Config;


class Clientwalletdetail extends Controller
{
    use \app\portal\traits\controller\Controller;

  //protected static $blacklist = ['delete', 'clear', 'deleteforever', 'recyclebin', 'recycle'];

/*批量新建帐号*/
	
	
    
    protected function filter(&$map)
    {
             
    }

	private function iniwallet($uid)
	{//初始化个人钱包

		$list=Loader::model('AdminIni')->where(['parenttitle'=>'钱包','status'=>'1','delflag'=>'0'])->select();//得到参数表，得到各种帐号
	

		foreach($list as $row)
		{	

			$data=['totalamount'=>0,'moneytp'=>$row['val'],'cr_operid'=>'0','clientid'=>$uid,'remark'=>'初始化','status'=>'1','crdt'=>'2014-3-3','updt'=>'2015-2-2'];
			Loader::model('ClientWallet')->iniWallet($data);
		
		}
	
	
	}

	public function index()
	{		
			
			
			
		 $tbl_pre="tp_";
			
			 $listRows = $this->request->param('numPerPage') ?: 10;


			$search=['main.clientid'  =>CLIENTID,'main.money_tp' =>$this->request->param('id')  ] ;
		
			if ($this->request->param('account')) {
				//$search=['main.account'  =>  ['like','%'.$this->request->param('account').'%']] ;
			}
			
				
				

			 // 接受 sort参数 0 表示倒序 非0都 表示正序
			

				
				
			
				$list=db::table($tbl_pre."clientwallet_detail")
				->alias(['main'=>$tbl_pre."clientwallet_detail",'ini'=>$tbl_pre."admin_ini"])
				->field("main.*,ini.title,ini.val")	
				->join($tbl_pre."admin_ini","main.money_tp = ini.id","LEFT")
			
				->where($search)
				->order('id desc')
				//->select()
				->paginate($listRows, false, ['query' => $this->request->get()]);
				
				
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