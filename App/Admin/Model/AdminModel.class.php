<?php
/**
 * Created by PhpStorm.
 * User: yuxiang
 * Date: 2016/12/24
 * Time: 16:55
 * Description:
 */
namespace Admin\Model;

use Think\Model;

class AdminModel extends Model{

    private $_dbObj = '';

    public function __construct(){
        $this->_dbObj = M('admin');
    }

    public function getUserByUsername($username){
        $condition = array('username'=>$username);
        $res = $this->_dbObj->where($condition)->find();
        return $res;
    }
}

