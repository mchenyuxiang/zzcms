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
        $cate = M()
            ->table('zzcms_plan as a')
            ->join('RIGHT JOIN zzcms_employee as b on b.id=a.employeeid')
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

        if($_POST){

        }else{
            $employeeName=M('employee')->select();
            $className=M('class')->select();
            $this->assign('employeeName',$employeeName);
            $this->assign('className',$className);
            $this->assign('tpye','增加排班');
            $this->display();
        }
    }
    
    public function edit(){
        $this->display();
    }
}
?>

