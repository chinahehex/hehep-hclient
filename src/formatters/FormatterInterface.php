<?php
namespace hclient\formatters;

use hclient\base\Request;

/**
 * request 数据序列化接口
 *<B>说明：</B>
 *<pre>
 * 略
 *</pre>
 */
interface FormatterInterface
{
    /**
     * 统一格式化入口
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param Request $request
     */
    public function format(Request $request);
}