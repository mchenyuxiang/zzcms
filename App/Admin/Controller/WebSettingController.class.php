<?php
/**
 * Created by PhpStorm.
 * User: yuxiang
 * Date: 2016/12/31
 * Time: 22:40
 * Description: 网站设置
 */
namespace Admin\Controller;

use Common\Lib\Category;
use Think\Controller;

class WebSettingController extends Controller{

    public function index()
    {
        $pid = I('get.id','','htmlspecialchars');
        if(empty($pid)){
            $pid = 0;
        }
        $condition['topid'] = $pid;
        $condition['status'] = 1;
        $menu = M('menu')->where(array('status' => 1))->order('sort,id')->select();
        if (empty($menu)) {
            $menu = array();
        }
        foreach ($menu as $k => $v) {
            $menu_c[] = $v;
        }
        $submenu = M('menu')->where($condition)->order('sort,id')->select();
        foreach ($submenu as $k => $v) {
            $submenu_c[] = $v;
        }
        $this->assign('pid',$pid);
        $this->assign('submenu',Category::toLayer($submenu_c,'child',$pid));
        $this->assign('menu', Category::toLayer($menu_c));
        $this->display();
    }
}
?>

