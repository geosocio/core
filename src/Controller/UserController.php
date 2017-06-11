<?php

namespace GeoSocio\Core\Controller;

use GeoSocio\Core\Entity\Location;
use GeoSocio\Core\Entity\Site;
use GeoSocio\Core\Entity\User\Name;
use GeoSocio\Core\Entity\User\User;
use GeoSocio\Core\Entity\User\Email;
use GeoSocio\Core\Entity\User\Membership;
use GeoSocio\Core\Entity\User\Verify\EmailVerify;
use GeoSocio\Core\Utils\PlaceFinderInterface;
use GeoSocio\Core\Utils\EntityAttacherInterface;
use GeoSocio\Core\Utils\User\VerificationManagerInterface;
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
 *    service="geosocio.controller_user",
 *    defaults = {
 *       "version" = "1.0",
 *       "_format" = "json"
 *    }
 * )
 *
 * @TODO Need a route to determine the place a user should see
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

        return $this->showAction($user);
    }

  /**
   * @Route("/user/{user}.{_format}")
   * @Method("GET")
   * @ParamConverter("user", converter="doctrine.orm", class="GeoSocio\Core\Entity\User\User")
   *
   * @param User $user
   * @param Request $request
   */
    public function showAction(User $user) : User
    {
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
     * @ParamConverter("user", converter="doctrine.orm", class="GeoSocio\Core\Entity\User\User")
     * @Security("has_role('authenticated')")
     *
     * @param Request $request
     */
    public function updateAction(User $authenticated, User $user, array $input) : User
    {
        if (!$authenticated->isEqualTo($user)) {
            throw new AccessDeniedHttpException("You may only modify your own user");
        }

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

        return $this->showAction($user);
    }

    /**
     * Update the user's location.
     *
     * @TODO Get rid of this method!
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
     * @ParamConverter("user", converter="doctrine.orm", class="GeoSocio\Core\Entity\User\User")
     * @Security("has_role('authenticated')")
     *
     * @param Request $request
     */
    public function deleteAction(User $authenticated, User $user) : string
    {
        if (!$authenticated->isEqualTo($user)) {
            throw new AccessDeniedHttpException("You may only deactivate your own user");
        }

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
     * @ParamConverter("user", converter="doctrine.orm", class="GeoSocio\Core\Entity\User\User")
     * @Security("has_role('authenticated')")
     *
     * @param Request $request
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
     * @ParamConverter("user", converter="doctrine.orm", class="GeoSocio\Core\Entity\User\User")
     * @ParamConverter("site", converter="doctrine.orm", class="GeoSocio\Core\Entity\Site")
     * @Security("has_role('authenticated')")
     *
     * @param Email $email
     * @param Request $request
     */
    public function removeMembershipAction(User $authenticated, User $user, Site $site) : string
    {
        if (!$authenticated->isEqualTo($user)) {
            throw new AccessDeniedHttpException("You may only modify your own user");
        }

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
     * @ParamConverter("user", converter="doctrine.orm", class="GeoSocio\Core\Entity\User\User")
     * @Security("has_role('authenticated')")
     *
     * @param Request $request
     */
    public function createMembershipAction(User $authenticated, User $user, array $input) : Collection
    {
        if (!$authenticated->isEqualTo($user)) {
            throw new AccessDeniedHttpException("You may only modify your own user");
        }

        $em = $this->doctrine->getEntityManager();
        $repository = $this->doctrine->getRepository(Site::class);

        $site = $this->denormalizer->denormalize($input, Site::class, null, [
            'user' => $user,
        ]);

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
     * @ParamConverter("user", converter="doctrine.orm", class="GeoSocio\Core\Entity\User\User")
     * @Security("has_role('authenticated')")
     *
     * @param Request $request
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
     * @ParamConverter("user", converter="doctrine.orm", class="GeoSocio\Core\Entity\User\User")
     * @Security("has_role('authenticated')")
     *
     * @param Request $request
     */
    public function createEmailAction(User $authenticated, User $user, array $input) : EmailVerify
    {
        if (!$authenticated->isEqualTo($user)) {
            throw new AccessDeniedHttpException("You may only modify your own user");
        }

        $em = $this->doctrine->getEntityManager();
        $email = $this->denormalizer->denormalize($input, Email::class, null, [
            'user' => $user,
        ]);

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
     * @ParamConverter("user", converter="doctrine.orm", class="GeoSocio\Core\Entity\User\User")
     * @Security("has_role('authenticated')")
     *
     * @param Email $email
     * @param Request $request
     */
    public function removeEmailAction(User $authenticated, User $user, Email $email) : string
    {
        if (!$authenticated->isEqualTo($user)) {
            throw new AccessDeniedHttpException("You may only modify your own user");
        }

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
     * @ParamConverter("user", converter="doctrine.orm", class="GeoSocio\Core\Entity\User\User")
     * @Security("has_role('authenticated')")
     *
     * @param Request $request
     */
    public function verifyEmailAction(User $authenticated, User $user, array $input) : Collection
    {
        if (!$authenticated->isEqualTo($user)) {
            throw new AccessDeniedHttpException("You may only modify your own user");
        }

        $input = $this->denormalizer->denormalize($input, EmailVerify::class, null, [
            'user' => $user,
        ]);

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
