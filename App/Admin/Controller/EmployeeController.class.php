<?php
/**
 * Created by PhpStorm.
 * User: yuxiang
 * Date: 2017/1/7
 * Time: 12:48
 * Description: 职员
 */
namespace Admin\Controller;

use Think\Controller;

class EmployeeController extends Controller{
    public function index(){
        $this->display();
    }
    
    public function employeeList(){
        $cid = I('cid');
        $pid = I('pid');

        $this->display();
    }
}
?>

