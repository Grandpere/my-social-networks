<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use KnpU\OAuth2ClientBundle\Client\Provider\GoogleClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GoogleController extends AbstractController
{
    /**
     * @Route("/connect/google", name="connect_google_start")
     */
    public function connectAction(ClientRegistry $clientRegistry): RedirectResponse
    {
        /** @var GoogleClient */
        return $clientRegistry
            ->getClient('google') 
            ->redirect([
                'profile', 'email'
            ], []);
    }
}