<?php
// make this run where ever you put it
$dir = explode(DIRECTORY_SEPARATOR, __DIR__);
array_pop($dir);
$dir = implode(DIRECTORY_SEPARATOR, $dir) . DIRECTORY_SEPARATOR;

// build config array
$cfg = array();
$cfg['path'] = $dir;
$cfg['lib'] = $cfg['path'] . 'lib' . DIRECTORY_SEPARATOR;
$cfg['config'] = $cfg['path'] . 'config' . DIRECTORY_SEPARATOR . 'config.php';
$cfg['composer'] = $cfg['path'] .'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

// post datasift connection settings
$cfg['process'] = array();
$cfg['process']['mode'] = 'csv';
$cfg['process']['allowed'] = array('csv');
$cfg['process']['file'] = $cfg['path'] . 'data';
$cfg['process']['output'] = $cfg['path'] . 'output';

// marvel api connection settings
$cfg['marvel'] = array();
$cfg['marvel']['apiKey'] = '40e6225eb377c490a1fad658565524c9';
$cfg['marvel']['privateKey'] = 'd3825bbdfeed58641fa1ab60223f92a8d9c430e4';
$cfg['marvel']['url'] = 'http://gateway.marvel.com/v1/public/';
$cfg['marvel']['limit'] = 100;
$cfg['marvel']['modifiedSince'] = '2014-01-01';

// datasift stream,ng api settings
$cfg['datasift'] = array();
$cfg['datasift']['apiKey'] = 'ee4d3649e2347f9bb00a44704c701b66';
$cfg['datasift']['user'] = 'corroded';
$cfg['datasift']['url'] = '';
$cfg['datasift']['types'] = array('twitter.text','facebook.message','reddit.content');
?>
