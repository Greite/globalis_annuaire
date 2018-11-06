<?php
/**
 * Created by PhpStorm.
 * User: gauthierpainteaux
 * Date: 06/11/2018
 * Time: 11:25
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function test () {
        return $this->render('test.html.twig');
    }
}