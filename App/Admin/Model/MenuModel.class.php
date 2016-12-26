<?php
/**
 * Created by PhpStorm.
 * User: yuxiang
 * Date: 2016/12/26
 * Time: 13:16
 * Description: menu菜单实例
 */
namespace Admin\Model;

use Think\Model;

class MenuModel extends Model{

    private $_db = '';

    public function __construct(){
       $this->_db = M('menu');
    }
}

?>

