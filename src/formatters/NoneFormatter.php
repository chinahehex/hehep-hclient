<?php
namespace hclient\formatters;

use hclient\base\Request;
/**
 * Request content 序列化
 *<B>说明：</B>
 *<pre>
 * 无须序列化
 *</pre>
 */
class NoneFormatter implements FormatterInterface
{
    /**
     * 解析请求返回数据 json 格式
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param Request $request
     * @return Request
     */
    public function format(Request $request)
    {
        $request->setContent($request->getData());

        return $request;
    }
}