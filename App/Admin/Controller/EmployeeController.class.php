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

    public function edit()
    {
        if (IS_POST) {

            $this->editPost();
            exit();
        } else {
            $id = I('id', 0, 'intval');
            $data = M('employee')->find($id);
            if (!$data) {
                $this->error("记录不存在");
            }
            $employeeName = M('employee')->select();
            $departmentName = M('department')->select();
            $departmentName = Category::toLevel($departmentName, '---', 0);
            $officeName = M('office')->select();
            $officeName = Category::toLevel($officeName, '---', 0);
            $this->assign('data', $data);
            $this->assign('departmentName',$departmentName);
            $this->assign('officeName',$officeName);
            $this->assign('cate', $employeeName);
            $this->assign('type', '修改人员');
            $this->display();
        }

    }

    public function editPost()
    {
        $data = I('post.', '');

        $data['name'] = trim($data['name']);
        $data['departmentId'] = intval($data['departmentId']);
        $data['officeId'] = intval($data['officeId']);

        //M验证
        if($data['departmentId'] == 0 || empty($data['departmentId'])){
            return show_tip(0,"请选择部门");
        }
        if($data['officeId'] == 0 || empty($data['officeId'])){
            return show_tip(0,"请选择职位");
        }
        if (empty($data['name'])) {
            return show_tip(0,"名称不能为空");
        }


        if (false !== M('employee')->save($data)) {

            return show_tip(1,'修改成功',null,U('employeeList'));
        } else {
            return show_tip(0,'修改失败');
        }
    }

    public function add()
    {

        if ($_POST) {
            if (!isset($_POST['name']) || !$_POST['name']) {
                return show_tip(0, '名称不能为空');
            }

            if($_POST['departmentId'] == 0){
                return show_tip(0,'请选择部门');
            }
            if($_POST['officeId'] == 0){
                return show_tip(0,'请选择职位');
            }

            $menuId = M("employee")->data($_POST)->add();
            if ($menuId) {
                return show_tip(1, '新增成功', $menuId, U('employeeList'));
            }
            return show_tip(0, '新增失败', $menuId);


        } else {
            $departmentName = M('department')->select();
            $departmentName = Category::toLevel($departmentName, '---', 0);
            $officeName = M('office')->select();
            $officeName = Category::toLevel($officeName, '---', 0);
            $this->assign('departmentName', $departmentName);
            $this->assign('officeName', $officeName);
            $this->assign('type', '人员添加');
            $this->display();
        }
    }
}

?>

