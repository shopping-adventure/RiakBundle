<?php

namespace Kbrw\RiakBundle\Model\KV;

/**
 * @author remi
 */
class Data 
{
    /**
     * @var string 
     */
    protected $key;
    
    /**
     * @var array<string,string>
     */
    protected $headers;
    
    /**
     * @var string
     */
    protected $rawContent;
    
    /**
     * @var mixed
     */
    protected $structuredContent;
    
    function __construct($key = null, $headers = array(), $rawContent = null, $structuredContent = null)
    {
        $this->setKey($key);
        $this->setHeaders($headers);
        $this->setRawContent($rawContent);
        $this->ssetStructuredContent($structuredContent);
    }
    
    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }
    
    /**
     * @return array<string,string>
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }
    
    /**
     * @param string $nane
     * @param mixed $default
     * @return mixed
     */
    public function getHeader($name, $default = null)
    {
        if (is_array($this->headers) && array_key_exists($name, $this->headers)) {
            return $this->headers[$name];
        }
        return $default;
    }

    /**
     * @return string
     */
    public function getRawContent()
    {
        return $this->rawContent;
    }

    public function setRawContent($rawContent)
    {
        $this->rawContent = $rawContent;
    }

    /**
     * @return mixed
     */
    public function getStructuredContent()
    {
        return $this->structuredContent;
    }

    public function setStructuredContent($structuredContent)
    {
        $this->structuredContent = $structuredContent;
    }
}