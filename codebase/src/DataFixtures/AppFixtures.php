<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Article;
use App\Entity\Category;
use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private const CATEGORIES = 5;
    private const ARTICLES = 20;

    private $faker;

    /**
     * @var UserPasswordHasherInterface
     */
    private $hasher;
    /**
     * @var SluggerInterface
     */
    private $slugger;

    public function __construct(UserPasswordHasherInterface $hasher, SluggerInterface $slugger)
    {
        $this->faker = Factory::create();
        $this->hasher = $hasher;
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager): void
    {
        // load users
        $admin = new User();
        $admin->setEmail('admin@example.com')
            ->setFirstName('SYSTEM')
            ->setLastName('ADMIN')
            ->setTitre('SYSADMIN')
            ->setRoles(['ROLE_ADMIN']);
        $hashedPassword = $this->hasher->hashPassword($admin, '0000');
        $admin->setPassword($hashedPassword);
        $manager->persist($admin);

        $user = new User();
        $user->setFirstName('JONATHAN');
        $user->setLastName('FRANZEN');
        $user->setTitre('CEO and Founder');
        $user->setEmail('user@example.com');
        $hashedPassword = $this->hasher->hashPassword($user, '0000');
        $user->setPassword($hashedPassword);
        $manager->persist($user);

        // load categories
        $categoryTitres= ['Sports', 'Business' , 'Travel', 'Science', 'Fashion'];
        for ($i = 1; $i <= self::CATEGORIES; $i++) 
        {
            $category = new Category();
            $categoryTitre = $categoryTitres[$i-1];
            $category
            ->setTitre($categoryTitre)
            ->setSlug((string)$this->slugger->slug((string)$categoryTitre));
            $manager->persist($category);
            
            // load articles
            for ($j = 1; $j <= rand(1,self::ARTICLES -1) ; $j++)
            {
                $visible = (($j % 2 == 0) ? true : false);
                $publisher = (($j % 2 == 0) ? $user : $admin);
                
                $article = new Article();
                $articleTitre = $this->faker->sentence(\rand(4, 7));
                $article
                ->setTitre($articleTitre)
                ->setSlug((string)$this->slugger->slug((string)$articleTitre))
                ->setIntro($this->faker->paragraph(2, false))
                ->setContent(
                    \sprintf(
                    '<p>%s</p>',
                    \implode('</p><p>', $this->faker->paragraphs(\rand(3, 5)))
                     )
                )
                ->setVisible($visible)
                ->setPublisher($publisher)
                ->setCreatedAt(new DateTimeImmutable())
                ->setCover('https://picsum.photos/200/300?random='.\rand(1, 100))
                ->setCategory($category);
                $manager->persist($article);

            }
        }



        $manager->flush();
    }
}