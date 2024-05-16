<?php
namespace hclient\base;

use hclient\protocols\HttpRequest;
use hclient\protocols\HttpResponse;
use hclient\transports\Transport;
use hclient\formatters\FormatterInterface;
use hclient\formatters\ParserInterface;
use Exception;

/**
 * http 客户端
 *<B>说明：</B>
 *<pre>
 * 关键词:request,response,transport(传输协议),format(序列化),Parse(反序列化),Api(外部系统api)
 * 一个url 就是一个Request 类
 * client 单例类
 *</pre>
 *<B>示例：</B>
 *<pre>
 * 实例1:GET HTTP 请求,支持OPTIONS, GET, HEAD, POST, PUT, DELETE, TRACE, CONNECT
 * $client = new Client();
 * $url = ‘http://www.baidu.com/’
 * $response = $client->post($url,['user_id'=>2])->send();
 * // 获取返回结果
 * $response->getData();
 *
 * 实例2:设置request,respone 数据为json格式,json 可为类命名空间
 * $client = new Client();
 * $url = ‘http://api.baidu.com/’
 * $response = $client->post($url,['user_id'=>2])->setFormat('json')->send();
 * $response->setFormat('json')->getData();
 *
 * 实例3:批量请求GET,POST
 * $client = new Client();
 * $request1 = $client->post('http://api.baidu.com/user/get',['user_id'=>2])->setFormat('json');
 * $request2 = $client->post('http://api.baidu.com/user/get',['user_id'=>3])->setFormat('json')
 *
 * $requests = [
 *    ‘user1’=>$request1,
 *    'user2'=>$request2
 * ];
 * // 返回多个Response 对象
 * $responses = $client->batchSend($requests);
 * // 获取user1 返回结果
 * $responses['user1']->setFormat('json')->getData();
 *
 *
 * 批量请求get,post
 *
 * $responses = $client->batch()->post('http://api.baidu.com/user/get',['user_id'=>2])
 * ->post('http://api.baidu.com/user/get',['user_id'=>2])->setFormat('json')
 * ->send();
 * // 获取user1 返回结果
 * $responses['user1']->setFormat('json')->getData();
 *
 * 实例4:验证http 错误
 * $client = new Client();
 * $url = 'http://api.baidu.com/user/get'
 * $response = $client->post($url,['user_id'=>2])->setFormat('json')->send();
 *
 * // 验证是否错误,验证网络,解析数据,Transport（传输层） 是否有错误
 * $response->hasError();
 * // 验证是否网络错误,主要验证header http-code 状态码 是否等于20x
 * $response->hasNetworkError();
 * // 获取错误信息
 * $response->getError();
 *
 * 实例5:设置Request 配置信息
 * $requestConfig = [
 *    'format'=>'json',
 *    'baseUrl'=>'http://api.baidu.com/'
 *    'response'=>[
 *      'format'=>'json',
 *    ]
 * ]
 *
 * $response = $client->post($url,['user_id'=>2],$requestConfig)->send();
 *
 * 其他配置如下
 * $requestConfig = [
 *    'format'=>'json',// http data 序列化，可支持类命名空间
 *    'baseUrl'=>'http://www.baidu.com/',// http 域名或ip
 *    'method'=>'POST',// http method,支持OPTIONS, GET, HEAD, POST, PUT, DELETE, TRACE, CONNECT
 *    'options'=>[CURLOPT_CONNECTTIMEOUT=>6],//,设置超时6秒， Transport 配置项，比如设置比如超时时间等等,
 *    'response'=>[
 *       'format'=>'json',// http content 反序列化，可支持类命名空间
 *    ]
 * ];
 *
 * 实例6:其他方法
 * Request 方法
 *      $Request->setMethod(); // 设置http method
 *      $Request->setOptions(); // 设置http Transport配置
 *      $Request->addOptions(); // 累加http Transport配置
 * Response 方法
 *      $Response->getError();// 获取错误信息
 *      $Response->getContent();// 获取请求返回原始字符串
 *
 * 实例7:流http传输StreamTransport
 * $client = new Client();
 * $client->setTransport('StreamTransport');
 *
 * 实例8: 根据http配置发送http
 * 	$config = [
        '123'=>[
            'class'=>'',//
            'baseUrl'=>'http://mapi.dev.vd.cn/',
            'format'=>'json',
            'transport'=>'CurlTransport',
            'headers'=>[],// http header
            'response'=>[
            'format'=>'json'
            ],// response 配置
            'options'=>[],// Transport配置
            'method'=>'POST',// HTTP method
        ]
    ];
 * $client = new Client($config);
 * $respone = $client->one('123','shop/detail',['shop_id'=>20])->send();
 * $data = $respone->getData();
 *
 * 实例9: socket 传输，可支持http,扩展Request 类，支持多种jsonRPC
 * 	$config = [
        '123'=>[
            'class'=>'',//
            'baseUrl'=>'http://mapi.dev.vd.cn/',
            'format'=>'json',
            'transport'=>'SocketTransport',
            'headers'=>[],// http header
            'response'=>[
            'format'=>'json'
            ],// response 配置
            'options'=>[],// Transport配置
            'method'=>'POST',// HTTP method
        ]
    ];
 * $client = new Client($config);
 * $respone = $client->one('123','shop/detail',['shop_id'=>20])->send();
 * $data = $respone->getData();
 *
 * 实例10: 日志
 *
 *</pre>
 *<B>日志：</B>
 *<pre>
 *  略
 *</pre>
 *<B>注意事项：</B>
 *<pre>
 *  略
 *</pre>
 * @method HttpRequest post($url, $data = null,$options = [])
 * @method array|string postResult($url, $data = null,$options = [])
 * @method HttpRequest get($url, $data = null,$options = [])
 * @method array|string getResult($url, $data = null,$options = [])
 * @method HttpRequest put($url, $data = null,$options = [])
 * @method array|string putResult($url, $data = null,$options = [])
 * @method HttpRequest patch($url, $data = null,$options = [])
 * @method array|string patchResult($url, $data = null,$options = [])
 * @method HttpRequest delete($url, $data = null,$options = [])
 * @method array|string deleteResult($url, $data = null,$options = [])
 * @method HttpRequest head($url, $data = null,$options = [])
 * @method array|string headResult($url, $data = null,$options = [])
 *
 *
 * @method array|string serviceResult($systemName,$url = '', $data = [],$requestConfig = [])
 */
