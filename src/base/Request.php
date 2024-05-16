<?php
namespace hclient\base;

use hclient\base\Response;
use hclient\extend\ServiceResponse;
use hclient\formatters\FormatterInterface;
use hclient\protocols\HttpResponse;


/**
 * request 请求类
 *<B>说明：</B>
 *<pre>
 * 一个url 创建一个对应的Request 对象
 *</pre>
 */
class Request extends Message
{

    /**
     * 空格换行符
     *<B>说明：</B>
     *<pre>
     *　用于http　协议
     *</pre>
     * @var string
     */
    const PHP_SPACE_EOL = "\r\n";

    /**
     * 请求地址域名或ip
     *<B>说明：</B>
     *<pre>
     *　如http://api.hehe.com/
     *</pre>
     * @var string
     */
    protected $baseUrl = '';

    /**
     * 请求地址
     *<B>说明：</B>
     *<pre>
     *　两种格式:
     * 比如 http://www.baidu.com/user/find/1,user/add
     * or  /user/find/1,user/add
     *</pre>
     * @var string
     */
    private $url = '';

    /**
     * 请求method
     *<B>说明：</B>
     *<pre>
     *　比如get,post
     *</pre>
     * @var string
     */
    protected $method = 'get';

    /**
     * 请求 传输协议Transport配置
     *<B>说明：</B>
     *<pre>
     *　比如curl 配置参数
     *</pre>
     * @var array
     */
    protected $options = [];

    /**
     * response 对象
     *<B>说明：</B>
     *<pre>
     *　每个request 对应一个response 对象
     *</pre>
     * @var Response
     */
    protected $response = null;

    /**
     * 传输协议类名
     *<B>说明：</B>
     *<pre>
     *　支持命令空间
     *</pre>
     * @var string
     */
    protected $transport = '';

    /**
     * 默认响应类
     * @var string
     */
    protected $defaultResponse = Response::class;

    /**
     * 序号
     *<B>说明：</B>
     *<pre>
     *　用于批量序号排序
     *</pre>
     * @var int|string
     */
    protected $index = 0;

    protected $_isResult = false;

    /**
     * 设置键名序号
     *<B>说明：</B>
     *<pre>
     *　用于批量序号排序
     *</pre>
     * @param  int|string
     */
    public function setIndex($index):self
    {
        $this->index = $index;

        return $this;
    }

    /**
     * 获取键名序号
     *<B>说明：</B>
     *<pre>
     *　用于批量序号排序
     *</pre>
     * @return   int|string
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * 判断是否设置过序号
     * @return bool
     */
    public function hasIndex():bool
    {
        if (is_null($this->index)) {
            return false;
        } else {
            return true;
        }
    }

    public function isResult():bool
    {
        return $this->_isResult;
    }

    public function asResult():self
    {
        $this->_isResult = true;

        return $this;
    }

    /**
     * 设置域名或http url 基础部分url
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @param string $baseUrl
     * @return $this
     */
    public function setBaseUrl(string $baseUrl = '')
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * 获取http url域名
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @return string
     */
    public function getBaseUrl():string
    {
        return $this->baseUrl;
    }

    /**
     * 获取传输协议类
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @return string
     */
    public function getTransport():string
    {
        return $this->transport;
    }

    public function setTransport(string $transport):self
    {
        $this->transport = $transport;

        return $this;
    }

    /**
     * 设置url 地址
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @param string $url 请求地址
     * @return $this
     */
    public function setUrl(string $url):self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * 获取请求地址 url
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @return string
     */
    public function getUrl():string
    {
        return $this->url;
    }

    /**
     * 设置 method
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @param string $method request method
     * @return $this
     */
    public function setMethod(string $method):self
    {
        $this->method = $method;

        return $this;
    }

    /**
     * 获取 method
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @return string
     */
    public function getMethod():string
    {
        return $this->method;
    }

