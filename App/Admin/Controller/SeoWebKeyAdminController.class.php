<?php
/**
 * Created by PhpStorm.
 * User: yuxiang
 * Date: 2017/2/7
 * Time: 19:37
 */
namespace Admin\Controller;


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
            $webid=$data['webid'];
            $websitearray = M('seo_web')->where(array('id'=>$webid))->select();
            $this->assign('websitearray', $websitearray);
//            print_r($baiduindex);

            $baiduprice = $baiduindex/6;
            $baidumobileprice = $baidumobileindex/5;

            if($baiduprice <=5){
                $baiduprice=5;
            }elseif($baiduprice >=50){
                $baiduprice=50;
            }

            if($baidumobileprice <=5 ){
                $baidumobileprice=5;
            }elseif($baidumobileprice>=60){
                $baidumobileprice=60;
            }

            $sou360 = round($baidumobileprice/3,2);
            if($sou360 <= 3){
                $sou360 = 3;
            }elseif ($sou360 >= 30){
                $sou360 = 30;
            }

            $sougou = round($baiduprice/6,2);
            if($sougou <= 1.5){
                $sougou = 1.5;
            }elseif ($sougou >= 20){
                $sougou=20;
            }

            $shenma = round($baiduprice/7,2);
            if($shenma <= 1){
                $shenma = 1;
            }elseif ($shenma >= 15){
                $shenma = 15;
            }
            $biying = round($baiduprice/7,2);
            if($biying <= 1){
                $biying = 1;
            }elseif ($biying >= 15){
                $biying = 15;
            }
            $this->assign('baiduprice',number_format($baiduprice,2));
            $this->assign('baidumobileprice',number_format($baidumobileprice,2));
            $this->assign('sou360',$sou360);
            $this->assign('sougou',$sougou);
            $this->assign('google',number_format($baiduprice,2));
            $this->assign('shenma',$shenma);
            $this->assign('biying',$biying);
            $this->assign('keyword',$keyword);
            $this->assign('webid',$webid);
            $this->assign('type', '添加关键词');
            $this->display();
        } elseif ($_POST) {

        } else {
            $userid = session('zzcms_adm_userid');
            $condition['userid'] = $userid;
            $websitearray = M('seo_web')->where($condition)->select();
            $this->assign('websitearray', $websitearray);
            $this->assign('type', '添加关键词');
            $this->display();
        }
    }

    public function seachprice()
    {

        $webid = $_POST['webid'];
        $keyword = $_POST['keyword'];

        $key = c402da805c1c46f8a00d1c9f477c6a6f;
        $url = 'http://api.91cha.com/index?key=' . $key . '&kws=' . urlencode($keyword);
        $ch = curl_init();
        $this_header = array(
            "content-type: application/x-www-form-urlencoded; charset=UTF-8"
        );
        curl_setopt($ch,CURLOPT_HTTPHEADER,$this_header);
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
            return show_tip(1, '新增成功', $resstaus, U('add', array('webid' => $webid, 'keyword' => $keyword, 'baiduindex' => $baiduindex, 'baidumobileindex' => $baidumobileindex)));
        }
        return show_tip(0, '新增失败', $resstaus);
    }

    /**
     * 关键词管理
     */
    public function ListInfo()
    {
        $this->display();
    }

    /**
     * 扣费记录详情
     */
    public function CostDetail()
    {
        $this->display();
    }
}