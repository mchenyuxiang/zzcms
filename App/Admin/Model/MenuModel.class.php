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
    
    public function getMenuByParentId($parentId){
        if($parentId == -1){
            $res = $this->_db->select(); 
        }else{
            $condition = array('pid'=>$parentId);
            $res = $this->_db->where($condition)->select();
        }
        return $res;
    }

    public function insert($data = array()){
        if(!$data || !is_array($data)){
            return 0;
        }
        return $this->_db->add($data);
    }

    public function getMenu(){
        $cate = $this->_db->order('sort,id')->select();
        return $cate;
    }
}

?>

