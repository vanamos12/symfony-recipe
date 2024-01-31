<?php

namespace App\Tests\Functional;

use App\Entity\Ingredient;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IngredientTest extends WebTestCase
{
    public function testIfCreateIngredientIsSuccesful(): void
    {
        $client = static::createClient();
        
        //Recupérer urlGenerator
        $urlGenerator = $client->getContainer()->get('router');

        // recupérer l'entity manager
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        $user = $entityManager->find(User::class, 1);

        $client->loginUser($user);

        // Se rendre sur la page de création d'un ingrédient
        $crawler = $client->request(Request::METHOD_GET, $urlGenerator->generate('ingredient.new'));

        // Gérer le formulaire
        $form = $crawler->filter('form[name=ingredient]')->form([
            'ingredient[name]' => "Un ingrédient 106",
            'ingredient[price]' => floatval(45),
        ]);

        $client->submit($form);

        // Gérer la redirection
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        // Gérer l'alert box et la route 
        $this->assertSelectorTextContains('div.alert-success', 'Votre ingrédient a été crée avec succès !');

        $this->assertRouteSame('ingredient.index');
    }

    public function testIfListIngredientIsSuccessful():void{

        $client = static::createClient();
        
        //Recupérer urlGenerator
        $urlGenerator = $client->getContainer()->get('router');

        // recupérer l'entity manager
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        $user = $entityManager->find(User::class, 1);
 
        $client->loginUser($user);

        // Se rendre sur la page de création d'un ingrédient
        $crawler = $client->request(Request::METHOD_GET, $urlGenerator->generate('ingredient.index'));

        $this->assertResponseIsSuccessful();

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertSelectorTextContains('h1', 'Mes ingrédients');

        $this->assertSelectorTextContains('a.btn.btn-primary', 'Créer un ingrédient');

        $this->assertRouteSame('ingredient.index');
    }

    public function testIfUpdateAnIngredientIsSuccessfull():void {
        $client = static::createClient();

        $urlGenerator = $client->getContainer()->get('router');
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        $user = $entityManager->find(User::class, 1);
        $ingredient = $entityManager->getRepository(Ingredient::class)->findOneBy([
            'user' => $user
        ]);

        $client->loginUser($user);

        $crawler = $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate('ingredient.edit', ['id' => $ingredient->getId()])
        );

        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name=ingredient]')->form([
            'ingredient[name]' => "Un ingrédient 107",
            'ingredient[price]' => floatval(26),
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertSelectorTextContains('div.alert-success', 'Votre ingrédient a été modifiée avec succès !');

        $this->assertRouteSame('ingredient.index');
    }

    public function testIfDeleteAnIngredientIsSuccessfull():void{
        $client = static::createClient();

        $urlGenerator = $client->getContainer()->get('router');
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        $user = $entityManager->find(User::class, 1);
        $ingredient = $entityManager->getRepository(Ingredient::class)->findOneBy([
            'user' => $user
        ]);

        $client->loginUser($user);

        $crawler = $client->request(
            Request::METHOD_GET,
            $urlGenerator->generate('ingredient.delete', ['id' => $ingredient->getId()])
        );
        
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertSelectorTextContains('div.alert-success', 'Un ingrédient a été supprimé avec succès !');

        $this->assertRouteSame('ingredient.index');
    }
}
