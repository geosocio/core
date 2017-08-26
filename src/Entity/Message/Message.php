<?php

namespace App\Entity\Message;

use GeoSocio\EntityUtils\ParameterBag;

/**
 * Interface for Messages.
 */
abstract class Message implements MessageInterface
{
    /**
     * @var string
     */
    protected $to;

    /**
     * @var array
     */
    protected $text;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $data = [])
    {
        $params = new ParameterBag($data);
        $this->to = $params->getString('to', '');
        $this->text = $params->getStringArray('text', []);
    }


    /**
     * {@inheritdoc}
     */
    public function getTo() : string
    {
        return $this->to;
    }


    /**
     * {@inheritdoc}
     */
    public function getText() : array
    {
        return $this->text;
    }

    /**
     * {@inheritdoc}
     */
    public function getTextString() : string
    {
        return implode("\n", $this->text);
    }
}
