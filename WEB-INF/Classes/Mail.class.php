<?php

include("lib/swift_required.php");
include_once("Catalogo.class.php");
include_once("ParametroGlobal.class.php");

/**
 * Description of Mail
 *
 * @author MAGG
 */
class Mail {

    private $from;
    private $to;
    private $subject;
    private $body;
    private $attachPDF;
    private $attachXML;
    private $empresa;

    function enviarMail() {
        try{
            $transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465,'ssl')
            // $transport = Swift_SmtpTransport::newInstance('localhost', 587)
                ->setUsername('ara@techra.com.mx')
                ->setPassword('5c9S411d4s!');
        }catch(ErrorException $e){
            echo "<br/>No se pudo establecer los datos del SMTP 1: $e";
        }

        //Creamos el mailer pasándole el transport con la configuración de gmail
        try{
            $mailer = Swift_Mailer::newInstance($transport);
            Swift_Preferences::getInstance()->setCharset('utf-8');
            //Creamos el mensaje
            $message = Swift_Message::newInstance($this->subject)
                ->setFrom($this->from)
                ->setTo($this->to)
                ->setBody($this->body, 'text/html', 'utf-8')
                ->setCharset('utf-8');        
        }catch(Exception $e){
            echo "<br/>No se pudo crear el mailer 1: ".$e;
        }
        
        //Enviamos
        try {
            $result = $mailer->send($message);
        } catch (Swift_TransportException $STe) {
            echo "<br/>No se pudo enviar el correo 1: ".$STe;
            $result = "0";
        }
        
