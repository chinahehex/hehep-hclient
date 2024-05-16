<?php
namespace hclient\formatters;
use hclient\base\Response;

/**
 * Response json 反序列化
 *<B>说明：</B>
 *<pre>
 * 略
 *</pre>
 */
class JsonParser implements ParserInterface
{
    /**
     * 解析请求返回数据 json 格式
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param Response $response
     * @return mixed
     */
    public function parse(Response $response)
    {
        // 错误码
        $decode = json_decode((string) $response->getContent(), true);
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                break;
            case JSON_ERROR_DEPTH:
                $response->addError('json decode error:#The maximum stack depth has been exceeded.');
                break;
            case JSON_ERROR_CTRL_CHAR:
                $response->addError('json decode error:#Control character error, possibly incorrectly encoded.');
                break;
            case JSON_ERROR_SYNTAX:
                $response->addError('json decode error:#Syntax error.');
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $response->addError('json decode error:#Invalid or malformed JSON.');
                break;
            case JSON_ERROR_UTF8:
                $response->addError('json decode error:#Malformed UTF-8 characters, possibly incorrectly encoded.');
                break;
            default:
                $response->addError('json decode error:#Unknown JSON decoding error.');
                break;
        }

        return $decode;
    }
}