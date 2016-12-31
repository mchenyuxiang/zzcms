<?php
/**
 * Created by PhpStorm.
 * User: yuxiang
 * Date: 2017/1/1
 * Time: 1:39
 * Description: 基类
 */
namespace Common\Controller;

use Think\Controller;

class BaseController extends Controller{
    public function index(){
        
        $this->display();
    }
}
?>

