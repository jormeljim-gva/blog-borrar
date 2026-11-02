<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Category;
use App\Entity\Contact;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ContactFormType;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Form\PostFormType;
use App\Entity\Post;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\PostRepository;
use App\Entity\Comment;
use App\Form\CommentFormType;


final class PageController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ManagerRegistry $doctrine, Request $request): Response
    {
        $repository = $doctrine->getRepository(Category::class);

        $categories = $repository->findAll();

        return $this->render('index.html.twig', ['categories' => $categories]);
    }

    #[Route('/about', name: 'about')]
    public function about(): Response
    {
	    return $this->render('about.html.twig', []);
    }

    #[Route('/blog/{page}', name: 'blog', requirements: ['page' => '\d+'])]
    public function blog(ManagerRegistry $doctrine, int $page = 1): Response
    {
        $repository = $doctrine->getRepository(Post::class);
        $posts = $repository->findAll($page);
        $recents = $repository->findRecents();

        return $this->render('blog.html.twig', [
            'posts' => $posts,
            'recents' => $recents
        ]);
    }

    #[Route('/contacto', name: 'contacto')]
    public function contacto(): Response
    {
	    return $this->render('contacto.html.twig', []);
    }

    #[Route('/contact', name: 'contact')]
    public function contact(ManagerRegistry $doctrine, Request $request): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactFormType::class, $contact);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $contacto = $form->getData();    
            $entityManager = $doctrine->getManager();    
            $entityManager->persist($contacto);
            $entityManager->flush();
            return $this->redirectToRoute('thankyou', []);
        }
        return $this->render('contacto.html.twig', array(
            'form' => $form->createView()    
        ));
    }

    #[Route('/thankyou', name: 'thankyou')]
    public function thankyou(): Response
    {
	    return $this->render('thankyou.html.twig', []);
    }

    #[Route('/blog/new', name: 'new_post')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function newPost(ManagerRegistry $doctrine, Request $request, SluggerInterface $slugger): Response
    {
        $post = new Post();
        $form = $this->createForm(PostFormType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $post = $form->getData();   
            
            // Quitamos los caracteres especiales del título para crear el slug
            $post->setSlug($slugger->slug($post->getTitle()));
            
            // Guardamos el usuario que crea el post y los contadores a 0
            $post->setPostUser($this->getUser());
            $post->setNumLikes(0);
            $post->setNumComments(0);
            //Gestiona la imagen
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move(
                    $this->getParameter('kernel.project_dir') . '/public/images', $newFilename
                );
                $post->setImage($newFilename);
            }
            $entityManager = $doctrine->getManager();    
            $entityManager->persist($post);
            $entityManager->flush();
            return $this->redirectToRoute('single_post', ["slug" => $post->getSlug()]);
        }
        return $this->render('new_post.html.twig', array(
            'form' => $form->createView()    
        ));
        
    }

   #[Route('/single_post/{slug}', name: 'single_post')]
    public function post(ManagerRegistry $doctrine, Request $request, $slug): Response
    {
        $repository = $doctrine->getRepository(Post::class);
        $post = $repository->findOneBy(["slug"=>$slug]);
        $recents = $repository->findRecents();
        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment = $form->getData();
            $comment->setPost($post);  
            //Aumentamos en 1 el número de comentarios del post
            $post->setNumComments($post->getNumComments() + 1);
            $entityManager = $doctrine->getManager();    
            $entityManager->persist($comment);
            $entityManager->flush();
            return $this->redirectToRoute('single_post', ["slug" => $post->getSlug()]);
        }
        return $this->render('single_post.html.twig', [
            'post' => $post,
            'recents' => $recents,
            'commentForm' => $form->createView()
        ]);
    }

  

    #[Route('/blog/recent_posts', name: 'recent_posts')]
    public function recentPosts(ManagerRegistry $doctrine): Response{
        $repository = $doctrine->getRepository(Post::class);
        $recents = $repository->findRecents();
        return $this->render('partials/_recent_post.html.twig', [
            'recents' => $recents
        ]);
    }
}