<?php

namespace ApiBundle\Controller;

use AppBundle\Entity\PasswordReset;
use AppBundle\Entity\User;
use AppBundle\Tools\FunglobeUtils;
use FOS\RestBundle\Controller\Annotations as Rest;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PasswordResetController extends FOSRestController
{
    /**
     * @Rest\Post("/reset-password-request")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Créer un demande reinitialisation du mot de passe",
     *  statusCodes={
     *     200="Retourné quand tout est OK !",
     *     400="Retourné quand la demande n'a pas été effectué !",
     *  },
     *  parameters={
     *     {"name"="email", "dataType"="string", "required"=true, "description"="Adresse email de l'utilisateur"}
     *  }
     * )
     */
    public function resetPasswordRequestAction(Request $request)
    {
        $datas = json_decode($request->getContent());

        $token = md5(uniqid());

        $appurl = $datas->url;

        $url = $appurl.'reset-password?email='.$datas->email.'&confirmationtoken='.$token;

        $translator = $this->get('translator');
        $subject = $translator->trans('resetting.email.subject', [], 'Email');

        ////->setFrom(['support@funglobe.com' => "FunGlobe support"])

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($this->getParameter('mailer_user'))
            ->setTo($datas->email)
            ->setBody($this->renderView('reset-password.html.twig', array('resetUrl'=> $url)), 'text/html');

        $mailer = $this->get('mailer');

        $sent = $mailer->send($message);

        if($sent)
        {
            $passwordReset = new PasswordReset();
            $passwordReset->setEmail($datas->email)->setToken($token);

            $em = $this->getDoctrine()->getManager();
            $em->persist($passwordReset);
            $em->flush();
        }

        return $this->json(['message' => $sent], $sent ? 200 : 400);
    }

    /**
     * @Rest\Post("/verify-reset-password")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Vérifier l'intégrité et la validité du jeton pour la réinitilisation d'un mot de passe",
     *  statusCodes={
     *     200="Retourné quand le jeton est valide",
     *     400={
     *          "Retourné quand le jeton n'est pas valide !",
     *          "Retourné quand l'adresse email n'est pas associé au jeton trouvé !",
     *          "Retourné quand le jeton a expiré !"
     *     }
     *  },
     *  parameters={
     *     {"name"="email", "dataType"="string", "required"=true, "description"="Adresse email de l'utilisateur"},
     *     {"name"="token", "dataType"="string", "required"=true, "description"="Jeton associé à l'adresse email"}
     *  }
     * )
     */
    public function verifyResetPasswordAction(Request $request)
    {
        $datas = json_decode($request->getContent());
        $status = 200;
        $message = ['The token is valid'];

        $email = $datas->email;
        $token = $datas->token;

        $em = $this->getDoctrine()->getManager();

        /** @var PasswordReset $passwordReset */
        $passwordReset = $em->getRepository('AppBundle:PasswordReset')->findOneBy(['token' => $token]);

        if(is_object($passwordReset))
        {
            //On vérifie que le token trouvé est associé à l'adresse email qui a été envoyé

            if($passwordReset->getEmail() !== $email)
            {
                $status = 400;
                $message = ["Bad email address for this token !"];
            }
            else
            {
                //On vérifie que le token a été créé il y'a moins de 2 jours

                $expirationDate = $passwordReset->getCreatedAt()->add(new \DateInterval('P2D'));
                $now = new \DateTime();

                if($expirationDate->diff($now)->days == 0)
                {
                    $status = 400;
                    $message = ["The token expired !"];
                }
            }
        }
        else
        {
            $status = 400;
            $message = ["Bad token provided !"];
        }

        return $this->json($message, $status);
    }

    /**
     * @Rest\Put("/reset-password")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="réinitilisation le mot de passe d'un utilisateur",
     *  statusCodes={
     *     200="Retourné quand le mot de passe a été mis à jour",
     *     404="Retourné quand aucun utilisateur n'est associé à l'addresse email fourni"
     *  },
     *  parameters={
     *     {"name"="email", "dataType"="string", "required"=true, "description"="Adresse email de l'utilisateur"},
     *     {"name"="password", "dataType"="string", "required"=true, "description"="Nouveau mot de passe de l'utilisateur"}
     *  }
     * )
     */
    public function resetPasswordAction(Request $request)
    {
        $datas = json_decode($request->getContent());

        $email = $datas->email;
        $password = $datas->password;

        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $em->getRepository('AppBundle:User')->findOneBy(['email' => $email]);

        if(is_object($user))
        {
            $user->setConfirmPassword(hash('sha256',$password));
            $user->setPassword(FunglobeUtils::encodePassword($this->container, new User(), $password, $user->getSalt()));
            $em->flush();
        }
        else
        {
            return $this->json(["No user found with the email provided !"], 404);
        }

        return $this->json(['The password updated succesfully !'], 200);
    }
}