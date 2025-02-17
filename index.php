<?php
global $conn;
require('conexion.php');
$conn->set_charset("utf8");

$selectParentesco = "SELECT P.id, CONCAT(UCASE(SUBSTRING(P.NOMBRE, 1, 1)), LOWER(SUBSTRING(P.NOMBRE, 2))) AS NOMBRE, genero
from parentesco P";
$resultParentesco= $conn->query($selectParentesco);
$parentescos = "<option value=''>Seleccione...</option>";
$parentescosDependiente = "<option value=''>Seleccione...</option>";
$tiposParentestcos = [3,4,5];
if ($resultParentesco->num_rows > 0) {
    while($row = $resultParentesco->fetch_assoc()) {
        if (in_array($row["id"], $tiposParentestcos)) {
            $parentescosDependiente .= '<option genero="'.$row["genero"].'"value="'.$row["id"].'">'.$row["NOMBRE"].'</option>';
        }
        $parentescos .= '<option value="'.$row["id"].'">'.$row["NOMBRE"].'</option>';
    }
}
$resultParentesco->close();

$selectSexo = "SELECT g.id, CONCAT(UCASE(SUBSTRING(g.nombre, 1, 1)), LOWER(SUBSTRING(g.nombre, 2))) AS NOMBRE
from genero g";
$resultSexo = $conn->query($selectSexo);
$generos = "<option value=''>Seleccione...</option>";
if ($resultSexo->num_rows > 0) {
    while($row = $resultSexo->fetch_assoc()) {
        $generos .= '<option value="'.$row["id"].'">'.$row["NOMBRE"].'</option>';
    }
}
$resultSexo->close();

$selectTipoIdentificacion = "SELECT ti.id, CONCAT(UCASE(SUBSTRING(ti.nombre, 1, 1)), LOWER(SUBSTRING(ti.nombre, 2))) AS NOMBRE
from tipo_identificacion ti";
$resultTipoIdentificacion = $conn->query($selectTipoIdentificacion);
$tiposIdentificacion = "<option value=''>Seleccione...</option>";
if ($resultTipoIdentificacion->num_rows > 0) {
    while($row = $resultTipoIdentificacion->fetch_assoc()) {
        $tiposIdentificacion .= '<option value="'.$row["id"].'">'.$row["NOMBRE"].'</option>';
    }
}
$resultTipoIdentificacion->close();

$selectEstadoCivil = "SELECT ec.id, CONCAT(UCASE(SUBSTRING(ec.NOMBRE, 1, 1)), LOWER(SUBSTRING(ec.NOMBRE, 2))) AS NOMBRE
from estado_civil ec";
$resultEstadoCivil= $conn->query($selectEstadoCivil);
$estadosCiviles = "<option value=''>Seleccione...</option>";
if ($resultEstadoCivil->num_rows > 0) {
    while($row = $resultEstadoCivil->fetch_assoc()) {
        $estadosCiviles .= '<option value="'.$row["id"].'">'.$row["NOMBRE"].'</option>';
    }
}
$resultEstadoCivil->close();

$selectMoneda = "SELECT id from moneda order by id desc";
$resultMoneda= $conn->query($selectMoneda);
$monedas = "";
if ($resultMoneda->num_rows > 0) {
    while($row = $resultMoneda->fetch_assoc()) {
        $monedas .= '<option value="'.$row["id"].'">'.$row["id"].'</option>';
    }
}
$resultMoneda->close();

$selectFinanciera = "SELECT id, CONCAT(UCASE(SUBSTRING(nombre, 1, 1)), LOWER(SUBSTRING(nombre, 2))) AS NOMBRE
from instituciones_financieras";
$resultFinanciera = $conn->query($selectFinanciera);
$institucionesFinancieras = "<option value=''>Seleccione...</option>";
if ($resultFinanciera->num_rows > 0) {
    while($row = $resultFinanciera->fetch_assoc()) {
        $institucionesFinancieras .= '<option value="'.$row["id"].'">'.$row["NOMBRE"].'</option>';
    }
}
$resultFinanciera->close();

$selectTipoCuenta = "SELECT id, CONCAT(UCASE(SUBSTRING(nombre, 1, 1)), LOWER(SUBSTRING(nombre, 2))) AS NOMBRE
from tipo_cuenta";
$resultTipoCuenta = $conn->query($selectTipoCuenta);
$tiposCuenta = "<option value=''>Seleccione...</option>";
if ($resultTipoCuenta->num_rows > 0) {
    while($row = $resultTipoCuenta->fetch_assoc()) {
        $tiposCuenta .= '<option value="'.$row["id"].'">'.$row["NOMBRE"].'</option>';
    }
}
$resultTipoCuenta->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Seguros</title>
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/alerta.css">
  <link rel="stylesheet" href="css/step.css">
  <link rel="stylesheet" href="jquery-ui-1.13.3/jquery-ui.min.css">
  <style>
    body {
      background-color: #f8f9fa;
      padding-top: 50px;
    }
    .container2 {
      background-color: #ffffff;
      border-radius: 10px;
      box-shadow: 0px 0px 10px 0px rgba(0,0,0,0.1);
      padding: 20px;
    }
    .form-title {
      margin-bottom: 30px;
    }
    #canvasDiv{
        position: relative;
        border: 2px dashed grey;
        height:300px;
    }
    .disabled2::before, .disabled2::after {
        content: '';
        background: gray !important; /* Color del pseudo-elemento ::before */
    }
    @media (max-width: 768px) { /* Ocultar elementos cuando la pantalla sea igual o menor a 768px */
      .hide-on-small-screen {
        display: none !important; /* Ocultar el elemento */
      }
    }
    @media (max-width: 400px) { /* Ocultar elementos cuando la pantalla sea igual o menor a 768px */
      .hide-on-small-screen2 {
        display: none !important; /* Ocultar el elemento */
      }
    }
    .current-page {
      font-weight: bold;
    }
    .center-vertical {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100%;
    }
    .input-group-vertical {
        margin-bottom: 10px;
        .form-control {
            border-radius: 0;
        }
        .form-group {
            margin-bottom: 0;
        }
        .form-group:not(:last-child) .form-control:not(:focus) {
            border-bottom-color: transparent;
        }
        .form-group:first-child .form-control {
            border-top-left-radius: 3px;
            border-top-right-radius: 3px;
        }
        .form-group:last-child .form-control {
            border-bottom-right-radius: 3px;
            border-bottom-left-radius: 3px;
            top: -2px;
        }
    }
    .page-link.active {
        background-color: #007bff;
        color: #fff;
    }
    .loading-spinner{
        width:30px;
        height:30px;
        border:2px solid indigo;
        border-radius:50%;
        border-top-color:#0001;
        display:inline-block;
        animation:loadingspinner .7s linear infinite;
        }
        @keyframes loadingspinner{
        0%{
            transform:rotate(0deg)
        }
        100%{
            transform:rotate(360deg)
        }
        }
        .float{
                position:fixed;
                width:60px;
                height:60px;
                bottom:40px;
                right:40px;
                background-color:#25d366;
                color:#FFF;
                border-radius:50px;
                text-align:center;
                font-size:30px;
                box-shadow: 2px 2px 3px #999;
                z-index:100;
            }
            .my-float{
                margin-top:16px;
            }
            .go{
                width: 300px;
                border: 2px solid black;
            }
            @media (max-width: 576px) {
                .form-title {
                    font-size: 1.5rem; /* Tamaño del título para dispositivos pequeños */
                }
            }
        .file-input {
            margin-bottom: 15px;
        }
        .file-input label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .file-input input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .error {
            color: red;
            font-size: 14px;
            margin-top: 10px;
        }
        #tablaDependientes th:nth-child(5), 
        #tablaDependientes th:nth-child(6), 
        #tablaDependientes td:nth-child(5) input, 
        #tablaDependientes td:nth-child(6) input{
            width: 60px;
        }
        #tablaDependientes th:nth-child(7),
        #tablaDependientes td:nth-child(7) input {
            width: 120px; 
        }
  </style>
</head>
<body>
    <div class="container hide-on-small-screen">
        <ul id="progressbar">
            <li class="active">Inicio</li>
            <li>Datos del Asegurado</li>
            <li>Pago de Indemnización</li>
            <li>Otros Seguros</li>
            <li>Beneficiarios Designados</li>
            <li>Beneficiarios Contingentes</li>
            <li>Dependientes Economicos</li>
            <li>Antecedentes de Seguro</li>
            <li>Preguntas</li>
            <li>Fin</li>
        </ul>
    </div>
