<?php
/**
 * Created by PhpStorm.
 * User: gauthierpainteaux
 * Date: 07/11/2018
 * Time: 15:41
 */

namespace App\Controller;

use App\Entity\Contact;
use App\Entity\Societe;
use App\Entity\PieceJointe;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


class AnnuaireController extends Controller
{
    //Création d'un contact
    public function createContact (request $request) {
        $session = $this->get('session');
        $userConnected = $session->get('user');
        if ($userConnected) {
            if ($userConnected['role']['const'] == 'ROLE_CLIENT') {
                return $this->redirectToRoute('listAnnuaire');
            }
        }else {
            return $this->redirectToRoute('connexion');
        }
        if ($this->isCsrfTokenValid('create-contact', $request->get('token'))) {
            $manager = $this->get('doctrine.orm.entity_manager');
            $fonctionRepository = $manager->getRepository('App:Fonction');
            $contact = new Contact();
            $message = "Merci de remplir le(s) champ(s) suivant(s) : <br>";
            if ($request->get('civilite') != '') {
                $contact->setCivilite($request->get('civilite'));
            }else {
                $message .= "- Civilité<br>";
            }
            if ($request->get('nom') != '') {
                $contact->setNom($request->get('nom'));
            }else {
                $message .= "- Nom<br>";
            }
            if ($request->get('prenom') != '') {
                $contact->setPrenom($request->get('prenom'));
            }else {
                $message .= "- Prenom<br>";
            }
            $contact->setTelephone($request->get('phone'));
            $arrayBirthdate = explode('/', $request->get('birthdate'));
            $birthdate = new \DateTime();
            $birthdate->setDate($arrayBirthdate[2], $arrayBirthdate[1], $arrayBirthdate[0]);
            $contact->setBirthdate($birthdate);
            $contact->setMobile($request->get('mobile'));
            $contact->setEmail($request->get('email'));
            $contact->setCommentaire($request->get('commentaire'));

            $societe = new Societe();
            if ($request->get('decideur') != '') {
                $societe->setDecideur($request->get('decideur'));
            }else {
                $message .= "- Decideur<br>";
            }
            if ($request->get('societe') != '') {
                $societe->setName($request->get('societe'));
            }else {
                $message .= "- Societe<br>";
            }
            $fonction = $fonctionRepository->findOneById($request->get('fonction'));
            $societe->setFonction($fonction);
            $arraySocieteDate = explode('/', $request->get('societedate'));
            $societeDate = new \DateTime();
            $societeDate->setDate($arraySocieteDate[2], $arraySocieteDate[1], $arraySocieteDate[0]);
            $societe->setEntersociete($societeDate);
            if ($birthdate > $societeDate) {
                $message .= "- Date de naissance plus récente que la date d'entrée dans la société<br>";
            }
            $societe->setEtranger($request->get('etranger'));
            $societe->setPays($request->get('pays'));
            $societe->setAddress1($request->get('address1'));
            $societe->setAddress2($request->get('address2'));
            if ($request->get('address3') != '') {
                $societe->setAddress3($request->get('address3'));
            }else {
                $message .= "- N° et libelle de voie<br>";
            }
            $societe->setAddress4($request->get('address4'));
            if ($request->get('postal') != '') {
                $societe->setPostal(intval($request->get('postal')));
            }else {
                $message .= "- Code postal<br>";
            }
            if ($request->get('city') != '') {
                $societe->setVille($request->get('city'));
            }else {
                $message .= "- Ville<br>";
            }
            $file = $request->files->get('file');
            if ($file != NULL) {
                $filename = $file->getFilename().'.'.$file->guessExtension();
                $file->move('uploads/images', $filename);
                $image = new PieceJointe();
                $image->setCaption($file->getClientOriginalName());
                $image->setContentUrl('uploads/images/'.$filename);
                $contact->setImage($image);
            }
            $contact->setSociete($societe);
            if ($message != "Merci de remplir le(s) champ(s) suivant(s) : <br>") {
                $filled = array(
                    'civilite' => $request->get('civilite'),
                    'nom' => $request->get('nom'),
                    'prenom' => $request->get('prenom'),
                    'telephone' => $request->get('phone'),
                    'birthdate' => $request->get('birthdate'),
                    'email' => $request->get('email'),
                    'mobile' => $request->get('mobile'),
                    'commentaire' => $request->get('commentaire'),
                    'decideur' => $request->get('decideur'),
                    'societe' => $request->get('societe'),
                    'fonction' => $request->get('fonction'),
                    'entersociete' => $request->get('societedate'),
                    'etranger' => $request->get('etranger'),
                    'pays' => $request->get('pays'),
                    'address1' => $request->get('address1'),
                    'address2' => $request->get('address2'),
                    'address3' => $request->get('address3'),
                    'address4' => $request->get('address4'),
                    'postal' => $request->get('postal'),
                    'city' => $request->get('city'),
                );
                return $this->render('createContact.html.twig', array('fonctions' => $fonctionRepository->findAll(), 'champs' => $message, 'filled' => $filled));
            }else {
                $manager->persist($societe);
                $manager->persist($contact);
                $manager->flush();
                return $this->redirectToRoute('listAnnuaire');
            }
        }else {
            return $this->redirectToRoute('formCreateContact');
        }
    }

