<?php

namespace App\Controller;
use App\Entity\Users;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

//////////////
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

//////////////////////

class DataController extends AbstractController
{

    
        /**
        * @Route("/add")
        * 
        */
        public function new(Request $request)
        {   
           
            $user = new users();
            $user->setName('Podaj imie');
            $user->setSname('Podaj nazwisko');
           
    
            $form = $this->createFormBuilder($user)
                ->add('Name', TextType::class)
                ->add('Sname', TextType::class)
                ->add('file', FileType::class)
                ->add('save', ButtonType::class, array('label' => 'Wyslij'))
                ->getForm();
            
          
                if ($request->isXmlHttpRequest() || $request->query->get('showJson') == 1) {  
                    $jsonData = array();  
                    $form->handleRequest($request);
                    $file = $request->files->get('file');
                    $name = $request->get('name');
                    $fname = $request->get('fname');
                    $sname = $request->get('sname');
                    $Ext = $file->guessExtension();
                    if($Ext != 'png' and $Ext != 'jpg' and $Ext != 'jpeg'){
                        return new JsonResponse("Zły format!");
                    }
                    else{
                    $user ->setName($name);
                    $user ->setSname($sname);
                    $user ->setFile($fname);
                    
                    $request->files->get('file')->move(
                        $this->getParameter('brochures_directory'),
                        $fname
                    );
                   
                    
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($user);
                    $entityManager->flush();
                    return new JsonResponse("plik został dodany!");
                }
            }
            else{  
            return $this->render('control1/index.html.twig', array(
                'form' => $form->createView()
            ));
            }
        }




        /**
        * @Route("/data/ajax")
        * 
        */    
       

        public function ajaxAction(Request $request) {  
            $students = $this->getDoctrine() 
               ->getRepository(users::class) 
               ->findAll();  
               
            if ($request->isXmlHttpRequest() || $request->query->get('showJson') == 1) {  
                $form->handleRequest($request);
                $file = $request->files->get('file');
                $name = $request->get('name');
                
               return new JsonResponse($jsonData); 
            } else { 
               return $this->render('/data/ajax.html.twig'); 
            } 
         }    




    /**
    * @Route("/log")
    * 
    */    
    public function Login(Request $request) {  
        $form = $this->createFormBuilder()
            ->add('Name', TextType::class)
            ->add('pass', PasswordType::class)
            ->add('save', ButtonType::class, array('label' => 'Wyslij'))
            ->getForm();

        if ($request->isXmlHttpRequest() || $request->query->get('showJson') == 1) {  
            $login = $request->get('login');
            $password = $request->get('password');

            if($login == 'a' and $password == 'b'){
                $users = $this->getDoctrine()
                    ->getRepository(Users::class)
                    ->findAll();
                return $this->render('data/show.html.twig', array('users' => $users));
            }
            else 
            {
                return new Response("zle hasło");
            }
        } 
        else 
        { 
            return $this->render('data/login.html.twig', array(
            'form' => $form->createView()));
        }
    }    
    
    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }

    
    /**
     * @Route("/list", name="list")
     */
    public function show()
    {
        $users = $this->getDoctrine()
            ->getRepository(Users::class)
            ->findAll();
        return $this->render('data/show.html.twig', array('users' => $users));
    }

    /**
     * @Route("/showFile/{id}", name="showFile")
     */
    public function showFile($id)
    {
        
        $file = $this->getDoctrine()->getRepository(Users::class);
        $userFile = $file->findOneBy(['id' => $id]);
        $userFileName  = $userFile->getFile();
        $publicResourcesFolderPath = $this-> getParameter('brochures_directory')."/" ;
       // $userFileName = "/Przechwytywanie.png";

        return  new BinaryFileResponse($publicResourcesFolderPath.$userFileName);
    }
}
