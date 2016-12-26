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

    private $_db = '';

    public function __construct(){
        $this->_db = M('admin');
    }

    public function getUserByUsername($username){
        $condition = array('username'=>$username);
        $res = $this->_db->where($condition)->find();
        return $res;
    }
    
    public function saveUserInfo($condition){
        $res = $this->_db->save($condition);
        return $res;
    }
}

