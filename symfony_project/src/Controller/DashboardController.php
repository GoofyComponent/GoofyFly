<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): Response
    {
        // get the current user
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        // get the current user
        $user = $this->getUser();

        // get the current user's linux credentials
        $linux_credentials = $user->getLinuxCredentials();

        //Get the username and password
        $username = $linux_credentials->getUsername();
        $password = $linux_credentials->getPassword();

        //Get the current web server domain name if it exists, if not use the IP address
        $domain_name = $_SERVER['SERVER_NAME'];
        if ($domain_name == "" && $_SERVER['SERVER_ADDR'] != "" && $domain_name == "_") {
            $domain_name = $_SERVER['SERVER_ADDR'];
        }

        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
            'username' => $username,
            'password' => $password,
            'domain_name' => $domain_name,
        ]);
    }
}