<?php
/**
 * Created by PhpStorm.
 * User: yuxiang
 * Date: 2017/2/7
 * Time: 19:41
 */
namespace Admin\Controller;

use Think\Controller;

class SeoWebAdminController extends Controller {

    /**
     * 网站添加
     */
    public function add(){
        $this->display();
    }

    /**
     * 网站管理
     */
    public function ListInfo(){
        $this->display();
    }
}