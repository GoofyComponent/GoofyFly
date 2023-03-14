<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;


use App\Entity\LinuxCredentials;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $email_prefix = explode("@", $user->getEmail())[0];
            $random_password = bin2hex(random_bytes(8));
            $LinuxCredentials = new LinuxCredentials();
            $LinuxCredentials->setUsername($email_prefix);
            $LinuxCredentials->setPassword($random_password);
            $user->setLinuxCredentials($LinuxCredentials);

            // Process to create user

                // Create user with random password
            $process = new Process(['sudo', 'useradd', $email_prefix, '-p', $random_password]);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            
                // créer le dossier de l'utilisateur
            $process = new Process(['sudo', 'mkdir', '/home/' . $email_prefix]);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
                // changer le propriétaire du dossier de l'utilisateur
            $process = new Process(['sudo', 'chown', $email_prefix . ':' . $email_prefix, '/home/' . $email_prefix]);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
                // changer les droits du dossier de l'utilisateur
            $process = new Process(['sudo', 'chmod', '700', '/home/' . $email_prefix]);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            //  change default shell folder to /home/user
            $process = new Process(['sudo', 'usermod', '-d', '/home/' . $email_prefix, $email_prefix]);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            // End of process

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}