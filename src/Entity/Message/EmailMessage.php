<?php

namespace GeoSocio\Core\Entity\Message;

class EmailMessage extends Message
{

    /**
     * @var string
     */
    protected $subject;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);

        $subject = $data['subject'] ?? '';
        $this->subject = is_string($subject) ? $subject : '';
    }

    /**
     * Gets the Subject.
     */
    public function getSubject() : string
    {
        return $this->subject;
    }
}
