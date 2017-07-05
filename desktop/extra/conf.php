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
// 自定义配置信息
//-------------------------

return [
    // 性别配置
    'sex' => [
        0 => '未填写',
        1 => '男' ,
        2 => '女'
    ],
    // 节点类型
    'node_type' => [
        0 => '方法',
        1 => '控制器'
    ],
    'select' => [
        1 => '值一',
        2 => '值二',
        3 => '值三',
        4 => '值四',
    ]	,
	'regsource'=>[
  0 => '测试创建',
  1 => '员工创建',
   2 => '网站注册',

  
],
'reglevel'=>'0000',//注册会员级别
'reginvite'=>'0',//1表示 邀请码注册，0开放注册
'regsource'=>'1',//会员注册来源



];
