<?php

namespace App\Controller;

use App\Entity\User\Login;
use App\Entity\User\Verify\EmailVerify;
use App\Entity\User\Verify\VerifyInterface;
use App\Utils\User\VerificationManagerInterface;
use GeoSocio\EntityAttacher\EntityAttacherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManagerInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
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
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        DenormalizerInterface $denormalizer,
        RegistryInterface $doctrine,
        EntityAttacherInterface $attacher,
        AuthorizationCheckerInterface $authorizationChecker,
        VerificationManagerInterface $verificationManager,
        JWTManagerInterface $jwtManager
    ) {
        parent::__construct($denormalizer, $doctrine, $attacher, $authorizationChecker);
        $this->verificationManager = $verificationManager;
        $this->jwtManager = $jwtManager;
    }

    /**
     * Login Action.
     *
     * @Route("/login")
     * @Method("POST")
     * @Security("!has_role('ROLE_AUTHENTICATED')")
     *
     * @param Login $login
     *
     * @return VerifyInterface
     */
    public function loginAction(Login $login) : VerifyInterface
    {
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
     * @Security("!has_role('ROLE_AUTHENTICATED')")
     *
     * @param EmailVerify $input
     *
     * @return array
     */
    public function loginEmailAction(EmailVerify $input) : array
    {
        $em = $this->doctrine->getEntityManager();
        $repository = $this->doctrine->getRepository(EmailVerify::class);

        $verify = $repository->findOneByToken($input->getToken());
        if (!$verify) {
            throw new BadRequestHttpException('Token does not exist');
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
     * @Security("has_role('ROLE_AUTHENTICATED')")
     *
     * @param UserInterface $user
     *
     * @return array
     */
    public function tokenAction(UserInterface $user) : array
    {
        return [
            'token' => $this->jwtManager->create($user),
        ];
    }
}
