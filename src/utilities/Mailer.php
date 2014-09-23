<?php
trait Mailer{

    private static $smtp = null;
    private static $sender = null;

    public static function init(){
        static::$smtp = true;
        require_once 'Master/base/libraries/Emails/Swift/swift_required.php';
        require_once 'Master/base/libraries/Emails/Swift/swift_init.php';
    }

    private static function _send($email, $name='', $subject='', $body='', $type=0){
        if(!static::$smtp){
            static::init();
        }

        //$body.='<br><br>Have a question or feedback? Just reply to this email!';

        $mcs = (array) Config::get('mails');
        if(count($mcs)===1){
            $config = (object) $mcs[0];
        }
        else{
            $config = (object) $mcs[$type];
        }

        $transport = Swift_SmtpTransport::newInstance($config->server, $config->port);
        //$transport->setEncryption($config->encrypt);
        $transport->setUsername($config->username);
        $transport->setPassword($config->password);

        $mailer = Swift_Mailer::newInstance($transport);

        $message = Swift_Message::newInstance();
        $message->setCharset('utf-8');
        $message->setPriority(2);
        $message->setSubject($subject);
        $message->setBody($body);
        $message->addPart($body, 'text/html');
        $message->setFrom($config->from['email'], $config->from['sender']);
        $message->setTo(array($email => $name));

        $mailer->send($message);

    }

    public static function sendSupport($email, $name='', $subject='', $body=''){
        return static::_send($email, $name, $subject, $body, 2);
    }

    public static function sendOnly($email, $name='', $subject='', $body=''){
        return static::_send($email, $name, $subject, $body, 1);
    }

    public static function send($email, $name='', $subject='', $body=''){
        return static::_send($email, $name, $subject, $body, 0);
    }

    public static function share($sender, $recipient, $subject='', $body=''){
        if(!static::$smtp){
            static::init();
        }
        $mcs = (array) Config::get('mails');

        if(count($mcs)<4){
            $config = (object) $mcs[0];
        }
        else{
            $config = (object) $mcs[3];
        }

        if(!!$config){
            File::write('resources/log.txt', json_encode($config));
            $to = array();
            $ob = (object) $recipient;
            if(!!$ob->name){
                $to[$ob->email] = $ob->name;
            }
            else{
                array_push($to, $ob->email);
            }

            $fromEmail = $config->from['email'];
            $fromName = $config->from['sender'];

                $ob = (object) $sender;
                if(!!$ob->email){
                    //$fromEmail = $ob->email;
                }
                if(!!$ob->name){
                    $fromName = $ob->name;
                }

                $transport = Swift_SmtpTransport::newInstance($config->server, $config->port);
                $transport->setUsername($config->username);
                $transport->setPassword($config->password);

                $mailer = Swift_Mailer::newInstance($transport);

                $message = Swift_Message::newInstance();
                $message->setCharset('utf-8');
                $message->setPriority(2);
                $message->setSubject($subject);
                $message->setBody($body);
                $message->addPart($body, 'text/html');
                $message->setFrom($fromEmail, $fromName);
                $message->setTo($to);

                $mailer->send($message);

        }
    }
}
