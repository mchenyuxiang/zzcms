<?php
/**
 * Created by PhpStorm.
 * User: yuxiang
 * Date: 2016/12/25
 * Time: 11:06
 * Description: 后台菜单管理
 */
namespace Admin\Controller;

use Common\Lib\Category;
use Common\Lib\Page;
use Think\Controller;

class MenuController extends Controller
{

    /**
     *
     */
    public function index()
    {
        $data = array();
        $page = $_REQUEST['p'] ? $_REQUEST['p']:1;
        $pageSize = $_REQUEST['pageSize']?$_REQUEST['pageSize']:15;
        $cate = D('Menu')->getMenu($data,$page,$pageSize);
        $cateCount = D('Menu')->getMenuCount($data);

        $res = new Page($cateCount,$pageSize);
        $pageRes = $res->show();
        if (empty($cate)) {
            $cate = array();
        }
        $cate = \Common\Lib\Category::toLevel($cate, '&nbsp;&nbsp;&nbsp;&nbsp;', 0);

        $this->assign('cate', $cate);
        $this->assign('type', '菜单列表');
        $this->assign('page',$pageRes);
        $this->display();
    }

    public function tree1()
    {
        $this->display();
    }

    public function add()
    {

        if ($_POST) {
            if(!isset($_POST['name']) || !$_POST['name']) {
                return show_tip(0,'菜单名称不能为空');
            }
            if(!isset($_POST['module']) || !$_POST['module']) {
                return show_tip(0,'模块名不能为空');
            }
            if(!isset($_POST['action']) || !$_POST['action']) {
                return show_tip(0,'方法名不能为空');
            }
           
            $menuId = D("Menu")->insert($_POST);
            if($menuId) {
                return show_tip(1,'新增成功',$menuId,U('Menu/index'));
            }
            return show_tip(0,'新增失败',$menuId);


        } else {
            $pid = 0;
            $menuName = D('Menu')->getMenuByParentId($pid);
            $this->assign('menuName',$menuName);
            $this->display();
        }
    }
}

?>

