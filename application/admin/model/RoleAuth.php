<?php
/**
 * Created by PhpStorm.
 * @author : [zhuangze]
 * @Date: 18/8/15
 * @Time: 18:05
 */
namespace app\admin\model;

class RoleAuth extends Base {

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'bg_role_auth';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [ [ 'role_id', 'auth_id' ], 'required' ],
            [ [ 'role_id', 'auth_id' ], 'integer' ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'role_id' => '角色ID',
            'auth_id' => '权限ID',
        ];
    }

    public function getMenu()
    {
        $data = $this->getAllCanSee();
        $res = $this->getTree( $data );
        return $res;
    }

    // 将所有的可用的可见菜单拿出来
    protected function getAllCanSee()
    {
        //从redis中读取菜单权限（排除了按钮的权限）
        $menu_auths_key = RedisKey::MENU_AUTHS . Yii::$app->getUser()->getId();
        //2、仅按钮权限列表
        $menu_auths_json = Yii::$app->redis->get( $menu_auths_key );
        //var_dump($menu_auths_json);die;
        $menu_auths = Json::decode( $menu_auths_json );
        //var_dump($menu_auths);die;
        return $menu_auths;
    }

    private function getTree($array, $pId = 0)
    {
        $result = array();
        foreach($array as $key => $val) {
            if(isset( $val['pid'] ) && ($val['pid'] == $pId)) {
                $tmp = $array[$key];
                unset( $array[$key] );
                if(count( $this->getTree( $array, $val['id'] ) ) > 0) {
                    $tmp['_son'] = $this->getTree( $array, $val['id'] );
                }
                $result[] = $tmp;
            }
        }
        return $result;
    }

}