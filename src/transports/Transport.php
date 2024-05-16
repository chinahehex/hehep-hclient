<?php
namespace  hclient\transports;

use hclient\base\Request;

/**
 * 传输http 接口类
 *<B>说明：</B>
 *<pre>
 * 略
 *</pre>
 */
abstract class Transport
{

    /**
     * 发送请求
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @param Request $request
     * @return Request
     */
    abstract public function send($request);

    /**
     * 批量发送请求
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @param Request[] $requests
     * @return Request[]
     */
    public function batchSend($requests)
    {
        foreach ($requests as $key => $request) {
            $this->send($request);
        }

        return $requests;
    }
}