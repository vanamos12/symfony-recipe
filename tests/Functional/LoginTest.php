<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class LoginTest extends WebTestCase
{
    public function testIfLoginIsSuccessful(): void
    {
        $client = static::createClient();
        
        // Get route by urlgenerator
        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get('router');

        $crawler = $client->request('GET', $urlGenerator->generate('security.login'));

        // Form
        $form = $crawler->filter("form[name=login]")->form([
            "_username" => "admin@symrecipe.fr",
            "_password" => "password"
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertRouteSame('home.index');


    }

    public function testIfLoginFailedWhenPasswordIsWrong(): void{
        $client = static::createClient();
        
        // Get route by urlgenerator
        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get('router');

        $crawler = $client->request('GET', $urlGenerator->generate('security.login'));

        // Form
        $form = $crawler->filter("form[name=login]")->form([
            "_username" => "admin@symrecipe.fr",
            "_password" => "password*"
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertRouteSame('security.login');

        $this->assertSelectorTextContains("div.alert-danger", "Invalid credentials");
    }
}
