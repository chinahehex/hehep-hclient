<?php
namespace hclient\formatters;
use hclient\base\Response;


/**
 * Response 反序列化
 *<B>说明：</B>
 *<pre>
 * 无须序列化
 *</pre>
 */
class NoneParser implements ParserInterface
{

    /**
     * 直接返回Response 内容，不做任务处理
     *<B>说明：</B>
     *<pre>
     *  一般用于html 网页
     *</pre>
     * @param Response $response
     * @return mixed
     */
    public function parse(Response $response)
    {
        return $response->getContent();
    }
}