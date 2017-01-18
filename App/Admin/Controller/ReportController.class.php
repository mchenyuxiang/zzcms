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

        } else {

            $month=array();
            for($i=1;$i<13;$i++){
                $month[$i]['month']=$i;
            }
            $employeeName=M('employee')->select();
            $this->assign('employeeName',$employeeName);
            $this->assign('month',$month);
            $this->assign('type','个人明细报表');
            $this->display();
        }
    }
}

?>

