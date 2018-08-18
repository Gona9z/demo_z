<?php
/**
 * Created by PhpStorm.
 * @author : [zhuangze]
 * @Date: 18/8/15
 * @Time: 18:05
 */
namespace app\admin\model;

use think\Model;

class Auth extends Model {

    //权限状态
    const STATUS_DENY = '0';//禁用
    const STATUS_ON = '1';//开启

    //是否是按钮
    const IS_BUTTON_YES = '1';//是
    const IS_BUTTON_NO = '0';//否

    //是否有权限说明
    const HAS_REMARK_YES = '1';//是
    const HAS_REMARK_NO = '0';//否

    public static function getAllStatus()
    {
        return [
            self::STATUS_ON => '启用',
            self::STATUS_DENY => '禁用',
        ];
    }

    public static function getStatusText($key)
    {
        $statusList = self::getAllStatus();
        if(array_key_exists( $key, $statusList )) {
            return $statusList[$key];
        } else {
            return '';
        }
    }

    public static function getAllIsButton()
    {
        return [
            self::IS_BUTTON_YES => '是',
            self::IS_BUTTON_NO => '否',
        ];
    }

    public static function getIsButtonText($key)
    {
        $list = self::getAllIsButton();
        if(array_key_exists( $key, $list )) {
            return $list[$key];
        } else {
            return '';
        }
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'bg_auth';
    }

    const SCENARIO_CREATE_TOP = 'create_top';//创建顶级菜单
    const SCENARIO_CREATE = 'create';//创建权限
    const SCENARIO_UPDATE = 'update';//修改权限
    const SCENARIO_SET_SORT = 'set_sort';//菜单排序

