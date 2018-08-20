<?php
// error_reporting(E_ALL & ~E_NOTICE);
error_reporting(0);
session_start();
require_once __DIR__.'/../vendor/autoload.php';
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();

/* Global constants */
define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT']);
define('APP_PATH', dirname(ROOT_PATH).DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR);
define('ASSETS_PATH', ROOT_PATH.DIRECTORY_SEPARATOR);

// Register Twig
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

// Register Swiftmailer
$app->register(new Silex\Provider\SwiftmailerServiceProvider());

// Register URL Generator
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

// Register Validator
$app->register(new Silex\Provider\ValidatorServiceProvider());

$gallery = array(
        'food' => '',
        'desert' => '',
    );

$app["twig"]->addGlobal("galleryData", $gallery);

// ------------------ Homepage ------------------------
$app->get('/', function () use ($app) {
    $msg = isset($_GET['msg'])? $_GET['msg'] : "";
	return $app['twig']->render('page/home.twig', array(
        'layout' => 'layouts/column1.twig',
        'msg'=> $msg,
    ));
})
->bind('homepage');

// ------------------ about ------------------
$app->get('/about', function () use ($app) {
    return $app['twig']->render('page/about.twig', array(
        'layout' => 'layouts/inside.twig',
    ));
})
->bind('about');

// ------------------ marketing ------------------
$app->get('/marketing', function () use ($app) {
    return $app['twig']->render('page/marketing.twig', array(
        'layout' => 'layouts/inside.twig',
    ));
})
->bind('marketing');

// $data_product = array(
// 'Sambel Asli <br> 140 ml',
// 'Hot and Sweet <br> 140 ml',
// 'Sambel Vegetarian <br> 320 ml',
// 'Sambel Sea Food <br> 320 ml',
// 'Hot and Sweet <br> 320 ml',
// 'Sambel Asli <br> 600 ml',
// 'Sambel Asli <br> 320 ml',
// 'Sambel Goreng <br> 250 ml',
// 'Sambel Asli <br> kemasan jerigen 5 kg',
// 'Sambel Asli <br> kemasan sachet',
//     );

$data_product = array(
           array(
            'nama'=>'Sambel Asli <br> 140 ml',
            'pict'=>'ok-5.png',
            'big'=>'ok-5.jpg'
            ),
           array(
            'nama'=>'Sambal Lampung <br> 140 ml',
            'pict'=>'ok-16.png',
            'big'=>'ok-16.jpg'
            ),
           array(
            'nama'=>'Hot and Sweet <br> 140 ml',
            'pict'=>'ok-7.png',
            'big'=>'ok-7.jpg'
            ),
           array(
            'nama'=>'Sambel Vegetarian <br> 320 ml',
            'pict'=>'ok-8.png',
            'big'=>'ok-8.jpg'
            ),
           array(
            'nama'=>'Sambel Sea Food <br> 320 ml',
            'pict'=>'ok-9.png',
            'big'=>'ok-9.jpg'
            ),
           array(
            'nama'=>'Hot and Sweet <br> 320 ml',
            'pict'=>'ok-10.png',
            'big'=>'ok-10.jpg'
            ),
           array(
            'nama'=>'Sambel Asli <br> 600 ml',
            'pict'=>'ok-11.png',
            'big'=>'ok-11.jpg'
            ),
           array(
            'nama'=>'Sambel Asli <br> 320 ml',
            'pict'=>'ok-15.png',
            'big'=>'ok-15.jpg'
            ),
           array(
            'nama'=>'Sambel Goreng <br> 250 ml',
            'pict'=>'ok-12.png',
            'big'=>'ok-12.jpg'
            ),
           array(
            'nama'=>'Sambel Asli <br> kemasan jerigen 5 kg',
            'pict'=>'ok-13.png',
            'big'=>'ok-13.jpg'
            ),
           array(
            'nama'=>'Sambel Asli <br> kemasan sachet',
            'pict'=>'ok-14.png',
            'big'=>'ok-14.jpg'
            ),

    );
$app["twig"]->addGlobal("productData", $data_product);

// ------------------ product ------------------
$app->get('/product', function () use ($app) {
    return $app['twig']->render('page/product.twig', array(
        'layout' => 'layouts/inside.twig',
    ));
})
->bind('product');

// ------------------ agent ------------------
$app->get('/agent', function () use ($app) {
    return $app['twig']->render('page/agent.twig', array(
        'layout' => 'layouts/inside.twig',
    ));
})
->bind('agent');


