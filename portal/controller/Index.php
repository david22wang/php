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
// 管理后台首页
//-------------------------

namespace app\portal\controller;

use app\portal\Controller;
use think\Loader;
use think\Session;
use think\Db;
use think\Config;

class Index extends Controller
{

    public function index()
    {//会员通过会员等级来控制
     
	   $prefix = Config::get("conf.sex");//文件名，后面是变量名
		$tp=Session::get(Config::get('rbac.user_auth_tp'));
		

       
		 /**/
		$menu=[];
		$groups = [];

        $this->view->assign('groups', $groups);
        $this->view->assign('menu', $menu);

       return $this->view->fetch();
		
    }

    /**
     * 欢迎页
     * @return mixed
     */
    public function welcome()
    {
		$tp=Session::get(Config::get('rbac.user_auth_tp'));
		
        // 查询 ip 地址和登录地点
        if (Session::get('last_login_time')) {
            $last_login_ip = Session::get('last_login_ip');
            $last_login_loc = \Ip::find($last_login_ip);

            $this->view->assign("last_login_ip", $last_login_ip);
            $this->view->assign("last_login_loc", implode(" ", $last_login_loc));

        }
        $current_login_ip = $this->request->ip();
        $current_login_loc = \Ip::find($current_login_ip);

        $this->view->assign("current_login_ip", $current_login_ip);
        $this->view->assign("current_login_loc", implode(" ", $current_login_loc));

        // 查询个人信息
        $info = Db::name("ClientPassport")->where("id", CLIENTID)->find();
        $this->view->assign("info", $info);

        return $this->view->fetch();
    }
}