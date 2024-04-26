<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Twig\Environment;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Form\FormFactoryInterface;
use App\Form\UserListType;
use App\Controller\UserListController;
use Symfony\Component\Routing\Annotation\Route;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    /**
     * @Route("/block_selected_users", name="block_selected_users", methods={"POST"})
     */
    


    public function unblockSelectedUsers(Request $request): Response
    {
        
        $selectedUserIds = $request->request->get('selected_users');

        
        foreach ($selectedUserIds as $userId) {
            $this->unblockUser($userId);
        }

        
        return new Response('Selected users unblocked successfully');
    }

    public function deleteSelectedUsers(Request $request): Response
    {
        
        $selectedUserIds = $request->request->get('selected_users');

        
        foreach ($selectedUserIds as $userId) {
            $this->deleteUser($userId);
        }

       
        return new Response('Selected users deleted successfully');
    }

    public function blockUser($userId): Response
    {
       
        $user = $this->getDoctrine()->getRepository(User::class)->find($userId);

       
        $user->setBlocked(true);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();

       
        return new Response('User blocked successfully');
    }

    public function unblockUser($userId): Response
    {
        
        $user = $this->getDoctrine()->getRepository(User::class)->find($userId);

        
        $user->setBlocked(false);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();

        
        return new Response('User unblocked successfully');
    }

    public function deleteUser($userId): Response
    {
       
        $user = $this->getDoctrine()->getRepository(User::class)->find($userId);

        
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush();

       
        return new Response('User deleted successfully');
    }

    private $kernel;
    private $tokenStorage;
    private $security;
    private $twig;
    private $formFactory;
    private $userListController;
    public function __construct(UrlGeneratorInterface $urlGenerator, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, Security $security, Environment $twig, HttpKernelInterface $kernel, FormFactoryInterface $formFactory, UserListController $userListController)
    {
        $this->urlGenerator = $urlGenerator;
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->security = $security;
        $this->twig = $twig;
        $this->kernel = $kernel;
        $this->formFactory = $formFactory;
        $this->userListController = $userListController;
    }
   
    public function authenticate(Request $request): Passport
    {
        $username = $request->request->get('username', '');
        $password = $request->request->get('password', '');

        
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);

        if (!$user || $password !== $user->getPassword()) {
            
            $errorMessage = empty($username) ? 'Username is empty' : 'Invalid username or password for user "' . $username . '"';
           
            throw new BadCredentialsException($errorMessage);
        }

        if ($user->getState() === 'blocking') {
           
            throw new BadCredentialsException('User "' . $username . '" is blocked');
        }
        
        
        $request->getSession()->set('id', $user->getId());

       
        return new Passport(
            new UserBadge($username),
            new PasswordCredentials($password)
        );
    }

   
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
{
    
    $userId = $request->getSession()->get('id');
    
    
    if (!$userId) {
       
        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }
    
   
    $timezone = new \DateTimeZone('Europe/Minsk');
    $lastLogin = (new \DateTime(null, $timezone))->format('Y-m-d H:i:s');
    $this->entityManager->createQueryBuilder()
        ->update(User::class, 'u')
        ->set('u.lastLogin', ':lastLogin')
        ->setParameter('lastLogin', $lastLogin)
        ->where('u.id = :userId')
        ->setParameter('userId', $userId)
        ->getQuery()
        ->execute();

    
    $user = $this->entityManager->getRepository(User::class)->find($userId);

    
    if (!$user) {
        
        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }

    
    $lastLogin = $user->getLastLogin();
    $userData = [
        'lastLogin' => $lastLogin ? $lastLogin->format('Y-m-d H:i:s') : 'Not available',
        'id' => $userId,
    ];

    
    $users = $this->entityManager->getRepository(User::class)->findAll();

   
    $form = $this->formFactory->create(UserListType::class);

    
    return new Response($this->twig->render('user_list/index.html.twig', [
        'user_data' => $userData,
        'form' => $form->createView(),
        'users' => $users,
        'userId' => $userId,
    ]));
}

    



    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate('app_login');
    }
}
