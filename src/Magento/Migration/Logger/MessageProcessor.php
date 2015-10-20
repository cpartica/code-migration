<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Logger;

/**
 * Logger messages processor
 */
class MessageProcessor
{
    /**
     * @var array
     */
    protected $extra = [
        'stage' => '',
        'codePool' => '',
        'module' => '',
        'file' => '',
        'method' => '',
    ];

    /**
     * @param array $record
     * @return array
     */
    public function setExtra(array $record)
    {
        foreach ($record['context'] as $key => $value) {
            switch ($key) {
                case 'stage':
                    $this->extra[$key] = '[stage: ' . $value . ']';
                    break;
                case 'codePool':
                    $this->extra[$key] = '[codePool: ' . $value . ']';
                    break;
                case 'module':
                    $this->extra[$key] = '[module: ' . $value . ']';
                    break;
                case 'file':
                    $this->extra[$key] = '[file: ' . $value . ']';
                    break;
                case 'method':
                    $this->extra[$key] = '[method: ' . $value . ']';
                    break;
            }
        }
        $record['extra'] = $this->extra;
        return $record;
    }

    /**
     * @param  array $record
     * @return array
     */
    public function replace(array $record)
    {
        if (false === strpos($record['message'], '{')) {
            return $record;
        }
        $replacements = [];
        foreach ($record['context'] as $key => $val) {
            if ($val === null || is_scalar($val) || (is_object($val) && method_exists($val, "__toString"))) {
                $replacements['{'.$key.'}'] = $val;
            } elseif (is_object($val)) {
                $replacements['{'.$key.'}'] = '[object '.get_class($val).']';
            } else {
                $replacements['{'.$key.'}'] = '['.gettype($val).']';
            }
        }
        $record['message'] = strtr($record['message'], $replacements);
        return $record;
    }
}
