<?php
namespace hclient\formatters;

use hclient\base\Request;
use \DOMDocument;
use \DOMElement;
use \SimpleXMLElement;
use \Arrayable;

class XmlFormatter implements FormatterInterface
{

    public $contentType = 'application/xml';

    public $version = '1.0';

    public $encoding;

    public $rootTag = 'request';

    public $charset = 'utf-8';


    /**
     * @inheritdoc
     */
    public function format(Request $request)
    {
        $contentType = $this->contentType;
        if (stripos($contentType, 'charset') === false) {
            $contentType .= '; charset=' . $this->charset;
        }
        $request->getHeaders()->set('Content-Type', $contentType);

        $data = $request->getData();
        if ($data !== null) {
            if ($data instanceof DOMDocument) {
                $content = $data->saveXML();
            } elseif ($data instanceof SimpleXMLElement) {
                $content = $data->saveXML();
            } else {
                $dom = new DOMDocument($this->version, $this->charset);
                $root = new DOMElement($this->rootTag);
                $dom->appendChild($root);
                $this->buildXml($root, $data);
                $content = $dom->saveXML();
            }
            $request->setContent($content);
        }

        return $request;
    }

    /**
     * @param DOMElement $element
     * @param mixed $data
     */
    protected function buildXml($element, $data)
    {
        if (is_object($data)) {
            $child = new DOMElement(StringHelper::basename(get_class($data)));
            $element->appendChild($child);
            if ($data instanceof Arrayable) {
                $this->buildXml($child, $data->toArray());
            } else {
                $array = [];
                foreach ($data as $name => $value) {
                    $array[$name] = $value;
                }
                $this->buildXml($child, $array);
            }
        } elseif (is_array($data)) {
            foreach ($data as $name => $value) {
                if (is_int($name) && is_object($value)) {
                    $this->buildXml($element, $value);
                } elseif (is_array($value) || is_object($value)) {
                    $child = new DOMElement(is_int($name) ? $this->itemTag : $name);
                    $element->appendChild($child);
                    $this->buildXml($child, $value);
                } else {
                    $child = new DOMElement(is_int($name) ? $this->itemTag : $name);
                    $element->appendChild($child);
                    $child->appendChild(new DOMText((string) $value));
                }
            }
        } else {
            $element->appendChild(new DOMText((string) $data));
        }
    }
}