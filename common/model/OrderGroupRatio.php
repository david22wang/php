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
// 产品组合返利表模型
//-------------------------

namespace app\common\model;

use think\Model;

class OrderGroupRatio extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'datetime';

    //自动完成
 

 


/*
自定义自己增加新增

**/
	 public function save($data = [], $where = [], $sequence = null)
    {
		
		return(parent::save($data));
	}

}