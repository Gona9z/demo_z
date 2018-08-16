<?php
/**
 * Created by PhpStorm.
 * User: zhuangze
 * Date: 2018/8/15
 * Time: 8:25
 */
namespace common\lib\getui;
trait GetuiTrait{
    public $app_id;
    public $app_key;
    public $master_secret;

    public function _initialize(){
        parent::_initialize();
        //初始化个推配置
        $getui_config = C('GETUI_CONFIG');
        $this->app_id = $getui_config['APP_ID'];
        $this->app_key = $getui_config['APP_KEY'];
        $this->master_secret = $getui_config['MASTER_SECRET'];
    }

    /**
     * 根据client_id推送消息
     * @param $gt_client_id
     * @param int $sms_type
     * @param array $send_data
     * @return array
     */
    public function getui_by_clientid($gt_client_id,$sms_type=1,$send_data=[]){
        vendor('Getui.IGt','','.Push.php');
        $igt = new \IGeTui(NULL,$this->app_key,$this->master_secret,false);
        //消息模版：
        // 1.TransmissionTemplate:透传功能模板
        // 2.LinkTemplate:通知打开链接功能模板
        // 3.NotificationTemplate：通知透传功能模板
        // 4.NotyPopLoadTemplate：通知弹框下载功能模板
        switch ($sms_type){
            case '2':
                $template = $this->IGtNotyPopLoadTemplate($send_data);
                break;
            case '3':
                $template = $this->IGtLinkTemplate($send_data);
                break;
            case '4':
                $template = $this->IGtNotificationTemplate($send_data);
                break;
            default:
                $template = $this->IGtTransmissionTemplate($send_data);
        }
        vendor('Getui.igetui.IGt','','.IGtSingleMessage.php');
        //个推信息体
        $message = new \IGtSingleMessage();
        $message->set_isOffline(true);//是否离线
        $message->set_offlineExpireTime(3600*12*1000);//离线时间
        $message->set_data($template);//设置推送消息类型
        $message->set_PushNetWorkType(0);//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送
        //接收方
        $target = new \IGtTarget();
        $target->set_appId($this->app_id);
        $target->set_clientId($gt_client_id);
//    $target->set_alias(Alias);
        try {
            $rep = $igt->pushMessageToSingle($message, $target);
//            var_dump($rep);
//            echo ("<br><br>");
            return ['message'=>'推送成功', 'errorCode'=>0];
        }catch(RequestException $e){
            $requstId =e.getRequestId();
            $rep = $igt->pushMessageToSingle($message, $target,$requstId);
//            var_dump($rep);
//            echo ("<br><br>");
            return ['message'=>'推送失败,错误编号:'.json_encode($rep),'errorCode'=>1];
        }
    }


    //所有推送接口均支持四个消息模板，依次为通知弹框下载模板，通知链接模板，通知透传模板，透传模板
    //注：IOS离线推送需通过APN进行转发，需填写pushInfo字段，目前仅不支持通知弹框下载功能

    /**
     * 通知弹框下载功能模板
     * @param $send_data
     *      title : 通知栏标题
     *      content: 通知栏内容
     *      icon:通知栏logo 默认:''
     *      is_belled:是否响铃 默认:true
     *      is_vibrationed:是否震动 默认:true
     *      is_cleared:通知栏是否可清除 默认:true
     *
     *      title : 通知栏标题
     *      content: 通知栏内容
     *      image:弹框图片
     *      button1:左键
     *      button2:右键
     *
     *      load_icon:弹框图片
     *      load_title:弹框标题
     *      load_url:弹框URL
     *      is_auto_install:是否自动加载
     *      is_actived:
     * @return IGtNotyPopLoadTemplate
     */
    function IGtNotyPopLoadTemplate($send_data){
        $template =  new \IGtNotyPopLoadTemplate();

        $template ->set_appId($this->app_id);//应用appid
        $template ->set_appkey($this->app_key);//应用appkey
        //通知栏
        $template ->set_notyTitle($send_data['title']);//通知栏标题
        $template ->set_notyContent($send_data['content']);//通知栏内容
        $template ->set_notyIcon("");//通知栏logo
        $template ->set_isBelled(true);//是否响铃
        $template ->set_isVibrationed(true);//是否震动
        $template ->set_isCleared(true);//通知栏是否可清除
        //弹框
        $template ->set_popTitle($send_data['title']);//弹框标题
        $template ->set_popContent($send_data['content']);//弹框内容
        $template ->set_popImage("");//弹框图片
        $template ->set_popButton1("下载");//左键
        $template ->set_popButton2("取消");//右键
        //下载
        $template ->set_loadIcon("");//弹框图片
        $template ->set_loadTitle($send_data['title']);
        $template ->set_loadUrl("http://dizhensubao.igexin.com/dl/com.ceic.apk");
        $template ->set_isAutoInstall(false);
        $template ->set_isActived(true);
        //$template->set_duration(BEGINTIME,ENDTIME); //设置ANDROID客户端在此时间区间内展示消息

        return $template;
    }

