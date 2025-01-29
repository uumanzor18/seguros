<?php

require('conexion.php');
require 'vendor/autoload.php';

use Lcobucci\JWT\Configuration as JwtConfiguration;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use DocuSign\eSign\Client\ApiClient;
use DocuSign\eSign\Configuration as DocuSignConfiguration;
use DocuSign\eSign\Api\AuthenticationApi;
use DocuSign\eSign\Model\LoginInformation;
use DocuSign\eSign\Api\EnvelopesApi;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\Tabs;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Recipients;
use GuzzleHttp\Client;

session_start();


if (isset($_GET['code'])) {
    $code = $_GET['code'];
    $curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://account-d.docusign.com/oauth/token',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => 'code='.$code.'&grant_type=authorization_code',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Basic uzziel.umanzor15@gmail.com',
    'Content-Type: application/x-www-form-urlencoded'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;
} else {
    echo "Código de autorización no encontrado.";
}



/*$pdfContent = $pdf->Output("","S");

unlink($file);
$mail = new PHPMailer(true);

try {
    // Configura el servidor SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'uzziel.umanzor15@gmail.com';
    $mail->Password = 'bbsrcifkvdojnhas';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // Configura el remitente y el destinatario
    $mail->setFrom('uzziel.umanzor15@gmail.com', 'Remitente');
    //$mail->addAddress('reclamos@wecarelatam.com', 'Destinatario');
    $mail->addAddress('uzzilou@gmail.com', 'Destinatario');

    // Configura el contenido del correo
    $mail->isHTML(true);
    $mail->Subject = 'Asunto del correo';
    $mail->Body = 'Este es el cuerpo del correo.';
    $mail->addStringAttachment($pdfContent, $primerNombreAsegurado.' '.$primerApellidoAsegurado.'.pdf', 'base64', 'application/pdf');
    if (isset($_FILES['archivo']) && $_FILES['archivo']['size'] > 0) {
        $pdf_temp = $_FILES['archivo']['tmp_name'];
        $mail->addAttachment($pdf_temp, $numeroIdentificacionAsegurado.'.pdf');
    }
    
    $mail->send();

    $parametro = "1";
    $conn->close();
    header("Location: index.php?rest=$parametro");
    exit;
} catch (Exception $e) {
    writeLog($mail->ErrorInfo);
    $parametro = "1";
    $conn->close();
    header("Location: index.php?rest=$parametro");
}*/
//$pdf->Output('F',__DIR__."/test.pdf");
?>
