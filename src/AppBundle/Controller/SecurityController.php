<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use AppBundle\Handler\UserHandler;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

class SecurityController extends Controller
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
     * SecurityController constructor.
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
     * @Route("/login", name="login")
     * @return Response
     */
    public function login()
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
    }

    /**
     * @Route("/register", name="register")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function register(Request $request)
    {
        $form = $this->createForm(UserType::class, null, [
            'isFromAdmin' => false,
            'isNewUser' => true,
            'editSelf' => false
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->userHandler->create($form);

            $this->addFlash(
                'success',
                $this->translator->trans('user.create.success', ['%name' => $user->getUsername()])
            );

            return $this->redirectToRoute('task_list');
        }

        return $this->render('security/register.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/login_check", name="login_check")
     */
    public function loginCheck()
    {
        // This code is never executed.
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutCheck()
    {
        // This code is never executed.
    }
}
