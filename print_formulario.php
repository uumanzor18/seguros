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

set_error_handler(
    function ($severity, $message, $file, $line) {
        writeLog("Error: ".$message.", nivel: ".$severity.", archivo: ".$file.", linea: ".$line);
        throw new ErrorException($message, $severity, $severity, $file, $line);
    }
);

//Seccion 1
$sql = "INSERT INTO seguro(
TIPO_SEGURO, USUARIO_CREO, ENTIDAD, NOMBRE_CONTRATANTE, APELLIDO_CONTRATANTE,
NUMERO_POLIZA, CATEGORIA_EMPLEADO, MAXIMO_VITALICIO, SUMA_ASEGURADA, SEGURO_VIDA,
SEGURO_VIDA_SUMA_ASEGURADA)
VALUES(?,?,?,?,?,?,?,?,?,?,?)";

// Preparar la sentencia

$stmt = $conn->prepare($sql);

$stmt->bind_param("isisssiddsd",
$tipoSeguro,$usuario,$entidad,$nombresContratante,$apellidosContratante,
$numeroPoliza,$categoriaEmpleado,$maximoVitalicio,$sumaAsegurada,$seguroVidaOpcional,
$sumaAseguradaOpcional);

$tipoSeguro = 0;
$usuario = "MASTER";
$entidad = 1;
$nombresContratante = "Ibex Honduras";
$apellidosContratante = null;
$numeroPoliza = "1000019816";
$categoriaEmpleado = 1;
$SumaSeguroVida = null;

$selectCategoria = "SELECT CONCAT(UCASE(SUBSTRING(c.NOMBRE, 1, 1)), LOWER(SUBSTRING(c.NOMBRE, 2))) AS NOMBRE 
from categoria c 
where c.categoria = ?";
$resultCategoria = $conn->prepare($selectCategoria);
$resultCategoria->bind_param("s", $categoriaEmpleado);
$resultCategoria->execute();
$resultCategoria = $resultCategoria->get_result();
$row = $resultCategoria->fetch_assoc();
$categoriaEmpleadoNombre = utf8_decode($row["NOMBRE"]);
$resultCategoria->close();

switch ($categoriaEmpleado) {
    case '1':
        $maximoVitalicio = 2000000;
        $sumaAsegurada = 100000;
    break;
    case '2':
        $maximoVitalicio = 3200000;
        $sumaAsegurada = 300000;
    break;
    case '3':
        $maximoVitalicio = 4200000;
        $sumaAsegurada = 300000;
    break;
    
    default:
        die();
    break;
}

$seguroVidaOpcional = "0";
$sumaAseguradaOpcional = "";
$stmt->execute();
$seguroId = $stmt->insert_id;
$stmt->close();
//Seccion 2
$sql = "INSERT INTO general(
PRIMER_NOMBRE_ASEGURADO, SEGUNDO_NOMBRE_ASEGURADO, PRIMER_APELLIDO_ASEGURADO, SEGUNDO_APELLIDO_AEGURADO, APELLIDO_CASADA_ASEGURADO,
TIPO_IDENTIFICACION, NUMERO_IDENTIFICACION, NACIONALIDAD, PROFESION, ESTADO_CIVIL,
ESTATURA,PESO,FUMA,CIGARRILLOS_DIA,TOMA,
TOMA_FRECUENCIA,CARGO,DEPARTAMENTO,FECHA_INGRESO_COMPANIA,SUELDO_MENSUAL,
SUCURSAL,USUARIO_CREO,SEGURO,LUGAR_NACIMIENTO,FECHA_NACIMIENTO,
SEXO)
VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

// Preparar la sentencia
$stmt = $conn->prepare($sql);

$stmt->bind_param("sssssisssiddsisssssdssissi",
$primerNombreAsegurado,$segundoNombreAsegurado,$primerApellidoAsegurado,$segundoApellidoAsegurado,$apellidoCasadaAsegurado,
$tipoIdentificacionAsegurado,$numeroIdentificacionAsegurado, $nacionalidadAsegurado,$profesionAsegurado,$estadoCivilAsegurado,
$estaturaAsegurado,$pesoAsegurado,$fumaAsegurado,$numeroCigarrosAsegurado,$bebeAsegurado,
$frecuenciaBebeAsegurado,$cargoAsegurado,$departamentoCompaniaAsegurado,$fechaIngresoAsegurado,$sueldoAsegurado,
$sucursalAsegurado,$usuario,$seguroId,$lugarNacimientoAsegurado,$fechaNacimientoAsegurado,
$sexoAsegurado);

