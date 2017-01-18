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
        $page = $_REQUEST['p'] ? $_REQUEST['p'] : 1;
        $pageSize = $_REQUEST['pageSize'] ? $_REQUEST['pageSize'] : 15;
        $cate = D('Menu')->getMenu($data, $page, $pageSize);
        $cateCount = D('Menu')->getMenuCount($data);

        $res = new Page($cateCount, $pageSize);
        $pageRes = $res->show();
        if (empty($cate)) {
            $cate = array();
        }
        $cate = \Common\Lib\Category::toLevel($cate, '&nbsp;&nbsp;&nbsp;&nbsp;', 0);

        $this->assign('cate', $cate);
        $this->assign('type', '菜单列表');
        $this->assign('page', $pageRes);
        $this->display();
    }

    public function tree1()
    {
        $this->display();
    }

    public function add()
    {

        if ($_POST) {
            if (!isset($_POST['name']) || !$_POST['name']) {
                return show_tip(0, '菜单名称不能为空');
            }
            if (!isset($_POST['module']) || !$_POST['module']) {
                return show_tip(0, '模块名不能为空');
            }
            if (!isset($_POST['action']) || !$_POST['action']) {
                return show_tip(0, '方法名不能为空');
            }
            $_POST['topid'] = $_POST['topid'];

            $menuId = D("Menu")->insert($_POST);
            if ($menuId) {
                return show_tip(1, '新增成功', $menuId, U('Menu/index'));
            }
            return show_tip(0, '新增失败', $menuId);


        } else {
            $pid = I('pid', 0, 'intval');
            $topid = I('topid');
            $menuName = M('menu')->order('sort')->select();
            $menuName = Category::toLevel($menuName, '---', 0);
            if($pid != 0 && $topid == 0){
                $topid = $pid;
            }
            $this->assign('cate', $menuName);
            $this->assign('pid', $pid);
            $this->assign('topid', $topid);
            $this->assign('type', '菜单添加');
            $this->display();
        }
    }

    public function edit()
    {
        if (IS_POST) {

            $this->editPost();
            exit();
        } else {
            $id = I('id', 0, 'intval');
            $data = M('menu')->find($id);
            if (!$data) {
                $this->error("记录不存在");
            }
            $menuName = M('menu')->order('sort')->select();
            $menuName = Category::toLevel($menuName, '---', 0);
            $this->assign('data', $data);
            $this->assign('cate', $menuName);
            $this->assign('type', '修改菜单');
            $this->display();
        }

    }

    public function editPost()
    {
        $data = I('post.', '');
        $id = $data['id'] = intval($data['id']);

        $data['name'] = trim($data['name']);
        $pid = $data['pid'] = intval($data['pid']);
        $data['module'] = ucfirst($data['module']);
        $data['parameter'] = I('parameter', '', '');

        //M验证
        if (empty($data['name'])) {
            return show_tip(0,"菜单名称不能为空");
        }

        if ($id == $pid) {
            return show_tip(0,"不能设置自己为子菜单");
        }

        if (false !== M('menu')->save($data)) {

            return show_tip(1,'修改成功',null,U('Menu/index'));
        } else {
            return show_tip(0,'修改失败');
        }
    }

    public function del()
    {

        $id = I('id', 0, 'intval');

        //查询是否有子类
        $childCate = M('menu')->where(array('pid' => $id))->select();
        if ($childCate) {
//            return show_tip(0,"删除失败，请先删除子菜单");
            return show_tip(0,$id);
        }
        if (M('menu')->delete($id)) {

            return show_tip(1,'删除成功',null,U('Menu/index'));
        } else {
            return show_tip(0,"删除失败");
        }
    }
}

?>

