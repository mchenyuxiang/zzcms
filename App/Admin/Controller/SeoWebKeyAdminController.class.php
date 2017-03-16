<?php
/**
 * Created by PhpStorm.
 * User: yuxiang
 * Date: 2017/2/7
 * Time: 19:37
 */
namespace Admin\Controller;

use Common\Lib\Page;

class SeoWebKeyAdminController extends CommonController
{
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
    }

    /**
     * 添加关键词
     */
    public function add()
    {
        if ($_GET) {
            $data = I('get.', '');

            $baiduindex = $data['baiduindex'];
            $baidumobileindex = $data['baidumobileindex'];
            $keyword = $data['keyword'];
            $webid = $data['webid'];
            $websitearray = M('seo_web')->where(array('id' => $webid))->select();
            $this->assign('websiteurl', $websitearray[0]['websiteurl']);
//            print_r($keyword.'------'.$webid);

            $baiduprice = $baiduindex / 6;
            $baidumobileprice = $baidumobileindex / 5;

            if ($baiduprice <= 5) {
                $baiduprice = 5;
            } elseif ($baiduprice >= 50) {
                $baiduprice = 50;
            }

            if ($baidumobileprice <= 5) {
                $baidumobileprice = 5;
            } elseif ($baidumobileprice >= 60) {
                $baidumobileprice = 60;
            }

            $sou360 = round($baidumobileprice / 3, 2);
            if ($sou360 <= 3) {
                $sou360 = 3;
            } elseif ($sou360 >= 30) {
                $sou360 = 30;
            }

            $sougou = round($baiduprice / 6, 2);
            if ($sougou <= 1.5) {
                $sougou = 1.5;
            } elseif ($sougou >= 20) {
                $sougou = 20;
            }

            $shenma = round($baiduprice / 7, 2);
            if ($shenma <= 1) {
                $shenma = 1;
            } elseif ($shenma >= 15) {
                $shenma = 15;
            }
            $biying = round($baiduprice / 7, 2);
            if ($biying <= 1) {
                $biying = 1;
            } elseif ($biying >= 15) {
                $biying = 15;
            }
            $this->assign('baiduprice', number_format($baiduprice, 2));
            $this->assign('baidumobileprice', number_format($baidumobileprice, 2));
            $this->assign('sou360', $sou360);
            $this->assign('sougou', $sougou);
            $this->assign('google', number_format($baiduprice, 2));
            $this->assign('shenma', $shenma);
            $this->assign('biying', $biying);
            $this->assign('keyword', $keyword);
            $this->assign('webid', $webid);
            $this->assign('type', '添加关键词');
            $this->display();
        } elseif ($_POST) {
            $data = I('post.', '');
            $userid = session('zzcms_adm_userid');
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
            if($keywordold != $data['keyword']){
                return show_tip(0, '添加关键字与查询关键字不同，不能添加', $keywordold);
            }
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
                return show_tip(1, '新增成功', $countkeyword, U('add'));
            }
            return show_tip(0, '新增失败', $countkeyword);

        } else {
            $userid = session('zzcms_adm_userid');
            $condition['userid'] = $userid;
            $websitearray = M('seo_web')->where($condition)->select();
            $this->assign('websiteurl', $websitearray[0]['websiteurl']);
            $this->assign('webid',$websitearray[0]['id']);
            $this->assign('userid', $userid);
            $this->assign('type', '添加关键词');
            $this->display();
        }
    }

    public function seachprice()
    {

        $webid = $_POST['webid'];
        $keyword = $_POST['keyword'];
//        dump($keyword);

        $key = c402da805c1c46f8a00d1c9f477c6a6f;
        $url = 'http://api.91cha.com/index?key=' . $key . '&kws=' . urlencode($keyword);
        $ch = curl_init();
        $this_header = array(
            "content-type: application/x-www-form-urlencoded; charset=UTF-8"
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this_header);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 获取数据返回
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true); // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
        $res = curl_exec($ch);
        $resdecode = json_decode($res);

