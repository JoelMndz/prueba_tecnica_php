<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'sqlsrv:Server='.$_ENV['MSSQL_HOST'].';Database='.$_ENV['MSSQL_DB'],
    'username' => $_ENV['MSSQL_USER'],
    'password' => $_ENV['MSSQL_PASSWORD'],
    'charset' => 'utf8',
];
