<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Task;
use AppBundle\Entity\User;
use AppBundle\Form\TaskType;
use AppBundle\Handler\TaskHandler;
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
     * TaskController constructor.
     * @param TranslatorInterface $translator
     * @param TaskHandler $taskHandler
     */
    public function __construct(
        TranslatorInterface $translator,
        TaskHandler $taskHandler
    ) {
        $this->translator = $translator;
        $this->taskHandler = $taskHandler;
    }

    /**
     * @Route("/tasks", name="task_list")
     */
    public function listAction()
    {
        return $this->render('task/list.html.twig', ['tasks' => $this->getDoctrine()->getRepository('AppBundle:Task')->findAll()]);
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
        $this->denyAccessUnlessGranted('edit', $task);

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', $this->translator->trans('task.update.success'));

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    /**
     * @Route("/tasks/{id}/toggle", name="task_toggle")
     * @param Task $task
     * @return RedirectResponse
     */
    public function toggleTaskAction(Task $task)
    {
        $task->toggle(!$task->isDone());
        $this->getDoctrine()->getManager()->flush();

        $message = 'task.done.success';
        if (!$task->isDone()) {
            $message = 'task.undone.success';
        }

        $this->addFlash('success', $this->translator->trans($message, ['%s' => $task->getTitle()]));

        return $this->redirectToRoute('task_list');
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
