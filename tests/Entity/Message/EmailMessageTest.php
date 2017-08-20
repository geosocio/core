<?php

namespace App\Tests\Entity\Message;

use App\Entity\Message\EmailMessage;

class EmailMessageTest extends MessageTest
{
    public function testMessage()
    {
        $data = [
            'subject' => 'Test Subject',
            'to' => 'test@example.com',
            'text' => [
                'Text',
                'Message',
            ],
        ];
        $message = new EmailMessage($data);

        $this->assertEquals($data['subject'], $message->getSubject());
        $this->assertEquals($data['to'], $message->getTo());
        $this->assertEquals($data['text'], $message->getText());
        $this->assertEquals(implode("\n", $data['text']), $message->getTextString());
    }
}
