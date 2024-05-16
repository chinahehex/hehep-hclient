<?php
namespace hclient\formatters;

use hclient\base\Request;

/**
 * Request json 序列化
 *<B>说明：</B>
 *<pre>
 * 略
 *</pre>
 */
class JsonFormatter implements FormatterInterface
{
    /**
     * json_encode 参数
     *<B>说明：</B>
     *<pre>
     *  略
     * <http://www.php.net/manual/en/function.json-encode.php>.
     *</pre>
     * @param int
     */
    public $encodeOptions = 0;

    /**
     * 格式化字符串
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param Request $request
     * @return Request
     */
    public function format(Request $request)
    {
        $request->getHeaders()->set('Content-Type', 'application/json; charset=UTF-8');
        $request->setContent(json_encode($request->getData(), $this->encodeOptions));

        return $request;
    }
}