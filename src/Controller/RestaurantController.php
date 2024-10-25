<?php

namespace App\Controller;


use App\Entity\Reservation;

use App\Repository\ObjektRepository;
use App\Repository\ObjektSubCategoriesRepository;
use App\Repository\OpeningTimeRepository;
use App\Repository\RentItemsRepository;
use App\Repository\ReservationRepository;
use App\Repository\SpecialOpeningTimeRepository;
use DateTime;
use Exception;
use Firebase\JWT\JWT;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
#[Route('/restaurant')]
class RestaurantController extends AbstractController
{
    #[Route('/', name: 'restaurant')]
    public function index(Request $request, ObjektRepository $objektRepository,RentItemsRepository $rentItemsRepository, ObjektSubCategoriesRepository $objektSubCategoriesRepository): Response
    {
        $objekts = $objektRepository->findby(['categories' => '1']);
        $categories = $objektSubCategoriesRepository->findAll();
       
        
        
        $Avform = $this->createFormBuilder()
        
        ->add('date', DateTimeType::class, [
            'label' => 'Datum' ,
            'date_widget' => 'single_text' ,
            'time_widget' => 'single_text' , ])
        ->add('pax', NumberType::class, [
            'label' => 'Personen',])
        ->add('send', SubmitType::class,[
            'label' => 'verfügbarkeit prüfen',
            'attr'=>[
                'class' => 'btn-info btn',
                'style' =>  'width:100%;',          ]
        ])
        ->getForm()
            ;
       
        $Avform->handleRequest($request);
        if ($Avform->isSubmitted() && $Avform->isValid() ) 
        {
            $eingabe = $Avform->getData();
            $date = $eingabe['date'];
            $pax = $eingabe['pax'];
            $datestr = $date->format('Y-m-d H:i:s');
            $time_edd= strtotime($datestr)+(120*60);
            $time  = new DateTime();
            $time->setTimestamp($time_edd);
            $timestr = $time->format( 'Y-m-d H:i:s' );
           
            $category = 1;
            $objekts = $objektRepository->findAllfree( $datestr, $timestr,  $pax, $category);
           
            return $this->render('restaurant/result.html.twig', [
                'info' => 'Verfügbare',
                'objekts'=> $objekts,
                'categories'=> $categories,
                'Avform' => $Avform->createView(),
            ]);
        }else{
            return $this->render('restaurant/index.html.twig', [
                'info' => 'Übersicht der Restaurants',
                'objekts'=> $objekts,
                'categories'=> $categories,
                'Avform' => $Avform->createView(),
            ]);
        }
    }

