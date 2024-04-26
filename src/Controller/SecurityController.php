<?php
namespace App\Controller;

use App\Form\SecurityFormType;
use App\Security\LoginFormAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


class SecurityController extends AbstractController
{
    

    #[Route(path: '/login', name: 'app_login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils, LoginFormAuthenticator $authenticator): Response
    {
       
        $error = $authenticationUtils->getLastAuthenticationError();
        
      
        $lastUsername = $authenticationUtils->getLastUsername();
        
        $form = $this->createForm(SecurityFormType::class);
        
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
    
            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                $username = $data['username'];
                $password = $data['password'];
    
              
                echo '<script>console.log('.json_encode($username).', '.json_encode($password).');</script>';
    
               
            }
        }
    
       
    
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'form' => $form->createView(),
        ]);
    }
    
}
