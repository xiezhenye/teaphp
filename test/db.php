<?php
ini_set('display_errors','on');
error_reporting(E_ALL);

include '../lib/tea/TEA.class.php';
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


$sql = 'select * from users';
$rs = $mysql->query($sql);

foreach ($rs as $k=>$row) {
    print_r($row);
}

print_r($rs->fetchAll());