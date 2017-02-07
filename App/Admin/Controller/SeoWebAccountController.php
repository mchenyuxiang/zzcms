<?php
/**
 * Created by PhpStorm.
 * User: yuxiang
 * Date: 2017/2/7
 * Time: 19:34
 */
namespace Admin\Controller;

use Think\Controller;

class SeoWebAccount extends Controller {

    /**
     * 账户资料
     */
    public function update(){
        $this->display();
    }

    /**
     * 修改密码
     */
    public function UpdateSecrect(){
        $this->display();
    }
}