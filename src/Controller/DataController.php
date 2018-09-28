<?php

namespace App\Controller;
use App\Entity\Users;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class DataController extends AbstractController
{
    /**
    * @Route("/add")
    * 
    */
    public function index(Request $request)
    {   
        $user = new users();    
        $form = $this->createFormBuilder($user)
            ->add('Name', TextType::class)
            ->add('Sname', TextType::class)
            ->add('file', FileType::class)
            ->add('save', ButtonType::class, array('label' => 'Wyslij'))
            ->getForm();
        
            if ($request->isXmlHttpRequest()) 
            {                    
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
                return new JsonResponse("Dane został dodane.");
            }
        }
        else{  
        return $this->render('data/index.html.twig', array(
            'form' => $form->createView()
        ));
        }
    }

    /**
    * @Route("/log")
    * 
    */    
    public function Login(Request $request) 
    {  
        $form = $this->createFormBuilder()
            ->add('Name', TextType::class)
            ->add('pass', PasswordType::class)
            ->add('save', ButtonType::class, array('label' => 'Wyslij'))
            ->getForm();

        if ($request->isXmlHttpRequest()){  
            $login = $request->get('login');
            $password = $request->get('password');

            if($login == 'admin' and $password == 'admin')
            {
                $users = $this->getDoctrine()
                    ->getRepository(Users::class)
                    ->findAll();
                return $this->render('data/show.html.twig', array('users' => $users));
            }
            else 
            {
                return new Response("Zły login lub hasło!");
            }
        } 
        else 
        { 
            return $this->render('data/login.html.twig', array(
            'form' => $form->createView()));
        }
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
        return  new BinaryFileResponse($publicResourcesFolderPath.$userFileName);
    }
}
