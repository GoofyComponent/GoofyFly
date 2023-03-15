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

            // on récupère la liste des ports utilisés dans un tableau php
            $process = new Process(['sudo', 'netstat', '-tulpn']);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            $result = $process->getOutput();
            // grab Local Address. it can also be :::XXXXX
            preg_match_all('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:\d{1,5}/', $result, $matches);
            $ports = $matches[0];
            array_push($ports, '0.0.0.0:9000', '0.0.0.0:3306');
            // only keep the port number
            $ports = array_map(function ($port) {
                return explode(':', $port)[1];
            }, $ports);
            // encode the plain password

            // get two random port that not used by any service
            $web_port = rand(1024, 65535);
            while (in_array($web_port, $ports)) {
                $web_port = rand(1024, 65535);
            }

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
            $LinuxCredentials->setPort($web_port);
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


            // With eloquent create a new database for the user and allow him to connect to it
            $MysqlCredentials = new MysqlCredentials();
            $MysqlCredentials->setUsername($email_prefix);
            $MysqlCredentials->setPassword($random_password);
            $user->setMysqlCredentials($MysqlCredentials);

            // connect to db with mysql -u root -p'password' -h db
            // create a user with the same name as the email prefix
            $process = new Process(['mysql', '-u', 'root', '-p' . 'password', '-h', 'db', '-e', 'CREATE USER "' . $email_prefix . '"@"%" IDENTIFIED BY "' . $random_password . '";']);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            // create a database with the same name as the email prefix
            $process = new Process(['mysql', '-u', 'root', '-p' . 'password', '-h', 'db', '-e', 'CREATE DATABASE ' . $email_prefix . ';']);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            // give the user all the privileges on the database
            $process = new Process(['mysql', '-u', 'root', '-p' . 'password', '-h', 'db', '-e', 'GRANT ALL PRIVILEGES ON ' . $email_prefix . '.* TO "' . $email_prefix . '"@"%";']);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            // flush privileges
            $process = new Process(['mysql', '-u', 'root', '-p' . 'password', '-h', 'db', '-e', 'FLUSH PRIVILEGES;']);
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
