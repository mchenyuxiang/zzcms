<?php
/**
 * Created by PhpStorm.
 * User: yuxiang
 * Date: 2017/1/18
 * Time: 15:51
 * Description: 报表
 */
namespace Admin\Controller;

use Think\Controller;

class ReportController extends Controller
{
    public function index()
    {

        $this->display();
    }

    public function personalDetail()
    {
        if ($_POST) {

            if ($_POST['employeeid'] == 0) {
                return show_tip(0, '请选择员工');
            }
            if ($_POST['month'] == 0) {
                return show_tip(0, '请选择月份');
            }

            $month = $_POST['month'];
            $condition['checkDay'] = array('LIKE',$month);
            $deduct = M('deduct')->where($condition)->select();
            $countNum = M('deduct')->where($condition)->count();
            $personalDetail = Array();
            for($i=0;$i<sizeof($deduct);$i++){
            }

        } else {

            $month = array();
            for ($i = 1; $i < 13; $i++) {
                if ($i < 10) {
                    $month[$i]['month'] = '0' . $i;
                } else {

                    $month[$i]['month'] = $i;
                }
            }
            $employeeName = M('employee')->select();
            $this->assign('employeeName', $employeeName);
            $this->assign('month', $month);
            $this->assign('type', '个人明细报表');
            $this->display();
        }
    }
}

?>