$primerApellidoAsegurado = trim($_POST["primerApellidoAsegurado"]);
$segundoApellidoAsegurado = trim($_POST["segundoApellidoAsegurado"]);
$primerNombreAsegurado = trim($_POST["primerNombreAsegurado"]);
$segundoNombreAsegurado = trim($_POST["segundoNombreAsegurado"]);
$tipoIdentificacionAsegurado = $_POST["tipoIdentificacion"];
$numeroIdentificacionAsegurado = $_POST["numeroIdentificacion"];
$nacionalidadAsegurado = $_POST["nacionalidad"];
if ($nacionalidadAsegurado == "Otra" && isset($_POST["otraNacionalidad"])) {
    $nacionalidadAsegurado = trim($_POST["otraNacionalidad"]);
}

$lugarNacimientoAsegurado = trim($_POST["lugarNacimiento"]);
$fechaNacimientoAsegurado = $_POST["fechaNacimiento"];
$edadAsegurado = $_POST["edad"];
$sexoAsegurado = $_POST["sexo"];
$profesionAsegurado = trim($_POST["profesion"]);
$estadoCivilAsegurado = $_POST["estadoCivil"];
$apellidoCasadaAsegurado = "";
if ($estadoCivilAsegurado == "2" && $sexoAsegurado == "2" && isset($_POST["apellidoCasadaAsegurado"])) {
    $apellidoCasadaAsegurado = trim($_POST["apellidoCasadaAsegurado"]);
}
$estaturaAsegurado = $_POST["estatura"];
$pesoAsegurado = $_POST["peso"];
$fumaAsegurado = $_POST["fuma"];
$numeroCigarrosAsegurado = "";
if ($fumaAsegurado == "1" && isset($_POST["numeroCigarros"])) {
    $numeroCigarrosAsegurado = trim($_POST["numeroCigarros"]);
}
$bebeAsegurado = $_POST["bebe"];
$frecuenciaBebeAsegurado = "";
if ($bebeAsegurado == "1" && isset($_POST["frecuenciaBebe"])) {
    $frecuenciaBebeAsegurado = trim($_POST["frecuenciaBebe"]);
}
$cargoAsegurado = $_POST["cargo"];
$departamentoCompaniaAsegurado = $_POST["departamentoCompania"];
$fechaIngresoAsegurado = $_POST["fechaIngreso"];
$sueldoAsegurado = $_POST["sueldo"];
$sucursalAsegurado = $_POST["sucursal"];
$numeroAfiliacionSeguroAsegurado = $_POST["numeroAfiliacionSeguro"];
$usuario = "MASTER";
$seguroId = $seguroId;
$stmt->execute();
$stmt->close();
//Seccion 3
$sql = "INSERT INTO conyugue(
NOMBRE_CONYUGUE, APELLIDO_CONYUGUE, EMPRESA_CONYUGUE, CELULAR_CONYUGUE, EMAIL_CONYUGUE,
USUARIO_CREO, SEGURO)
VALUES(?,?,?,?,?,?,?)";
$stmt = $conn->prepare($sql);

$stmt->bind_param("ssssssi",
$nombresConyuge,$apellidosConyuge,$empresaConyuge,$celularConyuge,$emailConyuge,
$usuario,$seguroId);
$nombresConyuge = trim($_POST["nombreConyuge"]);
$apellidosConyuge = trim($_POST["apellidoConyuge"]);
$empresaConyuge = trim($_POST["empresaConyuge"]);
$celularConyuge = $_POST["celularConyuge"];
$emailConyuge = trim($_POST["emailConyuge"]);
$usuario = "MASTER";
$seguroId = $seguroId;
$stmt->execute();
$stmt->close();
//Seccion 4
$sql = "INSERT INTO direccion_asegurado(
PAIS_ASEGURADO, DEPARTAMENTO_ASEGURADO, CIUDAD_ASEGURADO, COLONIA_ASEGURADO, CALLE_ASEGURADO,
AVENIDA_ASEGURADO, BLOQUE_ASEGURADO, NUMERO_CASA_ASEGURADO, TELEFONO_ASEGURADO, CELULAR_ASEGURADO,
EMAIL_ASEGURADO, USUARIO_CREO, SEGURO
)
VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)";
$stmt = $conn->prepare($sql);

