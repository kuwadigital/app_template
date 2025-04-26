<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/home/logged-in', name: 'app_home_logged_in')]
    #[IsGranted('ROLE_USER')]
    public function logged_in(#[CurrentUser] $user): Response
    {
        return $this->render('home/logged-in.html.twig', [
            'user_name' => $user->getUsername(),
            'user_email' => $user->getEmail(),
            'user_roles' => $user->getRoles()
        ]);
    }
}
