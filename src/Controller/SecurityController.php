<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/register", name="register")
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function register(EntityManagerInterface $em,
                             Request $request,
                             UserPasswordEncoderInterface $encoder)
    {
        $user = new Participant();
        $registerForm = $this->createForm(RegisterType::class, $user);

        $registerForm->handleRequest($request);
        if ($registerForm->isSubmitted() && $registerForm->isValid()) {
            $hash = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash);
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute("login");

//            $token = new UsernamePasswordToken($user, null,
//                'main', $user->getRoles());
//            $this->container->get('security.token_storage')->setToken($token);
//            $this->container->get('session')->set('_security_main', serialize($token));
//            return $this->redirectToRoute('article_liste');
        }

        return $this->render('test/register.html.twig', [
            'registerForm' => $registerForm->createView()
        ]);
    }

    /**
     * @Route("/login", name="login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \Exception('This method can be blank - it will be intercepted by the logout key on your firewall');
    }
}