$stmt->bind_param("ssssssssssssi",
$paisAsegurado,$departamentoAsegurado,$ciudadAsegurado,$coloniaAsegurado,$calleAsegurado,
$avenidaAsegurado,$bloqueAsegurado,$casaAsegurado,$telefonoAsegurado,$celularAsegurado,
$emailAsegurado,$usuario,$seguroId);
$paisAsegurado = trim($_POST["paisAsegurado"]);
$departamentoAsegurado = $_POST["departamentoAsegurado"];
$selectDepartemento = "SELECT CONCAT(UCASE(SUBSTRING(d.NOMBRE, 1, 1)), LOWER(SUBSTRING(d.NOMBRE, 2))) AS NOMBRE 
from departamento d 
where d.codigo = ?";
$resultDepartamento = $conn->prepare($selectDepartemento);
$resultDepartamento->bind_param("s", $departamentoAsegurado);
$resultDepartamento->execute();
$resultDepartamento = $resultDepartamento->get_result();
$row = $resultDepartamento->fetch_assoc();
$departamentoAseguradoNombre = $row["NOMBRE"];
$resultDepartamento->close();
$ciudadAsegurado = trim($_POST["ciudadAsegurado"]);
$coloniaAsegurado = trim($_POST["coloniaAsegurado"]);
$calleAsegurado = trim($_POST["calleAsegurado"]);
$avenidaAsegurado = trim($_POST["avenidaAsegurado"]);
$bloqueAsegurado = trim($_POST["bloqueAsegurado"]);
$casaAsegurado = trim($_POST["casaAsegurado"]);
$telefonoAsegurado = trim($_POST["telefonoAsegurado"]);
$celularAsegurado = trim($_POST["celularAsegurado"]);
$emailAsegurado = trim($_POST["emailAsegurado"]);
$usuario = "MASTER";
$seguroId = $seguroId;
$stmt->execute();
$stmt->close();
//Seccion 5
$beneficiariosSeguro = json_decode($_POST["hdnBeneficiariosSeguro"]);
$sql = "INSERT INTO beneficiario_seguro_vida(
NOMBRE, APELLIDO, PARENTESCO, FECHA_NACIMIENTO, PROCENTAJE,
USUARIO_CREO, SEGURO
)
VALUES(?,?,?,?,?,?,?)";
$stmt = $conn->prepare($sql);

foreach ($beneficiariosSeguro as $beneficiario) {
    $stmt->bind_param("ssisdsi",
    $beneficiario->nombres,$beneficiario->apellidos,$beneficiario->parentesco,$beneficiario->fechaNacimiento,$beneficiario->porcentaje,
    $usuario,$seguroId);
    $stmt->execute();
}
$stmt->close();

//Seccion 6
$beneficiariosContingencia = json_decode($_POST["hdnBeneficiariosContingencia"]);
$sql = "INSERT INTO beneficiario_contingencia(
NOMBRE, APELLIDO, PARENTESCO, FECHA_NACIMIENTO, PROCENTAJE,
USUARIO_CREO, SEGURO
)
VALUES(?,?,?,?,?,?,?)";
$stmt = $conn->prepare($sql);

foreach ($beneficiariosContingencia as $beneficiario) {
    $stmt->bind_param("ssisdsi",
    $beneficiario->nombres,$beneficiario->apellidos,$beneficiario->parentesco,$beneficiario->fechaNacimiento,$beneficiario->porcentaje,
    $usuario,$seguroId);
    $stmt->execute();
}
$stmt->close();
//Seccion 7
$dependientes = json_decode($_POST["hdnDatosDependientes"]);
$sql = "INSERT INTO dependiente_economico(
NOMBRE, APELLIDO, PARENTESCO, FECHA_NACIMIENTO, PESO,
ESTATURA, USUARIO_CREO, SEGURO
)
VALUES(?,?,?,?,?,?,?,?)";
$stmt = $conn->prepare($sql);

