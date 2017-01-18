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
            $deductId = M("deduct")->data($_POST)->add();
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

