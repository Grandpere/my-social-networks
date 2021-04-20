<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\RouterInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use KnpU\OAuth2ClientBundle\Client\Provider\GoogleClient;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class GoogleAuthenticator extends SocialAuthenticator
{
    use TargetPathTrait;
    
    /**
     * @var ClientRegistry
     */
    private $clientRegistry;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;
    
    /**
     * @param ClientRegistry $clientRegistry
     * @param EntityManagerInterface $em
     * @param RouterInterface $router
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $em, RouterInterface $router, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->clientRegistry = $clientRegistry;
        $this->em = $em;
        $this->router = $router;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function supports(Request $request)
    {
        return 'connect_oauth_check' === $request->attributes->get('_route') && 'google' === $request->get('service');
    }

    public function getCredentials(Request $request)
    {
        return $this->fetchAccessToken($this->getGoogleClient());
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        /** @var GoogleUser $googleUser */
        $googleUser = $this->getGoogleClient()
        ->fetchUserFromToken($credentials);

        $email = $googleUser->getEmail();

        $existingUser = $this->em->getRepository(User::class)
            ->findOneBy(['googleId' => $googleUser->getId()]);

        if ($existingUser) {
            $user = $existingUser;
        } else {
            $user = $this->em->getRepository(User::class)
                        ->findOneBy(['email' => $email]);

            if (!$user) {
                $user = new User();
                $user->setActive(true);
                $user->setEnabled(true);
                $user->setEmail($email);
                $user->setFirstname($googleUser->getFirstName());
                $user->setLastname($googleUser->getLastName());
                $user->setDisplayName($googleUser->getName());
                $user->setPassword($this->passwordEncoder->encodePassword($user, sha1($email)));
            }
        }

        $user->setGoogleId($googleUser->getId());
        $this->em->persist($user);
        $this->em->flush();

        return $userProvider->loadUserByUsername($user->getUsername());
    }

    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($request->hasSession()) {
            $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
        }

        return new RedirectResponse($this->router->generate('app_login'));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        $targetPath = $this->getTargetPath($request->getSession(), $providerKey);
        
        return new RedirectResponse($targetPath ?: $this->router->generate('app_home'));
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse($this->router->generate('app_login'));
    }

    public function supportsRememberMe(): bool
    {
        return false;
    }

    /**
     * @return GoogleClient
     */
    private function getGoogleClient()
    {
        return $this->clientRegistry->getClient('google');
    }
}
