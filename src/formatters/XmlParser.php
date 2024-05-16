<?php
namespace hclient\formatters;

use hclient\base\Response;

/**
 * Response content 反序列化
 *<B>说明：</B>
 *<pre>
 * 略
 *</pre>
 */
class XmlParser implements ParserInterface
{

    public function parse(Response $response)
    {
        return $this->convertXmlToArray($response->getContent());
    }

    protected function convertXmlToArray($xml)
    {
        if (!is_object($xml)) {
            $xml = simplexml_load_string($xml);
        }
        $result = (array) $xml;
        foreach ($result as $key => $value) {
            if (is_object($value)) {
                $result[$key] = $this->convertXmlToArray($value);
            }
        }
        return $result;
    }
}