<?php
/**
 * Created by PhpStorm.
 * User: gauthierpainteaux
 * Date: 06/11/2018
 * Time: 11:56
 */

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{
    //Création du l'utilisateur
    public function createUser (request $request) {
        $manager = $this->get('doctrine.orm.entity_manager');
        $roleRepository = $manager->getRepository('App:Role');

        $user = new User();
        $user->setUsername($request->get('username'));
        $user->setPassword($request->get('password'));
        $user->setNom($request->get('nom'));
        $user->setPrenom($request->get('prenom'));
        $user->setEmail($request->get('email'));
        $role = $roleRepository->findOneById($request->get('role'));
        $user->setRole($role);
        $manager->persist($user);
        $manager->flush();
        return $this->render('success.html.twig');
    }

    //Formulaire de création de l'utilisateur
    public function formCreateUser () {
        $manager = $this->get('doctrine.orm.entity_manager');
        $repository = $manager->getRepository('App:Role');
        $roles = $repository->findAll();
        return $this->render('createUser.html.twig', array('roles' => $roles));
    }

    //Liste des utilisateurs
    public function listUser () {
        $manager = $this->get('doctrine.orm.entity_manager');
        $repository = $manager->getRepository('App:User');
        $users = $repository->findAll();
        return $this->render('listUser.html.twig', array('users' => $users));
    }

    //Formulaire connexion
    public function formConnexion () {
        return $this->render('connexion.html.twig', array('failed' => false));
    }

    //Connexion
    public function connexion (request $request) {
        $manager = $this->get('doctrine.orm.entity_manager');
        $repository = $manager->getRepository('App:User');
        $user = $repository->findOneByEmail($request->get('email'));
        if ($user != null) {
            if ($user->getPasswordIsValid($request->get('password'))) {
                return $this->render('success.html.twig');
            }else {
                return $this->render('connexion.html.twig', array('failed' => true));
            }
        }else{
            return $this->render('connexion.html.twig', array('failed' => true));
        }
    }
}