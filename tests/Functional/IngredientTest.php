<?php

namespace App\Tests\Functional;

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
            'ingredient[name]' => "Un ingrédient 105",
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
}
