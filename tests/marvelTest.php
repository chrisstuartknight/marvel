<?php

class marvelTest extends PHPUnit_Framework_TestCase {
    protected $marvel = null;
    protected $key = 0;

    protected function setUp() {
        require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config'. DIRECTORY_SEPARATOR . 'config.php';
        require_once $cfg['lib'] . 'marvel.php';

        $this->marvel = new Marvel($cfg);
    }

    public function testClass() {
        //OK, this is pretty pointless
        $this->assertEquals('Marvel', get_class($this->marvel));
    }

    public function testConnection() {
        $characters = $this->marvel->getCharacters();

        //OK, we've got an array with data        
        $this->assertGreaterThan(0, count($characters));
        //OK, it's got that key
        $this->assertArrayHasKey($this->key, $characters);
        //OK, that key has got an id field
        $this->assertArrayHasKey('id', $characters[$this->key]);
    }
}
?>
