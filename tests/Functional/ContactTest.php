<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ContactTest extends WebTestCase
{
    public function testSomething(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact?env=test');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Formulaire de Contact');

        // Récupérer le formulaire 
        $submitButton = $crawler->selectButton('Soumettre ma demande');
        $form = $submitButton->form();

        $form["contact[fullName]"] = "Jean Dupont";
        $form["contact[email]"] = "jd@symrecipe.com";
        $form["contact[subject]"] = "Test";
        $form["contact[message]"] = "Test";

        //dd($crawler);
        //$form->remove("captcha");
        // Soumettre le formulaire
        $client->submit($form);

        // Vérifier le statut HTTP
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        // Verifier l'envoie du mail
        // $this->assertEmailCount(1);

        $client->followRedirect();
        //dd($client);
        // Vérifier la présence du message de succes
        $this->assertSelectorTextContains(
            'div.alert.alert-success.mt-4',
            'Votre demande a bien été envoyée !'
        );
    }
}
