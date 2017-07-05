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
// 公开不授权控制器
//-------------------------
//****************************************************

namespace app\portal\controller;

\think\Loader::import('controller/Jump', TRAIT_PATH, EXT);

use think\Loader;
use think\Session;
use think\Db;
use think\Config;
use think\Exception;
use think\View;
use think\Request;

class Pub
{
    use \traits\controller\Jump;

    // 视图类实例
    protected $view;
    // Request实例
    protected $request;

    public function __construct()
    {//初始化，定义OPERID，得到从SESSION，得到值
        if (null === $this->view) {
            $this->view = View::instance(Config::get('template'), Config::get('view_replace_str'));
        }
        if (null === $this->request) {
            $this->request = Request::instance();
        }

        // 用户ID
        defined('CLIENTID') or define('CLIENTID', Session::get(Config::get('rbac.user_auth_key')));
    }

    /**
     * 检查用户是否登录
     */
    protected function checkUser()
    {
        if (null === CLIENTID) {
            if ($this->request->isAjax()) {
                ajax_return_adv_error("登录超时，请先登陆", "", "", "current", url("loginFrame"))->send();
            } else {
                $this->error("登录超时，请先登录", Config::get('rbac.user_auth_gateway'));
            }
        }

        return true;
    }

    /**
     * 用户登录页面
     * @return mixed
     */
    public function login()
    {
        if (Session::has(Config::get('rbac.user_auth_key'))) {
            $this->redirect('Index/index');
        } else {
            return $this->view->fetch();
        }
    }

    /**
     * 小窗口登录页面
     * @return mixed
     */
    public function loginFrame()
    {
        return $this->view->fetch();
    }

    /**
     * 首页
     */
    public function index()
    {
        // 如果通过认证跳转到首页
        $this->redirect("Index/index");
    }

    /**
     * 用户登出
     */
    public function logout()
    {
        if (CLIENTID) {
            Session::clear();
            $this->success('登出成功！', Config::get('rbac.user_auth_gateway'));
        } else {
            $this->error('已经登出！', Config::get('rbac.user_auth_gateway'));
        }
    }

    /**
     * 登录检测
     * @return \think\response\Json
     */
    public function checkLogin()
    {
        if ( $this->request->isAjax() &&$this->request->isPost()) {//
            $data = $this->request->post();
		    $validate = Loader::validate('ClientPassportValidate');
			 
            if (!$validate->scene('login')->check($data)) {
                return ajax_return_adv_error($validate->getError());
            }

            $map['account'] = $data['account'];
            $map['status'] = 1;
            $auth_info = \Rbac::authenticate($map);
			
            // 使用用户名、密码和状态的方式进行认证
            if (null === $auth_info) {
                return ajax_return_adv_error('帐号不存在或已禁用！');
            } else {
                if ($auth_info['password'] != password_hash_tp($data['password'])) {
                    return ajax_return_adv_error('密码错误！');
                }

                // 生成session信息
                Session::set(Config::get('rbac.user_auth_key'), $auth_info['id']);
                Session::set('user_name', $auth_info['account']);
               
                Session::set('last_login_ip', $auth_info['last_login_ip']);
                Session::set('last_login_time', $auth_info['last_login_time']);


				//
				$votp=Db::name("ClientTp")->where(['fid'=> $auth_info['id'],"substatus"=>1,"status"=>1])->find();
				if(count($votp)>0)
				{
					Session::set(Config::get('rbac.user_auth_tp'), $votp['tp']);//开始会员等级,0，没有会员 等级
				}
				else
				{

					Session::set(Config::get('rbac.user_auth_tp'), 0);//开始会员等级,0，没有会员 等级
				}	
				
				


				
                // 保存登录信息
                $update['last_login_time'] = time();
                $update['login_count'] = ['exp', 'login_count+1'];
                $update['last_login_ip'] = $this->request->ip();
                Db::name("ClientPassport")->where('id', $auth_info['id'])->update($update);



				$lst_client_info=Db::name("client_info")->where('fid', $auth_info['id'])->field('id')->find();	//会员信息表
				if(!empty($lst_client_info))
				{//取个人信息，保存到COOKIE
				 Session::set(Config::get('rbac.user_info_key'),$lst_client_info['id']);//
				}

                // 记录登录日志
                $log['uid'] = $auth_info['id'];
                $log['login_ip'] = $this->request->ip();
                $log['login_location'] = implode(" ", \Ip::find($log['login_ip']));
                $log['login_browser'] = \Agent::getBroswer();
                $log['login_os'] = \Agent::getOs();
				$log['tp'] = 2;//会员操作日志
                Db::name("LoginLog")->insert($log);

                // 缓存访问权限
                \Rbac::saveAccessList();

                return ajax_return_adv('登录成功！', '');
            }
        } else {
            throw new Exception("非法请求");
        }
    }

