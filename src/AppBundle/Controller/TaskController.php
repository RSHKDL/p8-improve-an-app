<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Task;
use AppBundle\Entity\User;
use AppBundle\Form\TaskType;
use AppBundle\Handler\TaskHandler;
use AppBundle\Repository\TaskRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

class TaskController extends Controller
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var TaskHandler
     */
    private $taskHandler;

    /**
     * @var TaskRepository
     */
    private $taskRepository;

    /**
     * TaskController constructor.
     * @param TranslatorInterface $translator
     * @param TaskHandler $taskHandler
     * @param TaskRepository $taskRepository
     */
    public function __construct(
        TranslatorInterface $translator,
        TaskHandler $taskHandler,
        TaskRepository $taskRepository
    ) {
        $this->translator = $translator;
        $this->taskHandler = $taskHandler;
        $this->taskRepository = $taskRepository;
    }

    /**
     * @Route("/tasks", name="task_list")
     */
    public function listTasks()
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        return $this->render('task/list.html.twig', [
            'tasks' => $this->taskRepository->findAllByUser($currentUser)
        ]);
    }

    /**
     * @Route("/tasks/archived", name="task_archived")
     * @return Response
     */
    public function listArchivedTasks()
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        return $this->render('task/list.html.twig', [
            'tasks' => $this->taskRepository->findAllByUser($currentUser, true)
        ]);
    }

    /**
     * @Route("/tasks/public", name="task_public")
     * @return Response
     */
    public function listPublicTasks()
    {
        return $this->render('task/public.html.twig', [
            'tasks' => $this->taskRepository->findAllWithFilter(null)
        ]);
    }

    /**
     * @Route("/tasks/public/archived", name="task_public_archived")
     * @return Response
     */
    public function listArchivedPublicTasks()
    {
        return $this->render('task/public.html.twig', [
            'tasks' => $this->taskRepository->findAllWithFilter(null,true)
        ]);
    }

    /**
     * @Route("/tasks/create", name="task_create")
     * @param Request $request
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function createAction(Request $request)
    {
        $form = $this->createForm(TaskType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();
            $this->taskHandler->create($form->getData(), $user);
            $this->addFlash('success', $this->translator->trans('task.create.success'));

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/tasks/{id}/edit", name="task_edit")
     * @param Task $task
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function editAction(Task $task, Request $request)
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $this->denyAccessUnlessGranted('edit', $task);
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', $this->translator->trans('task.update.success'));

            return $this->redirectToRoute($currentUser->isAdmin() ? 'task_public' : 'task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    /**
     * @Route("/tasks/{id}/toggle", name="task_toggle")
     * @param Task $task
     * @param Request $request
     * @return RedirectResponse
     */
    public function toggleTaskAction(Task $task, Request $request)
    {
        $task->toggle(!$task->isDone());
        $this->getDoctrine()->getManager()->flush();

        $message = 'task.done.success';
        if (!$task->isDone()) {
            $message = 'task.undone.success';
        }

        $this->addFlash('success', $this->translator->trans($message, ['%s' => $task->getTitle()]));
        $referer = $request->headers->get('referer');

        return $this->redirectToRoute(strpos($referer, 'public') ? 'task_public' : 'task_list');
    }

    /**
     * @Route("/tasks/{id}/delete", name="task_delete")
     * @param Task $task
     * @return RedirectResponse
     */
    public function deleteTaskAction(Task $task)
    {
        $this->denyAccessUnlessGranted('edit', $task);
        $em = $this->getDoctrine()->getManager();
        $em->remove($task);
        $em->flush();
        $this->addFlash('success', $this->translator->trans('task.delete.success'));

        return $this->redirectToRoute('task_list');
    }
}
