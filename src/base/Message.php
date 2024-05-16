<?php
namespace hclient\base;

use hclient\Client;
use ReflectionMethod;

/**
 * Base HTTP message 类
 *<B>说明：</B>
 *<pre>
 * 可以演化成request,response
 *</pre>
 */
class Message
{

    /**
     * client manager 实例
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @var Client
     */
    public $client;

    /**
     * headers 集合对象
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @var Headers
     */
    protected $headers;

    /**
     * cookies 集合对象
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @var Cookies
     */
    protected $cookies;

    /**
     * request,response 原始内容
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @var string|null
     */
    protected $content;

    /**
     * request,response 数据
     *<B>说明：</B>
     *<pre>
     *　request 格式化之前数据,或response格式化之后数据
     *</pre>
     * @var array|null
     */
    protected $data = null;

    /**
     * 格式化名称
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @var string
     */
    protected $format;

    public function __construct($attrs = [])
    {
        if (!empty($attrs)) {
            foreach ($attrs as $name => $value) {
                $this->$name = $value;
            }
        }
    }

    /**
     * 设置Client
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @param Client $Client 配置信息
     * @return $this;
     */
    public function setClient(Client $Client = null)
    {
        $this->client = $Client;

        return $this;
    }

    /**
     * 设置http 头部信息
     *<B>说明：</B>
     *<pre>
     *　格式: [headerName => headerValue]
     *</pre>
     * @param array $headers 配置信息
     * @return $this;
     */
    public function setHeaders($headers):self
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * 获取header 集合对象
     *<B>说明：</B>
     *<pre>
     *　如 headers 还是数组，则转换 Headers 对象
     *</pre>
     * @return Headers
     */
    public function getHeaders():Headers
    {

        if (!is_object($this->headers)) {
            $headerList = new Headers();
            if (is_array($this->headers)) {
                foreach ($this->headers as $name => $value) {
                    if (is_int($name)) {
                        // parse raw header :
                        $rawHeader = $value;
                        if (($separatorPos = strpos($rawHeader, ':')) !== false) {
                            $name = strtolower(trim(substr($rawHeader, 0, $separatorPos)));
                            $value = trim(substr($rawHeader, $separatorPos + 1));
                            $headerList->add($name, $value);
                        } elseif (strpos($rawHeader, 'HTTP/') === 0) {
                            $parts = explode(' ', $rawHeader, 3);
                            $headerList->add('http-code', $parts[1]);
                        } else {
                            $headerList->add('raw', $rawHeader);
                        }
                    } else {
                        $headerList->set($name, $value);
                    }
                }
            }

            $this->headers = $headerList;
        }

        return $this->headers;
    }

    /**
     * 添加http header 信息
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @param array $headers
     * @return $this
     */
    public function addHeaders(array $headers = []):self
    {
        $headerList = $this->getHeaders();
        foreach ($headers as $name => $value) {
            $headerList->add($name, $value);
        }

        return $this;
    }

    /**
     * 是否设置过header
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @return boolean true or false
     */
    public function hasHeaders():bool
    {
        if (is_object($this->headers)) {
            return $this->headers->getCount() > 0;
        }

        return !empty($this->headers);
    }

    /**
     * 设置cookie
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @param array $cookies
     * @return $this
     */
    public function setCookies($cookies):self
    {
        $this->cookies = $cookies;

        return $this;
    }

    public function setCookie(string $name,$value,$expire = 0):self
    {
        $cookieAttrs = [
            'name'=>$name,
            'value'=>$value,
            'expire'=>$expire
        ];

        $cookie = new Cookie($cookieAttrs);
        $cookieList = $this->getCookies();
        $cookieList->add($cookie);

        return $this;
    }

    /**
     * 获取cookie 集合对象
     *<B>说明：</B>
     *<pre>
     *　如非Cookies 对象，则将转换成Cookies
     *</pre>
     * @return Cookies
     */
    public function getCookies():Cookies
    {
        if (!is_object($this->cookies)) {
            $cookies = new Cookies();
            if (is_array($this->cookies)) {
                foreach ($this->cookies as $cookie) {
                    if (!is_object($cookie)) {
                        $cookie = new Cookie($cookie);
                    }

                    $cookies->add($cookie);
                }
            }

            $this->cookies = $cookies;
        }

        return $this->cookies;
    }

    /**
     * 添加cookie
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @param Cookie[]|array $cookies additional cookies.
     * @return $this
     */
    public function addCookies(array $cookies):self
    {
        $cookieList = $this->getCookies();
        foreach ($cookies as $cookie) {
            if (!is_object($cookie)) {
                $cookie = new Cookie($cookie);
            }

            $cookieList->add($cookie);
        }

        return $this;
    }

    public function addCookie($cookie):self
    {
        if (!is_object($cookie)) {
            $cookie = new Cookie($cookie);
        }

        $cookieList = $this->getCookies();
        $cookieList->add($cookie);

        return $this;
    }

    /**
     * 是否设置过Cookie
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @return boolean true or false
     */
    public function hasCookies():bool
    {
        if (is_object($this->cookies)) {
            return $this->cookies->getCount() > 0;
        }

        return !empty($this->cookies);
    }

    /**
     * 设置 http request、response　content
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @param string $content raw content.
     * @return $this
     */
    public function setContent($content = ''):self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * 获取 http request、response　content
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * 设置 request data
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @param array $data 数据
     * @return $this
     */
    public function setData($data):self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * 获取 response data
     *<B>说明：</B>
     *<pre>
     *　经过Parser 后的数据
     *</pre>
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * 设置 content data 格式名称
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @param string $format 格式名称
     * @return $this
     */
    public function setFormat(string $format):self
    {
        $this->format = $format;

        return $this;
    }


    /**
     * 获取 content data 格式名称
     *<B>说明：</B>
     *<pre>
     *　如未设置，则返回默认格式名称
     *</pre>
     * @return string
     */
    public function getFormat():string
    {
        if ($this->format === null) {
            $this->format = $this->defaultFormat();
        }

        return $this->format;
    }

    /**
     * 获取默认格式名称
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @return string
     */
    protected function defaultFormat():string
    {
        return Client::FORMAT_NONE;
    }

}
