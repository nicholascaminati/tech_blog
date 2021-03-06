<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ArticleController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(PostRepository $postRepository)
    {
        $posts = $postRepository->findAll();
        return $this->render('article/index.html.twig', [
            'posts' => $posts
        ]);
    }

    /**
     * @Route("/post/{id}", name="post.show",methods={"GET"})
     */
    public function show($id, PostRepository $postRepository)
    {
        $post = $postRepository->find($id);
        return $this->render('article/show.html.twig', 
        ['post' => $post]);
    }
    /**
     * @Route("/post/create/article", name="post.create")
     * @param Request $request
     */
    public function create(CategoryRepository $categoryRepository)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $categories = $categoryRepository->findAll();
        return $this->render('article/create.html.twig', [
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/post/store", name="post.store", methods={"POST"})
     * @param Request $request
     */
    public function store(Request $request, ValidatorInterface $validator, CategoryRepository $categoryRepository, UserRepository $userRepository)
    {

        $post = new Post();

        $post->setTitle($request->get('title'));
        $post->setBody($request->get('body'));
        $user = $this->getUser();

        $post->setUser($userRepository->find($user->getId()));
        $post->setCategory($categoryRepository->find($request->get('category')));



        if ($request->files->get('imagePost')) {
            $fileUploader  = new FileUploader($this->getParameter('post_directory'));
            $fileUploader = $fileUploader->upload($request->files->get('imagePost'));
            $post->setImage($fileUploader);
        }
        $errors = $validator->validate($post);
        if (count($errors) > 0) {
            /*
             * Uses a __toString method on the $errors variable which is a
             * ConstraintViolationList object. This gives us a nice string
             * for debugging.
             */
            $errorsString = (string) $errors;

            return new Response($errorsString);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($post);
        $em->flush();
        return  $this->redirect($this->generateUrl('home'));
    }
    /**
     * @Route("/export/csv", name="export-csv")
     */
   /* public function exportCSV()
    {
        $arrayProva = ['ciao', 'miao', 'bao', 'not defined', 'rosso'];
    
        $fp = fopen('php://temp', 'w');
        foreach ($arrayProva as $fields) {
            fputcsv($fp, explode(",", $fields));
        }

        rewind($fp);
        $response = new Response(stream_get_contents($fp));
        fclose($fp);

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="barcode.csv"');

        return $response;
    }*/

/**
     * @Route("/chart", name="chart", methods={"get"})
     * @param Request $request
     */
    public function chartJs(){
        return $this->render('chart/index.html.twig');
    }
}
