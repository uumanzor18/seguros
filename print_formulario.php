<?php
//header('Content-Type: text/html; charset=UTF-8');

use DocuSign\Services\SignatureClientService;
use DocuSign\Services\Examples\eSignature\SigningViaEmailService;

use DocuSign\eSign\Client\ApiClient;
use DocuSign\eSign\Model\Notification;

require('conexion.php');
require_once('fpdf/fpdf.php');
require 'vendor/autoload.php';
//include_once('src/Services/SignatureClientService.php');
require 'config/config.php';
$conn->set_charset("utf8");
$integration_key = $GLOBALS['JWT_CONFIG']['ds_client_id'];
$impersonatedUserId = $GLOBALS['JWT_CONFIG']['ds_impersonated_user_id'];
$scopes = "signature impersonation";
$formularioId = null;

set_error_handler(
    function ($severity, $message, $file, $line) {
        writeLog("Error: ".$message.", nivel: ".$severity.", archivo: ".$file.", linea: ".$line);
        throw new ErrorException($message, $severity, $severity, $file, $line);
    }
);

function validar_input($key, $default = null) {
    return isset($_POST[$key]) && !empty(trim($_POST[$key])) ? $_POST[$key] : $default;
}

function validar_numero($key, $default = null) {
    return isset($_POST[$key]) && is_numeric($_POST[$key]) ? floatval($_POST[$key]) : $default;
}

function validar_entero($key, $default = null) {
    return isset($_POST[$key]) && is_numeric($_POST[$key]) ? intval($_POST[$key]) : $default;
}

function validar_nulo($key, $default = "") {
    return $key == null ? $default : $key;
}

function calcularEdad($fecha_nacimiento) {
    $fecha_nac = new DateTime($fecha_nacimiento);
    $hoy = new DateTime(); // Fecha actual
    $edad = $hoy->diff($fecha_nac);
    return $edad->y; // Retorna solo los años
}

function generarTablaPregunta($pdf, $numero, $descripcion, $respuestas) {
    $obj = null;
    foreach ($respuestas as $respuesta) {
        if ((string)$respuesta->id === (string)$numero) {
            $obj = $respuesta;
            break;
        }
    }



    // Evita posibles errores de acceso a propiedades inexistentes
    $nombre = isset($obj->nombre) ? $obj->nombre : '';
    $diagnostico = isset($obj->diagnostico) ? $obj->diagnostico : '';
    $tratamiento = isset($obj->tratamiento) ? $obj->tratamiento : '';
    $fecha = (!empty($obj->fecha) && strtotime($obj->fecha) !== false) ? date("d/m/Y", strtotime($obj->fecha)) : '';
    $medico = isset($obj->medico) ? $obj->medico : '';

    $pdf->SetFont('Arial', '', 9);
    $tempY = $pdf->GetY();

    // Descripción con salto de línea
    $pdf->MultiCell(156, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', "$numero. $descripcion"), 1, 'L');
    $altura = $pdf->GetY() - $tempY;

    writeLog("inicio: ".$tempY." fin: ".$pdf->GetY()." altura: ".$altura);

    // Primera columna de selección
    $pdf->SetY($tempY);
    $pdf->SetX(166);
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->MultiCell(20, $altura, '', 1, 0, 'L', true);
    $pdf->Text($x+9, $y+4, ($obj !== null) ? '[x]' : '[ ]');

    // Segunda columna de selección
    $pdf->SetY($tempY);
    $pdf->SetX(186);
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->MultiCell(20, $altura, '', 1, 1, 'L', true);
    $pdf->Text($x+9, $y+4, ($obj === null) ? '[x]' : '[ ]');

    // Tabla de detalles
    $pdf->Cell(196, 5, 'Para quien es afirmativa la respuesta detalle', 1, 1, 'L');
    $pdf->Cell(60, 5, 'Nombre', 1, 0, 'L');
    $pdf->Cell(30, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Diagnóstico'), 1, 0, 'L');
    $pdf->Cell(40, 5, 'Tratamiento', 1, 0, 'L');
    $pdf->Cell(20, 5, 'Fecha', 1, 0, 'L');
    $pdf->Cell(46, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Médico u hospital'), 1, 1, 'L');

    // Celdas con datos
    $pdf->Cell(60, 5, $nombre, 1, 0, 'L', true);
    $pdf->Cell(30, 5, $diagnostico, 1, 0, 'L', true);
    $pdf->Cell(40, 5, $tratamiento, 1, 0, 'L', true);
    $pdf->Cell(20, 5, $fecha, 1, 0, 'L', true);
    $pdf->Cell(46, 5, $medico, 1, 1, 'L', true);

    if ($numero == 7) {
        $pdf->AddPage('P', [215.9, 279.4]);
    }
    elseif ($numero == 18) {
        $pdf->AddPage('P', [215.9, 279.4]);
    }
}



//Seccion 1
// Definir la consulta SQL
$sql = "INSERT INTO formulario(
    codigo_agente, nombre_agente, nombre_contratante, numero_poliza, primer_nombre,
    segundo_nombre, primer_apellido, segundo_apellido, tipo_identificacion, numero_identificacion,
    rtn, fecha_nacimiento, lugar_nacimiento, genero, estado_civil, 
    nacionalidad_1, nacionalidad_2, estatura, peso, celular_1, 
    celular_2, email_1, email_2, profesion, area, 
    cargo, fecha_ingreso, moneda_remuneracion, remuneracion, cargo_publico, 
    nombre_cargo_publico, pais, departamento, municipio, colonia, 
    casa, calle, avenida, telefono_residencia)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

// Preparar la conexión
if ($stmt = $conn->prepare($sql)) {
    // Asignar valores
    $codigo_agente = "3109";
    $nombre_agente = "Correduria de Seguros y Fianzas We Care";
    $nombre_contratante = "The Workloop S.A.";
    $numero_poliza = "2222";
    $primer_nombre = validar_input("primerNombreAsegurado");
    $segundo_nombre = validar_input("segundoNombreAsegurado");
    $primer_apellido = validar_input("primerApellidoAsegurado");
    $segundo_apellido = validar_input("segundoApellidoAsegurado");
    $tipo_identificacion = validar_input("tipoIdentificacionAsegurado");
    $numero_identificacion = validar_input("numeroIdentificacionAsegurado");
    $rtn = validar_input("rtnAsegurado");
    $fecha_nacimiento = validar_input("fechaNacimientoAsegurado");
    $lugar_nacimiento = validar_input("lugarNacimientoAsegurado");
    $genero = validar_input("generoAsegurado");
    $estado_civil = validar_input("estadoCivilAsegurado");
    $nacionalidad_1 = validar_input("nacionalidadAsegurado1");
    $nacionalidad_2 = validar_input("nacionalidadAsegurado2");
    $estatura = validar_numero("estaturaAsegurado");
    $peso = validar_numero("pesoAsegurado");
    $celular_1 = validar_input("celularAsegurado1");
    $celular_2 = validar_input("celularAsegurado2");
    $email_1 = validar_input("emailAsegurado1");
    $email_2 = validar_input("emailAsegurado2");
    $nombre_conyugue = validar_input("nombreConyuge");
    $profesion = validar_input("profesionAsegurado");
    $area = validar_input("areaAseguro");
    $cargo = validar_input("cargoAsegurado");
    $fecha_ingreso = validar_input("fechaIngresoAsegurado");
    $moneda_remuneracion = validar_input("monedaRemuneracionAsegurado");
    $remuneracion = validar_numero("remuneracionAsegurado");
    $cargo_publico = validar_entero("cargoPublicoAsegurado"); // Si es booleano, usar 0 o 1
    $nombre_cargo_publico = validar_entero("nombreCargoPublicoAsegurado");
    $pais = validar_input("paisAsegurado");
    $departamento = validar_input("departamentoAsegurado");
    $municipio = validar_input("municipioAsegurado");
    $colonia = validar_input("coloniaAsegurado");
    $casa = validar_input("casaAsegurado");
    $calle = validar_input("calleAsegurado");
    $avenida = validar_input("avenidaAsegurado");
    $telefono_residencia = validar_input("telefonoResidenciaAsegurado");

    // Enlazar los parámetros
    $stmt->bind_param("ssssssssissssssssddsssssssssdssssssisss",
        $codigo_agente, $nombre_agente, $nombre_contratante, $numero_poliza, $primer_nombre,
        $segundo_nombre, $primer_apellido, $segundo_apellido, $tipo_identificacion, $numero_identificacion,
        $rtn, $fecha_nacimiento, $lugar_nacimiento, $genero, $estado_civil,
        $nacionalidad_1, $nacionalidad_2, $estatura, $peso, $celular_1,
        $celular_2, $email_1, $email_2, $profesion, $area,
        $cargo, $fecha_ingreso, $moneda_remuneracion, $remuneracion, $cargo_publico,
        $nombre_cargo_publico, $pais, $departamento, $municipio, $colonia, 
        $casa, $calle, $avenida, $telefono_residencia
    );

    // Ejecutar la consulta
    if (!$stmt->execute()) {
        writeLog("Error al insertar: " . $stmt->error);
    }

    // Cerrar la sentencia
    $formularioId = $stmt->insert_id;
    $stmt->close();
} else {
    writeLog("Error en la preparación de la consulta: " . $conn->error);
}

//Seccion 2
// Definir la consulta SQL
$sql = "INSERT INTO indemnizacion(
    id_formulario, institucion_financiera, tipo_cuenta, numero_cuenta, nombre_menor,
    identidad_menor)
VALUES (?, ?, ?, ?, ?, ?)";

// Preparar la conexión
if ($stmt = $conn->prepare($sql)) {
    // Asignar valores
    $id_formulario = $formularioId;
    $institucion_financiera = validar_numero("institucionFinancieraAsegurado");
    $tipo_cuenta = validar_numero("tipoCuentaAsegurado");
    $numero_cuenta = validar_input("numeroCuentaAsegurado");
    $nombre_menor = validar_input("nombreMenorAsegurado");
    $identidad_menor = validar_input("identidadMenorAsegurado");

    // Enlazar los parámetros
    $stmt->bind_param("iiisss",
        $id_formulario, $institucion_financiera, $tipo_cuenta, $numero_cuenta, $nombre_menor,
        $identidad_menor
    );

    // Ejecutar la consulta
    if (!$stmt->execute()) {
        writeLog("Error al insertar: " . $stmt->error);
    }

    // Cerrar la sentencia
    $stmt->close();
} else {
    writeLog("Error en la preparación de la consulta: " . $conn->error);
}

$select = "SELECT nombre
from instituciones_financieras
where id = ?";
$stmt = $conn->prepare($select);
$stmt->bind_param("i", $institucion_financiera); 
$stmt->execute();
$result = $stmt->get_result();
$nombre_institucion = "";
if ($row = $result->fetch_assoc()) {
    $nombre_institucion = $row["nombre"];
}
$stmt->close();

//Seccion 3
$otro_seguro = validar_numero("otroSeguro");
$tipo_seguro = ""; 
$extra_seguro = ""; 
$tipo_extra_seguro = "";
if ($_POST["otroSeguro"] == "1" || !empty($_POST["extraSeguro"])) {
    $sql = "INSERT INTO otro_seguro(
        id_formulario, otro_seguro, tipo_seguro, extra_seguro, tipo_extra_seguro)
    VALUES (?, ?, ?, ?, ?)";
    
    // Preparar la conexión
    if ($stmt = $conn->prepare($sql)) {
        // Asignar valores
        $id_formulario = $formularioId;
        $tipo_seguro = validar_input("otroTipoSeguro");
        $extra_seguro = validar_input("extraSeguro");
        $tipo_extra_seguro = validar_input("tipoExtraSeguro");
    
        // Enlazar los parámetros
        $stmt->bind_param("iisss",
            $id_formulario, $otro_seguro, $tipo_seguro, $extra_seguro, $tipo_extra_seguro
        );
    
        // Ejecutar la consulta
        if (!$stmt->execute()) {
            writeLog("Error al insertar: " . $stmt->error);
        }
    
        // Cerrar la sentencia
        $stmt->close();
    } else {
        writeLog("Error en la preparación de la consulta: " . $conn->error);
    }
}

//Seccion 4
$beneficiariosSeguro = json_decode($_POST["hdnBeneficiariosSeguro"]);
$sql = "INSERT INTO beneficiario(
formulario_id, nombre, parentesco, porcentaje)
VALUES(?,?,?,?)";
$stmt = $conn->prepare($sql);

foreach ($beneficiariosSeguro as $beneficiario) {
    $stmt->bind_param("isid",
        $formularioId,
        $beneficiario->nombres,
        $beneficiario->parentesco,
        $beneficiario->porcentaje
    );
    $stmt->execute();
}
$stmt->close();

//Seccion 5
$beneficiariosContingencia = json_decode($_POST["hdnBeneficiariosContingencia"]);
$sql = "INSERT INTO beneficiario_contingencia(
formulario_id, nombre, parentesco, porcentaje)
VALUES(?,?,?,?)";
$stmt = $conn->prepare($sql);

foreach ($beneficiariosContingencia as $beneficiario) {
    $stmt->bind_param("isid",
        $formularioId,
        $beneficiario->nombres,
        $beneficiario->parentesco,
        $beneficiario->porcentaje
    );
    $stmt->execute();
}
$stmt->close();

//Seccion 6
$dependientes = json_decode($_POST["hdnDatosDependientes"]);
$sql = "INSERT INTO dependiente(
formulario_id, nombre, genero, parentesco, peso,
estatura, fecha_nacimiento, identidad, ocupacion)
VALUES(?,?,?,?,?,?,?,?,?)";
$stmt = $conn->prepare($sql);
writeLog(print_r($dependientes, true));
foreach ($dependientes as $dependiente) {
    $stmt->bind_param("issiddsss",
        $formularioId,
        $dependiente->nombre,
        $dependiente->sexo,
        $dependiente->parentesco,
        $dependiente->peso,
        $dependiente->estatura,
        $dependiente->fechaNacimiento,
        $dependiente->identidad,
        $dependiente->ocupacion
    );
    $stmt->execute();
}
$stmt->close();

//Seccion 7
$antecedentes = json_decode($_POST["hdnDatosAntecedentes"]);
$sql = "INSERT INTO antecedente(
formulario_id, tipo, nombre, aseguradora, poliza)
VALUES(?,?,?,?,?)";
$stmt = $conn->prepare($sql);

foreach ($antecedentes as $antecedente) {
    $stmt->bind_param("issss",
        $formularioId,
        $antecedente->tipo,
        $antecedente->nombre,
        $antecedente->aseguradora,
        $antecedente->poliza
    );
    $stmt->execute();
}
$stmt->close();

//Seccion 8
$respuestas = json_decode($_POST["hdnRespuestas"]);
$sql = "INSERT INTO respuesta(
pregunta_id, formulario_id, nombre, diagnostico, tratamiento,
fecha, medico, detalle
)
VALUES(?,?,?,?,?,?,?,?)";
$stmt = $conn->prepare($sql);

foreach ($respuestas as $respuesta) {
    $pregunta_id = $respuesta->id;
    $nombre = property_exists($respuesta, 'nombre') ? $respuesta->nombre : null;
    $diagnostico = property_exists($respuesta, 'diagnostico') ? $respuesta->diagnostico : null;
    $tratamiento = property_exists($respuesta, 'tratamiento') ? $respuesta->tratamiento : null;
    $fecha = property_exists($respuesta, 'fecha') ? $respuesta->fecha : null;
    $medico = property_exists($respuesta, 'medico') ? $respuesta->medico : null;
    $detalle = property_exists($respuesta, 'detalle') ? $respuesta->detalle : null;

    $stmt->bind_param("iissssss",
        $pregunta_id,
        $formularioId,
        $nombre,
        $diagnostico,
        $tratamiento,
        $fecha,
        $medico,
        $detalle
    );
    $stmt->execute();
}
$stmt->close();

