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
    public function createUser (request $request) {
        $manager = $this->get('doctrine.orm.entity_manager');
        $roleRepository = $manager->getRepository('App:Role');

        $user = new User();
        $user->setUsername($request->get('username'));
        $user->setPassword(password_hash($request->get('password'), PASSWORD_DEFAULT));
        $user->setNom($request->get('nom'));
        $user->setPrenom($request->get('prenom'));
        $user->setEmail($request->get('email'));
        $role = $roleRepository->findOneById($request->get('role'));
        $user->setRole($role);
        $manager->persist($user);
        $manager->flush();
        return $this->render('success.html.twig');
    }

    public function formCreateUser () {
        $manager = $this->get('doctrine.orm.entity_manager');
        $repository = $manager->getRepository('App:Role');
        $roles = $repository->findAll();
        return $this->render('createUser.html.twig', array('roles' => $roles));
    }
}