class BaseClient
{
    /**
     * json format 名称
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var string
     */
    const FORMAT_JSON = 'json';

    /**
     * xml format　名称
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var string
     */
    const FORMAT_XML = 'xml';

    /**
     * none format　名称
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var string
     */
    const FORMAT_NONE = 'none';

    /**
     * Formatter 对象实例
     *<B>说明：</B>
     *<pre>
     * 单例
     * 请求过程创建的Formatter 对象存储于此
     * 格式:
     * ['对象名'=>'对象'] ，比如['json'=>new JsonFormatter()]
     *</pre>
     * @var FormatterInterface[]
     */
    public $formatters = [
        self::FORMAT_JSON => 'JsonFormatter',
        self::FORMAT_XML => 'XmlFormatter',
        self::FORMAT_NONE => 'NoneFormatter',
    ];

    /**
     * Parser 对象实例
     *<B>说明：</B>
     *<pre>
     * 单例
     *  请求过程创建的Parser 对象存储于此
     * 格式:
     * ['对象名'=>'对象'] ，比如['json'=>new JsonParser()]
     *</pre>
     * @var FormatterInterface[]
     */
    public $parsers = [
        self::FORMAT_JSON => 'JsonParser',
        self::FORMAT_XML => 'XmlParser',
        self::FORMAT_NONE => 'NoneParser',
    ];

    /**
     * http 传输服务对象
     *<B>说明：</B>
     *<pre>
     * 可支持对象，字符串，数组,闭包方法
     *</pre>
     * @var Transport|array|string|callable HTTP message transport
     */
    public $transport = 'CurlTransport';

    /**
     * 传输协议对象集合存储于此
     * 格式:
     * ['对象名'=>'对象'] ，比如['SocketTransport'=>new SocketTransport()]
     * @var Transport[]
     */
    protected $transports = [
        'curl'=>'CurlTransport',
        'socket'=>'SocketTransport',
        'stream'=>'StreamTransport',
    ];

    /**
     * 自定义站点信息
     * 格式:
     * [
     *    '站点名(自定义)'=>['request'=>[
     *        'class'=>'request class namespace',//
     *        'baseUrl'=>'系统域名或ip',
     *        'headers'=>[],// http header
     *        'response'=>[],// response 配置
     *        'options'=>[],// Transport配置
     *        'method'=>'POST',// HTTP method
     *    ]]
     * ]
     * @var Site[]
     */
    public $customSites = [];


    /**
     * 站点对象列表
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @var Site[]
     */
    protected $_sites = [];

    public function __construct($attrs = [])
    {
        if (!empty($attrs)) {
            foreach ($attrs as $name => $value) {
                $this->$name = $value;
            }
        }
    }