    /**
     * 设置请求 传输协(Transport)议置
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @param array $options request options.
     * @return $this
     */
    public function setTransportOptions(array $options):self
    {
        if (is_null($options)) {
            $this->options = [];
        } else {
            $this->options = array_merge($this->options, $options);
        }

        return $this;
    }

    /**
     * 获取请求 传输(Transport)议置
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @return array request options
     */
    public function getTransportOptions():array
    {
        return $this->options;
    }

    public function addContent($name, $content, $options = []):self
    {
        $multiPartContent = $this->getContent();
        if (!is_array($multiPartContent)) {
            $multiPartContent = [];
        }

        $options['content'] = $content;
        $multiPartContent[$name] = $options;
        $this->setContent($multiPartContent);

        return $this;
    }

    /**
     * 添加?参数
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @param array $params
     * @return $this
     */
    public function addQuery(array $params = []):self
    {
        $queryStr = http_build_query($params);

        if (strpos('?',$this->url) !== false) {
            // 找到?
            $this->url .= '&' . $queryStr;
        } else {
            $this->url .= '?' . $queryStr;
        }

        return $this;
    }

    /**
     * 添加参数
     *<B>说明：</B>
     *<pre>
     *　一般为post 参数
     *</pre>
     * @param array $params
     * @return $this
     */
    public function addData(array $params = []):self
    {
        if (!empty($params)) {
            $this->data = array_merge($this->data,$params);
        }

        return $this;
    }



    public function makeResponse():Response
    {
        if (!is_object($this->response)) {
            if (!isset($this->response['class'])) {
                $responseClass = $this->defaultResponse;
            } else {
                $requestClass = $this->response['class'];
                unset($this->response['class']);
            }
            $response = new $responseClass($this->response);
        }  else {
            $response = $this->response;
        }

        $response->setClient($this->client);
        $response->setRequest($this);
        $this->response = $response;

        return $response;
    }

    /**
     *　设置Response 对象或设置Response 配置
     *<B>说明：</B>
     *<pre>
     *　 略
     *</pre>
     * @param array|Response $response
     */
    public function setResponse($response = []):void
    {
        $this->response = $response;

        return;
    }

    /**
     *　获取Response
     *<B>说明：</B>
     *<pre>
     *　 略
     *</pre>
     * @return HttpResponse
     */
    public function getResponse():Response
    {
        return $this->response;
    }

    /**
     * 请求发送之前事件函数
     *<B>说明：</B>
     *<pre>
     *　 比如 格式化参数,组织url 地址
     *</pre>
     * @return $this
     */
    public function prepare()
    {

        if (!empty($this->baseUrl)) {
            $url = $this->getUrl();
            if (!preg_match('/^https?:\\/\\//i', $url)) {
                $this->setUrl($this->baseUrl  . $url);
            }
        }

        $content = $this->getContent();
        if ($content === null) {
            $this->getFormatter()->format($this);
        }

        return $this;
    }

    /**
     * 将给定的数据作为表标准单输入提交的值，并考虑嵌套数组
     *<B>说明：</B>
     *<pre>
     *　 转换 `['form' => ['name' => 'value']]` to `['form[name]' => 'value']`.
     *</pre>
     * @param array $data 数据
     * @param string $baseKey 表单名
     * @return array
     */
    public function composeFormInputs(array $data, $baseKey = '')
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (!empty($baseKey)) {
                $key = $baseKey . '[' . $key . ']';
            }
            if (is_array($value)) {
                $result = array_merge($result, $this->composeFormInputs($value, $key));
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }


    /**
     * 发送当前请求
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @return HttpResponse|ServiceResponse response 实例
     */
    public function send():Response
    {
        return $this->client->send($this);
    }


    /**
     * 获取format 对象
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @return FormatterInterface message formatter instance.
     */
    private function getFormatter():FormatterInterface
    {
        return $this->client->getFormatter($this->getFormat());
    }

    public function __toString()
    {
        return $this->encodeRequest();
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
        return ;
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

    }

    /**
     * 编码
     */
    public function encodeRequest()
    {

    }
}
