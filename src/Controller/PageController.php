<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Contact;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ContactFormType;

final class PageController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
	    return $this->render('index.html.twig', []);
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
}