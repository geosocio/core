<?php

namespace App\Utils\User;

use App\Entity\Message\EmailMessage;
use App\Entity\User\Verify\VerifyInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bridge\Doctrine\RegistryInterface as Doctrine;
use RandomLib\Generator as RandomGenerator;

use App\Entity\User\User;
use App\Entity\User\Email;
use App\Entity\User\Verify\EmailVerify;
use App\Utils\Dispatcher\DispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Email Verification.
 */
class EmailVerification implements VerificationInterface
{

    /**
     * @var \Symfony\Bridge\Doctrine\RegistryInterface
     */
    protected $doctrine;

    /**
     * @var \RandomLib\Generator
     */
    protected $random;

    /**
     * @var \App\Utils\Dispatcher\DispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * Create the Email Verification.
     *
     * @param Doctrine $doctrine
     * @param RandomGenerator $random
     * @param DispatcherInterface $dispatcher
     * @param RequestStack $requestStack
     */
    public function __construct(
        Doctrine $doctrine,
        RandomGenerator $random,
        DispatcherInterface $dispatcher,
        RequestStack $requestStack
    ) {
        $this->doctrine = $doctrine;
        $this->random = $random;
        $this->dispatcher = $dispatcher;
        $this->requestStack = $requestStack;
    }

    /**
     * Create a Verification from an email address.
     *
     * @param string $email_address Valid email address.
     *
     * @return EmailVerify Newly created verify object.
     */
    public function create(string $email_address) : VerifyInterface
    {
        $em = $this->doctrine->getManager();

        // Get the existig email from the database.
        $email = $this->findExisting($email_address);

        // If there is ane email, then there's also a user.
        if (!$email) {
            $email = new Email([
                'email' => $email_address,
            ]);

            $user = $em->getRepository(User::class)->createFromEmail($email);
        }

        $saved = false;
        while (!$saved) {
            try {
                $verify = new EmailVerify([
                    'email' => $email,
                    'token' => $this->random->generateString(6, $this->random::CHAR_LOWER | $this->random::CHAR_DIGITS),
                    'code' => $this->random->generateString(6, $this->random::CHAR_DIGITS),
                ]);

                $email->setVerify($verify);
                $em->persist($verify);
                $em->flush();
                $saved = true;
            } catch (UniqueConstraintViolationException $e) {
                // Try again.
            }
        }

        return $verify;
    }

    /**
     * {@inheritdoc}
     */
    public function send(VerifyInterface $verify) : bool
    {
        $request = $this->requestStack->getCurrentRequest();

        $message = new EmailMessage([
            'to' => $verify->getEmail()->getEmail(),
            'subject' => 'Confirm Your Email (' . $verify->getCode()  . ')',
            'text' => [
                'Please visit the following location to verify your email:',
                $request->getSchemeAndHttpHost() . '/v/e/' . $verify->getToken() . '/' . $verify->getCode(),
            ],
        ]);

        // Send the Message using Async.
        return $this->dispatcher->send($message);
    }


    /**
     * Finds an Existing Email.
     *
     * @param string $email_address Valid email_addressr.
     *
     * @return mixed Existing Email object or NULL.
     */
    protected function findExisting(string $email_address) :? Email
    {

        $em = $this->doctrine->getManager();

        // Get the existig email from the database.
        $repository = $this->doctrine->getRepository(Email::class);

        // If there is ane email, then there's also a user.
        if ($email = $repository->findOneByEmail($email_address)) {
            $repository = $this->doctrine->getRepository(EmailVerify::class);

            // If one is found, destroy it so a new one can be issued.
            if ($verify = $repository->findOneByEmail($email_address)) {
                $em->remove($verify);
                $em->flush();
            }
        }

        return $email;
    }
}
