<?php
/**
 * Created by PhpStorm.
 * User: yuxiang
 * Date: 2016/12/28
 * Time: 1:42
 * Description:
 */
namespace Common\Lib;

class Category{
    //一维数组
    static public function toLevel($cate, $delimiter = '———', $pid = 0, $level = 0) {

        $arr = array();
        foreach ($cate as $v) {
            if ($v['pid'] == $pid) {
                $v['level'] = $level + 1;
                $v['delimiter'] = str_repeat($delimiter, $level);
                $arr[] = $v;
                $arr = array_merge($arr, self::toLevel($cate, $delimiter, $v['id'], $v['level']));
            }
        }

        return $arr;

    }


    //组成多维数组
    static public function toLayer($cate, $name = 'child', $pid = 0){

        $arr = array();
        foreach ($cate as $v) {
            if ($v['pid'] == $pid) {
                $v[$name] = self::toLayer($cate, $name, $v['id']);
                $arr[] = $v;
            }
        }

        return $arr;
    }
}
?>