    //Archive un contact ou l'inverse
    public function archiveContact ($id) {
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
        $repository = $manager->getRepository('App:Contact');
        $contact = $repository->findOneById($id);
        $contact->setArchived(!$contact->getArchived());
        $manager->persist($contact);
        $manager->flush();
        return $this->redirectToRoute('listAnnuaire');
    }

    //Liste des contacts
    public function listAnnuaire (request $request) {
        $manager = $this->get('doctrine.orm.entity_manager');
        $repository = $manager->getRepository('App:Contact');
        $filtres = array();
        if ($request->query->all() != []) {
            $qb = $repository->createQueryBuilder('c');
            if ($request->query->get('nom') != '') {
                $qb->andWhere('c.nom LIKE :nom');
                $qb->setParameter('nom', '%'.$request->query->get('nom').'%');
                $filtres['nom'] = $request->query->get('nom');
            }
            if ($request->query->get('prenom') != '') {
                $qb->andWhere('c.prenom LIKE :prenom');
                $qb->setParameter('prenom', '%'.$request->query->get('prenom').'%');
                $filtres['prenom'] = $request->query->get('prenom');
            }
            if ($request->query->get('archivedContact') != null) {
                $filtres['archivedContact'] = $request->query->get('archivedContact');
                if (!$request->query->get('archivedContact')) {
                    $qb->andWhere('c.archived = 0');
                }
            }
            if ($request->query->get('phone') != '') {
                $qb->andWhere('c.telephone LIKE :telephone');
                $qb->setParameter('telephone', '%'.$request->get('phone').'%');
                $filtres['phone'] = $request->query->get('phone');
            }
            if ($request->query->get('col') != null && $request->query->get('order') != null){
                $qb->orderBy('c.'.$request->query->get('col'), $request->query->get('order'));
            }
            $query = $qb->getQuery();
            $contacts = $query->getResult();
        }else {
            if ($request->query->get('col') != null && $request->query->get('order') != null){
                $contacts = $repository->findBy(array(), array($request->query->get('col') => $request->query->get('order')));
            }else {
                $contacts = $repository->findAll();
            }
        }
        $count = count($contacts);
        $paginator  = $this->get('knp_paginator');
        $appointments = $paginator->paginate($contacts, $request->query->getInt('page', 1), 10);
        return $this->render('listAnnuaire.html.twig', array('contacts' => $appointments, 'count' => $count, 'filtres' => $filtres));
    }

    //Détails d'un contact
    public function showContact ($id) {
        $manager = $this->get('doctrine.orm.entity_manager');
        $repository = $manager->getRepository('App:Contact');
        $contact = $repository->findOneById($id);
        return $this->render('showContact.html.twig', array('contact' => $contact));
    }

