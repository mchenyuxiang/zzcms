<?php
/**
 * Created by PhpStorm.
 * User: yuxiang
 * Date: 2017/1/18
 * Time: 16:53
 * Description: 等级设置
 */
namespace Admin\Controller;

use Common\Lib\Page;
use Think\Controller;

class LevelController extends Controller
{
    public function levelList()
    {
        $data = array();
        $page = $_REQUEST['p'] ? $_REQUEST['p'] : 1;
        $pageSize = $_REQUEST['pageSize'] ? $_REQUEST['pageSize'] : 15;

        $offset = ($page - 1) * $pageSize;
        $cate = M('level')
            ->where($data)->limit($offset, $pageSize)->select();
        $cateCount = M('class')->where($data)->count();

        $res = new Page($cateCount, $pageSize);
        $pageRes = $res->show();
        $this->assign('cate', $cate);
        $this->assign('type', '分数等级列表');
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
        if (M('level')->delete($id)) {

            return show_tip(1, '删除成功', null, U('levelList'));
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
            if (!isset($_POST['startscore']) || !$_POST['startscore']) {
                return show_tip(0, '最高分不能为空');
            }
            if (!isset($_POST['endscore']) || !$_POST['endscore']) {
                return show_tip(0, '最低分不能为空');
            }
            
            $levelId = M('level')->data($_POST)->add();

            if ($levelId) {
                return show_tip(1, '新增成功', $levelId, U('levelList'));
            }
            return show_tip(0, '新增失败', $levelId);
        } else {
            $this->assign('type', '等级添加');
            $this->display();
        }
    }
}

?>

