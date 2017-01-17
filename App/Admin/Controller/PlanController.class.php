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
            if($_POST['employeeid']==0){
                return show_tip(0,'请选择员工');
            }
            if (!isset($_POST['startDay']) || !$_POST['startDay']) {
                return show_tip(0, '请输入开始时间');
            }
            if (!isset($_POST['endDay']) || !$_POST['endDay']) {
                return show_tip(0, '清输入结束时间');
            }
            
            $planId = M('plan')->data($_POST)->add();
            if($planId){
                return show_tip(1,'新增成功',$planId,U('planList'));
            }else{
                return show_tip(0,'新增失败',$planId);
            }

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
    
    public function del()
    {

        $id = I('id', 0, 'intval');
        if (M('plan')->delete($id)) {

            return show_tip(1, '删除成功', null, U('planList'));
        } else {
            return show_tip(0, "删除失败");
        }
    }
}
?>

