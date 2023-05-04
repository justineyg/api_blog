<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    #[Route('/article', name: 'app_article')]
    public function index(): Response
    {
        return $this->render('article/index.html.twig', [
            'controller_name' => 'ArticleController',
        ]);
    }

    #[Route('article/{id}', name: 'one_article', methods:['GET'])]
    public function get($id,EntityManagerInterface $em): Response
    {
        $article = $em->getRepository(Article::class)->findOneById($id);
        $category = $em->getRepository(Category::class)->findOneById($id);
        if($article == null){
            return new JsonResponse('Article introuvable', 404);
        }

        return new JsonResponse([
        $article,
        $category,
    ], 200
    );
    }

}
