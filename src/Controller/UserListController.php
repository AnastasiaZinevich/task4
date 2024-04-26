<?php

namespace App\Controller;

use App\Form\UserListType;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use DateTime;


use Doctrine\ORM\EntityManagerInterface;

class UserListController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    /**
     * @Route("/delete-user/{userId}", name="delete_user", methods={"POST"})
     */
    public function deleteUser(int $userId): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->find($userId);

        if (!$user) {
            return new JsonResponse('User not found', 404);
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return new JsonResponse('User deleted successfully');
    }
    public function deleteSelectedUsers(Request $request): Response
    {
        $selectedUserIds = json_decode($request->getContent(), true)['selected_users'] ?? [];
    
        foreach ($selectedUserIds as $userId) {
            $this->deleteUser((int)$userId); 
        }
    
        return new JsonResponse('Selected users deleted successfully');
    }
    
    

    public function blockUser(int $userId): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->find($userId);

        if (!$user) {
            return new JsonResponse('User not found', 404);
        }

        
        $user->setState('blocking');

        $this->entityManager->flush();

        return new JsonResponse('User blocked successfully');
    }
    public function blockUsers(array $userIds): JsonResponse
    {
        $users = $this->entityManager->getRepository(User::class)->findBy(['id' => $userIds]);
    
        if (empty($users)) {
            return new JsonResponse('Users not found', 404);
        }
    
        
        foreach ($users as $user) {
            $user->setState('blocking');
        }
    
        $this->entityManager->flush();
    
        return new JsonResponse('Users blocked successfully');
    }



    public function unblockUser(int $userId): JsonResponse
{
    $user = $this->entityManager->getRepository(User::class)->find($userId);

    if (!$user) {
        return new JsonResponse('User not found', 404);
    }

   
    $user->setState('active');

    $this->entityManager->flush();

    return new JsonResponse('User unblocked successfully');
}

public function unblockUsers(array $userIds): JsonResponse
{
    $users = $this->entityManager->getRepository(User::class)->findBy(['id' => $userIds]);

    if (empty($users)) {
        return new JsonResponse('Users not found', 404);
    }

   
    foreach ($users as $user) {
        $user->setState('active');
    }

    $this->entityManager->flush();

    return new JsonResponse('Users unblocked successfully');
}
    /**
     * @Route("/user_list", name="user_list")
     */
    public function index(Request $request): Response
    {
        $form = $this->createForm(UserListType::class);
        $form->handleRequest($request);
        
        
        if ($request->hasSession() && $this->getUser()) {
           
            $user = $this->getUser();

            
            $user->setLastLogin(new DateTime());
            $this->getDoctrine()->getManager()->flush();
            
           
            $username = $user->getUsername();
            $userId = $user->getId();
            
            
            $userData = [
                'username' => $username,
                'userId' => $user->getId(), 
               
            ];
            
           
            return $this->render('user_list/index.html.twig', [
                'form' => $form->createView(),
                'userData' => $userData,
                'users' => $this->getDoctrine()->getRepository(User::class)->findAll(),
                'userId' => $userId,
            ]);
        }
        
        
        return $this->redirectToRoute('app_login');
        
    }

    /**
     * @Route("/update_last_login_date", name="update_last_login_date")
     */
    public function updateLastLoginDate(EntityManagerInterface $entityManager): JsonResponse
    {
        
        $userId = $this->getUser()->getId();
        
       
        $user = $entityManager->getRepository(User::class)->find($userId);
        
       
        if ($user) {
            $user->setLastLogin(new DateTime());
            $entityManager->flush();
            
            $lastLogin = $user->getLastLogin()->format('Y-m-d H:i:s');
            return new JsonResponse($lastLogin);
        }
        
       
        return new JsonResponse(null, 401);
    }
}
