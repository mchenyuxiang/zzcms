<?php
/**
 * Created by PhpStorm.
 * User: yuxiang
 * Date: 2017/1/4
 * Time: 23:18
 * Description: 员工相关控制器
 */
namespace Admin\Controller;

use Common\Lib\Category;
use Think\Controller;

class OfficeController extends Controller
{

    public function officeList()
    {
        $listOffice = M('office')->select();
        $listOffice = \Common\Lib\Category::toLevel($listOffice, '&nbsp;&nbsp;&nbsp;&nbsp;', 0);
//        print_r($listDepartment);


        $arrList = array();
        foreach ($listOffice as $k => $v) {
            $id = $v['id'];
            $pid = $v['pid'];
            $name = $v['name'];
            $addurl = U("add", array('pid' => $v['id']));
            $editurl = U('edit', array('id' => $v['id']));
            if ($v['level'] == 1) {
                $arrList[$k] = " <tr class='treegrid-{$id}'>
                                <td>{$name}</td>
                                <td>Additional info</td>
                                <td>
                                    <a href='{$addurl}' class='btn btn-primary btn-rounded'>增加下属职位</a>
                                    <a href='{$editurl}' class='btn btn-primary btn-rounded'>编辑职位</a>
                                    <a href=\"javascript:void(0)\" attr-message=\"删除\" id=\"zzcms-delete\" class='btn btn-primary btn-rounded' attr-id=\"{$v['id']}\">删除职位</a>
                                </td>
                            </tr>";
            } else {
                $arrList[$k] = " <tr class='treegrid-{$id} treegrid-parent-{$pid}'>
                                <td>{$name}</td>
                                <td>Additional info</td>
                                <td>
                                    <a href='{$addurl}' class='btn btn-primary btn-rounded'>增加下属职位</a>
                                    <a href='{$editurl}' class='btn btn-primary btn-rounded'>编辑职位</a>
                                    <a href=\"javascript:void(0)\" attr-message=\"删除\" id=\"zzcms-delete\" class='btn btn-primary btn-rounded' attr-id=\"{$v['id']}\">删除职位</a>
                                </td>
                            </tr>";
            }
        }
        $this->assign("listOffice", $listOffice);
        $this->assign("arrList", $arrList);
        $this->assign("type", "职位管理");
        $this->display();
    }


    public function add()
    {

        if ($_POST) {

            if (!isset($_POST['name']) || !$_POST['name']) {
                return show_tip(0, '职位名称不能为空');
            }

            $menuId = M("office")->data($_POST)->add();
            if ($menuId) {
                return show_tip(1, '新增成功', $menuId, U('officeList'));
            }
            return show_tip(0, '新增失败', $menuId);
        } else {
            $officeName = M('office')->select();
            $officeName = Category::toLevel($officeName, '---', 0);
            $this->assign('cate', $officeName);
            $this->display();
        }
    }

    public function del()
    {

        $id = I('id', 0, 'intval');

        //查询是否有子类
        $childCate = M('office')->where(array('pid' => $id))->select();
        if ($childCate) {
//            return show_tip(0,"删除失败，请先删除子菜单");
            return show_tip(0, $id);
        }
        if (M('office')->delete($id)) {

            return show_tip(1, '删除成功', null, U('officeList'));
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
            $data = M('office')->find($id);
            if (!$data) {
                $this->error("记录不存在");
            }
            $officeName = M('office')->select();
            $officeName = Category::toLevel($officeName, '---', 0);
            $this->assign('data', $data);
            $this->assign('cate', $officeName);
            $this->assign('type', '修改职位');
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
            return show_tip(0, "职位名称不能为空");
        }

        if ($id == $pid) {
            return show_tip(0, "不能设置自己为子职位");
        }

        if (false !== M('office')->save($data)) {

            return show_tip(1, '修改成功', null, U('officeList'));
        } else {
            return show_tip(0, '修改失败');
        }
    }
}

?>

