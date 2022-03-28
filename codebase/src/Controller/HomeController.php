<?php

namespace App\Controller;

use DateTimeImmutable;
use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Category;
use App\Form\CommentFormType;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{

    /**
     * @Route("/", name="app_home")
     * @param ArticleRepository $repository
     * @param Request $request
     * @return Response
     */
    public function index(ArticleRepository $repository, Request $request): Response
    {

        $articles = $repository->findVisible();
        return $this->render('home/index.html.twig',[
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/blog/{slug}", name="app_article")
     * @param Request $request
     * @return Response
     */
    public function showArticle(
            Request $request, 
            Article $article, 
            EntityManagerInterface $em, 
            CommentRepository $commentRepo,
            RequestStack $requestStack
    ): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);
        $session = $requestStack->getSession();
        $user = false;
        if ($session->has('username') && $session->has('usermail'))
        {
            $user = [
                'name' => $session->get('username'),
                'mail' => $session->get('usermail'),
            ];
        }
        if ($form->isSubmitted() && $form->isValid()) 
        { 
            if ( $user )
            {
                $comment
                ->setUserName($user['name'])
                ->setUserEmail($user['mail']);
                
            }else
            {
                $session->set('username', $form->getData()->getUserName());
                $session->set('usermail', $form->getData()->getUserEmail());
            }
            $comment
                ->setArticle($article)
                ->setCreatedAt(new DateTimeImmutable());

            $em->persist($comment);
            $em->flush();
            $this->addFlash('success', 'Comment added successfully. Thank you!');
            return $this->redirectToRoute('app_article', ['slug' => $article->getSlug()]);
        }

        return $this->render('home/single.html.twig',[
            'article' => $article,
            'comments' => $commentRepo->findVisibleByArticle($article),
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    /**
     * @Route("/categories", name="app_categories")
     * @param int $n
     * @return Response
     */

    public function categories(int $n, CategoryRepository $CatRepository): Response
    {
        $output = [];
        $categories = $CatRepository->findAll();
        $count = \count($categories);
        $output[0] = \array_slice($categories, 0, $count / 2);
        $output[1] = \array_slice($categories, ($count / 2));

        return $this->render('categories/list.html.twig', [
            'categories' => $output,
        ]);
    }
    /**
     * @Route("/Category/{slug}", name="app_category")
     * @param Request $request
     * @return Response
     */

    public function showCategory(Request $request, Category $category): Response
    {
        return $this->render('categories/single.html.twig',[
            'category' => $category,
        ]);
    }
}
