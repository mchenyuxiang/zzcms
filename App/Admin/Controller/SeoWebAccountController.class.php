<?php
/**
 * Created by PhpStorm.
 * User: yuxiang
 * Date: 2017/2/7
 * Time: 19:34
 */
namespace Admin\Controller;


class SeoWebAccountController extends CommonController
{
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
    }

    /**
     * 账户资料
     */
    public function update()
    {
        if ($_POST) {
            $this->editPost();
            exit();
        } else {
            $userid = session('zzcms_adm_userid');
            $condition = array('id' => $userid);
            $usernameRes = M('admin')->where($condition)->select();
            $username = $usernameRes[0]['username'];
            $companyname = $usernameRes[0]['companyname'];
            $companyqq = $usernameRes[0]['companyqq'];
            $email = $usernameRes[0]['email'];
            $phone = $usernameRes[0]['phone'];
            $this->assign("username", $username);
            $this->assign("companyname", $companyname);
            $this->assign("companyqq", $companyqq);
            $this->assign("email", $email);
            $this->assign("phone", $phone);
            $this->assign("id",$userid);
            $this->assign("type", "账户资料");
            $this->display();
        }
    }

    public function editPost()
    {
        $data = I('post.', '');

        //M验证
        if (empty($data['companyname'])) {
            return show_tip(0, "公司名称不能为空");
        }
        if (false !== M('admin')->save($data)) {
            return show_tip(1, '修改成功', null, U('update'));
        } else {
            return show_tip(0, '修改失败');
        }
    }

    /**
     * 修改密码
     */
    public function UpdateSecrect()
    {
        $this->display();
    }
}

?>