    /**
     * 设置默认传输协议对象
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @param Transport|array|string $transport HTTP message transport
     * @return self
     */
    public function setTransport(string $transport):self
    {
        $this->transport = $transport;

        return $this;
    }

    /**
     * 注册传输协议
     * @param string $transport_alias
     * @param string $transport_class
     */
    public function installTransport(string $transport_alias,string $transport_class):self
    {
        $this->transports[$transport_alias] = $transport_class;

        return $this;
    }

    /**
     * 获取传输协议实例
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @param string $transport 传输协议名
     * @return Transport
     */
    public function getTransport(string $transport = null):Transport
    {

        if (empty($transport)) {
            $transport = $this->transport;
        }

        if (isset($this->transports[$transport])) {
            $custom_transport = $this->transports[$transport];
        } else {
            $custom_transport = $transport;
        }

        if (strpos($custom_transport,'\\') !== false) {
            $transportClass =  $custom_transport;
        } else {
            // 判断是否存在后缀Transport
            if (substr($custom_transport,-9) == 'Transport') {
                $transportClass =  substr(__NAMESPACE__,0,strrpos(__NAMESPACE__,"\\")) . '\\transports\\' . ucfirst($custom_transport);
            } else {
                $transportClass =  substr(__NAMESPACE__,0,strrpos(__NAMESPACE__,"\\")) . '\\transports\\' . ucfirst($custom_transport) . 'Transport';
            }
        }

        $this->transports[$transport] = new $transportClass();

        return $this->transports[$transport];
    }

    public function installFormat($alias,$formatter_class,$parser_class):self
    {
        $this->formatters[$alias] = $formatter_class;
        $this->parsers[$alias] = $parser_class;

        return $this;
    }


    /**
     * 获取格式化序列化对象
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @param string $format format name
     * @return FormatterInterface
     */
    public function getFormatter(string $format):FormatterInterface
    {

        if (!isset($this->formatters[$format]) || !is_object($this->formatters[$format])) {
            $formatClass = $this->formatters[$format];
            if (!empty($formatClass)) {
                if (strpos($formatClass,'\\') === false) {
                    $formatClass =  substr(__NAMESPACE__,0,strrpos(__NAMESPACE__,"\\")) . '\\formatters\\' . $formatClass;
                }
            } else {
                $formatClass = $format;
            }

            $this->formatters[$format] = new $formatClass();
        }

        return $this->formatters[$format];
    }

    /**
     * 获取格式化反序列化对象
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @param string $format parser name
     * @return ParserInterface parser 实例.
     */
    public function getParser(string $format):ParserInterface
    {

        if (!isset($this->parsers[$format]) || !is_object($this->parsers[$format])) {
            $formatClass = $this->parsers[$format];
            if (!empty($formatClass)) {
                if (strpos($formatClass,'\\') === false) {
                    $formatClass =  substr(__NAMESPACE__,0,strrpos(__NAMESPACE__,"\\")) . '\\formatters\\' . $formatClass;
                }
            } else {
                $formatClass = $format;
            }

            $this->parsers[$format] = new $formatClass();
        }

        return $this->parsers[$format];
    }

    /**
     * 获取站点对象
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @param string $siteName 站点名
     * @return Site
     * @throws Exception 站点找不到
     */
    protected function getSite(string $siteName):Site
    {
        if (isset($this->_sites[$siteName])) {
            return $this->_sites[$siteName];
        }

        if (!isset($this->customSites[$siteName])) {
            throw new Exception("site {$siteName} no find");
        }

        $options = $this->customSites[$siteName];
        $this->_sites[$siteName] = $this->createSite($options);

        return $this->_sites[$siteName];
    }

    /**
     * 创建一个预定义的站点对象
     * @param string $siteName 站点别名
     * @param array $options 站点配置
     * @return Site
     */
    public function site(string $siteName = '',array $options = []):Site
    {
        if (isset($this->customSites[$siteName])) {
            $options = array_merge($this->customSites[$siteName],$options);
        }

        return $this->createSite($options);
    }

    /**
     * 创建一个全新站点对象
     * @param string|array $baseUrl 路径
     * @return Site
     */
    public function createSite($baseUrl = ''):Site
    {
        $options = [];
        if (!empty($baseUrl)) {
            if (is_array($baseUrl)) {
                $options = $baseUrl;
            } else {
                $options = [
                    'baseUrl'=>$baseUrl,
                ];
            }
        }

        $site_attrs = [
            'client'=>$this,
            'options'=>$options
        ];

        return new Site($site_attrs);
    }

    /**
     * 添加站点配置
     * @param string $siteName 站点名称
     * @param array $options 站点配置
     * @return $this
     */
    public function addSite(string $siteName,array $options = []):self
    {
        $this->customSites[$siteName] = $options;

        return $this;
    }

