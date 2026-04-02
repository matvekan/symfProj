<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PageController extends AbstractController
{
    #[Route('/', name: 'page_login', methods: ['GET'])]
    public function loginPage(): Response
    {
        return $this->render('pages/login.html.twig');
    }

    #[Route('/register', name: 'page_register', methods: ['GET'])]
    public function registerPage(): Response
    {
        return $this->render('pages/register.html.twig');
    }

    #[Route('/forgot-password', name: 'page_forgot_password', methods: ['GET'])]
    public function forgotPasswordPage(): Response
    {
        return $this->render('pages/forgot_password.html.twig');
    }

    #[Route('/dashboard/user', name: 'page_user_dashboard', methods: ['GET'])]
    public function userDashboardPage(): Response
    {
        return $this->render('pages/user_dashboard.html.twig');
    }

    #[Route('/dashboard/admin', name: 'page_admin_dashboard', methods: ['GET'])]
    public function adminDashboardPage(): Response
    {
        return $this->render('pages/admin_dashboard.html.twig');
    }
}
