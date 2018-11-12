<?php
/**
 * Created by PhpStorm.
 * User: gauthierpainteaux
 * Date: 06/11/2018
 * Time: 15:06
 */

namespace App\Controller;

use App\Entity\Fonction;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class FonctionController extends Controller
{
    //Création de la fonction
    public function createFonction (request $request) {
        $session = $this->get('session');
        $userConnected = $session->get('user');
        if ($userConnected) {
            if ($userConnected['role']['const'] == 'ROLE_CLIENT') {
                return $this->redirectToRoute('listAnnuaire');
            }
        }else {
            return $this->redirectToRoute('connexion');
        }
        if ($this->isCsrfTokenValid('create-fonction', $request->get('token'))) {
            $manager = $this->get('doctrine.orm.entity_manager');
            if ($request->get('libelle')) {
                $fonction = new Fonction();
                $fonction->setTitle($request->get('libelle'));
                $manager->persist($fonction);
                $manager->flush();
                return $this->redirectToRoute('listFonction');
            }else {
                return $this->render('createFonction.html.twig', array('message' => 'Veuillez remplir le champ'));
            }
        }else {
            return $this->redirectToRoute('formCreateFonction');
        }
    }

    //Formulaire de création de la fonction
    public function formCreateFonction () {
        $session = $this->get('session');
        $userConnected = $session->get('user');
        if ($userConnected) {
            if ($userConnected['role']['const'] == 'ROLE_CLIENT') {
                return $this->redirectToRoute('listAnnuaire');
            }
        }else {
            return $this->redirectToRoute('connexion');
        }
        return $this->render('createFonction.html.twig');
    }

    //Liste des fonctions
    public function listFonction (request $request) {
        $session = $this->get('session');
        $userConnected = $session->get('user');
        if ($userConnected) {
            if ($userConnected['role']['const'] == 'ROLE_CLIENT') {
                return $this->redirectToRoute('listAnnuaire');
            }
        }else {
            return $this->redirectToRoute('connexion');
        }
        $manager = $this->get('doctrine.orm.entity_manager');
        $repository = $manager->getRepository('App:Fonction');
        $filtres = array();
        if ($request->request->all() != []) {
            $archived = $request->get('archived');
            $title = $request->get('libelle');
            $qb = $repository->createQueryBuilder('f');
            if (!$archived) {
                $qb->andWhere('f.archived = 0');
            }
            $filtres['archived'] = $archived;
            if ($title != '') {
                $qb->andWhere('f.title LIKE :title');
                $qb->setParameter('title', '%'.$title.'%');
                $filtres['title'] = $title;
            }
            $query = $qb->getQuery();
            $fonctions = $query->getResult();
        }else{
            $fonctions = $repository->findAll();
        }
        return $this->render('listFonction.html.twig', array('fonctions' => $fonctions, 'filtres' => $filtres));
    }

    //Archive une fonction ou l'inverse
    public function archiveFonction ($id) {
        $session = $this->get('session');
        $userConnected = $session->get('user');
        if ($userConnected) {
            if ($userConnected['role']['const'] == 'ROLE_CLIENT') {
                return $this->redirectToRoute('listAnnuaire');
            }
        }else {
            return $this->redirectToRoute('connexion');
        }
        $manager = $this->get('doctrine.orm.entity_manager');
        $repository = $manager->getRepository('App:Fonction');
        $fonction = $repository->findOneById($id);
        $fonction->setArchived(!$fonction->getArchived());
        $manager->persist($fonction);
        $manager->flush();
        return $this->redirectToRoute('listFonction');
    }
}