foreach ($dependientes as $dependiente) {
    $stmt->bind_param("ssisddsi",
    $dependiente->nombres,$dependiente->apellidos,$dependiente->parentesco,$dependiente->fechaNacimiento,$dependiente->peso,
    $dependiente->estatura,$usuario,$seguroId);
    $stmt->execute();
}
$stmt->close();
//Seccion 8
$respuestas = json_decode($_POST["hdnRespuestas"]);
$sql = "INSERT INTO respuesta(
SELECCION, PREGUNTA, USUARIO_CREO, SEGURO
)
VALUES(?,?,?,?)";
$stmt = $conn->prepare($sql);

$sql2 = "INSERT INTO subrespuesta(
SUBPREGUNTA, TEXTO, RESPUESTA, USUARIO_CREO
)
VALUES(?,?,?,?)";
$stmt2 = $conn->prepare($sql2);
$tratamientos = [];
foreach ($respuestas as $respuesta) {
    if ($respuesta->seleccion == "1") {
        $stmt->bind_param("sisi",
        $respuesta->seleccion,$respuesta->id,$usuario,$seguroId);
        $stmt->execute();

        foreach ($respuesta->formularios as $formulario) {
            foreach ($formulario as $subRespuesta) {
                if ($subRespuesta->id != "14") {
                    $stmt2->bind_param("isis",$a,$b,$c,$d);
                    $a = $subRespuesta->id;
                    $b = $subRespuesta->valor;
                    $c = $stmt->insert_id;
                    $d = $usuario;
                    $stmt2->execute();
                }else {
                    $stmt2->bind_param("isis",$a,$b,$c,$d);
                    $a = $subRespuesta->id;
                    $b = "";
                    $c = $stmt->insert_id;
                    $d = $usuario;
                    $stmt2->execute();
                    $subRespuesta->id = $stmt2->insert_id;
                    $tratamientos[] = $subRespuesta;
                }
            }
        }
    }
}
$stmt->close();
$stmt2->close();

$sql = "INSERT INTO tratamiento(
TEXTO, SUBRESPUESTA, USUARIO_CREO
)
VALUES(?,?,?)";
$stmt = $conn->prepare($sql);
foreach ($tratamientos as $tratamiento) {
    foreach ($tratamiento->valor as $value) {
        if (!empty($value)) {
            $stmt->bind_param("sis",$a,$b,$c);
            $a = $value;
            $b = $tratamiento->id;
            $c = $usuario;
            $stmt->execute();
        }
    }
}
$stmt->close();

/*if(isset($_POST['signature'])){ 
    $folderPath = "tmp/firmas";
    $nombreImagen = "(".$seguroId.")". $primerNombreAsegurado . " " . $primerApellidoAsegurado;
    $image_parts = explode(";base64,", $_POST['signature']); 
    $image_type_aux = explode("image/", $image_parts[0]);
    $image_type = $image_type_aux[1];
    $image_base64 = base64_decode($image_parts[1]);
    $file = $folderPath . $nombreImagen . '.'.$image_type;
    file_put_contents($file, $image_base64);

    $sql = "INSERT INTO firma(
    TEXTO, SEGURO, USUARIO_CREO, ARCHIVO
    )
    VALUES(?,?,?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siss",$a,$b,$c,$d);
    $a = $_POST['signature'];
    $b = $seguroId;
    $c = $usuario;
    $d = $nombreImagen;
    $stmt->execute();
          
    $stmt->close();
}*/


class PDF extends FPDF {
    private $useFooter2 = false;

    function DrawCheckBox($label, $x, $y, $checked) {
        $this->SetLineWidth(0.2);
        $this->SetDrawColor(0);
        $anchoTexto = $this->GetStringWidth($label);
        // Escribir la etiqueta
        $this->Text($x, $y + 2.5, $label);

        // Dibujar un cuadro de texto
        $this->Rect($x+$anchoTexto+1, $y-0.5, 3.9, 3.5, 'D');
        
        // Si está marcado, dibujar una X
        if ($checked) {
            $this->Text($x + $anchoTexto + 2.2, $y + 2, utf8_decode('x'));
        }
    }

