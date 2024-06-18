<?php
namespace hclient\tests\units;
use hclient\tests\TestCase;

class ExampleTest extends TestCase
{

    public function testCommon()
    {
        // get 请求
        $this->assertRegExp("/".static::$config['siteKeyword']."/",
            $this->hclient->get(static::$config['siteUrl'])->send()->getContent()
        );

        $this->assertRegExp("/".static::$config['siteKeyword']."/",
            $this->hclient->getResult(static::$config['siteUrl'])
        );

    }

    public function testSite()
    {
        $this->hclient->addSite('baidu',['baseUrl'=>static::$config['siteUrl'], 'method'=>'GET']);

        $this->assertRegExp("/".static::$config['siteKeyword']."/",
            $this->hclient->uri('baidu')->send()->getContent()
        );

        $this->assertRegExp("/".static::$config['siteKeyword']."/",
            $this->hclient->uriResult('baidu','')
        );

        $this->assertRegExp("/".static::$config['siteKeyword']."/",
            $this->hclient->site('baidu')->uri()->send()->getContent()
        );

        $this->assertRegExp("/".static::$config['siteKeyword']."/",
            $this->hclient->site('baidu')->uriResult()
        );

        $this->assertRegExp("/".static::$config['siteKeyword']."/",
            $this->hclient->site()->get(static::$config['siteUrl'])->send()->getContent()
        );

        $this->assertRegExp("/".static::$config['siteKeyword']."/",
            $this->hclient->site()->getResult(static::$config['siteUrl'])
        );

        $this->assertRegExp("/".static::$config['siteKeyword']."/",
            $this->hclient->site()->uri(static::$config['siteUrl'])->send()->getContent()
        );

        $this->assertRegExp("/".static::$config['siteKeyword']."/",
            $this->hclient->site()->uriResult(static::$config['siteUrl'])
        );

    }

    public function testBatch()
    {
        $reqeusts = [
            'reqeust1'=>$this->hclient->get(static::$config['siteUrl']),
            'reqeust2'=>$this->hclient->get(static::$config['siteUrl']),
        ];

        $responses = $this->hclient->batchSend($reqeusts);

        $this->assertRegExp("/".static::$config['siteKeyword']."/",
            $responses['reqeust1']->getContent()
        );

        $this->assertRegExp("/".static::$config['siteKeyword']."/",
            $responses['reqeust2']->getContent()
        );


        $reqeusts = [
            $this->hclient->get(static::$config['siteUrl'])->setIndex("reqeust1"),
            $this->hclient->get(static::$config['siteUrl'])->setIndex("reqeust2"),
        ];

        $responses = $this->hclient->batchSend($reqeusts);

        $this->assertRegExp("/".static::$config['siteKeyword']."/",
            $responses['reqeust1']->getContent()
        );

        $this->assertRegExp("/".static::$config['siteKeyword']."/",
            $responses['reqeust2']->getContent()
        );


    }

    // 请求组
    public function testRequestGroup()
    {
        $requestGroup = $this->hclient->batch();
        $requestGroup->get(static::$config['siteUrl'])->setIndex("reqeust1");
        $requestGroup->getResult(static::$config['siteUrl'])->setIndex("reqeust2");

        $responses = $requestGroup->send();

        $this->assertRegExp("/".static::$config['siteKeyword']."/",
            $responses['reqeust1']->getContent()
        );

        $this->assertRegExp("/".static::$config['siteKeyword']."/",
            $responses['reqeust2']
        );
    }

    public function testDefaultTransport()
    {
        $this->hclient->setTransport('stream');

        $this->assertRegExp("/".static::$config['siteKeyword']."/",
            $this->hclient->get(static::$config['siteUrl'])->send()->getContent()
        );
    }

    public function testRequestTransport()
    {
        $this->assertRegExp("/".static::$config['siteKeyword']."/",
            $this->hclient->get(static::$config['siteUrl'])->setTransport('stream')->send()->getContent()
        );
    }

    public function testSetHeaders()
    {
        $this->assertRegExp("/".static::$config['siteKeyword']."/",
            $this->hclient->get(static::$config['siteUrl'])->addHeaders([
                'c'=>1,
                'n'=>time(),
            ])->send()->getContent()
        );
    }

    public function testSetCookie()
    {
        $this->assertRegExp("/".static::$config['siteKeyword']."/",
            $this->hclient->get(static::$config['siteUrl'])->setCookie("ok","value",60 * 30)->send()->getContent()
        );
    }

    public function testError()
    {
        $response = $this->hclient->get(static::$config['siteUrl'] . '/olklllll')->send();
        $this->assertTrue($response->hasError());
        $this->assertEquals($response->getStatusCode(),'404');
        $this->assertTrue($response->hasNetworkError());
    }
}
