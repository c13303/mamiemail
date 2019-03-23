<?php

/**
 * Created by PhpStorm.
 * User: c13303
 * Date: 11/07/16
 * Time: 18:22
 * crontab EX : 0 9 * * * sleep $((3600 * (RANDOM % 12))); /usr/bin/php /home/charles/mamiemail/mamiemail.php > log.txt
 */
error_reporting(E_ALL);

require('PHPMailer-master/PHPMailerAutoload.php');
require('params.php');
global $conn;
$conn = mysqli_connect($db['host'], $db['user'], $db['pass'], $db['db']);

function get_word() {
    require('params.php');
    global $conn;
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

$word = get_word();

if (!$word) { // reset done
    /* $conn->query('UPDATE words SET done = 0');   
      echo PHP_EOL.'flush!';
      $word = get_word();
     * 
     */

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
    $mail->setFrom($params['sender'], 'Mamie Email');
    $mail->addAddress($params['sender']);
    $mail->isHTML(true);

    $mail->Subject = 'Mamie Mail : database is empty';
    $mail->Body = 'You need to flush the database of daily mail, or reload it';
    echo PHP_EOL . 'ERROR  : ' . date('d/m/Y') . ' : ';
    if (!$mail->send()) {
        echo PHP_EOL . 'Message could not be sent.';
        echo PHP_EOL . 'Mailer Error: ' . $mail->ErrorInfo;
    } else {
        // echo PHP_EOL . 'Message has been sent ' . $word;
    }
    echo $word;

    $conn->close();
    die();
}

if (!$word)
    die(PHP_EOL . 'word error missing' . PHP_EOL);


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

$mail->Subject = 'Re: Bisou du jour';
$mail->Body = $word;

echo PHP_EOL . 'Sending : ' . date('d/m/Y') . ' : ';
if (!$mail->send()) {
    echo PHP_EOL . 'Message could not be sent.';
    echo PHP_EOL . 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    // echo PHP_EOL . 'Message has been sent ' . $word;
}
echo $word;

$conn->close();
