<?php
/**
 * Created by PhpStorm.
 * User: zhuangze
 * Date: 2018/8/15
 * Time: 17:30
 */

namespace app\admin\behavior;
class Authorize {

    public function appInit(&$params){
        dump(22);
    }

    public function appEnd(&$params){
        dump(22);

    }

    // 行为逻辑
    public function run(&$params){
        dump(1111);
    }
}