<?php
/**
 * Created by PhpStorm.
 * User: yuxiang
 * Date: 2017/1/5
 * Time: 12:17
 * Description: 部门controller
 */
namespace Admin\Controller;

use Common\Lib\Category;
use Think\Controller;

class DepartmentController extends Controller
{
    public function departmentList()
    {
        $listDepartment = M('department')->select();
        $listDepartment = \Common\Lib\Category::toLevel($listDepartment, '&nbsp;&nbsp;&nbsp;&nbsp;', 0);
//        print_r($listDepartment);



        $arrList = array();
        foreach ($listDepartment as $k=>$v) {
            $id = $v['id'];
            $pid = $v['pid'];
            $name = $v['name'];
            $addurl = U("add",array('pid' => $v['id']));
            $editurl = U('edit',array('id' => $v['id']));
            if($v['level'] == 1){
                $arrList[$k] = " <tr class='treegrid-{$id}'>
                                <td>{$name}</td>
                                <td>Additional info</td>
                                <td>
                                    <a href='{$addurl}' class='btn btn-primary btn-rounded'>增加下属部门</a>
                                    <a href='{$editurl}' class='btn btn-primary btn-rounded'>编辑部门</a>
                                    <a href=\"javascript:void(0)\" attr-message=\"删除\" id=\"zzcms-delete\" class='btn btn-primary btn-rounded' attr-id=\"{$v['id']}\">删除部门</a>
                                </td>
                            </tr>";
            }else{
                $arrList[$k] = " <tr class='treegrid-{$id} treegrid-parent-{$pid}'>
                                <td>{$name}</td>
                                <td>Additional info</td>
                                <td>
                                    <a href='{$addurl}' class='btn btn-primary btn-rounded'>增加下属部门</a>
                                    <a href='{$editurl}' class='btn btn-primary btn-rounded'>编辑部门</a>
                                    <a href=\"javascript:void(0)\" attr-message=\"删除\" id=\"zzcms-delete\" class='btn btn-primary btn-rounded' attr-id=\"{$v['id']}\">删除部门</a>
                                </td>
                            </tr>";
            }
        }
        $this->assign("listDepartment", $listDepartment);
        $this->assign("arrList",$arrList);
        $this->assign("type", "部门管理");
        $this->display();
    }

    public function getDepartment()
    {
        $result = array(
            text => "ceshi4",
            nodes => array(),
        );
        exit(json_encode($result));
    }

    public function add()
    {

        if($_POST){

            if (!isset($_POST['name']) || !$_POST['name']) {
                return show_tip(0, '部门名称不能为空');
            }

            $menuId = M("Department")->data($_POST)->add();
            if ($menuId) {
                return show_tip(1, '新增成功', $menuId, U('departmentList'));
            }
            return show_tip(0, '新增失败', $menuId);
        }
        else{
            $departmentName = M('department')->select();
            $departmentName = Category::toLevel($departmentName, '---', 0);
            $this->assign('cate', $departmentName);
            $this->display();
        }
    }

    public function del()
    {

        $id = I('id', 0, 'intval');

        //查询是否有子类
        $childCate = M('department')->where(array('pid' => $id))->select();
        if ($childCate) {
//            return show_tip(0,"删除失败，请先删除子菜单");
            return show_tip(0, $id);
        }
        if (M('department')->delete($id)) {

            return show_tip(1, '删除成功', null, U('departmentList'));
        } else {
            return show_tip(0, "删除失败");
        }
    }

    public function edit()
    {
        if (IS_POST) {

            $this->editPost();
            exit();
        } else {
            $id = I('id', 0, 'intval');
            $data = M('department')->find($id);
            if (!$data) {
                $this->error("记录不存在");
            }
            $departmentName = M('department')->select();
            $departmentName = Category::toLevel($departmentName, '---', 0);
            $this->assign('data', $data);
            $this->assign('cate', $departmentName);
            $this->assign('type', '修改部门');
            $this->display();
        }

    }

    public function editPost()
    {
        $data = I('post.', '');
        $id = $data['id'] = intval($data['id']);

        $data['name'] = trim($data['name']);
        $pid = $data['pid'] = intval($data['pid']);

        //M验证
        if (empty($data['name'])) {
            return show_tip(0,"菜单名称不能为空");
        }

        if ($id == $pid) {
            return show_tip(0,"不能设置自己为子部门");
        }

        if (false !== M('department')->save($data)) {

            return show_tip(1,'修改成功',null,U('departmentList'));
        } else {
            return show_tip(0,'修改失败');
        }
    }
    public function departmentTest()
    {
        $this->display();
    }
}

?>

