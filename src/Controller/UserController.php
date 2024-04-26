<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security; 

use Symfony\Component\HttpFoundation\JsonResponse;
use DateTime;

use DateTimeZone;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class UserController extends AbstractController
{
    
  
    #[Route("/register", name: "register", methods: ["GET", "POST"])]
    public function register(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $form = $this->createForm(RegistrationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();

            $user->setPassword(
                $form->get('plainPassword')->getData()
            );

            $timezone = new \DateTimeZone('Europe/Minsk');
            $registrationDate = new \DateTime('now', $timezone);
            $user->setRegistrationDateу($registrationDate);

            $user->setState('active');

            try {
               
                $existingUser = $entityManager->getRepository(User::class)->findOneBy([
                    'email' => $user->getEmail(),
                ]);

                if ($existingUser) {
                    $errorMessage = 'This email is already registered. Please choose a different email address.';
                    $form->addError(new \Symfony\Component\Form\FormError($errorMessage));
                    
                    return $this->render('registration/register.html.twig', [
                        'registrationForm' => $form->createView(),
                    ]);
                }

              
                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirectToRoute('app_login');
            } catch (\Exception $e) {
                
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    
    #[Route("/logout", name: "app_logout")]
    public function logout(): void
    {
       
        throw new \Exception('This method can be intercepted by the logout key on your firewall.');
    }

   
    #[Route("/user/index", name: "user_index")]
    public function index(): Response 
    {
       
        $user = $this->getUser();

       
        if (!$user) {
           
            return $this->redirectToRoute('app_login');
        }

        
        return $this->render('user/index.html.twig', [
            'user' => $user,
        ]);
    }

    
}