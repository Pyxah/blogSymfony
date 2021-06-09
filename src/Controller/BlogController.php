<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Commentaire;
use App\Entity\User;
use App\Form\ArticleType;
use App\Form\CommentaireFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BlogController extends AbstractController
{
    public function index()
    {
        $articles = $this->getDoctrine()->getRepository(Article::class)->findBy(
            ['isPublished' => true],
            ['date' => 'desc']
        );

        return $this->render('blog/index.html.twig', [
            'articles' => $articles
        ]);
    }

    public function add(Request $request)
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setLastUpdateDate(new \DateTime());
            $article->setUser($user);

            if ($article->getPicture() !== null) {
                $file = $form->get('picture')->getData();
                $fileName = uniqid() . '.' . $file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('images_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }

                $article->setPicture($fileName);
            }

            if ($article->getIsPublished()) {
                $article->setDate(new \DateTime());
            }

            $em = $this->getDoctrine()->getManager(); // On récupère l'entity manager
            $em->persist($article); // On confie notre entité à l'entity manager (on persist l'entité)
            $em->flush(); // On execute la requete

            return new Response('L\'article a bien été enregistré.');
        }

        return $this->render('blog/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function show(Article $article)
    {
        $commentaire = new Commentaire();
        $formCommentaire = $this->createForm(CommentaireFormType::class, $commentaire);
        $user = $this->getUser();

        return $this->render('blog/show.html.twig', [
            'article' => $article,
            'commentaires' => $commentaire,
            'user' => $user,
            'formCommentaire' => $formCommentaire->createView()
        ]);


    }

    public function edit(Article $article, Request $request)
    {
        $oldPicture = $article->getPicture();

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setLastUpdateDate(new \DateTime());

            if ($article->getIsPublished()) {
                $article->setDate(new \DateTime());
            }

            if ($article->getPicture() !== null && $article->getPicture() !== $oldPicture) {
                $file = $form->get('picture')->getData();
                $fileName = uniqid() . '.' . $file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('images_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }

                $article->setPicture($fileName);
            } else {
                $article->setPicture($oldPicture);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();

            return new Response('L\'article a bien été modifié.');
        }

        return $this->render('blog/edit.html.twig', [
            'article' => $article,
            'form' => $form->createView()
        ]);
    }

    public function remove($id)
    {
        return new Response('<h1>Supprimer l\'article : ' . $id . '</h1>');
    }


    public function admin()
    {
        $articles = $this->getDoctrine()->getRepository(Article::class)->findBy(
            [],
            ['lastUpdateDate' => 'DESC']
        );

        $users = $this->getDoctrine()->getRepository(User::class)->findAll();

        $commentaires = $this->getDoctrine()->getRepository(Commentaire::class)->findAll();

        return $this->render('admin/index.html.twig', [
            'articles' => $articles,
            'commentaires' => $commentaires,
            'users' => $users
        ]);
    }

    public function profil()
    {
        $articles = $this->getDoctrine()->getRepository(Article::class)->findBy(
            [],
            ['lastUpdateDate' => 'DESC']
        );

        $commentaires = $this->getDoctrine()->getRepository(Commentaire::class)->findAll();

        $users = $this->getDoctrine()->getRepository(User::class)->findAll();

        return $this->render('profil/profil.html.twig', [
            'articles' => $articles,
            'commentaires' => $commentaires,
            'users' => $users
        ]);
    }

    public function addCommentaire(Request $request)
    {
        $commentaire = new Commentaire();
        $formCommentaire = $this->createForm(CommentaireFormType::class, $commentaire);

        $formCommentaire->handleRequest($request);

        $user = $this->getUser();

        if ($formCommentaire->isSubmitted() && $formCommentaire->isValid()) {
            $commentaire->setDate(new \DateTime());
            $commentaire->setUser($user);
        }

        $em = $this->getDoctrine()->getManager(); // On récupère l'entity manager
        $em->persist($commentaire); // On confie notre entité à l'entity manager (on persist l'entité)
        $em->flush(); // On execute la requete

        return new Response('Le commentaire a bien été envoyé.');
    }

}

