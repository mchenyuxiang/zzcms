<?php
/**
 * Created by PhpStorm.
 * User: yuxiang
 * Date: 2017/3/3
 * Time: 13:39
 */
namespace Admin\Controller;

use Common\Lib\Page;

class AgentAdminController extends CommonController
{

    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $userid = session('zzcms_adm_userid');
        if ($userid != 1) {
            $this->error('非代理商不能操作', U('/index/info'), 1);
        }
    }

    public function index()
    {
        $this->display();
    }

    public function UserAdmin()
    {
        $page = $_REQUEST['p'] ? $_REQUEST['p'] : 1;
        $pageSize = $_REQUEST['pageSize'] ? $_REQUEST['pageSize'] : 10;

        $offset = ($page - 1) * $pageSize;
        $userinfo = M('admin')->limit($offset, $pageSize)->select();
        $userInfoCount = M('admin')->count();
        $res = new Page($userInfoCount, $pageSize);
        $listallinfo = array();
        foreach ($userinfo as $key => $v) {
//            $listinfo = array();

            $sql = "SELECT 
  a.id,
  f.id as userid,
  f.recharge,
  f.username,
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
          c.id=d.id and c.userid=" . $v['id'] . ")
      )) AS b 
  LEFT JOIN
  zzcms_seo_keyword e
  ON a.id= e.webid
  LEFT JOIN
  zzcms_admin f
  on a.userid = f.id
WHERE f.`id`=" . $v['id'];
            $listinfo = M()->query($sql);
            $b_sql = "SELECT balance,updatetime FROM zzcms_admin WHERE id = " . $v['id'];
            $balanceArr = M()->query($b_sql);
            $balanceT = $balanceArr[0]['balance'];
            $data['id'] = $v['id'];
            $updatetime = strtotime($balanceArr[0]['updatetime']);
            $timetoday = date("Y-m-d", time());
            $data['updatetime'] = $timetoday;
            $timetodaystr = strtotime($timetoday);
//        print_r($timetodaystr."--".$updatetime);
            if ($updatetime < ($timetodaystr-3600)) {
                $recharge_sql = "SELECT SUM(priceone+pricetwo) AS cost FROM zzcms_seo_costdetail WHERE userid = " . $v['id'];
                $rechargeArr = M()->query($recharge_sql);
                $recharge = $rechargeArr[0]['cost'];
                $balance = $balanceT - $recharge;
                $data['balance'] = $balance;
                M('admin')->save($data);
                $balanceT = $balance;
            }
            $listallinfo[$key] = $listinfo;
            $listallinfo[$key]['balance'] = $balanceT;
        }
        $this->assign("listinfo", $listallinfo);
