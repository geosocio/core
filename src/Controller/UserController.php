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
     * {@inheritdoc}
     */
    public function __construct(
        DenormalizerInterface $denormalizer,
        RegistryInterface $doctrine,
        EntityAttacherInterface $attacher,
        VerificationManagerInterface $verificationManager,
        PlaceFinderInterface $placeFinder
    ) {
        parent::__construct($denormalizer, $doctrine, $attacher);
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

        // @TODO Use a security voter!

        return $this->showAction($user);
    }

  /**
   * @Route("/user/{user}.{_format}")
   * @Method("GET")
   * @ParamConverter("user", converter="doctrine.orm", class="App\Entity\User\User")
   *
   * @param User $user
   */
    public function showAction(User $user) : User
    {
        // @TODO Use a security voter!
        if (!$user->isEnabled()) {
            throw new NotFoundHttpException("User account is disabled");
        }

        return $user;
    }

    /**
     * Update the current user.
     *
     * @Route("/user/{user}")
     * @Method("PATCH")
     * @ParamConverter("user", converter="doctrine.orm", class="App\Entity\User\User")
     * @Security("has_role('ROLE_AUTHENTICATED')")
     *
     * @param User $user
     * @param array $input
     *
     * @return User
     */
    public function updateAction(User $user, array $input) : User
    {
        // @TODO Use a security voter!
        // if (!$authenticated->isEqualTo($user)) {
        //     throw new AccessDeniedHttpException("You may only modify your own user");
        // }

        $em = $this->doctrine->getEntityManager();

        // Get the original data for comparison.
        $locationId = $user->getLocationId();
        $primaryEmailAddress = $user->getPrimaryEmailAddress();

        $user = $this->denormalizer->denormalize($input, $user);

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
     * @Route("/user/{user}")
     * @Method("DELETE")
     * @ParamConverter("user", converter="doctrine.orm", class="App\Entity\User\User")
     * @Security("has_role('ROLE_AUTHENTICATED')")
     *
     * @param User $user
     *
     * @return string
     */
    public function deleteAction(User $user) : string
    {
        // @TODO Use a Symfony Security Voter!
        // if (!$authenticated->isEqualTo($user)) {
        //     throw new AccessDeniedHttpException("You may only deactivate your own user");
        // }

        $user->disable();

        $em = $this->doctrine->getEntityManager();
        $em->flush();

        return '';
    }

    /**
     * Show the ueer's memberships.
     *
     * @Route("/user/{user}/memberships.{_format}")
     * @Method("GET")
     * @ParamConverter("user", converter="doctrine.orm", class="App\Entity\User\User")
     * @Security("has_role('authenticated')")
     *
     * @param User $user
     *
     * @return Collection
     */
    public function showMembershipsAction(User $user) : Collection
    {
        if (!$user->isEnabled()) {
            throw new NotFoundHttpException("User account is disabled");
        }

        return $user->getMemberships();
    }

    /**
     * Removes an email.
     *
     * @Route("/user/{user}/memberships/{site}")
     * @Method("DELETE")
     * @ParamConverter("user", converter="doctrine.orm", class="App\Entity\User\User")
     * @ParamConverter("site", converter="doctrine.orm", class="App\Entity\Site")
     * @Security("has_role('ROLE_AUTHENTICATED')")
     *
     * @param Email $email
     * @param Site $site
     *
     * @return string
     */
    public function removeMembershipAction(User $user, Site $site) : string
    {
        // @TODO use a Symfony Security Voter!
        // if (!$authenticated->isEqualTo($user)) {
        //     throw new AccessDeniedHttpException("You may only modify your own user");
        // }

        $memberships = $user->getMembershipsBySite($site);

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
     * @Route("/user/{user}/memberships.{_format}")
     * @Method("POST")
     * @ParamConverter("user", converter="doctrine.orm", class="App\Entity\User\User")
     * @Security("has_role('ROLE_AUTHENTICATEDd')")
     *
     * @param User $user
     * @param Site $site
     *
     * @return Collection
     */
    public function createMembershipAction(User $user, Site $site) : Collection
    {
        // @TODO use a Symfony Security Voter!
        // if (!$authenticated->isEqualTo($user)) {
        //     throw new AccessDeniedHttpException("You may only modify your own user");
        // }

        $em = $this->doctrine->getEntityManager();
        $repository = $this->doctrine->getRepository(Site::class);

        $memberships = $user->getMembershipsBySite($site);

        if (!$memberships->isEmpty()) {
            throw new BadRequestHttpException("Membership already exists");
        }

        $site = $repository->find($site->getId());

        $membership = new Membership([
            'site' => $site,
            'user' => $user,
        ]);
        $user->addMembership($membership);
        $em->persist($membership);
        $em->flush();

        return $this->showMembershipsAction($user);
    }

    /**
     * Show the ueer's emails.
     *
     * @Route("/user/{user}/emails.{_format}")
     * @Method("GET")
     * @ParamConverter("user", converter="doctrine.orm", class="App\Entity\User\User")
     * @Security("has_role('ROLE_AUTHENTICATED')")
     *
     * @param User $user
     *
     * @return Collection
     */
    public function showEmailsAction(User $user) : Collection
    {
        if (!$user->isEnabled()) {
            throw new NotFoundHttpException("User account is disabled");
        }

        return $user->getEmails();
    }

    /**
     * Add emails to the user.
     *
     * @Route("/user/{user}/emails")
     * @Method("POST")
     * @ParamConverter("user", converter="doctrine.orm", class="App\Entity\User\User")
     * @Security("has_role('ROLE_AUTHENTICATED')")
     *
     * @param User $user
     * @param Email $email
     *
     * @return EmailVerify
     */
    public function createEmailAction(User $user, Email $email) : EmailVerify
    {
        // @TODO Use a Symfony Security Voter!
        // if (!$authenticated->isEqualTo($user)) {
        //     throw new AccessDeniedHttpException("You may only modify your own user");
        // }

        $em = $this->doctrine->getEntityManager();

        $repository = $this->doctrine->getRepository(Email::class);

        if ($existing = $repository->find($email->getEmail())) {
            $email = $existing;

            if ($email->getVerified()) {
                if ($user->isEqualTo($email->getUser())) {
                    throw new BadRequestHttpException('Email already added to user');
                } else {
                    throw new BadRequestHttpException('Email already belongs to another user');
                }
            }

            $email->setUser($user);
            $user->addEmail($email);
        } else {
            $email->setUser($user);
            $em->persist($email);
            $user->addEmail($email);
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
     * @Route("/user/{user}/emails/{email}")
     * @Method("DELETE")
     * @ParamConverter("user", converter="doctrine.orm", class="App\Entity\User\User")
     * @Security("has_role('ROLE_AUTHENTICATED')")
     *
     * @param Email $email
     *
     * @return string
     */
    public function removeEmailAction(Email $email) : string
    {
        // @TODO Use a Symfony Security Voter!
        // if (!$authenticated->isEqualTo($user)) {
        //     throw new AccessDeniedHttpException("You may only modify your own user");
        // }

        $em = $this->doctrine->getEntityManager();

        $em->remove($email);
        $em->flush();

        return '';
    }

    /**
     * Verify Email.
     *
     * @Route("/user/{user}/emails/verify")
     * @Method("POST")
     * @ParamConverter("user", converter="doctrine.orm", class="App\Entity\User\User")
     * @Security("has_role('ROLE_AUTHENTICATED')")
     *
     * @param User $user
     * @param EmailVerify $input
     *
     * @return Collection
     */
    public function verifyEmailAction(User $user, EmailVerify $input) : Collection
    {
        // if (!$authenticated->isEqualTo($user)) {
        //     throw new AccessDeniedHttpException("You may only modify your own user");
        // }

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

        return $this->showEmailsAction($user);
    }
}
