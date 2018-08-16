<?php
/**
 * Created by PhpStorm.
 * @author : [zhuangze]
 * @Date: 18/8/15
 * @Time: 18:05
 */
namespace app\admin\model;

class Role extends Base {

    /**
    * 状态列表
    */
    const STATUS_DENY = '0';
    const STATUS_ON = '1';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'bg_role';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [ [ 'role_name', 'remark', 'status' ], 'required' ],
            [ [ 'status' ], 'integer' ],
            [ [ 'role_name' ], 'string', 'max' => 45 ],
            [ [ 'remark' ], 'string', 'max' => 255 ],
        ];
    }

    public function beforeValidate()
    {
        if(!parent::beforeValidate()) {
            return false;
        }
        $this->status = $this->status ? self::STATUS_ON : self::STATUS_DENY;
        return true;
    }


    public static function getAllStatus()
    {
        return [
            self::STATUS_ON => '启用',
            self::STATUS_DENY => '屏蔽',
        ];
    }

    public static function getStatusText($key)
    {
        $list = self::getAllStatus();
        if(array_key_exists( $key, $list )) {
            return $list[$key];
        } else {
            return '';
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'role_name' => '角色名称',
            'remark' => '角色描述',
            'status' => '状态',
        ];
    }

    public function getRoleAdmins()
    {
        return $this->hasMany( RoleAdmin::className(), [ 'role_id' => 'id' ] );
    }

    public function getTree()
    {
        $data = Auth::find()->where(['status'=>Auth::STATUS_ON])->asArray()->all();
        $res = $this->buidTree( $data );
        return $res;
    }

    // 将所有的可用的该角色的所有权限都拿出来
    public function getAllAuthsByRoleId($roleid = null)
    {
        $roleid = is_null( $roleid ) ? $this->id : $roleid;
        $auths = (new \yii\db\Query())
            ->select( 'auth.*' )
            ->from( 'bg_role_auth ra' )
            ->leftJoin( 'bg_auth auth', 'ra.auth_id=auth.id' )
            ->where( [
                'auth.status' => Auth::STATUS_ON,
                'ra.role_id' => $roleid,
            ] )
            ->orderBy( [ 'auth.is_home' => SORT_DESC, 'auth.sort' => SORT_ASC, 'auth.path' => SORT_ASC ] )
            ->all();
        return $auths;
    }

    private function buidTree($array, $pId = 0)
    {
        $result = array();
        foreach($array as $key => $val) {
            if(isset( $val['pid'] ) && ($val['pid'] == $pId)) {
                $tmp = $array[$key];
                unset( $array[$key] );
                if(count( $this->buidTree( $array, $val['id'] ) ) > 0) {
                    $tmp['_son'] = $this->buidTree( $array, $val['id'] );
                }
                $result[] = $tmp;
            }
        }
        return $result;
    }

}