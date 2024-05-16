<?php
namespace  hclient\transports;
use hclient\protocols\HttpRequest;
use extend\coroutine\PollSocket;
use extend\coroutine\Scheduler;
use extend\coroutine\SystemCall;
use Exception;

/**
 * 协程socket
 *<B>说明：</B>
 *<pre>
 * 100 条请求 4652 ms
 * 性能比不上curl 批量
 *</pre>
 */
class CoroutineTransport extends Transport
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
            if ($socket === null) {
                return $request;
            }

            $responseRawContent = $this->read($socket);
            fclose ( $socket );
            $request->createResponse($responseRawContent);
            return $request;
        } catch (Exception $e) {
            $request->createResponse('')->addError($e->getTraceAsString());
            return $request;
        }
    }

    /**
     * @inheritdoc
     */
    public function batchSend($requests = [])
    {
        $scheduler = new Scheduler();

        foreach ($requests as $index => $request) {
            //$this->sendSocket($request);
            $scheduler->addTask($this->sendSocket($request));
        }

        $scheduler->withIoPoll()->run();
        return null;
    }

    /**
     * 批量发送请求
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @param HttpRequest $request requests to perform
     * @return void
     */
    private function sendSocket($request)
    {
        $request->prepare();
        $socket =  stream_socket_client($request->getHost(), $errorNumber, $errorMessage);
        if (!$socket) {
            $request->createResponse()->addError('http socket error: #' . $errorNumber . ' - ' . $errorMessage);
            yield SystemCall::retval('');
        }

        $httpContent = $request->composeRequestContent();
        stream_set_blocking($socket, 0);

        yield SystemCall::wait(['socket'=>$socket,'key'=>PollSocket::WRITE],'socket');
        @fwrite($socket,$httpContent);

        //yield SystemCall::wait(['socket'=>$socket,'key'=>PollSocket::READ],'socket');
        $content = $this->read($socket);

        $request->createResponse($content);
        fclose ( $socket );

        //yield SystemCall::retval('');
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
            $request->createResponse()->addError('http socket error: #' . $errorNumber . ' - ' . $errorMessage);
            return null;
        }

        $httpContent = $request->composeRequestContent();
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