    //Edition d'un contact
    public function editContact (request $request, $id) {
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
        $repository = $manager->getRepository('App:Contact');
        $fonctionRepository = $manager->getRepository('App:Fonction');
        $fonctions = $fonctionRepository->findByArchived(false);
        $contact = $repository->findOneById($id);
        if ($request->request->all() != []) {
            $message = "Merci de remplir le(s) champ(s) suivant(s) : <br>";
            if ($request->get('civilite') != '') {
                $contact->setCivilite($request->get('civilite'));
            }else {
                $message .= "- Civilité<br>";
            }
            if ($request->get('nom') != '') {
                $contact->setNom($request->get('nom'));
            }else {
                $message .= "- Nom<br>";
            }
            if ($request->get('prenom') != '') {
                $contact->setPrenom($request->get('prenom'));
            }else {
                $message .= "- Prenom<br>";
            }
            $contact->setTelephone($request->get('phone'));
            $arrayBirthdate = explode('/', $request->get('birthdate'));
            $birthdate = new \DateTime();
            $birthdate->setDate($arrayBirthdate[2], $arrayBirthdate[1], $arrayBirthdate[0]);
            $contact->setBirthdate($birthdate);
            $contact->setMobile($request->get('mobile'));
            $contact->setEmail($request->get('email'));
            $contact->setCommentaire($request->get('commentaire'));

            $societe = new Societe();
            if ($request->get('decideur') != '') {
                $societe->setDecideur($request->get('decideur'));
            }else {
                $message .= "- Decideur<br>";
            }
            if ($request->get('societe') != '') {
                $societe->setName($request->get('societe'));
            }else {
                $message .= "- Societe<br>";
            }
            $fonction = $fonctionRepository->findOneById($request->get('fonction'));
            $societe->setFonction($fonction);
            $arraySocieteDate = explode('/', $request->get('societedate'));
            $societeDate = new \DateTime();
            $societeDate->setDate($arraySocieteDate[2], $arraySocieteDate[1], $arraySocieteDate[0]);
            $societe->setEntersociete($societeDate);
            if ($birthdate > $societeDate) {
                $message .= "- Date de naissance plus récente que la date d'entrée dans la société<br>";
            }
            $societe->setEtranger($request->get('etranger'));
            $societe->setPays($request->get('pays'));
            $societe->setAddress1($request->get('address1'));
            $societe->setAddress2($request->get('address2'));
            if ($request->get('address3') != '') {
                $societe->setAddress3($request->get('address3'));
            }else {
                $message .= "- N° et libelle de voie<br>";
            }
            $societe->setAddress4($request->get('address4'));
            if ($request->get('postal') != '') {
                $societe->setPostal(intval($request->get('postal')));
            }else {
                $message .= "- Code postal<br>";
            }
            if ($request->get('city') != '') {
                $societe->setVille($request->get('city'));
            }else {
                $message .= "- Ville<br>";
            }
            $file = $request->files->get('file');
            if ($file != NULL) {
                $filename = $file->getFilename().'.'.$file->guessExtension();
                $file->move('uploads/images', $filename);
                $image = new PieceJointe();
                $image->setCaption($file->getClientOriginalName());
                $image->setContentUrl('uploads/images/'.$filename);
                $contact->setImage($image);
            }
            $contact->setSociete($societe);
            if ($message != "Merci de remplir le(s) champ(s) suivant(s) : <br>") {
                return $this->render('editContact.html.twig', array('fonctions' => $fonctions, 'champs' => $message, 'contact' => $contact));
            }else {
                $manager->persist($societe);
                $manager->persist($contact);
                $manager->flush();
                return $this->redirectToRoute('showContact', array('id' => $contact->getId()));
            }
        }else {
            return $this->render('editContact.html.twig', array('contact' => $contact, 'fonctions' => $fonctions));
        }
    }

    //Formulaire de création contact
    public function formCreateContact () {
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
        $fonctions = $repository->findByArchived(false);
        return $this->render('createContact.html.twig', array('fonctions' => $fonctions, 'champs' => NULL, 'filled' => NULL));
    }
}