<?php 

namespace App\Controller;

use App\Repository\RecipeRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController {
    #[Route('/', 'home.index', methods:['GET'])]
    public function index(
        PaginatorInterface $paginator,
        Request $request,
        RecipeRepository $recipeRepository
    ) : Response{
        $recipes = $paginator->paginate(
            $recipeRepository->findPublicRecipe(3),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('pages/home.html.twig', [
            'recipes' => $recipes
        ]);
    }
}