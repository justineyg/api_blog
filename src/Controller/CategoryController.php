<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    #[Route('/categories', name: 'app_category')]
    public function index(EntityManagerInterface $em): Response
    {
        $categories = $em->getRepository(Category::class)->findLastThree();

        return new JsonResponse($categories);
    }

    #[Route('/category/{id}', name:'one_category', methods:['GET'])]
    public function get($id, EntityManagerInterface $em): Response
    {
        $category = $em->getRepository(Category::class)->findOneById($id);

        $article = $em->getRepository(Article::class)->findById($id);
        
        if($category == null){
            return new JsonResponse('Catégorie introuvable', 404);
        }
        return new JsonResponse([
            $category,
            $article,
        ], 200
       );
    }


}
