<?php
/**
 * Created by PhpStorm.
 * User: yuxiang
 * Date: 2017/1/19
 * Time: 0:45
 * Description: 扣分
 */
namespace Admin\Controller;


class CheckController extends CommonController {
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
    }

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
            $getParListsql = "SELECT getParList(".$checkId.") AS getParList";
            $getParListRes = M()->query($getParListsql);
            $getParList = $getParListRes[0]['getparlist'];
            $hello = explode(',',$getParList);
            $checkTopid="";
            for($i=0;i<count($hello);$i++){
                $tempArr = M('score')->where(array('id'=>$hello[$i]))->select();
                if($tempArr[0]['pid'] == 0){
                    $checkTopid = $tempArr[0]['id'];
                    break;
                }
            }
            $data['checkTopid'] = $checkTopid;
            $checkTimeArr = explode(':',$data['checkTime']);
            $checkTime = $checkTimeArr[0];
            $classNamesql = "SELECT NAME FROM zzcms_class WHERE (CONVERT(SUBSTRING_INDEX(starttime,':',1),SIGNED)<='".$checkTime."' AND CONVERT(SUBSTRING_INDEX(endtime,':',1),SIGNED)>'".$checkTime."') AND scoreId = ".$checkTopid;
            $classNameRes = M()->query($classNamesql);
            $className = $classNameRes[0]['name'];
            $employIdsql = "SELECT * FROM (SELECT 
      id,employeeid,
      SUBSTRING_INDEX(
        SUBSTRING_INDEX(
          GROUP_CONCAT(mon,',',tues,',',wed,',',thur,',',fri,',',sat,',',sun),',',DAYOFWEEK('".$data[checkDay]."')),',',- 1
      ) AS banci 
    FROM
      zzcms_plan 
where '".$data['checkDay']."' between startDay and endDay
    GROUP BY id) AS a WHERE a.banci= '".$className."'";
            $employeeIdRes = M()->query($employIdsql);
            $employeeId = $employeeIdRes[0]['employeeid'];
            $data['employeeid'] = $employeeId;

            $projectNumsql = "select 
        count(*) as cnt 
      from
        zzcms_score as a 
        left join 
          (select 
            * 
          from
            zzcms_score 
          where FIND_IN_SET(id, getChildList (1))) as b 
          on a.id = b.pid 
      where b.id is null";
            $projectNumRes = M()->query($projectNumsql);
            $projectNumber = $projectNumRes[0]['cnt'];
            $data['projectNumber'] = $projectNumber;

            $data['adminId'] = session('zzcms_adm_userid');
            $data['createtime']=date('y-m-d h:i:s',time());
            $deductId = M("deduct")->data($data)->add();
            if($deductId){
//                return show_tip(1,'成功',$deductId,U('checkProject'));
                return show_tip(1,'成功',$checkTimeArr,U('checkProject'));
            }
            return show_tip(0,'新增失败',$deductId);
//            $this->display();
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

