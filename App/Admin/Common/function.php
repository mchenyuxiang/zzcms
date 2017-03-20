<?php
/**
 * Created by PhpStorm.
 * User: yuxiang
 * Date: 2016/12/24
 * Time: 16:20
 * Description:  通用方法
 */

function show_tip($status, $message, $data = array(), $url = '')
{
    $result = array(
        'status' => $status,
        'message' => $message,
        'data' => $data,
        'url' => $url,
    );

    exit(json_encode($result));
}

function getMd5Password($password)
{
    return md5($password . 'chenyuxiang');
}

/**
 * 导出数据为excel表格
 * @param $data    一个二维数组,结构如同从数据库查出来的数组
 * @param $title   excel的第一行标题,一个数组,如果为空则没有标题
 * @param $filename 下载的文件名
 * @examlpe
$stu = M ('User');
 * $arr = $stu -> select();
 * exportexcel($arr,array('id','账户','密码','昵称'),'文件名!');
 */
function exportexcel($data = array(), $title = array(), $filename = 'report')
{
    header("Content-type:application/octet-stream");
    header("Accept-Ranges:bytes");
    header("Content-type:application/vnd.ms-excel");
    header("Content-Disposition:attachment;filename=" . $filename . ".xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    //导出xls 开始
    if (!empty($title)) {
        foreach ($title as $k => $v) {
            $title[$k] = iconv("UTF-8", "GB2312", $v);
        }
        $title = implode("\t", $title);
        echo "$title\n";
    }
    if (!empty($data)) {
        foreach ($data as $key => $val) {
            foreach ($val as $ck => $cv) {
                $data[$key][$ck] = iconv("UTF-8", "GB2312", $cv);
            }
            $data[$key] = implode("\t", $data[$key]);

        }
        echo implode("\n", $data);
    }
}

function validateURL($URL)
{
//    $pattern_1 = "^(?=^.{3,255}$)[a-zA-Z0-9][-a-zA-Z0-9]{0,62}(\.[a-zA-Z0-9][-a-zA-Z0-9]{0,62})+$";
//    if(preg_match($pattern_1, $URL)){
    if (strstr($URL,'.com') || strstr($URL,'.net') || strstr($URL,'.org') || strstr($URL,'.cn') || strstr($URL,'.tv')) {
        return true;
    } else {
        return false;
    }
}

//创建TOKEN
function creatToken() {
    $code = chr(mt_rand(0xB0, 0xF7)) . chr(mt_rand(0xA1, 0xFE)) . chr(mt_rand(0xB0, 0xF7)) . chr(mt_rand(0xA1, 0xFE)) . chr(mt_rand(0xB0, 0xF7)) . chr(mt_rand(0xA1, 0xFE));
    session('TOKEN', authcode($code));
}

//判断TOKEN
function checkToken($token) {
    if ($token == session('TOKEN')) {
        session('TOKEN', NULL);
        return TRUE;
    } else {
        return FALSE;
    }
}

/* 加密TOKEN */
function authcode($str) {
    $key = "chenyuxiang";
    $str = substr(md5($str), 8, 10);
    return md5($key . $str);
}

?>