// ------------------ Contact Us ------------------
$app->match('/contact-us', function (Request $request) use ($app) {
    
    $data = $request->get('Contact');
    if ($data == null) {
        $data = array(
            'name'=>'',
            'email'=>'',
            'phone'=>'',
            'country'=>'',
            'address'=>'',
            'message'=>'',
        );
    }

    if ($_POST) {

         if (!isset($_POST['g-recaptcha-response'])) {
            return $app->redirect($app['url_generator']->generate('contact-us').'?msg=error_message');
        }
        $secret_key = "6LcqohUTAAAAAEd6Vc05jFTWhZT6rBij_hVggy3v";
        $response=file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$secret_key."&response=".$_POST['g-recaptcha-response']."&remoteip=".$_SERVER['REMOTE_ADDR']);
        $response = json_decode($response);
        if($response->success==false)
        {
          return $app->redirect($app['url_generator']->generate('contact-us').'?msg=error_message');
        }else{

        $constraint = new Assert\Collection( array(
            'name' => new Assert\NotBlank(),
            'email' => array(new Assert\Email(), new Assert\NotBlank()),
            'phone' => new Assert\Length(array('max'=>2000)),
            'country' => new Assert\Length(array('max'=>2000)),
            'address' => new Assert\Length(array('max'=>2000)),
            'message' => new Assert\Length(array('max'=>2000)),
        ) );

        $errors = $app['validator']->validateValue($data, $constraint);

        $errorMessage = array();
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $errorMessage[] = $error->getPropertyPath().' '.$error->getMessage();
            }
        } else {
            $pesan = \Swift_Message::newInstance()
                ->setSubject('Hi, Contact Website Sambel Cap Jempol')
                ->setFrom(array('no-reply@markdesign.net'))
                ->setTo( array('info@sambelcapjempol.com', $data['email']) )
                ->setBcc( array('deoryzpandu@gmail.com', 'ibnu@markdesign.net') )
                ->setReplyTo(array('info@sambelcapjempol.com '))
                ->setBody($app['twig']->render('page/mail.twig', array(
                    'data' => $data,
                )), 'text/html');

            $app['mailer']->send($pesan);
            return $app->redirect($app['url_generator']->generate('contactus').'?msg=success');
            }
        }
        // else captcha
    }

    return $app['twig']->render('page/contactus.twig', array(
        'layout' => 'layouts/inside.twig',
        'error' => $errorMessage,
        'data' => $data,
        'msg' =>$_GET['msg'],
    ));
})
->bind('contactus');

// ------------------ s_booking ------------------
$app->match('/s_booking', function (Request $request) use ($app) {

    $data = $request->get('reserve');

    if ($data == null) {
        $data = array(
            'name' => '',
            'email' => '',
        );
    }
 
    if ($_POST){
        if (!isset($_POST['g-recaptcha-response'])) {
            return $app->redirect($app['url_generator']->generate('homepage').'?msg=error_message');
        }
        $secret_key = "6LcqohUTAAAAAEd6Vc05jFTWhZT6rBij_hVggy3v";
        $response=file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$secret_key."&response=".$_POST['g-recaptcha-response']."&remoteip=".$_SERVER['REMOTE_ADDR']);
        $response = json_decode($response);
        if($response->success==false)
        {
          return $app->redirect($app['url_generator']->generate('homepage').'?msg=error_message');
        }else{
            echo "<pre>";
            print_r($data);
            exit;
           $messge_m = $app['twig']->render('page/reserve_mail.twig', array( 'data' => $data ));
               $pesan = \Swift_Message::newInstance()
                            ->setSubject('Hi, Inquiry Sambel Cap Jempol')
                            ->setFrom(array('no-reply@markdesign.net'))
                            ->setTo( array('info@sambelcapjempol.com', $data['email']) )
                            ->setBcc( array('deoryzpandu@gmail.com', 'ibnu@markdesign.net') )
                            ->setReplyTo(array('info@sambelcapjempol.com '))
                            ->setBody($messge_m, 'text/html');
                            // ->attach(Swift_Attachment::fromPath($fileToUpload));

                $app['mailer']->send($pesan);
                return $app->redirect($app['url_generator']->generate('homepage').'?msg=success_reseve');
        }
    }else{
        return $app->redirect( $app['url_generator']->generate('homepage') );
    }

})
->bind('s_booking');

$app['debug'] = true;

$app->run();