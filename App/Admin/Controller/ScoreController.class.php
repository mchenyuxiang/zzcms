<?php
/**
 * Created by PhpStorm.
 * User: yuxiang
 * Date: 2017/1/12
 * Time: 0:47
 * Description: 每个任务的分数
 */
namespace Admin\Controller;

use Common\Lib\Category;
use Think\Controller;

class ScoreController extends Controller
{
    public function scoreList()
    {
        $listScore = M('score')->select();
        $listScore = \Common\Lib\Category::toLevel($listScore, '&nbsp;&nbsp;&nbsp;&nbsp;', 0);
//        print_r($listDepartment);

        foreach (array_reverse($listScore) as $k => $v) {
            $id = $v['id'];
            $countSon = M('score')->where('pid = %d', $id)->count();
            $number = $v['number'];
            if ($countSon > 0) {
                $scoreTemp = M('score')->field('sum(score) as score')->where('pid=%d', $id)->select();
//                print_r($scoreTemp[0]['score']);
                $score = $scoreTemp[0]['score'] * $number;
                $res = M('score')->where('id=%d', $id)->setField('score', $score);
                if ($res) {
                    $listScore[sizeof($listScore) - $k - 1]['score'] = $score;
                }
            }
        }


        $arrList = array();
        foreach ($listScore as $k => $v) {
            $id = $v['id'];
            $pid = $v['pid'];
            $name = $v['name'];
            $number = $v['number'];
            $score = $v['score'];
            $addurl = U("add", array('pid' => $v['id']));
            $editurl = U('edit', array('id' => $v['id']));
            if ($v['level'] == 1) {
                $arrList[$k] = " <tr class='treegrid-{$id}'>
                                <td>{$name}</td>
                                <td>{$id}</td>
                                <td>{$number}</td>
                                <td>{$score}</td>
                                <td>
                                    <a href='{$addurl}' class='btn btn-primary btn-rounded'>添加下级</a>
                                    <a href='{$editurl}' class='btn btn-primary btn-rounded'>编辑</a>
                                    <a href=\"javascript:void(0)\" attr-message=\"删除\" id=\"zzcms-delete\" class='btn btn-primary btn-rounded' attr-id=\"{$v['id']}\">删除</a>
                                </td>
                            </tr>";
            } else {
                $arrList[$k] = " <tr class='treegrid-{$id} treegrid-parent-{$pid}'>
                                <td>{$name}</td>
                                <td>{$id}</td>
                                <td>{$number}</td>
                                <td>{$score}</td>
                                <td>
                                    <a href='{$addurl}' class='btn btn-primary btn-rounded'>添加下级</a>
                                    <a href='{$editurl}' class='btn btn-primary btn-rounded'>编辑</a>
                                    <a href=\"javascript:void(0)\" attr-message=\"删除\" id=\"zzcms-delete\" class='btn btn-primary btn-rounded' attr-id=\"{$v['id']}\">删除</a>
                                </td>
                            </tr>";
            }
        }
        $this->assign("listScore", $listScore);
        $this->assign("arrList", $arrList);
        $this->assign("type", "分数管理");
        $this->display();
    }

    public function del()
    {

        $id = I('id', 0, 'intval');

        //查询是否有子类
        $childCate = M('score')->where(array('pid' => $id))->select();
        if ($childCate) {
//            return show_tip(0,"删除失败，请先删除子菜单");
            return show_tip(0, "有子类不能删除");
        }
        if (M('score')->delete($id)) {

            return show_tip(1, '删除成功', null, U('scoreList'));
        } else {
            return show_tip(0, "删除失败");
        }
    }

    public function add()
    {

        if ($_POST) {

            if (!isset($_POST['name']) || !$_POST['name']) {
                return show_tip(0, '部门名称不能为空');
            }

            $menuId = M("score")->data($_POST)->add();
            if ($menuId) {
                return show_tip(1, '新增成功', $menuId, U('scoreList'));
            }
            return show_tip(0, '新增失败', $menuId);
        } else {
            $scoreName = M('score')->select();
            $scoreName = Category::toLevel($scoreName, '---', 0);
            $this->assign('cate', $scoreName);
            $this->assign("type", "分数增加");
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
            $data = M('score')->find($id);
            if (!$data) {
                $this->error("记录不存在");
            }
            $scoreName = M('score')->select();
            $scoreName = Category::toLevel($scoreName, '---', 0);
            $this->assign('data', $data);
            $this->assign('cate', $scoreName);
            $this->assign('type', '修改分数');
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
            return show_tip(0, "名称不能为空");
        }

        if ($id == $pid) {
            return show_tip(0, "不能设置自己为子类");
        }

        if (false !== M('score')->save($data)) {

            return show_tip(1, '修改成功', null, U('scoreList'));
        } else {
            return show_tip(0, '修改失败');
        }
    }
}

?>

