<?php

//load config, autoload composer modules and load local classes
require_once 'config' . DIRECTORY_SEPARATOR . 'config.php';
require_once $cfg['composer'];
require_once $cfg['lib'] . 'marvel.php';
require_once $cfg['lib'] . 'datasift.php';

try {
    //grab data from Marvel, way too many for a single
    //query, so it's limited to anything modified this year
    $marvel = new Marvel($cfg);
    $characters = $marvel->getCharacterNames();

    //prepare datasift
    $datasift = new Datasift($cfg); 
    $datasift->buildCDSL($characters);
    $datasift->createDefinition();

    //run the job
    $consumer = $datasift->getConsumer();
    $consumer->consume();
} catch(Exception $e) {
    print_r($e->getMessage());
}
?>
