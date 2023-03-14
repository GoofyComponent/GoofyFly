<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
// Entity manager
use Doctrine\ORM\EntityManagerInterface;

class ProfilController extends AbstractController
{
    #[Route('/deleteProfil', name: 'app_deleteProfil')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // get the current user
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $user = $this->getUser();
        $email_prefix = explode("@", $user->getEmail())[0];

        // // Process to delete user
        $process = new Process(['sudo', 'userdel', $email_prefix]);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }


        // del the home directory
        $process = new Process(['sudo', 'rm', '-rf', '/home/' . $email_prefix]);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        // End of process

        // Delete the user in the database
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_register');
    }
}
