<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\User\User;
use App\Entity\User\Email;
use App\Entity\User\Membership;
use App\Entity\User\Verify\EmailVerify;
use App\Utils\PlaceFinderInterface;
use App\Utils\User\VerificationManagerInterface;
use GeoSocio\EntityAttacher\EntityAttacherInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @Route(
 *    service="app.controller_user",
 *    defaults = {
 *       "version" = "1.0",
 *       "_format" = "json"
 *    }
 * )
 *
 * @todo Need a route to determine the place a user should see
 *      (not too many people, not too few)
 */
class UserController extends Controller
{

    /**
     * @var VerificationManagerInterface
     */
    protected $verificationManager;

    /**
     * @var PlaceFinderInterface
     */
    protected $placeFinder;

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
        PlaceFinderInterface $placeFinder
    ) {
        parent::__construct($denormalizer, $doctrine, $attacher, $authorizationChecker);
        $this->verificationManager = $verificationManager;
        $this->placeFinder = $placeFinder;
    }

    /**
     * @Route("/user.{_format}")
     * @Method("GET")
     *
     * @param Request $request
     */
    public function indexAction(Request $request) : User
    {
        if (!$request->query->has('username')) {
            throw new BadRequestHttpException("Missing Username Paramter");
        }

        $repository = $this->doctrine->getRepository(User::class);

        $user = $repository->findOneByUsername($request->query->get('username'));

        if (!$user) {
            throw new NotFoundHttpException("No user found");
        }

        if (!$this->authorizationChecker->isGranted('view', $user)) {
            throw new AccessDeniedHttpException();
        }

        return $user;
    }

  /**
   * @Route("/user/{account}.{_format}")
   * @Method("GET")
   * @ParamConverter("account", converter="doctrine.orm", class="App\Entity\User\User")
   * @Security("is_granted('view', account)", statusCode=404)
   *
   * @param User $account
   */
    public function showAction(User $account) : User
    {
        return $account;
    }

    /**
     * Update the current user.
     *
     * @Route("/user/{account}")
     * @Method("PATCH")
     * @ParamConverter("account", converter="doctrine.orm", class="App\Entity\User\User")
     * @Security("is_granted('edit', account)")
     *
     * @param User $account
     * @param array $input
     *
     * @return User
     */
    public function updateAction(User $account, array $input) : User
    {
        $em = $this->doctrine->getEntityManager();

        // Get the original data for comparison.
        $locationId = $account->getLocationId();
        $primaryEmailAddress = $account->getPrimaryEmailAddress();

        $user = $this->denormalizer->denormalize($input, $account);

        // If the primary email has changed, update it before updating the user.
        if ($primaryEmailAddress !== $user->getPrimaryEmailAddress()) {
            if (!$user->getPrimaryEmailAddress()) {
                throw new BadRequestHttpException('Cannot remove primary email address');
            }

            if (!$user->getPrimaryEmail()->getVerified()) {
                throw new BadRequestHttpException("Can only set a verified email as the primary email");
            }
        }

        // If the location id has changed, get the location's places before
        // updating the user.
        if ($locationId !== $user->getLocationId()) {
            $user = $this->updateLocation($user);
        }

        $em->flush();

        return $user;
    }

    /**
     * Update the user's location.
     *
     * @param User $user
     *
     * @return User
     *
     * @todo Get rid of this method!
     */
    protected function updateLocation(User $user) : User
    {
        // Get the new locaiton from the user and set it to null to prevent
        // the data from being updated.
        $location = $user->getLocation();

        // @TODO Create a new entity manager for placeFinder so we don't
        //       modify the entity manager state!
        $user->setLocation();
        $em->detach($location);

        // This gets called in placeFinder anyways, so we will explicitly
        // call it here first.
        $em->flush();

        $location = $this->placeFinder->find($location);

        $user->setLocation($location);

        return $user;
    }

    /**
     * Delete the current user.
     *
     * @Route("/user/{account}")
     * @Method("DELETE")
     * @ParamConverter("account", converter="doctrine.orm", class="App\Entity\User\User")
     * @Security("is_granted('edit', account)")
     *
     * @param User $account
     *
     * @return string
     */
    public function deleteAction(User $account) : string
    {
        $account->disable();

        $em = $this->doctrine->getEntityManager();
        $em->flush();

        return '';
    }

    /**
     * Show the ueer's memberships.
     *
     * @Route("/user/{account}/memberships.{_format}")
     * @Method("GET")
     * @ParamConverter("account", converter="doctrine.orm", class="App\Entity\User\User")
     * @Security("is_granted('view', account)", statusCode=404)
     *
     * @param User $account
     *
     * @return Collection
     */
    public function showMembershipsAction(User $account) : Collection
    {
        return $account->getMemberships();
    }

    /**
     * Removes an email.
     *
     * @Route("/user/{account}/memberships/{site}")
     * @Method("DELETE")
     * @ParamConverter("account", converter="doctrine.orm", class="App\Entity\User\User")
     * @ParamConverter("site", converter="doctrine.orm", class="App\Entity\Site")
     * @Security("is_granted('edit', account)")
     *
     * @param User $account
     * @param Site $site
     *
     * @return string
     */
    public function removeMembershipAction(User $account, Site $site) : string
    {
        $memberships = $account->getMembershipsBySite($site);

        $em = $this->doctrine->getEntityManager();

        foreach ($memberships as $membership) {
            $em->remove($membership);
        }

        $em->flush();

        return '';
    }

    /**
     * Add memberships to the user.
     *
     * @Route("/user/{account}/memberships.{_format}")
     * @Method("POST")
     * @ParamConverter("account", converter="doctrine.orm", class="App\Entity\User\User")
     * @Security("is_granted('edit', account)")
     *
     * @param User $account
     * @param Site $site
     *
     * @return Collection
     */
    public function createMembershipAction(User $account, Site $site) : Collection
    {
        $em = $this->doctrine->getEntityManager();
        $repository = $this->doctrine->getRepository(Site::class);

        $memberships = $account->getMembershipsBySite($site);

        if (!$memberships->isEmpty()) {
            throw new BadRequestHttpException("Membership already exists");
        }

        $site = $repository->find($site->getId());

        $membership = new Membership([
            'site' => $site,
            'user' => $account,
        ]);
        $account->addMembership($membership);
        $em->persist($membership);
        $em->flush();

        return $account->getMemberships();
    }

    /**
     * Show the ueer's emails.
     *
     * @Route("/user/{account}/emails.{_format}")
     * @Method("GET")
     * @ParamConverter("account", converter="doctrine.orm", class="App\Entity\User\User")
     * @Security("is_granted('view', account)", statusCode=404)
     *
     * @param User $account
     *
     * @return Collection
     *
     * @todo We can't handle collections now?
     */
    public function showEmailsAction(User $account) : Collection
    {
        return $account->getEmails();
    }

    /**
     * Add emails to the user.
     *
     * @Route("/user/{account}/emails")
     * @Method("POST")
     * @ParamConverter("account", converter="doctrine.orm", class="App\Entity\User\User")
     * @Security("is_granted('edit', account)")
     *
     * @param User $account
     * @param Email $email
     *
     * @return EmailVerify
     */
    public function createEmailAction(User $account, Email $email) : EmailVerify
    {
        $em = $this->doctrine->getEntityManager();

        $repository = $this->doctrine->getRepository(Email::class);

        if ($existing = $repository->find($email->getEmail())) {
            $email = $existing;

            if ($email->getVerified()) {
                if ($account->isEqualTo($email->getUser())) {
                    throw new BadRequestHttpException('Email already added to user');
                } else {
                    throw new BadRequestHttpException('Email already belongs to another user');
                }
            }

            $email->setUser($account);
            $account->addEmail($email);
        } else {
            $email->setUser($account);
            $em->persist($email);
            $account->addEmail($email);
        }

        $em->flush();

        $verification = $this->verificationManager->getVerification('email');

        $verify = $verification->create($email);

        $verification->send($verify);

        return $verify;
    }

    /**
     * Removes an email.
     *
     * @Route("/user/{account}/emails/{email}")
     * @Method("DELETE")
     * @ParamConverter("account", converter="doctrine.orm", class="App\Entity\User\User")
     * @ParamConverter("email", converter="doctrine.orm", class="App\Entity\User\Email")
     * @Security("is_granted('edit', email.user)")
     *
     * @param User $account
     * @param Email $email
     *
     * @return string
     */
    public function removeEmailAction(User $account, Email $email) : string
    {
        $em = $this->doctrine->getEntityManager();

        $em->remove($email);
        $em->flush();

        return '';
    }

    /**
     * Verify Email.
     *
     * @Route("/user/{account}/emails/verify")
     * @Method("POST")
     * @ParamConverter("account", converter="doctrine.orm", class="App\Entity\User\User")
     * @Security("is_granted('edit', account)")
     *
     * @param User $account
     * @param EmailVerify $input
     *
     * @return Collection
     */
    public function verifyEmailAction(User $account, EmailVerify $input) : Collection
    {
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

        $em->remove($verify);
        $em->flush();

        return $account->getEmails();
    }
}
