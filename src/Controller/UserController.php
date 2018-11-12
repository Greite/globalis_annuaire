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
use Symfony\Component\HttpFoundation\Session\Session;

class UserController extends Controller
{

    //Création du l'utilisateur
    public function createUser (request $request) {
        $session = $this->get('session');
        $userConnected = $session->get('user');
        if ($userConnected) {
            if ($userConnected['role']['const'] == 'ROLE_CLIENT') {
                return $this->redirectToRoute('listAnnuaire');
            }
        }else {
            return $this->redirectToRoute('connexion');
        }
        if ($this->isCsrfTokenValid('create-user', $request->get('token'))) {
            $manager = $this->get('doctrine.orm.entity_manager');
            $userRepository = $manager->getRepository('App:User');
            $roleRepository = $manager->getRepository('App:Role');
            $filled = array(
                'username' => $request->get('username'),
                'nom' => $request->get('nom'),
                'prenom' => $request->get('prenom'),
                'email' => $request->get('email'),
                'role' => $request->get('role')
            );

            if ($request->get('username') != '' && $request->get('password') != '' && $request->get('nom') != '' && $request->get('prenom') != '' && $request->get('email') != '') {
                if (filter_var($request->get('email'), FILTER_VALIDATE_EMAIL)) {
                    $alreadyUser = $userRepository->findOneByEmail($request->get('email'));
                    if (!$alreadyUser) {
                        $user = new User();
                        $user->setUsername($request->get('username'));
                        $user->setPassword($request->get('password'));
                        $user->setNom($request->get('nom'));
                        $user->setPrenom($request->get('prenom'));
                        $user->setEmail($request->get('email'));
                        $role = $roleRepository->findOneById($request->get('role'));
                        $user->setRole($role);
                        $user->setOnline(false);
                        $manager->persist($user);
                        $manager->flush();
                        return $this->redirectToRoute('listUser');
                    }else {
                        $roles = $roleRepository->findAll();
                        return $this->render('createUser.html.twig', array('roles' => $roles, 'message' => 'Cette adresse email possède déjà un compte', 'filled' => $filled));
                    }
                }else {
                    $roles = $roleRepository->findAll();
                    return $this->render('createUser.html.twig', array('roles' => $roles, 'message' => 'Veuillez saisir une adresse email valide', 'filled' => $filled));
                }
            }else {
                $roles = $roleRepository->findAll();
                return $this->render('createUser.html.twig', array('roles' => $roles, 'message' => 'Veuillez remplir tous les champs', 'filled' => $filled));
            }
        }else {
            return $this->redirectToRoute('formCreateUser');
        }
    }

    //Supprime un utilisateur
    public function deleteUser ($id) {
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
        $repository = $manager->getRepository('App:User');
        $user = $repository->findOneById($id);
        $manager->remove($user);
        $manager->flush();
        return $this->redirectToRoute('listUser');
    }

    //Formulaire de création de l'utilisateur
    public function formCreateUser () {
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
        $repository = $manager->getRepository('App:Role');
        $roles = $repository->findAll();
        return $this->render('createUser.html.twig', array('roles' => $roles));
    }

    //Liste des utilisateurs
    public function listUser (request $request) {
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
        $repository = $manager->getRepository('App:User');
        $roleRepository = $manager->getRepository('App:Role');
        $roles = $roleRepository->findAll();
        $filtres = array();
        if ($request->request->all() != []) {
            $qb = $repository->createQueryBuilder('u');
            if ($request->get('nom') != '') {
                $qb->andWhere('u.nom LIKE :nom');
                $qb->setParameter('nom', '%'.$request->get('nom').'%');
                $filtres['nom'] = $request->get('nom');
            }
            if ($request->get('prenom') != '') {
                $qb->andWhere('u.prenom LIKE :prenom');
                $qb->setParameter('prenom', '%'.$request->get('prenom').'%');
                $filtres['prenom'] = $request->get('prenom');
            }
            if (!$request->get('activeUser')) {
                $qb->andWhere('u.online = 0');
            }
            $filtres['activeUser'] = $request->get('activeUser');
            if ($request->get('role') != '') {
                $roleRepository = $manager->getRepository('App:Role');
                $role = $roleRepository->findOneById($request->get('role'));
                $qb->andWhere('u.role = :role');
                $qb->setParameter('role', $role);
                $filtres['role'] = $request->get('role');
            }
            $query = $qb->getQuery();
            $users = $query->getResult();
        }else {
            $users = $repository->findAll();
        }
        $count = count($users);
        $paginator  = $this->get('knp_paginator');
        $appointments = $paginator->paginate($users, $request->query->getInt('page', 1), 10);
        return $this->render('listUser.html.twig', array('users' => $appointments, 'count' => $count, 'roles' => $roles, 'filtres' => $filtres));
    }

    //Formulaire connexion
    public function formConnexion () {
        return $this->render('connexion.html.twig', array('failed' => false));
    }

    //Connexion
    public function connexion (request $request) {
        $session = new Session();
        $manager = $this->get('doctrine.orm.entity_manager');
        $repository = $manager->getRepository('App:User');
        $user = $repository->findOneByEmail($request->get('email'));
        if ($user != null) {
            if ($user->getPasswordIsValid($request->get('password'))) {
                $formattedUser = array(
                    'id' => $user->getId(),
                    'prenom' => $user->getPrenom(),
                    'nom' => $user->getNom(),
                    'password' => $user->getPassword(),
                    'role' => array(
                        'id' => $user->getRole()->getId(),
                        'title' => $user->getRole()->getTitle(),
                        'const' => $user->getRole()->getConst()
                    )
                );
                $session->set('user', $formattedUser);
                $user->setOnline(true);
                $manager->persist($user);
                $manager->flush();
                return $this->redirectToRoute('listAnnuaire');
            }else {
                return $this->render('connexion.html.twig', array('failed' => true));
            }
        }else{
            return $this->render('connexion.html.twig', array('failed' => true));
        }
    }

    //Deconnexion
    public function deconnexion () {
        $manager = $this->get('doctrine.orm.entity_manager');
        $repository = $manager->getRepository('App:User');
        $session = $this->get('session');
        $user = $session->get('user');
        $user = $repository->findOneById($user['id']);
        $user->setOnline(false);
        $manager->persist($user);
        $manager->flush();
        $session->clear();
        return $this->redirectToRoute('formConnexion');
    }
}