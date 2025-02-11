<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Doctrine\ORM\EntityManagerInterface;

use App\Form\UserType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\SecurityBundle\Security;




final class UserController extends AbstractController{
    #[Route('/user', name: 'app_user_index')]
    public function index(): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();
        
        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]

    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

    if (!$user) {
        return $this->redirectToRoute('app_login');
    }
    
    $form = $this->createForm(UserType::class, $user);
    $form->handleRequest($request);


    if ($form->isSubmitted() && $form->isValid()) {

        $entityManager->flush();

        // Rediriger vers la page de profil
        $this->addFlash('success', 'Votre profil a été mis à jour.');
        return $this->redirectToRoute('app_user_index');
    }

    // Afficher le formulaire de modification
    return $this->render('user/edit_profile.html.twig', [
        'form' => $form,
    ]);
    }

    #[Route('/{id}', name:'app_user_delete', methods:['POST'])]

    public function delete(Request $request, EntityManagerInterface $entityManager, Security $security): Response
{
    // Récupérer l'utilisateur connecté
    $user = $this->getUser();

    // Vérifier si l'utilisateur est connecté
    if (!$user) {
        throw $this->createNotFoundException('Utilisateur non trouvé.');
        return $this->redirectToRoute('app_login');
    }

    // Vérifier que la requête est une requête POST (pour éviter les suppressions accidentelles)
    if ($request->isMethod('POST')) {

        // Déconnecter l'utilisateur avant suppression
        $security->logout(false); // `false` pour éviter une redirection automatique

        $entityManager->remove($user);
        $entityManager->flush();

        // Rediriger vers la page d'accueil
        $this->addFlash('success', 'Votre compte a été supprimé.');
        return $this->redirectToRoute('app_home');
    }

    // Rediriger vers le profil si la méthode n'est pas POST
    return $this->redirectToRoute('user_profile');
}




}