    function UnderlinedText($text, $x, $y, $width) {
        $this->SetFont('calibri','',7);
        $anchoTexto = $this->GetStringWidth($text)/2;
        $this->SetLineWidth(0.2);
        $this->SetDrawColor(0);
        $x2 = $x + ($width/2);
        $this->Text($x2-$anchoTexto, $y, $text);
        $this->Line($x, $y + 1, $x + $width, $y + 1); // Dibujar línea debajo del texto
    }

    function BasicTable($header, $data, $x, $y){
        // Cabecera
        $this->SetY($y);
        $this->SetX($x);
        foreach($header as $col)
            $this->Cell(40,4.2,$col,1,0,'C');
            $this->Ln();
        // Datos
        $this->SetFontSize(7);
        foreach($data as $row){
            $this->SetX($x);
            foreach($row as $col)
                $this->Cell(40,5.2,$col,1,0,'C');
            $this->Ln();
        }
    }
    function footer1(){
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

        $this->SetFont('calibri', '',9);
        $this->SetY(-10);
        $this->Line($this->GetX(), $this->GetY()+3, $this->GetX() + 65, $this->GetY()+3);
        $this->UnderlinedText(date('d'), $this->GetX()+75, $this->GetY()+2,10);
        $this->SetFont('calibri', '',9);
        $this->Text($this->GetX()+87, $this->GetY()+3, "de");
        $this->UnderlinedText($meses[date('n')], $this->GetX()+92, $this->GetY()+2,10);
        $this->SetFont('calibri', '',9);
        $this->Text($this->GetX()+104, $this->GetY()+3, "del");
        $this->UnderlinedText(date('Y'), $this->GetX()+109, $this->GetY()+2,10);
        $this->SetX($this->GetX()+10);
        $this->SetFont('calibri', '',9);
        $this->Image('imagenes/firma_patrono.png', $this->GetX()+10, $this->GetY()-16, 24);
        $this->Cell(90, 10, 'Firma y sello del patrono o contratante', 0, 0, 'L');
        $this->SetFont('calibri','',12);
        $this->Cell(100, 10, 'V-02', 0, 0, 'R');
    }

    function footer2(){
        $this->SetY(-10);
        $this->SetFont('calibri','',12);
        $this->Cell(200, 10, 'V-02', 0, 0, 'R');
    }

    function UseFooter2(){
        $this->useFooter2 = true;
    }

    // Cambiar al pie de página original
    function UseFooter1()
    {
        $this->useFooter2 = false;
    }

    // Selector de pie de página
    function Footer()
    {
        if ($this->useFooter2) {
            $this->footer2();
        } else {
            $this->footer1();
        }
    }

