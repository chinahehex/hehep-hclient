<?php
namespace  hclient\transports;

use Exception;

/**
 * stream_context_create 模拟http 传输
 *<B>说明：</B>
 *<pre>
 * 不支持批量
 *</pre>
 */
class StreamTransport extends Transport
{

    public function send($request)
    {
        $request->prepare();

        $url = $request->getUrl();
        $method = strtoupper($request->getMethod());

        $contextOptions = [
            'http' => [
                'method' => $method,
                'ignore_errors' => true,
            ],
            'ssl' => [
                'verify_peer' => false,
            ],
        ];

        $content = $request->getContent();
        if ($content !== null) {
            $contextOptions['http']['content'] = $content;
        }

        $headers = $request->encodeHeaderLines();
        $contextOptions['http']['header'] = $headers;

        $contextOptions = array_merge($contextOptions, $this->composeContextOptions($request->getTransportOptions()));

        try {
            $context = stream_context_create($contextOptions);
            $stream = fopen($url, 'rb', false, $context);
            $responseContent = stream_get_contents($stream);
            $metaData = stream_get_meta_data($stream);

            fclose($stream);
            $responseHeaders = isset($metaData['wrapper_data']) ? $metaData['wrapper_data'] : [];
            $request->makeResponse()->setContent($responseContent)->setHeaders($responseHeaders);
        } catch (Exception $e) {
            $request->makeResponse()->addError($e->getTraceAsString());
        }

        return $request;
    }

    /**
     * 组织上下文配置
     *<B>说明：</B>
     *<pre>
     *　包括header 设置
     *</pre>
     * @param array $options raw request options.
     * @return array stream context options.
     */
    private function composeContextOptions($options = [])
    {
        $contextOptions = [];
        foreach ($options as $key => $value) {
            $section = 'http';
            if (strpos($key, 'ssl') === 0) {
                $section = 'ssl';
                $key = substr($key, 3);
            }

            $key = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $key));
            $contextOptions[$section][$key] = $value;
        }

        return $contextOptions;
    }
}
