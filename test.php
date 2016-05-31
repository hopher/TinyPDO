<?php

include 'TinyPDO.php';

$db = TinyPDO::getInstance('127.0.0.1', 'root', '123456', 'test');

$db->insert('users', array('username' => 'hongjh', 'age' => '30'));

$sql = 'SELECT * FROM users WHERE username = ?';

$sth = $db->prepare($sql);

$sth->execute(array('hongjh'));

$sth->setFetchMode(PDO::FETCH_ASSOC);

$rs = $sth->fetchAll();

print_r($rs);