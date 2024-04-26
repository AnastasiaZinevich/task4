<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RegistrationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();

           
            $encodedPassword = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($encodedPassword);
            $user->setState('active');
            
            $entityManager->persist($user);
            $entityManager->flush();

           
            $userRepository = $entityManager->getRepository(User::class);
            $addedUser = $userRepository->findOneBy(['email' => $user->getEmail()]);

            if ($addedUser) {
                
                $this->addFlash('success', 'Registration successful!');
            } else {
                
                $this->addFlash('error', 'Failed to register. Please try again.');
            }

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}