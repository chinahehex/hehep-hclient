<?php
namespace  hclient\transports;
use hclient\base\Request;

/**
 * curl 传输http 类
 *<B>说明：</B>
 *<pre>
 * 略
 *</pre>
 */
class CurlTransport extends Transport
{
    /**
     * @inheritdoc
     */
    public function send($request)
    {
        $curlOptions = $this->prepare($request);
        $curlResource = $this->initCurl($curlOptions);
        $responseHeaders = [];
        $this->setHeaderOutput($curlResource, $responseHeaders);
        $responseContent = curl_exec($curlResource);

        // check cURL error
        $errorNumber = curl_errno($curlResource);
        $errorMessage = curl_error($curlResource);

        curl_close($curlResource);
        $response = $request->makeResponse();
        $response->setContent($responseContent)->setHeaders($responseHeaders);
        if ($errorNumber > 0) {
            $response->addError('Curl error: #' . $errorNumber . ' - ' . $errorMessage);
        }

        return $request;
    }

    /**
     * @inheritdoc
     */
    public function batchSend($requests)
    {
        $curlBatchResource = curl_multi_init();

        $curlResources = [];
        $responseHeaders = [];
        foreach ($requests as $key => $request) {
            /* @var $request Request */
            $curlOptions = $this->prepare($request);
            $curlResource = $this->initCurl($curlOptions);

            $responseHeaders[$key] = [];
            $this->setHeaderOutput($curlResource, $responseHeaders[$key]);
            $curlResources[$key] = $curlResource;
            curl_multi_add_handle($curlBatchResource, $curlResource);
        }

        $isRunning = null;
        do {
            // See https://bugs.php.net/bug.php?id=61141
            if (curl_multi_select($curlBatchResource) === -1) {
                usleep(100);
            }
            do {
                $curlExecCode = curl_multi_exec($curlBatchResource, $isRunning);
            } while ($curlExecCode === CURLM_CALL_MULTI_PERFORM);
        } while ($isRunning > 0 && $curlExecCode === CURLM_OK);


        $responseContents = [];
        $errors = [];
        foreach ($curlResources as $key => $curlResource) {
            $responseContents[$key] = curl_multi_getcontent($curlResource);

            $errorNumber = curl_errno($curlResource);
            $errorMessage = curl_error($curlResource);
            if ($errorNumber > 0) {
                $errors[$key] = 'Curl error: #' . $errorNumber . ' - ' . $errorMessage;
            }

            curl_multi_remove_handle($curlBatchResource, $curlResource);
        }

        curl_multi_close($curlBatchResource);

        foreach ($requests as $key => $request) {
            $response = $request->makeResponse();
            $response->setContent($responseContents[$key])->setHeaders($responseHeaders[$key]);
            if (isset($errors[$key])) {
                $response->addError($errors[$key]);
            }
        }

        return $requests;
    }

    /**
     * 请求之前准备功能
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @param Request $request request instance.
     * @return array cURL options
     */
    private function prepare($request)
    {
        $request->prepare();
        $curlOptions = $this->composeCurlOptions($request->getTransportOptions());

        $method = strtoupper($request->getMethod());
        switch ($method) {
            case 'GET':
                break;
            case 'POST':
                $curlOptions[CURLOPT_POST] = true;
                break;
            default:
                $curlOptions[CURLOPT_CUSTOMREQUEST] = $method;
        }

        $content = $request->getContent();
        if ($content !== null) {
            $curlOptions[CURLOPT_POSTFIELDS] = $content;
        }

        $curlOptions[CURLOPT_RETURNTRANSFER] = true;
        $curlOptions[CURLOPT_URL] = $request->getUrl();
        
        $curlOptions[CURLOPT_HTTPHEADER] = $request->encodeHeaderLines();

        return $curlOptions;
    }

    /**
     * 创建curl 实例
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @param array $curlOptions cURL options
     * @return resource prepared cURL resource
     */
    private function initCurl(array $curlOptions)
    {
        $curlResource = curl_init();
        foreach ($curlOptions as $option => $value) {
            curl_setopt($curlResource, $option, $value);
        }

        // 禁用SSL证书验证
        curl_setopt($curlResource, CURLOPT_SSL_VERIFYPEER, false);

        return $curlResource;
    }

    /**
     * 组织curl 配置参数
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @param array $options raw request options
     * @return array cURL options, in format: [curl_constant => value]
     */
    private function composeCurlOptions(array $options)
    {
        static $optionMap = [
            'maxRedirects' => CURLOPT_MAXREDIRS,
            'sslCapath' => CURLOPT_CAPATH,
            'sslCafile' => CURLOPT_CAINFO,
        ];

        $curlOptions = [];
        foreach ($options as $key => $value) {
            if (is_int($key)) {
                $curlOptions[$key] = $value;
            } else {
                if (isset($optionMap[$key])) {
                    $curlOptions[$optionMap[$key]] = $value;
                } else {
                    $key = strtoupper($key);
                    if (strpos($key, 'SSL') === 0) {
                        $key = substr($key, 3);
                        $constantName = 'CURLOPT_SSL_' . $key;
                        if (!defined($constantName)) {
                            $constantName = 'CURLOPT_SSL' . $key;
                        }
                    } else {
                        $constantName = 'CURLOPT_' . strtoupper($key);
                    }

                    $curlOptions[constant($constantName)] = $value;
                }
            }
        }

        return $curlOptions;
    }

    /**
     * 设置一个变量，该变量应该收集curl 响应头
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @param resource $curlResource cURL resource.
     * @param array $output variable, which should collection headers.
     */
    private function setHeaderOutput($curlResource, array &$output)
    {
        curl_setopt($curlResource, CURLOPT_HEADERFUNCTION, function($resource, $headerString) use (&$output) {
            $header_lines = trim($headerString, "\r\n");
            if (strlen($header_lines) > 0) {
                $output[] = $header_lines;
            }

            return mb_strlen($headerString, '8bit');
        });
    }
}
