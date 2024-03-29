<?php

namespace App\Controller;

use App\Entity\Mark;
use App\Entity\Recipe;
use App\Form\MarkType;
use App\Form\RecipeType;
use App\Repository\MarkRepository;
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
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

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
        /*#[MapEntity(mapping: ['id' => 'id'])]
        Recipe $recipe,*/
        EntityManagerInterface $manager
    ) : Response {
        //dd($recipe);
        
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

    #[Security("is_granted('ROLE_USER') and user === repository.find(id).getUser()")]
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

    #[Route('/recette/communaute', 'recipe.community', methods:['GET'])]
    public function indexPublic(
        RecipeRepository $repository,
        PaginatorInterface $paginator,
        Request $request
    ) : Response{

        $cache = new FilesystemAdapter();
        $data = $cache->get('recipes', function (ItemInterface $item) use ($repository){
            $item->expiresAfter(20);
            return $repository->findPublicRecipe(null);
        });

        $recipes = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('pages/recipe/index_public.html.twig', [
            'recipes' => $recipes
        ]);
    }

    #[Security("is_granted('ROLE_USER') and (repository.find(id).getIsPublic() === true || user===repository.find(id).getUser())")]
    #[Route('/recette/{id}', 'recipe.show', methods:['GET', 'POST'])]
    public function show(
        RecipeRepository $recipeRepository,
        MarkRepository $markRepository,
        EntityManagerInterface $manager,
        int $id,
        Request $request
    ):Response
    {
        $mark = new Mark();

        $recipe = $recipeRepository->find($id);

        $form = $this->createForm(MarkType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $mark->setUser($this->getUser())
                ->setRecipe($recipe);

            $existingMark = $markRepository->findOneBy([
                'user' => $this->getUser(),
                'recipe' => $recipe
            ]);

            if (!$existingMark){
                $manager->persist($mark);

            }else{
                $existingMark->setMark(
                    $form->getData()->getMark()
                );
            }

            $manager->flush();

            $this->addFlash(
                'success',
                'Votre note a bien été prise en compte'
            );
            return $this->redirectToRoute('recipe.show', ['id' => $recipe->getId()]);
        }

        
        return $this->render('pages/recipe/show.html.twig', [
            'recipe' => $recipe,
            'form' => $form->createView()
        ]);
    }
}
