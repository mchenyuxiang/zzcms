<?php
/**
 * Created by PhpStorm.
 * User: yuxiang
 * Date: 2016/12/24
 * Time: 16:20
 * Description:  通用方法
 */

    function show_tip($status, $message,$data=array(),$url=''){
        $result = array(
            'status' => $status,
            'message' => $message,
            'data' => $data,
            'url' => $url,
        );

        exit(json_encode($result));
    }

    function getMd5Password($password){
       return md5($password.'chenyuxiang'); 
    }
?>
