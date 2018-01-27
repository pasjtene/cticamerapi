<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 6/22/17
 * Time: 7:19 AM
 */

namespace AppBundle\Tools;


use Symfony\Component\DependencyInjection\ContainerInterface;

class FunglobeUtils
{
    /**
     * Encoder le mot de passe de l'utilisateur
     *
     * @param ContainerInterface $container
     * @param object $object
     * @param string $password
     * @param string $salt
     * @return string
     */
    public static function encodePassword($container, $object, $password, $salt)
    {
        $factory = $container->get('security.encoder_factory');
        $encoder = $factory->getEncoder($object);
        $password = $encoder->encodePassword($password, $salt);

        return $password;
    }

    /**
     * @param mixed $mailer
     * @param array $receivers
     * @param string $sender
     * @param string $view
     * @param string $subject
     *
     * @return bool
     */
    public static function sendMail($mailer, $receivers, $sender, $view, $subject)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($sender)
            ->setTo($receivers)
            ->setBody($view)
            ->setContentType('text/html');

        return $mailer->send($message);

    }

    /**
     * @param array $array
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public static function paginate($array, $limit, $offset)
    {
        $result = [];
        $length = $offset +  $limit;

        for($i = $offset; $i < $length; $i++)
        {
            if(isset($array[$i])){
                $result[] = $array[$i];
            }
            else{
                //dès qu'un élément n'existe pas ca implique les éléments suivants n'existeront donc on arrête le parcours
                break;
            }
        }

        return $result;
    }
}