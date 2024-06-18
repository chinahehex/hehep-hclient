<?php
namespace hclient\tests;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected $url = '';

    /**
     * @var \hclient\Client
     */
    protected $hclient;

    protected static $config = [];

    protected function setUp()
    {
        $this->hclient = new \hclient\Client();
    }

    // 整个测试类之前
    public static function setUpBeforeClass()
    {
        static::$config = parse_ini_file(dirname(__DIR__) . '/hclient.ini');

    }
}
