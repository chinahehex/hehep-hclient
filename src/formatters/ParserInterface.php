<?php
namespace hclient\formatters;
use hclient\base\Response;


/**
 * Response 反序列化接口
 *<B>说明：</B>
 *<pre>
 * 略
 *</pre>
 */
interface ParserInterface
{
    /**
     * 解析请求返回的数据
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param Response $response
     * @return mixed
     */
    public function parse(Response $response);
}