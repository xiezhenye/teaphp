<?php


include '../lib/tea/TEA.php';
use tea::db::QueryBuilder;
use tea::db::driver::mysql::Mysql;

$conf = array(
    "host"=>"localhost",
    "user"=>"root",
    "password"=>"12345678",
    "name"=>"teaphp_test",
    "charset"=>"utf8",
);

$mysql = new Mysql();
$mysql->connect($conf);

$conf = array(
    'User'=>array(
        'properties'=>array(
            'id'=>array(
            ),
            'name'=>array(
            )
        ),
        'table'=>'user',
        'id'=>'id'
    )
);

$qb = new QueryBuilder($conf, $mysql);

$query = array(
    'class'=>'User', 
    'condition'=>array('id=:id and name like :n', array('id'=>2,'n'=>'aaa')), 
    'properties'=>'id,name'
);

echo $qb->build($query),"\n";
