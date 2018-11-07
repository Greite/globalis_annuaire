<?php
/**
 * Created by PhpStorm.
 * User: gauthierpainteaux
 * Date: 07/11/2018
 * Time: 15:41
 */

namespace App\Controller;

use App\Entity\User;
use App\Entity\Fonction;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AnnuaireController extends Controller
{
    //Liste des contacts
    public function listAnnuaire () {
        $manager = $this->get('doctrine.orm.entity_manager');
        $repository = $manager->getRepository('App:User');
        $users = $repository->findAll();
        return $this->render('listAnnuaire.html.twig', array('users' => $users));
    }

    //Formulaire de création contact
    public function formCreateContact () {
        $manager = $this->get('doctrine.orm.entity_manager');
        $repository = $manager->getRepository('App:Fonction');
        $fonctions = $repository->findAll();
        return $this->render('createContact.html.twig', array('fonctions' => $fonctions));
    }
}