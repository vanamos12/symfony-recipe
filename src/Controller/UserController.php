<?php

namespace App\Controller;

use App\Form\UserPasswordType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\Security;

class UserController extends AbstractController
{
    
    /**
     * This controller allow us to modify the profil of a user.
     *
     * @param integer $id
     * @param UserRepository $repository
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') and user === repository.find(id)")]
    #[Route('/utilisateur/edition/{id}', name: 'user.edit')]
    public function edit(
        int $id, 
        UserRepository $repository,
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordHasherInterface $hasher): Response
    {
        if (!$this->getUser()){
            return $this->redirectToRoute('security.login');
        }

        $user = $repository->find($id);
        if ($this->getUser() !== $user){
            return $this->redirectToRoute('recipe.index');
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            
            if ($hasher->isPasswordValid($user, $form->getData()->getPlainPassword()))
            {
                $user = $form->getData();
                $manager->persist($user);
                $manager->flush();

                $this->addFlash('success', 'Les informations de votre compte ont bien été enregistrées!');

                return $this->redirectToRoute('recipe.index');
            } else {
                $this->addFlash(
                    'warning',
                    'Le mot de passe renseigné est incorrect.'
                );
            }
        }

        return $this->render('pages/user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Security("is_granted('ROLE_USER') and user === repository.find(id)")]
    #[Route('/utilisateur/edition-mot-de-passe/{id}', 'user-edit-password', methods:['GET', 'POST'])]
    public function editPass(
        int $id,
        UserRepository $repository,
        Request $request,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $manager
        ):Response{

        $user = $repository->find($id);
        $form = $this->createForm(UserPasswordType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            if ($hasher->isPasswordValid($user, $form->getData()['plainPassword'])){
                $user->setPassword(
                    $hasher->hashPassword(
                        $user,
                        $form->getData()['newPassword']
                    )
                );

                $manager->persist($user);
                $manager->flush();

                $this->addFlash('success', 'Le mot de passe a été modifié!');
                return $this->redirectToRoute('recipe.index');
            }else{
                $this->$this->addFlash(
                    'warning',
                    'Le mot de passe renseigné est incorrect.'
                );
            }
        }
        return $this->render('pages/user/edit_password.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
