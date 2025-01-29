<?php

namespace DocuSign\Services\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\CarbonCopy;
use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\Notification;
use DocuSign\eSign\Model\Tabs;

class SigningViaEmailService
{
    /**
     * Do the work of the example
     * 1. Create the envelope request object
     * 2. Send the envelope
     *
     * @param  $args array
     * @param $clientService
     * @param $demoDocsPath
     * @return array ['redirect_url']
     */
    # ***DS.snippet.0.start
    public static function signingViaEmail(array $args, $clientService, $pdfFile, $name): array
    {
        #ds-snippet-start:eSign2Step3
        # Create the envelope request object
        $envelope_definition = SigningViaEmailService::makeEnvelope(
            $args["envelope_args"],
            $pdfFile,
            $name
        );
        $envelope_api = $clientService->getEnvelopeApi();

        # Call Envelopes::create API method
        # Exceptions will be caught by the calling function
        try {
            $envelopeResponse = $envelope_api->createEnvelope($args['account_id'], $envelope_definition);
        } catch (ApiException $e) {
            // if you modify this code and are using the the JWT Conole App, uncomment
            // the following line to debug issues for easier visibility in the console
            // var_dump($e);
            
            $clientService->showErrorTemplate($e);
            exit;
        }

        return ['envelope_id' => $envelopeResponse->getEnvelopeId()];
        #ds-snippet-end:eSign2Step3
    }

    /**
     * Creates envelope definition
     * Document 1: An HTML document.
     * Document 2: A Word .docx document.
     * Document 3: A PDF document.
     * DocuSign will convert all of the documents to the PDF format.
     * The recipients' field tags are placed using <b>anchor</b> strings.
     *
     * Parameters for the envelope: signer_email, signer_name, signer_client_id
     *
     * @param  $args array
     * @param $clientService
     * @param $demoDocsPath
     * @return EnvelopeDefinition -- returns an envelope definition
     */

    #ds-snippet-start:eSign2Step2
    public static function makeEnvelope(array $args, $pdfFile, $name): EnvelopeDefinition
    {
        # The envelope has two recipients.
        # recipient 1 - signer
        # recipient 2 - cc
        # The envelope will be sent first to the signer.
        # After it is signed, a copy is sent to the cc person.
        #
        # create the envelope definition
        $notification = new Notification([
            'use_account_defaults' => 'false', // Si se debe sobrescribir la configuración por defecto
            'completed_subject' => 'Tu solicitud se ha enviado correctamente.', // Asunto del correo de completado
            //'completed_message' => 'Tu documento ha sido completado y ya está disponible para su descarga.' // Mensaje personalizado (opcional)
        ]);
        $envelope_definition = new EnvelopeDefinition([
          'email_subject' => 'Completa el proceso de inscripción con tu firma',
          'notification' => $notification
        ]);

        $doc3_b64 = base64_encode($pdfFile);

        $document = new Document([  # create the DocuSign document object
           'document_base64' => $doc3_b64,
           'name' => $name.'.pdf',  # can be different from actual file name
           'file_extension' => 'pdf',  # many different document types are accepted
           'document_id' => '1'  # a label used to reference the doc
        ]);
        # The order in the docs array determines the order in the envelope
        $envelope_definition->setDocuments([$document]);
        

        # Create the signer recipient model
        $signer1 = new Signer([
           'email' => $args['signer_email'], 'name' => $args['signer_name'],
           'recipient_id' => "1", 'routing_order' => "1"]);
        # routingOrder (lower means earlier) determines the order of deliveries
        # to the recipients. Parallel routing order is supported by using the
        # same integer as the order for two or more recipients.

        # create a cc recipient to receive a copy of the documents
        $cc1 = new CarbonCopy([
           'email' => $args['cc_email'], 'name' => $args['cc_name'],
           'recipient_id' => "2", 'routing_order' => "2"]);

        return SMSDeliveryService::addSignersToTheDelivery($signer1, $cc1, $envelope_definition, $args);
    }
    #ds-snippet-end:eSign2Step2
}
