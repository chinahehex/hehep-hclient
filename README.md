# hehep-hclient

## 介绍
- hehep-hclient 是一个PHP客户端工具组件

## 安装
- 直接下载:
```

```
- 命令安装：
```
composer require hehep/hclient
```

## 组件配置
- 基础配置
```php

$conf = [
    // 默认传输协议
    'transport'=>'CurlTransport',// 目前提供CurlTransport,SocketTransport,StreamTransport 三种传输协议
    // request 对象格式对象
    'formatters'=>[
        'json'=>'JsonFormatter',
        'xml'=>'XmlFormatter',
        'none'=>'NoneFormatter'
    ],

    // response 对象格式对象
    'parsers'=>[
        'json'=>'JsonParser',
        'xml'=>'XmlParser',
        'none'=>'NoneParser'
    ],
    // 自定义站点信息
    'customSites'=>[
        'md'=>[
            // 请求类相关属性
            'class'=>'hclient\base\Request',
            'baseUrl'=>'http://xxxx.xxxx.cn/',// 站点域名或地址
            'method'=>'POST',// http 方法
            'format'=>'json',// 格式化名称
            'options'=>[],// 传输协议参数
            'response'=>[
                // 响应类相关属性
                'class'=>'hclient\extend\ServiceResponse',
                'format'=>'json',// 格式化名称
            ],
        ],
        'sina'=>[
            'baseUrl'=>'http://blog.sina.com.cn/',
            'method'=>'GET'
        ],
        'baidu'=>[
            'baseUrl'=>'http://www.thinkphp.cn/',
            'method'=>'GET'
        ]
    ],
];

```

## 基本示例

- 普通请求　GET HTTP 请求,支持OPTIONS, GET, HEAD, POST, PUT, DELETE, TRACE, CONNECT
```php
use hclient\Client;
$hclient = new Client();

// 发送get 请求
$html = $hclient->get("http://www.baidu.com/")->send()->getContent();

// 设置请求参数
$html = $hclient->get("http://www.baidu.com/",['id'=>1])->send()->getContent();

// 简写方式
$html = $hclient->getResult("http://www.baidu.com/",['id'=>1]);

// 发送post 请求
$html = $hclient->post("http://www.baidu.com/",['id'=>1])->send()->getContent();
// 简写方式
$html = $hclient->postResult("http://www.baidu.com/",['id'=>1]);

// 其他请求类
$html = $hclient->xxxResult("http://www.baidu.com/",['id'=>1]);

```
## 站点请求

- 添加站点
```php
use hclient\Client;
$hclient = new Client();

// 添加站点
$hclient->addSite('sina',[
			'baseUrl'=>'https://sports.sina.com.cn/',
			'method'=>'GET'
]);

$response = $hclient->service("baidu","system/site/siteInit",['id'=>1])->send();
// 获取返回的原始内容
$content = $response->getContent();

// 获取格式化后的内容
$data = $response->setFormat("json")->getData();

// 直接获取格式化后数据
$data = $hclient->serviceResult("baidu","system/site/siteInit",['id'=>1]);

```

- 站点post,get 请求
```php
use hclient\Client;
$hclient = new Client();
$hclient->site('baidu')->get('system/site/siteInit');
$hclient->site('baidu')->post('system/site/siteInit',['id'=>1]);
$hclient->site('baidu')->getResult('system/site/siteInit');
$hclient->site('baidu')->postResult('system/site/siteInit',['id'=>1]);
```

- 创建一个预定义站点
```php
use hclient\Client;
$hclient = new Client();
$response = $hclient->site('baidu')
    ->service('system/site/siteInit',['id'=>1])
    ->send();

```

- 创建一个全新站点
```php
use hclient\Client;
$hclient = new Client();
$response = $hclient->site()
    ->setBaseUrl('https://sports.sina.com.cn/')
    ->service('system/site/siteInit',['id'=>1])
    ->send();

```

## 批量请求

- 常规
```php
use hclient\Client;
$hclient = new Client();

// 方式1
$reqeusts = [
    'reqeust1'=>$hclient->service("md","system/site/siteInit",['id'=>1]),
    'reqeust2'=>$hclient->service("md","system/site/siteInit",['id'=>2]),
];

$responses = $hclient->batchSend($reqeusts);
$content = $responses['reqeust1']->getContent();
$content = $responses['reqeust2']->getContent();


// 方式2
$reqeusts = [
    $hclient->service("md","system/site/siteInit",['id'=>1])->setIndex('reqeust1'),
    $hclient->service("md","system/site/siteInit",['id'=>2])->setIndex('reqeust2'),
];

$responses = $hclient->batchSend($reqeusts);
$content = $responses['reqeust1']->getContent();
$content = $responses['reqeust2']->getContent();

```

- 创建请求组
```php
use hclient\Client;
$hclient = new Client();

$requestGroup = $this->hclient->batch();
$requestGroup->getResult('http://www.baidu.com')->setIndex("reqeust1");
$requestGroup->getResult('http://www.baidu.com')->setIndex("reqeust2");
$res_result = $requestGroup->send();
$res_result['reqeust1'];
$res_result['reqeust2'];

$requestGroup = $this->hclient->batch();
$requestGroup->get('http://www.baidu.com')->setIndex("reqeust1");
$requestGroup->getResult('http://www.baidu.com')->setIndex("reqeust2");
$res_result = $requestGroup->send();	
$res_result['reqeust1'];
$res_result['reqeust2'];

// reqeust1 返回的结果是response 对象
$response = $res_result['reqeust1'];
$html = $res_result['reqeust2'];

```

