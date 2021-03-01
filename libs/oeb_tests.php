<?php 

use PHPUnit\Framework\TestCase;

class MyScriptTest extends PHPUnit_Framework_TestCase {
    public function testMyFunction() {
        include_once 'path/to/script.php';
        $result = someFunction();

        $this->assertEquals('expected result', $result);
    }
} 

?>