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

        if(!IS_POST){
            E('页面不存在');
        }

        $username = $_POST['username'];
        $password = $_POST['password'];

        if(!trim($username)){
            print_r($username);
            return show_tip(0,'用户名不能为空!');
        }
        
        if(!trim($password)){
            return show_tip(0,'密码不能为空!');
        }

        $userresult = D('Admin')->getUserByUsername($username);

        if($userresult == ''){
            return show_tip(0,'用户不存在');
        }

        if($userresult['password'] != getMd5Password($password)){
            return show_tip(0,'密码错误');
        }

        //更新数据库参数
        $data = array('id' => $userresult['id'],
                'logintime' => time(),
                'loginip' => get_client_ip(),
            );
        
        //保存session
        session('zzcms_adm_username',$userresult['username']);
        session('zzcms_adm_logintime',$userresult['logintime']);
        session('zzcms_adm_loginip',$userresult['loginip']);
        
        $loginRecord = D('Admin')->saveUserInfo($data);
        if($loginRecord == ''){
            return show_tip(0,'数据库出了问题，请与管理员联系');
        }

        return show_tip(1,'登陆成功',null,U('/index'));
    }

    //退出
    public function logout(){
        session_unset();
        session_destroy();
        $this->redirect('Login/index');
    }
}