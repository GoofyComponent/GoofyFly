<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Process\Process;

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

        //Get user directory size
        $directory_size = 0;
        $getDirectoryPath = "/home/" . $username;
        $process = new Process(['du', '-hs', $getDirectoryPath]);
        $process->run();

        if ($process->isSuccessful()) {
            $output = $process->getOutput();
            $output = str_replace($getDirectoryPath, "", $output);
            $output = str_replace(" ", "", $output);
            $parts = explode(' ', $output);
            $bytes = (int) $parts[0];
            $mbs = round($bytes / (1024 * 1024), 1);
            $directory_size = $mbs;
        } else {
            echo $process->getErrorOutput();
        }

        //Get user database size
        $database_size = 0;
        $getDatabaseName = $username;
        $process = new Process(['sudo', 'mysql', '-e', 'SELECT table_schema "Data Base Name", Round(Sum(data_length + index_length) / 1024 / 1024, 1) "Data Base Size in MB" FROM information_schema.tables GROUP BY table_schema;']);
        $process->run();

        if ($process->isSuccessful()) {
            $output = $process->getOutput();
            $output = str_replace($getDatabaseName, "", $output);
            $output = str_replace(" ", "", $output);
            $parts = explode(' ', $output);
            $bytes = (int) $parts[0];
            $mbs = round($bytes / (1024 * 1024), 1);
            $database_size = $mbs;
        } else {
            echo $process->getErrorOutput();
        }

        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
            'username' => $username,
            'password' => $password,
            'domain_name' => $domain_name,
            'directory_size' => $directory_size,
            'directory_size_full' => $output,
            'database_size' => $database_size,
        ]);
    }
}