//        dump($resdecode);
        $baiduindex = $resdecode->data[0]->allindex;
        $baidumobileindex = $resdecode->data[0]->mobileindex;
        $resstaus = $resdecode->state;
        if ($resstaus == 1) {
            return show_tip(1, '查询成功', $resstaus, U('add', array('webid' => $webid, 'keyword' => $keyword, 'baiduindex' => $baiduindex, 'baidumobileindex' => $baidumobileindex)));
        }
        return show_tip(0, '查询失败', $resstaus);
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
        $listinfo = M()
            ->table('zzcms_seo_keyword as c')
            ->join('LEFT JOIN zzcms_seo_platform as b on c.platformid = b.id')
            ->join('LEFT JOIN (SELECT * FROM zzcms_seo_costdetail ORDER BY createtime DESC) AS a on a.keywordid = c.id')
            ->join('LEFT JOIN zzcms_seo_web as d on c.webid = d.id')
            ->field('c.userid,c.id as keywordid,b.platformname,CASE WHEN a.rank  IS NULL  THEN \'暂无排名信息\' when a.rank = 100 then \'50名之后\' ELSE a.rank END AS rank,c.name,CASE WHEN SUM(a.`priceone`+a.pricetwo) IS NULL THEN \'暂无更新\' ELSE SUM(a.priceone+a.pricetwo) END AS totalprice,d.`websiteurl` ,c.priceone,c.pricetwo,d.websitename')
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
WHERE c.userid = '".$userid."' 
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
     * 扣费记录详情
     */
    public function CostDetail()
    {
        $data = array();
        $page = $_REQUEST['p'] ? $_REQUEST['p'] : 1;
        $pageSize = $_REQUEST['pageSize'] ? $_REQUEST['pageSize'] : 20;

        $offset = ($page - 1) * $pageSize;
        $userid = session('zzcms_adm_userid');
        $listinfo = M()
            ->table('zzcms_seo_keyword as c')
            ->join('LEFT JOIN zzcms_seo_platform as b on c.platformid = b.id')
            ->join('LEFT JOIN zzcms_seo_costdetail as a on a.`keywordid` = c.id')
            ->join('LEFT JOIN zzcms_seo_web as d on c.webid = d.id')
            ->field('a.id,b.platformname,a.rank,c.name,(a.`priceone`+a.`pricetwo`) as priceone,DATE_FORMAT( a.`createtime`, \'%Y-%m-%d\') AS createtime,d.`websiteurl`')
            ->where("a.userid=%d and (a.priceone+a.pricetwo)!=0",array($userid))->order('createtime DESC,b.id ASC')->limit($offset, $pageSize)->select();
        $cateCount = M()
            ->table('zzcms_seo_keyword as c')
            ->join('LEFT JOIN zzcms_seo_platform as b on c.platformid = b.id')
            ->join('LEFT JOIN zzcms_seo_costdetail as a on a.`keywordid` = c.id')
            ->join('LEFT JOIN zzcms_seo_web as d on c.webid = d.id')
            ->field('a.id,b.platformname,a.rank,c.name,(a.`priceone`+a.`pricetwo`) as priceone,a.`createtime`,d.`websiteurl`')
            ->where("a.userid=%d and (a.priceone+a.pricetwo)!=0",array($userid))->count();

        $res = new Page($cateCount, $pageSize);
        $pageRes = $res->show();
        $this->assign('page', $pageRes);
        $this->assign('listinfo', $listinfo);
        $this->assign('type', '扣费详细记录');
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
        $this->assign('type', '排名详情');
        $this->display();
    }

    /**
     * 修改关键词价格
     */
    public function edit()
    {
        if (IS_POST) {
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

                return show_tip(1, '修改成功', null, U('ListInfo'));
            } else {
                return show_tip(0, '修改失败');
            }
        } else {
            $userid = session('zzcms_adm_userid');
            $data = I('get.', '');
            $keyword = $data['keyword'];
            $platformname = $data['platformname'];
            $websiteurl = $data['websiteurl'];
            $keywordid = $data['keywordid'];
            $priceone = $data['priceone'];
            $pricetwo = $data['pricetwo'];

            $this->assign('priceone',$priceone);
            $this->assign('pricetwo',$pricetwo);
            $this->assign('keyword',$keyword);
            $this->assign('platformname',$platformname);
            $this->assign('websiteurl',$websiteurl);
            $this->assign('keywordid',$keywordid);
            $this->assign('type', '修改关键词价格');
            $this->display();
        }
    }
}