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
            $addurl = U("Menu/add",array('pid' => $v['id']));
            $editurl = U('edit',array('id' => $v['id']));
            if($v['level'] == 1){
                $arrList[$k] = " <tr class='treegrid-{$id}'>
                                <td>{$name}</td>
                                <td>Additional info</td>
                                <td>
                                    <a href='{$addurl}' class='btn btn-primary btn-rounded'>增加下属部门</a>
                                    <a href='{$editurl}' class='btn btn-primary btn-rounded'>编辑部门</a>
                                    <a class='btn btn-default btn-rounded'>删除部门</a>
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

    public function addDepartment()
    {

        $data = Array();
        $data['id'] = I('id', 0);
        $data['pId'] = I('pId', 0);
        $data['url'] = $_POST['url'];
        $data['target'] = $_POST['target'];
        $data['name'] = $_POST['name'];
//        print_r($data['url']);

        $res = M('department')->add($data);

        if ($res) {

            return show_tip(1, '添加成功', null, U('Department/departmentList'));
        } else {
            return show_tip(0, "添加失败");
        }
    }

    public function delDepartment()
    {

        $id = I('id', 0, 'intval');

        //查询是否有子类
        $childCate = M('menu')->where(array('pid' => $id))->select();
        if ($childCate) {
//            return show_tip(0,"删除失败，请先删除子菜单");
            return show_tip(0, $id);
        }
        if (M('menu')->delete($id)) {

            return show_tip(1, '删除成功', null, U('Menu/index'));
        } else {
            return show_tip(0, "删除失败");
        }
    }

    public function departmentTest()
    {
        $this->display();
    }
}

?>

