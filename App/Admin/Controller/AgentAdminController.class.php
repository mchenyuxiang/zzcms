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

        session('zzcms_useradmin_page', $page);
        session('zzcms_useradmin_pagesize', $pageSize);
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
            if ($updatetime < ($timetodaystr - 7200)) {
                $recharge_sql = "SELECT SUM(priceone+pricetwo) AS cost FROM zzcms_seo_costdetail WHERE userid = " . $v['id'] . " and createtime > " . $timetoday;
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
            session('zzcms_listinfo_userid_admin', $data['userid']);
            session('zzcms_userlistinfo_page', $page);
            session('zzcms_userlistinfo_pagesize', $pageSize);
        }
        $listinfo = M()
            ->table('zzcms_seo_keyword as c')
            ->join('LEFT JOIN zzcms_seo_platform as b on c.platformid = b.id')
            ->join('LEFT JOIN (SELECT * FROM zzcms_seo_costdetail ORDER BY createtime DESC) AS a on a.keywordid = c.id')
            ->join('LEFT JOIN zzcms_seo_web as d on c.webid = d.id')
            ->field('c.userid,c.id as keywordid,b.platformname,CASE WHEN min(a.rank)  IS NULL  THEN \'暂无排名信息\' when min(a.rank) = 100 then \'50名之后\' ELSE min(a.rank) END AS rank,c.name,CASE WHEN SUM(a.`priceone`+a.pricetwo) IS NULL THEN \'暂无更新\' ELSE SUM(a.priceone+a.pricetwo) END AS totalprice,d.`websiteurl` ,c.priceone,c.pricetwo,d.websitename')
            ->where(array('c.userid' => $userid))->order('a.rank asc')->group('c.name,platformname')->limit($offset, $pageSize)->select();
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
        $this->assign('userid', $userid);
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
        $this->assign('userid', $userid);
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


            $data = I('post.', '');
            if (!isset($_POST['username']) || !$_POST['username']) {
                return show_tip(0, '用户名不能为空');
            }

            $userCount = M('admin')->where(array('username' => $_POST['username']))->count();
            if ($userCount > 0) {
                return show_tip(0, '用户名已经存在!');
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
            //防止重复提交 如果重复提交跳转至相关页面
            if (!checkToken($_POST['TOKEN'])) {
//                $this->redirect('index/index');
                return show_tip(0, '已经成功提交，请刷新页面!');
            }
            $data['password'] = getMd5Password($data['newpassword']);
            $data['recharge'] = $data['balance'];
            $data['createtime'] = date('Y-m-d', time());
            $userid = M('admin')->data($data)->add();
            if ($userid) {

                if (!isset($_POST['websitename']) || !$_POST['websitename']) {
                    return show_tip(0, '网站名称不能为空');
                }
                if (!isset($_POST['websiteurl']) || !$_POST['websiteurl']) {
                    return show_tip(0, '网站域名不能为空');
                }
                if (!isset($_POST['keyword']) || !$_POST['keyword']) {
                    return show_tip(0, '请填写关键字');
                }

                if (!validateURL($_POST['websiteurl'])) {
                    return show_tip(0, '请输入合法域名');
                }

                $_POST['createtime'] = date('Y-m-d H:i:s', time());
                $_POST['userid'] = $userid;
                $webid = M("seo_web")->data($_POST)->add();
                if ($webid) {
                    $platformidArr = $_POST['platformid'];
                    $arrplat = explode(",", $platformidArr);
                    $keywordArr = $_POST['keyword'];
//                    $firstkey = substr($keywordArr, 0, 1);
//                    if ($firstkey == '&') {
//                        return show_tip(0, '请填写关键字');
//                    }
                    $arrkey = explode("|", $keywordArr);
                    $countkeyword = 0;
                    foreach ($arrkey as $key) {
                        foreach ($arrplat as $plat) {
                            $insertdata = array();
                            $insertdata['name'] = $key;
                            $insertdata['webid'] = $webid;
                            $insertdata['userid'] = $userid;
                            $insertdata['platformid'] = $plat;
                            $insertdata['createtime'] = date('Y-m-d H:i:s', time());
                            $keywordid = M('seo_keyword')->data($insertdata)->add();
                        }
                        if ($keywordid) {
                            $countkeyword = $countkeyword + 1;
                        } else {
                            return show_tip(0, '新增失败', $keywordid);
                        }
                    }
                    if ($countkeyword == count($arrkey)) {
                        return show_tip(1, '新增成功', $countkeyword, U('ListInfo', array('userid' => $userid)));
                    }
                    return show_tip(0, '新增失败', $countkeyword);
                }
            }
            return show_tip(0, '新增失败', $userid);
        } else {
            creatToken();
            $platforminfo = M('seo_platform')->select();
            $this->assign("platform", $platforminfo);
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


        if ((M('seo_keyword')->where(array('userid' => $id))->delete()
            && M('seo_web')->where(array('userid' => $id))->delete()
            && M('admin')->delete($id))
        ) {

            return show_tip(1, '删除成功', null, U('UserAdmin'));
        } elseif ((M('seo_keyword')->where(array('userid' => $id))->count()) == 0) {
            if (M('admin')->delete($id)) {
                return show_tip(1, '删除成功', null, U('UserAdmin'));
            } else {
                return show_tip(0, "删除用户失败");
            }

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
            if (!isset($_POST['recharge']) || !$_POST['recharge']) {
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
            $this->assign("username", $userinfo['username']);
            $this->assign("id", $id);
            $this->assign("type", "用户续费");
            $this->display();
        }
    }

    public function KeywordEdit()
    {
        if ($_POST) {
            $data = I('post.', '');
            $id = $data['id'] = intval($data['id']);

//            $priceoneold = $data['priceoneold'];
//            $pricetwoold = $data['pricetwoold'];
            $priceone = $data['baidu1'];
            $pricetwo = $data['baidu2'];

//            if($priceone < $priceoneold || $pricetwo < $pricetwoold){
//                return show_tip(0,"价格不能小于原来价格");
//            }

            $condition = array();
            $condition['priceone'] = $priceone;
            $condition['pricetwo'] = $pricetwo;
            $condition['id'] = $id;
            if (false !== M('seo_keyword')->save($condition)) {
            } else {
                return show_tip(0, '修改失败');
            }

        } else {
            $data = I('get.', '');
            $keyword = $data['keyword'];
            $platformname = $data['platformname'];
            $websiteurl = $data['websiteurl'];
            $keywordid = $data['keywordid'];
            $priceone = $data['priceone'];
            $pricetwo = $data['pricetwo'];
            $userid = $data['userid'];

            $this->assign('priceone', $priceone);
            $this->assign('pricetwo', $pricetwo);
            $this->assign('keyword', $keyword);
            $this->assign('platformname', $platformname);
            $this->assign('websiteurl', $websiteurl);
            $this->assign('keywordid', $keywordid);
            $this->assign('userid', $userid);
            $this->assign('type', '修改关键词价格');
            $this->display();
        }
    }

    /**
     * 关键字删除
     */
    public function KeywordDel()
    {

        $data = I('post.', '');
        $id = $data['id'];
        $userid = session('zzcms_listinfo_userid_admin');

        if (M('seo_keyword')->delete($id)) {

            return show_tip(1, '删除成功', null, U('ListInfo', array('userid' => $userid)));
//            return show_tip(1,'删除成功');
        } else {
            return show_tip(0, "删除失败");
        }
    }

    public function AddKeyword()
    {
//        if ($_GET) {
//            $data = I('get.', '');
//
//            $baiduindex = $data['baiduindex'];
//            $baidumobileindex = $data['baidumobileindex'];
//            $keyword = $data['keyword'];
//            $webid = $data['webid'];
//            $websitearray = M('seo_web')->where(array('id' => $webid))->select();
//            $this->assign('websiteurl', $websitearray[0]['websiteurl']);
////            $this->assign('userid',$websitearray[0]['userid']);
////            print_r($keyword.'------'.$webid);
//
//            $baiduprice = $baiduindex / 6;
//            $baidumobileprice = $baidumobileindex / 5;
//
//            if ($baiduprice <= 5) {
//                $baiduprice = 5;
//            } elseif ($baiduprice >= 50) {
//                $baiduprice = 50;
//            }
//
//            if ($baidumobileprice <= 5) {
//                $baidumobileprice = 5;
//            } elseif ($baidumobileprice >= 60) {
//                $baidumobileprice = 60;
//            }
//
//            $sou360 = round($baidumobileprice / 3, 2);
//            if ($sou360 <= 3) {
//                $sou360 = 3;
//            } elseif ($sou360 >= 30) {
//                $sou360 = 30;
//            }
//
//            $sougou = round($baiduprice / 6, 2);
//            if ($sougou <= 1.5) {
//                $sougou = 1.5;
//            } elseif ($sougou >= 20) {
//                $sougou = 20;
//            }
//
//            $shenma = round($baiduprice / 7, 2);
//            if ($shenma <= 1) {
//                $shenma = 1;
//            } elseif ($shenma >= 15) {
//                $shenma = 15;
//            }
//            $biying = round($baiduprice / 7, 2);
//            if ($biying <= 1) {
//                $biying = 1;
//            } elseif ($biying >= 15) {
//                $biying = 15;
//            }
//            $this->assign('baiduprice', number_format($baiduprice, 2));
//            $this->assign('baidumobileprice', number_format($baidumobileprice, 2));
//            $this->assign('sou360', $sou360);
//            $this->assign('sougou', $sougou);
//            $this->assign('google', number_format($baiduprice, 2));
//            $this->assign('shenma', $shenma);
//            $this->assign('biying', $biying);
//            $this->assign('keyword', $keyword);
//            $this->assign('webid', $webid);
//            $this->assign('type', '添加关键词');
//            $this->display();
//        }
        if ($_POST) {
            $data = I('post.', '');
            $userid = $data['userid'];
            $webid = $data['webid'];
            $platformArray = M('seo_web')->where(array('id' => $webid))->select();
            $platformidArr = $platformArray[0]['platformid'];
            $arr = explode(",", $platformidArr);
            $countkeyword = 0;
            $beforesearch = M('seo_keyword')->where(array('name' => $data['keyword'], 'webid' => $webid
            , 'userid' => $userid))->count();
            if ($beforesearch > 0) {
                return show_tip(0, '该用户已经有关键词，无需重复添加', $beforesearch);
            }
            $keywordold = $data['keywordold'];
//            if($keywordold != $data['keyword']){
//                return show_tip(0, '添加关键字与查询关键字不同，不能添加', $keywordold);
//            }
            foreach ($arr as $u) {
                $insertdata = array();
                $insertdata['name'] = $data['keyword'];
                $insertdata['webid'] = $webid;
                $insertdata['userid'] = $userid;
                $insertdata['platformid'] = $u;
                $insertdata['createtime'] = date('Y-m-d H:i:s', time());
                if ($u == '1') {
                    $insertdata['priceone'] = $data['baidu1'];
                    $insertdata['pricetwo'] = $data['baidu2'];
                } elseif ($u == '2') {
                    $insertdata['priceone'] = $data['sou3601'];
                    $insertdata['pricetwo'] = $data['sou3602'];

                } elseif ($u == '3') {
                    $insertdata['priceone'] = $data['sougou1'];
                    $insertdata['pricetwo'] = $data['sougou2'];

                } elseif ($u == '4') {
                    $insertdata['priceone'] = $data['google1'];
                    $insertdata['pricetwo'] = $data['google2'];

                } elseif ($u == '5') {
                    $insertdata['priceone'] = $data['baidumobile1'];
                    $insertdata['pricetwo'] = $data['baidumobile2'];

                } elseif ($u == '6') {
                    $insertdata['priceone'] = $data['shenma1'];
                    $insertdata['pricetwo'] = $data['shenma2'];

                } elseif ($u == '7') {
                    $insertdata['priceone'] = $data['biying1'];
                    $insertdata['pricetwo'] = $data['biying2'];
                }
                $keywordid = M('seo_keyword')->data($insertdata)->add();
                if ($keywordid) {
                    $countkeyword = $countkeyword + 1;
                } else {
                    return show_tip(0, '新增失败', $keywordid);
                }
            }
            if ($countkeyword == count($arr)) {
                return show_tip(1, '新增成功', $countkeyword, U('AddKeyword',array('userid'=>$userid)));
            }
            return show_tip(0, '新增失败', $countkeyword);

        } else {
            $data=I('get.','');
            $userid = $data['userid'];
            $websitearray = M('seo_web')->where(array('userid'=>$userid))->select();
            $this->assign('websiteurl', $websitearray[0]['websiteurl']);
            $this->assign('webid',$websitearray[0]['id']);
            $this->assign('userid', $userid);
            $this->assign('type', '添加关键词');
            $this->display();
        }
    }
//    public function seachprice()
//    {
//
//        $webid = $_POST['webid'];
//        $keyword = $_POST['keyword'];
////        dump($keyword);
//
//        $key = c402da805c1c46f8a00d1c9f477c6a6f;
//        $url = 'http://api.91cha.com/index?key=' . $key . '&kws=' . urlencode($keyword);
//        $ch = curl_init();
//        $this_header = array(
//            "content-type: application/x-www-form-urlencoded; charset=UTF-8"
//        );
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $this_header);
//        curl_setopt($ch, CURLOPT_URL, $url);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 获取数据返回
//        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true); // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
//        $res = curl_exec($ch);
//        $resdecode = json_decode($res);
//
////        dump($resdecode);
//        $baiduindex = $resdecode->data[0]->allindex;
//        $baidumobileindex = $resdecode->data[0]->mobileindex;
//        $resstaus = $resdecode->state;
//        if ($resstaus == 1) {
//            return show_tip(1, '查询成功', $resstaus, U('AddKeyword', array('webid' => $webid, 'keyword' => $keyword, 'baiduindex' => $baiduindex, 'baidumobileindex' => $baidumobileindex)));
//        }
//        return show_tip(0, '查询失败', $resstaus);
//    }
}