    #[Route('/{id}', name: 'app_restaurant_availability', methods: ['GET', 'POST'])]
    public function new(string $id, ObjektSubCategoriesRepository $objektSubCategoriesRepository ,SpecialOpeningTimeRepository $specialOpeningTimeRepository,OpeningTimeRepository $openingTimeRepository ,Request $request,ObjektRepository $objektRepository ,RentItemsRepository $rentItemsRepository ,ReservationRepository $reservationRepository): Response
    {
        
        $Avform = $this->createFormBuilder()
        
            ->add('date', DateTimeType::class, [
                'label' => 'Datum' ,
                'date_widget' => 'single_text' ,
                'time_widget' => 'single_text'  ])
            ->add('pax', NumberType::class, [
                'label' => 'Personen'])
            ->add('send', SubmitType::class,[
                'label' => 'verfügbarkeit prüfen',
                'attr'=>[
                    'class' => 'btn-info btn',
                    'style' =>  'width:100%;',]
            ])
            ->getForm()
            ;
      

        $resform = $this->createFormBuilder() 
            ->setMethod('POST')
            ->add('date', DateTimeType::class, [
                'label' => 'Datum',
                'date_widget' => 'single_text',
                'time_widget' => 'single_text' ,
                'attr' => array(
                    'readonly' => true,
                   
                ),             
            ])
            ->add('pax', NumberType::class, [
                'label' => 'Personen' ,
                'attr' => array(
                    'readonly' => true,
                ),
                ])
            ->add('user', TextType::class)
            ->add('mail', EmailType::class ) 
            ->add('fon', TelType::class )
            ->add('send', SubmitType::class,[
                'label' => 'Reservieren',
                'attr'=>[
                    'class' => 'btn-info btn',
                    'style' =>  'width:100%;',]
            ])
            ->getForm()
            ;  
         
        $resform->handleRequest($request); 
        $Avform->handleRequest($request);
        if ($Avform->isSubmitted() && $Avform->isValid() ) 
            {   
            $eingabe = $Avform->getData();
            $date = $eingabe['date'];
            $datestr = $date->format( 'Y-m-d H:i:s' );
            $checkTime = $date->format( 'H:i:s' );
            $weekday = date('w',  strtotime($datestr));
            $openingtimes = $openingTimeRepository->findBy(['objekt'=>$id, 'day' => $weekday], ['day' => 'ASC']);
            $cois_day = $openingtimes[0]; 
            $open_time =  $cois_day->getStart();
            $open_time =   $open_time->format( 'H:i:s' );
            $close_time =  $cois_day->getEnd();
            $close_time =   $close_time->format( 'H:i:s' );   
        
            //länge des Aufenthalt noch abfragen aus Objekt ->testweise auf
            $time_edd= strtotime($datestr)+(60*60);
            $time  = new DateTime();
            $time->setTimestamp($time_edd);
            $timestr = $time->format( 'Y-m-d H:i:s' );
            $pax = $eingabe['pax'];
            $items = $rentItemsRepository->findfree( $datestr,$timestr, $id, $pax);
            $item_count =  count($items);
            $spezialOpenings = $specialOpeningTimeRepository->findBy(['objket'=>$id]);
        
            
            foreach($spezialOpenings as $spezialOpening){
                
                if ($date->format( 'Y-m-d' ) === $spezialOpening->getDay()->format( 'Y-m-d' )){
                if($spezialOpening->isClose() === true){
                    $open_time = 0;
                    $close_time = 0;
                }else{
                $open_time = $spezialOpening->getStart()->format( 'H:i:s' );
                $close_time = $spezialOpening->getEnd()->format( 'H:i:s' ); 
                } 
                
                }
            }
            //verfügbarkeit der items prüfen   
            if( $checkTime >= $open_time AND $checkTime < $close_time ){ 
                    if ($item_count>=1){
                            $item = $items[0];
                            $item_id = $item['item_id'];
                        return $this->render('restaurant/availability.html.twig', [   
                            'info' => 'Item '.$item_id.' wird Reserviert. Es sind insgesamt '.$item_count.' Items Verfügbar',
                            'Avform' => $resform->createView(),
                        ]);
                    }else{
                        return $this->render('restaurant/availability.html.twig', [   
                            'info' => 'leider sind wir ausgebucht',
                            'Avform' => $Avform->createView(),
                        ]);
                    }
                }else{
                    return $this->render('restaurant/availability.html.twig', [   
                        'info' => 'Ihre Anfrage liegt außerhalb der Öffnungszeiten',
                        'Avform' => $Avform->createView(),
                    ]);
                }
        }  
        
        if ($resform->isSubmitted() && $resform->isValid() ) 
            {
                $eingabe = $resform->getData();
            // dump( $eingabe);
                $date = $eingabe['date'];
                $datestr = $date->format( 'Y-m-d H:i:s' );
                $time_edd= strtotime($datestr)+(60*60);
                $time  = new DateTime();
                $time->setTimestamp($time_edd);
                $timestr = $time->format( 'Y-m-d H:i:s' );
                $pax = $eingabe['pax'];
                $items = $rentItemsRepository->findfree( $datestr,$timestr, $id, $pax);
                $item = $items[0]['item_id'];
                $item = $rentItemsRepository->findOneBy(['id' => $item]);
            

                
                //übergeben der daten an das Reposetory classe
                $resavation = new Reservation;
                $resavation->setUser($eingabe['user']);
                $resavation->setKommen($eingabe['date']);
                $resavation->setPax($eingabe['pax']);
                $resavation->setItem($item);
                $resavation->setMail($eingabe['mail']);
                $resavation->setFon($eingabe['fon']);
                $resavation->setGehen($eingabe['date']);
                $reservationRepository->save($resavation, true);
            
                $objekts = $objektRepository->findby(['categories' => '1']);
                $categories = $objektSubCategoriesRepository->findAll();
            return $this->render('restaurant/index.html.twig', [   
                    'info' => 'Vielen Dank für deine Reservierung',
                    'objekts'=> $objekts,
                    'categories'=> $categories,
                    'Avform' => $resform->createView(),
                ]);
            }
        $objekt = $objektRepository->findOneById($id);
        $recomodations = $objektRepository->findRecomodation($id);
        $count_rec = count($recomodations);
        $sum_points = 0;
        for($x=0; $x<$count_rec; $x++){
            $points  = $recomodations[$x]['points']; 
            $sum_points = $sum_points+$points;
        }
        $finale_points =  $sum_points / $count_rec;
        return $this->render('restaurant/objekt.html.twig', [
                'recomodation' => $finale_points,
                'info' => 'das Objekt:hat:',
                'objekt'=> $objekt,
                'Avform' => $Avform->createView(),
        ]); 
    }
    #[Route('/{id}/api', name: 'api_restaurant_availability', methods: ['GET', 'POST'])]
    public function apiAvailability(string $id, Request $request, SpecialOpeningTimeRepository $specialOpeningTimeRepository, RentItemsRepository $rentItemsRepository, OpeningTimeRepository $openingTimeRepository): JsonResponse
    {
        // API-Schlüssel konfigurieren
        $apiKey = 'YOUR_API_KEY';

        // Prüfen, ob ein gültiger Authorization-Header gesendet wurde
        if (!isset($_SERVER['HTTP_AUTHORIZATION']) || empty($_SERVER['HTTP_AUTHORIZATION'])) {
            http_response_code(401); // Unauthorized
            echo json_encode(array('error' => 'Authorization header missing.'));
            exit();
        }

        // Authorization-Header auslesen
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];

