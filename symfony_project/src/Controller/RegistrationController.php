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
use App\Entity\MysqlCredentials;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


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
            $username = $email_prefix;
            $password = $random_password;

            // Exécution de la commande adduser avec les paramètres appropriés
            $process = new Process(['sudo', 'adduser', '--gecos', '', $username]);
            $process->run();

            // Vérification de la sortie de la commande et affichage d'une erreur si nécessaire
            if (!$process->isSuccessful()) {
                throw new \RuntimeException($process->getErrorOutput());
            }

            // Définition du mot de passe pour le nouvel utilisateur
            $process = new Process(['bash', '-c', sprintf('echo -e "%s\n%s" | sudo passwd %s', $password, $password, $username)]);
            //$combo = $username . ':' . $password;
            //$process = new Process(['sudo', 'echo', $combo , '|', 'sudo', 'chpasswd']);
            $process->run();

            // Vérification de la sortie de la commande et affichage d'une erreur si nécessaire
            if (!$process->isSuccessful()) {
                throw new \RuntimeException($process->getErrorOutput());
            }

            //Accorde un acces ssh
            $process = new Process(['sudo', 'usermod', '-a', '-G', 'ssh', $username]);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new \RuntimeException($process->getErrorOutput());
            }

            // créer le dossier de l'utilisateur
            $process = new Process(['echo', 'hetic2023groupe10AIR!', '|' ,'sudo', '-S', 'mkdir', '/home/' . $email_prefix]);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            // changer le propriétaire du dossier de l'utilisateur
            $process = new Process(['echo', 'hetic2023groupe10AIR!','|' ,'sudo', '-S', 'chown', $email_prefix . ':' . $email_prefix, '/home/' . $email_prefix]);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            // changer les droits du dossier de l'utilisateur
            $process = new Process(['echo', 'hetic2023groupe10AIR!','|' ,'sudo', '-S', 'chmod', '700', '/home/' . $email_prefix]);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            //  change default shell folder to /home/user
            $process = new Process(['echo', 'hetic2023groupe10AIR!','|' ,'sudo', '-S', 'usermod', '-d', '/home/' . $email_prefix, $email_prefix]);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }


            // With eloquent create a new database for the user and allow him to connect to it
            $MysqlCredentials = new MysqlCredentials();
            $MysqlCredentials->setUsername($email_prefix);
            $MysqlCredentials->setPassword($random_password);
            $user->setMysqlCredentials($MysqlCredentials);

            // connect to db with mysql -u root -p'password' -h db
            // create a user with the same name as the email prefix
            $process = new Process(['mysql', '-u', 'root', '-p' . 'password', '-h', 'localhost', '-e', 'CREATE USER "' . $email_prefix . '"@"%" IDENTIFIED BY "' . $random_password . '";']);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            // create a database with the same name as the email prefix
            $process = new Process(['mysql', '-u', 'root', '-p' . 'password', '-h', 'localhost', '-e', 'CREATE DATABASE ' . $email_prefix . ';']);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            // give the user all the privileges on the database
            $process = new Process(['mysql', '-u', 'root', '-p' . 'password', '-h', 'localhost', '-e', 'GRANT ALL PRIVILEGES ON ' . $email_prefix . '.* TO "' . $email_prefix . '"@"%";']);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            // flush privileges
            $process = new Process(['mysql', '-u', 'root', '-p' . 'password', '-h', 'localhost', '-e', 'FLUSH PRIVILEGES;']);
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