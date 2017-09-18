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
        $userinfo = M('admin')->order('id asc')->limit($offset, $pageSize)->select();
//        print_r($userinfo);
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
//            $updatetime = strtotime($balanceArr[0]['updatetime']);
            $timetoday = date("Y-m-d", time());
            $data['updatetime'] = $timetoday;
            $listallinfo[$key] = $listinfo;
            $listallinfo[$key]['balance'] = $balanceT;
        }
        $this->assign("listinfo", $listallinfo);
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
        if ($data['platformid'] == null) {
            $condition = array('c.userid' => $userid);
            $cntsql = " c.userid = '" . $userid . "' ";
        } else {
            $condition = array('c.userid' => $userid, 'c.platformid' => $data['platformid']);
            $cntsql = " c.userid = '" . $userid . "' and c.platformid = '" . $data['platformid'] . "' ";
        }
        $listinfo = M()
            ->table('zzcms_seo_keyword as c')
            ->join('LEFT JOIN zzcms_seo_platform as b on c.platformid = b.id')
            ->join('LEFT JOIN (SELECT * FROM zzcms_seo_costdetail ORDER BY createtime DESC) AS a on a.keywordid = c.id')
            ->join('LEFT JOIN zzcms_seo_web as d on c.webid = d.id')
            ->field('c.userid,c.id as keywordid,b.platformname,CASE WHEN a.rank  IS NULL  THEN \'暂无排名信息\' when a.rank = 100 then \'50名之后\' ELSE a.rank END AS rank,c.name,CASE WHEN SUM(a.`priceone`+a.pricetwo) IS NULL THEN \'暂无更新\' ELSE SUM(a.priceone+a.pricetwo) END AS totalprice,d.`websiteurl` ,c.priceone,c.pricetwo,d.websitename')
            ->where($condition)->order('c.name DESC,a.rank asc')->group('c.name,platformname')->limit($offset, $pageSize)->select();
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
WHERE " . $cntsql . " 
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
            ->table('zzcms_seo_costdetail as a')
            ->join('LEFT JOIN zzcms_seo_platform as b on a.platformid = b.id')
            ->join('LEFT JOIN zzcms_seo_keyword as c on a.`keywordid` = c.id')
            ->join('LEFT JOIN zzcms_seo_web as d on a.webid = d.id')
            ->field('a.userid,a.id,b.platformname,a.rank,a.keywordname as name,(a.`priceone`+a.`pricetwo`) as priceone,DATE_FORMAT( a.`createtime`, \'%Y-%m-%d\') AS createtime,d.`websiteurl`')
            ->where("a.userid=%d and (a.priceone+a.pricetwo)!=0", array($userid))->order('createtime DESC,b.id ASC')->limit($offset, $pageSize)->select();
        $cateCount = M()
            ->table('zzcms_seo_costdetail as a')
            ->join('LEFT JOIN zzcms_seo_platform as b on a.platformid = b.id')
            ->join('LEFT JOIN zzcms_seo_keyword as c on a.`keywordid` = c.id')
            ->join('LEFT JOIN zzcms_seo_web as d on a.webid = d.id')
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
                        $priceresult = $this->seachzhanprice($key);
                        foreach ($arrplat as $plat) {
                            $insertdata = array();
                            $insertdata['name'] = $key;
                            $insertdata['webid'] = $webid;
                            $insertdata['userid'] = $userid;
                            $insertdata['platformid'] = $plat;
                            if ($plat == '1') {
                                $insertdata['priceone'] = $priceresult['baidu1'];
                                $insertdata['pricetwo'] = $priceresult['baidu2'];
                            } elseif ($plat == '2') {
                                $insertdata['priceone'] = $priceresult['haosou1'];
                                $insertdata['pricetwo'] = $priceresult['haosou2'];

                            } elseif ($plat == '3') {
                                $insertdata['priceone'] = $priceresult['sogou1'];
                                $insertdata['pricetwo'] = $priceresult['sogou2'];

                            } elseif ($plat == '4') {
                                $insertdata['priceone'] = $priceresult['sogou1'];
                                $insertdata['pricetwo'] = $priceresult['sogou2'];

                            } elseif ($plat == '5') {
                                $insertdata['priceone'] = $priceresult['baidumobile1'];
                                $insertdata['pricetwo'] = $priceresult['baidumobile2'];

                            } elseif ($plat == '6') {
                                $insertdata['priceone'] = $priceresult['shenma1'];
                                $insertdata['pricetwo'] = $priceresult['shenma2'];

                            } elseif ($plat == '7') {
                                $insertdata['priceone'] = $priceresult['haosou1'];
                                $insertdata['pricetwo'] = $priceresult['haosou2'];
                            }
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

    /**
     * 一键修改关键字价格使得价格与云客网一致
     */
    public function QuicUpdatePrice()
    {

        if ($_POST) {

            $userid = session('zzcms_listinfo_userid_admin');
            $keywrodArr = M('seo_keyword')->where(array('userid' => $userid))->select();
            $keywrodCount = M('seo_keyword')->where(array('userid' => $userid))->count();
            $keycnt = 0;
            $selectPrice = new WebSettingController();
            foreach ($keywrodArr as $item) {
                $updateData = Array();
                $updateData['id'] = $item['id'];
                $priceresult = $selectPrice->seachzhanprice($item['name']);

                if ($item['platformid'] == '1') {
                    $updateData['priceone'] = $priceresult['baidu1'];
                    $updateData['pricetwo'] = $priceresult['baidu2'];
                } elseif ($item['platformid'] == '2') {
                    $updateData['priceone'] = $priceresult['haosou1'];
                    $updateData['pricetwo'] = $priceresult['haosou2'];
                } elseif ($item['platformid'] == '3') {
                    $updateData['priceone'] = $priceresult['sogou1'];
                    $updateData['pricetwo'] = $priceresult['sogou2'];
                } elseif ($item['platformid'] == '4') {
                    $updateData['priceone'] = $priceresult['sogou1'];
                    $updateData['pricetwo'] = $priceresult['sogou2'];
                } elseif ($item['platformid'] == '5') {
                    $updateData['priceone'] = $priceresult['baidumobile1'];
                    $updateData['pricetwo'] = $priceresult['baidumobile2'];
                } elseif ($item['platformid'] == '6') {
                    $updateData['priceone'] = $priceresult['shenma1'];
                    $updateData['pricetwo'] = $priceresult['shenma2'];
                } elseif ($item['platformid'] == '7') {
                    $updateData['priceone'] = $priceresult['haosou1'];
                    $updateData['pricetwo'] = $priceresult['haosou2'];
                }
                if (false != M('seo_keyword')->save($updateData)) {
                    $keycnt = $keycnt + 1;
                }

            }
            if ($keycnt == $keywrodCount) {
                return show_tip(1, "修改关键字成功", null, U('ListInfo', array('userid' => $userid)));
            } else {
                return show_tip(0, "已同步");
            }
        }
    }

    /**
     * 查询云客关键词价格
     */
    public function seachyunprice($keyword)
    {


//        dump($keyword);
        $url = 'http://seo.hnqzwfx.com/channel/singlekeywordquery?word=' . urlencode($keyword);
        $ch = curl_init();
        $this_header = array(
            "content-type: application/x-www-form-urlencoded; charset=UTF-8"
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this_header);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 获取数据返回
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true); // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
        $res = curl_exec($ch);
        $res = stripslashes($res);
        $arr = explode("<link", $res);
        $res = $arr[0];
        $res = trim($res, '"');
        $resdecode = json_decode($res);
//        dump($resdecode);
//        $resstaus = $resdecode->msg;
//        if ($resstaus == 'succeed!') {
//            return $resdecode;
//        }
        return $resdecode;
    }

    /**
     * 查询云客关键词价格
     */
    public function seachzhanprice($keyword)
    {
//        dump($keyword);
        $url='www.baidu.com';
        $id = '4c9a02ee5e4041f4';
        $m=md5(md5(md5(md5($keyword))).mb_strlen($keyword,'UTF8').$id.$keyword.$url);
        $apiurl = 'http://seo.zhantengwang.com/searchDesc.do?keyword='.urlencode($keyword).'&url='.$url.'&id='.$id.'&m='.$m.'&_'.time();
//        $post_data = array("keyword" => $keyword,"time" => date("Y-m-d"));
        $ch = curl_init();
//        $this_header = array(
//            "content-type: application/x-www-form-urlencoded; charset=UTF-8"
//        );
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $this_header);
        curl_setopt($ch, CURLOPT_URL, $apiurl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 获取数据返回
//        curl_setopt($ch, CURLOPT_POST, 1);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
//        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true); // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
        $res = curl_exec($ch);
        $userReg = substr($res, 5,-2);
//        $res = stripslashes($res);
//        $arr = explode("<link", $res);
//        $res = $arr[0];
//        $res = trim($res, '"');
        $resdecode = json_decode($userReg,True);
        $resT = json_decode($resdecode['Susan'],TRUE);
        $resF = array();
        $resF['baidu1'] = (float)ceil($resT['baidu1'])*1.5;
        $resF['baidu2'] = (float)ceil($resT['baidu2'])*1.5;
        $resF['haosou1'] =  (float)ceil($resT['haosou1'])*1.5;
        $resF['haosou2'] = (float)ceil($resT['haosou2'])*1.5;
        $resF['sogou1'] = (float)ceil($resT['sogou1'])*1.5;
        $resF['sogou2'] = (float)ceil($resT['sogou2'])*1.5;
        $resF['baidumobile1'] = (float)ceil($resT['baidumobile1'])*1.5;
        $resF['baidumobile2'] = (float)ceil($resT['baidumobile2'])*1.5;
        $resF['shenma1'] = (float)ceil($resT['shenma1'])*1.5;
        $resF['shenma2'] = (float)ceil($resT['shenma2'])*1.5;
//        dump($resdecode);
//        $resstaus = $resdecode->msg;
//        if ($resstaus == 'succeed!') {
//            return $resdecode;
//        }
        return $resF;
    }

    public function edit()
    {
        if ($_POST) {

            $data = I('post.', '');
            if (!isset($_POST['username']) || !$_POST['username']) {
                return show_tip(0, '用户名不能为空');
            }
            if (false !== M('admin')->save($data)) {
                $userid = $data['id'];
                $userweb = M('seo_web')->where(array('userid' => $userid))->find();
                if ($userweb) {
                    $platformidold = $userweb['platformid'];
                    $keywordsql = "select distinct(name) as keyword from zzcms_seo_keyword where platformid in (" . $platformidold . ") and userid = " . $data['id'];
                    $keywordAll = M()->query($keywordsql);
                    $platformidArr = $data['platformid'];
                    $delKeysql = "delete from zzcms_seo_keyword where platformid not in (" . $platformidArr . ") and userid = " . $data['id'];
                    M()->execute($delKeysql);
                    $arr = explode(",", $platformidArr);
                    $arrold = explode(",", $platformidold);
                    $webidArr = M('seo_web')->where(array('userid' => $data['id']))->select();
                    $webid = $webidArr[0]['id'];
                    foreach ($arr as $u) {
                        $flag = 1;
                        foreach ($arrold as $v) {
                            if ($u == $v) {
                                $flag = 1;
                                break;
                            }
                            $flag = 0;
                        }
                        if ($flag == 0) {
                            foreach ($keywordAll as $keyword) {
                                $insertdata = array();
                                $insertdata['name'] = $keyword['keyword'];
                                $insertdata['webid'] = $webid;
                                $insertdata['userid'] = $data['id'];
                                $insertdata['platformid'] = $u;
                                $insertdata['createtime'] = date('Y-m-d H:i:s', time());
                                $insertdata['priceone'] = 10;
                                $insertdata['pricetwo'] = 5;
                                M('seo_keyword')->data($insertdata)->add();
                            }
                            $flag = 1;
                        }
                    }
                    $resultsql = "update zzcms_seo_web set platformid='" . $platformidArr . "' where userid = " . $data['id'];
                    M()->execute($resultsql);

                    $page = session('zzcms_useradmin_page');
                    $pageSize = session('zzcms_useradmin_pagesize');
                    return show_tip(1, '修改成功', null, U('UserAdmin', array('page' => $page, 'pageSize' => $pageSize)));
                }
            } else {
                return show_tip(0, '修改失败');
            }
        } else {
//            $data = I('get.', '');
            $id = I('id', 0, 'intval');
            $userinfo = M('admin')->find($id);
//            print_r($userinfo);
            $platforminfo = M('seo_platform')->select();
            $userweb = M('seo_web')->where(array('userid' => $id))->select();
//            print_r($userweb);
            if ($userweb) {
                $platformid = $userweb[0]['platformid'];
                $platformidRes = explode(',', $platformid);
                foreach ($platformidRes as $i => $platvalue) {
                    foreach ($platforminfo as $key => $value) {
                        if ($platforminfo[$key]['id'] == $platformidRes[$i]) {
                            $platforminfo[$key]['checked'] = 'checked';
                            break;
                        }
                    }
                }
                $this->assign("platformid", $platformidRes);
            }
            $this->assign("platform", $platforminfo);
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
            $userid = session('zzcms_listinfo_userid_admin');

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
                return show_tip(1, '修改成功', null, U('ListInfo', array('userid' => $userid)));
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

    /**
     * 管理员添加关键字
     */
    public function AddKeyword()
    {
        if ($_POST) {
            $data = I('post.', '');
            $userid = $data['userid'];
            $webid = $data['webid'];
//            $platformArray = M('seo_web')->where(array('id' => $webid))->select();
//            $platformidArr = $platformArray[0]['platformid'];
            $platformidArr = $data['platformid'];
            $arr = explode(",", $platformidArr);
            $countkeyword = 0;
//            $beforesearch = M('seo_keyword')->where(array('name' => $data['keyword'], 'webid' => $webid
//            , 'userid' => $userid))->count();
//            if ($beforesearch > 0) {
//                return show_tip(0, '该用户已经有关键词，无需重复添加', $beforesearch);
//            }
            $keywordold = $data['keywordold'];
//            if($keywordold != $data['keyword']){
//                return show_tip(0, '添加关键字与查询关键字不同，不能添加', $keywordold);
//            }
            $priceresult = $this->seachzhanprice($data['keyword']);
            foreach ($arr as $u) {
                $insertdata = array();
                $insertdata['name'] = $data['keyword'];
                $insertdata['webid'] = $webid;
                $insertdata['userid'] = $userid;
                $insertdata['platformid'] = $u;
                $insertdata['createtime'] = date('Y-m-d H:i:s', time());
                $beforesearch = M('seo_keyword')->where(array('name' => $data['keyword'], 'webid' => $webid
                , 'userid' => $userid, 'platformid' => $u))->count();
                if ($beforesearch > 0) {
                    $countkeyword = $countkeyword + 1;
                    continue;
                }
                if ($u == '1') {
                    $insertdata['priceone'] = $priceresult['baidu1'];
                    $insertdata['pricetwo'] = $priceresult['baidu2'];
                } elseif ($u == '2') {
                    $insertdata['priceone'] = $priceresult['haosou1'];
                    $insertdata['pricetwo'] = $priceresult['haosou2'];

                } elseif ($u == '3') {
                    $insertdata['priceone'] = $priceresult['sogou1'];
                    $insertdata['pricetwo'] = $priceresult['sogou2'];

                } elseif ($u == '4') {
                    $insertdata['priceone'] = $priceresult['sogou1'];
                    $insertdata['pricetwo'] = $priceresult['sogou2'];

                } elseif ($u == '5') {
                    $insertdata['priceone'] = $priceresult['baidumobile1'];
                    $insertdata['pricetwo'] = $priceresult['baidumobile2'];

                } elseif ($u == '6') {
                    $insertdata['priceone'] = $priceresult['shenma1'];
                    $insertdata['pricetwo'] = $priceresult['shenma2'];

                } elseif ($u == '7') {
                    $insertdata['priceone'] = $priceresult['haosou1'];
                    $insertdata['pricetwo'] = $priceresult['haosou2'];
                }

                $keywordid = M('seo_keyword')->data($insertdata)->add();
                if ($keywordid) {
                    $countkeyword = $countkeyword + 1;
                } else {
                    return show_tip(0, '新增失败', $keywordid);
                }
            }
            if ($countkeyword == count($arr)) {
                return show_tip(1, '新增成功', $countkeyword, U('AddKeyword', array('userid' => $userid)));
            }
            return show_tip(0, '新增失败', $countkeyword);

        } else {
            $data = I('get.', '');
            $userid = $data['userid'];
            $websitearray = M('seo_web')->where(array('userid' => $userid))->select();
            $platforminfo = M('seo_platform')->select();
            $this->assign("platform", $platforminfo);
            $this->assign('websiteurl', $websitearray[0]['websiteurl']);
            $this->assign('webid', $websitearray[0]['id']);
            $this->assign('userid', $userid);
            $this->assign('type', '添加关键词');
            $this->display();
        }
    }

    /**
     * 用户关键字下载
     */
    public function DownloadKeyword()
    {
        $data = I('get.', '');
        $userid = $data['userid'];
        $listinfo = M()
            ->table('zzcms_seo_keyword as a')
            ->join('LEFT JOIN zzcms_seo_platform as b on a.platformid = b.id')
            ->field('a.id AS keywordid,a.platformid,a.name,b.platformname,a.priceone,a.pricetwo')
            ->where("a.userid=%d", array($userid))->order('name ASC')->select();
//先设置好表头和名称
        $filename = "keyword";
//        $headArr = array("序号", "keywordid", "platformid", "name", "platformname", "priceone", "pricetwo");
        $headArr = array("序号", "关键词", "平台", "首页价格", "第二页价格");
//下面是从表中查询到的一组二维数组数据$data:

//下面这个必须要和$headArr一一对应起来
        foreach ($listinfo as $k => $v) {
            $listinfo[$k] = array(
                $k + 1,//从1开始
//                $v['keywordid'],
//                $v['platformid'],
                $v['name'],
                $v['platformname'],
                $v['priceone'],
                $v['pricetwo']
            );
        }

//最后执行getExcel方法
        getExcel($filename, $headArr, $listinfo);
    }

    /**
     * 下载扣费记录
     */
    public function DownloadCostDetail()
    {
        $data = I('get.', '');
        $userid = $data['userid'];
        $listinfo = M()
            ->table('zzcms_seo_keyword as c')
            ->join('LEFT JOIN zzcms_seo_platform as b on c.platformid = b.id')
            ->join('LEFT JOIN zzcms_seo_costdetail as a on a.`keywordid` = c.id')
            ->join('LEFT JOIN zzcms_seo_web as d on c.webid = d.id')
            ->field('a.userid,a.id,b.platformname,a.rank,c.name,(a.`priceone`+a.`pricetwo`) as priceone,DATE_FORMAT( a.`createtime`, \'%Y-%m-%d\') AS createtime,d.`websiteurl`')
            ->where("a.userid=%d and (a.priceone+a.pricetwo)!=0", array($userid))->order('createtime DESC,b.id ASC')->select();
//先设置好表头和名称
        $filename = "CostDetail";
//        $headArr = array("序号", "keywordid", "platformid", "name", "platformname", "priceone", "pricetwo");
        $headArr = array("序号", "关键词", "搜索引擎", "网站域名", "扣费金额", "扣费时间");
//下面是从表中查询到的一组二维数组数据$data:

//下面这个必须要和$headArr一一对应起来
        foreach ($listinfo as $k => $v) {
            $listinfo[$k] = array(
                $k + 1,//从1开始
//                $v['keywordid'],
//                $v['platformid'],
                $v['name'],
                $v['platformname'],
                $v['websiteurl'],
                $v['priceone'],
                $v['createtime']
            );
        }

//最后执行getExcel方法
        getExcel($filename, $headArr, $listinfo);
    }


    public function SearchByName()
    {

        if (IS_POST) {
            $searchName = $_POST['searchname'];
            $searchSql = "select count(*) as count from zzcms_admin a left join zzcms_seo_web b on a.id=b.userid where (a.companyname like '%".$searchName."%' or b.websitename like '%".$searchName."%') order by a.id asc";
            $userinfo = M()->query($searchSql);
            if($userinfo[0]['count'] == 0){
                return show_tip(0, '没有搜索到此用户');
            }else{
                return show_tip(1, '搜索成功', null, U('SearchByName', array('searchName' => $searchName, 'type' => '搜索结果')));
            }

        } else {
            $searchName = $_GET['searchName'];
            $searchSql = "select a.id,a.username,a.companyname,a.balance,a.recharge,a.updatetime from zzcms_admin a left join zzcms_seo_web b on a.id=b.userid where (a.companyname like '%".$searchName."%' or b.websitename like '%".$searchName."%') order by a.id asc";
            $userinfo = M()->query($searchSql);
//            $userinfo = M('admin')->where(array('companyname'=>array('like',"'%".$searchName."%'")))->order('id asc')->select();
//        print_r($userinfo);
            $listallinfo = array();
            foreach ($userinfo as $key => $v) {
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
//            $updatetime = strtotime($balanceArr[0]['updatetime']);
                $timetoday = date("Y-m-d", time());
                $data['updatetime'] = $timetoday;
                $listallinfo[$key] = $listinfo;
                $listallinfo[$key]['balance'] = $balanceT;
            }
        $this->assign("listinfo", $listallinfo);
        $this->assign("type", "搜索结果");
        $this->display();
        }
    }
}