    function MultiCellRow($cells, $x, $y, $data, $pdf, $indice, $respuesta){
        $h = 0;
        $x -= 4.5;
        $y -= 3;
        $pdf->SetX($x);
        //$lines = explode("\n", wordwrap($data[0], 130, "\n"));
        $lines = explode("\\n", $data[0]);
        //$lines = str_split($data[0], 130);
        $h = count($lines);
        $tResp = $pdf->GetStringWidth($respuesta);
        /*$indiceUltimo = count($lines) - 1;
        $longitudActual = strlen($lines[$indiceUltimo]);
        while ($longitudActual < 125) {
            $lines[$indiceUltimo] .= "_";
            $longitudActual++;
        }
        $data[0] = implode("", $lines);*/
        //$lines2 = explode("\n", wordwrap($data[2], 20, "\n"));
        $lines2 = explode("\n", $data[2]);
        
        //$lines2 = str_split($data[2], 127);
        $h2 = count($lines2);

        if ($h2>$h) {
            $h=$h2;
        }
        $renglon = 4;
        writeLog("lines: ".print_r($lines, true));
        writeLog("cells: ".print_r($cells, true));
        for ($i = 0; $i < $cells; $i++) {
            if ($i==0) {
                for ($j=0; $j < $h; $j++) {
                    if ($j == 0) {
                        $pdf->SetFont('calibrib','',8);
                        $pdf->text($pdf->GetX()+0.5, $pdf->GetY()+$renglon, $indice);
                        $pdf->SetFont('calibri','',8);
                    } 
                    $pdf->Text($pdf->GetX()+2.5, $pdf->GetY()+$renglon, $lines[$j]);
                    $renglon += 4;
                }
                $ultimoElemento = end($lines);
                
                $extra = $pdf->GetStringWidth($ultimoElemento);
                if ($extra < 150) {
                    $pdf->Line($pdf->GetX()+$extra+2.5-$tResp, $pdf->GetY()+$renglon-4, $pdf->GetX()+150, $pdf->GetY()+$renglon-4);
                }
                $pdf->Cell(154, $h*5, "");
            }
            elseif ($i==1) {
                if ($data[$i] == "0") {
                    $pdf->Image('imagenes/checked_icon.png', $pdf->GetX()+7, $pdf->GetY()+(($renglon-4)/4), 4);
                    $pdf->Image('imagenes/unchecked_icon.png', $pdf->GetX()+2, $pdf->GetY()+(($renglon-4)/4), 4);
                }else{
                    $pdf->Image('imagenes/checked_icon.png', $pdf->GetX()+2, $pdf->GetY()+(($renglon-4)/4), 4);
                    $pdf->Image('imagenes/unchecked_icon.png', $pdf->GetX()+7, $pdf->GetY()+(($renglon-4)/4), 4);
                }
                $pdf->Cell(13, $h*5, "");
                //$pdf->MultiCell(15, $h, "", 0, "C");
                //$pdf->SetY($y);
            }
            else{
                if (empty($data[$i])) {
                    $data[$i] = str_repeat('_', 20);
                }
                $renglon = 4;
                for ($j=0; $j < $h2; $j++) { 
                    $pdf->Text($pdf->GetX()+1, $pdf->GetY()+$renglon, $lines2[$j]);
                    $renglon += 4;
                }
                $pdf->Line($pdf->GetX()+1, $pdf->GetY()+$renglon-4, $pdf->GetX()+35, $pdf->GetY()+$renglon-4);
                $pdf->Cell(36, $h*5, "");
                //$pdf->MultiCell(31, $h, $data[$i], 0, 'L');
                //$pdf->SetY($pdf->GetY()+$h*5);
                $pdf->Ln($h*5);
            }
        }
    }
}

$pdf = new PDF();
$pdf->UseFooter1();
$pdf->AddPage('P', [215.9, 279.4]);
$pdf->Image('imagenes/logo.png',9.4,8,40,9);
$pdf->AddFont('calibrib', '', 'calibrib.php');
$pdf->SetFont('calibrib','',14);

$pdf->text(110.7, 6.7,utf8_decode("Solicitud de inscripción al seguro colectivo de"));
$pdf->text(118.7,11,utf8_decode("gastos médicos y vida consentimiento del "));
$pdf->text(122.7,16,utf8_decode("asegurado - con responsabilidad laboral"));
$pdf->Ln(8);

$pdf->SetDrawColor(160, 160, 160);
$pdf->SetLineWidth(1);
$anchoPagina = $pdf->GetPageWidth();
$pdf->Line(8.7, 18.7, $anchoPagina-9.3, 18.7);

$pdf->AddFont('calibri', '', 'calibri.php');
$pdf->SetFont('calibri','',9);

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

$pdf->SetFont('calibrib','',9);
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

$pdf->SetFont('calibrib','',9);
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
$pdf->SetFont('calibrib', '',9);
$x = 8.5;
$y = $y + 5;
$pdf->text($x,$y,"Datos generales del asegurado:");
$pdf->SetFont('calibri','',9);
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
$pdf->SetFont('calibri', '',9);
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
$pdf->SetFont('calibri', '',9);
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
$pdf->SetFont('calibri', '',9);
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
$pdf->SetFont('calibri', '',9);
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
$pdf->SetFont('calibrib', '',9);
$pdf->text($x,$y,"Datos del conyuge:");
$pdf->SetFont('calibri','',9);

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
$pdf->SetFont('calibrib', '',9);
$pdf->text($x,$y,"Direccion del asegurado:");
$pdf->SetFont('calibri','',9);

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
$pdf->SetFont('calibrib', '',9.5);
$pdf->SetFillColor(35, 31, 32);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(200,5,'Beneficiarios del seguro de vida',1,0,'C',true);
$pdf->Ln();

