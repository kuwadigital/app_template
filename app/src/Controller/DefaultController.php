<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_MANAGER')]
final class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_default')]
    #[IsGranted('ROLE_DEFAULT_VIEW')]
    public function index(): Response
    {
        return $this->render('default/index.html.twig');
    }

    #[Route('/add-test-user', name: 'app__test_user')]
    public function add_test_user(
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $em
    ): Response
    {
        $user = new User();
        $user->setUsername('test_user')
             ->setEmail('test_user@user.com')
             ->setPassword($userPasswordHasher->hashPassword($user, 'test_user'))
             ->setRoles([]);

        $em->persist($user);
        $em->flush();

        return $this->redirect("/");
    }
}
