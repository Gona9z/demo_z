<?php
namespace app\admin\controller;

class Index extends Base{

    /**
     * @description : [后台首页]
     * @author : [zhuangze]
     * @return mixed
     */
    public function index(){
        return $this->fetch();
    }

    public function welcome() {
        return "hello api-admin";
    }
}
