<?php
/**
 * Created by PhpStorm.
 * User: yuxiang
 * Date: 2017/2/7
 * Time: 19:37
 */
namespace Admin\Controller;


class SeoWebKeyAdminController extends CommonController  {
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
    }

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