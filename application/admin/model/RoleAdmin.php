<?php
/**
 * Created by PhpStorm.
 * @author : [zhuangze]
 * @Date: 18/8/15
 * @Time: 18:05
 */
namespace app\admin\model;

class RoleAdmin extends Base {

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'bg_role_admin';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [ [ 'id', 'role_id', 'admin_id' ], 'required' ],
            [ [ 'id', 'role_id', 'admin_id' ], 'integer' ],
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
            'admin_id' => '管理员ID',
        ];
    }

    public function getAdmins()
    {
        return $this->hasMany( Admin::className(), [ 'id' => 'admin_id' ] );
    }

    public function getRoles()
    {
        return $this->hasMany( Role::className(), [ 'id' => 'role_id' ] );
    }

    public function getManyRolesByAdminId($adminId)
    {
        return $this->hasMany( Role::className(), [ 'id' => 'role_id' ] )->joinWith('')->where( [ 'admin_id' => $adminId ] );
    }
}