        /*******        Cuenta de respaldo      **********/
        if ($result == "0") { /* No se pudo enviar el correo con la primer cuenta, intentamos con la cuenta de respaldo */
            try{
                $transport = Swift_SmtpTransport::newInstance('localhost', 587)
                    ->setUsername('ara@techra.com.mx')
                    ->setPassword('5c9S411d4s!');
            }catch(Exception $e){
                echo "<br/>No se pudo establecer los datos del SMTP 2: "+$e;
            }
            //Creamos el mailer pasándole el transport con la configuración de gmail
            try{
                $mailer = Swift_Mailer::newInstance($transport);
                Swift_Preferences::getInstance()->setCharset('utf-8');
                //Creamos el mensaje
                $message = Swift_Message::newInstance($this->subject)
                    ->setFrom($this->from)
                    ->setTo($this->to)
                    ->setBody($this->body, 'text/html', 'utf-8')
                    ->setCharset('utf-8');        
            }catch(Exception $e){
                echo "<br/>No se pudo crear el mailer 2: ".$e;
            }
            
            //Enviamos             
            try {
                $result = $mailer->send($message);
            } catch (Swift_TransportException $STe) {
                echo "<br/>No se pudo enviar el correo 2: ".$STe;
                $result = "0";
            }
        }
        return $result;
    }

    function enviarMailPDF() {
        $transport = Swift_SmtpTransport::newInstance('localhost', 587)
                ->setUsername('ara@techra.com.mx')
                ->setPassword('5c9S411d4s!');
        //Creamos el mailer pasándole el transport con la configuración de gmail
        $mailer = Swift_Mailer::newInstance($transport);
        Swift_Preferences::getInstance()->setCharset('utf-8');
        //Creamos el mensaje
        if ($this->attachXML != "") {
            $message = Swift_Message::newInstance($this->subject)
                    ->setFrom($this->from)
                    ->setTo($this->to)
                    ->setBody($this->body, 'text/html', 'utf-8')
                    ->setCharset('utf-8')
                    ->attach(Swift_Attachment::fromPath($this->attachPDF))
                    ->attach(Swift_Attachment::fromPath($this->attachXML));
        } else {
            $message = Swift_Message::newInstance($this->subject)
                    ->setFrom($this->from)
                    ->setTo($this->to)
                    ->setBody($this->body, 'text/html', 'utf-8')
                    ->setCharset('utf-8')
                    ->attach(Swift_Attachment::fromPath($this->attachPDF));
        }
        //Enviamos
        $result = $mailer->send($message);
        
        /*         * *****        Cuenta de respaldo      ********* */
        if ($result == "0") { /* No se pudo enviar el correo con la primer cuenta, intentamos con la cuenta de respaldo */
            $transport = Swift_SmtpTransport::newInstance('localhost', 587)
                    ->setUsername('ara@techra.com.mx')
                    ->setPassword('5c9S411d4s!');
            //Creamos el mailer pasándole el transport con la configuración de gmail
            $mailer = Swift_Mailer::newInstance($transport);
            Swift_Preferences::getInstance()->setCharset('utf-8');
            //Creamos el mensaje
            $message = Swift_Message::newInstance($this->subject)
                    ->setFrom($this->from)
                    ->setTo($this->to)
                    ->setBody($this->body, 'text/html', 'utf-8')
                    ->setCharset('utf-8')
                    ->attach(Swift_Attachment::fromPath($this->attachPDF))
                    ->attach(Swift_Attachment::fromPath($this->attachXML));
            //Enviamos
            $result = $mailer->send($message);
        }
        return $result;
    }

    public function getAttachPDF() {
        return $this->attachPDF;
    }

    public function getAttachXML() {
        return $this->attachXML;
    }

    public function setAttachPDF($attachPDF) {
        $this->attachPDF = $attachPDF;
    }

    public function setAttachXML($attachXML) {
        $this->attachXML = $attachXML;
    }

    public function getFrom() {
        return $this->from;
    }

    public function setFrom($from) {
        $this->from = $from;
    }

    public function getTo() {
        return $this->to;
    }

    public function setTo($to) {
        $this->to = $to;
    }

    public function getSubject() {
        return $this->subject;
    }

    public function setSubject($subject) {
        $this->subject = $subject;
    }

    public function getBody() {
        return $this->body;
    }

    public function setBody($body) {
        $this->body = $body;
    }

    function generaPass() {
        //Se define una cadena de caractares. Te recomiendo que uses esta.
        $cadena = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
        //Obtenemos la longitud de la cadena de caracteres
        $longitudCadena = strlen($cadena);

        //Se define la variable que va a contener la contraseña
        $pass = "";
        //Se define la longitud de la contraseña, en mi caso 10, pero puedes poner la longitud que quieras
        $longitudPass = 12;

        //Creamos la contraseña
        for ($i = 1; $i <= $longitudPass; $i++) {
            //Definimos numero aleatorio entre 0 y la longitud de la cadena de caracteres-1
            $pos = rand(0, $longitudCadena - 1);

            //Vamos formando la contraseña en cada iteraccion del bucle, añadiendo a la cadena $pass la letra correspondiente a la posicion $pos en la cadena de caracteres definida.
            $pass .= substr($cadena, $pos, 1);
        }
        return $pass;
    }

    /**
     * Funcion para saber si el id de correo y la clave existen y aun no han sido contestados.
     * @param type $id id del correo enviado
     * @param type $clave clave enviada
     * @return boolean true en caso de existir y no ser contestada, false en caso contrario
     */
    function getClaveGeneralByIDClave($id, $clave) {
        $consulta = ("SELECT * FROM `c_mailgeneral` WHERE id_mail = $id AND clave = MD5('$clave') AND contestada = 0;");
        $catalogo = new Catalogo(); 
        if(isset($this->empresa)){
            $catalogo->setEmpresa($this->empresa);            
        }
        $query = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($query) > 0) {            
            return true;
        }        
        return false;
    }

    /**
     * Marca el mail especificado como leido para que no se pueda volver a usar.
     * @param type $id id del mail a cambiar
     * @return boolean true en caso de poder hacer el cambio, false en caso contrario.
     */
    function marcarContestado($id) {
        $consulta = ("UPDATE `c_mailgeneral` SET contestada = 1 WHERE id_mail = $id;");
        $catalogo = new Catalogo(); 
        if(isset($this->empresa)){
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($query == "1") {            
            return true;
        }        
        return false;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }


}

?>
