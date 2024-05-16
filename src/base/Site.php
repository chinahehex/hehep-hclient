<?php
namespace hclient\base;

use hclient\protocols\HttpRequest;

/**
 * 服务站点
 *<B>说明：</B>
 *<pre>
 * 略
 *</pre>
 *
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
 */
class Site
{

    /**
     * 请求客户端
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @var BaseClient
     */
    protected $client = null;

    /**
     * 请求配置
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @var array
     */
    protected $options = null;

    public function __construct($attrs = [])
    {
        if (!empty($attrs)) {
            foreach ($attrs as $name => $value) {
                $this->$name = $value;
            }
        }


    }

    public function setFormat($format)
    {
        $this->options['format'] = $format;

        return $this;
    }

    public function setTransport($transport):self
    {
        $this->options['transport'] = $transport;

        return $this;
    }

    public function setBaseUrl($baseUrl)
    {
        $this->options['baseUrl'] = $baseUrl;

        return $this;
    }

    /**
     * 设置请求配置
     * @param array $request_config
     * @return $this
     */
    public function setRequest($request_config = []):self
    {
        $this->options = array_merge($this->options,$request_config);

        return $this;
    }

    /**
     * 设置响应配置
     * @param array $response_config
     * @return $this
     */
    public function setResponse($response_config):self
    {
        if (isset($this->options['response'])) {
            $this->options['response'] = array_merge($this->options['response'],$response_config);
        } else {
            $this->options['response'] = $response_config;
        }

        return $this;
    }

    /**
     * 创建api 请求对象
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @param string $url api 地址
     * @param array $data 参数
     * @param array $options request 配置信息
     * @return Request
     */
    public function service(string $url = '', array $data = null,array $options = []):Request
    {
        $siteConf = array_merge($this->options,$options);
        $siteRequest = $this->client->createRequest($siteConf);
        $siteRequest->setUrl($url);

        if (is_array($data)) {
            $siteRequest->setData($data);
        } else {
            $siteRequest->setContent($data);
        }

        return $siteRequest;
    }

    /**
     * 发送api 请求
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @param string $url api 地址
     * @param array $data 参数
     * @param array $options request 配置信息
     * @return mixed
     */
    public function serviceResult(string $url = '', array $data = null,array $options = [])
    {
        $siteRequest = $this->service($url,$data,$options);

        return $siteRequest->send()->getData();
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
     * @param array $options request 配置
     * @return Request request 实例
     */
    protected function createRequestShortcut(string $method,string $url, array $data = null,array $options = []):Request
    {
        $opts = array_merge($this->options,$options);
        $request = call_user_func_array([$this->client,$method],[$url,$data,$opts]);

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
        if (substr($method,-6) == 'Result') {
            $methodName = substr($method,0,strlen($method) - 6);
            $request = $this->createRequestShortcut($methodName,...$parameters);

            return $request->send()->getData();
        } else {
            return $this->createRequestShortcut($method,...$parameters);
        }
    }
}
