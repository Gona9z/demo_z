<?php

namespace app\admin\helper\key;

/**
 * @description : [后台cookies使用的key]
 * Class CookieKey
 * @package app\admin\helper\key
 */
class CookieKey
{
    const CURRENT_MENU_DATA = 'current_menu_data';//当前打开的路由的权限信息
    const CURRENT_MENU_DATA_EXPIRE = 3600;//过期时间

    const MENU_PARENT_IDS = 'menu_parent_ids';//当前菜单的父级菜单ID列表(非按钮)，用于左侧菜单的展开
    const MENU_PARENT_IDS_EXPIRE = 3600;//过期时间

    const CURRENT_MENU_ID = 'current_menu_id';//当前需要点击的菜单（左侧默认点击的菜单）
    const CURRENT_MENU_ID_EXPIRE = 3600;//过期时间

    const MENU_CRUMBS = 'current_menu_crumbs';//面包屑导航（连续的菜单）
    const MENU_CRUMBS_EXPIRE = 3600;//过期时间

}