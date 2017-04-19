<?php

namespace GeoSocio\Core\Entity\Message;

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
        $to = $data['to'] ?? '';
        $this->to = is_string($to) ? $to : '';

        $text = $data['text'] ?? [];
        if (is_array($text)) {
            $text = array_filter($text, function ($line) {
                return is_string($line);
            });
        }
        $this->text = is_array($text) ? $text : [];
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
