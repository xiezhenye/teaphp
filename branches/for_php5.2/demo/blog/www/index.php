<?php
$projectConf = include(__DIR__.'/../config/project.conf.php');

include $projectConf['teaRoot'].'/TEA.php';

$conf = new tea::common::Config($projectConf['projectRoot'].'/config/db.conf.js');


