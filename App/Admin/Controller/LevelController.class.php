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

class LevelController extends Controller{
    public function levelList(){
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
}
?>

