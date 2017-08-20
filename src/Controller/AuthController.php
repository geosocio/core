<?php

namespace App\Controller;

use App\Entity\User\Login;
use App\Entity\User\User;
use App\Entity\User\Verify\EmailVerify;
use App\Entity\User\Verify\VerifyInterface;
use App\Utils\User\VerificationManagerInterface;
use GeoSocio\EntityAttacher\EntityAttacherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManagerInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Current User actions.
 *
 * @Route(
 *    service="app.controller_auth",
 *    defaults = {
 *       "version" = "1.0",
 *       "_format" = "json"
 *    }
 * )
 */
class AuthController extends Controller
{

    /**
     * @var VerificationManagerInterface
     */
    protected $verificationManager;

    /**
     * @var JWTManagerInterface
     */
    protected $jwtManager;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        DenormalizerInterface $denormalizer,
        RegistryInterface $doctrine,
        EntityAttacherInterface $attacher,
        VerificationManagerInterface $verificationManager,
        JWTManagerInterface $jwtManager
    ) {
        parent::__construct($denormalizer, $doctrine, $attacher);
        $this->verificationManager = $verificationManager;
        $this->jwtManager = $jwtManager;
    }

    /**
     * Login Action.
     *
     * @Route("/login")
     * @Method("POST")
     * @Security("!has_role('authenticated')")
     *
     * @param Request $request
     */
    public function loginAction(array $input) : VerifyInterface
    {
        $login = $this->denormalizer->denormalize($input, Login::class);

        $verification = $this->verificationManager->getVerification($login->getType());

        $verify = $verification->create($login);

        $verification->send($verify);

        return $verify;
    }

    /**
     * Verify Email.
     *
     * @Route("/login/email")
     * @Method("POST")
     * @Security("!has_role('authenticated')")
     *
     * @param Request $request
     */
    public function loginEmailAction(array $input) : array
    {
        $input = $this->denormalizer->denormalize($input, EmailVerify::class);
        $em = $this->doctrine->getEntityManager();
        $repository = $this->doctrine->getRepository(EmailVerify::class);

        $verify = $repository->findOneByToken($input->getToken());
        if (!$verify) {
            throw new BadRequestHttpException("Token does not exist'");
        }

        if (!$verify->isEqualTo($input)) {
            throw new BadRequestHttpException('Token & Verification Code do not match');
        }

        if (!$verify->isFresh()) {
            throw new BadRequestHttpException('Verification Code is older than 1 hour');
        }

        $email = $verify->getEmail();

        $email->setVerified(new \DateTime());

        $em->persist($email);
        $em->remove($verify);
        $em->flush();

        return [
            'token' => $this->jwtManager->create($email->getUser()),
        ];
    }

    /**
     * Exchange current token for a new one.
     *
     * @Route("/token")
     * @Method("GET")
     * @Security("has_role('authenticated')")
     *
     * @param Request $request
     */
    public function tokenAction(User $authenticated) : array
    {
        return [
            'token' => $this->jwtManager->create($authenticated),
        ];
    }
}
