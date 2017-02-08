<?php
/**
 * Created by PhpStorm.
 * User: yuxiang
 * Date: 2017/2/8
 * Time: 10:50
 */
namespace Admin\Controller;

use Think\Controller;

class CommonController extends Controller {
    public function _initialize(){

        //判断用户是否已经登录
        if (!isset($_SESSION['zzcms_adm_username'])) {
            $this->error('请先登录', U('Login/index'), 1);
        }
    }

}