<?php
/**
 * Created by PhpStorm.
 * User: yuxiang
 * Date: 2017/1/7
 * Time: 12:48
 * Description: 职员
 */
namespace Admin\Controller;

use Common\Lib\Category;
use Common\Lib\Page;
use Think\Controller;

class EmployeeController extends Controller
{

    public function employeeList()
    {
        $data = array();
        $page = $_REQUEST['p'] ? $_REQUEST['p'] : 1;
        $pageSize = $_REQUEST['pageSize'] ? $_REQUEST['pageSize'] : 15;

        $offset = ($page - 1) * $pageSize;
        $cate = M()
            ->table('zzcms_employee as a')
            ->join('LEFT JOIN zzcms_department as b on b.id=a.departmentId')
            ->join('LEFT JOIN zzcms_office as c on c.id=a.officeId')
            ->field('a.id as id,a.name as name,b.name as departmentName,c.name as officeName')
            ->where($data)->limit($offset, $pageSize)->select();
        $cateCount = M('employee')->where($data)->count();

        $res = new Page($cateCount, $pageSize);
        $pageRes = $res->show();
        $this->assign('cate', $cate);
        $this->assign('type', '人员列表');
        $this->assign('page', $pageRes);
        $this->display();
    }
}

?>

