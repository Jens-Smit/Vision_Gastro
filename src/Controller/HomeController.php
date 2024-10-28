<?php

namespace App\Controller;

use App\Entity\Comments;
use App\Repository\ObjektCategoriesRepository;
use App\Repository\ObjektRepository;
use DateTime;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/')]
class HomeController extends AbstractController
{
    /**
     * The above function in PHP creates a form, processes user input, and sends an email with the form
     * data if the form is submitted and valid.
     * 
     * @param Request request The `` parameter in the `index` method of your Symfony controller
     * represents the current HTTP request. It contains information about the request such as the
     * request method, headers, parameters, and more. In your code snippet, you are using the
     * `` parameter to handle the form submission.
     * @param MailerInterface mailer The code you provided is a Symfony controller action that handles
     * a form submission for sending an email using the MailerInterface service. The MailerInterface
     * service is used to send emails in Symfony applications.
     * 
     * @return Response The code snippet provided is a Symfony controller method for handling a form
     * submission on the homepage route. The method creates a form with fields for email, company name,
     * and message, and a submit button. If the form is submitted and valid, it extracts the form data,
     * constructs an email message, and sends it using the MailerInterface.
     */
    #[Route('/', name: 'home', methods: ['GET', 'POST'])]
    public function index(ObjectManager $manager,MailerInterface $mailer): Response
    {
        $comments = $manager->getRepository(Comments::class)->findBy([], ['datetime' => 'DESC'], 5);

       
       
        /*
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $user_email= $data['email'];
            $Firmenname = $data['Firmenname'];
            $Nachricht = $data['Nachricht'];
            $email = (new Email())
                ->from($user_email)
                ->to('info@tex-mex.de')
                ->subject('Anfrage BETA-User '.$Firmenname )
                ->text($Nachricht)
                ->html('txt');

                $mailer->send($email);
            
        }
        */
        return $this->render('home/index.html.twig',[
            'posts' => $comments,
        ]);
    }
    /**
     * The function handles a POST request to save a comment with user details and sends a JSON
     * response.
     * 
     * Args:
     *   request (Request): The code snippet you provided is a PHP function that handles a POST request
     * to a specific route '/post'. It takes in parameters such as the Request object, ObjectManager,
     * and MailerInterface.
     *   manager (ObjectManager): The `` parameter in the code snippet you provided is an
     * instance of `ObjectManager`, which is typically used in Symfony applications for managing
     * entities and their lifecycle. In this context, it is used to persist the `Comments` entity to
     * the database using the `persist` and `flush` methods
     *   mailer (MailerInterface): The code snippet you provided is a PHP function that handles a POST
     * request to create a new comment. It takes input parameters such as email, user, and comment from
     * the request, creates a new Comments entity, sets its properties, persists it to the database
     * using Doctrine's ObjectManager, and returns a
     * 
     * Returns:
     *   The code snippet is a PHP function that handles a POST request to create a new comment. It
     * takes in the request object, ObjectManager for database operations, and MailerInterface for
     * sending emails. It extracts the email, username, and comment content from the request, creates a
     * new Comments entity with the provided data, persists it to the database, and returns a JSON
     * response with the user, comment,
     */
    #[Route('/post', name: 'post', methods: ['GET', 'POST'])]
    public function post( Request $request,ObjectManager $manager,MailerInterface $mailer): Response
    {   $email = $request->request->get('mail');
        $user = $request->request->get('user');
        $comment = $request->request->get('comments');
        $now = new DateTime();
        $comments = new Comments();
        $comments->setDatetime($now);
        $comments->setEmail($email);
        $comments->setPost($comment);
        $comments->setUsername($user);
        $manager->persist($comments);
        try {
            $manager->flush();
            $response = [
                'user' => $user,
                'comment' => $comment,
                'datetime' => $now->format('Y-m-d H:i:s')
            ];
        } catch (\Exception $e) {
            $response = ['error' => 'Could not save comment'];
        }
        return new JsonResponse($response);
    }
    #[Route('/community', name: 'community', methods: ['GET', 'POST'])]
    public function community(Request $request, ObjectManager $manager, MailerInterface $mailer): Response
    {
        $userEmail  = $request->request->get('mail');
        
        $Nachricht = 'Willkommen bei Vision Gastro – Deine Reise in die Zukunft der Gastronomie beginnt jetzt!
    
        Herzlich willkommen in der Vision Gastro Community! Wir freuen uns sehr, dass du dich entschieden hast, Teil unserer innovativen Gemeinschaft zu werden.
    
        Mit Vision Gastro hast du die Möglichkeit, dein Restaurant flexibel und kostengünstig zu managen. Unsere Plattform bietet dir die Werkzeuge und Ressourcen, die du benötigst, um dein Geschäft auf das nächste Level zu heben.
    
        Was dich erwartet:
        - Flexibles Management: Verwalte dein Restaurant von überall aus.
        - Kosteneffizienz: Spare Zeit und Geld mit unseren optimierten Prozessen.
        - Community: Vernetze dich mit anderen Gastronomie-Profis und tausche wertvolle Erfahrungen aus.
    
        Wir sind begeistert, dich auf dieser spannenden Reise zu begleiten und gemeinsam die Gastronomie zu revolutionieren. Bleib gespannt auf weitere Updates und exklusive Inhalte, die dir helfen werden, das Beste aus Vision Gastro herauszuholen.
    
        Falls du Fragen hast oder Unterstützung benötigst, zögere nicht, uns zu kontaktieren. Wir sind immer für dich da!
    
        Mit freundlichen Grüßen,
        Dein Vision Gastro Team';
    
        $email = (new Email())
            ->from('office@tex-mex.de')
            ->to($userEmail )
            ->subject('Teil der Vision Gastro Community werden')
            ->text($Nachricht)
            ->html(nl2br($Nachricht));
    
        $mailer->send($email);
        
        return new JsonResponse(true);
    }
    
