<?php
namespace hclient\extend;

use Exception;
use hclient\base\Response;

/**
 * ServiceResponse 服务response
 *<B>说明：</B>
 *<pre>
 * 专门用于api 接口的response
 *</pre>
 */
class ServiceResponse extends Response
{
    // 是否初始化结果
    protected $init = false;

    /**
     * 错误码参数名
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @var string
     */
    protected $varCode = 'code';

    /**
     * 错误消息参数名
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @var string
     */
    protected $varMsg = 'message';

    /**
     * 错误数据
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @var string
     */
    protected $varResult = 'data';

    /**
     * 默认成功状态
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @var string
     */
    protected $defaultCode = 0;

    // 错误码
    protected $errcode = '';

    // 错误消息
    protected $errmsg = '';

    /**
     * 检测业务操作成功
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param array $errorCode
     * @return boolean true 操作成功,false 操作失败
     */
    public function check($errorCode = []):bool
    {
        try {
            $this->formatData();

            if (empty($errorCode)) {
                $errorCode = [$this->defaultCode];
            }

            if (in_array($this->errcode,$errorCode)) {
                return true;
            } else {
                return false;
            }

        } catch (Exception $e) {
            // json 解析异常
            return false;
        }
    }

    /**
     * 获取错误码
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @return string|null
     */
    public function getErrorCode()
    {
        $this->formatData();

        return $this->errcode;
    }

    /**
     * 获取结果
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param boolean $clean 获取结果之后是否清空数据
     * @return string|array|null
     */
    public function getResult($clean = true)
    {
        $data = parent::getResult($clean);
        if (!is_array($data)) {
            return null;
        }

        return isset($data[$this->varResult]) ? $data[$this->varResult] : null;
    }

    /**
     * 获取错误消息
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @return string|null
     */
    public function getMessage()
    {
        $this->formatData();

        return $this->errmsg;
    }

    protected function formatData()
    {
        if ($this->init) {
            return false;
        }

        $this->init = true;

        $data = $this->getData();
        if (!is_array($data)) {
            return null;
        }

        // 出错了
        if (isset($data[$this->varCode])) {
            $this->errcode = $data[$this->varCode];
            $this->errmsg = $data[$this->varMsg];
        }
    }
}
