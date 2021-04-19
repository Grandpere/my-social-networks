<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use KnpU\OAuth2ClientBundle\Client\Provider\GithubClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GithubController extends AbstractController
{
    /**
     * @Route("/connect/github", name="connect_github_start")
     */
    public function connect(ClientRegistry $clientRegistry): RedirectResponse
    {
        /** @var GithubClient */
        return $clientRegistry
            ->getClient('github')
            ->redirect([
                'read:user', 'user:email'
            ], []);
    }
}
