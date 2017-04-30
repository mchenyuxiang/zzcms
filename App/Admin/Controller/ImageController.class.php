<?php
/**
 * Created by PhpStorm.
 * User: yuxiang
 * Date: 2017/4/30
 * Time: 16:27
 * Description:
 */

namespace Admin\Controller;
use Think\Controller;
use Think\Upload;

/**
 * Class ImageController
 * @package Admin\Controller
 * 图片上传
 */
class ImageController extends CommonController
{
    private $_uploadObj;

    public function __construct()
    {
    }

    public function ajaxuploadimage()
    {
        $upload = D("UploadImage");
        $res = $upload->imageUpload();
        if ($res === false) {
            return show_tip(0, '上传失败', '');
        } else {
            return show_tip(1, '上传成功', $res);
        }
    }


}

?>

