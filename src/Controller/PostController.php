<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Post;
use App\Repository\PostRepository;
use App\Form\PostFormType;
use App\Services\FileUploader;

/**
 * @Route("/post", name="post.")
 */
class PostController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(PostRepository $postRepository)
    {
        $posts = $postRepository->findAll();
        return $this->render('post/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    /**
     * @Route("/create", name="create")
     */
    public function create(Request $request, FileUploader $fileUploader)
    {
        $post = new Post();
        $form = $this->createForm(PostFormType::class, $post);
        $form->handleRequest($request);

        //$form->getErrors();
        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $file = $request->files->get('post_form')['image'];
            dump($file);
            if ($file) {
                $filename = $fileUploader->uploadFile($file);
                $post->setImage($filename);
            }
            $em->persist($post);
            $em->flush();

            return $this->redirect($this->generateUrl('post.index'));
        }

        return $this->render('post/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/show/{id}", name="show")
     * @param Post $post
     */
    public function show(Post $post) //$id, PostRepository $postRepository 
    {
        // $post = $postRepository->findPostWithCategory($id);
        // dump($post);
        return $this->render('post/show.html.twig', [
            'post' => $post
            ]);
    }

    /**
     * @Route("/delete/{id}", name="delete")
     * @param Post $post
     */
    public function delete(Post $post) 
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();

        $this->addFlash('success', 'Post was removed');
        return $this->redirect($this->generateUrl('post.index'));
    }
}
