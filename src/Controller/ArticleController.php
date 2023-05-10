<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\User;
use App\Service\Validator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
class ArticleController extends AbstractController
{
   
    // public function index(): Response
    // {
    //     return $this->render('article/index.html.twig', [
    //         'controller_name' => 'ArticleController',
    //     ]);
    // }

    #[Route('article/{id}', name: 'one_article', methods:['GET'])]
    public function get($id,EntityManagerInterface $em): Response
    {
        $article = $em->getRepository(Article::class)->findArticleById($id);
        $comment = $em->getRepository(Comment::class)->findCommentById($id);
        if($article == null){
            return new JsonResponse('Article introuvable', 404);
        }

        return new JsonResponse([
        $article,
        $comment,
    ], 200
    );
    }

    #[Route('/article', name:'article_add', methods:['POST'])]
    public function add( Request $r, EntityManagerInterface $em, ValidatorInterface $v): Response
    {
         // On récupère les infos envoyées en header
         $headers = $r->headers->all();
         // Si la clé 'token' existe et qu'elle n'est pas vide dans le header
         if(isset($headers['token']) && !empty($headers['token'])){
             $jwt = current($headers['token']); // Récupère la cellule 0 avec current()
             $key = $this->getParameter('jwt_secret');
 
             // On essaie de décoder le jwt
             try{
                 $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
             }
             // Si la signature n'est pas vérifiée ou que la date d'expiration est passée, il entrera dans le catch
             catch(\Exception $e){
                 return new JsonResponse($e->getMessage(), 403);
             }
 
             // On regarde si la clé 'roles' existe et si l'utilisateur possède le bon rôle
             if($decoded->roles != null  && in_array('ROLE_ADMIN', $decoded->roles)){
                //Pour récupérer l'auteur et l'id du user
                $author = $em->getRepository((User::class))->findOneById($r->get('author'));
                $category = $em->getRepository((Category::class))->findOneById($r->get('category'));
                //on créé un nouveau commentaire
                $article = new Article();
                $article->setTitle($r->get('title'));
                $article->setContent($r->get('content')); // Récupère le paramètre 'content' de la requête et l'assigne à l'objet
                $article->setAuthor($author);
                $article->setReleaseDate(new \DateTime());
                $article->setCreateAt(new \DateTime());
                $article->setStatus($r->get('status'));
                $article->setCategory($category);

                 $errors = $v->validate($article); // Vérifie que l'objet soit conforme avec les validations (assert)
                 if(count($errors) > 0){
                     // S'il y a au moins une erreur
                     $e_list = [];
                     foreach($errors as $e){ // On parcours toutes les erreurs
                         $e_list[] = $e->getMessage(); // On ajoute leur message dans le tableau de messages
                     }
 
                     return new JsonResponse($e_list, 400); // On retourne le tableau de messages
                 }
 
                 $em->persist($article);
                 $em->flush();
 
                 return new JsonResponse('success', 201);
                }
            
            }
            return new JsonResponse('Access denied', 403);
    }

    #[Route('/article/{id}', name:'article_delete', methods:['DELETE'])]
    public function delete(Article $article = null, EntityManagerInterface $em): Response
    {
        if($article == null){
            return new JsonResponse('Article introuvable', 404);
        }

        $em->remove($article);
        $em->flush();

        return new JsonResponse('Article supprimée', 204);
    }

}
