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
        $contact->setCivilite($request->get('civilite'));
        $contact->setNom($request->get('nom'));
        $contact->setPrenom($request->get('prenom'));
        $contact->setTelephone(intval($request->get('phone')));
        $contact->setBirthdate(new \DateTime($request->get('birthdate')));
        $contact->setMobile(intval($request->get('mobile')));
        $contact->setEmail($request->get('email'));
        $contact->setCommentaire($request->get('commentaire'));

        $societe = new Societe();
        $societe->setDecideur($request->get('decideur'));
        $societe->setName($request->get('societe'));
        $fonction = $fonctionRepository->findOneById($request->get('fonction'));
        $societe->setFonction($fonction);
        $societe->setEntersociete(new \DateTime($request->get('societedate')));
        $societe->setEtranger($request->get('etranger'));
        $societe->setPays($request->get('pays'));
        $societe->setAddress1($request->get('address1'));
        $societe->setAddress2($request->get('address2'));
        $societe->setAddress3($request->get('address3'));
        $societe->setAddress4($request->get('address4'));
        $societe->setPostal(intval($request->get('postal')));
        $societe->setVille($request->get('city'));

        $file = $request->files->get('file');
        $filename = $file->getFilename().'.'.$file->guessExtension();
        $file->move('uploads/images', $filename);
        $image = new PieceJointe();
        $image->setCaption($file->getClientOriginalName());
        $image->setContentUrl('uploads/images/'.$filename);

        $contact->setImage($image);
        $contact->setSociete($societe);
        $manager->persist($societe);
        $manager->persist($contact);
        $manager->flush();
        return $this->redirectToRoute('listAnnuaire');
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

    //Formulaire de création contact
    public function formCreateContact () {
        $manager = $this->get('doctrine.orm.entity_manager');
        $repository = $manager->getRepository('App:Fonction');
        $fonctions = $repository->findByArchived(false);
        return $this->render('createContact.html.twig', array('fonctions' => $fonctions));
    }
}