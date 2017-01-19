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
        if ($_GET) {
            if ($_GET['employeeId'] == 0) {
                return show_tip(0, '请选择员工');
            }
            if ($_GET['month'] == 0) {
                return show_tip(0, '请选择月份');
            }

            $checksql = "select 
  a.*,
  b.name as evl 
from
  (select 
    a.`checkDay`,
    d.name as employeeName,
    COUNT(a.checkProject) as checknum,
    sum(b.score) as checkscore,
    projectNumber,
    c.name as scoreName,
    (c.`score` * c.`number`) as total,
    (
      (
        c.`score` * c.`number` - sum(b.score)
      ) / (c.`score` * c.`number`)
    ) * 100 AS finalScore 
  from
    zzcms_deduct a 
    left join zzcms_score b 
      on a.checkProject = b.`id` 
    left join zzcms_score c 
      on a.`checkTopid` = c.`id` 
    left join zzcms_employee d 
      on d.`id` = a.`employeeid` 
  where a.employeeid = ".$_GET['employeeId']." 
    and checkDay like '%".$_GET['month']."%' 
  group by a.`checkDay`) a 
  join zzcms_level b 
where a.finalScore between b.startscore 
  and b.endscore ";
            $checkTopidRes = M()->query($checksql);
            $this->assign('cate', $checkTopidRes);
            $this->assign('type', '个人明细报表');
            $month = array();
            for ($i = 1; $i < 13; $i++) {
                if ($i < 10) {
                    $month[$i]['month'] = '0' . $i;
                } else {

                    $month[$i]['month'] = $i;
                }
            }
            $employeeName = M('employee')->select();
            $this->assign('month', $month);
            $this->assign('employeeName', $employeeName);
            $this->display();
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
    
    
    public function personalBasic(){
        if($_GET){

            if ($_GET['employeeId'] == 0) {
                return show_tip(0, '请选择员工');
            }
            if ($_GET['month'] == 0) {
                return show_tip(0, '请选择月份');
            }

            $checksql = "select 
  a.*,
  b.name as evl,
    count(*) as 'checktimes'
from
  (select 
    COUNT(*) AS totaltime,
    d.name as employeeName,
    (c.`score` * c.`number`) as total,
    (
      (
        c.`score` * c.`number` - sum(b.score)
      ) / (c.`score` * c.`number`)
    ) * 100 AS finalScore 
  from
    zzcms_deduct a 
    left join zzcms_score b 
      on a.checkProject = b.`id` 
    left join zzcms_score c 
      on a.`checkTopid` = c.`id` 
    left join zzcms_employee d 
      on d.`id` = a.`employeeid` 
  where a.employeeid = ".$_GET['employeeId']." 
    and checkDay like '%".$_GET['month']."%' 
  group by a.`checkDay`) a 
  join zzcms_level b 
where a.finalScore between b.startscore 
  and b.endscore GROUP BY evl ";
            $checkTopidRes = M()->query($checksql);

            $total = 0;
            foreach ($checkTopidRes as $key=>$v){
                $total = $total + intval($v['totaltime']);
            }

            $this->assign('cate', $checkTopidRes);
            $this->assign('total',$total);
            $this->assign('type', '个人概况');
            $month = array();
            for ($i = 1; $i < 13; $i++) {
                if ($i < 10) {
                    $month[$i]['month'] = '0' . $i;
                } else {

                    $month[$i]['month'] = $i;
                }
            }
            $employeeName = M('employee')->select();
            $this->assign('month', $month);
            $this->assign('employeeName', $employeeName);
            $this->display();
        }else{
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
            $this->assign('type', '个人概况');
            $this->display();
        }
    }
}

?>

