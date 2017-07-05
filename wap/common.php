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
// 公共函数
//-------------------------
/**
 * 统一密码加密方式，如需变动直接修改此处
 * @param $password
 * @return string
 */
function password_hash_tp($password)
{
    return hash("md5", trim($password));
}
/**
 * 显示产品竞猜结果状态
 * @param int $status     0|1|-1
 * @param bool $imageShow true只显示图标|false只显示文字
 * @return string
 */
function get_win_status($status, $imageShow = false)
{
    switch ($status) {
        case 0 :
            $showText = '末开';
          
            break;
        case 1 :
            $showText = '赢';
            
            break;
		case 2 :
            $showText = '输';
            
            break;
       
        default :
            $showText = '末开';
         

    }

    return ($imageShow === true) ? $showImg : $showText;
}

/**
 * 显示产品竞猜提取记录状态
 * @param int $status     0|1|-1
 * @param bool $imageShow true只显示图标|false只显示文字
 * @return string
 */
function get_deposit_status($status, $imageShow = false)
{
    switch ($status) {
        case 0 :
            $showText = '末提取';
          
            break;
        case 1 :
            $showText = '已提取';
            
            break;
	
        case 0 :
        default :
            $showText = '末提取';
         

    }

    return ($imageShow === true) ? $showImg : $showText;
}
