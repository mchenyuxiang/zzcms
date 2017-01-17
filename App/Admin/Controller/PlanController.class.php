<?php
/**
 * Created by PhpStorm.
 * User: yuxiang
 * Date: 2017/1/15
 * Time: 14:42
 * Description: 人员排班
 */
namespace Admin\Controller;

use Common\Lib\Page;
use Think\Controller;

class PlanController extends Controller{
    public function planList(){

        $data = array();
        $page = $_REQUEST['p'] ? $_REQUEST['p'] : 1;
        $pageSize = $_REQUEST['pageSize'] ? $_REQUEST['pageSize'] : 15;

        $offset = ($page - 1) * $pageSize;
        $today = date("Y-m-d");
        $data['endDay'] = array('egt',$today);
        $cate = M('plan')
            ->where($data)->limit($offset, $pageSize)->select();
        $cateCount = M('employee')->where($data)->count();

        $res = new Page($cateCount, $pageSize);
        $pageRes = $res->show();
        $this->assign('cate', $cate);
        $this->assign('type',"人员排班");
        $this->assign('page',$pageRes);
        $this->display();
    }
    
    public function add(){
        $this->display();
    }
    
    public function edit(){
        $this->display();
    }
}
?>

