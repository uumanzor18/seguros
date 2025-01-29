<?php

/*return [
    'integrator_key' => '617ebc1a-2ff1-43c9-aece-9a0b6c108f3f',
    'secret_key' => '557dbb58-ed76-447a-9ca8-c9425dd755f5',
    'username' => 'uzziel.umanzor15@gmail.com',
    'password' => 'holaMundo1987',
    'account_id' => '88b10d26-60c5-403d-9eb5-5db89b84d8c9',
    'base_path' => 'https://demo.docusign.net/restapi'
];*/

$JWT_CONFIG = [
    'ds_client_id' => '617ebc1a-2ff1-43c9-aece-9a0b6c108f3f', // The app's DocuSign integration key
    'authorization_server' => 'account-d.docusign.com',
    "ds_impersonated_user_id" => '833de776-d232-4e04-9e1d-ae607d9877be',  // the id of the user
    "private_key_file" => "config/private.key", // path to private key file
];

$DS_CONFIG = [
    'demo_doc_path' => '/../../'.'../../tmp/',
    'doc_docx' => 'World_Wide_Corp_Battle_Plan_Trafalgar.docx',
    'doc_pdf' =>  'World_Wide_Corp_lorem.pdf',
    'doc_txt' =>  'Check_If_Approved.txt',
    'quickACG' => '{QUICK_ACG_VALUE}',
    "CodeExamplesManifest" => "https://raw.githubusercontent.com/docusign/code-examples-csharp/master/manifest/CodeExamplesManifest.json"

];
$GLOBALS['DS_CONFIG'] = $DS_CONFIG;
$GLOBALS['JWT_CONFIG'] = $JWT_CONFIG;