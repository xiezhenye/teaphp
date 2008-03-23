<?php
include '../lib/tea/TEA.php';

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

$sql = 'select * from user';
$rs = $mysql->query($sql);

while ($row = $rs->fetch()) {
    print_r($row);
}



