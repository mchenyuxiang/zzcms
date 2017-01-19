<?php
/**
 * Created by PhpStorm.
 * User: yuxiang
 * Date: 2017/1/19
 * Time: 0:45
 * Description: 扣分
 */
namespace Admin\Controller;

use Think\Controller;

class CheckController extends Controller{
    public function checkProject(){
        if($_POST){

            if (!isset($_POST['checkDay']) || !$_POST['checkDay']) {
                return show_tip(0, '请输入日期');
            }
            if ($_POST['checkTime'] == 0) {
                return show_tip(0, '请选择时间');
            }
            $data = I('post.','');
            $checkId = $data['checkProject'];
            $checksql = "SELECT id,NAME,score,number FROM zzcms_score WHERE FIND_IN_SET(pid, getParList(".$checkId.")) AND pid = 0";
            $checkTopidRes = M()->query($checksql);
            $checkTopid = $checkTopidRes[0]['id'];
            $data['checkTopid'] = $checkTopid;
            $employIdsql = "SELECT 
      id,employeeid,
      SUBSTRING_INDEX(
        SUBSTRING_INDEX(
          GROUP_CONCAT(mon,',',tues,',',wed,',',thur,',',fri,',',sat,',',sun),',',DAYOFWEEK('".$data[checkDay]."')),',',- 1
      ) AS banci 
    FROM
      zzcms_plan 
where '".$data['checkDay']."' between startDay and endDay
    GROUP BY id";
            $employeeIdRes = M()->query($employIdsql);
            $employeeId = $employeeIdRes[0]['employeeid'];
            $data['employeeid'] = $employeeId;
            $deductId = M("deduct")->data($data)->add();
            if($deductId){
                return show_tip(1,'成功',$deductId,U('checkProject'));
            }
            return show_tip(0,'新增失败',$deductId);
        }else{

            $time = Array();
            for ($a = 8; $a <= 24; $a++) {
                $time[$a]['time'] = $a . ':00';
            }
            $this->assign('time', $time);
            $this->assign('type', '项目检查');
            $this->display();
        }
    }
}
?>