## 设置格式化
```
目前支持三种数据格式化,分别为
json,xml,none(无)
```
- 安装格式化
```php
use hclient\Client;
$hclient = new Client();
$hclient->installFormat('json','hclient\formatters\JsonFormatter','hclient\formatters\JsonParser');

```

- 设置单个请求格式化
```php
use hclient\Client;
$hclient = new Client();
// $request_data json 编码后传输
$request_data = ['id'=>1];
$response = $hclient->post('http://www.baidu.com',[$request_data])->setFormat('json')->send();

// 对返回结果json 解码
$data = $response->setFormat('json')->getData();

```

## 传输协议
```
目前支持三种传输协议,分别为
curl,socket,stream
```

- 安装传输协议
```php
use hclient\Client;
$hclient = new Client();

$hclient->installTransport('curl','hclient\transports\CurlTransport');

```
- 设置默认传输协议
```php
use hclient\Client;
$hclient = new Client();
$hclient->setTransport('curl');
```

- 设置单个请求传输协议
```php
use hclient\Client;
$hclient = new Client();
$hclient->post('http://www.baidu.com')->setTransport('curl')->send();
```

- 设置传输协议相关参数
```php
use hclient\Client;
$hclient = new Client();
$reqeust = $hclient->service("md","system/site/siteInit",['id'=>1]);

// 设置传输协议相关参数
$reqeust->setTransportOptions([
    CURLOPT_RETURNTRANSFER=>true,
    CURLOPT_POST=>'POST'
]);


```

## 设置信息
- 设置头部信息
```php
use hclient\Client;
$hclient = new Client();
$reqeust = $hclient->service("md","system/site/siteInit",['id'=>1]);
$reqeust->addHeaders([
    'c'=>1,
    'n'=>time(),
]);

```

- 设置cookies
```php
use hclient\Client;
$hclient = new Client();
$reqeust = $hclient->service("md","system/site/siteInit",['id'=>1]);
$reqeust->addCookie([
    "name"=>"ok",
    'value'=>"12121"
]);

$reqeust->setCookie("ok","value",60 * 30);

```

- 设置Method
```php
use hclient\Client;
$hclient = new Client();
$reqeust = $hclient->service("md","system/site/siteInit",['id'=>1]);

// 设置http method
$reqeust->setMethod("post");
$reqeust->setMethod("get");

```

## 错误处理
- 验证请求错误信息
```php
use hclient\Client;
$hclient = new Client();
$reqeust = $hclient->service("md","system/site/siteInit",['id'=>1]);
$response = $reqeust->send();

// 验证是否错误(验证网络,解析数据,Transport（传输层） 是否有错误)
if ($response->hasError()) {
    echo "error";
} else {
    echo "succeed";
}

// 验证是否网络错误(主要验证header http-code 状态码 是否等于20x)
if ($response->hasNetworkError()) {
    echo "error";
} else {
    echo "succeed";
}

// 获取错误信息
$response->getError();

```

### 格式化,序列化
```
目前支持三种json,xml,none(无须序列化)
```

- 自定义序列化类
```php
namespace hclient\formatters;

use hclient\base\Request;

class JsonFormatter implements FormatterInterface
{

    public $encodeOptions = 0;
    
    // 实现此方法
    public function format(Request $request)
    {
        $request->getHeaders()->set('Content-Type', 'application/json; charset=UTF-8');
        $request->setContent(json_encode($request->getData(), $this->encodeOptions));

        return $request;
    }
}
```

- 自定义反序列化类
```php
namespace hclient\formatters;
use hclient\base\Response;

/**
 * Response json 反序列化
 *<B>说明：</B>
 *<pre>
 * 略
 *</pre>
 */
class JsonParser implements ParserInterface
{
    // 实现此方法
    public function parse(Response $response)
    {
        // 错误码
        $decode = json_decode((string) $response->getContent(), true);
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                break;
            case JSON_ERROR_DEPTH:
                $response->addError('json decode error:#The maximum stack depth has been exceeded.');
                break;
            case JSON_ERROR_CTRL_CHAR:
                $response->addError('json decode error:#Control character error, possibly incorrectly encoded.');
                break;
            case JSON_ERROR_SYNTAX:
                $response->addError('json decode error:#Syntax error.');
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $response->addError('json decode error:#Invalid or malformed JSON.');
                break;
            case JSON_ERROR_UTF8:
                $response->addError('json decode error:#Malformed UTF-8 characters, possibly incorrectly encoded.');
                break;
            default:
                $response->addError('json decode error:#Unknown JSON decoding error.');
                break;
        }

        return $decode;
    }
}
```

## 自定义用户协议
```
目前支持三种http
```

## 自定义传输协议
```
目前支持三种curl,socket,stream
```

- 自定义传输协议类
```php
namespace  hclient\transports;
use Exception;

class StreamTransport extends Transport
{
    // 实现此方法
    public function send($request)
    {
        // 发送单个请求

        return $request;
    }
    
    // 扩展此方法
    public function batchSend($requests)
    {
        // 批量发送多个请求
    }

}
```


## 任务列表
- 增加协议mqtt

