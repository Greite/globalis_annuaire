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
    }

    //Formulaire de création de la fonction
    public function formCreateFonction () {
        return $this->render('createFonction.html.twig');
    }

    //Liste des fonctions
    public function listFonction (request $request) {
        $manager = $this->get('doctrine.orm.entity_manager');
        $repository = $manager->getRepository('App:Fonction');
        if ($request->request->all() != []) {
            $archived = $request->get('archived');
            $title = $request->get('libelle');
            $qb = $repository->createQueryBuilder('f');
            if (!$archived) {
                $qb->andWhere('f.archived = 0');
            }
            if ($title != '') {
                $qb->andWhere('f.title LIKE :title');
                $qb->setParameter('title', '%'.$title.'%');
            }
            $query = $qb->getQuery();
            $fonctions = $query->getResult();
        }else{
            $fonctions = $repository->findAll();
        }
        return $this->render('listFonction.html.twig', array('fonctions' => $fonctions));
    }

    //Archive une fonction ou l'inverse
    public function archiveFonction ($id) {
        $manager = $this->get('doctrine.orm.entity_manager');
        $repository = $manager->getRepository('App:Fonction');
        $fonction = $repository->findOneById($id);
        $fonction->setArchived(!$fonction->getArchived());
        $manager->persist($fonction);
        $manager->flush();
        return $this->redirectToRoute('listFonction');
    }
}