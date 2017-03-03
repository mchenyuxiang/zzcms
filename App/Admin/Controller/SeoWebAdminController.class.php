<?php
/**
 * Created by PhpStorm.
 * User: yuxiang
 * Date: 2017/2/7
 * Time: 19:41
 */
namespace Admin\Controller;


class SeoWebAdminController extends CommonController
{
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
    }

    /**
     * 网站添加
     */
    public function add()
    {
        if ($_POST) {

            if (!isset($_POST['websitename']) || !$_POST['websitename']) {
                return show_tip(0, '网站名称不能为空');
            }
            if (!isset($_POST['websiteurl']) || !$_POST['websiteurl']) {
                return show_tip(0, '网站域名不能为空');
            }

            if (!validateURL($_POST['websiteurl'])) {
                return show_tip(0, '请输入合法域名');
            }

            $_POST['createtime'] = date('Y-m-d H:i:s', time());
            $webId = M("seo_web")->data($_POST)->add();
            if ($webId) {
                return show_tip(1, '新增成功', $webId, U('add'));
            }
            return show_tip(0, '新增失败', $webId);
        } else {
            $userid = session('zzcms_adm_userid');
            $platforminfo = M('seo_platform')->select();
            $this->assign("platform", $platforminfo);
            $this->assign("id", $userid);
            $this->assign("type", "添加网站");
            $this->display();
        }
    }

    /**
     * 网站管理
     */
    public function ListInfo()
    {
        $userid = session('zzcms_adm_userid');
        $sql = "SELECT 
  a.id,
  websitename,
  websiteurl,
  platformname,
  DATE_FORMAT(a.createtime,'%Y-%m-%d') as createtime,
  COUNT(DISTINCT(e.name)) AS keywordnumber
FROM
  zzcms_seo_web AS a 
  JOIN 
    (SELECT 
      GROUP_CONCAT(platformname) AS platformname 
    FROM
      zzcms_seo_platform 
    WHERE FIND_IN_SET(
        id,
        (SELECT 
          c.platformid 
        FROM
          zzcms_seo_web c , zzcms_seo_web d
          WHERE
          c.id=d.id and c.userid=".$userid.")
      )) AS b 
  LEFT JOIN
  zzcms_seo_keyword e
  ON a.id= e.webid
WHERE a.`userid`=" . $userid;
        $listinfo = M()->query($sql);
        $b_sql = "SELECT balance,updatetime FROM zzcms_admin WHERE id = ".$userid;
        $balanceArr = M()->query($b_sql);
        $balanceT = $balanceArr[0]['balance'];
        $updatetime = strtotime($balanceArr[0]['updatetime']);
        $timetoday = date("Y-m-d",time());
        $data['updatetime'] = $timetoday;
        $timetodaystr = strtotime($timetoday);
//        print_r($timetodaystr."--".$updatetime);
        if($updatetime < $timetodaystr){
            $recharge_sql = "SELECT SUM(priceone+pricetwo) AS cost FROM zzcms_seo_costdetail WHERE userid = ".$userid;
            $rechargeArr = M()->query($recharge_sql);
            $recharge = $rechargeArr[0]['cost'];
            $balance = $balanceT - $recharge;
            $data['balance'] = $balance;
            $data['id'] = $userid;
            M('admin')->save($data);
            $balanceT = $balance;
        }
        $this->assign("listinfo", $listinfo);
        $this->assign("balance", $balanceT);
        $this->assign("type", "管理网站");
        $this->display();
    }

    /**
     * 修改网站信息
     */
    public function edit()
    {
        if (IS_POST) {

            $data = I('post.', '');
            $data['createtime'] = date('Y-m-d H:i:s', time());

            //M验证
            if (!isset($_POST['websitename']) || !$_POST['websitename']) {
                return show_tip(0, '网站名称不能为空');
            }
            if (!isset($_POST['websiteurl']) || !$_POST['websiteurl']) {
                return show_tip(0, '网站域名不能为空');
            }

            if (!validateURL($_POST['websiteurl'])) {
                return show_tip(0, '请输入合法域名');
            }
            if (false !== M('seo_web')->save($data)) {
                return show_tip(1, '修改成功', null, U('ListInfo'));
            } else {
                return show_tip(0, '修改失败');
            }
        } else {
            $userid = session('zzcms_adm_userid');
            $platforminfo = M('seo_platform')->select();
            $id = I('id', 0, 'intval');
            $data = M('seo_web')->find($id);
            if (!$data) {
                $this->error("记录不存在");
            }
            $platformid = $data['platformid'];
            $platformidRes = explode(',', $platformid);
            foreach($platformidRes as $i=>$platvalue){
               foreach($platforminfo as $key=>$value){
                   if($platforminfo[$key]['id'] == $platformidRes[$i]){
                       $platforminfo[$key]['checked'] = 'checked';
                       break;
                   }
                }
            }
//            print_r($platforminfo);
//            print_r($platformidRes);
            $this->assign("platform", $platforminfo);
            $this->assign("platformid", $platformidRes);
            $this->assign('data', $data);
            $this->assign("id", $userid);
            $this->assign("type", "修改网站");
            $this->display();
        }

    }
}