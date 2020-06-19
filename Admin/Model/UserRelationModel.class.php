<?php
namespace Admin\Model;
use Think\Model\RelationModel;
Class UserRelationModel extends RelationModel{
    Protected $tableName = 'user';
    Protected $_link = array(
        'role' => array(
            'mapping_type' => self::MANY_TO_MANY,
            'foreign_key' => 'user_id',
            'relation_key' => 'role_id',
            'relation_table' => 'fx_role_user',
            'mapping_fields' => 'id,name,remark'
        )
    );
}
?>