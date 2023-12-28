<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\Security;

class RecipeController extends AbstractController
{
    /**
     * This controller display all recipes
     *
     * @param RecipeRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    #[Route('/recette', name: 'recipe.index', methods:['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(
        RecipeRepository $repository, 
        PaginatorInterface $paginator, 
        Request $request): Response
    {
        $recipes = $paginator->paginate(
            $repository->findBy(['user' => $this->getUser()]), /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('pages/recipe/index.html.twig', [
            'recipes' => $recipes,
        ]);
    }
    /**
     * This controller allows us to create a new recipe
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */ 
    #[Route('/recette/nouveau', name:'recipe.new', methods:['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $manager
        ): Response{
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $recipe = $form->getData();
            $recipe->setUser($this->getUser());

            $manager->persist($recipe);
            $manager->flush();

            $this->addFlash('success', 'Une recette a été enregistrée.');

            return $this->redirectToRoute('recipe.index');
        }

        return $this->render('pages/recipe/new.html.twig', [
            'form' => $form->createView()
        ]);
    } 

    /**
     * This controller allows us to edit a recipe
     */
    #[Security("is_granted('ROLE_USER') and user === repository.find(id).getUser()")]
    #[Route('/recette/edition/{id}', name:'recipe.edit', methods:['GET', 'POST'])]
    public function edit(
        RecipeRepository $repository,
        int $id,
        Request $request,
        EntityManagerInterface $manager
    ) : Response {
        $recipe = $repository->findOneBy(['id' => $id]);
        $form = $this->createForm(RecipeType::class, $recipe);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $recipe = $form->getData();

            $manager->persist($recipe);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre recette a été modifiée avec succès !'
            );

            return $this->redirectToRoute('recipe.index');

        }

        return $this->render('pages/recipe/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/recette/suppression/{id}', name:'recipe.delete', methods:['GET'])]
    public function delete(
        RecipeRepository $repository,
        EntityManagerInterface $manager,
        int $id
    ) : Response{
        $ingredient = $repository->findOneBy(['id' => $id]);

        if (!$ingredient){
            $this->addFlash(
                'success',
                'La recette en question n\'a pas été trouvée !'
            );
    
            return $this->redirectToRoute('recipe.index');
        }

        $manager->remove($ingredient);
        $manager->flush();

        $this->addFlash(
            'success',
            'La recette a été supprimée avec succès !'
        );

        return $this->redirectToRoute('recipe.index');
    } 
}
