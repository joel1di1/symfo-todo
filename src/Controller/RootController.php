<?php
// src/Controller/DefaultController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RootController extends AbstractController
{
  #[Route('/', name: 'root')]
  public function redirectToTasks(): Response
  {
    return $this->redirectToRoute('app_task_index');
  }
}
