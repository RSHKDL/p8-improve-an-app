<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use AppBundle\Handler\UserHandler;
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
     * UserController constructor.
     * @param TranslatorInterface $translator
     * @param UserHandler $userHandler
     */
    public function __construct(
        TranslatorInterface $translator,
        UserHandler $userHandler
    ) {
        $this->translator = $translator;
        $this->userHandler = $userHandler;
    }

    /**
     * @Route("/users", name="user_list")
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);
        $nonAdminUsers = $this->getDoctrine()->getRepository('AppBundle:User')->findAllNonAdmin();

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
        $form = $this->createForm(UserType::class, [], [
            'isFromAdmin' => true,
            'isNewUser' => true
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->userHandler->create($form->getData());
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
        $form = $this->createForm(UserType::class, $user, [
            'isFromAdmin' => $currentUser->isAdmin(),
            'isNewUser' => false
        ]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $password = $this->get('security.password_encoder')->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', $this->translator->trans('user.update.success'));

            return in_array(User::ROLE_ADMIN, $user->getRoles(), true) ? $this->redirectToRoute('user_list') : $this->redirectToRoute('user_profile');
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
        $currentUser = $this->getUser();
        $this->denyAccessUnlessGranted('delete', $currentUser);

        $this->getDoctrine()->getManager()->remove($user);
        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('success', $this->translator->trans('user.delete.success'));

        return $this->redirectToRoute('user_list');
    }
}
