<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact.index')]
    public function index(
        Request $request,
        EntityManagerInterface $manager,
        MailService $mailService
        ): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);

        $user = $this->getUser();
        if ($user){
            $contact->setFullName($user->getFullName())
            ->setEmail($user->getEmail());

        }

        //if ($request->query->get('env') == 'test'){
            $form->remove('captcha');
        //}

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $contact = $form->getData();
            $manager->persist($contact);
            $manager->flush();

            $mailService->sendEmail(
                $contact->getEmail(),
                $contact->getSubject(),
                'emails/contact.html.twig',
                ['contact' => $contact]
            );


            $this->addFlash(
                'success',
                'Votre demande a bien été envoyée !'
            );
    
            return $this->redirectToRoute('contact.index');
        }

        return $this->render('pages/contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
