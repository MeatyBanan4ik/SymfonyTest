<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostFormType;
use App\Repository\PostRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    /**
     * @Route("/", methods="GET", name="homepage")
     */
    public function index(Request $request, PaginatorInterface $paginator, EntityManagerInterface $entityManager): Response
    {

        $posts = $paginator->paginate(
            $entityManager->getRepository(Post::class)->createQueryBuilder('p')->orderBy('p.createdAt', 'DESC')->getQuery(),
            $request->query->getInt('page', 1)
        );

        return $this->render('post/index.html.twig', compact('posts'));
    }



    /**
     * @Route("/post/{id}/edit", methods="GET|POST", name="post_edit", requirements={"id"="\d+"})
     */
    public function postEdit(Request $request, int $id): Response
    {

        if(!$this->isGranted('IS_AUTHENTICATED_REMEMBERED'))
        {
            return $this->redirectToRoute('homepage');
        }
        $entityManager = $this->getDoctrine()->getManager();
        $post = $entityManager->getRepository(Post::class)->find($id);
        if($this->getUser() != $post->getAuthor())
        {
            return $this->redirectToRoute('homepage');
        }
        $form = $this->createForm(PostFormType::class, $post);
        $old_image = $post->getImage();
        $form->handleRequest($request);
        if($form->isSubmitted() and $form->isValid())
        {
            $image = $form->get('image')->getData();
            if($image)
            {
                $post->setImage('assets/image/'.$this->saveFile($image));
            }
            else
            {
                $post->setImage($old_image);
            }
            $entityManager->persist($post);
            $entityManager->flush();
            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
        }
        return $this->render('post/edit.html.twig', ['form' => $form->createView(), 'post' => $post]);
    }

    /**
     * @Route("/post/create", methods="GET|POST", name="post_add")
     */
    public function postAdd(Request $request): Response
    {
        if(!$this->isGranted('IS_AUTHENTICATED_REMEMBERED'))
        {
            return $this->redirectToRoute('homepage');
        }
        $entityManager = $this->getDoctrine()->getManager();
        $post = new Post();
        $form = $this->createForm(PostFormType::class, $post);
        $form->handleRequest($request);
        if($form->isSubmitted() and $form->isValid())
        {
            $image = $form->get('image')->getData();
            $post->setImage($image ? 'assets/image/'.$this->saveFile($image) : null);
            $post->setAuthor($this->getUser());
            $entityManager->persist($post);
            $entityManager->flush();
            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
        }
        return $this->render('post/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/post/{id}", methods="GET", name="post_show", requirements={"id" = "\d+"})
     */
    public function postShow(Request $request, int $id = null): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $post = $entityManager->getRepository(Post::class)->find($id);
        return $this->render('post/show.html.twig', compact('post'));
    }

    private function saveFile(UploadedFile $file)
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $saveFilename = md5($originalFilename . (new DateTime())->getTimestamp());
        $filename = sprintf('%s-%s.%s', $saveFilename, uniqid(), $file->guessExtension());
        $filesystem = new Filesystem();
        $path = $this->getParameter('image_directory');
        if(!$path)
        {
            $filesystem->mkdir($path);
        }

        try
        {
            $file->move($path, $filename);
        }
        catch (\Exception $exception)
        {
            return null;
        }
        return $filename;


    }
}
