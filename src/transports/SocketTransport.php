<?php
namespace  hclient\transports;

use hclient\protocols\HttpRequest;
use Exception;

/**
 * Socket 传输
 *<B>说明：</B>
 *<pre>
 * 采用Socket tcp 传输
 * http request 有三部分组成，每部分由回车换行分割
 * 请求行,请求头部,请求数据,请参照http://blog.csdn.net/firefoxbug/article/details/7677240
 *</pre>
 */
class SocketTransport extends Transport
{

    /**
     * 当数据可读时，从socket缓冲区读取多少字节数据
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @var int
     */
    const READ_BUFFER_SIZE = 65535;

    /**
     * 空格换行符
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @var int
     */
    const PHP_SPACE_EOL = "\r\n";

    /**
     * @inheritdoc
     */
    public function send($request)
    {

        try {
            $request->prepare();
            $socket = $this->initSocket($request);
            $responseRawContent = $this->read($socket);
            fclose ( $socket );
            $response = $request->createResponse($responseRawContent);
        } catch (Exception $e) {
            $response = $request->createResponse()->addError($e->getTraceAsString());
        }

        return $request;
    }

    /**
     * @inheritdoc
     */
    public function batchSend($requests)
    {
        $sockets = [];

        foreach ($requests as $index => $request) {
            $request->prepare();
            $socket = $this->initSocket($request);
            $sockets[$index] = $socket;
        }

        // 读取socket 数据, $readSockets 为 可读socket 列表
        $readSockets = $sockets;
        $socketOutputs = [];
        while (count($readSockets) > 0) {
            if (@stream_select($readSockets, $write, $except, 0, 200000)) {
                foreach ($readSockets as $index=>$socket) {
                    $socketOutput = $this->read($socket);
                    $socketOutputs[$index] = $socketOutput;
                    // unset $sockets
                    unset($sockets[$index]);
                }
            }

            $readSockets = $sockets;
        }

        // 组织socket output
        foreach ($requests as $index=>$request) {
            $request->createResponse($socketOutputs[$index]);
        }

        return $requests;
    }

    /**
     * 创建socket 实例
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @param HttpRequest $request
     * @return resource 资源句柄
     * @throws Exception
     */
    public function initSocket($request)
    {
        $socket =  stream_socket_client($request->getHost(), $errorNumber, $errorMessage);
        if (!$socket) {
            throw new Exception('http socket error: #' . $errorNumber . ' - ' . $errorMessage);
        }

        $httpContent = $request->encodeRequest();
        stream_set_blocking($socket, 0);
        $len = @fwrite($socket,$httpContent);

        return $socket;
    }

    /**
     * 读取socket 返回数据
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @param resource|null $socket 句柄
     * @return string
     */
    public function read($socket = null)
    {
        $recvBuffer = '';
        while (!feof($socket)) {
            $buffer = fread($socket, self::READ_BUFFER_SIZE);
            if ($buffer === '' || $buffer === false) {
                continue;
            }

            $recvBuffer .= $buffer;
        }

        return $recvBuffer;
    }


}
