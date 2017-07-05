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
// 参数控制器
//-------------------------

namespace app\admin\controller;

\think\Loader::import('controller/Controller', \think\Config::get('traits_path') , EXT);

use app\admin\Controller;
use think\Exception;
use think\Loader;

class AdminIni extends Controller
{
    use \app\admin\traits\controller\Controller;

//    protected static $blacklist = ['delete', 'clear', 'deleteforever', 'recyclebin', 'recycle'];

    protected function filter(&$map)
    {
       

        if ($this->request->param('title')) {
            $map['title'] = ["like", "%" . $this->request->param('title') . "%"];
        }

		 if ($this->request->param('parenttitle')) {
            $map['parenttitle'] = ["like", "%" . $this->request->param('parenttitle') . "%"];
        }
        
    }

    /**
     * 修改密码
     */
  

    
}