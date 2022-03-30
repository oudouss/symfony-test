<?php

namespace App\Controller;

use DateTimeImmutable;
use App\Entity\Article;
use App\Entity\Comment;
use App\Service\Mailer;
use App\Entity\Category;
use App\Form\CommentFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/", name="app_home")
     * @return Response
     */
    public function index(): Response
    {
        $repo = $this->em->getRepository(Article::class);
        $categories = $this->em->getRepository(Category::class)->findHome(2);
        $homeCategories = [];
        foreach($categories as $category)
        {
            $homeCategories[] = [
                'titre' => $category->getTitre(),
                'slug' => $category->getSlug(),
                'articles' => $category->getVisibleArticles(),
            ];
        }
        return $this->render('home/index.html.twig',[
            'articles' => $repo->findVisible(6),
            'trendingArticles' => $repo->findTrending(4),
            'popularArticles' => $repo->findPopular(6),
            'homeCategories' => $homeCategories,
        ]);
    }

    /**
    * @Route("/blog/{slug}", name="app_article")
    *
    * @param Request $request
    * @param Article $article
    * @param RequestStack $requestStack
    * @param Mailer $mailer
    * @return Response
    */
    public function showArticle( Request $request, Article $article, RequestStack $requestStack, Mailer $mailer ): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);
        $session = $requestStack->getSession();
        $user = ( $session->has('username') && $session->has('usermail') ) ? true : false;

        if ($form->isSubmitted() && $form->isValid()) 
        { 
            $comment->setArticle($article)->setCreatedAt(new DateTimeImmutable());
            if ( $user )
            {
                if ($session->get('username') !=null && $session->get('usermail') !=null ) {
                    $comment->setUserName($session->get('username'))->setUserEmail($session->get('usermail'));
                }
            }else{
                $session->set('username', $form->getData()->getUserName());
                $session->set('usermail', $form->getData()->getUserEmail());
            }
            
            $this->em->persist($comment);
            $mail=$comment->getUserEmail();
            $message=$comment->getMessage();
            $titre=$article->getTitre();
            $this->em->flush();
            try {
                $mailer->notifyAdmin($mail, $message, $titre);
                $this->addFlash('success', 'Success: Thank you for your feedback! Your comment will be visible once approuved.');
            } catch (\Throwable $ex) {
                $this->addFlash('danger', 'Error: Something went wrong!');
            } finally {
                return $this->redirectToRoute('app_article', ['slug' => $article->getSlug()]);
            }
        }
        return $this->render('home/single.html.twig',[
            'article' => $article,
            'relatedArticles' => $article->getCategory()->getVisibleArticles(),
            'comments' => $this->em->getRepository(Comment::class)->findBy([
                'article'=>$article,
                'visible'=>true,
            ]),
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    /**
     * @Route("/categories", name="app_categories")
     *
     * @return Response
    */
    public function categories(): Response
    {
        return $this->render('categories/list.html.twig', [
            'categories' => $this->em->getRepository(Category::class)->findAll(),
        ]);
    }
    /**
     * @Route("/Category/{slug}", name="app_category")
     * @return Response
    */
    public function showCategory(Category $category): Response
    {
        return $this->render('categories/single.html.twig',[
            'category' => $category,
        ]);
    }
}
