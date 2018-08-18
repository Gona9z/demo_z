<?php
/**
 * Created by PhpStorm.
 * User: zhuangze
 * Date: 2018/8/15
 * Time: 17:30
 */

namespace app\admin\behavior;
use app\admin\helper\key\CookieKey;
use app\admin\model\Auth;
use think\Cookie;
use think\Request;

class Authorize {
    use \traits\controller\Jump;
    // 行为入口
    public function run(&$params){
        $request = Request::instance();
        //查看该用户的所属角色是否拥有该路由权限
        if(true){
            $request->isGet() && $this->saveCurrentMenu();
            return true;
        }else{
            //如果请求类型为get，重定向到登录页面
            if($request->isGet()) {
                $this->redirect('/login');
            } else {
                return false;
            }
        }
    }

    /**
     * @description : [保存当前点击的节点、父节点、及面包屑]
     * @author : [zhuangze]
     */
    private function saveCurrentMenu(){
        $request = Request::instance();
        //获取当前节点保存在数据库中的信息
        $data = model('Auth')
            ->field(['path', 'auth_name', 'has_remark', 'remark'])
            ->where(['route_url' => $request->path()])
            ->find();
        //把当前节点数据保存进节点
        Cookie::set(
            CookieKey::CURRENT_MENU_DATA,
            json_encode($data),
            CookieKey::CURRENT_MENU_DATA_EXPIRE);

        if(is_null( $data )) {
            return;
        }
        $currentMenuPath = $data['path'];
        if(!empty( $currentMenuPath )) {
            $pids = $parentIds = explode( '-', $currentMenuPath );
            //找出上面路径中按钮的权限数组列表
            $menu_pids = model('Auth')
                ->field( 'id' )
                ->where( [ 'status' => Auth::STATUS_ON, 'is_button' => Auth::IS_BUTTON_YES ] )
                ->andWhere( [ 'in', 'id', $pids ] )
                ->asArray()->select();
            //路径中如果存在含按钮的菜单，那么久需要去除，并返回最近的一个父级菜单
            if(!empty( $menu_pids )) {
                $menu_pids = array_column( $menu_pids, 'id' );
                $parentIds = array_diff( $pids, $menu_pids );
            }

            $currentId = array_pop( $parentIds );//弹出当前需要展开的菜单ID

            //父菜单ID列表
            Cookie::set(
                CookieKey::MENU_PARENT_IDS,
                $parentIds,
                time() + CookieKey::MENU_PARENT_IDS_EXPIRE
                );
            //当前菜单ID
            Cookie::set(
                CookieKey::CURRENT_MENU_ID,
                $currentId,
                time() + CookieKey::CURRENT_MENU_ID_EXPIRE
                );

            //面包屑(todo:缓存)
            $crumbs = model(Auth)
                ->field(['id', 'auth_name', 'route_url','is_button', 'icon'])
                ->where(['status' => Auth::STATUS_ON])
                ->where(['in', 'id', $pids])
                ->order(['path' => SORT_ASC])
                ->asArray()
                ->select();

            //当前菜单ID
            Cookie::set(
                CookieKey::MENU_CRUMBS,
                $crumbs,
                time() + CookieKey::MENU_CRUMBS_EXPIRE
            );

        } else {
            //没有就清空，省的受上次点击菜单的影响
            Cookie::delete(CookieKey::CURRENT_MENU_DATA);
            Cookie::delete(CookieKey::MENU_PARENT_IDS);
            Cookie::delete(CookieKey::CURRENT_MENU_ID);
        }
    }

}