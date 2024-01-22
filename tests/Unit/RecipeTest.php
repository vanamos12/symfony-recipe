<?php

namespace App\Tests\Unit;

use App\Entity\Mark;
use App\Entity\Recipe;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RecipeTest extends KernelTestCase
{
    public function getEntity(): Recipe
    {
        return (new Recipe())->setDescription('Descripition #1')
        ->setFavorite(true)
        ->setPrice(100)
        ->setDifficulty('average')
        ->setNbPeople(7)
        ->setCreatedAt(new \DateTimeImmutable())
        ->setUpdatedAt(new \DateTimeImmutable());
    }
    public function testEntityIsValid(): void
    {
        $kernel = self::bootKernel();
        $container = static::getContainer();

        //$this->assertSame('test', $kernel->getEnvironment());
        // $routerService = static::getContainer()->get('router');
        // $myCustomService = static::getContainer()->get(CustomService::class);

        $recipe = $this->getEntity();
        $recipe->setName('Recipe #1');

        $errors = $container->get('validator')->validate($recipe);
        
        $this->assertCount(0, $errors);
    }

    public function testInvalidName():void
    {
        $kernel = self::bootKernel();
        $container = static::getContainer();

        $recipe = $this->getEntity();
        $recipe->setName('');

        $errors = $container->get('validator')->validate($recipe);
        
        $this->assertCount(2, $errors);
    }

    public function testGetAverage():void
    {
        $recipe = $this->getEntity();
        $user = static::getContainer()
                    ->get('doctrine.orm.entity_manager')
                    ->find(User::class, 1);

        for ($i=0; $i < 5; $i++) { 
            $mark = new Mark();
            $mark->setMark(2)
                ->setUser($user)
                ->setRecipe($recipe);

            $recipe->addMark($mark);
        }

        $this->assertEquals(2, $recipe->getAverage());
    }
}
