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

    #[Route('/blog', name: 'blog')]
    public function blog(): Response
    {
	    return $this->render('blog.html.twig', []);
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

            $entityManager = $doctrine->getManager();    
            $entityManager->persist($post);
            $entityManager->flush();
            return $this->render('new_post.html.twig', array(
                'form' => $form->createView()    
            ));
        }
        return $this->render('new_post.html.twig', array(
            'form' => $form->createView()    
        ));
    }
}