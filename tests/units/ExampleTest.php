<?php
namespace hclient\tests\units;
use hclient\tests\TestCase;

class ExampleTest extends TestCase
{

    public function testCommon()
    {
        // get 请求
        $this->assertRegExp('/11000002000001号/',
            $this->hclient->get('https://www.baidu.com/')->send()->getContent()
        );

        $this->assertRegExp('/11000002000001号/',
            $this->hclient->getResult('https://www.baidu.com/')
        );

    }

    public function testSite()
    {
        $this->hclient->addSite('baidu',['baseUrl'=>"https://www.baidu.com/", 'method'=>'GET']);

        $this->assertRegExp('/11000002000001号/',
            $this->hclient->uri('baidu')->send()->getContent()
        );

        $this->assertRegExp('/11000002000001号/',
            $this->hclient->uriResult('baidu','')
        );

        $this->assertRegExp('/11000002000001号/',
            $this->hclient->site('baidu')->uri()->send()->getContent()
        );

        $this->assertRegExp('/11000002000001号/',
            $this->hclient->site('baidu')->uriResult()
        );

        $this->assertRegExp('/11000002000001号/',
            $this->hclient->site()->get('https://www.baidu.com')->send()->getContent()
        );

        $this->assertRegExp('/11000002000001号/',
            $this->hclient->site()->getResult('https://www.baidu.com')
        );

        $this->assertRegExp('/11000002000001号/',
            $this->hclient->site()->uri('https://www.baidu.com')->send()->getContent()
        );

        $this->assertRegExp('/11000002000001号/',
            $this->hclient->site()->uriResult('https://www.baidu.com')
        );

    }

    public function testBatch()
    {
        $reqeusts = [
            'reqeust1'=>$this->hclient->get('https://www.baidu.com/'),
            'reqeust2'=>$this->hclient->get('https://news.baidu.com/'),
        ];

        $responses = $this->hclient->batchSend($reqeusts);

        $this->assertRegExp('/11000002000001号/',
            $responses['reqeust1']->getContent()
        );

        $this->assertRegExp('/百度新闻/',
            $responses['reqeust2']->getContent()
        );


        $reqeusts = [
            $this->hclient->get('https://www.baidu.com/')->setIndex("reqeust1"),
            $this->hclient->get('https://news.baidu.com/')->setIndex("reqeust2"),
        ];

        $responses = $this->hclient->batchSend($reqeusts);

        $this->assertRegExp('/11000002000001号/',
            $responses['reqeust1']->getContent()
        );

        $this->assertRegExp('/百度新闻/',
            $responses['reqeust2']->getContent()
        );


    }

    // 请求组
    public function testRequestGroup()
    {
        $requestGroup = $this->hclient->batch();
        $requestGroup->get('https://www.baidu.com/')->setIndex("reqeust1");
        $requestGroup->getResult('https://news.baidu.com/')->setIndex("reqeust2");

        $responses = $requestGroup->send();

        $this->assertRegExp('/11000002000001号/',
            $responses['reqeust1']->getContent()
        );

        $this->assertRegExp('/百度新闻/',
            $responses['reqeust2']
        );
    }

    public function testDefaultTransport()
    {
        $this->hclient->setTransport('stream');

        $this->assertRegExp('/11000002000001号/',
            $this->hclient->get('https://www.baidu.com/')->send()->getContent()
        );
    }

    public function testRequestTransport()
    {
        $this->assertRegExp('/11000002000001号/',
            $this->hclient->get('https://www.baidu.com/')->setTransport('stream')->send()->getContent()
        );
    }

    public function testSetHeaders()
    {
        $this->assertRegExp('/11000002000001号/',
            $this->hclient->get('https://www.baidu.com/')->addHeaders([
                'c'=>1,
                'n'=>time(),
            ])->send()->getContent()
        );
    }

    public function testSetCookie()
    {
        $this->assertRegExp('/11000002000001号/',
            $this->hclient->get('https://www.baidu.com/')->setCookie("ok","value",60 * 30)->send()->getContent()
        );
    }

    public function testError()
    {
        $response = $this->hclient->get('https://www.baidu.com/olklllll')->send();
        $this->assertTrue($response->hasError());
        $this->assertEquals($response->getStatusCode(),'404');
        $this->assertTrue($response->hasNetworkError());
    }
}