//        $this->assign("balance", $balance);
        $pageRes = $res->show();
        $this->assign('page', $pageRes);
        $this->assign("type", "用户管理");
        $this->display();
    }

    /**
     * 关键词管理
     */
    public function ListInfo()
    {
        $page = $_REQUEST['p'] ? $_REQUEST['p'] : 1;
        $pageSize = $_REQUEST['pageSize'] ? $_REQUEST['pageSize'] : 20;

        $offset = ($page - 1) * $pageSize;
        $userid = session('zzcms_adm_userid');
        $data = I('get.', '');
        if ($data['userid'] != null) {
            $userid = $data['userid'];
        }
        $listinfo = M()
            ->table('zzcms_seo_keyword as c')
            ->join('LEFT JOIN zzcms_seo_platform as b on c.platformid = b.id')
            ->join('LEFT JOIN zzcms_seo_costdetail as a  on a.`keywordid` = c.id')
            ->join('LEFT JOIN zzcms_seo_web as d on c.webid = d.id')
            ->field('c.userid,a.keywordid,b.platformname,CASE WHEN a.rank  IS NULL  THEN \'暂无排名信息\' when a.rank = 100 then \'50名之后\' ELSE a.rank END AS rank,c.name,CASE WHEN SUM(a.`priceone`+a.pricetwo) IS NULL THEN \'暂无更新\' ELSE SUM(a.priceone+a.pricetwo) END AS totalprice,d.`websiteurl` ,c.priceone,c.pricetwo,d.websitename')
            ->where(array('c.userid' => $userid))->group('c.name,platformname')->limit($offset, $pageSize)->select();
        $countsql = "SELECT COUNT(*) as count FROM (SELECT 
 COUNT(*)
FROM
  zzcms_seo_keyword AS c 
  LEFT JOIN zzcms_seo_platform AS b 
    ON c.platformid = b.id 
  LEFT JOIN zzcms_seo_costdetail AS a 
    ON a.`keywordid` = c.id 
  LEFT JOIN zzcms_seo_web AS d 
    ON c.webid = d.id 
WHERE c.userid = '" . $userid . "' 
GROUP BY c.name,
  platformname ) t";

        $checkCount = M()->query($countsql);

        $cateCount = $checkCount[0]['count'];
        $res = new Page($cateCount, $pageSize);
        $pageRes = $res->show();
        $this->assign('page', $pageRes);
        $this->assign('listinfo', $listinfo);
        $this->assign('type', '管理关键词');
        $this->display();
    }

    /**
     * 排名详情
     */
    public function listrank()
    {
        $page = $_REQUEST['p'] ? $_REQUEST['p'] : 1;
        $pageSize = $_REQUEST['pageSize'] ? $_REQUEST['pageSize'] : 20;

        $data = I('get.', '');
        $keywordid = $data['keywordid'];
        $platformname = $data['platformname'];
        $offset = ($page - 1) * $pageSize;
        $userid = session('zzcms_adm_userid');
        if ($data['userid'] != null) {
            $userid = $data['userid'];
        }
        $listinfo = M()
            ->table('zzcms_seo_costdetail as a')
            ->join('LEFT JOIN zzcms_seo_platform as b on a.platformid = b.id')
            ->join('LEFT JOIN zzcms_seo_keyword as c on a.`keywordid` = c.id')
            ->join('LEFT JOIN zzcms_seo_web as d on a.webid = d.id')
            ->field('a.id,b.platformname,a.rank,c.name,(a.`priceone`+a.`pricetwo`) as priceone,DATE_FORMAT( a.`createtime`, \'%Y-%m-%d\') AS createtime,d.`websiteurl`')
            ->where(array('a.userid' => $userid, 'a.keywordid' => $keywordid, 'b.platformname' => $platformname))->order('createtime DESC,b.id ASC')->limit($offset, $pageSize)->select();
        $cateCount = M()
            ->table('zzcms_seo_costdetail as a')
            ->join('LEFT JOIN zzcms_seo_platform as b on a.platformid = b.id')
            ->join('LEFT JOIN zzcms_seo_keyword as c on a.`keywordid` = c.id')
            ->join('LEFT JOIN zzcms_seo_web as d on a.webid = d.id')
            ->field('a.id,b.platformname,a.rank,c.name,(a.`priceone`+a.`pricetwo`) as priceone,a.`createtime`,d.`websiteurl`')
            ->where(array('a.userid' => $userid, 'a.keywordid' => $keywordid, 'b.platformname' => $platformname))->count();

        $res = new Page($cateCount, $pageSize);
        $pageRes = $res->show();
        $this->assign('page', $pageRes);
        $this->assign('listinfo', $listinfo);
        $this->assign('userid',$userid);
        $this->assign('type', '排名详情');
        $this->display();
    }

    /**
     * 扣费记录详情
     */
    public function CostDetail()
    {
        $data = array();
        $page = $_REQUEST['p'] ? $_REQUEST['p'] : 1;
        $pageSize = $_REQUEST['pageSize'] ? $_REQUEST['pageSize'] : 20;

        $offset = ($page - 1) * $pageSize;
        $userid = session('zzcms_adm_userid');
        $data = I('get.', '');
        if ($data['userid'] != null) {
            $userid = $data['userid'];
        }
        $listinfo = M()
            ->table('zzcms_seo_keyword as c')
            ->join('LEFT JOIN zzcms_seo_platform as b on c.platformid = b.id')
            ->join('LEFT JOIN zzcms_seo_costdetail as a on a.`keywordid` = c.id')
            ->join('LEFT JOIN zzcms_seo_web as d on c.webid = d.id')
            ->field('a.id,b.platformname,a.rank,c.name,(a.`priceone`+a.`pricetwo`) as priceone,DATE_FORMAT( a.`createtime`, \'%Y-%m-%d\') AS createtime,d.`websiteurl`')
            ->where("a.userid=%d and (a.priceone+a.pricetwo)!=0", array($userid))->order('createtime DESC,b.id ASC')->limit($offset, $pageSize)->select();
        $cateCount = M()
            ->table('zzcms_seo_keyword as c')
            ->join('LEFT JOIN zzcms_seo_platform as b on c.platformid = b.id')
            ->join('LEFT JOIN zzcms_seo_costdetail as a on a.`keywordid` = c.id')
            ->join('LEFT JOIN zzcms_seo_web as d on c.webid = d.id')
            ->field('a.id,b.platformname,a.rank,c.name,(a.`priceone`+a.`pricetwo`) as priceone,a.`createtime`,d.`websiteurl`')
            ->where("a.userid=%d and (a.priceone+a.pricetwo)!=0", array($userid))->count();

        $res = new Page($cateCount, $pageSize);
        $pageRes = $res->show();
        $this->assign('page', $pageRes);
        $this->assign('listinfo', $listinfo);
        $this->assign('type', '扣费详细记录');
        $this->display();
    }

    public function add()
    {
        if ($_POST) {

            //防止重复提交 如果重复提交跳转至相关页面
            if (!checkToken($_POST['TOKEN'])) {
//                $this->redirect('index/index');
                return show_tip(0, '已经成功提交，请刷新页面!');
            }


            $data = I('post.', '');
            if (!isset($_POST['username']) || !$_POST['username']) {
                return show_tip(0, '用户名不能为空');
            }
            if (strlen($data['newpassword']) < 6 && $data['id'] != 1) {
                return show_tip(0, '密码长度必须大于6');
            }

            if ($data['newpassword'] != $data['confirmpassword']) {
                return show_tip(0, '两次密码不一样请重新输入');
            }
            if (!isset($data['balance']) || !$data['balance']) {
                return show_tip(0, '必须填写充值金额');
            }
            $data['password'] = getMd5Password($data['newpassword']);
            $data['recharge'] = $data['balance'];
            $data['createtime'] = date('Y-m-d', time());
            $userid = M('admin')->data($data)->add();
            if ($userid) {
                return show_tip(1, '新增成功', $userid, U('UserAdmin'));
            }
            return show_tip(0, '新增失败', $userid);
        } else {
            creatToken();
            $this->assign("type", "添加用户");
            $this->display();
        }
    }

    public function edit()
    {
        if ($_POST) {

            $data = I('post.', '');
            if (!isset($_POST['username']) || !$_POST['username']) {
                return show_tip(0, '用户名不能为空');
            }
            if (false !== M('admin')->save($data)) {
                return show_tip(1, '修改成功', null, U('UserAdmin'));
            } else {
                return show_tip(0, '修改失败');
            }
        } else {
            $data = I('get.', '');
            $userinfo = M('admin')->find($data['id']);


            $this->assign("data", $userinfo);
            $this->assign("type", "修改用户");
            $this->display();
        }
    }

    /**
     * 删除
     */
    public function del()
    {

        $id = I('id', 0, 'intval');

        //查询是否有子类
        $childCate = M('seo_web')->where(array('userid' => $id))->select();
        if ($childCate) {
//            return show_tip(0,"删除失败，请先删除子菜单");
            return show_tip(0, "用户名下有网站不能删除");
        }
        if (M('admin')->delete($id)) {

            return show_tip(1, '删除成功', null, U('UserAdmin'));
        } else {
            return show_tip(0, "删除失败");
        }
    }

    /**
     * 续费功能
     */
    public function recharge()
    {
        if ($_POST) {
            $data = I('post.', '');
            if(!isset($_POST['recharge']) || !$_POST['recharge']){
                return show_tip(0, '充值金额不能为空');
            }
            $olduserinfo = M('admin')->where(array('id' => $data['id']))->select();
            $oldbalance = $olduserinfo[0]['balance'];
            $oldrecharge = $olduserinfo[0]['recharge'];
            $condition['balance'] = $oldbalance + $data['recharge'];
            $condition['recharge'] = $oldrecharge + $data['recharge'];
            $condition['id'] = $data['id'];
            if (false != M('admin')->save($condition)) {
                return show_tip(1, "充值成功", null, U('UserAdmin'));
            } else {
                return show_tip(0, "修改失败");
            }
        } else {
            $id = I('id', 0, 'intval');
            $userinfo = M('admin')->find($id);
            $this->assign("username",$userinfo['username']);
            $this->assign("id", $id);
            $this->assign("type", "用户续费");
            $this->display();
        }
    }

    public function KeywordEdit(){
        if($_POST){
            $data = I('post.', '');
            $id = $data['id'] = intval($data['id']);

            $priceoneold = $data['priceoneold'];
            $pricetwoold = $data['pricetwoold'];
            $priceone = $data['baidu1'];
            $pricetwo = $data['baidu2'];

            if($priceone < $priceoneold || $pricetwo < $pricetwoold){
                return show_tip(0,"价格不能小于原来价格");
            }

            $condition = array();
            $condition['priceone'] = $priceone;
            $condition['pricetwo'] = $pricetwo;
            $condition['id'] = $id;
            if (false !== M('seo_keyword')->save($condition)) {
                return show_tip(1, '修改成功', null, U('UserAdmin'));
            } else {
                return show_tip(0, '修改失败');
            }

        }else{
            $data = I('get.', '');
            $keyword = $data['keyword'];
            $platformname = $data['platformname'];
            $websiteurl = $data['websiteurl'];
            $keywordid = $data['keywordid'];
            $priceone = $data['priceone'];
            $pricetwo = $data['pricetwo'];
            $userid = $data['userid'];

            $this->assign('priceone',$priceone);
            $this->assign('pricetwo',$pricetwo);
            $this->assign('keyword',$keyword);
            $this->assign('platformname',$platformname);
            $this->assign('websiteurl',$websiteurl);
            $this->assign('keywordid',$keywordid);
            $this->assign('userid',$userid);
            $this->assign('type', '修改关键词价格');
            $this->display();
        }
    }
}