    //透传功能模板
    private function IGtTransmissionTemplate($send_data){
//        vendor('Getui.igetui.template.IGt','','.TransmissionTemplate.php');
        $template =  new \IGtTransmissionTemplate();
        $template->set_appId($this->app_id);//应用appid
        $template->set_appkey($this->app_key);//应用appkey
        $template->set_transmissionType(1);//透传消息类型
        $contnt = $send_data['content'];
        $title = $send_data['title'];
        $template->set_transmissionContent('{"title":"'.$contnt.'","content":"'.$title.'","payload":{}}');//透传内容
        //$template->set_duration(BEGINTIME,ENDTIME); //设置ANDROID客户端在此时间区间内展示消息

        /*//APN简单推送
        $template = new \IGtAPNTemplate();
        $apn = new \IGtAPNPayload();
        $alertmsg=new \SimpleAlertMsg();
        $alertmsg->alertMsg="";
        $apn->alertMsg=$alertmsg;
//        $apn->badge=2;
//        $apn->sound="";
        $apn->add_customMsg("payload","payload");
        $apn->contentAvailable=1;
        $apn->category="ACTIONABLE";
        $template->set_apnInfo($apn);
        $message = new \IGtSingleMessage();*/

        //APN高级推送
        $apn = new \IGtAPNPayload();
        $alertmsg=new \DictionaryAlertMsg();
        $alertmsg->body=$send_data['content'];//对应的是在线透传的content
        $alertmsg->actionLocKey="ActionLockey";
        $alertmsg->locKey=$send_data['title'];// 对应的是在线透传的title
        $alertmsg->locArgs=array("locargs");
        $alertmsg->launchImage="launchimage";
//        IOS8.2 支持
        $alertmsg->title=$send_data['title'];//对应的也是在线透传的title
        $alertmsg->titleLocKey="TitleLocKey";
        $alertmsg->titleLocArgs=array("TitleLocArg");

        $apn->alertMsg=$alertmsg;
        $apn->badge=7;
        $apn->sound="";
        $apn->add_customMsg("payload","payload");//传送自定义参数的
        $apn->contentAvailable=1;
        $apn->category="ACTIONABLE";
        $template->set_apnInfo($apn);

        //PushApn老方式传参
//    $template = new IGtAPNTemplate();
//          $template->set_pushInfo("", 10, "", "com.gexin.ios.silence", "", "", "", "");

        return $template;
    }

    //通知透传功能模板
    private function IGtNotificationTemplate($send_data){
        $template =  new \IGtNotificationTemplate();
        $template->set_appId($this->app_id);//应用appid
        $template->set_appkey($this->app_key);//应用appkey
        $template->set_transmissionType(1);//透传消息类型
        $template->set_transmissionContent("测试离线");//透传内容
        $template->set_title($send_data['title']);//通知栏标题
        $template->set_text($send_data['content']);//通知栏内容
        $template->set_logo("http://wwww.igetui.com/logo.png");//通知栏logo
        $template->set_isRing(true);//是否响铃
        $template->set_isVibrate(true);//是否震动
        $template->set_isClearable(true);//通知栏是否可清除
        //$template->set_duration(BEGINTIME,ENDTIME); //设置ANDROID客户端在此时间区间内展示消息
        return $template;
    }

    //通知打开链接功能模板
    private function IGtLinkTemplate($send_data){
        $template =  new \IGtLinkTemplate();
        $template ->set_appId($this->app_id);//应用appid
        $template ->set_appkey($this->app_key);//应用appkey
        $template ->set_title($send_data['title']);//通知栏标题
        $template ->set_text($send_data['content']);//通知栏内容
        $template ->set_logo("");//通知栏logo
        $template ->set_isRing(true);//是否响铃
        $template ->set_isVibrate(true);//是否震动
        $template ->set_isClearable(true);//通知栏是否可清除
        $template ->set_url("http://www.nuonuojinfu.cn/");//打开连接地址
        //$template->set_duration(BEGINTIME,ENDTIME); //设置ANDROID客户端在此时间区间内展示消息
        return $template;
    }
}