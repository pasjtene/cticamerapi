<?php
namespace ApiBundle\Controller;

use AppBundle\Entity\AuthToken;
use AppBundle\Entity\User;
use AppBundle\Tools\FunglobeUtils;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use  Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;

class MailController extends FOSRestController
{

    public  function  getAbsolutPath()
    {
        return __DIR__;
    }


    /**
     * @Rest\Get("{_locale}/confirm/{email}/{name}/{password}")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Teste l'envoi  du  mail",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="name", "dataType"="string", "required"=true, "description"="the user name"},
     *     {"name"="password", "dataType"="string", "required"=true, "description"="the user password"},
     *     {"name"="email", "dataType"="string", "required"=true, "description"="the user email  adresse"}
     *  }
     * )
     */
    public function indexAction($email, $name, $password)
    {
        $logo = "funglobe.com/logo.ico";
        $url = "test.com";
        $urlPassword = "rest.com";
        $confirm ="confirm.com";

        $array = ["confirm"=>$confirm,"email"=>$email, "name"=>$name, "password"=>$password,"urlPassword"=>$urlPassword, "url"=>$url,"logo"=>$logo, "key"=>md5($password.$email)];
        return $this->render('ApiBundle:Mail:emailSend.html.twig',$array);
    }


    /**
     * @Rest\Post("/auth/send-email")
     * @return Response
     * @ApiDoc(
     *  resource=true,
     *  description="Envoie un email à une liste d'utilisateurs ",
     *  statusCodes = {
     *      200 = "Updated (seems to be OK)",
     *      400 = "Bad request (see messages)"
     *  },
     *  parameters={
     *     {"name"="recipients", "dataType"="array", "required"=true, "description"="Tableau d'adresse email des utilisateurs"},
     *     {"name"="title", "dataType"="string", "required"=true, "description"="Titre du mail"},
     *     {"name"="message", "dataType"="string", "required"=true, "description"="Contenu du mail"}
     *  }
     * )
     */
    public function sendEmailAction(Request $request)
    {
        $title = $request->get('title');
        $message = $request->get('message');
        $recipients = $request->get('recipients');

        $recipients = explode(',', $recipients);
        $from = $this->getParameter('mailer_user');
        $template = "ApiBundle:Mail:emailSend.html.twig";
        $data = ['title' => $title, 'message' => $message];

        $view = $this->renderView($template, $data);

        $code = FunglobeUtils::sendMail($this->get('mailer'), $recipients, $from, $view, $title);

        return $this->json(['code' => '122', 'message' => 'Email sent successfully !']);
    }
}
