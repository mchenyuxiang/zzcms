<?php
/**
 * User: mchenyuxiang
 * CreateTime: 2016/12/23 18:00
 * Time: 18:00
 * Description:
 */
namespace Admin\Controller;

use Think\Controller;

class LoginController extends Controller{
    public function index(){
        $this->display();
    }

    //登录验证
    public function login(){
        print_r($_POST);
    }

    //退出
    public function logout(){
        session_unset();
        session_destroy();
        $this->redirect('Login/index');
    }
}