class PDF extends FPDF {
    function Footer() {
        // Posiciona el footer a 1.5 cm del final de la página
        $this->SetY(-15);
        $this->SetFont('Arial', '', 8);

        // Texto del footer
        $footerText = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Este producto fue autorizado y registrado mediante acuerdo 1568 de la Secretaria de Hacienda y Crédito Público.');

        // Número de página
        $pageNum = 'Pag. ' . $this->PageNo();

        // Determina si la página es par o impar
        if ($this->PageNo() % 2 == 0) {
            // Página par: número de página a la izquierda
            $this->Cell(0, 5, $pageNum, 0, 0, 'L');
            $this->Cell(0, 5, $footerText, 0, 0, 'R');
        } else {
            // Página impar: número de página a la derecha
            $this->Cell(0, 5, $footerText, 0, 0, 'L');
            $this->Cell(0, 5, $pageNum, 0, 0, 'R');
        }
    }
}

$pdf = new PDF();
$pdf->AddPage('P', [215.9, 279.4]);
$pdf->Image('imagenes/logo.png', 10, 6, 50);
$pdf->SetFont('Arial','',14);

// Configurar fuente y agregar texto en la esquina superior derecha
$pdf->SetFont('Arial', '', 10);
$pdf->SetXY(150, 11); // Posición del texto
$pdf->Cell(50, 6, 'HN.F.P.GM2.V2.0', 0, 1, 'R');

// 🔹 Configurar el título en negrita y rojo
$pdf->SetFont('Arial', 'B', 14); // Negrita, tamaño 14
$pdf->SetTextColor(200, 0, 0); // Rojo
$pdf->Cell(0, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'SOLICITUD DE INSCRIPCIÓN PARA SEGURO COLECTIVO DE GASTOS MEDICOS'), 0, 1, 'C');

$pdf->Ln(3);
// 🔹 Configurar el subtítulo en gris
$pdf->SetFont('Arial', '', 12); // Normal, tamaño 12
$pdf->SetTextColor(100, 100, 100); // Gris
$pdf->Cell(0, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'La solicitud deberá ser llenada con letra de molde y sin omitir datos, sin tachaduras, borrones ni manchones.'), 0, 1, 'C');
$pdf->Ln(3);
// 🔹 Encabezado rojo con texto blanco
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(200, 0, 0); // Fondo rojo
$pdf->SetTextColor(255, 255, 255); // Texto blanco
$pdf->Cell(0, 5, 'PARA USO EXCLUSIVO DE MAPFRE', 1, 1, 'L', true);

// 🔹 Configurar fuente y colores para la tabla
$pdf->SetFont('Arial', '', 9);
$pdf->SetFillColor(230, 230, 230); // Fondo gris claro
$pdf->SetTextColor(0, 0, 0); // Texto negro

