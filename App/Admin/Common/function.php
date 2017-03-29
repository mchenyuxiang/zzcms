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
    if (strstr($URL, '.com') || strstr($URL, '.net') || strstr($URL, '.org') || strstr($URL, '.cn') || strstr($URL, '.tv')) {
        return true;
    } else {
        return false;
    }
}

//创建TOKEN
function creatToken()
{
    $code = chr(mt_rand(0xB0, 0xF7)) . chr(mt_rand(0xA1, 0xFE)) . chr(mt_rand(0xB0, 0xF7)) . chr(mt_rand(0xA1, 0xFE)) . chr(mt_rand(0xB0, 0xF7)) . chr(mt_rand(0xA1, 0xFE));
    session('TOKEN', authcode($code));
}

//判断TOKEN
function checkToken($token)
{
    if ($token == session('TOKEN')) {
        session('TOKEN', NULL);
        return TRUE;
    } else {
        return FALSE;
    }
}

/* 加密TOKEN */
function authcode($str)
{
    $key = "chenyuxiang";
    $str = substr(md5($str), 8, 10);
    return md5($key . $str);
}

/**
 * excel 导出数据
 * @param $fileName 文件名
 * @param $headArr  表格头部
 * @param $data     数据
 */
function getExcel($fileName, $headArr, $data)
{
    /*$filename ============  导出表的名字 */
    /*$headArr============  导出表的第一行名称 */
    /*$data============  导出表的数据（array） */
    /*此方法不用刻意去修改，直接使用即可注意导入类时不要出错就行了 */
    //header("Content-type: text/html;charset=utf-8");
    //对数据进行检验
    if (empty($data) || !is_array($data)) {
        die("data must be a array");
    }
    //检查文件名
    if (empty($fileName)) {
        exit;
    }

    $date = date("Y_m_d", time());
    $fileName .= "_{$date}.xls";
    import("Org.Util.PHPExcel");
    import("Org.Util.PHPExcel.Writer.Excel5");
    import("Org.Util.PHPExcel.IOFactory.php");
    //创建PHPExcel对象，注意，不能少了\
    $objPHPExcel = new \PHPExcel();
    $objProps = $objPHPExcel->getProperties();

    //设置表头
    $key = ord("A");
    foreach ($headArr as $v) {
        $colum = chr($key);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . '1', $v);
        $key += 1;
    }

    $column = 2;
    $objActSheet = $objPHPExcel->getActiveSheet();
    foreach ($data as $key => $rows) { //行写入
        $span = ord("A");
        foreach ($rows as $keyName => $value) {// 列写入
            //$value=iconv("utf-8","gb2312",$value);
            $j = chr($span);
            $objActSheet->setCellValue($j . $column, $value);
            $span++;
        }
        $column++;
    }
    ob_end_clean();//清除缓冲区,避免乱码
    $fileName = iconv("utf-8", "gb2312", $fileName);
    //重命名表
    // $objPHPExcel->getActiveSheet()->setTitle('test');
    //设置活动单指数到第一个表,所以Excel打开这是第一个表
    $objPHPExcel->setActiveSheetIndex(0);
    header("Content-type: text/csv");//重要
    header("Content-Type: application/force-download");
    //header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment;filename=\"$fileName\"");
    //header('Cache-Control: max-age=0');
    header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
    header('Cache-Control: must-revalidate, post-check=0,pre-check=0');
    header('Expires:0');
    header('Pragma:public');

    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output'); //文件通过浏览器下载
    exit;
}

?>

