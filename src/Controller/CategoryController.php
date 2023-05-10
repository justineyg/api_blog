<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

    #[Route('/category/{id}', name:'category_update', methods:['PATCH'])]
    public function update(Category $category = null, Request $r, ValidatorInterface $v, EntityManagerInterface $em) : Response
    {
        if($category === null){
            return new JsonResponse('Catégorie introuvable', 404); // Retourne un status 404 car le 204 ne retourne pas de message
        }

        $params = 0;
        // On regarde si l'attribut name reçu n'est pas null
        if($r->get('title') != null){
            $params++;
            // On attribue à la category le nouveau name
            $category->setTitle($r->get('title'));
        }

        if($params > 0){
            $errors = $v->validate($category); // Vérifie que l'objet soit conforme avec les validations (assert)
            if(count($errors) > 0){
                // S'il y a au moins une erreur
                $e_list = [];
                foreach($errors as $e){ // On parcours toutes les erreurs
                    $e_list[] = $e->getMessage(); // On ajoute leur message dans le tableau de messages
                }

                return new JsonResponse($e_list, 400); // On retourne le tableau de messages
            }

            // Si tout va bien, on sauvegarde
            $em->persist($category);
            $em->flush();
        }
        else{
            return new JsonResponse('Empty', 200);
        }

        return new JsonResponse('Success', 200);
    }

    #[Route('/category/{id}', name:'category_delete', methods:['DELETE'])]
    public function delete(Category $category = null, EntityManagerInterface $em): Response
    {
        if($category == null){
            return new JsonResponse('Catégorie introuvable', 404);
        }

        $em->remove($category);
        $em->flush();

        return new JsonResponse('Catégorie supprimée', 204);
    }


}
