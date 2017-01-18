<?php
/**
 * Created by PhpStorm.
 * User: yuxiang
 * Date: 2017/1/12
 * Time: 22:48
 * Description:
 */
namespace Admin\Controller;

use Common\Lib\Page;
use Think\Controller;

class ClassSettingController extends Controller
{
    public function index()
    {
        $this->display();
    }

    public function classList()
    {
        $data = array();
        $page = $_REQUEST['p'] ? $_REQUEST['p'] : 1;
        $pageSize = $_REQUEST['pageSize'] ? $_REQUEST['pageSize'] : 15;

        $offset = ($page - 1) * $pageSize;
        $cate = M()
            ->table('zzcms_class as a')
            ->join('LEFT JOIN zzcms_score as b on a.scoreId = b.id')
            ->field('a.id as id,a.name as name,a.starttime as starttime,a.endtime as endtime,b.name as scoreName')
            ->where($data)->limit($offset, $pageSize)->select();
        $cateCount = M('class')->where($data)->count();

        $res = new Page($cateCount, $pageSize);
        $pageRes = $res->show();
        $this->assign('cate', $cate);
        $this->assign('type', '班次列表');
        $this->assign('page', $pageRes);
        $this->display();
    }

    public function del()
    {

        $id = I('id', 0, 'intval');

//        //查询是否有子类
//        $childCate = M('employee')->where(array('pid' => $id))->select();
//        if ($childCate) {
////            return show_tip(0,"删除失败，请先删除子菜单");
//            return show_tip(0, "有子类不能删除");
//        }
        if (M('class')->delete($id)) {

            return show_tip(1, '删除成功', null, U('classList'));
        } else {
            return show_tip(0, "删除失败");
        }
    }

    public function add()
    {
        if ($_POST) {

            if (!isset($_POST['name']) || !$_POST['name']) {
                return show_tip(0, '名称不能为空');
            }

            if ($_POST['starttime'] == 0) {
                return show_tip(0, '请选择上班时间');
            }
            if ($_POST['endtime'] == 0) {
                return show_tip(0, '请选择下班时间');
            }
            if ($_POST['scoreId'] == 0) {
                return show_tip(0, '请选择管理区域');
            }

            $classId = M("class")->data($_POST)->add();
            if ($classId) {
                return show_tip(1, '新增成功', $classId, U('classList'));
            }
            return show_tip(0, '新增失败', $classId);
        } else {
            $time = Array();
            for ($a = 1; $a <= 24; $a++) {
                $time[$a]['time'] = $a . ':00';
            }
            $scoreName = M('score')->where('pid=0')->select();
            $this->assign('time', $time);
            $this->assign('scoreName',$scoreName);
            $this->assign('type', '班次添加');
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
            $data = M('class')->find($id);
            if (!$data) {
                $this->error("记录不存在");
            }
            $time = Array();
            for ($a = 1; $a <= 24; $a++) {
                $time[$a]['time'] = $a . ':00';
            }
            $scoreName = M('score')->where('pid=0')->select();
            $this->assign('scoreName',$scoreName);
            $this->assign('data', $data);
            $this->assign('time', $time);
            $this->assign('type', '修改班次');
            $this->display();
        }
    }

    public function editPost()
    {
        $data = I('post.', '');

        $data['name'] = trim($data['name']);
        $data['starttime'] = trim($data['starttime']);
        $data['endtime'] = trim($data['endtime']);
        $data['scoreId'] = intval($data['scoreId']);

        //M验证
        if (!isset($_POST['name']) || !$_POST['name']) {
            return show_tip(0, '名称不能为空');
        }
        if ($data['starttime'] == 0 || empty($data['starttime'])) {
            return show_tip(0, "请选择上班时间");
        }
        if ($data['endtime'] == 0 || empty($data['endtime'])) {
            return show_tip(0, "请选择下班时间");
        }


        if (false !== M('class')->save($data)) {

            return show_tip(1, '修改成功', null, U('classList'));
        } else {
            return show_tip(0, '修改失败');
        }
    }
}

?>

