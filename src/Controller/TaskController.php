<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Turbo\TurboBundle;

#[Route('/tasks')]
final class TaskController extends AbstractController
{
    #[Route(name: 'app_task_index', methods: ['GET'])]
    public function index(TaskRepository $taskRepository): Response
    {
        $task = new Task();
        $new_task_form = $this->createForm(TaskType::class, $task);

        return $this->render('task/index.html.twig', [
            'tasks' => $taskRepository->findAllNotCompleted(),
            'new_task_form' => $new_task_form
        ]);
    }

    #[Route('/today', name: 'app_task_today', methods: ['GET'])]
    public function today(TaskRepository $taskRepository): Response
    {
        $task = new Task();
        $new_task_form = $this->createForm(TaskType::class, $task);

        return $this->render('task/index.html.twig', [
            'tasks' => $taskRepository->findAllToday(),
            'new_task_form' => $new_task_form
        ]);
    }

    #[Route('/to-review', name: 'app_task_to_review', methods: ['GET'])]
    public function toReview(TaskRepository $taskRepository): Response
    {
        $task = new Task();
        $new_task_form = $this->createForm(TaskType::class, $task);

        return $this->render('task/index.html.twig', [
            'tasks' => $taskRepository->findAllToReview(),
            'new_task_form' => $new_task_form
        ]);
    }

    #[Route('/{id}/{action}', name: 'app_task_complete', methods: ['POST'], requirements: ['action' => 'complete|uncomplete'])]
    public function completeTask(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        if ('complete' === $request->get('action')) {
            $task->setCompleted();
        } else {
            $task->setUncompleted();
        }
        $entityManager->flush();

        if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
            return $this->render('task/_task_replace.html.twig', ['task' => $task]);
        }

        return $this->redirectToRoute('app_task_index');
    }

    #[Route('/create', name: 'app_task_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $task = new Task();
        $new_task_form = $this->createForm(TaskType::class, $task);
        $new_task_form->handleRequest($request);

        if ($new_task_form->isSubmitted() && $new_task_form->isValid()) {
            $entityManager->persist($task);
            $entityManager->flush();

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                return $this->render('task/_task_create.html.twig', ['task' => $task, 'new_task_form' => $this->createForm(TaskType::class, new Task())]);
            }

            return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->redirectToRoute('app_task_index');
    }


    #[Route('/new', name: 'app_task_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $task = new Task();
        $new_task_form = $this->createForm(TaskType::class, $task);
        $new_task_form->handleRequest($request);

        if ($new_task_form->isSubmitted() && $new_task_form->isValid()) {
            $entityManager->persist($task);
            $entityManager->flush();

            return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task/new.html.twig', [
            'task' => $task,
            'new_task_form' => $new_task_form,
        ]);
    }

    #[Route('/{id}', name: 'app_task_show', methods: ['GET'])]
    public function show(Task $task): Response
    {
        return $this->render('task/show.html.twig', [
            'task' => $task,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_task_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        $edit_task_form = $this->createForm(TaskType::class, $task);
        $edit_task_form->handleRequest($request);

        if ($edit_task_form->isSubmitted() && $edit_task_form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'edit_task_form' => $edit_task_form,
            'new_task_form' => $this->createForm(TaskType::class, new Task()),
        ]);
    }

    #[Route('/{id}', name: 'app_task_delete', methods: ['POST'])]
    public function delete(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        $id = $task->getId();
        if ($this->isCsrfTokenValid('delete' . $task->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($task);
            $entityManager->flush();
        }
        if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
            return $this->render('partials/_delete_element.html.twig', ['elementId' => "task_{$id}"]);
        }

        return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
    }
}