    public function scenarios()
    {
        $common = [ 'auth_name', 'pid', 'route_url', 'sort', 'icon', 'is_button', 'has_remark', 'remark', 'status' ];
        return [
            self::SCENARIO_CREATE_TOP => [ 'auth_name', 'route_url', 'sort', 'icon', 'is_button', 'has_remark', 'remark', 'status' ],
            self::SCENARIO_CREATE => $common,
            self::SCENARIO_UPDATE => $common,
            self::SCENARIO_SET_SORT => [ 'sort' ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [ [ 'auth_name', 'sort' ], 'required' ],
            [ [ 'pid', 'sort', 'is_button', 'is_home', 'has_remark', 'status' ], 'integer' ],
            [ [ 'auth_name' ], 'string', 'max' => 45 ],
            [ [ 'route_url', 'remark' ], 'string', 'max' => 255 ],
            [ [ 'path' ], 'string', 'max' => 150 ],
            [ [ 'icon' ], 'string', 'max' => 50 ],
            [ 'pid', 'default', 'value' => 0, 'on' => self::SCENARIO_CREATE_TOP ],
            [ 'is_home', 'default', 'value' => 0, 'on' => [ self::SCENARIO_CREATE_TOP, self::SCENARIO_CREATE, self::SCENARIO_UPDATE ] ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'auth_name' => '权限名称',
            'pid' => '父级权限ID',
            'route_url' => '路由地址',
            'path' => '路径',
            'sort' => '同级排序顺序序号',
            'icon' => '图标class样式名',
            'is_button' => '是否是按钮',
            'is_home' => '是否是主页 0不是 1是',
            'has_remark' => '是否添加权限说明',
            'remark' => '权限说明',
            'status' => '状态',
        ];
    }

    public function beforeValidate()
    {
        if(!parent::beforeValidate()) {
            return false;
        }
        $this->status = $this->status ? self::STATUS_ON : self::STATUS_DENY;
        $this->has_remark = $this->has_remark ? self::HAS_REMARK_YES : self::HAS_REMARK_NO;
        $this->is_button = $this->is_button ? self::IS_BUTTON_YES : self::IS_BUTTON_NO;
        //print_r($this->attributes);
        //如果是更新，那么path需要重新组装
        if($this->getScenario() == self::SCENARIO_UPDATE) {
            //找到新的父级的path
            $this->path = $this->getParentPath() . '-' . $this->id;
        }
        return true;
    }

    /**
     * @var [当前权限的父级权限对象]
     */
    private $_parent_auth;

    /**
     * @var [当前权限的所有子权限IDs列表]
     */
    private $_son_auths_ids;

    /**
     * @return mixed    [得到当前权限的父级权限对象信息]
     */
    public function getParentAuth()
    {
        $auth = $this->_parent_auth;
        if(is_null( $this->_parent_auth )) {
            $auth = $this->select( 'path' )->where( [ 'id' => $this->pid ] )->asArray()->find();
        }
        return $auth;
    }

    /**
     * @var [父权限path属性值]
     */
    private $_parent_path;

    /**
     * @use          [得到父权限path属性值]
     * @author       chenxiaogang
     */
    public function getParentPath()
    {
        $path = $this->_parent_path;
        if(is_null( $path )) {
            if($this->pid > 0) {
                $auth = $this->select( 'path' )->where( [ 'id' => $this->pid ] )->asArray()->find();
                $path = $auth['path'];
            } else {
                $path = '0';
            }
        }
        return $path;
    }

    /**
     * @return mixed
     */
    public function getSonAuthsIds()
    {
        return $this->_son_auths_ids;
    }

    /**
     * @param mixed $son_auths_ids
     */
    public function setSonAuthsIds($son_auths_ids)
    {
        $this->_son_auths_ids = $son_auths_ids;
    }


    //默认图标类
    const ICON_DEFAULT = 'fa-caret-right';

    public function beforeSave($insert)
    {
        if(!parent::beforeSave( $insert )) {
            return false;
        }
        //新增和修改时，如果没指定图标类名，使用默认图标类
        if($this->icon == '' && in_array( $this->getScenario(), [ self::SCENARIO_CREATE_TOP, self::SCENARIO_CREATE, self::SCENARIO_UPDATE ] )) {
            $this->icon = self::ICON_DEFAULT;
        }
        //菜单首次创建时，需要判断路由的唯一（非空字符串时判断）
        if($this->route_url && in_array( $this->getScenario(), [ self::SCENARIO_CREATE, self::SCENARIO_CREATE_TOP ] ) && self::findOne( [ 'route_url' => $this->route_url, 'status' => self::STATUS_ON ] )) {
            $this->addError( 'route_url', '该路由已存在' );
            return false;
        }
        return true;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave( $insert, $changedAttributes );
        if($insert) {
            if($this->getScenario() == self::SCENARIO_CREATE_TOP) {
                //创建顶级菜单的时候，要拼接一下path，然后更新回去
                $this->updateAttributes( [ 'path' => '0-' . $this->getPrimaryKey() ] );//保存path
            } elseif($this->getScenario() == self::SCENARIO_CREATE) {
                //创建普通子菜单时，要先将父级的pid找出，然后再拼接更新path
                $parent = $this->select( 'path' )->where( [ 'id' => $this->pid, 'status' => self::STATUS_ON ] )->asArray()->find();
                $this->updateAttributes( [ 'path' => $parent['path'] . '-' . $this->getPrimaryKey() ] );//保存path
            }
        } else {
            //修改时，相对应当前权限的所有子权限的path也要发生相应改变
            if($this->getScenario() == self::SCENARIO_UPDATE) {
                //先改path
                $sonIds = $this->getSonAuthsIds();
                //判断是否有儿子
                if(!empty( $sonIds )) {
                    $sql = 'UPDATE ' . self::tableName() . ' SET path=CONCAT("' . $this->getParentPath() . '",REPLACE(path,SUBSTRING_INDEX(path,"-' . $this->id . '-",1),"")) WHERE id IN (' . implode( ',', $sonIds ) . ')';
                    if(!(Yii::$app->db->createCommand( $sql )->execute() >= 0)) {
                        $this->addError( 'pid', '编辑失败' );
                        throw new \Exception();
                    }
                }

            }
        }

    }

    /**
     * @use          [提取当前权限的所有的权限ID列表]
     * @author       chenxiaogang
     * @param $all [所有
     * @param $id  [被找的
     * @return array
     */
    public function getAllSonIds($all, $id)
    {
        $arr = array();
        foreach($all as $v) {
            if($v['pid'] == $id) {
                $arr[] = $v['id'];
                $arr = array_merge( $arr, $this->getAllSonIds( $all, $v['id'] ) );
            }
        };
        $id == $this->id && $this->setSonAuthsIds( $arr );//将子权限保存起来
        return $arr;
    }

//    public function beforeDelete()
//    {
//        if(!parent::beforeDelete()) {
//            return false;
//        }
//        $auths = Auth::find()->select( [ 'id', 'pid' ] )->asArray()->all();
//        //查一下是否有子级
//        if(empty( $this->getAllSonIds( $auths, $this->id ) )) {
//            $this->setHasSon( false );
//        } else {
//            $this->setHasSon( true );
//        }
//        return true;
//    }

    /**
     * @var [是否有子级]
     */
    private $_has_son;

    /**
     * @return mixed
     */
    public function getHasSon()
    {
        return $this->_has_son;
    }

    /**
     * @param mixed $has_son
     */
    public function setHasSon($has_son)
    {
        $this->_has_son = $has_son;
    }


//    public function afterDelete()
//    {
//        parent::afterDelete();
//
//        //删除对应关系（权限和角色关系），删除所有子级
//        if($this->getHasSon() && !(self::deleteAll( [ 'in', 'id', $this->getSonAuthsIds() ] ) > 0)) {
//            throw new \Exception();
//        }
//        $this->delRoleAuth();//删除角色和权限的关系
//    }

    /**
     * @use          [删除角色和权限对应关系]
     * @author       chenxiaogang
     * @throws \Exception
     */
    private function delRoleAuth()
    {
        //删除对应关系（权限和角色关系）
        if($this->getHasSon()) {
            if(!(RoleAuth::deleteAll( [ 'in', 'auth_id', array_merge( [ $this->id ], $this->getSonAuthsIds() ) ] ) >= 0)) {
                throw new \Exception();
            }
        } else {
            if(!(RoleAuth::deleteAll( [ 'auth_id' => $this->id ] ) >= 0)) {
                throw new \Exception();
            }
        }
    }

}