    /**
     * The above function is a PHP route that renders a Twig template for the "about" page.
     * 
     * @return Response The `about()` function is returning a response that renders the
     * 'Datenschutz.html.twig' template located in the 'home' directory.
     */
    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->render('home/Datenschutz.html.twig');
    }
    /**
     * The function `contact` in PHP creates a form for users to submit their email and message, sends
     * an email with the submitted data to a specified address, and renders a contact form template.
     * 
     * @param Request request The `` parameter in the `contact` function represents the
     * incoming request made to the `/contact` route. It contains information about the request such as
     * headers, parameters, and other data sent by the client.
     * @param MailerInterface mailer The `` parameter in the `contact` function is an instance
     * of `Symfony\Component\Mailer\Mailer\MailerInterface`. This interface provides methods for
     * sending emails in Symfony applications. In the provided code snippet, the `` service is
     * used to send an email with the user's input data
     * 
     * @return Response The `contact` method is returning a Response object which renders the
     * `contact.html.twig` template with the form data passed to it.
     */
    #[Route('/contact', name: 'contact')]
    public function contact(Request $request,MailerInterface $mailer): Response
    {
        $form = $this->createFormBuilder(null, [
            'attr' => ['class' => 'w-100']
        ])
        ->add('email', EmailType::class, [
            'attr' => ['class' => 'w-100']
        ])
        ->add('Nachricht', TextareaType::class, [
            'attr' => ['class' => 'w-100']
        ])
        
        ->add('submit', SubmitType::class, [
            'label' => 'Anfrage senden',
            'attr' => ['class' => 'btn-info w-100 btn']
        ])
        ->getForm();
        $form -> handleRequest($request);
       

       if ($form->isSubmitted() && $form->isValid()) {
           $data = $form->getData();
           $user_email= $data['email'];
          
           $Nachricht = $data['Nachricht'];
           $email = (new Email())
            ->from($user_email)
            ->to('info@tex-mex.de')
            ->subject('Anfrage userForm Gamasy ' )
            ->text($Nachricht)
            ->html('txt');

            $mailer->send($email);
         
       }
        return $this->render('home/contact.html.twig', [
            'form' => $form->createView()
        ]);
    }
   
  /**
   * The above function in PHP is a controller method that renders a Twig template for the "features"
   * route.
   * 
   * @return Response The `features()` method is returning a Response object which renders the
   * 'features.html.twig' template.
   */
    #[Route('/features ', name: 'features')]
    public function features(): Response
    {
        return $this->render('home/features.html.twig');
    }
   /**
    * The above function in PHP defines a route for a privacy page that renders a Twig template for
    * displaying privacy information.
    * 
    * @return Response The `privacy()` method is returning a Response object that renders the
    * `datenschutz.html.twig` template file located in the `home` directory.
    */
    #[Route('/privacy ', name: 'privacy')]
    public function privacy(): Response
    {
        return $this->render('home/datenschutz.html.twig');
    }

   /* public function index(Request $request,RentItemsRepository $rentItemsRepository ,ReservationRepository $reservationRepository): Response
    {
        $items = $reservationRepository->findAll();
        $Avform = $this->createFormBuilder()
        ->add('date', DateTimeType::class, [
            'date_label' => 'Datum',
            ])
        ->add('pax', NumberType::class)
        ->add('send', SubmitType::class)
        ->getForm()
        ;
        $Avform->handleRequest($request);

        if ($Avform->isSubmitted()){
            
        $id = 1;
        $eingabe = $Avform->getData();
        $date = $eingabe['date'];
        $datestr = $date->format( 'Y-m-d H:i:s' ); 
        $time_edd= strtotime($datestr)+(60*60);
        $time  = new DateTime();
        $time->setTimestamp($time_edd);
        $timestr = $time->format( 'Y-m-d H:i:s' );
        




        $pax = $eingabe['pax'];
        
        $items = $rentItemsRepository->findfree( $datestr,$timestr, $id, $pax);
           
        $info = 'test';   
        return $this->render('home/index.html.twig', [
            'info' => $info,
            'Avform' => $Avform->createView(),
            'items' => $items,
        ]);

        }
        return $this->render('home/index.html.twig', [
            'info' => 'Alle reservierungen',
            'Avform' => $Avform->createView(),
            'items' => $items,
        ]);
    }
    */
}

    