        // Prüfen, ob der Authorization-Header mit 'Bearer' beginnt
        if (strpos($authHeader, 'Bearer') !== 0) {
            http_response_code(401); // Unauthorized
            echo json_encode(array('error' => 'Invalid authorization header.'));
            exit();
        }

        // JWT aus Authorization-Header extrahieren
        $jwt = substr($authHeader, 7); // "Bearer " hat 7 Zeichen

        // JWT verifizieren
        try {
            $decodedJwt = JWT::decode($jwt, $apiKey, array('HS256'));
        } catch (Exception $e) {
            http_response_code(401); // Unauthorized
            echo json_encode(array('error' => 'Invalid token.'));
            exit();
        }

        // Der JWT ist gültig und enthält die benötigten Daten
        $userID = $decodedJwt->user_id;


       
        // Hole die Formdaten
        $datestr = $request->request->get('date');
        $checkTime = $request->request->get('time');
        $pax = $request->request->get('persons');
    
        // Hier könnten wir die Verfügbarkeit und den Status der Reservierung prüfen
        
            
            $weekday = date('w',  strtotime($datestr));
            $openingtimes = $openingTimeRepository->findBy(['objekt'=>$id, 'day' => $weekday], ['day' => 'ASC']);
            $cois_day = $openingtimes[0]; 
            $open_time =  $cois_day->getStart();
            $open_time =   $open_time->format( 'H:i:s' );
            $close_time =  $cois_day->getEnd();
            $close_time =   $close_time->format( 'H:i:s' );   
        
            //länge des Aufenthalt noch abfragen aus Objekt ->testweise auf 1 Stunde
            $time_edd= strtotime($datestr)+(60*60);
            $time  = new DateTime();
            $time->setTimestamp($time_edd);
            $timestr = $time->format( 'Y-m-d H:i:s' );
            
            $items = $rentItemsRepository->findfree( $datestr,$timestr, $id, $pax);
            $item_count =  count($items);
            $spezialOpenings = $specialOpeningTimeRepository->findBy(['objket'=>$id]);
        
            
            foreach($spezialOpenings as $spezialOpening){
                
                if ($datestr === $spezialOpening->getDay()->format( 'Y-m-d' )){
                if($spezialOpening->isClose() === true){
                    $open_time = 0;
                    $close_time = 0;
                }else{
                $open_time = $spezialOpening->getStart()->format( 'H:i:s' );
                $close_time = $spezialOpening->getEnd()->format( 'H:i:s' ); 
                } 
                
                }
            }
            //verfügbarkeit der items prüfen   
            if( $checkTime >= $open_time AND $checkTime < $close_time ){ 
                    if ($item_count>=1){
                            $item = $items[0];
                            $item_id = $item['item_id'];
                            $response = [
                                'status' => true,
                                'date' => $datestr,
                                'time' => $checkTime,
                                'persons' => $pax,
                                'item' => $item_id,
                            ];
                        
                    }else{
                        $response = [
                            'status' => 'leider sind wir ausgebucht',
                            'date' => $datestr,
                            'time' => $checkTime,
                            'persons' => $pax
                        ];
                       
                    }
                }else{
                    $response = [
                        'status' => 'Ihre Anfrage liegt außerhalb der Öffnungszeiten'.$datestr,
                        'date' => $datestr,
                        'time' => $checkTime,
                        'persons' => $pax
                    ];
                    
                }
         // und entsprechende Antworten zurückgeben.
         // Zum Beispiel:
        
    
        // JSON-Antwort zurückgeben
        return new JsonResponse($response);
    }
    
}



 