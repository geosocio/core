<?php

namespace App\Entity\Message;

use GeoSocio\EntityUtils\ParameterBag;

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

        $params = new ParameterBag($data);
        $this->subject = $params->getString('subject', '');
    }

    /**
     * Gets the Subject.
     */
    public function getSubject() : string
    {
        return $this->subject;
    }
}
