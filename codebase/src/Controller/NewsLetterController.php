<?php

namespace App\Controller;

use App\Entity\NewsLetter;
use App\Form\NewsLetterFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NewsLetterController extends AbstractController
{
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    /**
     * @Route("/subscribe", name="app_news_letter_subscribe")
     */
    public function subscribeToNewsLetter(Request $request): Response
    {
        $repo = $this->em->getRepository(NewsLetter::class);
        $newsLetter = new NewsLetter();
        $newsletterform = $this->createForm(NewsLetterFormType::class, $newsLetter);
        $newsletterform->handleRequest($request);
        if ($newsletterform->isSubmitted()) 
        { 
            $subscribed =  $repo->findOneBy(['email' => $newsletterform->getdata()->getEmail()]);
            if ($subscribed)
            {
                $subscribed->setSubscribed(true);
                $this->em->persist($subscribed);
                $this->em->flush();
            }else{
                $repo->add($newsLetter);
            }
            $this->addFlash('success', 'Success: Thank you for your subscription!');
            $route = $request->headers->get('referer');

            return $this->redirect($route);

        }
        return $this->renderForm('newsletter/_newsletter_form.html.twig',[
            'newsletterform' => $newsletterform
        ]);
    }
}
