<?php

namespace App\DataFixtures;

use App\Entity\Ingredient;
use App\Entity\Recipe;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class AppFixtures extends Fixture
{
    private Generator $faker;

    public function __construct(){
        $this->faker = Factory::create("fr_FR");
    }

    public function load(ObjectManager $manager): void
    {
        // ingredients
        $ingredients = [];
        for ($i=1; $i <=50 ; $i++) { 
            $ingredient = new Ingredient();
            $ingredient->setName($this->faker->word())
                ->setPrice(mt_rand(0, 100));

            $ingredients[] = $ingredient;
            $manager->persist($ingredient);
        }
       
        $difficulties = ['very easy','easy', 'average', 'hard', 'very hard'];
        // Recipes
        for ($j=0; $j< 25; $j++){
            $recipe = new Recipe();
            $recipe->setName($this->faker->word)
            ->setTime(mt_rand(1, 999))
            ->setNbPeople(mt_rand(1, 99))
            ->setDifficulty($difficulties[array_rand($difficulties)])
            ->setDescription($this->faker->text(300))
            ->setPrice(mt_rand(1, 10000))
            ->setFavorite(mt_rand(0, 1) == 1 ? true : false);
            
            for ($k=0; $k < mt_rand(5, 15); $k++ ){
                $recipe->addIngredient($ingredients[mt_rand(0, count($ingredients)-1)]);
            }
            $manager->persist($recipe);
        }
        
        $manager->flush();
    }
}
