<?php


include '../lib/tea/TEA.class.php';
TEA::load('orm/SqlBuilder');
TEA::load('db/DB');
TEA::load('db/mysql/Mysql');
TEA::load('db/PreparedStatement');
TEA::load('db/ResultSet');
$conf = array(
    "host"=>"127.0.0.1",
    "username"=>"root",
    "password"=>"12345678",
    "name"=>"teaphp_test",
    "charset"=>"utf8",
);

$mysql = new Mysql($conf);

$conf = array(
    'User'=>array(
        'properties'=>array(
            'id'=>array(
            ),
            'username'=>array(
            ),
            'password'=>array(
            ),
            'status'=>array(
            ),
            'created'=>array(
                'field'=>'create_time'
            ),
        ),
        'table'=>'users',
        'id'=>'id'
    )
);

$qb = new SqlBuilder($conf, $mysql);

$query = array(
    'class'=>'User', 
    'condition'=>array('id'=>123), 
    'properties'=>'id,username',
    'orderBy'=>array('create_time'=>'asc'),
    'limit'=>array(12=>21)
);

print_r($qb->buildSelect($query));
TEA::load('orm/Query');

$q = new Query('User', $conf, $mysql);

$rs = $q->where(array('id>1', 'username like :name'))
        ->orderBy(array('id'=>'desc'))
        //->select('id,username')
        ->execute(array('name'=>'user%'));

foreach ($rs as $row) {
    print_r($row);
}
