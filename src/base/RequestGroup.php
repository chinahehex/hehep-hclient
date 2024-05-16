<?php
namespace hclient\base;

use Exception;
use hclient\Client;
use hclient\protocols\HttpRequest;


/**
 * request 请求组类
 *<B>说明：</B>
 *<pre>
 * 略
 *</pre>
 * @method HttpRequest post($url, $data = null,$options = [])
 * @method HttpRequest postResult($url, $data = null,$options = [])
 * @method HttpRequest get($url, $data = null,$options = [])
 * @method HttpRequest getResult($url, $data = null,$options = [])
 * @method HttpRequest put($url, $data = null,$options = [])
 * @method HttpRequest putResult($url, $data = null,$options = [])
 * @method HttpRequest patch($url, $data = null,$options = [])
 * @method HttpRequest patchResult($url, $data = null,$options = [])
 * @method HttpRequest delete($url, $data = null,$options = [])
 * @method HttpRequest deleteResult($url, $data = null,$options = [])
 * @method HttpRequest head($url, $data = null,$options = [])
 * @method HttpRequest headResult($url, $data = null,$options = [])
 */
class RequestGroup
{
    /**
     * 批量发送请求临时存储
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @var Request[]
     */
    protected $requests = [];

    /**
     * client manager 实例
     *<B>说明：</B>
     *<pre>
     *　不支持api
     *</pre>
     * @var Client
     */
    public $client;


    public function __construct($attrs = [])
    {
        if (!empty($attrs)) {
            foreach ($attrs as $name => $value) {
                $this->$name = $value;
            }
        }
    }

    /**
     * 发送单个请求
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @param string $siteName 系统名称
     * @param string $url 目标地址
     * @param array|string $data 数据
     * @param array $options Request 配置
     * @throws Exception $siteName 配置不存在
     * @return Request
     */
    public function service(string $siteName,string $url = '', array $data = [],array $options = []):Request
    {
        $request = $this->client->service($siteName,$url,$data,$options);

        $this->addRequest($request);

        return $request;
    }

    /**
     * 发送单个请求
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @param string $siteName 系统名称
     * @param string $url 目标地址
     * @param array|string $data 数据
     * @param array $options Request 配置
     * @return Request
     */
    public function serviceResult(string $siteName,string $url = '', array $data = [],array $options = []):Request
    {
        $request = $this->client->service($siteName,$url,$data,$options);
        $request->asResult();
        $this->addRequest($request);

        return $request;
    }

    protected function addRequest(Request $request)
    {
        $this->requests[] = $request;

        return;
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
    protected function createRequestShortcut(string $method, string $url, array $data = null,array $options = []):Request
    {

        $request = call_user_func_array([$this->client,$method],[$url,$data,$options]);

        $this->addRequest($request,$index);

        return $request;
    }

    /**
     * 发送当前请求
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @return Response[] response 实例
     */
    public function send():array
    {
        $responses = $this->client->batchSend($this->formatRequests());
        $result = [];
        foreach ($responses as $index=>$response) {
            /** @var Request $request */
            $request = $response->getRequest();
            if ($request->isResult()) {
                $result[$index] = $response->getData();
            } else {
                $result[$index] = $response;
            }
        }

        return $result;
    }

    /**
     * 发送当前请求并返回结果
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @return array
     */
    public function sendResult()
    {
        $responses = $this->client->batchSend($this->formatRequests());
        $result = [];
        foreach ($responses as $index=>$response) {
            $result[$index] = $response->getData();
        }

        return $result;
    }

    protected function formatRequests()
    {
        $requests = [];
        foreach ($this->requests as $index=>$request) {
            /** @var Request $request */
            if ($request->hasIndex()) {
                $requests[$request->getIndex()] = $request;
            } else {
                $requests[$index] = $request;
            }
        }

        return $requests;
    }

    /**
     * 调用默认存储方法
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $method 方法名称
     * @param array $parameters 参数
     * @return Request
     */
    public function __call($method, $parameters):Request
    {
        $suffix = substr($method,-6);
        if ($suffix == 'Result') {
            $methodName = substr($method,0,strlen($method) - 6);
            $request = $this->createRequestShortcut($methodName,...$parameters)->asResult();
        } else {
            $request = $this->createRequestShortcut($method,...$parameters);
        }

        return $request;
    }

}
