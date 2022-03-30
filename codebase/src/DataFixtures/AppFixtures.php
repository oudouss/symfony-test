<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use DateTimeImmutable;
use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private const CATEGORIES = 5;
    private const ARTICLES = 20;
    private const COMMENTS = 50;

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
            ->setRoles(['ROLE_ADMIN'])
            ->setAvatar('https://i.pravatar.cc/45');
        $hashedPassword = $this->hasher->hashPassword($admin, '0000');
        $admin->setPassword($hashedPassword);
        $manager->persist($admin);

        $user = new User();
        $user->setFirstName('JONATHAN');
        $user->setLastName('FRANZEN');
        $user->setTitre('CEO and Founder');
        $user->setEmail('user@example.com');
        $user->setAvatar('https://i.pravatar.cc/45');
        $hashedPassword = $this->hasher->hashPassword($user, '0000');
        $user->setPassword($hashedPassword);
        $manager->persist($user);

        // load categories
        $categoryTitres= ['Sports', 'Business' , 'Travel', 'Science', 'Fashion', 'Health'];
        for ($i = 1; $i <= self::CATEGORIES; $i++) 
        {
            $home = (($i % 2 == 0) ? true : false);
            $category = new Category();
            $categoryTitre = $categoryTitres[$i-1];
            $category
            ->setTitre($categoryTitre)
            ->setHome($home)
            ->setSlug((string)$this->slugger->slug(strtolower((string)$categoryTitre)));
            $manager->persist($category);
            
            // load articles
            for ($j = 1; $j <= rand(1,self::ARTICLES -1) ; $j++)
            {
                $visible = (($j % 2 == 0) ? true : false);
                $trending = (($j % 3 == 0) ? true : false);
                $popular = (($j % 3 != 0) ? true : false);
                $publisher = (($j % 2 == 0) ? $user : $admin);
                $rand=\rand(1, 100);
                $article = new Article();
                $articleTitre = $this->faker->sentence(\rand(4, 7));
                $article
                ->setTitre($articleTitre)
                ->setSlug((string)$this->slugger->slug(strtolower((string)$articleTitre)))
                ->setIntro($this->faker->paragraph(2, false))
                ->setContent(
                    \sprintf(
                        '<p>%s</p>',
                        \implode('</p><p>', $this->faker->paragraphs(\rand(5, 15)))
                        )
                        )
                ->setVisible($visible)
                ->setTrending($trending)
                ->setPopular($popular)
                ->setPublisher($publisher)
                ->setCreatedAt(new DateTimeImmutable())
                ->setCover('https://picsum.photos/800/514?random='.$rand)
                ->setCategory($category);
                $manager->persist($article);
                
                // load comments
                for ($k = 1; $k <= rand(1,self::COMMENTS -1) ; $k++)
                {
                    $visiblecomment = (($k % 2 == 0) ? true : false);
                    $comment = new Comment();
                    $comment
                    ->setUserName($this->faker->name())
                    ->setUserEmail($this->faker->email())
                    ->setVisible($visiblecomment)
                    ->setMessage($this->faker->paragraph(2, false))
                    ->setCreatedAt(new DateTimeImmutable())
                    ->setArticle($article)
                    ;
                    $manager->persist($comment);
                }


            }
        }



        $manager->flush();
    }
}