    /**
     * 修改密码
     */
    public function password()
    {
       $this->checkUser();
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 数据校验
            $validate = Loader::validate('ClientPassportValidate');
            if (!$validate->scene('password')->check($data)) {
                return ajax_return_adv_error($validate->getError());
            }

            // 查询旧密码进行比对
            $info = Db::name("ClientPassport")->where("id", CLIENTID)->field("password")->find();
            if ($info['password'] != password_hash_tp($data['oldpassword'])) {
                return ajax_return_adv_error("旧密码错误");
            }
			  // 写入新密码
		   
			   // 写入新密码
            if (false === Loader::model('ClientPassport')->updatePassword(CLIENTID, $data['password'])) {
                return ajax_return_adv_error("密码修改失败");
            }

              
            return ajax_return_adv("密码修改成功", '');
        } else {
            return $this->view->fetch();
        }
    }

    /**
     * 查看用户信息|修改资料|现在修改成实名
     */
    public function profile()
    {
       $this->checkUser();//这检测如果长时间没有操作，跳到LOGIN 界面

        if ($this->request->isPost()) {
            // 修改资料
           
			$data = $this->request->post();
			$data=["nm"=>$data["nm"],"mobile"=>$data["mobile"],"substatus"=>1,"remark"=>$data["remark"]];

			$validate = Loader::validate('ClientInfoValidate');
            if (!$validate->scene('add')->check($data)) {
                return ajax_return_adv_error($validate->getError());
            }

            if (Db::name("ClientInfo")->where("fid", CLIENTID)->where("substatus",0)->update($data) === false) {
                return ajax_return_adv_error("信息修改失败");
            }

            return ajax_return_adv("实名信息申请成功", '');
        } else {
            // 查看用户信息
            $vo = Db::name("ClientInfo")->field('id,nm,mobile,substatus')->where("fid", CLIENTID)->find();
            $this->view->assign('vo', $vo);

            return $this->view->fetch();
        }
    }

	  /**
     * 注册
     * @return mixed
     */
    public function reg()
    {

		$str_reglevel = Config::get("conf.reglevel");//文件名，会员注册初始等级 
		 $str_reginvite = Config::get("conf.reginvite");//文件名，会员注册 是不是邀请
		 $this->view->assign('str_reglevel', $str_reglevel);
		$this->view->assign('str_reginvite', $str_reginvite);
       return $this->view->fetch();

	}
	 /**
     * 注册
     * @return mixed
     */
	public function regsave()
	{
		 $str_reglevel = Config::get("conf.reglevel");//文件名，会员注册初始等级 
		 $str_reginvite = Config::get("conf.reginvite");//文件名，会员注册 是不是邀请
		 $str_regsource=Config::get("conf.regsource");//注册来源
		 //
			if ($this->request->isAjax() && $this->request->isPost()) {

				 $data = $this->request->post();
				$validate = Loader::validate('ClientPassportValidate');
				 
				if (!$validate->scene('reg')->check($data)) {
					return ajax_return_adv_error($validate->getError());
				}
				$parentid=0;
				 if($str_reginvite==1)
				{//邀请码注册
					 $code=$this->request->post("code");
					  $vo = Db::name("invitecode")->where("code", $code)->find();
					  if(count($vo)==0)
						{//如果数据为0，表示 没有邀请码
						return ajax_return_adv_error($validate->getError());
					  }
				 
				 }
				$data=array("account"=> $this->request->post("account1"),"password"=> $this->request->post("password"),"parentid"=>$parentid,"reglevel"=>$str_reglevel,"regsource"=>$str_regsource,"remark"=>"","status"=>"1");
				
				$flag=Loader::model('ClientPassport')->savereg($data);
				//下面开始执行，注册成功的事件

				$pk=Loader::model('ClientPassport')->id;//会员 号

				$this->inipar_regsuccess($pk);//注册成功后，初始化参数
				
				
				return ajax_return_adv_error('注册成功');
			//========================================
			}

		
	
	}

	//这是注册成功后，初始化各个参数
	private function inipar_regsuccess($passportid=0)
	{	 $tbl_pre="tp_";
		//初始化实名
		$uname='uname'.strval($passportid);
		$data=["fid"=>$passportid,"nm"=>$uname,"mobile"=>"","status"=>"1","nickname"=>$uname];
		$flag=Loader::model('ClientInfo')->save($data);
		//初始化钱包
		$arr_wallet=db::table($tbl_pre."admin_ini")->field("id,title")->where(["parenttitle"=>"钱包","status"=>"1","delflag"=>"0"])->order("val desc")->select();
		$data=[];
		foreach($arr_wallet as $wallet)
		{
			$walletid=$wallet['id'];
			$wallettitle=$wallet['title'];
		
				array_push($data,['totalamount'=>0,'cr_operid'=>0,'clientid'=>$passportid,'moneytp'=>$walletid,'remark'=>$wallettitle.'初始化']);
			
		
		}
		$flag=Loader::model('ClientWallet')->iniWalletbyReg($data);	//初始化钱包	

	
	
	}
	// 申请当领导
	public function leader()
	{
	
		 $this->checkUser();//这检测如果长时间没有操作，跳到LOGIN 界面

        if ($this->request->isPost()) {
            // 修改资料
			
			$vo = Db::name("ClientTp")->where(["fid"=> CLIENTID,"status"=>1,"substatus"=>0])->find();
			if(count($vo)==0)
			{//如果数据为0，表示 没有邀请码
				return ajax_return_adv_error("已经申请了");
			 }



			$data = $this->request->post();
			$data=["fid"=>CLIENTID,"substatus"=>0,"tp"=>5];

			
			$flag=Loader::model('ClientTp')->save($data);	//保存
            return ajax_return_adv("信息申请成功", '');
        } else {
            // 查看用户信息
				$vo = Db::name("ClientTp")->where(["fid"=> CLIENTID])->select();
				
				$this->view->assign('list', $vo);	

			 return $this->view->fetch();
        }
	
	
	}

}
