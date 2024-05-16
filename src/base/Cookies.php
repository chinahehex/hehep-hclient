<?php
namespace  hclient\base;
use ArrayIterator;

/**
 * cookie 集合类
 *<B>说明：</B>
 *<pre>
 * 略
 *</pre>
 */
class Cookies implements \IteratorAggregate, \ArrayAccess, \Countable
{

    private $_cookies = [];

    public function __construct($cookies = [], $attrs = [])
    {
        $this->_cookies = $cookies;
        foreach ($attrs as $name=>$value) {
            $this->$name = $value;
        }
    }

    public function getIterator()
    {
        return new ArrayIterator($this->_cookies);
    }

    public function count()
    {
        return $this->getCount();
    }

    public function getCount()
    {
        return count($this->_cookies);
    }

    public function get($name)
    {
        return isset($this->_cookies[$name]) ? $this->_cookies[$name] : null;
    }


    public function getValue($name, $defaultValue = null)
    {
        return isset($this->_cookies[$name]) ? $this->_cookies[$name]->value : $defaultValue;
    }


    public function has($name)
    {
        return isset($this->_cookies[$name]) && $this->_cookies[$name]->value !== ''
            && ($this->_cookies[$name]->expire === null || $this->_cookies[$name]->expire >= time());
    }

    public function add($cookie)
    {
        $this->_cookies[$cookie->name] = $cookie;
    }

    public function remove($cookie, $removeFromBrowser = true)
    {

        if ($cookie instanceof Cookie) {
            $cookie->expire = 1;
            $cookie->value = '';
        } else {
            $cookie = new Cookie([
                'name' => $cookie,
                'expire' => 1,
            ]);
        }
        if ($removeFromBrowser) {
            $this->_cookies[$cookie->name] = $cookie;
        } else {
            unset($this->_cookies[$cookie->name]);
        }
    }

    public function removeAll()
    {
        $this->_cookies = [];
    }


    public function toArray()
    {
        return $this->_cookies;
    }

    public function offsetExists($name)
    {
        return $this->has($name);
    }

    public function offsetGet($name)
    {
        return $this->get($name);
    }

    public function offsetSet($name, $cookie)
    {
        $this->add($cookie);
    }

    public function offsetUnset($name)
    {
        $this->remove($name);
    }
}
