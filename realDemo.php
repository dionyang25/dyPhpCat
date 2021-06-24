<?php
require __DIR__.'/autoload.php';
use PhpCat\PhpCat;
use PhpCat\Message\Message;
$phpCat = new PhpCat("app-www",[["10.8.101.102","2280"]]);
error_reporting(E_ALL ^ E_WARNING);
    $t1 = $phpCat->newTransaction("remotingService","/actuator/abc");
//var_dump($t1);
    $t1->setStatus(Message::SUCCESS);
    $t1->addData("Hello", "world");
    $t1->complete();


echo '22';


