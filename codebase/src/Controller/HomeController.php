<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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

        $articles = $repository->findAll();
        return $this->render('home/index.html.twig',[
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/blog/{slug}", name="app_article")
     * @param Request $request
     * @return Response
     */
    public function showArticle(Request $request, Article $article): Response
    {
        return $this->render('home/single.html.twig',[
            'article' => $article,
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
