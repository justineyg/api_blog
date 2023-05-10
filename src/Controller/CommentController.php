<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\User;
use App\Service\Validator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CommentController extends AbstractController
{
    #[Route('/comment', name: 'app_comment')]
    public function index(): Response
    {
        return $this->render('comment/index.html.twig', [
            'controller_name' => 'CommentController',
        ]);
    }
    #[Route('/comment/{id}', name:'comment_add', methods:['POST'])]
    public function add(Article $article = null, Request $r, EntityManagerInterface $em, ValidatorInterface $v): Response
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
                $author = $em->getRepository((User::class))->findOneById(["id" => 1]);
                $article->setAuthor($author);
                //on créé un nouveau commentaire
                $comment = new Comment();
                $comment->setContent($r->get('content')); // Récupère le paramètre 'content' de la requête et l'assigne à l'objet
                $comment->setAuthor($author);
                $comment->setPublicationDate(new \DateTime());
                $comment->setStatus($r->get('status'));
                $comment->setArticle($article);

                 $errors = $v->validate($comment); // Vérifie que l'objet soit conforme avec les validations (assert)
                 if(count($errors) > 0){
                     // S'il y a au moins une erreur
                     $e_list = [];
                     foreach($errors as $e){ // On parcours toutes les erreurs
                         $e_list[] = $e->getMessage(); // On ajoute leur message dans le tableau de messages
                     }
 
                     return new JsonResponse($e_list, 400); // On retourne le tableau de messages
                 }
 
                 $em->persist($comment);
                 $em->flush();
 
                 return new JsonResponse('success', 201);
                }
            
            }
    }

    #[Route('/comment/{id}', name:'comment_update', methods:['PATCH'])]
    public function update(Comment $comment = null, Request $r, ValidatorInterface $v, EntityManagerInterface $em) : Response
    {
        if($comment === null){
            return new JsonResponse('Commentaire introuvable', 404); // Retourne un status 404 car le 204 ne retourne pas de message
        }
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
       
                $comment->setStatus($r->get('status'));

            $errors = $v->validate($comment); // Vérifie que l'objet soit conforme avec les validations (assert)
            if(count($errors) > 0){
                // S'il y a au moins une erreur
                $e_list = [];
                foreach($errors as $e){ // On parcours toutes les erreurs
                    $e_list[] = $e->getMessage(); // On ajoute leur message dans le tableau de messages
                }

                return new JsonResponse($e_list, 400); // On retourne le tableau de messages
            }

            // Si tout va bien, on sauvegarde
            $em->persist($comment);
            $em->flush();
        }
        else{
            return new JsonResponse('Empty', 200);
        }

        return new JsonResponse('Success', 200);
    }
         }
        
   
}
