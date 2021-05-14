<?php

require_once Config::getData('path') . '/library/_lib/phpmailer/class.phpmailer.php';

class sendMailUtil {

    public static function send($to, $toName, $subject, $text, $anexo = false) {
        $mail = new PHPMailer();
        $mail->IsSMTP(); // Define que a mensagem será SMTP
        $mail->Host = Config::getData('sendMail', 'host'); //, $key2)"smtp.gmail.com"; // Endereço do servidor SMTP
        $mail->SMTPAuth = true; // Autenticação
        $mail->SMTPDebug = 1;
        $mail->Username = Config::getData('sendMail', 'username'); //'palavravivaoficial@gmail.com'; // Usuário do servidor SMTP
        $mail->Password = Config::getData('sendMail', 'password'); //'jefe2604'; // Senha da caixa postal utilizada
        $mail->Port = Config::getData('sendMail', 'port'); //465;
        $mail->SMTPSecure = Config::getData('sendMail', 'smtpSecure'); //'ssl';
        $mail->Subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

        $mail->SetFrom($mail->Username, (string) Config::getData('sendMail', 'usertxt'));

        //Define os destinatário(s)
        if (is_array($to)) {
            foreach ($to as $toName => $to) {
                $mail->AddAddress($to, $toName);
            }
        } else {
            $mail->AddAddress($to, $toName);
        }

        //Define os destinatário(s)
        //$mail->AddAddress($to, $toName);
        //$mail->AddAddress('e-mail@destino2.com.br');
        //$mail->AddBCC('cristofer.batschauer@gmail.com', 'Cristofer Batschauer');
        //Define os dados técnicos da Mensagem
        $mail->IsHTML(true); // Define que o e-mail será enviado como HTML
        $mail->CharSet = 'UTF-8'; // Charset da mensagem (opcional)
        //Texto e Assunto

        $mail->Body = $text;
        $mail->AltBody = $text;


        //Anexos (opcional)
        if ($anexo) {
            $mail->AddAttachment($anexo); //, "novo_nome.pdf");
        }

        //Envio da Mensagem
        $enviado = $mail->Send();

        // salva email enviado para futura auditoria
        $dados = array('para' => $to, 'nome' => $toName, 'assunto' => $subject, 'error' => $mail->ErrorInfo);
        Log::log('sendmail', json_encode($dados));


        //Limpa os destinatários e os anexos
        $mail->ClearAllRecipients();
        $mail->ClearAttachments();

        //Exibe uma mensagem de resultado
        if ($enviado) {
            return true;
        } else {
            return $mail->ErrorInfo;
        }
    }

}
