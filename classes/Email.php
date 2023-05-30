<?php

namespace Classes;

use PHPMailer\PHPMailer\PHPMailer;

class Email {
    protected $email;
    protected $nombre;
    protected $token;

    public function __construct($email, $nombre, $token)
    {
        $this->email = $email;
        $this->nombre = $nombre;
        $this->token = $token;
    }

    public function enviarConfirmacion(){
        
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host='smtp.gmail.com';
        $mail->Port=587;
        $mail->SMTPAuth=true;
        $mail->SMTPSecure='tls';
        $mail->Username=''; /* Desde que correo */
        $mail->Password='';  /* Contraseña del correo */

        $mail->setFrom('',''); /* Desde Donde se envia */
        $mail->addAddress($this->email); /* Destinatario */
        $mail->addReplyTo('',''); /* Hacia donde */

        $mail->isHTML(true);
        $mail->Subject = 'Confirma tu Cuenta en UpTask';

        $contenido = '<html>';
        $contenido .= "<p><strong>Hola " . $this->email . "</strong> Has Creado tu cuenta en UpTask, solo debes confirmarla en el siguiente enlace</p>";
        $contenido .= "<a>Presiona aquí: <a href='http://localhost:3000/confirmar?token=" . $this->token ."'>Confirmar Cuenta</a></p>";
        $contenido .= "<p>Si tu no creaste esta cuenta, puedes ignorar este mensaje</p>";
        $contenido .= '</html>';

        $mail->Body = $contenido;

        //Enviamos el Email
        $mail->send();
    }

    public function enviarReestablecer(){

        $mail = new PHPMailer();

        $mail->isSMTP();
        $mail->Host = 'smtp.mailtrap.io';
        $mail->SMTPAuth = true;
        $mail->Port = 2525;
        $mail->Username = '';
        $mail->Password = '';

        $mail->setFrom('');
        $mail->addAddress('', '');
        $mail->Subject = 'Reestablece tu Contraseña';

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';

        $contenido = '<html>';
        $contenido .= "<p><strong>Hola " . $this->email . "</strong> Parace que has olvidado tu contraseña, sigue el siguiente enlace para recuperarla</p>";
        $contenido .= "<a>Presiona aquí para reestablecer: <a href='http://localhost:3000/reestablecer?token=" . $this->token ."'>Reestablecer Contraseña</a></p>";
        $contenido .= '</html>';

        $mail->Body = $contenido;

        //Enviamos el Email
        $mail->send();
    }
}