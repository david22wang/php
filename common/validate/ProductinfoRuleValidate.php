<?php
/**
 * tpAdmin [a web admin based ThinkPHP5]
 *
 * @author yuan1994 <tianpian0805@gmail.com>
 * @link http://tpadmin.yuan1994.com/
 * @copyright 2016 yuan1994 all rights reserved.
 * @license http://www.apache.org/licenses/LICENSE-2.0
 */

namespace app\common\validate;

use think\Validate;

class ProductinfoRuleValidate extends Validate
{
    protected $rule = [
        'title|标题'      => 'require',
  
        
    ];

    protected $scene = [
        'add' => ['title'],
       
    ];
}