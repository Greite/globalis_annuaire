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
        $fonction = new Fonction();
        $fonction->setTitle($request->get('libelle'));
        $manager->persist($fonction);
        $manager->flush();
        return $this->render('success.html.twig');
    }

    //Formulaire de création de la fonction
    public function formCreateFonction () {
        return $this->render('createFonction.html.twig');
    }

    //Liste des fonctions
    public function listFonction () {
        $manager = $this->get('doctrine.orm.entity_manager');
        $repository = $manager->getRepository('App:Fonction');
        $fonctions = $repository->findAll();
        return $this->render('listFonction.html.twig', array('fonctions' => $fonctions));
    }
}