// 🔹 primera 196 ancho
$pdf->Cell(35, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Cód. de contratante'), 1, 0, 'C');
$pdf->Cell(30, 5, 'No. de solicitud', 1, 0, 'C');
$pdf->Cell(30, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Vinculación'), 1, 0, 'C');
$pdf->Cell(31, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Fecha de emisión'), 1, 0, 'C');
$pdf->Cell(35, 5, 'No. de Certificado', 1, 0, 'C');
$pdf->Cell(35, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Cód. Agente de Venta'), 1, 1, 'C');

// 🔹 Fila vacía para rellenar datos
$pdf->Cell(35, 5, '', 1, 0, 'C', true);
$pdf->Cell(30, 5, '', 1, 0, 'C', true);
$pdf->Cell(30, 5, '', 1, 0, 'C', true);
$pdf->Cell(31, 5, date("d/m/Y"), 1, 0, 'C', true);
$pdf->Cell(35, 5, '', 1, 0, 'C', true);
$pdf->Cell(35, 5, '', 1, 1, 'C', true);

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(200, 0, 0);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(0, 5, 'A. DATOS DEL AGENTE DE SEGURO', 1, 1, 'L', true);

// 🔹 segunda seccion
$pdf->SetFont('Arial', '', 9);
$pdf->SetFillColor(230, 230, 230); // Fondo gris claro
$pdf->SetTextColor(0, 0, 0); // Texto negro
$pdf->Cell(15, 5, 'Agente', 1);
$pdf->Cell(111, 5, $nombre_agente, 1, 0, 'C', true);
$pdf->Cell(35, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Código del agente'), 1);
$pdf->Cell(35, 5, $codigo_agente, 1, 1, 'C', true);

// 🔹 tercera sección en rojo
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(200, 0, 0);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(0, 5, 'I. SOLICITUD DE INSCRIPCION PARA SEGURO COLECTIVO DE GASTOS MEDICOS', 1, 1, 'L', true);

$pdf->SetFont('Arial', '', 9);
$pdf->SetFillColor(230, 230, 230); // Fondo gris claro
$pdf->SetTextColor(0, 0, 0); // Texto negro
$pdf->Cell(25, 5, 'Contratante', 1);
$pdf->Cell(101, 5, $nombre_contratante, 1, 0, 'C', true);
$pdf->Cell(35, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Número de póliza'), 1);
$pdf->Cell(35, 5, $numero_poliza, 1, 1, 'C', true);

$pdf->MultiCell(196, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'De acuerdo con las condiciones de la póliza colectiva de gastos médicos que hace mención este apartado, solicito inscribir como asegurado a la persona cuyos datos se detallan a continuación.'), 1, 'J');

// 🔹 cuarta sección en rojo
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(200, 0, 0);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(0, 5, 'II. DATOS DEL ASEGURADO', 1, 1, 'L', true);

// 🔹 Datos del asegurado (tabla)
$pdf->SetFont('Arial', '', 9);
$pdf->SetFillColor(230, 230, 230); // Fondo gris claro
$pdf->SetTextColor(0, 0, 0); // Texto negro
$pdf->Cell(49, 5, 'Primer nombre', 1, 0, 'L');
$pdf->Cell(49, 5, 'Segundo nombre', 1, 0, 'L');
$pdf->Cell(49, 5, 'Primer apellido', 1, 0, 'L');
$pdf->Cell(49, 5, 'Segundo apellido', 1, 1, 'L');

$pdf->Cell(49, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $primer_nombre), 1, 0, 'L', true);
$pdf->Cell(49, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', validar_nulo($segundo_nombre)), 1, 0, 'L', true);
$pdf->Cell(49, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $primer_apellido), 1, 0, 'L', true);
$pdf->Cell(49, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', validar_nulo($segundo_apellido)), 1, 1, 'L', true);

// 🔹 Más datos...
$pdf->Cell(49, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Tipo de identificación'), 'L',0,'L');
$pdf->Cell(49, 5, 'Identidad ['.(($tipo_identificacion == "1") ? 'x' : ' ').']', 0, 0,'L');
$pdf->Cell(49, 5, 'Pasaporte ['.(($tipo_identificacion == "2") ? 'x' : ' ').']', 0, 0,'L');
$pdf->Cell(49, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Carné de residencia ['.(($tipo_identificacion == "5") ? 'x' : ' ').']'), 'R',1,'L');

$pdf->Cell(49, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'No. identificación'), 1, 0, 'L');
$pdf->Cell(49, 5, 'RTN', 1, 0, 'L');
$pdf->Cell(49, 5, 'Fecha de nacimiento', 1, 0, 'L');
$pdf->Cell(49, 5, 'Lugar de nacimiento', 1, 1, 'L');

$pdf->Cell(49, 5, $numero_identificacion, 1, 0, 'L', true);
$pdf->Cell(49, 5, $rtn, 1, 0, 'L', true);
$pdf->Cell(49, 5, date("d/m/Y", strtotime($fecha_nacimiento)), 1, 0, 'L', true);
$pdf->Cell(49, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $lugar_nacimiento), 1, 1, 'L', true);

$pdf->Cell(30, 5, 'Estado civil', 1, 0, 'L');
$pdf->Cell(68, 5, 'S ['.(($estado_civil == "S") ? 'x' : ' ').'] C ['.(($estado_civil == "C") ? 'x' : ' ').'] D ['.(($estado_civil == "D") ? 'x' : ' ').'] V ['.(($estado_civil == "V") ? 'x' : ' ').'] UL ['.(($estado_civil == "UL") ? 'x' : ' ').']', 1, 0, 'C');
$pdf->Cell(98, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Género         Masculino ['.(($genero == "M") ? 'x' : ' ').'] Femenino ['.(($genero == "F") ? 'x' : ' ').']'), 1, 1, 'C');

$pdf->Cell(80, 5, 'Nacionalidad (es)', 1, 0, 'L');
$pdf->Cell(61, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Profesión, oficio u ocupación'), 1, 0, 'L');
$pdf->Cell(30, 5, 'Estatura(mts)', 1, 0, 'L');
$pdf->Cell(25, 5, 'Peso(lbs)', 1, 1, 'L');

$y = $pdf->GetY();
$pdf->Cell(80, 5, '1 '.iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $nacionalidad_1), 1, 2, 'L', true);
$x = $pdf->GetX();
$pdf->Cell(80, 5, '2 '.iconv('UTF-8', 'ISO-8859-1//TRANSLIT', validar_nulo($nacionalidad_2)), 1, 0, 'L', true);
$pdf->SetY($y);
$pdf->SetX($x+80);
$pdf->Cell(61, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $profesion), 1, 0, 'L', true);
$pdf->Cell(30, 10, $estatura, 1, 0, 'L', true);
$pdf->Cell(25, 10, $peso, 1, 1, 'L', true);

$pdf->Cell(25, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Móvil _ 1'), 1);
$pdf->Cell(73, 5, $celular_1, 1, 0, 'C', true);
$pdf->Cell(25, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Móvil _ 2'), 1);
$pdf->Cell(73, 5, $celular_2, 1, 1, 'C', true);

$pdf->Cell(50, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Correo electrónico _ 1'), 1);
$pdf->Cell(146, 5, $email_1, 1, 1, 'C', true);
$pdf->Cell(50, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Correo electrónico _ 2'), 1);
$pdf->Cell(146, 5, $email_2, 1, 1, 'C', true);

$pdf->Cell(60, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Nombre del cónyuge (completo)'), 1);
$pdf->Cell(136, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', validar_nulo($nombre_conyugue)), 1, 1, 'C', true);

$pdf->Cell(20, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Área'), 1, 0, 'L');
$pdf->Cell(78, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Cargo que desempeña'), 1, 0, 'L');
$pdf->Cell(39, 5, 'Fecha de ingreso', 1, 0, 'L');
$pdf->Cell(59, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Remuneración mensual'), 1, 1, 'L');

$pdf->Cell(20, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', validar_nulo($area)), 1, 0, 'L', true);
$pdf->Cell(78, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $cargo), 1, 0, 'L', true);
$pdf->Cell(39, 5, (($fecha_ingreso == null) ? '' : date("d/m/Y", strtotime($fecha_ingreso))), 1, 0, 'L', true);
$pdf->Cell(59, 5, '$ ['.(($moneda_remuneracion == "$") ? 'x' : ' ').']   L ['.(($moneda_remuneracion == "L") ? 'x' : ' ').']', 1, 1, 'L', true);

$pdf->Cell(196, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Ha desempeñado un cargo público en los últimos cuatro (4) años                   Si ['.(($cargo_publico == "1") ? 'x' : ' ').']                No ['.(($cargo_publico == "0") ? 'x' : ' ').']'), 1, 1, 'L');

$pdf->Cell(60, 5, 'Detalle el nombre del cargo', 1);
$pdf->Cell(136, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', validar_nulo($nombre_cargo_publico)), 1, 1, 'L', true);

$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(196, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Dirección de residencia del asegurado'), 1, 1, 'L');

$pdf->SetFont('Arial', '', 9);
$pdf->Cell(49, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'País'), 1, 0, 'L');
$pdf->Cell(49, 5, 'Departamento', 1, 0, 'L');
$pdf->Cell(49, 5, 'Municipio', 1, 0, 'L');
$pdf->Cell(49, 5, 'Barrio o colonia', 1, 1, 'L');

$pdf->Cell(49, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $pais), 1, 0, 'L', true);
$pdf->Cell(49, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $departamento), 1, 0, 'L', true);
$pdf->Cell(49, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $municipio), 1, 0, 'L', true);
$pdf->Cell(49, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $colonia), 1, 1, 'L', true);

$pdf->Cell(49, 5, 'No. Casa/ lote', 1, 0, 'L');
$pdf->Cell(49, 5, 'Calle(s)', 1, 0, 'L');
$pdf->Cell(49, 5, 'Avenida(s)', 1, 0, 'L');
$pdf->Cell(49, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Teléfono de la residencia'), 1, 1, 'L');

$pdf->Cell(49, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $casa), 1, 0, 'L', true);
$pdf->Cell(49, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', validar_nulo($calle)), 1, 0, 'L', true);
$pdf->Cell(49, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', validar_nulo($avenida)), 1, 0, 'L', true);
$pdf->Cell(49, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', validar_nulo($telefono_residencia)), 1, 1, 'L', true);

// 🔹 quinta sección en rojo
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(200, 0, 0);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(0, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'III. PAGO DE INDEMNIZACIÓN'), 1, 1, 'L', true);

$pdf->SetFont('Arial', '', 9);
$pdf->SetFillColor(230, 230, 230); // Fondo gris claro
$pdf->SetTextColor(0, 0, 0); // Texto negro
$pdf->MultiCell(196, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'En caso de pago por reclamación amparada por el contrato de seguro, sírvase completar la 
siguiente información:'), 1, 'J');

$pdf->Cell(40, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Institución Financiera'), 1, 0, 'L');
$pdf->Cell(70, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $nombre_institucion), 1, 0, 'L', true);
$pdf->Cell(86, 5, 'Tipo de Cuenta               ['.(($tipo_cuenta == "1") ? 'x' : ' ').'] Cheque            ['.(($tipo_cuenta == "2") ? 'x' : ' ').'] Ahorro', 1, 1, 'L');

$pdf->Cell(40, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Número de Cuenta'), 1, 0, 'L');
$pdf->Cell(70, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', validar_nulo($numero_cuenta)), 1, 0, 'L', true);
$pdf->Cell(86, 5, '', 1, 1, 'L', true);

$pdf->Cell(196, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Cualquier beneficio que corresponde a un menor de edad, se entregará a'), 1, 1, 'L');

$pdf->Cell(146, 5, 'Nombre completo', 1, 0, 'L');
$pdf->Cell(50, 5, 'Identidad', 1, 1, 'L');

$pdf->Cell(146, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', validar_nulo($nombre_menor)), 1, 0, 'L', true);
$pdf->Cell(50, 5, $identidad_menor, 1, 1, 'L', true);

// 🔹 sexta sección en rojo
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(200, 0, 0);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(0, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'IV. INFORMACIÓN DE OTROS SEGUROS'), 1, 1, 'L', true);

$pdf->SetFont('Arial', '', 9);
$pdf->SetFillColor(230, 230, 230); // Fondo gris claro
$pdf->SetTextColor(0, 0, 0); // Texto negro
$pdf->Cell(98, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '¿Tiene otros seguros con la compañía?'), 1, 0, 'L');
$pdf->Cell(98, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '¿Qué tipo de seguro?'), 1, 1, 'L');

$pdf->Cell(98, 5, 'Si ['.(($otro_seguro == "1") ? 'x' : ' ').']    No ['.(($otro_seguro == "0") ? 'x' : ' ').']', 1, 0, 'L');
$pdf->Cell(98, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $tipo_seguro), 1, 1, 'L', true);

$pdf->Cell(98, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '¿Con cuál otra compañía tiene o tenía seguros?'), 1, 0, 'L');
$pdf->Cell(98, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '¿Qué tipo de seguro?'), 1, 1, 'L');

$pdf->Cell(98, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $extra_seguro), 1, 0, 'L', true);
$pdf->Cell(98, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $tipo_extra_seguro), 1, 1, 'L', true);

$pdf->SetAutoPageBreak(true, 15); // 10 mm de margen inferior


// 🔹 sexta sección en rojo
$pdf->Ln(6);
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(200, 0, 0);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(0, 5, 'B. DATOS DEL RIESGO', 1, 1, 'L', true);

$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(230, 230, 230); // Fondo gris claro
$pdf->SetTextColor(0, 0, 0); // Texto negro
$pdf->Cell(196, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Dependientes que desea incluir en el seguro médico'), 1, 1, 'L');

$pdf->SetFont('Arial', '', 9);
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+1, $y+4, 'Nombre completo');
$pdf->Cell(40, 15, '', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+1, $y+4, 'Sexo');
$pdf->Cell(16, 15, '', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+1, $y+4, 'Parentesco');
$pdf->Cell(20, 15, '', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+1, $y+4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Ocupación'));
$pdf->Cell(30, 15, '', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+1, $y+4, 'Peso');
$pdf->Cell(15, 15, '', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+1, $y+4, 'Estatura');
$pdf->Cell(20, 15, '', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+1, $y+4, 'Fecha de');
$pdf->Text($x+1, $y+8, 'nacimiento');
$pdf->Cell(24, 15, '', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+1, $y+4, 'Edad');
$pdf->Cell(10, 15, '', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+1, $y+4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Número'));
$pdf->Text($x+1, $y+8, 'de');
$pdf->Text($x+1, $y+12, 'identidad');
$pdf->Cell(21, 15, '', 1, 1, 'L');

$lineasUso = 5-count($dependientes);
if(!empty($dependientes)){
    foreach ($dependientes as $dependiente) {
        $select = "SELECT nombre
        from genero
        where id = ?";
        $stmt = $conn->prepare($select);
        $stmt->bind_param("i", $dependiente->sexo); 
        $stmt->execute();
        $result = $stmt->get_result();
        $nombre_genero = "";
        if ($row = $result->fetch_assoc()) {
            $nombre_genero = $row["nombre"];
        }
        $stmt->close();

        $select = "SELECT nombre
        from parentesco
        where id = ?";
        $stmt = $conn->prepare($select);
        $stmt->bind_param("i", $dependiente->parentesco); 
        $stmt->execute();
        $result = $stmt->get_result();
        $nombre_parentesco = "";
        if ($row = $result->fetch_assoc()) {
            $nombre_parentesco = $row["nombre"];
        }
        $stmt->close();

        $pdf->Cell(40, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $dependiente->nombre), 1, 0, 'L', true);
        $pdf->Cell(16, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $nombre_genero), 1, 0, 'L', true);
        $pdf->Cell(20, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $nombre_parentesco), 1, 0, 'L', true);
        $pdf->Cell(30, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $dependiente->ocupacion), 1, 0, 'L', true);
        $pdf->Cell(15, 5, $dependiente->peso.' lbs', 1, 0, 'L', true);
        $pdf->Cell(20, 5, $dependiente->estatura.' mts', 1, 0, 'L', true);
        $pdf->Cell(24, 5, date("d/m/Y", strtotime($dependiente->fechaNacimiento)), 1, 0, 'L', true);
        $pdf->Cell(10, 5, calcularEdad($dependiente->fechaNacimiento), 1, 0, 'L', true);
        $pdf->Cell(21, 5, $dependiente->identidad, 1, 1, 'L', true);
    }
}
if ($lineasUso > 0) {
    for ($i=0; $i < $lineasUso; $i++) { 
        $pdf->Cell(40, 5, '', 1, 0, 'L', true);
        $pdf->Cell(16, 5, '', 1, 0, 'L', true);
        $pdf->Cell(20, 5, '', 1, 0, 'L', true);
        $pdf->Cell(30, 5, '', 1, 0, 'L', true);
        $pdf->Cell(15, 5, '', 1, 0, 'L', true);
        $pdf->Cell(20, 5, '', 1, 0, 'L', true);
        $pdf->Cell(24, 5, '', 1, 0, 'L', true);
        $pdf->Cell(10, 5, '', 1, 0, 'L', true);
        $pdf->Cell(21, 5, '', 1, 1, 'L', true);
    }
}

$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(196, 8, 'Cuestionario de salud del asegurado principal y sus dependientes', 'LR', 1, 'L');
$pdf->SetFont('Arial', '', 9);
$pdf->MultiCell(196, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '¿Ha tenido alguna vez usted o algún dependiente nombrado alguna(s) condición(es), signo(s) o síntoma (s)
manifestado(s) y/o evidente(s) que aún no ha recibido atención médica, ha padecido, ha sido informado de
que ha padecido, o ha recibido consejo o tratamiento o cirugía por alguna de las condiciones mencionadas
en el siguiente cuestionario.'), 'LR', 'J');

$selectPreguntas = "SELECT p.id, p.texto
from pregunta p
where p.id not in (24,25)
order by id asc";
$resultPreguntas= $conn->query($selectPreguntas);
if ($resultPreguntas->num_rows > 0) {
    while($row = $resultPreguntas->fetch_assoc()) {
        generarTablaPregunta($pdf, $row["id"], $row["texto"], $respuestas);
    }
} else {
    echo "No hay preguntas";
}
$resultPreguntas->close();


$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(156, 8, 'Cuestionario personal', 1, 0, 'L');
$pdf->Cell(20, 8, 'Si', 1, 0, 'C');
$pdf->Cell(20, 8, 'No', 1, 1, 'C');
//pregunta 1
$obj = null;
foreach ($respuestas as $respuesta) {
    if ($respuesta->id == "1") {
        $obj = $respuesta;
        break;
    }
}

/*$x = $pdf->GetX();
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(156, 12, '', 1, 0, 'L');
$y = $pdf->GetY();
$pdf->Text($x+1, $y+4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '1. Padecimiento cerebro vasculares incluyendo ictus, tumores cerebrales, migraña, dolores'));
$pdf->Text($x+1, $y+8, 'de cabeza, embolia, traumas y otros.');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Cell(20, 12, '', 1, 0, 'L', true);
$pdf->Text($x+9, $y+7, ($obj != null) ? '[x]' : '[ ]');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Cell(20, 12, '', 1, 1, 'L', true);
$pdf->Text($x+9, $y+7, ($obj == null) ? '[x]' : '[ ]');

$pdf->Cell(196, 8, 'Para quien es afirmativa la respuesta detalle', 1, 1, 'L');

$pdf->Cell(60, 8, 'Nombre', 1, 0, 'L');
$pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Diagnóstico'), 1, 0, 'L');
$pdf->Cell(40, 8, 'Tratamiento', 1, 0, 'L');
$pdf->Cell(20, 8, 'Fecha', 1, 0, 'L');
$pdf->Cell(46, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Médico u hospital'), 1, 1, 'L');

$pdf->Cell(60, 8, (($obj != null) ? $obj->nombre : ''), 1, 0, 'L', true);
$pdf->Cell(30, 8, (($obj != null) ? $obj->diagnostico : ''), 1, 0, 'L', true);
$pdf->Cell(40, 8, (($obj != null) ? $obj->tratamiento : ''), 1, 0, 'L', true);
$pdf->Cell(20, 8, (($obj != null) ? date("d/m/Y", strtotime($obj->fecha)) : ''), 1, 0, 'L', true);
$pdf->Cell(46, 8, (($obj != null) ? $obj->medico : ''), 1, 1, 'L', true);
//pregunta 2
$obj = null;
foreach ($respuestas as $respuesta) {
    if ($respuesta->id == "2") {
        $obj = $respuesta;
        break;
    }
}
$x = $pdf->GetX();
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(156, 12, '', 1, 0, 'L');
$y = $pdf->GetY();
$pdf->Text($x+1, $y+4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '2. Padecimientos del cerebro o sistema nervioso incluyendo depresión, ansiedad, epilepsia,'));
$pdf->Text($x+1, $y+8, 'bulimia, anorexia, ideas suicidas y otros.');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+7, '[ ]');
$pdf->Cell(20, 12, '', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+7, '[ ]');
$pdf->Cell(20, 12, '', 1, 1, 'L');

$pdf->Cell(196, 8, 'Para quien es afirmativa la respuesta detalle', 1, 1, 'L');

$pdf->Cell(60, 8, 'Nombre', 1, 0, 'L');
$pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Diagnóstico'), 1, 0, 'L');
$pdf->Cell(40, 8, 'Tratamiento', 1, 0, 'L');
$pdf->Cell(16, 8, 'Fecha', 1, 0, 'L');
$pdf->Cell(50, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Médico u hospital'), 1, 1, 'L');

$pdf->Cell(60, 8, (($obj != null) ? $obj->nombre : ''), 1, 0, 'L', true);
$pdf->Cell(30, 8, (($obj != null) ? $obj->diagnostico : ''), 1, 0, 'L', true);
$pdf->Cell(40, 8, (($obj != null) ? $obj->tratamiento : ''), 1, 0, 'L', true);
$pdf->Cell(16, 8, (($obj != null) ? date("d/m/Y", strtotime($obj->fecha)) : ''), 1, 0, 'L', true);
$pdf->Cell(50, 8, (($obj != null) ? $obj->medico : ''), 1, 1, 'L', true);
//pregunta 3
$x = $pdf->GetX();
$pdf->SetFont('Arial', '', 9);;
$pdf->Cell(156, 16, '', 1, 0, 'L');
$y = $pdf->GetY();
$pdf->Text($x+1, $y+4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '3. Padecimientos cardiovasculares incluyendo hipertensión arterial, hipotensión arterial,'));
$pdf->Text($x+1, $y+8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'angina de pecho, infartos, electrocardiogramas alterados, colesterol o triglicéridos altos,'));
$pdf->Text($x+1, $y+12, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'insuficiencia venosa, tromboflebitis, flebitis, alteraciones vasculares, soplos y otros.'));
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+9, '[ ]');
$pdf->Cell(20, 16, '', 1, 0, 'L', true);
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+9, '[ ]');
$pdf->Cell(20, 16, '', 1, 1, 'L', true);

$pdf->Cell(196, 8, 'Para quien es afirmativa la respuesta detalle', 1, 1, 'L');

$pdf->Cell(60, 8, 'Nombre', 1, 0, 'L');
$pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Diagnóstico'), 1, 0, 'L');
$pdf->Cell(40, 8, 'Tratamiento', 1, 0, 'L');
$pdf->Cell(16, 8, 'Fecha', 1, 0, 'L');
$pdf->Cell(50, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Médico u hospital'), 1, 1, 'L');

$pdf->Cell(60, 8, '', 1, 0, 'L', true);
$pdf->Cell(30, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(16, 8, '', 1, 0, 'L', true);
$pdf->Cell(50, 8, '', 1, 1, 'L', true);
//pregunta 4
$x = $pdf->GetX();
$pdf->SetFont('Arial', '', 9);;
$pdf->Cell(156, 8, '', 1, 0, 'L');
$y = $pdf->GetY();
$pdf->Text($x+1, $y+4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '4. Padecimiento de la vista o de los oídos'));
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+5, '[ ]');
$pdf->Cell(20, 8, '', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+5, '[ ]');
$pdf->Cell(20, 8, '', 1, 1, 'L');

$pdf->Cell(196, 8, 'Para quien es afirmativa la respuesta detalle', 1, 1, 'L');

$pdf->Cell(60, 8, 'Nombre', 1, 0, 'L');
$pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Diagnóstico'), 1, 0, 'L');
$pdf->Cell(40, 8, 'Tratamiento', 1, 0, 'L');
$pdf->Cell(16, 8, 'Fecha', 1, 0, 'L');
$pdf->Cell(50, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Médico u hospital'), 1, 1, 'L');

$pdf->Cell(60, 8, '', 1, 0, 'L', true);
$pdf->Cell(30, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(16, 8, '', 1, 0, 'L', true);
$pdf->Cell(50, 8, '', 1, 1, 'L', true);
//pregunta 5
$x = $pdf->GetX();
$pdf->SetFont('Arial', '', 9);;
$pdf->Cell(156, 12, '', 1, 0, 'L');
$y = $pdf->GetY();
$pdf->Text($x+1, $y+4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '5. Padecimiento del sistema respiratorio incluyendo asma, EPOC, enfisema, bronquitis'));
$pdf->Text($x+1, $y+8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'crónica, hiperactividad bronquial y otros.'));
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+7, '[ ]');
$pdf->Cell(20, 12, '', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+7, '[ ]');
$pdf->Cell(20, 12, '', 1, 1, 'L');

$pdf->Cell(196, 8, 'Para quien es afirmativa la respuesta detalle', 1, 1, 'L');

$pdf->Cell(60, 8, 'Nombre', 1, 0, 'L');
$pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Diagnóstico'), 1, 0, 'L');
$pdf->Cell(40, 8, 'Tratamiento', 1, 0, 'L');
$pdf->Cell(16, 8, 'Fecha', 1, 0, 'L');
$pdf->Cell(50, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Médico u hospital'), 1, 1, 'L');

$pdf->Cell(60, 8, '', 1, 0, 'L', true);
$pdf->Cell(30, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(16, 8, '', 1, 0, 'L', true);
$pdf->Cell(50, 8, '', 1, 1, 'L', true);
//pregunta 6
$x = $pdf->GetX();
$pdf->SetFont('Arial', '', 9);;
$pdf->Cell(156, 8, '', 1, 0, 'L');
$y = $pdf->GetY();
$pdf->Text($x+1, $y+4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '6. Sistema digestivo incluyendo hernia hiatal, gastritis, reflujo gastroesofágico y otros.'));
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+5, '[ ]');
$pdf->Cell(20, 8, '', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+5, '[ ]');
$pdf->Cell(20, 8, '', 1, 1, 'L');

$pdf->Cell(196, 8, 'Para quien es afirmativa la respuesta detalle', 1, 1, 'L');

$pdf->Cell(60, 8, 'Nombre', 1, 0, 'L');
$pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Diagnóstico'), 1, 0, 'L');
$pdf->Cell(40, 8, 'Tratamiento', 1, 0, 'L');
$pdf->Cell(16, 8, 'Fecha', 1, 0, 'L');
$pdf->Cell(50, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Médico u hospital'), 1, 1, 'L');

$pdf->Cell(60, 8, '', 1, 0, 'L', true);
$pdf->Cell(30, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(16, 8, '', 1, 0, 'L', true);
$pdf->Cell(50, 8, '', 1, 1, 'L', true);
//pregunta 7
$x = $pdf->GetX();
$pdf->SetFont('Arial', '', 9);;
$pdf->Cell(156, 12, '', 1, 0, 'L');
$y = $pdf->GetY();
$pdf->Text($x+1, $y+4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '7. Padecimiento del sistema urinario, litiasis, tumores de vejiga, píelo nefritis, hidronefrosis,'));
$pdf->Text($x+1, $y+8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'exámenes de orina con proteinuria y/o hematuria con glucosa en orina.'));
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+7, '[ ]');
$pdf->Cell(20, 12, '', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+7, '[ ]');
$pdf->Cell(20, 12, '', 1, 1, 'L');

$pdf->Cell(196, 8, 'Para quien es afirmativa la respuesta detalle', 1, 1, 'L');

$pdf->Cell(60, 8, 'Nombre', 1, 0, 'L');
$pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Diagnóstico'), 1, 0, 'L');
$pdf->Cell(40, 8, 'Tratamiento', 1, 0, 'L');
$pdf->Cell(16, 8, 'Fecha', 1, 0, 'L');
$pdf->Cell(50, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Médico u hospital'), 1, 1, 'L');

$pdf->Cell(60, 8, '', 1, 0, 'L', true);
$pdf->Cell(30, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(16, 8, '', 1, 0, 'L', true);
$pdf->Cell(50, 8, '', 1, 1, 'L', true);
//pregunta 8
$x = $pdf->GetX();
$pdf->SetFont('Arial', '', 9);;
$pdf->Cell(156, 12, '', 1, 0, 'L');
$y = $pdf->GetY();
$pdf->Text($x+1, $y+4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '8. Padecimientos de los órganos reproductores masculinos o femeninos incluyendo endome-'));
$pdf->Text($x+1, $y+8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'triosis, miomatosis, citología con alteración y otros.'));
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+7, '[ ]');
$pdf->Cell(20, 12, '', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+7, '[ ]');
$pdf->Cell(20, 12, '', 1, 1, 'L');

$pdf->Cell(196, 8, 'Para quien es afirmativa la respuesta detalle', 1, 1, 'L');

$pdf->Cell(60, 8, 'Nombre', 1, 0, 'L');
$pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Diagnóstico'), 1, 0, 'L');
$pdf->Cell(40, 8, 'Tratamiento', 1, 0, 'L');
$pdf->Cell(16, 8, 'Fecha', 1, 0, 'L');
$pdf->Cell(50, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Médico u hospital'), 1, 1, 'L');

$pdf->Cell(60, 8, '', 1, 0, 'L', true);
$pdf->Cell(30, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(16, 8, '', 1, 0, 'L', true);
$pdf->Cell(50, 8, '', 1, 1, 'L', true);
//pregunta 9
$x = $pdf->GetX();
$pdf->SetFont('Arial', '', 9);;
$pdf->Cell(156, 8, '', 1, 0, 'L');
$y = $pdf->GetY();
$pdf->Text($x+1, $y+4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '9. Padecimiento sanguíneos, anemia, leucemia, sangrados y trantornos de la cogulación.'));
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+5, '[ ]');
$pdf->Cell(20, 8, '', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+5, '[ ]');
$pdf->Cell(20, 8, '', 1, 1, 'L');

$pdf->Cell(196, 8, 'Para quien es afirmativa la respuesta detalle', 1, 1, 'L');

$pdf->Cell(60, 8, 'Nombre', 1, 0, 'L');
$pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Diagnóstico'), 1, 0, 'L');
$pdf->Cell(40, 8, 'Tratamiento', 1, 0, 'L');
$pdf->Cell(16, 8, 'Fecha', 1, 0, 'L');
$pdf->Cell(50, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Médico u hospital'), 1, 1, 'L');

$pdf->Cell(60, 8, '', 1, 0, 'L', true);
$pdf->Cell(30, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(16, 8, '', 1, 0, 'L', true);
$pdf->Cell(50, 8, '', 1, 1, 'L', true);
//pregunta 10
$x = $pdf->GetX();
$pdf->SetFont('Arial', '', 9);;
$pdf->Cell(156, 12, '', 1, 0, 'L');
$y = $pdf->GetY();
$pdf->Text($x+1, $y+4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '10. Trantornos endocrinos como diábetes y/o hiperglicemia, hipertiroidismo, hipotiroidismo,'));
$pdf->Text($x+1, $y+8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'triosis, miomatosis, citología con alteración y otros.'));
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+7, '[ ]');
$pdf->Cell(20, 12, '', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+7, '[ ]');
$pdf->Cell(20, 12, '', 1, 1, 'L');

$pdf->Cell(196, 8, 'Para quien es afirmativa la respuesta detalle', 1, 1, 'L');

$pdf->Cell(60, 8, 'Nombre', 1, 0, 'L');
$pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Diagnóstico'), 1, 0, 'L');
$pdf->Cell(40, 8, 'Tratamiento', 1, 0, 'L');
$pdf->Cell(16, 8, 'Fecha', 1, 0, 'L');
$pdf->Cell(50, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Médico u hospital'), 1, 1, 'L');

$pdf->Cell(60, 8, '', 1, 0, 'L', true);
$pdf->Cell(30, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(16, 8, '', 1, 0, 'L', true);
$pdf->Cell(50, 8, '', 1, 1, 'L', true);
//pregunta 11
$x = $pdf->GetX();
$pdf->SetFont('Arial', '', 9);;
$pdf->Cell(156, 8, '', 1, 0, 'L');
$y = $pdf->GetY();
$pdf->Text($x+1, $y+4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '11. Transtronos de la piel y colagenos como lupus, dermatitis psoriasis, fiebre reumática.'));
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+5, '[ ]');
$pdf->Cell(20, 8, '', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+5, '[ ]');
$pdf->Cell(20, 8, '', 1, 1, 'L');

$pdf->Cell(196, 8, 'Para quien es afirmativa la respuesta detalle', 1, 1, 'L');

$pdf->Cell(60, 8, 'Nombre', 1, 0, 'L');
$pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Diagnóstico'), 1, 0, 'L');
$pdf->Cell(40, 8, 'Tratamiento', 1, 0, 'L');
$pdf->Cell(16, 8, 'Fecha', 1, 0, 'L');
$pdf->Cell(50, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Médico u hospital'), 1, 1, 'L');

$pdf->Cell(60, 8, '', 1, 0, 'L', true);
$pdf->Cell(30, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(16, 8, '', 1, 0, 'L', true);
$pdf->Cell(50, 8, '', 1, 1, 'L', true);
//pregunta 12
$x = $pdf->GetX();
$pdf->SetFont('Arial', '', 9);;
$pdf->Cell(156, 8, '', 1, 0, 'L');
$y = $pdf->GetY();
$pdf->Text($x+1, $y+4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '12. Padecimientos del sistema músculo esquelético incluyendo artritis, osteoartritis y otros.'));
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+5, '[ ]');
$pdf->Cell(20, 8, '', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+5, '[ ]');
$pdf->Cell(20, 8, '', 1, 1, 'L');

$pdf->Cell(196, 8, 'Para quien es afirmativa la respuesta detalle', 1, 1, 'L');

$pdf->Cell(60, 8, 'Nombre', 1, 0, 'L');
$pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Diagnóstico'), 1, 0, 'L');
$pdf->Cell(40, 8, 'Tratamiento', 1, 0, 'L');
$pdf->Cell(16, 8, 'Fecha', 1, 0, 'L');
$pdf->Cell(50, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Médico u hospital'), 1, 1, 'L');

$pdf->Cell(60, 8, '', 1, 0, 'L', true);
$pdf->Cell(30, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(16, 8, '', 1, 0, 'L', true);
$pdf->Cell(50, 8, '', 1, 1, 'L', true);
//pregunta 13
$x = $pdf->GetX();
$pdf->SetFont('Arial', '', 9);;
$pdf->Cell(156, 8, '', 1, 0, 'L');
$y = $pdf->GetY();
$pdf->Text($x+1, $y+4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '13. Cualquier tipo de quistes, nódulos, tumores o cáncer.'));
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+5, '[ ]');
$pdf->Cell(20, 8, '', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+5, '[ ]');
$pdf->Cell(20, 8, '', 1, 1, 'L');

$pdf->Cell(196, 8, 'Para quien es afirmativa la respuesta detalle', 1, 1, 'L');

$pdf->Cell(60, 8, 'Nombre', 1, 0, 'L');
$pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Diagnóstico'), 1, 0, 'L');
$pdf->Cell(40, 8, 'Tratamiento', 1, 0, 'L');
$pdf->Cell(16, 8, 'Fecha', 1, 0, 'L');
$pdf->Cell(50, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Médico u hospital'), 1, 1, 'L');

$pdf->Cell(60, 8, '', 1, 0, 'L', true);
$pdf->Cell(30, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(16, 8, '', 1, 0, 'L', true);
$pdf->Cell(50, 8, '', 1, 1, 'L', true);
//pregunta 14
$x = $pdf->GetX();
$pdf->SetFont('Arial', '', 9);;
$pdf->Cell(156, 8, '', 1, 0, 'L');
$y = $pdf->GetY();
$pdf->Text($x+1, $y+4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '14. Problemas alcohólicos o problemas de drogas.'));
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+5, '[ ]');
$pdf->Cell(20, 8, '', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+5, '[ ]');
$pdf->Cell(20, 8, '', 1, 1, 'L');

$pdf->Cell(196, 8, 'Para quien es afirmativa la respuesta detalle', 1, 1, 'L');

$pdf->Cell(60, 8, 'Nombre', 1, 0, 'L');
$pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Diagnóstico'), 1, 0, 'L');
$pdf->Cell(40, 8, 'Tratamiento', 1, 0, 'L');
$pdf->Cell(16, 8, 'Fecha', 1, 0, 'L');
$pdf->Cell(50, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Médico u hospital'), 1, 1, 'L');

$pdf->Cell(60, 8, '', 1, 0, 'L', true);
$pdf->Cell(30, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(16, 8, '', 1, 0, 'L', true);
$pdf->Cell(50, 8, '', 1, 1, 'L', true);
//pregunta 15
$x = $pdf->GetX();
$pdf->SetFont('Arial', '', 9);;
$pdf->Cell(156, 8, '', 1, 0, 'L');
$y = $pdf->GetY();
$pdf->Text($x+1, $y+4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '15. Problemas de la columna vertebral , incluyendo hernia discal, lumbago, tumores y otros.'));
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+5, '[ ]');
$pdf->Cell(20, 8, '', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+5, '[ ]');
$pdf->Cell(20, 8, '', 1, 1, 'L');

$pdf->Cell(196, 8, 'Para quien es afirmativa la respuesta detalle', 1, 1, 'L');

$pdf->Cell(60, 8, 'Nombre', 1, 0, 'L');
$pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Diagnóstico'), 1, 0, 'L');
$pdf->Cell(40, 8, 'Tratamiento', 1, 0, 'L');
$pdf->Cell(16, 8, 'Fecha', 1, 0, 'L');
$pdf->Cell(50, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Médico u hospital'), 1, 1, 'L');

$pdf->Cell(60, 8, '', 1, 0, 'L', true);
$pdf->Cell(30, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(16, 8, '', 1, 0, 'L', true);
$pdf->Cell(50, 8, '', 1, 1, 'L', true);
//pregunta 16
$x = $pdf->GetX();
$pdf->SetFont('Arial', '', 9);;
$pdf->Cell(156, 12, '', 1, 0, 'L');
$y = $pdf->GetY();
$pdf->Text($x+1, $y+4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '16. Le han realizado a usted o sus dependientes algún examen especial de laboratorio,'));
$pdf->Text($x+1, $y+8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'ultrasonido, mamografía, laparoscopia u otro?'));
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+7, '[ ]');
$pdf->Cell(20, 12, '', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+7, '[ ]');
$pdf->Cell(20, 12, '', 1, 1, 'L');

$pdf->Cell(196, 8, 'Para quien es afirmativa la respuesta detalle', 1, 1, 'L');

$pdf->Cell(60, 8, 'Nombre', 1, 0, 'L');
$pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Diagnóstico'), 1, 0, 'L');
$pdf->Cell(40, 8, 'Tratamiento', 1, 0, 'L');
$pdf->Cell(16, 8, 'Fecha', 1, 0, 'L');
$pdf->Cell(50, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Médico u hospital'), 1, 1, 'L');

$pdf->Cell(60, 8, '', 1, 0, 'L', true);
$pdf->Cell(30, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(16, 8, '', 1, 0, 'L', true);
$pdf->Cell(50, 8, '', 1, 1, 'L', true);
//pregunta 17
$x = $pdf->GetX();
$pdf->SetFont('Arial', '', 9);;
$pdf->Cell(156, 8, '', 1, 0, 'L');
$y = $pdf->GetY();
$pdf->Text($x+1, $y+4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '17. Ha estado usted o sus dependientes recluido en un hospital o institución similar?'));
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+5, '[ ]');
$pdf->Cell(20, 8, '', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+5, '[ ]');
$pdf->Cell(20, 8, '', 1, 1, 'L');

$pdf->Cell(196, 8, 'Para quien es afirmativa la respuesta detalle', 1, 1, 'L');

$pdf->Cell(60, 8, 'Nombre', 1, 0, 'L');
$pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Diagnóstico'), 1, 0, 'L');
$pdf->Cell(40, 8, 'Tratamiento', 1, 0, 'L');
$pdf->Cell(16, 8, 'Fecha', 1, 0, 'L');
$pdf->Cell(50, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Médico u hospital'), 1, 1, 'L');

$pdf->Cell(60, 8, '', 1, 0, 'L', true);
$pdf->Cell(30, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(16, 8, '', 1, 0, 'L', true);
$pdf->Cell(50, 8, '', 1, 1, 'L', true);
// pregunta 18
$x = $pdf->GetX();
$pdf->SetFont('Arial', '', 9);;
$pdf->Cell(156, 8, '', 1, 0, 'L');
$y = $pdf->GetY();
$pdf->Text($x+1, $y+4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '18. Esta usted o alguno de sus dependientes nombrados embarazada actualmente?'));
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+5, '[ ]');
$pdf->Cell(20, 8, '', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+5, '[ ]');
$pdf->Cell(20, 8, '', 1, 1, 'L');

$pdf->Cell(196, 8, 'Para quien es afirmativa la respuesta detalle', 1, 1, 'L');

$pdf->Cell(60, 8, 'Nombre', 1, 0, 'L');
$pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Diagnóstico'), 1, 0, 'L');
$pdf->Cell(40, 8, 'Tratamiento', 1, 0, 'L');
$pdf->Cell(16, 8, 'Fecha', 1, 0, 'L');
$pdf->Cell(50, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Médico u hospital'), 1, 1, 'L');

$pdf->Cell(60, 8, '', 1, 0, 'L', true);
$pdf->Cell(30, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(16, 8, '', 1, 0, 'L', true);
$pdf->Cell(50, 8, '', 1, 1, 'L', true);
//pregunta 19
$x = $pdf->GetX();
$pdf->SetFont('Arial', '', 9);;
$pdf->Cell(156, 12, '', 1, 0, 'L');
$y = $pdf->GetY();
$pdf->Text($x+1, $y+4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '19. Ha recibido usted o alguno de sus dependientes nombrados transfusiones'));
$pdf->Text($x+1, $y+8, 'de sangre?');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+7, '[ ]');
$pdf->Cell(20, 12, '', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+7, '[ ]');
$pdf->Cell(20, 12, '', 1, 1, 'L');

$pdf->Cell(196, 8, 'Para quien es afirmativa la respuesta detalle', 1, 1, 'L');

$pdf->Cell(60, 8, 'Nombre', 1, 0, 'L');
$pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Diagnóstico'), 1, 0, 'L');
$pdf->Cell(40, 8, 'Tratamiento', 1, 0, 'L');
$pdf->Cell(16, 8, 'Fecha', 1, 0, 'L');
$pdf->Cell(50, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Médico u hospital'), 1, 1, 'L');

$pdf->Cell(60, 8, '', 1, 0, 'L', true);
$pdf->Cell(30, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(16, 8, '', 1, 0, 'L', true);
$pdf->Cell(50, 8, '', 1, 1, 'L', true);
//pregunta 20
$x = $pdf->GetX();
$pdf->SetFont('Arial', '', 9);;
$pdf->Cell(156, 12, '', 1, 0, 'L');
$y = $pdf->GetY();
$pdf->Text($x+1, $y+4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '20. Practica usted o alguno de sus dependientes algún tipo de deporte profesional'));
$pdf->Text($x+1, $y+8, 'o amateur?');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+7, '[ ]');
$pdf->Cell(20, 12, '', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+7, '[ ]');
$pdf->Cell(20, 12, '', 1, 1, 'L');

$pdf->Cell(196, 8, 'Para quien es afirmativa la respuesta detalle', 1, 1, 'L');

$pdf->Cell(60, 8, 'Nombre', 1, 0, 'L');
$pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Diagnóstico'), 1, 0, 'L');
$pdf->Cell(40, 8, 'Tratamiento', 1, 0, 'L');
$pdf->Cell(16, 8, 'Fecha', 1, 0, 'L');
$pdf->Cell(50, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Médico u hospital'), 1, 1, 'L');

$pdf->Cell(60, 8, '', 1, 0, 'L', true);
$pdf->Cell(30, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(16, 8, '', 1, 0, 'L', true);
$pdf->Cell(50, 8, '', 1, 1, 'L', true);
//pregunta 21
$x = $pdf->GetX();
$pdf->SetFont('Arial', '', 9);;
$pdf->Cell(156, 12, '', 1, 0, 'L');
$y = $pdf->GetY();
$pdf->Text($x+1, $y+4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '21. Usted o alguno de sus dependientes ha sido sometido a cirugías, radioterapia,'));
$pdf->Text($x+1, $y+8, 'quimioterapia?');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+7, '[ ]');
$pdf->Cell(20, 12, '', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+7, '[ ]');
$pdf->Cell(20, 12, '', 1, 1, 'L');

$pdf->Cell(196, 8, 'Para quien es afirmativa la respuesta detalle', 1, 1, 'L');

$pdf->Cell(60, 8, 'Nombre', 1, 0, 'L');
$pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Diagnóstico'), 1, 0, 'L');
$pdf->Cell(40, 8, 'Tratamiento', 1, 0, 'L');
$pdf->Cell(16, 8, 'Fecha', 1, 0, 'L');
$pdf->Cell(50, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Médico u hospital'), 1, 1, 'L');

$pdf->Cell(60, 8, '', 1, 0, 'L', true);
$pdf->Cell(30, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(16, 8, '', 1, 0, 'L', true);
$pdf->Cell(50, 8, '', 1, 1, 'L', true);
//pregunta 22
$x = $pdf->GetX();
$pdf->SetFont('Arial', '', 9);;
$pdf->Cell(156, 12, '', 1, 0, 'L');
$y = $pdf->GetY();
$pdf->Text($x+1, $y+4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '22. Ha padecido usted o alguno de sus dependientes de glándula mamaria,'));
$pdf->Text($x+1, $y+8, 'galactorrea, mastitis, quiste, tumores, gigantomastia y otros');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+7, '[ ]');
$pdf->Cell(20, 12, '', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+7, '[ ]');
$pdf->Cell(20, 12, '', 1, 1, 'L');

$pdf->Cell(196, 8, 'Para quien es afirmativa la respuesta detalle', 1, 1, 'L');

$pdf->Cell(60, 8, 'Nombre', 1, 0, 'L');
$pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Diagnóstico'), 1, 0, 'L');
$pdf->Cell(40, 8, 'Tratamiento', 1, 0, 'L');
$pdf->Cell(16, 8, 'Fecha', 1, 0, 'L');
$pdf->Cell(50, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Médico u hospital'), 1, 1, 'L');

$pdf->Cell(60, 8, '', 1, 0, 'L', true);
$pdf->Cell(30, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(16, 8, '', 1, 0, 'L', true);
$pdf->Cell(50, 8, '', 1, 1, 'L', true);
//pregunta 23
$x = $pdf->GetX();
$pdf->SetFont('Arial', '', 9);;
$pdf->Cell(156, 12, '', 1, 0, 'L');
$y = $pdf->GetY();
$pdf->Text($x+1, $y+4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '23. Se le ha aconsejado a usted o alguno de sus dependientes una operación quirúrgica o'));
$pdf->Text($x+1, $y+8, 'tratamiento pero usted decidio no hacerla?');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+7, '[ ]');
$pdf->Cell(20, 12, '', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+7, '[ ]');
$pdf->Cell(20, 12, '', 1, 1, 'L');

$pdf->Cell(196, 8, 'Para quien es afirmativa la respuesta detalle', 1, 1, 'L');

$pdf->Cell(60, 8, 'Nombre', 1, 0, 'L');
$pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Diagnóstico'), 1, 0, 'L');
$pdf->Cell(40, 8, 'Tratamiento', 1, 0, 'L');
$pdf->Cell(16, 8, 'Fecha', 1, 0, 'L');
$pdf->Cell(50, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Médico u hospital'), 1, 1, 'L');

$pdf->Cell(60, 8, '', 1, 0, 'L', true);
$pdf->Cell(30, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(16, 8, '', 1, 0, 'L', true);
$pdf->Cell(50, 8, '', 1, 1, 'L', true);
//pregunta 24
$x = $pdf->GetX();
$pdf->SetFont('Arial', '', 9);;
$pdf->Cell(156, 12, '', 1, 0, 'L');
$y = $pdf->GetY();
$pdf->Text($x+1, $y+4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '24. Alguna vez alguna compañía de seguros le ha denegado, aplazado o limitado'));
$pdf->Text($x+1, $y+8, 'un seguro de vida, de accidentes o de salud a usted o alguno de sus dependientes?');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+7, '[ ]');
$pdf->Cell(20, 12, '', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+7, '[ ]');
$pdf->Cell(20, 12, '', 1, 1, 'L');

$pdf->Cell(20, 8, 'Detalle', 1, 0, 'L');
$pdf->Cell(176, 8, '', 1, 1, 'L', true);
$pdf->Cell(196, 8, '', 1, 1, 'L', true);
//pregunta 25
$x = $pdf->GetX();
$pdf->SetFont('Arial', '', 9);;
$pdf->Cell(156, 12, '', 1, 0, 'L');
$y = $pdf->GetY();
$pdf->Text($x+1, $y+4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '25. Ha solicitado o recibido beneficios de hospitalización, incapacidad, o algún otro tipo'));
$pdf->Text($x+1, $y+8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'de beneficio médico de alguna compañía de seguros?'));
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+7, '[ ]');
$pdf->Cell(20, 12, '', 1, 0, 'L');
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Text($x+9, $y+7, '[ ]');
$pdf->Cell(20, 12, '', 1, 1, 'L');

$pdf->Cell(20, 8, 'Detalle', 1, 0, 'L');
$pdf->Cell(176, 8, '', 1, 1, 'L', true);
$pdf->Cell(196, 8, '', 1, 1, 'L', true);*/
//dependientes
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(196, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '¿Tiene o ha tenido usted o alguno de los dependientes nombrados algun seguro?'), 1, 1, 'L');

$pdf->SetFont('Arial', '', 9);
$pdf->Cell(40, 8, 'Tipo de seguro', 1, 0, 'L');
$pdf->Cell(13, 8, 'Si', 1, 0, 'C');
$pdf->Cell(13, 8, 'No', 1, 0, 'C');
$pdf->Cell(50, 8, 'Nombre del asegurado', 1, 0, 'L');
$pdf->Cell(40, 8, 'Aseguradora', 1, 0, 'L');
$pdf->Cell(40, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Número de póliza'), 1, 1, 'L');

$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(13, 8, '', 1, 0, 'L', true);
$pdf->Cell(13, 8, '', 1, 0, 'L', true);
$pdf->Cell(50, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 1, 'L', true);

$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(13, 8, '', 1, 0, 'L', true);
$pdf->Cell(13, 8, '', 1, 0, 'L', true);
$pdf->Cell(50, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 1, 'L', true);

$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(13, 8, '', 1, 0, 'L', true);
$pdf->Cell(13, 8, '', 1, 0, 'L', true);
$pdf->Cell(50, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 1, 'L', true);

$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(13, 8, '', 1, 0, 'L', true);
$pdf->Cell(13, 8, '', 1, 0, 'L', true);
$pdf->Cell(50, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 1, 'L', true);

$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(13, 8, '', 1, 0, 'L', true);
$pdf->Cell(13, 8, '', 1, 0, 'L', true);
$pdf->Cell(50, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 1, 'L', true);

$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(50, 8, 'Beneficiarios designados', 'L', 0, 'L');
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(66, 8, '(aplica solo para el beneficio de vida)', 0, 0, 'L');
$pdf->Cell(40, 8, 'Suma asegurada', 'R', 0, 'L');
$pdf->Cell(40, 8, '', 1, 1, 'L', true);

$pdf->Cell(116, 8, 'Nombre', 1, 0, 'L');
$pdf->Cell(40, 8, 'Parentesco', 1, 0, 'C');
$pdf->Cell(40, 8, 'Porcentaje', 1, 1, 'C');

$pdf->Cell(116,8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 1, 'L', true);

$pdf->Cell(116,8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 1, 'L', true);

$pdf->Cell(116,8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 1, 'L', true);

$pdf->Cell(116,8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 1, 'L', true);
//contingentes
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(196, 8, 'Beneficiarios contingentes:', 1, 1, 'L');

$pdf->SetFont('Arial', '', 9);
$pdf->Cell(116, 8, 'Nombre', 1, 0, 'L');
$pdf->Cell(40, 8, 'Parentesco', 1, 0, 'C');
$pdf->Cell(40, 8, 'Porcentaje', 1, 1, 'C');

$pdf->Cell(116,8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 1, 'L', true);

$pdf->Cell(116,8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 0, 'L', true);
$pdf->Cell(40, 8, '', 1, 1, 'L', true);

// 🔹 septima sección en rojo
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(200, 0, 0);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(0, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'C. DECLARACIÓN Y ACEPTACIÓN DE CONDICIONES'), 1, 1, 'L', true);

$pdf->SetFont('Arial', '', 9);
$pdf->SetFillColor(230, 230, 230); // Fondo gris claro
$pdf->SetTextColor(0, 0, 0); // Texto negro
$pdf->MultiCell(196, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Declaro expresamente que:
1. Tanto mi actividad, profesión u oficio es lícita y la ejerzo dentro de los marcos legales y los recursos que poseo,
no provienen de ninguna actividad ilícita de las contempladas en el código penal hondureño.

'), 'LR', 'J');
$pdf->MultiCell(196, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '2. Este formulario únicamente constituye una solicitud de seguros y no representa garantía de que la misma será
aceptada por MAPFRE; ni que la misma, en todo caso, será aceptada en los mismos términos solicitados.

'), 'LR', 'J');
$pdf->MultiCell(196, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '3. La información que he suministrado en esta solicitud es veraz y verificable y puede ser confirmada en cualquier
momento por esta compañía.

'), 'LR', 'J');
$pdf->MultiCell(196, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '4. Toda la información anterior, ha sido escrita o dictada por mi, de acuerdo con mi leal saber y entender, y que la
misma es la base para que MAPFRE emita la cobertura de seguro solicitado. Así mismo, libero a MAPFRE de
toda responsabilidad sobre la cobertura otorgada, en caso de omisiones o declaraciones falsas o inexactas sobre
hechos conocidos por mi, que de haber sido debidamente conocidos por MAPFRE hubieran podido influir de
modo determinante para que la cobertura solicitada no se suscribiera, o se hubiera suscrito en condiciones
distintas.

'), 'LR', 'J');
$pdf->MultiCell(196, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '5. Queda debidamente entendido y aceptado por parte de MAPFRE que el uso de la información obtenida con
motivo de esta autorización, esta circunscrito estrictamente al proceso de análisis para suscribir o denegar la
cobertura solicitada, y el trámite posterior de reclamaciones de derecho si la misma fuera otorgada; por tanto,
únicamente podrá ser recopilada, consultada y utilizada por suscriptores de riesgo o analistas de reclamos de
MAPFRE, en razón de su naturaleza, MAPFRE deberá garantizar la debida custodia, confidencialidad absoluta y
el buen uso de esta información.

'), 'LR', 'J');
$pdf->MultiCell(196, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '6. Acepto el hecho que, de demostrarse falso testimonio en la información aquí presentada, MAPFRE Seguros
Honduras, S.A. está facultada a dar por terminado el Contrato de Seguro según se indica en el artículo 1141 del
Código de Comercio, sin que esto implique responsabilidad alguna para MAPFRE frente al asegurado.

'), 'LR', 'J');
$pdf->MultiCell(196, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '7. Manifiesto que he leído toda la solicitud y las condiciones generales de la póliza a través de mi agente de
seguros, las cuales formarán parte integra del contrato y que he tomado conocimiento de mi derecho a decidir
sobre la contratación del seguro y a la libre elección de la institución aseguradora y estoy de acuerdo con ella.

'), 'LR', 'J');
$pdf->MultiCell(196, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '8. Autorizo por este medio a los médicos, laboratorios, clínicas u hospitales que han atendido a mi cónyuge, hijos
en lo particular a mi persona en la recuperación de la salud, para que suministren a MAPFRE S.A. las
informaciones que ésta requiera en relación al seguro que solicito, relevándolos de cualquier prohibición legal que
exista sobre revelación de los datos de sus registros con respecto a mi persona. Queda entendido y convenido que
una copia fotostática de esta autorización debe considerarse tan efectiva y valida como original.

'), 'LRB', 'J');

$pdf->Ln(2);

// 🔹 octava sección en rojo
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(200, 0, 0);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(0, 8, 'D. FIRMAS', 1, 1, 'L', true);

$pdf->SetFont('Arial', '', 9);
$pdf->SetFillColor(230, 230, 230); // Fondo gris claro
$pdf->SetTextColor(0, 0, 0); // Texto negro

$pdf->Cell(196, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT','Firmado en la ciudad de________________________, a los_____________dia(s) del mes de____________del año ______'), 'RL', 1, 'C');

$pdf->MultiCell(196, 5, '



', 'LR', 'J');

$pdf->Cell(98, 4, '________________________', 'L', 0, 'C');
$pdf->Cell(98, 4, '________________________', 'R', 1, 'C');

$pdf->Cell(98, 8, 'Firma del asegurado', 'LB', 0, 'C');
$pdf->Cell(98, 8, 'Firma del agente', 'RB', 1, 'C');
/*$pdf->SetDrawColor(160, 160, 160);
$pdf->SetLineWidth(1);
$anchoPagina = $pdf->GetPageWidth();
$pdf->Line(8.7, 18.7, $anchoPagina-9.3, 18.7);

$pdf->AddFont('Arial', '', 'Arial.php');
$pdf->SetFont('Arial','',9);

$x = 55;
$selectTipoSeguro = "SELECT ts.TIPO_SEGURO, CONCAT(UCASE(SUBSTRING(ts.NOMBRE, 1, 1)), LOWER(SUBSTRING(ts.NOMBRE, 2))) AS NOMBRE
from tipo_seguro ts";
$resultTipoSeguro = $conn->query($selectTipoSeguro);
if ($resultTipoSeguro->num_rows > 0) {
    while($row = $resultTipoSeguro->fetch_assoc()) {
        $pdf->DrawCheckBox(utf8_decode($row["NOMBRE"]), $x, 21.9, true);
        $x += 50.5;
    }
} else {
    echo "No hay opciones";
}
$resultTipoSeguro->close();
$pdf->SetFontSize(14);
$pdf->text(162,24,utf8_decode("Código: SPN-F.GTP-63"));
$x = 8.5;
$y = 30.2;
$z = 121;
//ancho form 206.55

$pdf->SetFont('Arialb','',9);
$label = utf8_decode("Nombre del contratante:");
$pdf->text($x,$y,$label);
$x += $pdf->GetStringWidth($label);
$pdf->UnderlinedText($nombresContratante." ".$apellidosContratante, $x, $y-0.5, $z);

$pdf->SetFontSize(9);
$x += ($z+3);
$z = 22;
$label = utf8_decode("No. de Póliza:");
$pdf->text($x,$y,$label);
$x += $pdf->GetStringWidth($label);
$pdf->UnderlinedText($numeroPoliza, $x, $y-0.5, $z);

$pdf->SetFont('Arialb','',9);
$x = 8.5;
$y = $y + 9;
$z = 24;
$label = utf8_decode("Categoría del Empleado:");
$pdf->text($x,$y,$label);
$x += $pdf->GetStringWidth($label);
$pdf->UnderlinedText($categoriaEmpleadoNombre, $x, $y-0.5, $z);

$pdf->SetFontSize(9);
$x += ($z+3);
$z = 28;
$label = utf8_decode("Máximo Vitalicio (Gastos Médicos):");
$pdf->text($x,$y,$label);
$x += $pdf->GetStringWidth($label);
$pdf->UnderlinedText($maximoVitalicio, $x, $y-0.5, $z);

$pdf->SetFontSize(9);
$x += ($z+3);
$z = 29.2;
$label = utf8_decode("Suma Asegurada (Vida):");
$pdf->text($x,$y,$label);
$x += $pdf->GetStringWidth($label);
$pdf->UnderlinedText($sumaAsegurada, $x, $y-0.5, $z);

$pdf->SetFontSize(9);
$x = 8.5;
$y = $y + 9;
$z = 45;
$label = utf8_decode("Seguro de Vida Opcional:");
$pdf->text($x,$y,$label);
$x += ($pdf->GetStringWidth($label)+3);
if ($seguroVidaOpcional == '1') {
    $pdf->DrawCheckBox("Si", $x, $y-2.5, true);
}else {
    $pdf->DrawCheckBox("Si", $x, $y-2.5, false);
}
if ($seguroVidaOpcional == '0') {
    $x += 18;
    $pdf->DrawCheckBox("No", $x, $y-2.5, true);
}else {
    $x += 18;
    $pdf->DrawCheckBox("No", $x, $y-2.5, false);
}

$x += 18;
$z = 44;
$label = utf8_decode("Suma Asegurada Opcional:");
$pdf->text($x,$y,$label);
$x += $pdf->GetStringWidth($label);
$pdf->UnderlinedText(utf8_decode((empty(trim($_POST["sumaAseguradaOpcional"])) ? "N/A" : trim($_POST["sumaAseguradaOpcional"]))), $x, $y-0.5, $z);

$pdf->SetFontSize(9);
$x += ($z+1);
$z = 24.2;
$label = utf8_decode("(Pólizas que Aplique)");
$pdf->text($x,$y,$label);

//tabla DATOS GENERALES DEL ASEGURADO
$pdf->SetFont('Arialb', '',9);
$x = 8.5;
$y = $y + 5;
$pdf->text($x,$y,"Datos generales del asegurado:");
$pdf->SetFont('Arial','',9);
$y = $y + 1;
$tableHeader = array('Primer apellido', 'Segundo apellido', 'Primer nombre', 'Segundo nombre', 'Apellido casada');
// Datos de la fila
$data = array(
    array(
        utf8_decode($primerApellidoAsegurado), 
        utf8_decode($segundoApellidoAsegurado), 
        utf8_decode($primerNombreAsegurado), 
        utf8_decode($segundoNombreAsegurado),
        utf8_decode((empty(trim($apellidoCasadaAsegurado)) ? "N/A" : trim($apellidoCasadaAsegurado)))
    )
);
$pdf->BasicTable($tableHeader,$data,$x-1,$y);
$pdf->SetFontSize(9);
$x = 8.5;
$pdf->SetX($x-1);
$pdf->Cell(120, 5, '', 'LBR',0);
$pdf->Cell(80, 5,'','BR',0);
$selectTipoIdentificacion = "SELECT ti.TIPO_IDENTIFICACION , CONCAT(UCASE(SUBSTRING(ti.NOMBRE, 1, 1)), LOWER(SUBSTRING(ti.NOMBRE, 2))) AS NOMBRE
from tipo_identificacion ti";
$resultTipoIdentificacion = $conn->query($selectTipoIdentificacion);
$y = $y + 13.5;
if ($resultTipoIdentificacion->num_rows > 0) {
    while($row = $resultTipoIdentificacion->fetch_assoc()) {
        $checked = "";
        if ($row["TIPO_IDENTIFICACION"] == $tipoIdentificacionAsegurado) {
            $pdf->DrawCheckBox(utf8_decode($row["NOMBRE"]), $x+2, $y-2.7, true);
        }else {
            $pdf->DrawCheckBox(utf8_decode($row["NOMBRE"]), $x+2, $y-2.7, false);
        }
        $x += 45;
    }
} else {
    echo "No hay opciones";
}
$x -= 15;
$resultTipoIdentificacion->close();
//5
$label = utf8_decode("No. identificación");
$pdf->text($x,$y,$label);
$x += $pdf->GetStringWidth($label);
$pdf->SetFontSize(8);
$label = $numeroIdentificacionAsegurado;
$pdf->text($x+2,$y,$label);
$pdf->SetFontSize(9);
$pdf->Ln();

$x = 8.5;
$y += 5;
$pdf->SetX($x-1);
$pdf->Cell(88, 5, '', 'LBR',0);
$pdf->Cell(28, 5,'','B',0);
$pdf->Cell(7, 5, '', 'BR',0);
$pdf->Cell(8, 5,'','BR',0);
$pdf->Cell(10, 5, '', 'BR',0);
$pdf->Cell(28, 5,'','BR',0);
$pdf->Cell(31, 5, '', 'BR',0);
$label = utf8_decode("Lugar de nacimiento:");
$pdf->text($x,$y,$label);
$x += $pdf->GetStringWidth($label)+2;
$pdf->SetFontSize(8);
$label = utf8_decode($lugarNacimientoAsegurado);
$pdf->text($x,$y,$label);
$x += 58;
$pdf->SetFontSize(9);
//$x += $pdf->GetStringWidth($label);
$label = utf8_decode("Fecha de nacimiento:");
$pdf->text($x,$y,$label);
$x += $pdf->GetStringWidth($label);
$pdf->SetFontSize(7.5);
list($anio, $mes, $dia) = explode('-', $fechaNacimientoAsegurado);
$fecha_nacimiento = array(
    'dia' => $dia,
    'mes' => $mes,
    'anio' => $anio
);
$x += 1;
$label = utf8_decode("Día");
$pdf->text($x,$y-2,$label);
$pdf->text($x,$y,$fecha_nacimiento["dia"]);
$x += $pdf->GetStringWidth($label);
$label = utf8_decode("Mes");
$pdf->text($x+3,$y-2,$label);
$pdf->text($x+3,$y,$fecha_nacimiento["mes"]);
$x += $pdf->GetStringWidth($label);
$label = utf8_decode("Año");
$pdf->text($x+7,$y-2,$label);
$pdf->text($x+7,$y,$fecha_nacimiento["anio"]);
$pdf->SetFontSize(9);
$label = utf8_decode("Edad:");
$pdf->text($x+16,$y,$label);
$x += $pdf->GetStringWidth($label);
$pdf->SetFontSize(8);
$label = $edadAsegurado;
$pdf->text($x+18,$y,$label);
$pdf->SetFontSize(9);
$x += $pdf->GetStringWidth($label);
$label = utf8_decode("Sexo:");
$pdf->text($x+33,$y,$label);
$x += $pdf->GetStringWidth($label);
$selectTipoSexo = "SELECT s.SEXO , CONCAT(UCASE(SUBSTRING(s.CODIGO, 1, 1)), LOWER(SUBSTRING(s.CODIGO, 2))) AS NOMBRE
from sexo s";
$resultTipoSexo = $conn->query($selectTipoSexo);
$x += 25;
if ($resultTipoSexo->num_rows > 0) {
    while($row = $resultTipoSexo->fetch_assoc()) {
        $checked = "";
        if ($row["SEXO"] == $sexoAsegurado) {
            $pdf->DrawCheckBox(utf8_decode($row["NOMBRE"]), $x+9, $y-3, true);
        }else {
            $pdf->DrawCheckBox(utf8_decode($row["NOMBRE"]), $x+9, $y-3, false);
        }
        $x += 10;
    }
} else {
    echo "No hay opciones";
}
$resultTipoSexo->close();
$pdf->Ln();

$x = 8.5;
$y += 5;
$pdf->SetFontSize(9);
$pdf->SetX($x-1);
$pdf->Cell(80, 5, '', 'LBR',0);
$pdf->Cell(120, 5,'','BR',0);
$label = utf8_decode("Nacionalidad:");
$pdf->text($x,$y,$label);
$x += $pdf->GetStringWidth($label);
$pdf->SetFontSize(8);
$label = utf8_decode($nacionalidadAsegurado);
$pdf->SetFontSize(9);
$pdf->text($x+2,$y,$label);
$label = utf8_decode("Profesión u oficio:");
$pdf->text($x+62,$y,$label);
$x += $pdf->GetStringWidth($label);
$pdf->SetFontSize(8);
$label = utf8_decode($profesionAsegurado);
$pdf->text($x+64,$y,$label);
$pdf->SetFontSize(9);
$pdf->Ln();

$x = 8.5;
$y += 5;
$pdf->SetFont('Arial', '',9);
$pdf->SetX($x-1);
$pdf->Cell(200, 5, '', 'LBR',0);
$label = utf8_decode("Estado civil:");
$pdf->text($x,$y,$label);
$x += $pdf->GetStringWidth($label);
$selectEstadoCivil = "SELECT ec.ESTADO_CIVIL , CONCAT(UCASE(SUBSTRING(ec.NOMBRE, 1, 1)), LOWER(SUBSTRING(ec.NOMBRE, 2))) AS NOMBRE
from estado_civil ec";
$resultEstadoCivil = $conn->query($selectEstadoCivil);
if ($resultEstadoCivil->num_rows > 0) {
    while($row = $resultEstadoCivil->fetch_assoc()) {
        $checked = "";
        if ($row["ESTADO_CIVIL"] == $estadoCivilAsegurado) {
            $pdf->DrawCheckBox(utf8_decode($row["NOMBRE"]), $x+9, $y-3, true);
        }else {
            $pdf->DrawCheckBox(utf8_decode($row["NOMBRE"]), $x+9, $y-3, false);
        }
        $x += 24;
    }
} else {
    echo "No hay opciones";
}
$resultEstadoCivil->close();
$pdf->Ln();

$x = 8.5;
$y += 5;
$pdf->SetX($x-1);
$pdf->SetFont('Arial', '',9);
$pdf->Cell(80, 5, '', 'LBR',0);
$pdf->Cell(120, 5,'','BR',0);
$label = utf8_decode("Estatura en metros:");
$pdf->text($x,$y,$label);
$x += $pdf->GetStringWidth($label);
$label = $estaturaAsegurado;
$pdf->SetFontSize(8);
$pdf->text($x+2,$y,$label);
$pdf->SetFontSize(9);
$label = utf8_decode("Peso en libras:");
$pdf->text($x+54,$y,$label);
$x += $pdf->GetStringWidth($label)+54;
$label = $pesoAsegurado;
$pdf->SetFontSize(8);
$pdf->text($x+2,$y,$label);
$pdf->SetFontSize(9);
$pdf->Ln();

$x = 8.5;
$y += 5;
$pdf->SetX($x-1);
$pdf->Cell(32, 5, '', 'LBR',0);
$pdf->Cell(48, 5,'','BR',0);
$pdf->Cell(60, 5,'','BR',0);
$pdf->Cell(60, 5,'','BR',0);
$label = utf8_decode("Fuma:");
$pdf->text($x,$y,$label);
$x += $pdf->GetStringWidth($label)+2;
for ($i=0; $i <= 1; $i++) { 
    if ($i == $fumaAsegurado) {
        $pdf->DrawCheckBox("No", $x, $y-3, true);
    }else {
        $pdf->DrawCheckBox("Si", $x, $y-3, false);
    }
    $x += 12;
}
$x -= 12;
$pdf->SetFont('Arial', '',9);
$label = utf8_decode("¿Cuántos cigarrillos al día?");
$pdf->text($x+10,$y,$label);
$x += $pdf->GetStringWidth($label)+10;
$pdf->SetFontSize(8);
$label = utf8_decode((empty(trim($numeroCigarrosAsegurado)) ? "N/A" : trim($numeroCigarrosAsegurado)));
$pdf->text($x+3,$y,$label);
$pdf->SetFontSize(9);
$x += 3;
$label = utf8_decode("¿Ingiere bebidas alcohólicas?");
$pdf->text($x+10,$y,$label);
$x += $pdf->GetStringWidth($label)+11;
for ($i=0; $i <= 1; $i++) { 
    if ($i == $bebeAsegurado) {
        $pdf->DrawCheckBox("No", $x, $y-3, true);
    }else {
        $pdf->DrawCheckBox("Si", $x, $y-3, false);
    }
    $x += 10;
}
$pdf->SetFont('Arial', '',9);
$label = utf8_decode("¿Con que frecuencia?");
$pdf->text($x,$y,$label);
$x += $pdf->GetStringWidth($label);
$label = utf8_decode((empty(trim($frecuenciaBebeAsegurado)) ? "N/A" : trim($frecuenciaBebeAsegurado)));
$pdf->SetFontSize(8);
$pdf->text($x+1,$y,$label);
$pdf->SetFontSize(9);
$pdf->Ln();

$x = 8.5;
$y += 5;
$pdf->SetX($x-1);
$pdf->Cell(60, 5, '', 'LBR',0);
$pdf->Cell(48, 5,'','BR',0);
$pdf->Cell(92, 5,'','BR',0);
$label = utf8_decode("Cargo que desempeña:");
$pdf->text($x,$y,$label);
$pdf->SetFontSize(8);
$x += $pdf->GetStringWidth($label)+4;
$label = utf8_decode($cargoAsegurado);
$pdf->text($x,$y,$label);
$pdf->SetFontSize(9);
$label = utf8_decode("Departamento:");
$pdf->text($x+29,$y,$label);
$x += $pdf->GetStringWidth($label)+29;
$pdf->SetFontSize(8);
$label = utf8_decode($departamentoCompaniaAsegurado);
$pdf->text($x+1,$y,$label);
$pdf->SetFontSize(9);
$label = utf8_decode("Fecha de ingreso a la compañia:");
$pdf->text($x+28,$y,$label);
$x += $pdf->GetStringWidth($label)+28;
$label = utf8_decode(date("d/m/Y", strtotime($fechaIngresoAsegurado)));
$pdf->SetFontSize(8);
$pdf->text($x+2,$y,$label);
$pdf->SetFontSize(9);
$pdf->Ln();

$x = 8.5;
$y += 5;
$pdf->SetX($x-1);
$pdf->Cell(60, 5, '', 'LBR',0);
$pdf->Cell(48, 5,'','BR',0);
$pdf->Cell(92, 5,'','BR',0);
$label = utf8_decode("Sueldo mensual:");
$pdf->text($x,$y,$label);
$x += $pdf->GetStringWidth($label)+1;
$pdf->SetFontSize(8);
$label = $sueldoAsegurado;
$pdf->text($x,$y,$label);
$pdf->SetFontSize(9);
$label = utf8_decode("Sucursal:");
$pdf->text($x+37,$y,$label);
$x += $pdf->GetStringWidth($label)+37;
$pdf->SetFontSize(8);
$label = utf8_decode($sucursalAsegurado);
$pdf->text($x+1,$y,$label);
$pdf->SetFontSize(9);
$label = utf8_decode("Número de afiliación al Seguro Social:");
$pdf->text($x+36.5,$y,$label);
$x += $pdf->GetStringWidth($label)+36.5;
$pdf->SetFontSize(8);
$label = $numeroAfiliacionSeguroAsegurado;
$pdf->text($x+1,$y,$label);
$pdf->SetFontSize(9);
$pdf->Ln(10);

//tabla DATOS DEL CONYUGE
$x = 8.5;
$y += 5;
$pdf->SetFont('Arialb', '',9);
$pdf->text($x,$y,"Datos del conyuge:");
$pdf->SetFont('Arial','',9);

$y += 5;
$pdf->SetX($x-1);
$pdf->Cell(200, 5, '', 1,0);
$label = utf8_decode("Nombre:");
$pdf->text($x,$y,$label);
$x += $pdf->GetStringWidth($label)+2;
$pdf->SetFontSize(8);
$label = utf8_decode((empty(trim($nombresConyuge." ".$apellidosConyuge)) ? "N/A" : trim($nombresConyuge." ".$apellidosConyuge)));
$pdf->text($x,$y,$label);
$pdf->Ln();

$x = 8.5;
$y += 5;
$pdf->SetX($x-1);
$pdf->Cell(200, 5, '', 1,0);
$pdf->SetFontSize(9);
$label = utf8_decode("Empresa donde labora:");
$pdf->text($x,$y,$label);
$x += $pdf->GetStringWidth($label)+2;
$pdf->SetFontSize(8);
$label = utf8_decode((empty(trim($empresaConyuge)) ? "N/A" : trim($empresaConyuge)));
$pdf->text($x,$y,$label);
$pdf->Ln();

$x = 8.5;
$y += 5;
$pdf->SetX($x-1);
$pdf->Cell(87, 5, '', 'LBR',0);
$pdf->Cell(113, 5, '', 'BR',0);
$pdf->SetFontSize(9);
$label = utf8_decode("Celular:");
$pdf->text($x,$y,$label);
$x += $pdf->GetStringWidth($label)+2;
$pdf->SetFontSize(8);
$label = utf8_decode((empty(trim($celularConyuge)) ? "N/A" : trim($celularConyuge)));
$pdf->text($x,$y,$label);
$pdf->SetFontSize(9);
$label = utf8_decode("E-mail");
$pdf->text($x+75,$y,$label);
$x += $pdf->GetStringWidth($label)+77;
$pdf->SetFontSize(8);
$label = utf8_decode((empty(trim($emailConyuge)) ? "N/A" : trim($emailConyuge)));
$pdf->text($x,$y,$label);
$pdf->Ln(10);

$x = 8.5;
$y += 5;
//tabla DIRECCION DEL ASEGURADO
$pdf->SetFont('Arialb', '',9);
$pdf->text($x,$y,"Direccion del asegurado:");
$pdf->SetFont('Arial','',9);

$y += 5;
$pdf->SetX($x-1);
$pdf->Cell(51, 5, '', 1,0);
$pdf->Cell(66, 5, '', 1,0);
$pdf->Cell(83, 5, '', 1,0);
$label = utf8_decode("País:");
$pdf->text($x,$y,$label);
$x += $pdf->GetStringWidth($label)+2;
$pdf->SetFontSize(8);
$label = utf8_decode($paisAsegurado);
$pdf->text($x,$y,$label);
$pdf->SetFontSize(9);
$label = utf8_decode("Departamento:");
$pdf->text($x+43,$y,$label);
$x += $pdf->GetStringWidth($label)+45;
$pdf->SetFontSize(8);
$label = utf8_decode($departamentoAseguradoNombre);
$pdf->text($x,$y,$label);
$pdf->SetFontSize(9);
$label = utf8_decode("Ciudad / Municipio:");
$pdf->text($x+44,$y,$label);
$x += $pdf->GetStringWidth($label)+46;
$pdf->SetFontSize(8);
$label = utf8_decode($ciudadAsegurado);
$pdf->text($x,$y,$label);
$pdf->Ln();

$x = 8.5;
$y += 5;
$pdf->SetX($x-1);
$pdf->Cell(51, 5, '', 'LBR',0);
$pdf->Cell(42, 5, '', 'BR',0);
$pdf->Cell(45, 5, '', 'BR',0);
$pdf->Cell(25, 5, '', 'BR',0);
$pdf->Cell(37, 5, '', 'BR',0);
$pdf->SetFontSize(9);
$label = utf8_decode("Colonia:");
$pdf->text($x,$y,$label);
$x += $pdf->GetStringWidth($label)+2;
$pdf->SetFontSize(8);
$label = utf8_decode($coloniaAsegurado);
$pdf->text($x,$y,$label);
$pdf->SetFontSize(9);
$label = utf8_decode("Calle:");
$pdf->text($x+39,$y,$label);
$x += $pdf->GetStringWidth($label)+41;
$pdf->SetFontSize(8);
$label = utf8_decode((empty(trim($calleAsegurado)) ? "N/A" : trim($calleAsegurado)));
$pdf->text($x,$y,$label);
$pdf->SetFontSize(9);
$label = utf8_decode("Avenida:");
$pdf->text($x+33,$y,$label);
$x += $pdf->GetStringWidth($label)+35;
$pdf->SetFontSize(8);
$label = utf8_decode((empty(trim($avenidaAsegurado)) ? "N/A" : trim($avenidaAsegurado)));
$pdf->text($x,$y,$label);
$pdf->SetFontSize(9);
$label = utf8_decode("Bloque:");
$pdf->text($x+31,$y,$label);
$x += $pdf->GetStringWidth($label)+32;
$pdf->SetFontSize(8);
$label = utf8_decode((empty(trim($bloqueAsegurado)) ? "N/A" : trim($bloqueAsegurado)));
$pdf->text($x,$y,$label);
$pdf->SetFontSize(9);
$label = utf8_decode("Casa No.:");
$pdf->text($x+14,$y,$label);
$x += $pdf->GetStringWidth($label)+16;
$pdf->SetFontSize(8);
$label = utf8_decode((empty(trim($casaAsegurado)) ? "N/A" : trim($casaAsegurado)));
$pdf->text($x,$y,$label);
$pdf->Ln();

$x = 8.5;
$y += 5;
$pdf->SetX($x-1);
$pdf->Cell(60, 5, '', 'LBR',0);
$pdf->Cell(57, 5, '', 'BR',0);
$pdf->Cell(83, 5, '', 'BR',0);
$pdf->SetFontSize(9);
$label = utf8_decode("Teléfono:");
$pdf->text($x,$y,$label);
$x += $pdf->GetStringWidth($label)+2;
$pdf->SetFontSize(8);
$label = utf8_decode((empty(trim($telefonoAsegurado)) ? "N/A" : trim($telefonoAsegurado)));
$pdf->text($x,$y,$label);
$pdf->SetFontSize(9);
$label = utf8_decode("Celular:");
$pdf->text($x+46,$y,$label);
$x += $pdf->GetStringWidth($label)+48;
$pdf->SetFontSize(8);
$label = $celularAsegurado;
$pdf->text($x,$y,$label);
$pdf->SetFontSize(9);
$label = utf8_decode("E-mail:");
$pdf->text($x+45,$y,$label);
$x += $pdf->GetStringWidth($label)+47;
$pdf->SetFontSize(8);
$label = $emailAsegurado;
$pdf->text($x,$y,$label);
$pdf->Ln(7);

$x = 8.5;
$y += 11;
$pdf->SetX($x-1);
$pdf->SetFont('Arialb', '',9.5);
$pdf->SetFillColor(35, 31, 32);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(200,5,'Beneficiarios del seguro de vida',1,0,'C',true);
$pdf->Ln();

$x = 8.5;
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial','',9);
$pdf->SetX($x-1);
$pdf->Cell(200,2,'',0,0);
$label = utf8_decode("Por este medio declaro mi único beneficiario de mi seguro de vida a la empresa contratante que ha suscrito la póliza para la cual he completado esta");
$pdf->text($x-1,$y,$label);
$pdf->Ln();
$x = 8.5;
$y += 3;
$pdf->SetX($x-1);
$pdf->Cell(200,3,'',0,0);
$label = utf8_decode("solicitud, con el propósito de cubrir la respondabilidad laboral en base a lo establecido en el Código de Trabajo; Si la suma asegurada contratada supera");
$pdf->text($x-1,$y,$label);
$pdf->Ln();
$x = 8.5;
$y += 3;
$pdf->SetX($x-1);
$pdf->Cell(200,3,'',0,0);
$label = utf8_decode("la obligación laboral del contratante de esta póliza; Designo como beneficiario (s) por el remanente de la suma asegurada si existiere a:");
$pdf->text($x-1,$y,$label);
$pdf->Ln(5);

$pdf->SetFontSize(9);
$pdf->SetX($x-1);
$pdf->Cell(93,5,'Nombre completo',1,0,'C');
$pdf->Cell(35,5,'Parentesco','TBR',0,'C');
$pdf->Cell(40,5,'Fecha de nacimiento','TBR',0,'C');
$pdf->Cell(32,5,'Porcentaje','TBR',0,'C');
$y += 9;
$pdf->Ln();

$pdf->SetFontSize(8);
foreach ($beneficiariosSeguro as $beneficiario) {
    if (!empty($beneficiario->parentesco)) {
        $selectParentesco = "SELECT CONCAT(UCASE(SUBSTRING(p.NOMBRE, 1, 1)), LOWER(SUBSTRING(p.NOMBRE, 2))) AS NOMBRE 
        from parentesco p 
        where p.parentesco = ?";
        $resultParentesco = $conn->prepare($selectParentesco);
        $resultParentesco->bind_param("s", $beneficiario->parentesco);
        $resultParentesco->execute();
        $resultParentesco = $resultParentesco->get_result();
        $row = $resultParentesco->fetch_assoc();
        $parentescoNombre = $row["NOMBRE"];
        $resultParentesco->close();

        $pdf->SetX($x-1);
        $pdf->Cell(93,5, utf8_decode($beneficiario->nombres ." ".$beneficiario->apellidos),'LBR',0,'C');
        $pdf->Cell(35,5,utf8_decode($parentescoNombre),'BR',0,'C');
        $pdf->Cell(40,5,date("d/m/Y", strtotime($beneficiario->fechaNacimiento)),'BR',0,'C');
        $pdf->Cell(32,5,$beneficiario->porcentaje."%",'BR',0,'C');
        $y += 5;
        $pdf->Ln();
    }else{
        $pdf->SetX($x-1);
        $pdf->Cell(93,5,"N/A",'LBR',0,'C');
        $pdf->Cell(35,5,"N/A",'BR',0,'C');
        $pdf->Cell(40,5,"N/A",'BR',0,'C');
        $pdf->Cell(32,5,"N/A",'BR',0,'C');
        $y += 5;
        $pdf->Ln();
    }
}

$pdf->SetX($x-1);
$pdf->SetFont('Arialb', '',9.5);
$pdf->SetFillColor(35, 31, 32);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(200,5,'Beneficiarios de contingencia',1,0,'C',true);
$y += 5;
$pdf->Ln();

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial','',9);
$pdf->SetX($x-1);
$pdf->Cell(200,2,'',0,0);
$label = utf8_decode("En caso de fallecimiento de él (los) beneficiario (s) designado(s) por el remanente de la suma asegurada; si existiere, nombro como beneficiario (s) de");
$pdf->text($x-1,$y,$label);
$y += 3;
$pdf->Ln(3);

$pdf->SetX($x-1);
$pdf->Cell(200,2,'',0,0);
$label = utf8_decode("contingencia a:");
$pdf->text($x-1,$y,$label);
$y += 4;
$pdf->Ln(4);

$pdf->SetFont('Arial', '',9);
$pdf->SetX($x-1);
$pdf->Cell(93,5,'Nombre completo',1,0,'C');
$pdf->Cell(35,5,'Parentesco','TBR',0,'C');
$pdf->Cell(40,5,'Fecha de nacimiento','TBR',0,'C');
$pdf->Cell(32,5,'Porcentaje','TBR',0,'C');
$pdf->Ln();

$pdf->SetFontSize(8);
foreach ($beneficiariosContingencia as $beneficiario) {
    if (!empty($beneficiario->parentesco)) {
        $selectParentesco = "SELECT CONCAT(UCASE(SUBSTRING(p.NOMBRE, 1, 1)), LOWER(SUBSTRING(p.NOMBRE, 2))) AS NOMBRE 
        from parentesco p 
        where p.parentesco = ?";
        $resultParentesco = $conn->prepare($selectParentesco);
        $resultParentesco->bind_param("s", $beneficiario->parentesco);
        $resultParentesco->execute();
        $resultParentesco = $resultParentesco->get_result();
        $row = $resultParentesco->fetch_assoc();
        $parentescoNombre = $row["NOMBRE"];
        $resultParentesco->close();

        $pdf->SetX($x-1);
        $pdf->Cell(93,5,utf8_decode($beneficiario->nombres ." ".$beneficiario->apellidos),'LBR',0,'C');
        $pdf->Cell(35,5,utf8_decode($parentescoNombre),'BR',0,'C');
        $pdf->Cell(40,5,date("d/m/Y", strtotime($beneficiario->fechaNacimiento)),'BR',0,'C');
        $pdf->Cell(32,5,$beneficiario->porcentaje."%",'BR',0,'C');
        $y += 5;
        $pdf->Ln();
    }else{
        $pdf->SetX($x-1);
        $pdf->Cell(93,5,"N/A",'LBR',0,'C');
        $pdf->Cell(35,5,"N/A",'BR',0,'C');
        $pdf->Cell(40,5,"N/A",'BR',0,'C');
        $pdf->Cell(32,5,"N/A",'BR',0,'C');
        $y += 5;
        $pdf->Ln();
    }
}

$pdf->SetX($x-1);
$pdf->SetFont('Arialb', '',9.5);
$pdf->SetFillColor(35, 31, 32);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(200,5,utf8_decode('Dependientes economicos (cónyuge e hijos) para el Plan Médico Hospitalario y/o Dental (si aplica)'),1,0,'C',true);
$pdf->Ln();

$y =+ 5;
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial','',9);
$pdf->SetX($x-1);
$pdf->Cell(93,5,'Nombre completo',1,0,'C');
$pdf->Cell(28,5,'Parentesco','TBR',0,'C');
$pdf->Cell(29,5,'Fecha de nacimiento','TBR',0,'C');
$pdf->Cell(25,5,'Peso (Libras)','TBR',0,'C');
$pdf->Cell(25,5,'Estatura (Metros)','TBR',0,'C');
$pdf->Ln();

$y =+ 5;
$pdf->SetFontSize(8);
foreach ($dependientes as $dependiente) {
    if (!empty($dependiente->parentesco)) {
        $selectParentesco = "SELECT CONCAT(UCASE(SUBSTRING(p.NOMBRE, 1, 1)), LOWER(SUBSTRING(p.NOMBRE, 2))) AS NOMBRE 
        from parentesco p 
        where p.parentesco = ?";
        $resultParentesco = $conn->prepare($selectParentesco);
        $resultParentesco->bind_param("s", $dependiente->parentesco);
        $resultParentesco->execute();
        $resultParentesco = $resultParentesco->get_result();
        $row = $resultParentesco->fetch_assoc();
        $parentescoNombre = $row["NOMBRE"];
        $resultParentesco->close();

        $pdf->SetX($x-1);
        $pdf->Cell(93,5,utf8_decode($dependiente->nombres." ".$dependiente->apellidos),'LBR',0,'C');
        $pdf->Cell(28,5,utf8_decode($parentescoNombre),'BR',0,'C');
        $pdf->Cell(29,5,date("d/m/Y", strtotime($dependiente->fechaNacimiento)),'BR',0,'C');
        $pdf->Cell(25,5,$dependiente->peso,'BR',0,'C');
        $pdf->Cell(25,5,$dependiente->estatura,'BR',0,'C');
        $y += 5;
        $pdf->Ln();
    }else{
        $pdf->SetX($x-1);
        $pdf->Cell(93,5,"N/A",'LBR',0,'C');
        $pdf->Cell(28,5,"N/A",'BR',0,'C');
        $pdf->Cell(29,5,"N/A",'BR',0,'C');
        $pdf->Cell(25,5,"N/A",'BR',0,'C');
        $pdf->Cell(25,5,"N/A",'BR',0,'C');
        $y += 5;
        $pdf->Ln();
    }
}


$pdf->AddPage('P', [215.9, 279.4]);
$pdf->UseFooter2();
$y = $pdf->GetY();
$pdf->SetTextColor(0, 0, 0);
$pdf->SetX($x-3);
$pdf->SetFont('Arialb','',8.5);
$pdf->Cell(154,12,'','1',0,'C');
$pdf->Cell(13,12,'Si o No','TRB',0,'C');
$pdf->Cell(36,12,'NOMBRE(S)','TRB',0,'C');
$x = 8.5;

$pdf->text($x-2,$y+4,utf8_decode('1 -'));
$pdf->SetFont('Arial','',8.5);

$pdf->text($x+1,$y+4,utf8_decode('Ha tenido alguna vez o tiene usted, su cónyuge o sus hijos; alguna de las enfermedades o trastornos siguientes,'));
$pdf->text($x+1,$y+7,utf8_decode('(Conteste Si o No con su puño y letra). Si la respuesta es afirmativa, especifique en la línea de la derecha el nombre'));
$pdf->text($x+1,$y+10,utf8_decode('de la persona a quien aplica.'));
$pdf->Ln(12);
$pdf->SetFontSize(8);

$abecedario = 'abcdefghijklmnopqrstuvwxyz';

$selectPreguntas = "SELECT p.PREGUNTA, p.TEXTO, p.SECCION, p.INDICE 
from pregunta p";
$resultPreguntas= $conn->query($selectPreguntas);
if ($resultPreguntas->num_rows > 0) {
    $seccion = 0;
    $numero = "";
    while($row = $resultPreguntas->fetch_assoc()) {
        $nombrePregunta = "";

        $selectSubPregunta = "SELECT s.SUBPREGUNTA, s.TEXTO AS NOMBRE, ps.IMPRIME  
        from pregunta p 
        inner join pregunta_subpregunta ps on p.PREGUNTA = ps.PREGUNTA 
        inner join subpregunta s on ps.SUB_PREGUNTA = s.SUBPREGUNTA 
        where p.PREGUNTA = ?";
        $resultSubPregunta = $conn->prepare($selectSubPregunta);
        $resultSubPregunta->bind_param("s", $row["PREGUNTA"]);
        $resultSubPregunta->execute();
        $resultSubPregunta = $resultSubPregunta->get_result();

        $nombrePregunta .= $abecedario[$row["INDICE"]-1].') '.utf8_decode($row["TEXTO"]);
        $lstDepe = "";
        $lstImprime = [];
        while($row2 = $resultSubPregunta->fetch_assoc()) {
            if ($row2["IMPRIME"] == "1") {
                $lstImprime[] = $row2["SUBPREGUNTA"];
                $nombrePregunta .= utf8_decode($row2["NOMBRE"]);   
            }
        }
        $resultSubPregunta->close();

        $seleccion = "0";
        $idRespuesta = "";

        $sql = "SELECT r.RESPUESTA as ID, r.SELECCION
        from respuesta r 
        where r.SEGURO = ? and r.PREGUNTA = ?";
        $resultR = $conn->prepare($sql);
        $resultR->bind_param("ii", $seguroId, $row["PREGUNTA"]);
        $resultR->execute();
        $resultR = $resultR->get_result();
        if ($resultR->num_rows > 0) {
            $rowR = $resultR->fetch_assoc();
            $seleccion = $rowR["SELECCION"];
            $idRespuesta = $rowR["ID"];
        }
        $resultR->close();
        $sizeSR = "";
        if (!empty($idRespuesta)) {

            $sql = "SELECT s.SUBPREGUNTA, s.TEXTO, s.SUBRESPUESTA 
            from subrespuesta s 
            where s.RESPUESTA = ?";
            $resultSR = $conn->prepare($sql);
            $resultSR->bind_param("i", $idRespuesta);
            $resultSR->execute();
            $resultSR = $resultSR->get_result();
            if ($resultSR->num_rows > 0) {
                while($rowSR = $resultSR->fetch_assoc()) {
                    if (in_array($rowSR["SUBPREGUNTA"], $lstImprime)) {
                        $nombrePregunta .= " ".utf8_decode($rowSR["TEXTO"]);
                        $sizeSR .= " ".utf8_decode($rowSR["TEXTO"]);
                    }
                    if ($rowSR["SUBPREGUNTA"] == "12") {
                        $lstDepe .= utf8_decode($rowSR["TEXTO"])."\n";
                    }
                }
            }
            $resultSR->close();
        }

        if ($row["SECCION"] != 1) {
            if ($seccion != $row["SECCION"]) {
                $seccion = $row["SECCION"];
                $numero = $row["SECCION"].'. ';
                $pdf->Ln(6);
            }else{
                $numero = "";
            }
        }
        if ($row["SECCION"] == 6 && $row["INDICE"] == 1) {
            $pdf->SetFont('Arialb','',8);
            $pdf->SetX($pdf->GetX()-3.5);
            $pdf->Cell(200, 5, utf8_decode("6. Para personas de sexo femenino"), 0, 0, 'L');
            $pdf->SetX($pdf->GetX()+3.5);
            $pdf->SetFont('Arial','',9);
            $pdf->Ln(6);
            $numero="";
        }
        if ($row["SECCION"] == 7 && $row["INDICE"] == 1) {
            $pdf->Ln();
            $pdf->SetFont('Arialb','',8);
            $pdf->SetX($pdf->GetX()-3);
            $pdf->Cell(200, 5, utf8_decode("7. Antecedentes Covid-19"), 0, 0, 'L');
            $pdf->SetX($pdf->GetX()+3);
            $pdf->SetFont('Arial','',8);
            $numero="";
            $pdf->Ln(6);
        }
        
        $lstDepe = substr($lstDepe, 0, -1);
        
        $pdf->MultiCellRow(3,$pdf->GetX(),$pdf->GetY(),[$nombrePregunta, $seleccion,$lstDepe ], $pdf, $numero, $sizeSR);
        
        if ($row["SECCION"] == 7 && $row["INDICE"] == 4) {
            $pdf->SetX($pdf->GetX()-1);
            $pdf->Cell(200, 5, utf8_decode("En caso de ser afirmativo, ¿Qué tratamiento recibió?"), 0, 0, 'L');
            $pdf->Ln();

            $sql = "SELECT t.TEXTO
            from subrespuesta s 
            inner join tratamiento t on s.SUBRESPUESTA = t.SUBRESPUESTA 
            where s.RESPUESTA = ?";
            $resultT = $conn->prepare($sql);
            $resultT->bind_param("i", $idRespuesta);
            $resultT->execute();
            $resultT = $resultT->get_result();
            $index = 1;
            if ($resultT->num_rows > 0) {
                while($rowT = $resultT->fetch_assoc()) {
                    $pdf->SetX($pdf->GetX()+4);
                    $pdf->Text($pdf->GetX()-4, $pdf->GetY()+4, $index.". ");
                    $pdf->Cell(200, 5, utf8_decode($rowT["TEXTO"]), 0, 0, 'L');
                    $pdf->Line($pdf->GetX()-200, $pdf->GetY()+4, $pdf->GetX()-100, $pdf->GetY()+4);
                    $pdf->Ln();
                    $index += 1;
                }
            }
            for ($i=($index-1); $i < 3; $i++) { 
                $pdf->SetX($pdf->GetX()+4);
                $pdf->Text($pdf->GetX()-4, $pdf->GetY()+4, ($i+1).". ");
                $pdf->Cell(200, 5, "", 0, 0, 'L');
                $pdf->Line($pdf->GetX()-200, $pdf->GetY()+4, $pdf->GetX()-100, $pdf->GetY()+4);
                $pdf->Ln();
            }
            $resultT->close();
        }
    }
} else {
    echo "No hay preguntas";
}
$resultPreguntas->close();
$lineas = [];
$columnas = [[],[],[]];
$contador = 1;
$sql = "SELECT s.TEXTO, s.SUBPREGUNTA, s.RESPUESTA  
from subrespuesta s 
inner join respuesta r on s.RESPUESTA = r.RESPUESTA 
where s.SUBPREGUNTA in (13,8,9,1,10,11) and r.SEGURO = ?";
$resultT = $conn->prepare($sql);
$resultT->bind_param("i", $seguroId);
$resultT->execute();
$resultT = $resultT->get_result();
$pdf->Ln(10);
$pdf->SetFont('Arialb','',8.5);
$pdf->SetFillColor(255, 255, 255);
$y = $pdf->GetY();
$x = $pdf->GetX();
$pdf->MultiCell(80, 10, "", 'TBR', 'C');
$pdf->Text($pdf->GetX()+5, $pdf->GetY()-6.5, utf8_decode("Si ha contestado afirmativamente alguna pregunta"));
$pdf->Text($pdf->GetX()+5.5, $pdf->GetY()-2.5, utf8_decode("de la 1 a la 6, especifique enfermedad o accidente"));
$pdf->SetXY($x+80, $y);
$pdf->MultiCell(60, 10, "", 'TBR', 'C');
$pdf->Text($pdf->GetX()+98, $pdf->GetY()-6.5, utf8_decode("Nombre y dirección"));
$pdf->Text($pdf->GetX()+98, $pdf->GetY()-2.5, utf8_decode("del médico tratante"));
$pdf->SetXY($x+140, $y);
$pdf->MultiCell(60, 10, "", 'TB', 'C');
$pdf->Text($pdf->GetX()+155, $pdf->GetY()-6.5, utf8_decode("¿Cuándo? ¿Duración?"));
$pdf->Text($pdf->GetX()+162, $pdf->GetY()-2.5, utf8_decode("¿Secuela?"));
$pdf->SetFont('Arial','',8);
if ($resultT->num_rows > 0) {
    while($rowT = $resultT->fetch_assoc()) {
        if ($rowT["SUBPREGUNTA"] == "13") {
            $columnas[0] = utf8_decode($rowT["TEXTO"]);
        }
        elseif($rowT["SUBPREGUNTA"] == "8"){
            $columnas[1] = utf8_decode($rowT["TEXTO"]);
        }
        elseif($rowT["SUBPREGUNTA"] == "9"){
            $columnas[1] .= ", ".utf8_decode($rowT["TEXTO"]);
        }
        elseif($rowT["SUBPREGUNTA"] == "1"){
            $columnas[2] = utf8_decode($rowT["TEXTO"]);
        }
        elseif($rowT["SUBPREGUNTA"] == "10"){
            $columnas[2] .= ", ".utf8_decode($rowT["TEXTO"]);
        }
        elseif($rowT["SUBPREGUNTA"] == "11"){
            $columnas[2] .= ", ".utf8_decode($rowT["TEXTO"]);
            $lineas[] = $columnas;
            $columnas = [[],[],[]];
        }
    }
}
$lineasUso = 5-count($lineas);
$resultT->close();
if(!empty($lineas)){
    foreach ($lineas as $linea) {
        $y = $pdf->GetY();
        $x = $pdf->GetX();
        $pdf->MultiCell(80, 5, $linea[0], 'BR', 'L');
        $pdf->SetXY($x+80, $y);
        $pdf->MultiCell(60, 5, $linea[1], 'BR', 'L');
        $pdf->SetXY($x+140, $y);
        $pdf->MultiCell(60, 5, $linea[2], 'B', 'L');
    }
}
if ($lineasUso > 0) {
    for ($i=0; $i < $lineasUso; $i++) { 
        $y = $pdf->GetY();
        $x = $pdf->GetX();
        $pdf->MultiCell(80, 5, "N/A", 'BR', 'C');
        $pdf->SetXY($x+80, $y);
        $pdf->MultiCell(60, 5, "N/A", 'BR', 'C');
        $pdf->SetXY($x+140, $y);
        $pdf->MultiCell(60, 5, "N/A", 'B', 'C');
    }
}

$pdf->Ln(10);
$pdf->SetFont('Arialb','',9);
$pdf->MultiCell(200, 5, utf8_decode("Muy importante para el solicitante (debe leerse antes de firmar)"), 0, 'C');
$pdf->SetFont('Arial','',9);
$pdf->MultiCell(200, 5, utf8_decode("Se advierte que conforme al artículo 1141 y 1143 del código de Comercio, debe declarar todos los hechos a que se refiere este cuestionario tal y como los conozca o deba conocer en el momento de firmarla. La omisión en las declaraciones o la inexactitud o falsedad de estas respecto a los hechos que se preguntan, podría originar la pérdida del derecho del solicitante o del beneficiario en su caso, a la indemnización que se derive de la póliza que se expida basada en tales declaraciones. AUTORIZO por este medio a los médicos, laboratorios, clínicas u hospitales que han atendido a mi cónyuge, hijos en lo particular a mi persona en la recuperación de la salud, para que suministren a: INTERAMERICANA DE SEGUROS, S.A. las informaciones que ésta requiera en relación al seguro que solicito, relevandolos de cualquier prohibición legal que exista sobre revelación de los datos de sus registros con respecto a mi persona. Queda entendido y convenido que una copia fotostática de esta autorización debe considerarse tan efectiva y valida como original."), 0);
$pdf->SetTitle('solicitud de inscripcion al seguro colectivo de gastos medicos y vida consentimiento del asegurado - con res LABORAL laboral SPN-FGTP-63');
$pdf->Ln();
$pdf->MultiCell(200, 5, utf8_decode("Autoriza a LA COMPAÑÍA, para que los documentos que acreditan la celebración de la póliza de Seguros, incluyendo las Condiciones Generales así como cualquier modificación realizada, pueden ser enviados al correo electrónico indicado en esta solicitud."), 0);

$pdf->Ln(40);

$meses = array(
    1 => 'Enero',
    2 => 'Febrero',
    3 => 'Marzo',
    4 => 'Abril',
    5 => 'Mayo',
    6 => 'Junio',
    7 => 'Julio',
    8 => 'Agosto',
    9 => 'Septiembre',
    10 => 'Octubre',
    11 => 'Noviembre',
    12 => 'Diciembre'
);

//$pdf->Image($file, $pdf->GetX(), $pdf->GetY()-27, 70);
$pdf->Line($pdf->GetX(), $pdf->GetY(), $pdf->GetX()+70, $pdf->GetY());
$pdf->Text($pdf->GetX()+76, $pdf->GetY()-1, date('d'));
$pdf->Line($pdf->GetX()+75, $pdf->GetY(), $pdf->GetX()+80, $pdf->GetY());
$pdf->Text($pdf->GetX()+82, $pdf->GetY(), "de");
$pdf->Text($pdf->GetX()+90, $pdf->GetY()-1, $meses[date('n')]);
$pdf->Line($pdf->GetX()+86, $pdf->GetY(), $pdf->GetX()+108, $pdf->GetY());
$pdf->Text($pdf->GetX()+110, $pdf->GetY(), "del 20");
$pdf->Text($pdf->GetX()+120, $pdf->GetY()-1, date('y'));
$pdf->Line($pdf->GetX()+119, $pdf->GetY(), $pdf->GetX()+125, $pdf->GetY());
$pdf->Text($pdf->GetX()+25, $pdf->GetY()+5, "Firma del solicitante");

$uploadDir = 'tmp/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Manejar imágenes recibidas por POST
if (isset($_FILES['image1']) && isset($_FILES['image2'])) {
    // Guardar las imágenes en el servidor
    $image1Path = $uploadDir . "1".$numeroIdentificacionAsegurado."_".basename($_FILES['image1']['name']);
    $image2Path = $uploadDir . "2".$numeroIdentificacionAsegurado."_".basename($_FILES['image2']['name']);
    
    if (move_uploaded_file($_FILES['image1']['tmp_name'], $image1Path) && move_uploaded_file($_FILES['image2']['tmp_name'], $image2Path)) {
        // Agregar imágenes al PDF
        $pdf->Image($image1Path, 10, 10, 90); // Imagen 1
        $pdf->AddPage(); // Nueva página para la segunda imagen
        $pdf->Image($image2Path, 10, 10, 90); // Imagen 2

        // Eliminar las imágenes temporales después de usarlas
        unlink($image1Path);
        unlink($image2Path);
    } else {
        echo "Error al subir las imágenes.";
        exit;
    }
}

$pdfContent = $pdf->Output("","S");
//$pdfName = $numeroIdentificacionAsegurado.".pdf";
//$pdf->Output('F', "tmp/".$numeroIdentificacionAsegurado.".pdf");


// Credit: https://raw.githubusercontent.com/matt-allan/open/master/src/open.php
function open($target)
{
    switch (PHP_OS) {
        case 'Darwin':
            $opener = 'open';
            break;
        case 'WINNT':
            $opener = 'start ""';
            break;
        default:
            $opener = 'xdg-open';
    }

    return exec(sprintf('%s %s', $opener, escapeshellcmd($target)));
}

try {
    $rsaPrivateKey = file_get_contents($GLOBALS['JWT_CONFIG']['private_key_file']);
}
catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

restore_error_handler();


$args = [];


try {
    $notification = new Notification([
        'use_account_defaults' => 'false', // Si se debe sobrescribir la configuración por defecto
        'completed_subject' => 'Tu solicitud se ha enviado correctamente.', // Asunto del correo de completado
        //'completed_message' => 'Tu documento ha sido completado y ya está disponible para su descarga.' // Mensaje personalizado (opcional)
    ]);
    $args['envelope_args']['signer_email'] = trim($emailAsegurado);
    $args['envelope_args']['signer_name'] = trim($primerNombreAsegurado . " " . $primerApellidoAsegurado);
    $args['envelope_args']['cc_email'] = trim("gestiones@wecarelatam.com");
    $args['envelope_args']['signer_name'] = trim("Uzziel Umanzor");
    $args['envelope_args']['cc_email'] = trim("uzziel_umanzor@hotmail.com");
    $args['envelope_args']['cc_name'] = trim("Alfredo Ortiz");
    $args['envelope_args']['status'] = "sent";
    $args['envelope_args']['notification'] = $notification;

    // these are extra arguments used for the html document in SigningViaEmailService
    $args['envelope_args']['item'] = "wafer biscuit";
    $args['envelope_args']['quantity'] = "60";
    $apiClient = new ApiClient();
    $apiClient->getOAuth()->setOAuthBasePath("account-d.docusign.com");
    $response = $apiClient->requestJWTUserToken($integration_key, $impersonatedUserId, $rsaPrivateKey, $scopes, 60);
} catch (\Throwable $th) {
    // we found consent_required in the response body meaning first time consent is needed
    if (strpos($th->getMessage(), "consent_required") !== false) {
        $authorizationURL = 'https://account-d.docusign.com/oauth/auth?' . http_build_query([
            'scope'         => $scopes,
            'redirect_uri'  => "https://developers.docusign.com/platform/auth/consent",
            'client_id'     => $integration_key,
            'response_type' => 'code'
        ]);

        header('Location: '.$authorizationURL);
        //open($authorizationURL);
        exit;
    }else{
        echo $th->xdebug_message;
    }
}

// We've gotten a JWT token, now we can use it to make API calls

if (isset($response)) {
    
    $access_token = $response[0]['access_token'];
    // retrieve our API account Id
    $info = $apiClient->getUserInfo($access_token);
    $account_id = $info[0]["accounts"][0]["account_id"];
    $args['base_path'] = "https://demo.docusign.net/restapi";
    $args['account_id'] = $account_id;
    $args['ds_access_token'] = $access_token;

    $clientService = new SignatureClientService($args);
    $demoDocsPath =  $GLOBALS['DS_CONFIG']['demo_doc_path'];
    try {
        $callAPI = new SigningViaEmailService();
        $result = $callAPI->signingViaEmail($args, $clientService, $pdfContent, $primerNombreAsegurado . "_" . $primerApellidoAsegurado);

        header('Location: '.'https://ibex.wecarelatam.com/');
        //echo "Successfully sent envelope with envelope ID: " . $result['envelope_id'] . "\n";
    } catch (\Throwable $th) {
        writeLog($th);
        exit;
    }
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
$pdf->Output('I');
?>