<?php

/**
 * Created by PhpStorm.
 * User: c13303
 * Date: 11/07/16
 * Time: 18:22
 * crontab EX : 0 9 * * * sleep $((3600 * (RANDOM % 12))); /usr/bin/php /home/charles/mamiemail/mamiemail.php > log.txt
 */
error_reporting(E_ALL);
error_reporting(0);

require('PHPMailer-master/PHPMailerAutoload.php');
require('params.php');
global $conn;

function get_word($conn) {

    $word = $id = null;
    $words = $conn->query('SELECT id,word FROM words WHERE done!=1 ORDER BY rand() LIMIT 0,1 ');
    while ($ligne = $words->fetch_assoc()) {
        $word = $ligne['word'];
        $id = $ligne['id'];
    }
    if (!$id)
        return (null);
    $conn->query('UPDATE words SET `done`=1 WHERE id =' . $id);
    return($word);
}

$heuredujour = array();
$sent = array();



$last_now = '';

while (1) {
    $today = date('Y-m-d');

    if (empty($heuredujour[$today])) {
        $H = rand(6, 23);
        $M = rand(0, 59);

        if ($H < 10) {
            $H = '0' . $H;
        }
        if ($M < 10) {
            $M = '0' . $M;
        }
        $heuredujour[$today] = $H . ':' . $M;
        echo 'New Day ! ' . $today . ' - Heure du jour : ' . $heuredujour[$today] . '
       ';
    }

    if ($heuredujour[$today]) {
        $conn = mysqli_connect($db['host'], $db['user'], $db['pass'], $db['db']);


        $now = date('H:i');
        $last_now = $now;


        if ($now >= $heuredujour[$today] && empty($sent[$today])) {
            $word = get_word($conn);
            if (!$word) {
                $mail = new PHPMailer;
                $mail->isSMTP();                                      // Set mailer to use SMTP
                $mail->Host = $params['host'];  // Specify main and backup SMTP servers
                $mail->SMTPAuth = true;                               // Enable SMTP authentication
                $mail->Username = $params['senderaccount'];                 // SMTP username
                $mail->Password = $params['senderpass'];                           // SMTP password
                $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
                $mail->Port = 587;
                $mail->setFrom($params['sender'], 'Mamie Email');
                $mail->addAddress($params['sender']);
                $mail->isHTML(true);
                $mail->Subject = 'Mamie Mail : database is empty';
                $mail->Body = 'You need to flush the database of daily mail, or reload it';
                echo PHP_EOL . 'ERROR  : ' . date('d/m/Y') . ' : ';
                $mail->send();
                $conn->close();
                die();
            }

            if (!$word)
                die(PHP_EOL . 'word error missing' . PHP_EOL);

            $sent[$today] = $word;

            $mail = new PHPMailer;

//$mail->SMTPDebug = 3;                               // Enable verbose debug output

            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = $params['host'];  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = $params['senderaccount'];                 // SMTP username
            $mail->Password = $params['senderpass'];                           // SMTP password
            $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587;                                    // TCP port to connect to
//$mail->SMTPDebug = 1;
            $mail->setFrom($params['sender'], $params['sendername']);
            $mail->addAddress($params['dest']);     // Add a recipient
            $mail->isHTML(true);                                  // Set email format to HTML

            $de = rand(0, 3);
            switch ($de) {
                case 0:
                    $presujet = 'bonjour ';
                    break;
                case 1:
                    $presujet = 'bisous ';
                    break;
                case 2:
                    $presujet = '';
                    break;
            }
            
             $de = rand(0, 3);
            switch ($de) {
                case 0:
                    $presujet .= ' mamie';
                    break;
                case 1:
                    $presujet .= ' ';
                    break;
                case 2:
                    $presujet .= ' ';
                    break;
            }

            $sujet = explode(' ', $word);
            $suj = $sujet[0];
            $suj .= $sujet[1] ? $sujet[1] : '';
            $mail->Subject = $presujet . $sujet[0];
            $mail->Body = $word;

            echo PHP_EOL . 'Sending : ' . date('d/m/Y') . ' : ';
            if (!$mail->send()) {
                echo PHP_EOL . 'Message could not be sent.';
                echo PHP_EOL . 'Mailer Error: ' . $mail->ErrorInfo;
            } else {
                // echo PHP_EOL . 'Message has been sent ' . $word;
            }
            echo $word . PHP_EOL;

            $conn->close();
        }
    }
}   