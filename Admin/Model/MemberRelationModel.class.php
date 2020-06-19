<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/2/5
 * Time: 15:11
 */
namespace Admin\Model;
use Think\Model\RelationModel;
class MemberRelationModel extends RelationModel{
    Protected $tableName = 'member';
    Protected $_link = array(
        'package_type' => array(
            'mapping_type'=> self::BELONGS_TO,
            'foreign_key' => 'package_type',
            'condition'=>'status = 1',
            'as_fields' => 'title:package_type_title'
        ),
		'member_login_info' => array(
            'mapping_type'=> self::HAS_MANY,
            'foreign_key' => 'member_id',
			'mapping_limit'=>1,
			'mapping_order'=>"id desc",
        ),
    );
}