<?php
/**
 * Created by PhpStorm.
 * User: yuxiang
 * Date: 2017/1/5
 * Time: 12:17
 * Description: 部门controller
 */
namespace Admin\Controller;

use Think\Controller;

class DepartmentController extends Controller
{
    public function departmentList()
    {
        $this->display();
    }

    public function getDepartment()
    {
        $result = array(
            text => "ceshi4",
            nodes => array(
            ),
        );
        exit(json_encode($result));
    }
}

?>