    /**
     * 创建Request 对象
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @param array $config 配置,主要是request 的属性
     * @return HttpRequest request 实例
     */
    public function createRequest(array $config = []):Request
    {

        if (is_array($config) && isset($config['class'])) {
            $requestClass = $config['class'];
            unset($config['class']);
            if (strpos('\\',$requestClass) !== false) {
                $requestClass = substr(__NAMESPACE__,0,strrpos(__NAMESPACE__,"\\")) . '\\protocols\\' . ucfirst($requestClass);
            }
            $request = new $requestClass($config);
        } else {
            $request = new HttpRequest($config);
        }

        return $request;
    }

    /**
     * 发送请求
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @param Request|null $request 发送的request 对象
     * @return HttpResponse response 实例
     */
    public function send(Request $request =  null):Response
    {
        return $this->getTransport($request->getTransport())->send($request)->getResponse();
    }

    /**
     * 批量推送request 请求
     *<B>说明：</B>
     *<pre>
     * 此方法接受[请求] ]对象的数组，并返回一个[响应]对象的数组。
     * 响应数组的键对应于请求数组的键
     * $client = new Client();
     * $requests = [
     *     'news' => $client->get('http://domain.com/news'),
     *     'friends' => $client->get('http://domain.com/user/friends', ['userId' => 12]),
     * ];
     * $responses = $client->batchSend($requests);
     * var_dump($responses['news']->hasError());
     * var_dump($responses['friends']->hasError());
     *</pre>
     * @param Request[] $requests requests to perform.
     * @return HttpResponse[] responses list
     */
    public function batchSend(array $requests):array
    {
        // 保留request key
        foreach ($requests as $index=>$request) {
            $request->setIndex($index);
        }

        // 按transport 归类 request
        $classRequestList = $this->classRequestsByTransport($requests);
        foreach ($classRequestList as $transport=>$reqs) {
            $this->getTransport($transport)->batchSend($reqs);
        }

        $responses = [];
        foreach ($requests as $index=>$request) {
            $responses[$request->getIndex()] = $request->getResponse();
        }

        return $responses;
    }

    /**
     * 创建批量请求组
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @return RequestGroup
     */
    public function batch():RequestGroup
    {
        return new RequestGroup(['client'=>$this]);
    }

    /**
     * 按传输协议归类Requests
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @param Request[] $requests
     * @return Request[][] $requests list
     */
    public function classRequestsByTransport(array $requests):array
    {
        $newRequests = [];
        foreach ($requests as $index=>$request) {
            $newRequests[$request->getTransport()][$index] = $request;
        }

        return $newRequests;
    }


    /**
     * 创建一个 Request 对象
     *<B>说明：</B>
     *<pre>
     *　根据$systemName 系统名称创建Request 对象
     *</pre>
     * @param string $site_alias 站点别
     * @param string $url 目标地址
     * @param array|string $data 数据
     * @param array $options Request 配置
     * @return Request Request 实例
     */
    public function service(string $site_alias,string $url = '', array $data = [],array $options = []):Request
    {
        $site = $this->getSite($site_alias);

        return $site->service($url,$data,$options);
    }

    /**
     * 创建Request 快捷方法
     *<B>说明：</B>
     *<pre>
     *　不支持api
     *</pre>
     * @param string $method 方法名
     * @param string $url 目标地址
     * @param array|string $data request date request content
     * @param array $config request 配置
     * @return Request request 实例
     */
    protected function createRequestShortcut(string $method, string $url, array $data = null,array $config = []):Request
    {
        if ($method === 'head') {
            $config['_headers'] = $data;
        } else if ($method === 'options') {
            $config['options'] = $data;
        }

        $request = $this->createRequest($config)
            ->setClient($this)
            ->setMethod($method)
            ->setUrl($url);

        if (is_array($data)) {
            $request->setData($data);
        } else {
            $request->setContent($data);
        }

        return $request;
    }


    /**
     * 调用默认存储方法
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $method 方法名称
     * @param array $parameters 参数
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        // 计算名称
        if (substr($method,-6) == 'Result') {
            $methodName = substr($method,0,strlen($method) - 6);
            /**@var Request $request  */
            if (method_exists($this,$methodName)) {
                $request = call_user_func_array([$this,$methodName],$parameters);
            } else {
                $request = $this->createRequestShortcut($methodName, ...$parameters);
            }

            return $request->send()->getData();
        } else {
            $request = $this->createRequestShortcut($method, ...$parameters);

            return $request;
        }
    }
}