$x = 8.5;
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('calibri','',9);
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
$pdf->SetFont('calibrib', '',9.5);
$pdf->SetFillColor(35, 31, 32);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(200,5,'Beneficiarios de contingencia',1,0,'C',true);
$y += 5;
$pdf->Ln();

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('calibri','',9);
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

$pdf->SetFont('calibri', '',9);
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
$pdf->SetFont('calibrib', '',9.5);
$pdf->SetFillColor(35, 31, 32);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(200,5,utf8_decode('Dependientes economicos (cónyuge e hijos) para el Plan Médico Hospitalario y/o Dental (si aplica)'),1,0,'C',true);
$pdf->Ln();

$y =+ 5;
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('calibri','',9);
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
$pdf->SetFont('calibrib','',8.5);
$pdf->Cell(154,12,'','1',0,'C');
$pdf->Cell(13,12,'Si o No','TRB',0,'C');
$pdf->Cell(36,12,'NOMBRE(S)','TRB',0,'C');
$x = 8.5;

$pdf->text($x-2,$y+4,utf8_decode('1 -'));
$pdf->SetFont('calibri','',8.5);

$pdf->text($x+1,$y+4,utf8_decode('Ha tenido alguna vez o tiene usted, su cónyuge o sus hijos; alguna de las enfermedades o trastornos siguientes,'));
$pdf->text($x+1,$y+7,utf8_decode('(Conteste Si o No con su puño y letra). Si la respuesta es afirmativa, especifique en la línea de la derecha el nombre'));
$pdf->text($x+1,$y+10,utf8_decode('de la persona a quien aplica.'));
$pdf->Ln(12);
$pdf->SetFontSize(8);

$abecedario = 'abcdefghijklmnopqrstuvwxyz';
/**
 * Se extraen todas las preguntas para impresion
 */
$selectPreguntas = "SELECT p.PREGUNTA, p.TEXTO, p.SECCION, p.INDICE 
from pregunta p";
$resultPreguntas= $conn->query($selectPreguntas);
if ($resultPreguntas->num_rows > 0) {
    $seccion = 0;
    $numero = "";
    while($row = $resultPreguntas->fetch_assoc()) {
        $nombrePregunta = "";
        /**
         * Se extraen todas las subpreguntas correspondientes a la pregunta del ciclo
         */
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
        /**
         * Se extrae la respuesta de la pregunta
         */
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
            /**
             * Se extrae la sub-respuesta de la pregunta
             */
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
            $pdf->SetFont('calibrib','',8);
            $pdf->SetX($pdf->GetX()-3.5);
            $pdf->Cell(200, 5, utf8_decode("6. Para personas de sexo femenino"), 0, 0, 'L');
            $pdf->SetX($pdf->GetX()+3.5);
            $pdf->SetFont('calibri','',9);
            $pdf->Ln(6);
            $numero="";
        }
        if ($row["SECCION"] == 7 && $row["INDICE"] == 1) {
            $pdf->Ln();
            $pdf->SetFont('calibrib','',8);
            $pdf->SetX($pdf->GetX()-3);
            $pdf->Cell(200, 5, utf8_decode("7. Antecedentes Covid-19"), 0, 0, 'L');
            $pdf->SetX($pdf->GetX()+3);
            $pdf->SetFont('calibri','',8);
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
$pdf->SetFont('calibrib','',8.5);
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
$pdf->SetFont('calibri','',8);
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
$pdf->SetFont('calibrib','',9);
$pdf->MultiCell(200, 5, utf8_decode("Muy importante para el solicitante (debe leerse antes de firmar)"), 0, 'C');
$pdf->SetFont('calibri','',9);
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

//$pdf->Output('I');
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
?>