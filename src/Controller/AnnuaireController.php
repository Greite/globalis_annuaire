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
        $contact->setTelephone(intval($request->get('phone')));
        $contact->setBirthdate(new \DateTime($request->get('birthdate')));
        $contact->setMobile(intval($request->get('mobile')));
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
        $societe->setEntersociete(new \DateTime($request->get('societedate')));
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
    }

    //Archive un contact ou l'inverse
    public function archiveContact ($id) {
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
        if ($request->request->all() != []) {
            $qb = $repository->createQueryBuilder('c');
            if ($request->get('nom') != '') {
                $qb->andWhere('c.nom LIKE :nom');
                $qb->setParameter('nom', '%'.$request->get('nom').'%');
            }
            if ($request->get('prenom') != '') {
                $qb->andWhere('c.prenom LIKE :prenom');
                $qb->setParameter('prenom', '%'.$request->get('prenom').'%');
            }
            if (!$request->get('archivedContact')) {
                $qb->andWhere('c.archived = 0');
            }
            if ($request->get('phone') != '') {
                $qb->andWhere('c.telephone LIKE :telephone');
                $qb->setParameter('telephone', '%'.$request->get('phone').'%');
            }
            $query = $qb->getQuery();
            $contacts = $query->getResult();
        }else {
            $contacts = $repository->findAll();
        }
        return $this->render('listAnnuaire.html.twig', array('contacts' => $contacts));
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
            $contact->setTelephone(intval($request->get('phone')));
            $contact->setBirthdate(new \DateTime($request->get('birthdate')));
            $contact->setMobile(intval($request->get('mobile')));
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
            $societe->setEntersociete(new \DateTime($request->get('societedate')));
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
        $manager = $this->get('doctrine.orm.entity_manager');
        $repository = $manager->getRepository('App:Fonction');
        $fonctions = $repository->findByArchived(false);
        return $this->render('createContact.html.twig', array('fonctions' => $fonctions, 'champs' => NULL, 'filled' => NULL));
    }
}