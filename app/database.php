<?php

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule();

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => DB_HOST,
    'username'  => DB_USERNAME,
    'password'  => DB_PASSWORD,
    'database'  => DB_DATABASE,
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => ''
]);

$capsule->bootEloquent();