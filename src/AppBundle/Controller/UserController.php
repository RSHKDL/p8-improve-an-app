<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use AppBundle\Handler\UserHandler;
use AppBundle\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

class UserController extends Controller
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var UserHandler
     */
    private $userHandler;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * UserController constructor.
     * @param TranslatorInterface $translator
     * @param UserHandler $userHandler
     * @param UserRepository $userRepository
     */
    public function __construct(
        TranslatorInterface $translator,
        UserHandler $userHandler,
        UserRepository $userRepository
    ) {
        $this->translator = $translator;
        $this->userHandler = $userHandler;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/users", name="user_list")
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);
        $nonAdminUsers = $this->userRepository->findAllNonAdmin();

        return $this->render('user/list.html.twig',
            [
                'users' => $this->getDoctrine()->getRepository('AppBundle:User')->findAll(),
                'hasOnlyAdmin' => empty($nonAdminUsers) ? true : false
            ]
        );
    }

    /**
     * @Route("/profile", name="user_profile")
     */
    public function profileAction()
    {
        $this->denyAccessUnlessGranted(User::ROLE_USER);
        $user = $this->getUser();

        return $this->render('user/profile.html.twig', ['user' => $user]);
    }

    /**
     * @Route("/users/create", name="user_create")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);
        $form = $this->createForm(UserType::class, null, [
            'isFromAdmin' => true,
            'isNewUser' => true,
            'editSelf' => false,
            'validation_groups' => ['registration'],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->userHandler->createUserFromDTO($form->getData());
            $this->addFlash(
                'success',
                $this->translator->trans('user.create.success', ['%name' => $user->getUsername()]));

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/users/{id}/edit", name="user_edit")
     * @param User $user
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function editAction(User $user, Request $request)
    {
        $this->denyAccessUnlessGranted('edit', $user);
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $dto = $this->userHandler->createUserDtoFromUser($user);

        $form = $this->createForm(UserType::class, $dto, [
            'isFromAdmin' => $currentUser->isAdmin(),
            'isNewUser' => false,
            'editSelf' => $currentUser === $user,
            'validation_groups' => ['edition']
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->userHandler->update($user, $form->getData());
            $this->addFlash('success', $this->translator->trans('user.update.success', ['%name' => $user->getUsername()]));

            return in_array(User::ROLE_ADMIN, $currentUser->getRoles(), true) ? $this->redirectToRoute('user_list') : $this->redirectToRoute('user_profile');
        }

        return $this->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $user]);
    }

    /**
     * @Route("/users/{id}/delete", name="user_delete")
     * @param User $user
     * @return RedirectResponse
     */
    public function deleteAction(User $user)
    {
        $this->denyAccessUnlessGranted('delete', $user);

        $this->userHandler->delete($user);
        $this->addFlash('success', $this->translator->trans('user.delete.success'));

        return $this->redirectToRoute('user_list');
    }
}