<div class="container container2" id="principal">
    <form action="print_formulario.php" method="post"  enctype="multipart/form-data" id="formulario" accept-charset="UTF-8">
    <div id="seccion1">
        <h3 class="form-title">A. DATOS DEL AGENTE DE SEGURO</h3>
        <div class="row">
            <div class="col-sm-6 col-md-6">
                <div class="form-group">
                    <label for="nombreAgente">Agente:</label>
                    <input type="text" class="form-control form-control-sm" id="nombreAgente" name="nombreAgente" value="Correduria de Seguros y Fianzas We Care" readonly>
                </div>
            </div>
            <div class="col-sm-6 col-md-6">
                <div class="form-group">
                    <label for="codAgente">Código del agente:</label>
                    <input type="text" class="form-control form-control-sm" id="codAgente" name="codAgente" value="3109" readonly>
                </div>
            </div>
        </div>
        <br>
        <h4>SOLICITUD DE INSCRIPCIÓN PARA SEGURO COLECTIVO DE GASTOS MEDICOS</h4>
        <div class="row">
            <div class="col-sm-6 col-md-6">
                <div class="form-group">
                    <label for="nombreContratante">Contratante:</label>
                    <input type="text" class="form-control form-control-sm" id="nombreContratante" name="nombreContratante" value="The Workloop S.A." readonly>
                </div>
            </div>
            <div class="col-sm-6 col-md-6">
                <div class="form-group">
                    <label for="numeroPoliza">Número de póliza:</label>
                    <input type="text" class="form-control form-control-sm" id="numeroPoliza" name="numeroPoliza" value="2222" readonly>
                </div>
            </div>
        </div>
        <p>De acuerdo con las condiciones de la póliza colectiva de gastos médicos que hace mención este apartado, solicito
        inscribir como asegurado a la persona cuyos datos se detallan a continuación.</p>
        <button type="button" class="btn btn-primary btn-sm next-section" id="siguiente1">Siguiente&nbsp;<i class="fa fa-arrow-right fa-sm" aria-hidden="true"></i></button>
    </div>
    <div id="seccion2" style="display: none;">
        <h3 class="form-title">DATOS DEL ASEGURADO</h3>
        <div class="row">
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="primerNombreAsegurado">Primer nombre:</label>
                    <input type="text" class="form-control form-control-sm obligatorio" id="primerNombreAsegurado" name="primerNombreAsegurado" value="">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="segundoNombreAsegurado">Segundo nombre:</label>
                    <input type="text" class="form-control form-control-sm" id="segundoNombreAsegurado" name="segundoNombreAsegurado" value="">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="primerApellidoAsegurado">Primer apellido:</label>
                    <input type="text" class="form-control form-control-sm obligatorio" id="primerApellidoAsegurado" name="primerApellidoAsegurado" value="">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="segundoApellidoAsegurado">Segundo apellido:</label>
                    <input type="text" class="form-control form-control-sm" id="segundoApellidoAsegurado" name="segundoApellidoAsegurado" value="">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                <label for="tipoIdentificacionAsegurado">Tipo de identificación:</label>
                    <select class="form-control form-control-sm obligatorio" name="tipoIdentificacionAsegurado" id="tipoIdentificacionAsegurado">
                    <?=$tiposIdentificacion?>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="numeroIdentificacionAsegurado">No. identificación:</label>
                    <input type="text" class="form-control form-control-sm obligatorio" id="numeroIdentificacionAsegurado" name="numeroIdentificacionAsegurado" oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, '')">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="rtnAsegurado">RTN:</label>
                    <input type="text" class="form-control form-control-sm" id="rtnAsegurado" name="rtnAsegurado" oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, '')">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="fechaNacimientoAsegurado">Fecha de nacimiento:</label>
                    <input type="date" class="form-control form-control-sm obligatorio" id="fechaNacimientoAsegurado" name="fechaNacimientoAsegurado" value="">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="lugarNacimientoAsegurado">Lugar de nacimiento:</label>
                    <textarea class="form-control form-control-sm obligatorio" id="lugarNacimientoAsegurado" name="lugarNacimientoAsegurado" rows="3"></textarea>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="generoAsegurado">Género:</label>
                    <select class="form-control form-control-sm obligatorio" name="generoAsegurado" id="generoAsegurado">
                        <?= $generos; ?>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="estadoCivilAsegurado">Estado civil:</label>
                    <select class="form-control form-control-sm obligatorio" name="estadoCivilAsegurado" id="estadoCivilAsegurado">
                    <?= $estadosCiviles; ?>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="nombreConyuge">Nombre del cónyuge (completo):</label>
                    <input type="text" class="form-control form-control-sm conyugue" id="nombreConyuge" name="nombreConyuge"  oninput="this.value = this.value.replace(/[^a-zA-Z0-9 ]/g, '')" value="" disabled>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="">Nacionalidad_1:</label>
                    <input type="text" class="form-control form-control-sm obligatorio" id="nacionalidadAsegurado1" name="nacionalidadAsegurado1" value="" oninput="this.value = this.value.replace(/[^a-zA-Z ]/g, '')">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="">Nacionalidad_2:</label>
                    <input type="text" class="form-control form-control-sm" id="nacionalidadAsegurado2" name="nacionalidadAsegurado2" value="" oninput="this.value = this.value.replace(/[^a-zA-Z ]/g, '')">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="estaturaAsegurado">Estatura(mts):</label>
                    <input type="number" step="0.01" class="form-control form-control-sm obligatorio" id="estaturaAsegurado" name="estaturaAsegurado" value="">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="pesoAsegurado">Peso(lbs):</label>
                    <input type="number" step="0.01" class="form-control form-control-sm obligatorio" id="pesoAsegurado" name="pesoAsegurado" value="">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="celularAsegurado1">Móvil_1:</label>
                    <input type="text" class="form-control form-control-sm obligatorio" id="celularAsegurado1" name="celularAsegurado1" value="">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="celularAsegurado2">Móvil_2:</label>
                    <input type="text" class="form-control form-control-sm" id="celularAsegurado2" name="celularAsegurado2" value="">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="emailAsegurado1">Correo electrónico_1:</label>
                    <input type="email" class="form-control form-control-sm obligatorio" id="emailAsegurado1" name="emailAsegurado1" value="">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="emailAsegurado2">Correo electrónico_2:</label>
                    <input type="email" class="form-control form-control-sm" id="emailAsegurado2" name="emailAsegurado2" value="">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="profesionAsegurado">Profesión, oficio u ocupación:</label>
                    <input type="text" class="form-control form-control-sm obligatorio" id="profesionAsegurado" name="profesionAsegurado">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="areaAseguro">Área:</label>
                    <input type="text" class="form-control form-control-sm" id="areaAseguro" name="areaAseguro" value="">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="cargoAsegurado">Cargo que desempeña:</label>
                    <input type="text" class="form-control form-control-sm obligatorio" id="cargoAsegurado" name="cargoAsegurado" value="">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="fechaIngresoAsegurado">Fecha de ingreso:</label>
                    <input type="date" class="form-control form-control-sm" id="fechaIngresoAsegurado" name="fechaIngresoAsegurado" value="">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="remuneracionMensualAsegurado">Remuneración mensual:</label>
                    <div class="input-group">
                        <select class="form-select" id="monedaRemuneracionAsegurado" name="monedaRemuneracionAsegurado">
                        <?=$monedas;?>
                        </select>
                        <input type="number" step="0.01" class="form-control form-control-sm obligatorio" id="remuneracionAsegurado" name="remuneracionAsegurado" value="">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-6">
                <div class="form-group">
                    <label for="cargoPublicoAsegurado">Ha desempeñado un cargo público en los últimos cuatro (4) años:</label>
                    <select class="form-control form-control-sm" name="cargoPublicoAsegurado" id="cargoPublicoAsegurado">
                        <option value="0">No</option>    
                        <option value="1">Si</option>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="nombreCargoPublicoAsegurado">Detalle el nombre del cargo:</label>
                    <input type="text" class="form-control form-control-sm" id="nombreCargoPublicoAsegurado" name="nombreCargoPublicoAsegurado" value="" disabled>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>
        <h5>Dirección de residencia del asegurado</h5>
        <div class="row">
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="paisAsegurado">País:</label>
                    <input type="text" class="form-control form-control-sm obligatorio" id="paisAsegurado" name="paisAsegurado" value="">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="departamentoAsegurado">Departamento:</label>
                    <input type="text" class="form-control form-control-sm obligatorio" id="departamentoAsegurado" name="departamentoAsegurado" value="">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="municipioAsegurado">Municipio:</label>
                    <input type="text" class="form-control form-control-sm obligatorio" id="municipioAsegurado" name="municipioAsegurado" value="">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="coloniaAsegurado">Barrio o colonia:</label>
                    <input type="text" class="form-control form-control-sm obligatorio" id="coloniaAsegurado" name="coloniaAsegurado" value="">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="casaAsegurado">No. Casa/ lote:</label>
                    <input type="text" class="form-control form-control-sm obligatorio" id="casaAsegurado" name="casaAsegurado" value="">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="calleAsegurado">Calle(s):</label>
                    <input type="text" class="form-control form-control-sm" id="calleAsegurado" name="calleAsegurado" value="">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="avenidaAsegurado">Avenida(s):</label>
                    <input type="text" class="form-control form-control-sm" id="avenidaAsegurado" name="avenidaAsegurado" value="">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="telefonoResidenciaAsegurado">Teléfonos de la residencia:</label>
                    <input type="text" class="form-control form-control-sm" id="telefonoResidenciaAsegurado" name="telefonoResidenciaAsegurado" value="">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-secondary btn-sm prev-section" id="anterior1"><i class="fa fa-arrow-left fa-sm" aria-hidden="true"></i>&nbsp;Anterior</button>
        <button type="button" class="btn btn-primary btn-sm next-section" id="siguiente2">Siguiente&nbsp;<i class="fa fa-arrow-right fa-sm" aria-hidden="true"></i></button>
    </div>
    <div id="seccion3" style="display: none;">
        <h3 class="form-title">PAGO DE INDEMNIZACIÓN</h3>
        <p>En caso de pago por reclamación amparada por el contrato de seguro, sírvase completar la
        siguiente información</p>
        <div class="row">
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="institucionFinancieraAsegurado">Institución Financiera:</label>
                    <select class="form-control form-control-sm" name="institucionFinancieraAsegurado" id="institucionFinancieraAsegurado">
                    <?=$institucionesFinancieras;?>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="tipoCuentaAsegurado">Tipo de Cuenta:</label>
                    <select class="form-control form-control-sm" name="tipoCuentaAsegurado" id="tipoCuentaAsegurado">
                    <?=$tiposCuenta;?>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="numeroCuentaAsegurado">Número de Cuenta:</label>
                    <input type="text" class="form-control form-control-sm" id="numeroCuentaAsegurado" name="numeroCuentaAsegurado"  oninput="this.value = this.value.replace(/[^0-9 ]/g, '')" value="">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>
        <p>Cualquier beneficio que corresponde a un menor de edad, se entregará a:</p>
        <div class="row">
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="nombreMenorAsegurado">Nombre completo:</label>
                    <input type="text" class="form-control form-control-sm" id="nombreMenorAsegurado" name="nombreMenorAsegurado">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="identidadMenorAsegurado">Identidad:</label>
                    <input type="text" class="form-control form-control-sm" id="identidadMenorAsegurado" name="identidadMenorAsegurado" oninput="this.value = this.value.replace(/[^0-9 ]/g, '')">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-secondary btn-sm prev-section" id="anterior2"><i class="fa fa-arrow-left fa-sm" aria-hidden="true"></i>&nbsp;Anterior</button>
        <button type="button" class="btn btn-primary btn-sm next-section" id="siguiente3">Siguiente&nbsp;<i class="fa fa-arrow-right fa-sm" aria-hidden="true"></i></button>
    </div>
    <div id="seccion4" style="display: none;">
        <h3 class="form-title">INFORMACIÓN DE OTROS SEGUROS</h3>
        <div class="row">
            <div class="col-sm-6 col-md-6">
                <div class="form-group">
                    <label for="otroSeguro">¿Tiene otros seguros con la compañía?</label>
                    <select class="form-control form-control-sm" name="otroSeguro" id="otroSeguro">
                        <option value="0">No</option>    
                        <option value="1">Si</option>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-sm-6 col-md-6">
                <div class="form-group">
                    <label for="otroTipoSeguro">¿Qué tipo de seguro?</label>
                    <input type="text" class="form-control form-control-sm" id="otroTipoSeguro" name="otroTipoSeguro">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 col-md-6">
                <div class="form-group">
                    <label for="extraSeguro">¿Con cuál otra compañía tiene o tenía seguros?</label>
                    <input type="text" class="form-control form-control-sm" id="extraSeguro" name="extraSeguro">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-sm-6 col-md-6">
                <div class="form-group">
                    <label for="tipoExtraSeguro">¿Qué tipo de seguro?</label>
                    <input type="text" class="form-control form-control-sm" id="tipoExtraSeguro" name="tipoExtraSeguro">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-secondary btn-sm prev-section" id="anterior3"><i class="fa fa-arrow-left fa-sm" aria-hidden="true"></i>&nbsp;Anterior</button>
        <button type="button" class="btn btn-primary btn-sm next-section" id="siguiente4">Siguiente&nbsp;<i class="fa fa-arrow-right fa-sm" aria-hidden="true"></i></button>
    </div>
    <div id="seccion5" style="display: none;">
        <h3 class="form-title">BENEFICIARIOS DESIGNADOS</h3>
        <p>aplica solo para el beneficio de vida</p>
        <div class="row justify-content-end">
            <div class="col-sm-6 col-md-3">
                <div class="form-group">
                    <label for="sumaAegurada">Suma asegurada:</label>
                    <input type="text" class="form-control form-control-sm" id="sumaAegurada" name="sumaAegurada" value="******" disabled>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="table table-sm table-responsive">
                <table class="table" id="tablaBeniSeg">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Parentesco</th>
                            <th>Porcentaje</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        for ($i=1; $i <= 5; $i++) { 
                            echo '<tr>
                                <td><input type="text" class="form-control form-control-sm nombreBenAse" name="nombreBenAse'.$i.'" id="nombreBenAse'.$i.'"><div class="invalid-feedback"></div></td>
                                <td><select class="form-control form-control-sm parentescoBenAse" name="parentescoBenAse'.$i.'" id="parentescoBenAse'.$i.'">'.$parentescos.'</select><div class="invalid-feedback"></div></td>
                                <td><input type="number" step="0.01" class="form-control form-control-sm tblPorcentaje" name="porcentajeBenAse'.$i.'" id="porcentajeBenAse'.$i.'"><div class="invalid-feedback"></div></td>
                                <td><i class="fa fa-percent fa-sm" aria-hidden="true"></i></td>
                                <td><button type="button" class="btn btn-danger btn-sm btnLimpiar"><i class="fa fa-trash fa-sm" aria-hidden="true"></i></button></td>
                            </tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <button type="button" class="btn btn-secondary btn-sm prev-section" id="anterior4"><i class="fa fa-arrow-left fa-sm" aria-hidden="true"></i>&nbsp;Anterior</button>
        <button type="button" class="btn btn-primary btn-sm next-section" id="siguiente5">Siguiente&nbsp;<i class="fa fa-arrow-right fa-sm" aria-hidden="true"></i></button>  
    </div>
    <div id="seccion6" style="display: none;">
        <h3 class="form-title">BENEFICIARIOS CONTINGENTES</h3>
        <div class="row">
            <div class="table table-sm table-responsive">
                <table class="table" id="tablaBeniConti">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Parentesco</th>
                            <th>Porcentaje</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    for ($i=1; $i <= 2; $i++) { 
                        echo '<tr>
                            <td><input type="text" class="form-control form-control-sm nombreBeniConti" name="nombreBeniConti'.$i.'" id="nombreBeniConti'.$i.'"><div class="invalid-feedback"></div></td>
                            <td><select class="form-control form-control-sm parentescoBeniConti" name="parentescoBeniConti'.$i.'" id="parentescoBeniConti'.$i.'">'.$parentescos.'</select><div class="invalid-feedback"></div>
                            <td><input type="number" step="0.01" class="form-control form-control-sm tblPorcentaje" name="porcentajeBeniConti'.$i.'" id="porcentajeBeniConti'.$i.'"><div class="invalid-feedback"></div></td>
                            <td><i class="fa fa-percent fa-sm" aria-hidden="true"></i></td>
                            <td><button type="button" class="btn btn-danger btn-sm btnLimpiar"><i class="fa fa-trash fa-sm" aria-hidden="true"></button></td>
                        </tr>';
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
        <button type="button" class="btn btn-secondary btn-sm prev-section" id="anterior5"><i class="fa fa-arrow-left fa-sm" aria-hidden="true"></i>&nbsp;Anterior</button>
        <button type="button" class="btn btn-primary btn-sm next-section" id="siguiente6">Siguiente&nbsp;<i class="fa fa-arrow-right fa-sm" aria-hidden="true"></i></button>
    </div>
    <div id="seccion7" style="display: none;">
        <h3 class="form-title">Dependientes Economicos</h3>
        <h4>Dependientes que desea incluir en el seguro médico</h4>
        <div class="row">
            <table class="table table-sm table-responsive" id="tablaDependientes">
                <thead>
                    <tr>
                        <th>Nombre completo</th>
                        <th>Sexo</th>
                        <th>Parentesco</th>
                        <th>Ocupación</th>
                        <th>Peso</th>
                        <th>Estatura</th>
                        <th>Fecha de nacimiento</th>
                        <th>Número de identidad</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    for ($i=1; $i <= 5; $i++) {
                        echo '<tr>
                            <td><input type="text" class="form-control form-control-sm nombreDependiente" nombre="nombreDependiente" name="nombreDependiente'.$i.'" id="nombreDependiente'.$i.'"><div class="invalid-feedback"></div></td>
                            <td><select class="form-control form-control-sm sexoDependiente" nombre="sexoDependiente" name="sexodoDependiente'.$i.'" id="sexodoDependiente'.$i.'">'.$generos.'</select><div class="invalid-feedback"></td>
                            <td><select class="form-control form-control-sm parentescoDependiente" nombre="parentescoDependiente" name="parentescoDependiente'.$i.'" id="parentescoDependiente'.$i.'">'.$parentescosDependiente.'</select><div class="invalid-feedback"></td>
                            <td><input type="text" class="form-control form-control-sm ocupacionDependiente" nombre="ocupacionDependiente" name="ocupacionDependiente'.$i.'" id="ocupacionDependiente'.$i.'"><div class="invalid-feedback"></div></td>
                            <td><input type="number" step="0.01" class="form-control form-control-sm pesoDependiente" nombre="pesoDependiente" name="pesoDependiente'.$i.'" id="pesoDependiente'.$i.'"><div class="invalid-feedback"></div></td>
                            <td><input type="number" step="0.01" class="form-control form-control-sm estaturaDependiente" nombre="estaturaDependiente" name="estaturaDependiente'.$i.'" id="estaturaDependiente'.$i.'"><div class="invalid-feedback"></div></td>
                            <td><input type="date" class="form-control form-control-sm fechaNacimientoDependiente" nombre="fechaNacimientoDependiente" name="fechaNacimientoDependiente'.$i.'" id="fechaNacimientoDependiente'.$i.'"><div class="invalid-feedback"></div></td>
                            <td><input type="text" class="form-control form-control-sm identidadDependiente" nombre="identidadDependiente" name="identidadDependiente'.$i.'" id="identidadDependiente'.$i.'"><div class="invalid-feedback"></div></td>
                            <td><button type="button" data-toggle="tooltip" data-placement="top" title="Limpiar" class="btn btn-danger btn-sm btnLimpiar"><i class="fa fa-trash-o" aria-hidden="true"></i></button></td>
                        </tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <button type="button" class="btn btn-secondary btn-sm prev-section" id="anterior6"><i class="fa fa-arrow-left fa-sm" aria-hidden="true"></i>&nbsp;Anterior</button>
        <button type="button" class="btn btn-primary btn-sm next-section" id="siguiente7">Siguiente&nbsp;<i class="fa fa-arrow-right fa-sm" aria-hidden="true"></i></button>  
    </div>
    <div id="seccion8" style="display: none;">
        <h3 class="form-title">Antecedentes de Seguro</h3>
        <h4>¿Tiene o ha tenido usted o alguno de los dependientes nombrados algun seguro?</h4>
        <div class="row">
            <div class="table table-sm table-responsive">
                <table class="table table-sm table-responsive" id="tablaAntecedentes">
                    <thead>
                        <tr>
                            <th>Tipo de seguro</th>
                            <th>Nombre del asegurado</th>
                            <th>Aseguradora</th>
                            <th>Número de póliza</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        for ($i=1; $i <= 5; $i++) {
                            echo '<tr>
                                <td><input type="text" class="form-control form-control-sm tipoAntecedenteSeguro" nombre="tipoAntecedenteSeguro" name="tipoAntecedenteSeguro'.$i.'" id="tipoAntecedenteSeguro'.$i.'"><div class="invalid-feedback"></div></td>
                                <td><select class="form-control form-control-sm nombreAntecedenteSeguro" nombre="nombreAntecedenteSeguro" name="nombreAntecedenteSeguro'.$i.'" id="nombreAntecedenteSeguro'.$i.'"></select><div class="invalid-feedback"></td>
                                <td><input type="text" class="form-control form-control-sm aseguradoraAntecedenteSeguro" nombre="aseguradoraAntecedenteSeguro" name="aseguradoraAntecedenteSeguro'.$i.'" id="aseguradoraAntecedenteSeguro'.$i.'"><div class="invalid-feedback"></div></td>
                                <td><input type="text" class="form-control form-control-sm polizaAntecedenteSeguro" nombre="polizaAntecedenteSeguro" name="polizaAntecedenteSeguro'.$i.'" id="polizaAntecedenteSeguro'.$i.'"><div class="invalid-feedback"></div></td>
                                <td><button type="button" data-toggle="tooltip" data-placement="top" title="Limpiar" class="btn btn-danger btn-sm btnLimpiar"><i class="fa fa-trash-o" aria-hidden="true"></i></button></td>
                            </tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <button type="button" class="btn btn-secondary btn-sm prev-section" id="anterior7"><i class="fa fa-arrow-left fa-sm" aria-hidden="true"></i>&nbsp;Anterior</button>
        <button type="button" class="btn btn-primary btn-sm next-section" id="siguiente8">Siguiente&nbsp;<i class="fa fa-arrow-right fa-sm" aria-hidden="true"></i></button>  
    </div>
    <div id="seccion9" style="display: none;">
        <h3 class="form-title">PREGUNTAS</h3>
        <div class="row">
            <div class="col-auto">
                <table class="table table-sm table-responsive" id="tablaPreguntas">
                    <thead>
                        <tr>
                            <th class="col-8"></th>
                            <th class="col-1">Si o No</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $selectPreguntas = "SELECT p.id, p.texto
                        from pregunta p
                        order by id asc";
                        $resultPreguntas= $conn->query($selectPreguntas);
                        if ($resultPreguntas->num_rows > 0) {
                            while($row = $resultPreguntas->fetch_assoc()) {
                                echo '<tr pregunta="'.$row["id"].'">
                                <td>
                                    <p>'.$row["id"].'. '.str_replace("\\n", " ",$row["texto"]).'</p>
                                </td>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input seleccion-radio" type="radio" name="seleccion'.$row["id"].'" id="seleccion'.$row["id"].'N" value="0" checked>
                                        <label class="form-check-label" for="opcion1">No</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input seleccion-radio" type="radio" name="seleccion'.$row["id"].'" id="seleccion'.$row["id"].'S" value="1">
                                        <label class="form-check-label" for="opcion2">Si</label>
                                    </div>
                                </td>
                                <td class="center-vertical"><button type="button" class="btn btn-sm btn-link abrirModalPreguntas" data-toggle="modal" data-target="#modalPreguntas_'.$row["id"].'" id="btnVerPreguntas'.$row["id"].'" disabled>Ver Respuestas</button></td>
                            </tr>';
                            }
                        } else {
                            echo "No hay preguntas";
                        }
                        $resultPreguntas->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-auto">
                <nav>
                    <ul class="pagination justify-content-center" id="paginacion">
                    <li class="page-item"><a class="page-link" href="#" id="anterior"><i class="fa fa-chevron-left fa-sm" aria-hidden="true"></i></a></li>
                    <!-- Enlaces de página generados dinámicamente -->
                    <li class="page-item"><a class="page-link" href="#" id="siguiente"><i class="fa fa-chevron-right fa-sm" aria-hidden="true"></i></a></li>
                    </ul>
                </nav>
            </div>
        </div>
        <button type="button" class="btn btn-secondary btn-sm prev-section" id="anterior8"><i class="fa fa-arrow-left fa-sm" aria-hidden="true"></i>&nbsp;Anterior</button>
        <button type="button" class="btn btn-primary btn-sm next-section" id="siguiente9">Siguiente&nbsp;<i class="fa fa-arrow-right fa-sm" aria-hidden="true"></i></button>
    </div>
    <div id="seccion9" style="display: none;">
        <h3 class="form-title">Adjuntar Documentos</h3>
        <div class="row pb-2">
            <div class="col-auto">
                <div class="form-group">
                    <label for="exampleFormControlFile1">Documento de identificacion <strong>frontal</strong>(jpg, jpeg, png, gif):</label>
                    <input type="file" class="form-control-file" id="exampleFormControlFile1" name="archivo1">
                </div>
            </div>
            <div class="col-auto">
                <div class="form-group">
                    <label for="exampleFormControlFile2">Documento de identificacion <strong>opuesta</strong>(pg, jpeg, png, gif):</label>
                    <input type="file" class="form-control-file" id="exampleFormControlFile2" name="archivo2">
                </div>
            </div>
        </div>
        <input type="hidden" id="signature" name="signature">
        <input type="hidden" name="signaturesubmit" value="1">
        <input type="hidden" name="hdnEstado" value="1">
        <input type="hidden" name="hdnDependientes" id="hdnDependientes" value="0">
        <input type="hidden" name="hdnDatosDependientes" id="hdnDatosDependientes">
        <input type="hidden" name="hdnDatosAntecedentes" id="hdnDatosAntecedentes">
        <input type="hidden" name="hdnBeneficiariosSeguro" id="hdnBeneficiariosSeguro">
        <input type="hidden" name="hdnBeneficiariosContingencia" id="hdnBeneficiariosContingencia">
        <input type="hidden" name="hdnRespuestas" id="hdnRespuestas">
        <input type="hidden" name="hdnRest" id="hdnRest" value="<?= (isset($_GET['rest']) ? $_GET['rest'] : "")?>">
        <button type="button" class="btn btn-secondary btn-sm prev-section" id="anterior9"><i class="fa fa-arrow-left fa-sm" aria-hidden="true"></i>&nbsp;Anterior</button>
        <button type="button" formmethod="post" class="btn btn-success btn-sm" onclick="enviarFormulario()">Firmar</button>  
    </div>
</div>
    <div class="modal fade" id="statusErrorsModal" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false"> 
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document"> 
            <div class="modal-content"> 
                <div class="modal-body text-center p-lg-4"> 
                    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 130.2 130.2">
                        <circle class="path circle" fill="none" stroke="#db3646" stroke-width="6" stroke-miterlimit="10" cx="65.1" cy="65.1" r="62.1" /> 
                        <line class="path line" fill="none" stroke="#db3646" stroke-width="6" stroke-linecap="round" stroke-miterlimit="10" x1="34.4" y1="37.9" x2="95.8" y2="92.3" />
                        <line class="path line" fill="none" stroke="#db3646" stroke-width="6" stroke-linecap="round" stroke-miterlimit="10" x1="95.8" y1="38" X2="34.4" y2="92.2" /> 
                    </svg> 
                    <h4 class="text-danger mt-3 errorEncabezado"></h4> 
                    <p class="mt-3 errorMensaje"></p>
                    <button type="button" class="btn btn-sm mt-3 btn-danger" data-dismiss="modal">Cerrar</button> 
                </div> 
            </div> 
        </div> 
    </div>
    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered custom-modal" role="document">
            <div class="modal-content custom-modal-content">
                <div class="modal-header custom-modal-header">
                    <h5 class="modal-title" id="confirmModalTitle">Confirmacion</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body custom-modal-body" id="confirmModalBody">
                    <!-- Contenido del modal -->
                </div>
                <div class="modal-footer custom-modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal" id="confDepe">Si</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal" id="rechDepe">No</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="statusSuccessModal" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false"> 
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document"> 
            <div class="modal-content"> 
                <div class="modal-body text-center p-lg-4"> 
                    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 130.2 130.2">
                        <circle class="path circle" fill="none" stroke="#198754" stroke-width="6" stroke-miterlimit="10" cx="65.1" cy="65.1" r="62.1" />
                        <polyline class="path check" fill="none" stroke="#198754" stroke-width="6" stroke-linecap="round" stroke-miterlimit="10" points="100.2,40.2 51.5,88.8 29.8,67.5 " /> 
                    </svg> 
                    <h4 class="text-success mt-3">Formulario Enviado!</h4> 
                    <p class="mt-3">Operacion exitosa</p>
                    <button type="button" class="btn btn-sm mt-3 btn-success" data-dismiss="modal">Ok</button> 
                </div> 
            </div> 
        </div> 
    </div>
    <div class="modal" id="modal-loading" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
            <div class="modal-body text-center">
                <div class="loading-spinner mb-2"></div>
                <div>Enviando formulario</div>
            </div>
            </div>
        </div>
    </div>
</div>
</form>
<link rel="stylesheet" href="css/font-awesome/css/font-awesome.min.css">
<a href="https://api.whatsapp.com/send?phone=50496011125&text=Hola%21%20Quisiera%20m%C3%A1s%20informaci%C3%B3n%20sobre%20Varela%202." class="float" target="_blank">
    <i class="fa fa-whatsapp my-float"></i>
</a>
<?php
    $conn->close();
?>
<script src="js/jquery-3.5.1.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/html2canvas.min.js"></script>
<script src="jquery-ui-1.13.3/jquery-ui.min.js"></script>
<script src="js/step.js"></script>
<script src="js/paginacion.js"></script>
<script>
    var cancelButton = document.getElementById('clear');

    $(document).ready(function(){
        if ($("#hdnRest").val() == "1") {
            $("#statusSuccessModal").modal('show');
            $('[data-toggle="tooltip"]').tooltip();
        }
        //temporal
        $(".obligatorio, #institucionFinancieraAsegurado, #tipoCuentaAsegurado, #numeroCuentaAsegurado, #nombreMenorAsegurado, #identidadMenorAsegurado").each(function(){
            var campo = $(this);
            var idCampo = campo.attr("id");

            // Llenar según el tipo de campo
            if (campo.is("input[type='text']")) {
                campo.val("Texto de prueba");
            } 
            else if (campo.is("input[type='email']")) {
                campo.val("prueba@example.com");
            }
            else if (campo.is("input[type='email']")) {
                campo.val("prueba@example.com");
            }  
            else if (campo.is("input[type='number']")) {
                campo.val("12345");
            } 
            else if (campo.is("input[type='date']")) {
                // Obtener la fecha actual en formato DD/MM/YYYY
                var today = new Date();
                var dd = String(today.getDate()).padStart(2, '0'); // Día con dos dígitos
                var mm = String(today.getMonth() + 1).padStart(2, '0'); // Mes con dos dígitos
                var yyyy = today.getFullYear();

                var formattedDate = `${yyyy}-${mm}-${dd}`; // Formato compatible con input date
                campo.val(formattedDate);
            }
            else if (campo.is("textarea")) {
                campo.val("Este es un texto de prueba.");
            } 
            else if (campo.is("select")) {
                campo.find("option:eq(2)").prop("selected", true); // Selecciona la segunda opción
            }

            // Remover errores si los había
            campo.removeClass('is-invalid').next('.invalid-feedback').html('');
        });
        $("#formulario").submit(function(event){
            //$("#signature").val(signaturePad.toDataURL('image/png'));
            event.preventDefault();

            let file1 = $('#exampleFormControlFile1').val();
            let file2 = $('#exampleFormControlFile2').val();
            let validExtensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (!file1 || !file2) {
                $("#statusErrorsModal .errorMensaje").empty().text("Debe seleccionar ambos archivos.");
                $("#statusErrorsModal .errorEncabezado").empty().text("Informacion incompleta!");
                $('#statusErrorsModal').modal('show');
                return;
            }

            let ext1 = file1.split('.').pop().toLowerCase();
            let ext2 = file2.split('.').pop().toLowerCase();

            if (!validExtensions.includes(ext1) || !validExtensions.includes(ext2)) {
                $("#statusErrorsModal .errorMensaje").empty().text("Ambos archivos deben ser imágenes (jpg, jpeg, png, gif).");
                $("#statusErrorsModal .errorEncabezado").empty().text("Informacion incompleta!");
                $('#statusErrorsModal').modal('show');
                return;
            }

            $('#modal-loading').modal('show');
            $(this).unbind('submit').submit();
        });
        $("#confDepe, #rechDepe").click(function() {
            var liActivo = $("#progressbar").find('li.active:last');
            if($(this).attr("id") == "confDepe"){
                $("#hdnDependientes").val("1");
                $("#seccion6").hide().next().show();
                liActivo.next().addClass("active");
            }else{
                $("#hdnDependientes").val("0");
                $("#hdnDatosDependientes").val("");
                $("#seccion6").hide().next().next().show();
                liActivo.next().next().addClass("active");
                liActivo.next().addClass("disabled2");
            }
        });

        $(".next-section").click(function() {
            if (!validarSeccion($(this))) {
                var liActivo = $("#progressbar").find('li.active:last');
                if ($(this).attr("id") === "siguiente2"){
                    let idSeccion = $(this).attr("id");
                    $.get("html/modalConfirmSeccion.html", function(data) {
                        let contenido = '<div class="table-responsive"><table class="table table-striped"><tbody>';
                        let contador = 0;
                        const elementos = $("#seccion2").find("input, select, textarea");
                        elementos.each(function (index) {
                            contador ++;
                            if (contador === 1){
                                contenido = contenido + '<tr>';
                            }
                            let inputId = $(this).attr('id');
                            let inputValue = $(this).val();
                            if ($(this).is('select')){
                                inputValue = $(this).find('option:selected').text();
                            }
                            if ($(this).attr("type") === "date"){
                                let partes = inputValue.split("-");
                                inputValue = partes[2] + "/" + partes[1] + "/" + partes[0];
                            }
                            if (inputValue === ''){
                                inputValue = 'N/A';
                            }
                            let label = $('label[for="' + inputId + '"]').text();
                            contenido = contenido + '<td><p><strong>'+label+'</strong></p><p><span>'+inputValue+'</span></p></td>';
                            if (contador === 4 || index === elementos.length - 1){
                                contenido = contenido + '<td></td>'.repeat(4-contador) + '</tr>';

                                contador = 0;
                            }
                        });
                        contenido = contenido + '<tbody></table></div>';
                        data = data.replace("{{ID}}", idSeccion);
                        data = data.replace("{{PREGUNTAS}}", contenido);
                        $("#confirmModalSeccion").remove();
                        $("#principal").append(data);
                        $('#confirmModalSeccion').modal('show');
                    });
                }
                else if($(this).attr("id") === "siguiente5"){
                    let idSeccion = $(this).attr("id");
                    $.get("html/modalConfirmSeccion.html", function(data) {
                        let contenido = '<div class="table-responsive"><table class="table table-striped">' +
                            '<thead><tr><th>Nombre</th><th>Parentesco</th>' +
                            '<th>Porcentaje</th></tr></thead><tbody>';
                        let contador = 0;
                        const elementos = $("#tablaBeniSeg").find("input, select, textarea");
                        elementos.each(function (index) {
                            contador ++;
                            if (contador === 1){
                                contenido = contenido + '<tr>';
                            }
                            let inputId = $(this).attr('id');
                            let inputValue = $(this).val();
                            if ($(this).is('select')){
                                inputValue = $(this).find('option:selected').text();
                            }
                            if ($(this).attr("type") === "date" && inputValue !== '' ){
                                let partes = inputValue.split("-");
                                inputValue = partes[2] + "/" + partes[1] + "/" + partes[0];
                            }
                            if ($(this).hasClass("tblPorcentaje") && inputValue !== '' ){
                                inputValue = inputValue+"%";
                            }
                            if (inputValue === '' || inputValue === 'Seleccione...'){
                                inputValue = 'N/A';
                            }
                            contenido = contenido + '<td>'+inputValue+'</td>';
                            if (contador === 3 || index === elementos.length - 1){
                                contenido = contenido + '<td></td>'.repeat(3-contador) + '</tr>';
                                contador = 0;
                            }
                        });
                        contenido = contenido + '<tbody></table></div>';
                        data = data.replace("{{ID}}", idSeccion);
                        data = data.replace("{{PREGUNTAS}}", contenido);
                        $("#confirmModalSeccion").remove();
                        $("#principal").append(data);
                        $('#confirmModalSeccion').modal('show');
                    });
                }
                else if ($(this).attr("id") === "siguiente6") {
                    var nombreAsegurado = [
                        $("#primerNombreAsegurado").val(),
                        $("#segundoNombreAsegurado").val(),
                        $("#primerApellidoAsegurado").val(),
                        $("#segundoApellidoAsegurado").val()
                    ].filter(Boolean).join(" ");
                    var optDependientes = `<option value="">Seleccione...</option><option value="${nombreAsegurado}">${nombreAsegurado}</option>`;
                    $(".nombreAntecedenteSeguro").html(optDependientes);

                    $("#confirmModal #confirmModalBody").empty().text("¿Desea asegurar a sus dependientes?");
                    $("#statusErrorsModal #confirmModalTitle").empty().text("Dependientes");
                    $('#confirmModal').modal('show');
                }
                else if($(this).attr("id") === "siguiente7"){
                    let idSeccion = $(this).attr("id");
                    $.get("html/modalConfirmSeccion.html", function(data) {
                        let contenido = '<div class="table-responsive"><table class="table table-striped">' +
                            '<thead><tr><th>Nombre</th><th>Sexo</th><th>Parentesco</th><th>Ocupación</th>' +
                            '<th>Peso</th><th>Estatura</th><th>Fecha de Nacimiento</th><th>Número de identidad</th></tr></thead><tbody>';
                        let contador = 0;
                        const elementos = $("#tablaDependientes").find("input, select, textarea");
                        elementos.each(function (index) {
                            contador ++;
                            if (contador === 1){
                                contenido = contenido + '<tr>';
                            }
                            let inputId = $(this).attr('id');
                            let inputValue = $(this).val();
                            if ($(this).is('select')){
                                inputValue = $(this).find('option:selected').text();
                            }
                            if ($(this).attr("type") === "date" && inputValue !== '' ){
                                let partes = inputValue.split("-");
                                inputValue = partes[2] + "/" + partes[1] + "/" + partes[0];
                            }
                            if (inputValue === '' || inputValue === 'Seleccione...'){
                                inputValue = 'N/A';
                            }
                            contenido = contenido + '<td>'+inputValue+'</td>';
                            if (contador === 8 || index === elementos.length - 1){
                                contenido = contenido + '<td></td>'.repeat(8-contador) + '</tr>';
                                contador = 0;
                            }
                        });
                        contenido = contenido + '<tbody></table></div>';
                        data = data.replace("{{ID}}", idSeccion);
                        data = data.replace("{{PREGUNTAS}}", contenido);
                        $("#confirmModalSeccion").remove();
                        $("#principal").append(data);
                        $('#confirmModalSeccion').modal('show');
                    });
                }
                else if($(this).attr("id") === "siguiente7"){
                    var nombreAsegurado = [
                        $("#primerNombreAsegurado").val(),
                        $("#segundoNombreAsegurado").val(),
                        $("#primerApellidoAsegurado").val(),
                        $("#segundoApellidoAsegurado").val()
                    ].filter(Boolean).join(" ");
                    var optDependientes = `<option value="">Seleccione...</option><option value="${nombreAsegurado}">${nombreAsegurado}</option>`;
                    var dependientesData = $("#hdnDatosDependientes").val();

                    if (dependientesData) {
                        var hdnDatosDependientes = JSON.parse(dependientesData);

                        optDependientes += hdnDatosDependientes
                            .map((v, i) => `<option value="${v.nombreDependiente}">${v.nombreDependiente}</option>`)
                            .join('');
                    }
                    $(".nombreAntecedenteSeguro").html(optDependientes);
                    $("#seccion7").hide().next().show();
                    $(this).closest('div[id^="seccion"]').hide().next().show();
                    liActivo.next().addClass("active");
                }
                else{
                    $(this).closest('div[id^="seccion"]').hide().next().show();
                    liActivo.next().addClass("active");
                }
            }
        });
        $(".prev-section").click(function() {
            var liActivo = $("#progressbar").find('li.active:last');
            if ($(this).attr("id") === "anterior7" && $("#hdnDependientes").val() === "0") {
                $(this).closest('div[id^="seccion"]').hide().prev().prev().show();
                liActivo.removeClass("active");
                liActivo.prev().removeClass("disabled2");
            }
            else{
                $(this).closest('div[id^="seccion"]').hide().prev().show();
                liActivo.removeClass("active");
            }
        });

        $('#tablaBeniSeg, #tablaBeniConti, #tablaDependientes, #tablaAntecedentes, #tablaEnfermedades').on('click', '.btnLimpiar', function(){
            var fila = $(this).closest('tr'); // Obtiene la fila padre del botón clickeado
            fila.find('input, select').val('').removeClass('is-invalid').next('.invalid-feedback').html('');; // Limpia todos los campos de la fila
        });

        $('.seleccion-radio').change(function(){
            var valorSeleccionado = $(this).val();
            var trPregunta = $(this).closest('tr');
            var pregunta = trPregunta.attr("pregunta");

            if (valorSeleccionado == "0") {
                $('#modalPreguntas_' + pregunta).remove();
                trPregunta.find('.abrirModalPreguntas').prop('disabled', true);
            } else {
                var nombreAsegurado = [
                    $("#primerNombreAsegurado").val(),
                    $("#segundoNombreAsegurado").val(),
                    $("#primerApellidoAsegurado").val(),
                    $("#segundoApellidoAsegurado").val()
                ].filter(Boolean).join(" "); // Elimina espacios en blanco extras

                var optDependientes = `<option value="">Seleccione...</option><option value="${nombreAsegurado}">${nombreAsegurado}</option>`;
                if(pregunta == "18" && $("#generoAsegurado").val() == "M"){
                    optDependientes = ``;
                }

                var dependientesData = $("#hdnDatosDependientes").val();

                if (dependientesData) {
                    var hdnDatosDependientes = JSON.parse(dependientesData);

                    optDependientes += hdnDatosDependientes
                        .filter(v => pregunta !== "18" || v.sexoDependiente === "F") // Si pregunta es "18", solo incluye 'F'
                        .map((v, i) => `<option value="${v.nombreDependiente}">${v.nombreDependiente}</option>`)
                        .join('');
                }

                var contadorModal = $(".form_" + pregunta).length + 1;
                if (pregunta === "24" || pregunta === "25") {
                    $.get("html/modalPreguntas2.html", function(data) {
                        var contenido = data.replace(/{{ID}}/g, pregunta);
                        var resp = `
                            <form class="form_${pregunta}">
                                <div class="form-group">
                                    <label class="form-label">Detalle:</label>
                                    <div>
                                        <textarea type="text" id="detallePregunta_${pregunta}" name="detallePregunta_${pregunta}" class="form-control form-control-sm detallePregunta"></textarea>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </form>
                        `;
                        contenido = contenido.replace(/{{PREGUNTAS}}/g, resp);
                        $("#principal").append(contenido);
                        $('#modalPreguntas_' + pregunta).modal('show');
                    });
                }else{
                    $.get("html/modalPreguntas.html", function(data) {
                        var contenido = data.replace(/{{ID}}/g, pregunta);
                        var resp = `
                            <form class="form_${pregunta}">
                                <div class="form-group">
                                    <label class="form-label">Nombre:</label>
                                    <div>
                                        <select id="nombrePregunta_${pregunta}" name="nombrePregunta_${pregunta}" class="form-control form-control-sm nombrePregunta">${optDependientes}</select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Diagnóstico:</label>
                                    <div>
                                        <input type="text" id="diagnosticoPregunta_${pregunta}" name="diagnosticoPregunta_${pregunta}" class="form-control form-control-sm diagnosticoPregunta">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Tratamiento:</label>
                                    <div>
                                        <input type="text" id="tratamientoPregunta_${pregunta}" name="tratamientoPregunta_${pregunta}" class="form-control form-control-sm tratamientoPregunta">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Fecha:</label>
                                    <div>
                                        <input type="date" id="fechaPregunta_${pregunta}" name="fechaPregunta_${pregunta}" class="form-control form-control-sm fechaPregunta">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Médico u hospital:</label>
                                    <div>
                                        <input type="text" id="medicoPregunta_${pregunta}" name="medicoPregunta_${pregunta}" class="form-control form-control-sm medicoPregunta">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </form>
                        `;
                        contenido = contenido.replace(/{{PREGUNTAS}}/g, resp);
                        $("#principal").append(contenido);
                        $('#modalPreguntas_' + pregunta).modal('show');
                    });   
                }
            }
        });

        $("select").change(function(){
            // Remueve la clase de error y limpia el mensaje asociado
            $(this).removeClass('is-invalid').next('.invalid-feedback').html('');

            // Verifica si el select modificado es el de estado civil o género del asegurado
            if ($(this).attr('id') === "estadoCivilAsegurado" || $(this).attr('id') === "generoAsegurado") {
                // Si el estado civil del asegurado no es 'C', se limpia el nombre del cónyuge
                if ($("#estadoCivilAsegurado").val() !== 'C'){
                    $("#nombreConyuge").val("");
                }
                // Habilita o deshabilita el campo nombre del cónyuge según el valor del estado civil
                if ($("#estadoCivilAsegurado").val() === 'C') {
                    $("#nombreConyuge").prop('disabled', false);
                } else {
                    $("#nombreConyuge").prop('disabled', true);
                    $("#nombreConyuge").val("");
                }
            }
            else if ($(this).attr('id') === "otroSeguro" && $(this).val() === "0") {
                $("#otroTipoSeguro").val("");
            }
            else if ($(this).attr('id') === "cargoPublicoAsegurado") {
                if ($(this).val() === "1") {
                    $("#nombreCargoPublicoAsegurado").prop('disabled', false);
                }else{
                    $("#nombreCargoPublicoAsegurado").prop('disabled', true);
                    $("#nombreCargoPublicoAsegurado").val("");
                }
            }

            if ($(this).hasClass("sexoDependiente")) {
                var sexo = $(this).val();
                var cmbParentesco = $(this).closest('td').next('td').find('.parentescoDependiente')
                cmbParentesco.val("");
                cmbParentesco.find('option').each(function () {
                    let generoOpcion = $(this).attr('genero');

                    if (generoOpcion === "" || generoOpcion === sexo) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }
        });

        $("input, textarea").change(function() {
            $(this).removeClass('is-invalid').next('.invalid-feedback').html('');
            if ($(this).attr('id') === "nombreMenorAsegurado" && $("#identidadMenorAsegurado").val() !== "") {
                $("#identidadMenorAsegurado").removeClass('is-invalid').next('.invalid-feedback').html('');
            }
            else if ($(this).attr('id') === "identidadMenorAsegurado" && $("#nombreMenorAsegurado").val() !== "") {
                $("#nombreMenorAsegurado").removeClass('is-invalid').next('.invalid-feedback').html('');
            } 
        });

        $(document).on("change",".cmbEnfermedad",function() {
            var elemento = this;
            var modalPreguntas = $(elemento).closest('.modal');
            var idPregunta = modalPreguntas.attr("id").split("_")[1];
            var seleccion = $(this).find('option:selected').attr('otra');
            modalPreguntas.find('.otra').removeClass('is-invalid');
            if (seleccion == "1") {
                modalPreguntas.find('.otra').show();
                
                modalPreguntas.find('.otra').change(function(){
                    $(this).removeClass('is-invalid').next('.invalid-feedback');
                });
            }else{
                modalPreguntas.find('.otra').hide().val("");
            }
        });
    });
    
    function enviarFormulario(params) {
        var $formulario = $("#formulario");
        var beneficiariosSeguro = [];
        $('#tablaBeniSeg tbody tr').each(function(){
            var objTemp = {};
            var $fila = $(this);

            $fila.find('input, select').each(function(){
                var $elemento = $(this);
                if ($elemento.hasClass("nombreBenAse") && $elemento.val() != "") {
                    objTemp["nombres"] = $elemento.val();
                }
                else if ($elemento.hasClass("parentescoBenAse") && $elemento.val() != "") {
                    objTemp["parentesco"] = $elemento.val();
                }
                else if ($elemento.hasClass("tblPorcentaje") && $elemento.val() != "") {
                    objTemp["porcentaje"] = $elemento.val();
                }
            });
            if (Object.keys(objTemp).length > 0) {
                beneficiariosSeguro.push(objTemp);
            }
        });
        $("#hdnBeneficiariosSeguro").val(JSON.stringify(beneficiariosSeguro));

        var beneficiariosContingencia = [];
        $('#tablaBeniConti tbody tr').each(function(){
            var objTemp = {};
            var $fila = $(this);
            $fila.find('input, select').each(function(){
                var $elemento = $(this);
                if ($elemento.hasClass("nombreBeniConti") && $elemento.val() != "") {
                    objTemp["nombres"] = $elemento.val();
                }
                else if ($elemento.hasClass("parentescoBeniConti") && $elemento.val() != "") {
                    objTemp["parentesco"] = $elemento.val();
                }
                else if ($elemento.hasClass("tblPorcentaje") && $elemento.val() != "") {
                    objTemp["porcentaje"] = $elemento.val();
                }
            });
            
            if (Object.keys(objTemp).length > 0) {
                beneficiariosContingencia.push(objTemp);
            }
        });
        $("#hdnBeneficiariosContingencia").val(JSON.stringify(beneficiariosContingencia));

        var dependientes = [];
        $('#tablaDependientes tbody tr').each(function(){
            var objTemp = {};
            var $fila = $(this);
            $fila.find('input, select').each(function(){
                var $elemento = $(this);
                if ($elemento.hasClass("nombreDependiente") && $elemento.val() != "") {
                    objTemp["nombre"] = $elemento.val();
                }
                else if ($elemento.hasClass("sexoDependiente") && $elemento.val() != "") {
                    objTemp["sexo"] = $elemento.val();
                }
                else if ($elemento.hasClass("parentescoDependiente") && $elemento.val() != "") {
                    objTemp["parentesco"] = $elemento.val();
                }
                else if ($elemento.hasClass("ocupacionDependiente") && $elemento.val() != "") {
                    objTemp["ocupacion"] = $elemento.val();
                }
                else if ($elemento.hasClass("pesoDependiente") && $elemento.val() != "") {
                    objTemp["peso"] = $elemento.val();
                }
                else if ($elemento.hasClass("estaturaDependiente") && $elemento.val() != "") {
                    objTemp["estatura"] = $elemento.val();
                }
                else if ($elemento.hasClass("fechaNacimientoDependiente") && $elemento.val() != "") {
                    objTemp["fechaNacimiento"] = $elemento.val();
                }
                else if ($elemento.hasClass("identidadDependiente") && $elemento.val() != "") {
                    objTemp["identidad"] = $elemento.val();
                }
            });
            
            if (Object.keys(objTemp).length > 0) {
                dependientes.push(objTemp);
            }
        });
        $("#hdnDatosDependientes").val(JSON.stringify(dependientes));

        var antecedentes = [];
        $('#tablaAntecedentes tbody tr').each(function(){
            var objTemp = {};
            var $fila = $(this);
            $fila.find('input, select').each(function(){
                var $elemento = $(this);
                if ($elemento.hasClass("tipoAntecedenteSeguro") && $elemento.val() != "") {
                    objTemp["tipo"] = $elemento.val();
                }
                else if ($elemento.hasClass("nombreAntecedenteSeguro") && $elemento.val() != "") {
                    objTemp["nombre"] = $elemento.val();
                }
                else if ($elemento.hasClass("aseguradoraAntecedenteSeguro") && $elemento.val() != "") {
                    objTemp["aseguradora"] = $elemento.val();
                }
                else if ($elemento.hasClass("polizaAntecedenteSeguro") && $elemento.val() != "") {
                    objTemp["poliza"] = $elemento.val();
                }
            });
            
            if (Object.keys(objTemp).length > 0) {
                antecedentes.push(objTemp);
            }
        });
        $("#hdnDatosAntecedentes").val(JSON.stringify(antecedentes));

        var respuestas = [];
        $('#tablaPreguntas tbody tr[pregunta]').each(function(){
            var $pregunta = $(this);
            var numeroPregunta = $pregunta.attr("pregunta"); 

            $(".form_"+numeroPregunta).each(function(){
                var $formulario = $(this);
                var respuesta ={
                    id: numeroPregunta,
                    nombre: $formulario.find(".nombrePregunta").val(),
                    diagnostico: $formulario.find(".diagnosticoPregunta").val(),
                    tratamiento: $formulario.find(".tratamientoPregunta").val(),
                    fecha: $formulario.find(".fechaPregunta").val(),
                    medico: $formulario.find(".medicoPregunta").val(),
                    detalle: $formulario.find(".detallePregunta").val()
                };
                respuestas.push(respuesta);
            });
        });
        $("#hdnRespuestas").val(JSON.stringify(respuestas));
        $formulario.submit();
    }

    function seleccionarForm(elemento) {
        if (validarFormAgregar(elemento)) {
            var modalPreguntas = $(elemento).closest('.modal');

            // Verificar que el modal tenga un ID válido con el formato esperado
            var modalId = modalPreguntas.attr("id");
            if (!modalId || modalId.indexOf("_") === -1) {
                console.error("ID del modal no tiene el formato esperado.");
                return;
            }

            var idPregunta = modalId.split("_")[1];

            var forms = $('.form_' + idPregunta);
            if (forms.length === 0) {
                console.error("No se encontraron formularios con la clase 'form_" + idPregunta + "'");
                return;
            }

            var pageNum = parseInt($(elemento).text(), 10);
            if (isNaN(pageNum) || pageNum < 1 || pageNum > forms.length) {
                console.error("Número de página inválido: " + pageNum);
                return;
            }

            mostrarPagina(pageNum, forms, modalPreguntas);
        }
    }

    function mostrarPagina(pageNum, forms, modalPreguntas) {
        var formsPerPage = 1;
        var startIndex = (pageNum - 1) * formsPerPage;//1
        var endIndex = pageNum * formsPerPage;//2
        forms.hide().slice(startIndex, endIndex).show();

        // Remover la clase 'active' de todos los botones de paginación
        modalPreguntas.find('#pagination button').removeClass('active');
        // Agregar la clase 'active' al botón de la página actual
        modalPreguntas.find('#pagination button').eq(pageNum - 1).addClass('active');
    }

    function eliminarModalPreguntas(elemento) {
        var modalPreguntas = $(elemento).closest('.modal');
        var paginaActual = modalPreguntas.find('#pagination button.active').text();
        var formsPerPage = 1;
        var startIndex = (paginaActual - 1) * formsPerPage;
        var endIndex = paginaActual * formsPerPage;
        var idPregunta = modalPreguntas.attr("id").split("_")[1];
        var forms = $('.form_'+idPregunta);
        var numForms = forms.length;

        // Eliminar el botón de paginación y el formulario correspondiente
        if (numForms == 1) {
            $('#seleccion'+idPregunta+'N').prop('checked', true);
            $('#seleccion'+idPregunta+'S').prop('checked', false);
            $("#btnVerPreguntas"+idPregunta).prop("disabled", true);
            modalPreguntas.modal("hide");
            modalPreguntas.on('hidden.bs.modal', function (e) {
                $(this).remove();
            });
        }else{
            modalPreguntas.find("form").eq(paginaActual-1).remove();
        }
        numForms -= 1;
        if (numForms == 1) {
            modalPreguntas.find('#pagination button').remove();
        }else{
            modalPreguntas.find('#pagination button.active').remove();
        }

        forms = $('.form_'+idPregunta);
        modalPreguntas = $(elemento).closest('.modal');
        numPages = Math.ceil(numForms / formsPerPage);
        modalPreguntas.find('#pagination').empty();
        if (numForms > 1) {
            for (var i = 1; i <= numPages; i++) {
                modalPreguntas.find('#pagination').append('<button type="button" class="page-link" onclick="seleccionarForm(this)">' + i + '</button>');
            }
        }
        if (numPages < paginaActual) {
            paginaActual -= 1;
        }
        mostrarPagina(paginaActual, forms, modalPreguntas);
    }

    function validarFormAgregar(elemento) {
        var modalPreguntas = $(elemento).closest('.modal');
        var idPregunta = modalPreguntas.attr("id").split("_")[1];
        var paginaActual = modalPreguntas.find('#pagination button.active').text();
        var formValidar = modalPreguntas.find("form").eq(paginaActual-1);
        var tratamiento = 0;
        var lleno = true;
        formValidar.find('input:visible, select:visible, textarea:visible').each(function(){
            if ($(this).val() == '') {
                if ($(this).hasClass("tratamiento")) {
                    tratamiento = tratamiento + 1;
                }else{
                    lleno = false;
                    $(this).addClass('is-invalid').next('.invalid-feedback').html('Campo requerido.');
                }
            }else{
                $(this).removeClass('is-invalid').next('.invalid-feedback').html('');
            }
            $(this).change(function(){
                if ($(this).hasClass("tratamiento")) {
                    formValidar.find('.tratamiento').removeClass('is-invalid');
                }else{
                    $(this).removeClass('is-invalid').next('.invalid-feedback').html('');
                }
            });
        });
        if (tratamiento == 3) {
            formValidar.find('.tratamiento').addClass('is-invalid');
            lleno = false;
        }else{
            formValidar.find('.tratamiento').removeClass('is-invalid');
        }
        return lleno;
    }

    function agregarModalPreguntas(elemento) {
        if (validarFormAgregar(elemento)) {
            var modalPreguntas = $(elemento).closest('.modal');

            // Verificar que el modal tenga un ID con el formato esperado
            var modalId = modalPreguntas.attr("id");
            if (!modalId || modalId.indexOf("_") === -1) {
                console.error("ID del modal no tiene el formato esperado.");
                return;
            }

            var idPregunta = modalId.split("_")[1];

            var nombreAsegurado = [
                $("#primerNombreAsegurado").val(),
                $("#segundoNombreAsegurado").val(),
                $("#primerApellidoAsegurado").val(),
                $("#segundoApellidoAsegurado").val()
            ].filter(Boolean).join(" "); // Elimina espacios en blanco extras

            var optDependientes = `<option value="">Seleccione...</option><option value="${nombreAsegurado}">${nombreAsegurado}</option>`;
            if(idPregunta == "18" && $("#generoAsegurado").val() == "M"){
                optDependientes = ``;
            }

            var dependientesData = $("#hdnDatosDependientes").val();

            if (dependientesData) {
                var hdnDatosDependientes = JSON.parse(dependientesData);

                optDependientes += hdnDatosDependientes
                    .filter(v => idPregunta !== "18" || v.sexoDependiente === "F") // Si pregunta es "18", solo incluye 'F'
                    .map((v, i) => `<option value="${v.nombreDependiente}">${v.nombreDependiente}</option>`)
                    .join('');
            }
            var resp = '';
            if (idPregunta === "24" || idPregunta === "24") {
                resp = `
                    <form class="form_${idPregunta}">
                        <div class="form-group">
                            <label class="form-label">Detalle:</label>
                            <div>
                                <textarea id="detallePregunta_${idPregunta}" name="detallePregunta_${idPregunta}" class="form-control form-control-sm detallePregunta"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </form>
                `;
            }else{
                resp = `
                    <form class="form_${idPregunta}">
                        <div class="form-group">
                            <label class="form-label">Nombre:</label>
                            <div>
                                <select id="nombrePregunta_${idPregunta}" name="nombrePregunta_${idPregunta}" class="form-control form-control-sm nombrePregunta">
                                    ${optDependientes}
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Diagnóstico:</label>
                            <div>
                                <input type="text" id="diagnosticoPregunta_${idPregunta}" name="diagnosticoPregunta_${idPregunta}" class="form-control form-control-sm diagnosticoPregunta">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tratamiento:</label>
                            <div>
                                <input type="text" id="tratamientoPregunta_${idPregunta}" name="tratamientoPregunta_${idPregunta}" class="form-control form-control-sm tratamientoPregunta">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Fecha:</label>
                            <div>
                                <input type="date" id="fechaPregunta_${idPregunta}" name="fechaPregunta_${idPregunta}" class="form-control form-control-sm fechaPregunta">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Médico u hospital:</label>
                            <div>
                                <input type="text" id="medicoPregunta_${idPregunta}" name="medicoPregunta_${idPregunta}" class="form-control form-control-sm medicoPregunta">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </form>
                `;
            }

            // Agregar el formulario al modal
            if (modalPreguntas.find("form").length) {
                modalPreguntas.find("form").last().after(resp);
            } else {
                modalPreguntas.append(resp);
            }

            var formsPerPage = 1; // Cantidad de formularios por página
            var forms = $('.form_' + idPregunta);
            var numForms = forms.length;
            var numPages = Math.ceil(numForms / formsPerPage);

            // Generar botones de paginación
            if (numForms >= 2) {
                modalPreguntas.find('#pagination').empty();
                for (var i = 1; i <= numPages; i++) {
                    modalPreguntas.find('#pagination').append(`<button type="button" class="page-link" onclick="seleccionarForm(this)">${i}</button>`);
                }
            } else {
                modalPreguntas.find('#pagination').append(`<button type="button" class="page-link" onclick="seleccionarForm(this)">${numForms}</button>`);
            }

            // Mostrar la página correspondiente
            mostrarPagina(numForms, forms, modalPreguntas);
        }
    }


    function validarModalPreguntas(elemento) {
        var modalPreguntas = $(elemento).closest('.modal');
        var idPregunta = modalPreguntas.attr("id").split("_")[1];
        if (validarFormAgregar(elemento)) {
            $("#btnVerPreguntas"+idPregunta).attr("disabled", false);
            modalPreguntas.modal("hide");    
        }
    }

    function validarSeccion(elemento){
        let seccion = elemento.attr('id');
        switch (seccion) {
            case 'siguiente2':
                elementos = $(".obligatorio");
                elementos.removeClass('is-invalid').next('.invalid-feedback').html('');
                var camposVacios = false;
                var emailRegex = /\S+@\S+\.\S+/; // Expresión regular para validar email

                elementos.each(function() {
                    var campo = $(this);
                    var idCampo = campo.attr("id");

                    // Ignorar campos ocultos o deshabilitados
                    if (!campo.is(':visible') || campo.prop('disabled')) return;

                    // Validación del estado civil
                    if (idCampo === "estadoCivilAsegurado") {
                        if (campo.val() === '') {
                            camposVacios = true;
                            campo.addClass('is-invalid').next('.invalid-feedback').html('Campo requerido.');
                        } else if (campo.val() === 'C' && $("#nombreConyuge").val() === "") {
                            camposVacios = true;
                            $("#nombreConyuge").addClass('is-invalid').next('.invalid-feedback').html('Campo requerido.');
                        }
                    }
                    // Validación de al menos un celular
                    else if (idCampo === "celularAsegurado1") {
                        if (campo.val() === '' && $("#celularAsegurado2").val() === "") {
                            camposVacios = true;
                            campo.addClass('is-invalid').next('.invalid-feedback').html('Campo requerido.');
                        }
                    }
                    // Validación de correos electrónicos
                    else if (idCampo === "emailAsegurado1") {
                        var email1 = campo.val();
                        var email2 = $("#emailAsegurado2").val();

                        if (email1 === '' && email2 === "") {
                            camposVacios = true;
                            campo.addClass('is-invalid').next('.invalid-feedback').html('Campo requerido.');
                        } else {
                            if (email1 !== '' && !emailRegex.test(email1)) {
                                camposVacios = true;
                                campo.addClass('is-invalid').next('.invalid-feedback').html('Formato incorrecto.');
                            }
                            if (email2 !== '' && !emailRegex.test(email2)) {
                                camposVacios = true;
                                $("#emailAsegurado2").addClass('is-invalid').next('.invalid-feedback').html('Formato incorrecto.');
                            }
                        }
                    }
                    // Validación de nacionalidad (al menos una debe estar llena)
                    else if (idCampo === "nacionalidadAsegurado1") {
                        if (campo.val() === '' && $("#nacionalidadAsegurado2").val() === "") {
                            camposVacios = true;
                            campo.addClass('is-invalid').next('.invalid-feedback').html('Campo requerido.');
                        }
                    }
                    // Validación general para otros campos
                    else {
                        if (campo.val() === '') {
                            camposVacios = true;
                            campo.addClass('is-invalid').next('.invalid-feedback').html('Campo requerido.');
                        }
                    }
                });

                // Mostrar mensaje de error si hay campos vacíos
                if (camposVacios) {
                    $("#statusErrorsModal .errorMensaje").empty().text("Debe ingresar todos los campos requeridos.");
                    $("#statusErrorsModal .errorEncabezado").empty().text("Información incompleta!");
                    $('#statusErrorsModal').modal('show');
                }

                return camposVacios;
            break;
            case 'siguiente3':
                elementos = $("#institucionFinancieraAsegurado, #tipoCuentaAsegurado, #numeroCuentaAsegurado, #nombreMenorAsegurado, #identidadMenorAsegurado");
                elementos.removeClass('is-invalid').next('.invalid-feedback').html('');
                var camposVacios = false;

                elementos.each(function() {
                    if ($(this).attr("id") == "identidadMenorAsegurado") {
                        if ($(this).val() == '') {
                            if ($("#nombreMenorAsegurado").val() != '') {
                                camposVacios = true;
                                $(this).addClass('is-invalid').next('.invalid-feedback').html('Campo requerido.');
                            }
                        }
                    }else if ($(this).attr("id") == "nombreMenorAsegurado") {
                        if ($(this).val() == '') {
                            if ($("#identidadMenorAsegurado").val() != '') {
                                camposVacios = true;
                                $(this).addClass('is-invalid').next('.invalid-feedback').html('Campo requerido.');
                            }
                        }
                    }else{
                        if ($(this).val() == '') {
                            camposVacios = true;
                            $(this).addClass('is-invalid').next('.invalid-feedback').html('Campo requerido.');
                        }
                    }
                });

                if (camposVacios) {
                    $("#statusErrorsModal .errorMensaje").empty().text("Debe ingresar todos los campos requeridos.");
                    $("#statusErrorsModal .errorEncabezado").empty().text("Informacion incompleta!");
                    $('#statusErrorsModal').modal('show');
                }

                return camposVacios;
            break;
            case 'siguiente4':
                elementos = $("#otroSeguro, #otroTipoSeguro, #extraSeguro, #tipoExtraSeguro");
                elementos.removeClass('is-invalid').next('.invalid-feedback').html('');
                var camposVacios = false;

                const validaciones = [
                    { selector: "#otroSeguro", condicion: val => val === "1" && $("#otroTipoSeguro").val() === "", error: "#otroTipoSeguro" },
                    { selector: "#extraSeguro", condicion: val => val !== "" && $("#tipoExtraSeguro").val() === "", error: "#tipoExtraSeguro" },
                    { selector: "#tipoExtraSeguro", condicion: val => val !== "" && $("#extraSeguro").val() === "", error: "#extraSeguro" }
                ];

                validaciones.forEach(({ selector, condicion, error }) => {
                    if (condicion($(selector).val())) {
                        camposVacios = true;
                        $(error).addClass('is-invalid').next('.invalid-feedback').html('Campo requerido.');
                    }
                });

                if (camposVacios) {
                    $("#statusErrorsModal .errorMensaje").empty().text("Debe ingresar todos los campos requeridos.");
                    $("#statusErrorsModal .errorEncabezado").empty().text("Informacion incompleta!");
                    $('#statusErrorsModal').modal('show');
                }

                return camposVacios;
            break;
            case 'siguiente5':
                var datos = [];
                var camposVacios = false;
                var porcentaje = 0.00;
                
                $('#tablaBeniSeg tbody tr').each(function(){
                    var lleno = false; // Variable para verificar si al menos una columna está llena
                    
                    $(this).find('input, select').each(function(){
                        if ($(this).val() != '') {
                            lleno = true;
                        }
                    });
                    
                    if (lleno) {
                        datos.push($(this));
                    }
                });
                if (datos.length === 0) {
                    $("#statusErrorsModal .errorMensaje").empty().text("Debe registrar uno o mas beneficiarios");
                    $("#statusErrorsModal .errorEncabezado").empty().text("Informacion incompleta!");
                    $('#statusErrorsModal').modal('show');
                    return true;
                }
                $.each(datos,function(){
                    $(this).find('input, select').each(function() {
                        if ($(this).val() == '') {
                            camposVacios = true;
                            $(this).addClass('is-invalid').next('.invalid-feedback').html('Campo requerido.');
                        }else{
                            if ($(this).hasClass("tblPorcentaje")) {
                                porcentaje = porcentaje + parseFloat($(this).val());
                            }
                        }
                    });
                });
                if (camposVacios) {
                    $("#statusErrorsModal .errorMensaje").empty().text("Debe ingresar todos los campos requeridos.");
                    $("#statusErrorsModal .errorEncabezado").empty().text("Informacion incompleta!");
                    $('#statusErrorsModal').modal('show');
                    return camposVacios;
                }
                if (porcentaje != 100) {
                    $("#statusErrorsModal .errorMensaje").empty().text("El total de los porcentajes de todos los beneficiario debe ser igual al 100%");
                    $("#statusErrorsModal .errorEncabezado").empty().text("Informacion incompleta!");
                    $('#statusErrorsModal').modal('show');
                    return true;
                }

                return camposVacios;
            break;
            case 'siguiente6':
                var datos = [];
                var camposVacios = false;
                var porcentaje = 0.00;

                $('#tablaBeniConti tbody tr').each(function(){
                    var lleno = false; // Variable para verificar si al menos una columna está llena

                    $(this).find('input, select').each(function(){
                        if ($(this).val().trim() !== '') {
                            lleno = true;
                        }
                    });

                    if (lleno) {
                        datos.push($(this));
                    }
                });

                $.each(datos, function(){
                    $(this).find('input, select').each(function() {
                        var $this = $(this);

                        if ($this.val().trim() === '') {
                            camposVacios = true;
                            $this.addClass('is-invalid');

                            if ($this.next('.invalid-feedback').length === 0) {
                                $this.after('<div class="invalid-feedback">Campo requerido.</div>');
                            } else {
                                $this.next('.invalid-feedback').html('Campo requerido.');
                            }
                        } else {
                            if ($this.hasClass("tblPorcentaje")) {
                                porcentaje += parseFloat($this.val()) || 0;
                            }
                        }
                    });
                });

                if (camposVacios) {
                    return camposVacios;
                }

                if (datos.length > 0 && porcentaje !== 100) {
                    $("#statusErrorsModal .errorMensaje").text("El total de los porcentajes de cada beneficiario debe ser igual al 100%");
                    $("#statusErrorsModal .errorEncabezado").text("Información incompleta!");
                    $('#statusErrorsModal').modal('show');
                    return true;
                }

                $('#tablaPreguntas tbody tr[pregunta="18"] input').prop('disabled', $("#generoAsegurado").val() !== "F");

                return camposVacios;
            break;
            case 'siguiente7':
                var datos = [];
                var hdnDatosDependientes = [];
                var camposVacios = false;
                var bloqueo = true;

                $('#tablaDependientes tbody tr').each(function(){
                    var inputs = $(this).find('input, select');
                    var lleno = inputs.filter(function() { return $(this).val() !== ''; }).length > 0;

                    if (lleno) {
                        datos.push(inputs.toArray());
                    }
                });

                $.each(datos, function(index, inputsArray){
                    var objTemp = { id: index };
                    var sexoDependiente = "";

                    $(inputsArray).each(function() {
                        var $this = $(this);
                        var nombre = $this.attr("nombre");

                        if (nombre == "sexoDependiente") {
                            sexoDependiente = $this.val();
                        }

                        $this.removeClass('is-invalid'); // Limpia errores previos
                        if ($this.val() === '') {
                            camposVacios = true;
                            $this.addClass('is-invalid');

                            if ($this.next('.invalid-feedback').length === 0) {
                                $this.after('<div class="invalid-feedback">Campo requerido.</div>');
                            } else {
                                $this.next('.invalid-feedback').html('Campo requerido.');
                            }
                        } else {
                            objTemp[nombre] = $this.val();

                            if (nombre === "parentescoDependiente" && $this.val() == "5" 
                                && $("#generoAsegurado").val() == "M" && sexoDependiente == "F") {
                                bloqueo = false;
                            }
                        }
                    });

                    if (!camposVacios) {
                        hdnDatosDependientes.push(objTemp);
                    }
                });

                bloqueo = bloqueo || ($("#generoAsegurado").val() == "F");

                $('#tablaPreguntas tbody tr[pregunta="18"] input').prop('disabled', bloqueo);

                if (camposVacios) {
                    $("#statusErrorsModal .errorMensaje").text("Debe ingresar todos los campos requeridos.");
                    $("#statusErrorsModal .errorEncabezado").text("Información incompleta!");
                    $('#statusErrorsModal').modal('show');
                    return true;
                }

                $("#hdnDatosDependientes").val(JSON.stringify(hdnDatosDependientes));
                return false;
            break;

            case 'siguiente8':
                var datos = [];
                var hdnDatosAntecedentes = [];
                var camposVacios = false;
                var bloqueo = true;

                $('#tablaAntecedentes tbody tr').each(function(){
                    var inputs = $(this).find('input, select');
                    var lleno = inputs.filter(function() { return $(this).val() !== ''; }).length > 0;

                    if (lleno) {
                        datos.push(inputs.toArray());
                    }
                });

                $.each(datos, function(index, inputsArray){
                    var objTemp = { id: index };
                    
                    $(inputsArray).each(function() {
                        var $this = $(this);
                        var nombre = $this.attr("nombre");

                        $this.removeClass('is-invalid'); // Limpia errores previos
                        if ($this.val() === '') {
                            camposVacios = true;
                            $this.addClass('is-invalid');

                            if ($this.next('.invalid-feedback').length === 0) {
                                $this.after('<div class="invalid-feedback">Campo requerido.</div>');
                            } else {
                                $this.next('.invalid-feedback').html('Campo requerido.');
                            }
                        } else {
                            objTemp[nombre] = $this.val();
                        }
                    });

                    if (!camposVacios) {
                        hdnDatosAntecedentes.push(objTemp);
                    }
                });

                if (camposVacios) {
                    $("#statusErrorsModal .errorMensaje").text("Debe ingresar todos los campos requeridos.");
                    $("#statusErrorsModal .errorEncabezado").text("Información incompleta!");
                    $('#statusErrorsModal').modal('show');
                    return true;
                }

                $("#hdnDatosAntecedentes").val(JSON.stringify(hdnDatosAntecedentes));
                return false;
            break;

            default:
            break;
        }
    }

    function pasarPagina(siguiente) {
        let liActivo = $("#progressbar").find('li.active:last');
        let estadosProhibidos = ["2", "4"];
        if (siguiente === "siguiente2"){
            $("#seccion2").hide().next().show();
            liActivo.next().addClass("active");
        }
        else if (siguiente === "siguiente5"){
            $("#seccion5").hide().next().show();
            liActivo.next().addClass("active");
        }
        else if (siguiente === "siguiente7"){
            var nombreAsegurado = [
                $("#primerNombreAsegurado").val(),
                $("#segundoNombreAsegurado").val(),
                $("#primerApellidoAsegurado").val(),
                $("#segundoApellidoAsegurado").val()
            ].filter(Boolean).join(" ");
            var optDependientes = `<option value="">Seleccione...</option><option value="${nombreAsegurado}">${nombreAsegurado}</option>`;
            var dependientesData = $("#hdnDatosDependientes").val();

            if (dependientesData) {
                var hdnDatosDependientes = JSON.parse(dependientesData);

                optDependientes += hdnDatosDependientes
                    .map((v, i) => `<option value="${v.nombreDependiente}">${v.nombreDependiente}</option>`)
                    .join('');
            }
            $(".nombreAntecedenteSeguro").html(optDependientes);
            $("#seccion7").hide().next().show();
            liActivo.next().addClass("active");
        }
    }
</script>

</body>
</html>