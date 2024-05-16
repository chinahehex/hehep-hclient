<?php
namespace  hclient\base;

/**
 * cookie 类
 *<B>说明：</B>
 *<pre>
 * 略
 *</pre>
 */
class Cookie
{

    public $name;

    public $value = '';

    public $domain = '';

    public $expire = 0;

    public $path = '/';

    public $secure = false;

    public $httpOnly = true;

    public function __construct($attrs = [])
    {
        if (!empty($attrs)) {
            foreach ($attrs as $name => $value) {
                $this->$name = $value;
            }
        }
    }

}
