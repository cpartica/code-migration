<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor;

class TokenArgument
{
    /**
     * @var int
     */
    protected $line;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $token;

    /**
     * @return int
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @param int $number
     * @return $this
     */
    public function setLine($number)
    {
        $this->line = $number;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param array $token
     * @return $this
     */
    public function setToken($token)
    {
        if (is_array($token)) {
            $this->token = $token;
            $this->type = $token[0];
            $this->name = $token[1];
            $this->line = $token[2];
        } else {
            $this->token = $token;
            $this->name = $token;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getToken()
    {
        return $this->token;
    }
}
