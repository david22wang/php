<?php
namespace app\index\controller;

class Index
{
    public function index()
    {
       //  return \think\Response::create(\think\Url::build('/admin'), 'redirect');
		//return \think\Response::create(\think\Url::build('/desktop'), 'redirect');
		return \think\Response::create(\think\Url::build('/portal'), 'redirect');


	
    //连接本地的 Redis 服务
	 
	 
    }
}
