<?php
namespace hclient\base;

use hclient\Client;
use hclient\formatters\ParserInterface;
use hclient\base\Headers;
use hclient\base\Cookie;

/**
 * response 响应类
 *<B>说明：</B>
 *<pre>
 * 一个Request 对象对应一个Response 类
 *</pre>
 */
class Response extends Message
{

    /**
     * 系统错误
     *<B>说明：</B>
     *<pre>
     * 比如解析数据错误
     *　curl 错误,http-code 错误码
     *</pre>
     * @var array
     */
    protected $errors = [];

    /**
     * 状态码
     *<B>说明：</B>
     *<pre>
     * 如400
     *</pre>
     * @var string
     */
    protected $statusCode = null;

    /**
     * 是否有网络错误
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @var null|boolean
     */
    protected $networkError = null;

    /**
     * Request 对象
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @var Request
     */
    protected $request  = null;

    /**
     * 设置Request 对象
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request = null):self
    {
        $this->request = $request;

        return $this;
    }

    public function getRequest():Request
    {
        return $this->request;
    }

    /**
     * 添加错误
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param string $error
     * @return $this
     */
    public function addError($error):self
    {
        $this->errors[] = $error;

        return $this;
    }

    /**
     *　请求过程中是否有错误
     *<B>说明：</B>
     *<pre>
     * 错误类型有:网络错误，业务错误
     *</pre>
     * @return boolean
     */
    public function hasError():bool
    {
        // 验证是否网络错误
        if ($this->hasNetworkError() === true) {
            return true;
        }

        // 执行获取数据方法,获取Parser 错误
        $this->getData();

        return count($this->errors) > 0;
    }

    /**
     *获取错误消息
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @return string
     */
    public function getError():string
    {
        if (empty($this->errors)) {
            return '';
        } else {
            return implode(',',$this->errors);
        }
    }

    /**
     * 获取第一个错误消息
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @return string|null
     */
    public function getFirstError()
    {
        if (isset($this->errors[0])) {
            return $this->errors[0];
        } else {
            return null;
        }
    }

    public function getData()
    {
        $data = parent::getData();
        if ($data === null) {
            if ($this->format == Client::FORMAT_NONE) {
                return $this->getContent();
            } else {
                $content = $this->getContent();
                if (!empty($content)) {
                    $data = $this->getParser()->parse($this);
                    // 是否需要清空content 节省内存资源
                    $this->setData($data);
                }
            }
        }

        return $data;
    }

    /**
     * 获取结果
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param boolean $clean 获取结果之后是否清空数据
     * @return string|array|null
     */
    public function getResult($clean = true)
    {
        $data = $this->getData();
        if ($clean) {
            $this->data = null;
        }

        return $data;
    }

    public function getCookies():Cookies
    {
        $cookieList = parent::getCookies();
        if ($cookieList->getCount() === 0 && $this->getHeaders()->has('set-cookie')) {
            $cookieStrings = $this->getHeaders()->get('set-cookie', [], false);
            foreach ($cookieStrings as $cookieString) {
                $cookieList->add($this->parseCookie($cookieString));
            }
        }

        return $cookieList;
    }

    /**
     * 获取http 协议状态码
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @return string
     */
    public function getStatusCode()
    {
        if (!is_null($this->statusCode)) {
            return $this->statusCode;
        }
        
        $headers = $this->getHeaders();
        if ($headers->has('http-code')) {
            $statusCodeHeaders = $headers->get('http-code', null, false);
            $statusCode =  empty($statusCodeHeaders) ? '' : end($statusCodeHeaders);
        } else {
            $statusCode = '';
        }

        $this->statusCode = $statusCode;

        return $this->statusCode;
    }

    /**
     * 检测是否有网络错误
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     *  @return boolean true 有错误,false 无
     */
    public function hasNetworkError():bool
    {
        if (is_bool($this->networkError)) {
            return $this->networkError;
        }

        $statusCode = $this->getStatusCode();
        if (empty($statusCode)) {
            $this->addError('Unable to get status code: referred header information is missing.');
            $this->networkError = true;
            return true;
        }

        $result = strncmp('20', $statusCode, 2) === 0;

        if ($result === false) {
            $this->addError('header http-code is not equal to 20x,http-code is ' . $statusCode);
        }

        $this->networkError = $result === true ? false : true;

        return $this->networkError;
    }

    /**
     * 获取默认Parser格式化名称
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     *  @return string|null
     */
    protected function defaultFormat():string
    {
        // 从头部header 获取
        $format = $this->detectFormatByHeaders($this->getHeaders());
        if ($format === null) {
            $format = parent::defaultFormat();
        }

        return $format;
    }

    /**
     * 检测 response header 是否包含format 名称
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param Headers $headers source headers.
     * @return null|string
     */
    protected function detectFormatByHeaders(Headers $headers)
    {
        $contentType = $headers->get('content-type');
        if (!empty($contentType)) {
            if (stripos($contentType, 'json') !== false) {
                return Client::FORMAT_JSON;
            }

            if (stripos($contentType, 'xml') !== false) {
                return Client::FORMAT_XML;
            }
        }

        return null;
    }

    /**
     * 从返回content 种识别响应格式
     *<B>说明：</B>
     *<pre>
     * 识别:{}对应json,name1=name&name1=value1对应urlencoded
     * <>对应 xml
     *</pre>
     * @param string $content raw response content.
     * @return null|string
     */
    protected function detectFormatByContent($content)
    {
        if (preg_match('/^\\{.*\\}$/is', $content)) {
            return Client::FORMAT_JSON;
        }

        if (preg_match('/^<.*>$/s', $content)) {
            return Client::FORMAT_XML;
        }

        return null;
    }

    /**
     * 解析cookie 字符串，创建对应的Cookie 对象
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param string $cookieString cookie header string.
     * @return Cookie cookie object.
     */
    private function parseCookie($cookieString)
    {
        $params = [];
        $pairs = explode(';', $cookieString);
        foreach ($pairs as $number => $pair) {
            $pair = trim($pair);
            if (strpos($pair, '=') === false) {
                $params[$this->normalizeCookieParamName($pair)] = true;
            } else {
                list($name, $value) = explode('=', $pair, 2);
                if ($number === 0) {
                    $params['name'] = $name;
                    $params['value'] = urldecode($value);
                } else {
                    $params[$this->normalizeCookieParamName($name)] = urldecode($value);
                }
            }
        }

        return new Cookie($params);
    }

    /**
     * 解析cookie 字符串，创建对应的Cookie 对象
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param string $rawName raw cookie parameter name.
     * @return string name of [[Cookie]] field.
     */
    private function normalizeCookieParamName($rawName)
    {
        static $nameMap = [
            'expires' => 'expire',
            'httponly' => 'httpOnly',
        ];
        $name = strtolower($rawName);
        if (isset($nameMap[$name])) {
            $name = $nameMap[$name];
        }

        return $name;
    }

    /**
     * 获取Parser 对象
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @return ParserInterface message parser instance.
     */
    private function getParser():ParserInterface
    {
        return $this->client->getParser($this->getFormat());
    }
}
