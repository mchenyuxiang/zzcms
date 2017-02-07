<?php
/**
 * Created by PhpStorm.
 * User: yuxiang
 * Date: 2017/2/7
 * Time: 19:37
 */
namespace Admin\Controller;

use Think\Controller;

class SeoWebKeyAdmin extends Controller {

    /**
     * 添加关键词
     */
    public function add(){
        $this->display();
    }

    /**
     * 关键词管理
     */
    public function ListInfo(){
        $this->display();
    }

    /**
     * 扣费记录详情
     */
    public function CostDetail(){
        $this->display();
    }
}