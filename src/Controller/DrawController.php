<?php

namespace App\Controller;


use App\Entity\Draw;
use App\Entity\Participant;
use App\Form\DrawType;
use App\Repository\DrawRepository;
use App\Repository\ParticipantRepository;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

class DrawController extends AbstractController
{

    /**
     * @var DrawRepository
     */
    private $repository;
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(DrawRepository $repository,ParticipantRepository $repoParti, EntityManagerInterface $em)
    {
        $this->repository = $repository;
        $this->repoParti = $repoParti;

        $this->em = $em;
    }

    /**
     * @Route("/draws/", name="draws")
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param $draws
     * @return Response
     */
    public function list(): Response
    {
        // get list of participants for the user current
        $partipants = $this->repoParti->findBy(
            ['user' => $this->getUser()]
        );

        $listDraw = array();

        foreach ($partipants as $part){
            $listDraw = $part->getDraw()->getId();
        }
        dump("test");

        dump($listDraw);

        //
        $draws = $this->repository->findBy(
            ['id' => array($listDraw)]);

        return $this->render('draw/list.html.twig', [
            'controller_name' => 'DrawController',
            'participants' => $partipants,
            'draws' => $draws
        ]);
    }

    /**
     * @Route("/draw/{id}/", name="draw.id")
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param $draws
     * @return Response
     */
    public function displayDraw(int $id): Response
        {
        $draw = $this->repository->findOneBy(
            ['id' => $id ]
        );

        $partipants = $this->repoParti->findBy(
            ['draw' => $draw]
        );

        dump($partipants);



            return $this->render('draw/info.html.twig', [
                'controller_name' => 'DrawController',
                'draw' => $draw,
                'participants' => $partipants
        ]);
    }

    /**
     * @Route("/draw", name="draw.new")
     * @param EntityManagerInterface $em
     * @param Draw $draw
     * @param Request $request
     * @return Response
     */
    public function new(EntityManagerInterface $em,Request $request): Response
    {

        $draw = new Draw();
        $form = $this->createForm(DrawType::class,$draw);
        $form->handleRequest($request);
        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()){
            $this->em->persist($draw);
            $this->em->flush();


            // add new Participant
            $participant = new Participant();
            $participant->setOwner(true);
            $participant->setSubscribed(true);
            $participant->setUser($user);
            $participant->setDraw($draw);
            $em->persist($participant);
            $em->flush();
            return $this->redirectToRoute('home');
        }

        return $this->render('draw/index.html.twig', [
            'controller_name' => 'DrawController',
            'form' => $form->createView()
        ]);
    }
}
