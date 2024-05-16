<?php
namespace hclient\tests;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \hclient\Client
     */
    protected $hclient;

    protected function setUp()
    {
        $this->hclient = new \hclient\Client();
    }
}
