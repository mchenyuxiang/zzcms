<?php
return array(
	//'配置项'=>'配置值'

    //设置2级目录控制器层
//    'CONTROLLER_LEVEL'      =>  2,

    'TMPL_PARSE_STRING' => array(
        '__PUBLIC__' => __ROOT__. ltrim(APP_PATH,'.'). MODULE_NAME . '/View/Public',
//        '__DATA__' => __ROOT__. '/Data',
    ),
);