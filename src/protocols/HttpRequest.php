<?php
namespace hclient\protocols;

use hclient\base\Request;
use hclient\base\Response;

/**
 * http 协议 request 请求类
 *<B>说明：</B>
 *<pre>
 * 一个url 就是一个 HttpRequest 类
 * 一般用于socket http
 *</pre>
 */
class HttpRequest extends Request
{
    protected $defaultResponse = HttpResponse::class;

    /**
     * socket
     * @var string
     */
    protected $host = '';

    /**
     * 获取socket 服务器地址
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }


    /**
     * 推送之前组织数据
     */
    public function prepare()
    {
        parent::prepare();

        $pathInfo = parse_url($this->getUrl());
        $port = !empty($pathInfo['port'])  ? ':' . $pathInfo['port'] : ':80';
        $this->host = (in_array($pathInfo['scheme'],['http','https']) ?  'tcp' : $pathInfo['scheme'])
            . '://' . $pathInfo['host'] . $port;

    }

    /**
     * headers 关联数组转换成i索引数组
     *<B>说明：</B>
     *<pre>
     *转换 `['header-name1' => 'value1','header-name2' => 'value2']`
     * to `['header-name1:value1','header-name2:value2']`
     *</pre>
     * @return array raw header lines
     */
    public function encodeHeaderLines()
    {

        if (!$this->hasHeaders()) {
            return [];
        }

        $headers = [];
        foreach ($this->getHeaders() as $name => $values) {
            $name = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));
            foreach ($values as $value) {
                $headers[] = "$name: $value";
            }
        }

        if ($this->hasCookies()) {
            $headers[] = $this->encodeCookieHeader();
        }

        return $headers;
    }

    /**
     * 组合cookie header 信息
     *<B>说明：</B>
     *<pre>
     *　cookie 数组转换成header 字符串
     *</pre>
     * @return string cookie header value.
     */
    protected function encodeCookieHeader():string
    {
        $parts = [];
        foreach ($this->getCookies() as $cookie) {
            $parts[] = $cookie->name . '=' . urlencode($cookie->value);
        }

        return 'Cookie: ' . implode(';', $parts);
    }

    /**
     * request 编码
     * @return string
     */
    public function encodeRequest()
    {
        // header lines
        $requestHeaders = $this->encodeHeaderLines();
        $requestData =  $this->getContent();

        $pathInfo = parse_url($this->getUrl());
        $method = strtoupper($this->getMethod());
        $port = !empty($pathInfo['port'])  ? ':' . $pathInfo['port'] : '';

        array_unshift($requestHeaders,$method  . ' ' . $pathInfo['path'] . ' HTTP/1.0');
        array_push($requestHeaders,'Host: '.$pathInfo['host'] . $port);
        array_push($requestHeaders,'Content-Length: ' . strlen($requestData));
        array_push($requestHeaders,'Content-Connection: close');
        array_push($requestHeaders,'Accept: */*');

        return implode(self::PHP_SPACE_EOL,$requestHeaders) . str_repeat(self::PHP_SPACE_EOL,2) . $requestData;
    }

    /**
     *　创建response 实例
     *<B>说明：</B>
     *<pre>
     *　 一般用于socket,可以重构此方法
     *</pre>
     * @param string $rawContent request 原始输出
     * @return Response
     */
    public function createResponse($rawContent = '')
    {
        // 获取状态码
        $response = $this->makeResponse();
        $content = $headers = null;
        if (!empty($rawContent)) {
            $responseRawContent = explode(str_repeat(self::PHP_SPACE_EOL,2), $rawContent, 2);
            $headers = isset($responseRawContent[0]) ? $responseRawContent[0] : '';
            $content = isset($responseRawContent[1]) ? $responseRawContent[1] : '';
        }

        $response->setContent($content);
        if (!empty($headers)) {
            $header_list = explode(self::PHP_SPACE_EOL,$headers);
            $response->setHeaders($header_list);
        }

        return $response;
    }

}
