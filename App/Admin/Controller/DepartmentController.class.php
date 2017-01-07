<?php
/**
 * Created by PhpStorm.
 * User: yuxiang
 * Date: 2017/1/5
 * Time: 12:17
 * Description: 部门controller
 */
namespace Admin\Controller;

use Think\Controller;

class DepartmentController extends Controller
{
    public function departmentList()
    {
        $listDepartment = M('department')->select();
        $this->assign("listDepartment",json_encode($listDepartment));
        $this->assign("type","部门管理");
        $this->display();
    }

    public function getDepartment()
    {
        $result = array(
            text => "ceshi4",
            nodes => array(
            ),
        );
        exit(json_encode($result));
    }

    public function addDepartment()
    {

        $data = Array();
        $data['id'] = I('id', 0);
        $data['pId']= I('pId',0);
        $data['url'] = I('url');
        $data['target'] = I('target');
        $data['name'] = I('name');

        $res = M('department')->add($data);

        if ($res) {

            return show_tip(1,'添加成功',null,U('Department/departmentList'));
        } else {
            return show_tip(0,"添加失败");
        }
    }

    public function delDepartment()
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

