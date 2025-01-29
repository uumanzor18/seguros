<?php
global $conn;
require('conexion.php');
$conn->set_charset("utf8");

$selectParentesco = "SELECT P.PARENTESCO, CONCAT(UCASE(SUBSTRING(P.NOMBRE, 1, 1)), LOWER(SUBSTRING(P.NOMBRE, 2))) AS NOMBRE
from parentesco P";
$resultParentesco= $conn->query($selectParentesco);
$parentescos = "<option value=''>Seleccione...</option>";
$parentescosDependiente = "<option value=''>Seleccione...</option>";
$tiposParentestcos = [3,4,5];
if ($resultParentesco->num_rows > 0) {
    while($row = $resultParentesco->fetch_assoc()) {
        if (in_array($row["PARENTESCO"], $tiposParentestcos)) {
            $parentescosDependiente .= '<option value="'.$row["PARENTESCO"].'">'.$row["NOMBRE"].'</option>';
        }
        $parentescos .= '<option value="'.$row["PARENTESCO"].'">'.$row["NOMBRE"].'</option>';
    }
} else {
    echo "<option value=''>No hay opciones</option>";
}
$resultParentesco->close();
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
        background-color: #ED1C24;
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
  </style>
</head>
<body>
    <div class="container hide-on-small-screen">
        <ul id="progressbar">
            <li class="active">Inicio</li>
            <li>Datos Generales</li>
            <li>Datos Conyugue</li>
            <li>Direccion Asegurado</li>
            <li>Beneficiarios Seguro De Vida</li>
            <li>Beneficiario Contingencia</li>
            <li>Dependientes Economicos</li>
            <li>Preguntas</li>
            <li>Fin</li>
        </ul>
    </div>
<div class="container container2" id="principal">
    <form action="print_formulario.php" method="post"  enctype="multipart/form-data" id="formulario" accept-charset="UTF-8">
    <div id="seccion1">
        <h3 class="form-title">SOLICITUD DE INSCRIPCION AL SEGURO COLECTIVO DE GASTOS MEDICOS Y
        VIDA CONSENTIMIENTO DEL ASEGURADO - CON RESPONSABILIDAD LABORAL
        </h3>
            <div class="row">
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label>Tipo de seguro:</label><br>
                        <?php
                        $selectTipoSeguro = "SELECT ts.TIPO_SEGURO, CONCAT(UCASE(SUBSTRING(ts.NOMBRE, 1, 1)), LOWER(SUBSTRING(ts.NOMBRE, 2))) AS NOMBRE
                        from tipo_seguro ts";
                        $resultTipoSeguro = $conn->query($selectTipoSeguro);

                        if ($resultTipoSeguro->num_rows > 0) {
                            while($row = $resultTipoSeguro->fetch_assoc()) {
                                echo '<div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="tipoSeguro" id="tipoSeguro'.utf8_decode($row["TIPO_SEGURO"]).'" value="'.$row["TIPO_SEGURO"].'" checked disabled>
                                <label class="form-check-label" for="tipoSeguro'.$row["TIPO_SEGURO"].'">'.$row["NOMBRE"].'</label>
                                </div>';
                            }
                        } else {
                            echo "No hay opciones";
                        }

                        $resultTipoSeguro->close();
                        ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="nombreContratante">Nombres del contratante:</label>
                        <input type="text" class="form-control form-control-sm" id="nombreContratante" name="nombreContratante" value="Ibex Honduras" readonly>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="nombreContratante">Apellidos del contratante:</label>
                        <input type="text" class="form-control form-control-sm" id="apellidoContratante" name="apellidoContratante" readonly>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="numeroPoliza">No. de Póliza:</label>
                        <input type="text" class="form-control form-control-sm" id="numeroPoliza" name="numeroPoliza" value="1000019816" readonly>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="categoriaEmpleado">Categoría del Empleado:</label>
                        <input type="text" class="form-control form-control-sm" id="categoriaEmpleado" name="categoriaEmpleado" value="Categoría I" readonly>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="maximoVitalicio">Máximo Vitalicio (Gastos Médicos):</label>
                        <input type="text" class="form-control form-control-sm" id="maximoVitalicio" name="maximoVitalicio" value="*******" readonly>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="sumaAsegurada">Suma Asegurada (Vida):</label>
                        <input type="text" class="form-control form-control-sm" id="sumaAsegurada" name="sumaAsegurada" value="*******" readonly>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="seguroVidaOpcional">Seguro de Vida Opcional:</label>
                        <select class="form-control form-control-sm" id="seguroVidaOpcional" name="seguroVidaOpcional" readonly>
                            <option value="0" selected>No</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="sumaAseguradaOpcional">Suma Asegurada Opcional:</label>
                        <input type="text" class="form-control form-control-sm" id="sumaAseguradaOpcional" name="sumaAseguradaOpcional" placeholder="N/A" readonly>
                        (Pólizas que Aplique)
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-primary btn-sm next-section" id="siguiente1">Siguiente&nbsp;<i class="fa fa-arrow-right fa-sm" aria-hidden="true"></i></button>
    </div>
    <div id="seccion2" style="display: none;">
        <h3 class="form-title">DATOS GENERALES DEL ASEGURADO</h3>
            <div class="row">
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="primerApellidoAsegurado">Primer apellido:</label>
                        <input type="text" class="form-control form-control-sm" id="primerApellidoAsegurado" name="primerApellidoAsegurado" value="">
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
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="primerNombreAsegurado">Primer nombre:</label>
                        <input type="text" class="form-control form-control-sm" id="primerNombreAsegurado" name="primerNombreAsegurado" value="">
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
            </div>
            <div class="row">
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                    <label for="tipoIdentificacion">Tipo de identificación:</label>
                        <select class="form-control form-control-sm" name="tipoIdentificacion" id="tipoIdentificacion">
                        <?php
                        $selectTipoIdentificacion = "SELECT ti.TIPO_IDENTIFICACION, CONCAT(UCASE(SUBSTRING(ti.NOMBRE, 1, 1)), LOWER(SUBSTRING(ti.NOMBRE, 2))) AS NOMBRE
                        from tipo_identificacion ti";
                        $resultTipoIdentificacion = $conn->query($selectTipoIdentificacion);

                        if ($resultTipoIdentificacion->num_rows > 0) {
                            while($row = $resultTipoIdentificacion->fetch_assoc()) {
                                echo '<option value="'.$row["TIPO_IDENTIFICACION"].'">'.$row["NOMBRE"].'</option>';
                            }
                        } else {
                            echo "<option value=''>No hay opciones</option>";
                        }
                        $resultTipoIdentificacion->close();
                        ?>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="numeroIdentificacion">No. identificación:</label>
                        <input type="text" class="form-control form-control-sm" id="numeroIdentificacion" name="numeroIdentificacion" oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, '')">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="nacionalidad">Nacionalidad:</label>
                        <select class="form-control form-control-sm" name="nacionalidad" id="nacionalidad">
                            <option value="Hondureño">Hondureño</option>
                            <option value="Otra">Otra</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="otraNacionalidad">Otra Nacionalidad:</label>
                        <input type="text" class="form-control form-control-sm" id="otraNacionalidad" disabled>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="lugarNacimiento">Lugar de nacimiento:</label>
                        <textarea class="form-control form-control-sm" id="lugarNacimiento" name="lugarNacimiento" rows="3"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="fechaNacimiento">Fecha de nacimiento:</label>
                        <input type="date" class="form-control form-control-sm" id="fechaNacimiento" name="fechaNacimiento" value="">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="edad">Edad:</label>
                        <input type="text" class="form-control form-control-sm" id="edad" name="edad" value="36" readonly>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="sexo">Sexo:</label>
                        <select class="form-control form-control-sm" name="sexo" id="sexo">
                            <option value="">Seleccione...</option>
                            <?php
                            $selectSexo = "SELECT s.SEXO, CONCAT(UCASE(SUBSTRING(s.NOMBRE, 1, 1)), LOWER(SUBSTRING(s.NOMBRE, 2))) AS NOMBRE
                            from sexo s";
                            $resultSexo = $conn->query($selectSexo);

                            if ($resultSexo->num_rows > 0) {
                                while($row = $resultSexo->fetch_assoc()) {
                                    echo '<option value="'.$row["SEXO"].'">'.$row["NOMBRE"].'</option>';
                                }
                            } else {
                                echo "<option value=''>No hay opciones</option>";
                            }
                            $resultSexo->close();
                            ?>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="profesion">Profesión u oficio:</label>
                        <input type="text" class="form-control form-control-sm" id="profesion" name="profesion" value="">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                    <label for="estadoCivil">Estado civil:</label>
                        <select class="form-control form-control-sm" name="estadoCivil" id="estadoCivil">
                        <?php
                        $selectEstadoCivil = "SELECT ec.ESTADO_CIVIL, CONCAT(UCASE(SUBSTRING(ec.NOMBRE, 1, 1)), LOWER(SUBSTRING(ec.NOMBRE, 2))) AS NOMBRE
                        from estado_civil ec";
                        $resultEstadoCivil= $conn->query($selectEstadoCivil);

                        if ($resultEstadoCivil->num_rows > 0) {
                            while($row = $resultEstadoCivil->fetch_assoc()) {
                                echo '<option value="'.$row["ESTADO_CIVIL"].'">'.$row["NOMBRE"].'</option>';
                            }
                        } else {
                            echo "<option value=''>No hay opciones</option>";
                        }
                        $resultEstadoCivil->close();
                        ?>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="apellidoCasadaAsegurado">Apellido casada:</label>
                        <input type="text" class="form-control form-control-sm" id="apellidoCasadaAsegurado" name="apellidoCasadaAsegurado" disabled>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="estatura">Estatura en metros:</label>
                        <input type="number" step="0.01" class="form-control form-control-sm" id="estatura" name="estatura" value="">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="peso">Peso en libras:</label>
                        <input type="number" step="0.01" class="form-control form-control-sm" id="peso" name="peso" value="">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="fuma">Fuma:</label>
                        <select class="form-control form-control-sm" id="fuma" name="fuma">
                            <option value="0">No</option>
                            <option value="1">Si</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="numeroCigarros">¿Cuántos cigarrillos al día?</label>
                        <input type="number" class="form-control form-control-sm" id="numeroCigarros" name="numeroCigarros" disabled>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                    <label for="bebe">¿Ingiere bebidas alcohólicas?</label>
                        <select class="form-control form-control-sm" id="bebe" name="bebe">
                            <option value="0">No</option>
                            <option value="1">Si</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                    <label for="frecuenciaBebe"> ¿Con que frecuencia?</label>
                        <textarea class="form-control form-control-sm" id="frecuenciaBebe" name="frecuenciaBebe" rows="3" disabled></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="cargo">Cargo que desempeña:</label>
                        <input type="text" class="form-control form-control-sm" id="cargo" name="cargo" value="">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="departamentoCompania">Departamento:</label>
                        <input type="text" class="form-control form-control-sm" id="departamentoCompania" name="departamentoCompania" value="">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="fechaIngreso">Fecha de ingreso a la compañia:</label>
                        <input type="date" class="form-control form-control-sm" id="fechaIngreso" name="fechaIngreso" value="">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="sueldo">Sueldo mensual:</label>
                        <input type="number" step="0.01" class="form-control form-control-sm" id="sueldo" name="sueldo" value="">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="sucursal">Sucursal:</label>
                        <input type="text" class="form-control form-control-sm" id="sucursal" name="sucursal" value="">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-sm-7 col-md-4">
                    <div class="form-group">
                        <label for="numeroAfiliacionSeguro">Número de afiliación al Seguro Social:</label>
                        <input type="text" class="form-control form-control-sm" id="numeroAfiliacionSeguro" name="numeroAfiliacionSeguro" readonly>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-secondary btn-sm prev-section" id="anterior1"><i class="fa fa-arrow-left fa-sm" aria-hidden="true"></i>&nbsp;Anterior</button>
                    <button type="button" class="btn btn-primary btn-sm next-section" id="siguiente2">Siguiente&nbsp;<i class="fa fa-arrow-right fa-sm" aria-hidden="true"></i></button>
    </div>
    <div id="seccion3" style="display: none;">
        <h3 class="form-title">DATOS DEL CONYUGE</h3>
            <div class="row">
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="nombreConyuge">Nombres:</label>
                        <input type="text" class="form-control form-control-sm conyugue" id="nombreConyuge" name="nombreConyuge"  oninput="this.value = this.value.replace(/[^a-zA-Z0-9 ]/g, '')" value="Heydi Melissa">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="apellidoConyuge">Apellidos:</label>
                        <input type="text" class="form-control form-control-sm conyugue" id="apellidoConyuge" name="apellidoConyuge" oninput="this.value = this.value.replace(/[^a-zA-Z0-9 ]/g, '')" value="Flores Alonzo">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="empresaConyuge">Empresa donde labora:</label>
                        <input type="text" class="form-control form-control-sm conyugue" id="empresaConyuge" name="empresaConyuge">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="celularConyuge">Celular:</label>
                        <input type="text" class="form-control form-control-sm conyugue" id="celularConyuge" name="celularConyuge" value="98713816">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="emailConyuge">E-mail:</label>
                        <input type="email" class="form-control form-control-sm conyugue" id="emailConyuge" name="emailConyuge" value="melyAlonzo@gmail.com">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-secondary btn-sm prev-section" id="anterior2"><i class="fa fa-arrow-left fa-sm" aria-hidden="true"></i>&nbsp;Anterior</button>
                    <button type="button" class="btn btn-primary btn-sm next-section" id="siguiente3">Siguiente&nbsp;<i class="fa fa-arrow-right fa-sm" aria-hidden="true"></i></button>
    </div>
    <div id="seccion4" style="display: none;">
        <h3 class="form-title">DIRECCION DEL ASEGURADO</h3>
            <div class="row">
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="paisAsegurado">País:</label>
                        <input type="text" class="form-control form-control-sm" id="paisAsegurado" name="paisAsegurado" value="Honduras" readonly>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="departamentoAsegurado">Departamento:</label>
                        <select class="form-control form-control-sm" id="departamentoAsegurado" name="departamentoAsegurado">
                        <?php
                        $selectDepartamentos = "SELECT d.CODIGO, CONCAT(UCASE(SUBSTRING(d.NOMBRE, 1, 1)), LOWER(SUBSTRING(d.NOMBRE, 2))) AS NOMBRE
                        from departamento d";
                        $resultDepartamento = $conn->query($selectDepartamentos);

                        if ($resultDepartamento->num_rows > 0) {
                            while($row = $resultDepartamento->fetch_assoc()) {
                                echo '<option value="'.$row["CODIGO"].'">'.$row["NOMBRE"].'</option>';
                            }
                        } else {
                            echo "<option value=''>No hay opciones</option>";
                        }
                        $resultDepartamento->close();
                        ?>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="ciudadAsegurado">Ciudad / Municipio:</label>
                        <input type="text" class="form-control form-control-sm" id="ciudadAsegurado" name="ciudadAsegurado" value="">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="coloniaAsegurado">Colonia:</label>
                        <input type="text" class="form-control form-control-sm" id="coloniaAsegurado" name="coloniaAsegurado" value="">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="calleAsegurado">Calle:</label>
                        <input type="text" class="form-control form-control-sm" id="calleAsegurado" name="calleAsegurado">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="avenidaAsegurado">Avenida:</label>
                        <input type="text" class="form-control form-control-sm" id="avenidaAsegurado" name="avenidaAsegurado">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="bloqueAsegurado">Bloque:</label>
                        <input type="text" class="form-control form-control-sm" id="bloqueAsegurado" name="bloqueAsegurado" value="">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="casaAsegurado">Casa No.:</label>
                        <input type="text" class="form-control form-control-sm" id="casaAsegurado" name="casaAsegurado" value="">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="telefonoAsegurado">Teléfono:</label>
                        <input type="text" class="form-control form-control-sm" id="telefonoAsegurado" name="telefonoAsegurado" value="">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="celularAsegurado">Celular:</label>
                        <input type="text" class="form-control form-control-sm" id="celularAsegurado" name="celularAsegurado" value="">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="emailAsegurado">E-mail:</label>
                        <input type="text" class="form-control form-control-sm" id="emailAsegurado" name="emailAsegurado" value="">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-secondary btn-sm prev-section" id="anterior3"><i class="fa fa-arrow-left fa-sm" aria-hidden="true"></i>&nbsp;Anterior</button>
                    <button type="button" class="btn btn-primary btn-sm next-section" id="siguiente4">Siguiente&nbsp;<i class="fa fa-arrow-right fa-sm" aria-hidden="true"></i></button>
    </div>
    <div id="seccion5" style="display: none;">
        <h3 class="form-title">BENEFICIARIOS DEL SEGURO DE VIDA</h3>
        <p>Por este medio declaro mi único beneficiario de mi seguro de vida a la empresa contratante que ha suscrito la póliza para la cual he completado esta solicitud, con el
        propósito de cubrir la respondabilidad laboral en base a lo establecido en el Código de Trabajo; Si la suma asegurada contratada supera la obligación laboral del
        contratante de esta póliza; Designo como beneficiario (s) por el remanente de la suma asegurada si existiere a:</p>
            <div class="row">
            <div class="table table-sm table-responsive">
                <table class="table" id="tablaBeniSeg">
                    <thead>
                        <tr>
                            <th>Nombres</th>
                            <th>Apellidos</th>
                            <th>Parentesco</th>
                            <th>Fecha de nacimiento</th>
                            <th>Porcentaje</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $selectBeniSeg = "SELECT p.CANTIDAD 
                        from parametro p 
                        where p.PARAMETRO = 1";
                        $resultBeniSeg = $conn->query($selectBeniSeg);

                        $row = $resultBeniSeg->fetch_assoc();
                        for ($i=1; $i <= $row["CANTIDAD"]; $i++) { 
                            echo '<tr>
                                <td><input type="text" class="form-control form-control-sm nombreBenAse" name="nombreBenAse'.$i.'" id="nombreBenAse'.$i.'"><div class="invalid-feedback"></div></td>
                                <td><input type="text" class="form-control form-control-sm apellidoBenAse" name="apellidoBenAse'.$i.'" id="apellidoBenAse'.$i.'"><div class="invalid-feedback"></div></td>
                                <td><select class="form-control form-control-sm parentescoBenAse" name="parentescoBenAse'.$i.'" id="parentescoBenAse'.$i.'">'.$parentescos.'</select><div class="invalid-feedback"></div></td>
                                <td><input type="date" class="form-control form-control-sm fechaNacimientoBenAse" name="fechaNacimientoBenAse'.$i.'" id="fechaNacimientoBenAse'.$i.'"><div class="invalid-feedback"></div></td>
                                <td><input type="number" step="0.01" class="form-control form-control-sm tblPorcentaje" name="porcentajeBenAse'.$i.'" id="porcentajeBenAse'.$i.'"><div class="invalid-feedback"></div></td>
                                <td><i class="fa fa-percent fa-sm" aria-hidden="true"></i></td>
                                            <td><button type="button" class="btn btn-danger btn-sm btnLimpiar"><i class="fa fa-trash fa-sm" aria-hidden="true"></i></button></td>
                            </tr>';
                        }

                        $resultBeniSeg->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <button type="button" class="btn btn-secondary btn-sm prev-section" id="anterior4"><i class="fa fa-arrow-left fa-sm" aria-hidden="true"></i>&nbsp;Anterior</button>
                    <button type="button" class="btn btn-primary btn-sm next-section" id="siguiente5">Siguiente&nbsp;<i class="fa fa-arrow-right fa-sm" aria-hidden="true"></i></button>  
    </div>
    <div id="seccion6" style="display: none;">
        <h3 class="form-title">BENEFICIARIOS DE CONTINGENCIA</h3>
        <p>En caso de fallecimiento de él (los) beneficiario (s) designado(s) por el remanente de la suma asegurada; si existiere, nombro como beneficiario (s) de contingencia a:</p>
            <div class="row">
                <table class="table table-sm table-responsive" id="tablaBeniConti">
                    <thead>
                        <tr>
                            <th>Nombres</th>
                            <th>Apellidos</th>
                            <th>Parentesco</th>
                            <th>Fecha de nacimiento</th>
                            <th>Porcentaje</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $selectBeniConti = "SELECT p.CANTIDAD 
                        from parametro p 
                        where p.PARAMETRO = 2";
                        $resultBeniConti = $conn->query($selectBeniConti);

                        $row = $resultBeniConti->fetch_assoc();
                        for ($i=1; $i <= $row["CANTIDAD"]; $i++) { 
                            echo '<tr>
                                <td><input type="text" class="form-control form-control-sm nombreBeniConti" name="nombreBeniConti'.$i.'" id="nombreBeniConti'.$i.'"><div class="invalid-feedback"></div></td>
                                <td><input type="text" class="form-control form-control-sm apellidoBeniConti" name="apellidoBeniConti'.$i.'" id="apellidoBeniConti'.$i.'"><div class="invalid-feedback"></div></td>
                                <td><select class="form-control form-control-sm parentescoBeniConti" name="parentescoBeniConti'.$i.'" id="parentescoBeniConti'.$i.'">'.$parentescos.'</select><div class="invalid-feedback"></div>
                                <td><input type="date" class="form-control form-control-sm fechaNacimientoBeniConti" name="fechaNacimientoBeniConti'.$i.'" id="fechaNacimientoBeniConti'.$i.'"><div class="invalid-feedback"></div></td>
                                <td><input type="number" step="0.01" class="form-control form-control-sm tblPorcentaje" name="porcentajeBeniConti'.$i.'" id="porcentajeBeniConti'.$i.'"><div class="invalid-feedback"></div></td>
                                <td><i class="fa fa-percent fa-sm" aria-hidden="true"></i></td>
                                        <td><button type="button" class="btn btn-danger btn-sm btnLimpiar"><i class="fa fa-trash fa-sm" aria-hidden="true"></button></td>
                            </tr>';
                        }

                        $resultBeniConti->close();
                        ?>
                    </tbody>
                </table>
            </div>
            <button type="button" class="btn btn-secondary btn-sm prev-section" id="anterior5"><i class="fa fa-arrow-left fa-sm" aria-hidden="true"></i>&nbsp;Anterior</button>
                    <button type="button" class="btn btn-primary btn-sm next-section" id="siguiente6">Siguiente&nbsp;<i class="fa fa-arrow-right fa-sm" aria-hidden="true"></i></button>
    </div>
    <div id="seccion7" style="display: none;">
        <h3 class="form-title">DEPENDIENTES ECONOMICOS (Cónyuge e hijos) PARA EL PLAN MEDICO HOSPITALARIO Y/O DENTAL (Si aplica)</h3>
            <div class="row">
                <table class="table table-sm table-responsive" id="tablaDependientes">
                    <thead>
                        <tr>
                            <th>Nombres</th>
                            <th>Apellidos</th>
                            <th>Parentesco</th>
                            <th>Fecha de nacimiento</th>
                            <th>Peso (Libras)</th>
                            <th>Estatura (Metros)</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $selectDependientes = "SELECT p.CANTIDAD 
                        from parametro p 
                        where p.PARAMETRO = 3";
                        $resultBeniDependientes = $conn->query($selectDependientes);

                        $row = $resultBeniDependientes->fetch_assoc();
                        for ($i=1; $i <= $row["CANTIDAD"]; $i++) { 
                            echo '<tr>
                                <td><input type="text" class="form-control form-control-sm nombreDependiente" nombre="nombreDependiente" name="nombreDependiente'.$i.'" id="nombreDependiente'.$i.'"><div class="invalid-feedback"></div></td>
                                <td><input type="text" class="form-control form-control-sm apellidoDependiente" nombre="apellidoDependiente" name="apellidoDependiente'.$i.'" id="apellidoDependiente'.$i.'"><div class="invalid-feedback"></div></td>
                                <td><select class="form-control form-control-sm parentescoDependiente" nombre="parentescoDependiente" name="parentescoDependiente'.$i.'" id="parentescoDependiente'.$i.'">'.$parentescosDependiente.'</select><div class="invalid-feedback"></td>
                                <td><input type="date" class="form-control form-control-sm fechaNacimientoDependiente" nombre="fechaNacimientoDependiente" name="fechaNacimientoDependiente'.$i.'" id="fechaNacimientoDependiente'.$i.'"><div class="invalid-feedback"></div></td>
                                <td><input type="number" step="0.01" class="form-control form-control-sm pesoDependiente" nombre="pesoDependiente" name="pesoDependiente'.$i.'" id="pesoDependiente'.$i.'"><div class="invalid-feedback"></div></td>
                                <td><input type="number" step="0.01" class="form-control form-control-sm estaturaDependiente" nombre="estaturaDependiente" name="estaturaDependiente'.$i.'" id="estaturaDependiente'.$i.'"><div class="invalid-feedback"></div></td>
                                <td><button type="button" class="btn btn-danger btn-sm btnLimpiar">Limpiar</button></td>
                            </tr>';
                        }

                        $resultBeniDependientes->close();
                        ?>
                    </tbody>
                </table>
            </div>
            <button type="button" class="btn btn-secondary btn-sm prev-section" id="anterior6"><i class="fa fa-arrow-left fa-sm" aria-hidden="true"></i>&nbsp;Anterior</button>
                    <button type="button" class="btn btn-primary btn-sm next-section" id="siguiente7">Siguiente&nbsp;<i class="fa fa-arrow-right fa-sm" aria-hidden="true"></i></button>
    </div>
    <div id="seccion8" style="display: none;">
        <h3 class="form-title">PREGUNTAS</h3>
        <div class="row">
            <div class="col-auto">
                <table class="table table-sm table-responsive" id="tablaPreguntas">
                    <thead>
                        <tr>
                            <th class="col-5"></th>
                            <th class="col-1">Si o No</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $abecedario = 'abcdefghijklmnopqrstuvwxyz';
                        $selectPreguntas = "SELECT p.PREGUNTA, p.TEXTO, p.SECCION, p.INDICE 
                        from pregunta p
                        where p.seccion in (1,2,3,4,5,6,7)";
                        $resultPreguntas= $conn->query($selectPreguntas);
                        if ($resultPreguntas->num_rows > 0) {
                            while($row = $resultPreguntas->fetch_assoc()) {
                                if ($row["SECCION"] == 1 && $row["INDICE"] == 1) {
                                    echo '<tr seccion="1"><td colspan="3"><strong>Ha tenido alguna vez o tiene usted, su cónyuge o sus hijos; alguna de las enfermedades o trastornos siguientes,(Conteste Si o No). 
                                    Si la respuesta es afirmativa, indique lo solicitado en el cuadro del dialogo que le aparecera.</strong></td></tr>';
                                }elseif ($row["SECCION"] == 6 && $row["INDICE"] == 1) {
                                    echo '<tr seccion="6"><td colspan="4"><strong>Para personas de sexo femenino</strong></td></tr>';
                                }
                                elseif ($row["SECCION"] == 7 && $row["INDICE"] == 1) {
                                    echo '<tr seccion="7"><td colspan="4"><strong>Antecedentes Covid-19</strong></td></tr>';
                                }
                                echo '<tr pregunta="'.$row["PREGUNTA"].'" seccion="'.$row["SECCION"].'" indice="'.$row["INDICE"].'">
                                <td>
                                    <p>'.$abecedario[$row["INDICE"]-1].') '.str_replace("\\n", " ",$row["TEXTO"]).'</p>
                                </td>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input seleccion-radio" type="radio" name="seleccion'.$row["PREGUNTA"].'" id="seleccion'.$row["PREGUNTA"].'N" value="0" checked>
                                        <label class="form-check-label" for="opcion1">No</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input seleccion-radio" type="radio" name="seleccion'.$row["PREGUNTA"].'" id="seleccion'.$row["PREGUNTA"].'S" value="1">
                                        <label class="form-check-label" for="opcion2">Si</label>
                                    </div>
                                </td>
                                <td class="center-vertical"><button type="button" class="btn btn-sm btn-link abrirModalPreguntas" data-toggle="modal" data-target="#modalPreguntas_'.$row["PREGUNTA"].'" id="btnVerPreguntas'.$row["PREGUNTA"].'" disabled>Ver Respuestas</button></td>
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
        <button type="button" class="btn btn-secondary btn-sm prev-section" id="anterior7"><i class="fa fa-arrow-left fa-sm" aria-hidden="true"></i>&nbsp;Anterior</button>
        <button type="button" class="btn btn-primary btn-sm next-section" id="siguiente8">Siguiente&nbsp;<i class="fa fa-arrow-right fa-sm" aria-hidden="true"></i></button>
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
        <input type="hidden" name="hdnBeneficiariosSeguro" id="hdnBeneficiariosSeguro">
        <input type="hidden" name="hdnBeneficiariosContingencia" id="hdnBeneficiariosContingencia">
        <input type="hidden" name="hdnRespuestas" id="hdnRespuestas">
        <input type="hidden" name="hdnRest" id="hdnRest" value="<?= (isset($_GET['rest']) ? $_GET['rest'] : "")?>">
        <button type="button" class="btn btn-secondary btn-sm prev-section" id="anterior8"><i class="fa fa-arrow-left fa-sm" aria-hidden="true"></i>&nbsp;Anterior</button>
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
<!--<script src="js/signature_pad.min.js"></script>-->
<script>
    /*var pad = document.getElementById('signature-pad')
    var signaturePad = new SignaturePad(pad, {
        backgroundColor: 'rgba(255, 255, 255, 0)',
        penColor: 'rgb(0, 0, 0)'
    });*/
    var cancelButton = document.getElementById('clear');

    /*cancelButton.addEventListener('click', function (event) {
        signaturePad.clear();
    });*/

    $(document).ready(function(){
        if ($("#hdnRest").val() == "1") {
            $("#statusSuccessModal").modal('show');
        }
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
                            '<thead><tr><th>Nombres</th><th>Apellidos</th><th>Parentesco</th>' +
                            '<th>Fecha de Nacimiento</th><th>Porcentaje</th></tr></thead><tbody>';
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
                            if (contador === 5 || index === elementos.length - 1){
                                contenido = contenido + '<td></td>'.repeat(5-contador) + '</tr>';
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
                    $("#confirmModal #confirmModalBody").empty().text("¿Desea asegurar a sus dependientes?");
                    $("#statusErrorsModal #confirmModalTitle").empty().text("Dependientes");
                    $('#confirmModal').modal('show');
                }
                else if($(this).attr("id") === "siguiente7"){
                    let idSeccion = $(this).attr("id");
                    $.get("html/modalConfirmSeccion.html", function(data) {
                        let contenido = '<div class="table-responsive"><table class="table table-striped">' +
                            '<thead><tr><th>Nombres</th><th>Apellidos</th><th>Parentesco</th>' +
                            '<th>Fecha de Nacimiento</th><th>Peso (Libras)</th><th>Estatura (Metros)</th></tr></thead><tbody>';
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
                            if (contador === 6 || index === elementos.length - 1){
                                contenido = contenido + '<td></td>'.repeat(6-contador) + '</tr>';
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
                else{
                    $(this).closest('div[id^="seccion"]').hide().next().show();
                    liActivo.next().addClass("active");
                }
            }
        });
        $(".prev-section").click(function() {
            var liActivo = $("#progressbar").find('li.active:last');
            if ($(this).attr("id") === "anterior3" && ($("#estadoCivil").val() != "2" && $("#estadoCivil").val() != "4")) {
                $(this).closest('div[id^="seccion"]').hide().prev().prev().show();
                liActivo.removeClass("active");
                liActivo.prev().removeClass("disabled2");
            }
            else if ($(this).attr("id") === "anterior7" && $("#hdnDependientes").val() === "0") {
                $(this).closest('div[id^="seccion"]').hide().prev().prev().show();
                liActivo.removeClass("active");
                liActivo.prev().removeClass("disabled2");
            }
            else{
                $(this).closest('div[id^="seccion"]').hide().prev().show();
                liActivo.removeClass("active");
            }
        });

        $('#fechaNacimiento').change(function(){
            var fechaNacimiento = $(this).val();
            var fechaActual = new Date();
            var fechaNac = new Date(fechaNacimiento);
            var edad = fechaActual.getFullYear() - fechaNac.getFullYear();

            // Restar un año si aún no se ha cumplido el cumpleaños este año
            if (fechaActual.getMonth() < fechaNac.getMonth() || (fechaActual.getMonth() == fechaNac.getMonth() && fechaActual.getDate() < fechaNac.getDate())) {
                edad--;
            }

            $('#edad').val(edad);
        });

        $('#tablaBeniSeg, #tablaBeniConti, #tablaDependientes , #tablaEnfermedades').on('click', '.btnLimpiar', function(){
            var fila = $(this).closest('tr'); // Obtiene la fila padre del botón clickeado
            fila.find('input, select').val('').removeClass('is-invalid').next('.invalid-feedback').html('');; // Limpia todos los campos de la fila
        });

        $('.seleccion-radio').change(function(){
            var valorSeleccionado = $(this).val();
            var trPregunta = $(this).closest('tr');
            var pregunta = trPregunta.attr("pregunta");
            
            if(valorSeleccionado == "0") {
                $('#modalPreguntas_'+pregunta).remove();
                $(this).closest('tr').find('.abrirModalPreguntas').prop('disabled', true);
            }else {
                var nombreAsegurado = $("#primerNombreAsegurado").val()+" "+$("#segundoNombreAsegurado").val()+" "+$("#primerApellidoAsegurado").val()+" "+$("#segundoApellidoAsegurado").val();
                var optDependientes = "<option value=''>Seleccione...</option><option value=1'>"+nombreAsegurado.replace(/\s+/g, ' ').trim()+"</option>";
                if ($("#hdnDatosDependientes").val() != "") {
                    var hdnDatosDependientes = JSON.parse($("#hdnDatosDependientes").val());
                    $.each(hdnDatosDependientes, function(i,v) {
                        optDependientes = optDependientes + "<option value="+(i+1)+">"+v.nombreDependiente+" "+v.apellidoDependiente+"</option>"
                    });
                }
                var contadorModal = $("form_"+pregunta).length + 1;
                $.ajax({
                    url: 'ajax/consultas.php',
                    type: 'POST',
                    data: {opcion: "getFormulario", a: pregunta, b: optDependientes, c:contadorModal},
                    success: function(resp) {
                        $.get("html/modalPreguntas.html", function(data) {
                            var contenido = data.replace("{{ID}}", pregunta);
                            contenido = contenido.replace("{{PREGUNTAS}}", resp);
                            $("#principal").append(contenido);
                            $('#modalPreguntas_'+pregunta).modal('show');
                        });
                    }
                });
            }
        });

        $("select").change(function(){
            $(this).removeClass('is-invalid').next('.invalid-feedback').html('');

            if ($(this).attr('id') == "estadoCivil" || $(this).attr('id') == "sexo") {
                if ($("#estadoCivil").val() != '2'){
                    $(".conyugue").val("");
                }
                if ($("#estadoCivil").val() == '2' && $("#sexo").val() == '2') {
                    $("#apellidoCasadaAsegurado").prop('disabled', false);
                }else{
                    $("#apellidoCasadaAsegurado").prop('disabled', true);
                    $("#apellidoCasadaAsegurado").val("");
                }
            }
            if ($(this).attr('id') == "nacionalidad") {
                $("#otraNacionalidad").removeClass('is-invalid').next('.invalid-feedback').html('');
                if ($("#nacionalidad").val() == 'Otra') {
                    $("#otraNacionalidad").prop('disabled', false);
                }else{
                    $("#otraNacionalidad").prop('disabled', true);
                    $("#otraNacionalidad").val("");
                }
            }
            if ($(this).attr('id') == "fuma") {
                var opcionSeleccionada = $(this).val();

                if (opcionSeleccionada == '1') {
                    $('#numeroCigarros').prop('disabled', false);
                } else {
                    $('#numeroCigarros').prop('disabled', true).val("");
                    $('#numeroCigarros').removeClass('is-invalid').next('.invalid-feedback').html('');
                }
            }
            if ($(this).attr('id') == "bebe") {
                var opcionSeleccionada = $(this).val();

                if (opcionSeleccionada == '1') {
                    $('#frecuenciaBebe').prop('disabled', false);
                } else {
                    $('#frecuenciaBebe').prop('disabled', true).val("");
                    $('#frecuenciaBebe').removeClass('is-invalid').next('.invalid-feedback').html('');
                }
            }
        });

        $("input, textarea").change(function() {
            $(this).removeClass('is-invalid').next('.invalid-feedback').html('');
            if ($(this).attr('id') == "numeroIdentificacion") {
                $('#numeroAfiliacionSeguro').val($(this).val());
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
                else if ($elemento.hasClass("apellidoBenAse") && $elemento.val() != "") {
                    objTemp["apellidos"] = $elemento.val();
                }
                else if ($elemento.hasClass("parentescoBenAse") && $elemento.val() != "") {
                    objTemp["parentesco"] = $elemento.val();
                }
                else if ($elemento.hasClass("fechaNacimientoBenAse") && $elemento.val() != "") {
                    objTemp["fechaNacimiento"] = $elemento.val();
                }
                else if ($elemento.hasClass("tblPorcentaje") && $elemento.val() != "") {
                    objTemp["porcentaje"] = $elemento.val();
                }
            });

            beneficiariosSeguro.push(objTemp);
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
                else if ($elemento.hasClass("apellidoBeniConti") && $elemento.val() != "") {
                    objTemp["apellidos"] = $elemento.val();
                }
                else if ($elemento.hasClass("parentescoBeniConti") && $elemento.val() != "") {
                    objTemp["parentesco"] = $elemento.val();
                }
                else if ($elemento.hasClass("fechaNacimientoBeniConti") && $elemento.val() != "") {
                    objTemp["fechaNacimiento"] = $elemento.val();
                }
                else if ($elemento.hasClass("tblPorcentaje") && $elemento.val() != "") {
                    objTemp["porcentaje"] = $elemento.val();
                }
            });
            
            beneficiariosContingencia.push(objTemp);
        });
        $("#hdnBeneficiariosContingencia").val(JSON.stringify(beneficiariosContingencia));

        var dependientes = [];
        $('#tablaDependientes tbody tr').each(function(){
            var objTemp = {};
            var $fila = $(this);
            $fila.find('input, select').each(function(){
                var $elemento = $(this);
                if ($elemento.hasClass("nombreDependiente") && $elemento.val() != "") {
                    objTemp["nombres"] = $elemento.val();
                }
                else if ($elemento.hasClass("apellidoDependiente") && $elemento.val() != "") {
                    objTemp["apellidos"] = $elemento.val();
                }
                else if ($elemento.hasClass("parentescoDependiente") && $elemento.val() != "") {
                    objTemp["parentesco"] = $elemento.val();
                }
                else if ($elemento.hasClass("fechaNacimientoDependiente") && $elemento.val() != "") {
                    objTemp["fechaNacimiento"] = $elemento.val();
                }
                else if ($elemento.hasClass("pesoDependiente") && $elemento.val() != "") {
                    objTemp["peso"] = $elemento.val();
                }
                else if ($elemento.hasClass("estaturaDependiente") && $elemento.val() != "") {
                    objTemp["estatura"] = $elemento.val();
                }
            });
            
            dependientes.push(objTemp);
        });
        $("#hdnDatosDependientes").val(JSON.stringify(dependientes));

        var respuestas = [];
        $('#tablaPreguntas tbody tr[pregunta]').each(function(){
            var $pregunta = $(this);
            var numeroPregunta = $pregunta.attr("pregunta"); 
            var seleccion = $pregunta.find('input[type="radio"]:checked').val();
            var objTemp = {id: numeroPregunta, seleccion: seleccion};
            var formularios = [];

            $(".form_"+numeroPregunta).each(function(){
                var $formulario = $(this);
                var tratamientos = [];
                var objTemp2 = [];
                $formulario.find("input, select, textarea").each(function(){
                    var $elemento = $(this);

                    if ($elemento.hasClass("txtCuando")) {
                        objTemp2.push({id: $elemento.attr("subrespuesta"), valor: $elemento.val()});
                    }
                    else if ($elemento.hasClass("txtPorQue")) {
                        objTemp2.push({id: $elemento.attr("subrespuesta"), valor: $elemento.val()});
                    }
                    else if ($elemento.hasClass("txtMeses")) {
                        objTemp2.push({id: $elemento.attr("subrespuesta"), valor: $elemento.val()});
                    }
                    else if ($elemento.hasClass("txtTranscurso")) {
                        objTemp2.push({id: $elemento.attr("subrespuesta"), valor: $elemento.val()});
                    }
                    else if ($elemento.hasClass("txtCiclo")) {
                        objTemp2.push({id: $elemento.attr("subrespuesta"), valor: $elemento.val()});
                    }
                    else if ($elemento.hasClass("txtCitologia")) {
                        objTemp2.push({id: $elemento.attr("subrespuesta"), valor: $elemento.val()});
                    }
                    else if ($elemento.hasClass("txtDonde")) {
                        objTemp2.push({id: $elemento.attr("subrespuesta"), valor: $elemento.val()});
                    }
                    else if ($elemento.hasClass("txtMedicoNombre")) {
                        objTemp2.push({id: $elemento.attr("subrespuesta"), valor: $elemento.val()});
                    }
                    else if ($elemento.hasClass("txtMedicoDireccion")) {
                        objTemp2.push({id: $elemento.attr("subrespuesta"), valor: $elemento.val()});
                    }
                    else if ($elemento.hasClass("txtDuracion")) {
                        objTemp2.push({id: $elemento.attr("subrespuesta"), valor: $elemento.val()});
                    }
                    else if ($elemento.hasClass("txtSecuela")) {
                        objTemp2.push({id: $elemento.attr("subrespuesta"), valor: $elemento.val()});
                    }
                    else if ($elemento.hasClass("cmbDependientes")) {
                        objTemp2.push({id: $elemento.attr("subrespuesta"), valor: $elemento.find("option:selected").text()});
                    }
                    else if ($elemento.hasClass("cmbEnfermedad")) {
                        if ($elemento.find("option:selected").attr("otra") == '1' ) {
                            objTemp2.push({id: $elemento.attr("subrespuesta"), valor: $formulario.find(".otra").val()});    
                        }else{
                            objTemp2.push({id: $elemento.attr("subrespuesta"), valor: $elemento.find("option:selected").text()});
                        }
                    }
                    else if ($elemento.hasClass("tratamiento")) {
                        tratamientos.push($elemento.val());
                    }
                });
                if (tratamientos.length > 0) {
                    objTemp2.push({id: "14", valor: tratamientos});
                }
                formularios.push(objTemp2);
            });
            objTemp["formularios"] = formularios;
            respuestas.push(objTemp);
        });
        $("#hdnRespuestas").val(JSON.stringify(respuestas));
        $formulario.submit();
    }

    function seleccionarForm(elemento) {
        if(validarFormAgregar(elemento)){
            var modalPreguntas = $(elemento).closest('.modal');
            var idPregunta = modalPreguntas.attr("id").split("_")[1];
            var forms = $('.form_'+idPregunta);
            var pageNum = parseInt($(elemento).text());
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
        if(validarFormAgregar(elemento)){
            var modalPreguntas = $(elemento).closest('.modal');
            var idPregunta = modalPreguntas.attr("id").split("_")[1];
            var nombreAsegurado = $("#primerNombreAsegurado").val()+" "+$("#segundoNombreAsegurado").val()+" "+$("#primerApellidoAsegurado").val()+" "+$("#segundoApellidoAsegurado").val();
            var optDependientes = "<option value=''>Seleccione...</option><option value='1'>"+nombreAsegurado.replace(/\s+/g, ' ').trim()+"</option>";
            if ($("#hdnDatosDependientes").val() != "") {
                var hdnDatosDependientes = JSON.parse($("#hdnDatosDependientes").val());
                $.each(hdnDatosDependientes, function(i,v) {
                    optDependientes = optDependientes + "<option value="+(i+1)+">"+v.nombreDependiente+" "+v.apellidoDependiente+"</option>"
                });
            }
            $.ajax({
                url: 'ajax/consultas.php',
                type: 'POST',
                data: {opcion: "getFormulario", a: idPregunta, b: optDependientes},
                success: function(resp) {
                    modalPreguntas.find("form").last().after(resp);

                    var formsPerPage = 1; // Cantidad de formularios por página
                    var forms = $('.form_'+idPregunta);
                    var numForms = forms.length;
                    var numPages = Math.ceil(numForms / formsPerPage);

                    // Generar botones de paginación
                    if (numForms == 2) {
                        for (var i = 1; i <= numPages; i++) {
                            modalPreguntas.find('#pagination').append('<button type="button" class="page-link" onclick="seleccionarForm(this)">' + i + '</button>');
                        }
                    }else{
                        var i = numForms
                        modalPreguntas.find('#pagination').append('<button type="button" class="page-link" onclick="seleccionarForm(this)">' + i + '</button>');
                    }

                    // Mostrar la primera página al cargar
                    if (numForms == 2) {
                        mostrarPagina(2, forms, modalPreguntas);
                    }else{
                        mostrarPagina(numForms, forms, modalPreguntas);
                    }
                }
            });
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
            case 'siguiente1':
                return false;
                break;
            case 'siguiente2':
                elementos = $("#primerApellidoAsegurado, #primerNombreAsegurado, #numeroIdentificacion, #otraNacionalidad, #lugarNacimiento, #fechaNacimiento, #sexo, #profesion, #estatura, #peso, #numeroCigarros, #frecuenciaBebe, #cargo, #sueldo");
                elementos.removeClass('is-invalid').next('.invalid-feedback').html('');
                var camposVacios = false;

                elementos.each(function() {
                    if ($(this).attr("id") == "otraNacionalidad") {
                        if ($("#nacionalidad").val() == "Otra" && $(this).val() == "") {
                            camposVacios = true;
                            $(this).addClass('is-invalid').next('.invalid-feedback').html('Campo requerido.');
                        }
                    }
                    else if ($(this).attr("id") == "numeroCigarros") {
                        if ($("#fuma").val() == "1" && $(this).val() == "") {
                            camposVacios = true;
                            $(this).addClass('is-invalid').next('.invalid-feedback').html('Campo requerido.');
                        }
                    }
                    else if ($(this).attr("id") == "frecuenciaBebe") {
                        if ($("#bebe").val() == "1" && $(this).val() == "") {
                            camposVacios = true;
                            $(this).addClass('is-invalid').next('.invalid-feedback').html('Campo requerido.');
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
            case 'siguiente3':
                elementos = $("#nombreConyuge, #apellidoConyuge, #celularConyuge, #emailConyuge");
                elementos.removeClass('is-invalid').next('.invalid-feedback').html('');
                var camposVacios = false;

                elementos.each(function() {
                    if ($(this).attr("id") == "emailConyuge") {
                        if ($(this).val() != '') {
                            var re = /\S+@\S+\.\S+/;
                            if (!re.test($(this).val())) {
                                camposVacios = true;
                                $(this).addClass('is-invalid').next('.invalid-feedback').html('Formato incorrecto.');
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
                elementos = $("#ciudadAsegurado, #coloniaAsegurado, #casaAsegurado, #emailAsegurado, #celularAsegurado");
                elementos.removeClass('is-invalid').next('.invalid-feedback').html('');
                var camposVacios = false;

                elementos.each(function() {
                    if ($(this).attr("id") == "emailAsegurado") {
                        if ($(this).val() != '') {
                            var re = /\S+@\S+\.\S+/;
                            if (!re.test($(this).val())) {
                                camposVacios = true;
                                $(this).addClass('is-invalid').next('.invalid-feedback').html('Formato incorrecto.');
                            }
                        }else{
                            camposVacios = true;
                            $(this).addClass('is-invalid').next('.invalid-feedback').html('Campo requerido.');
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
                        if ($(this).val() != '') {
                            lleno = true;
                        }
                    });
                    
                    if (lleno) {
                        datos.push($(this));
                    }
                });
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
                    return camposVacios;
                }
                if (datos.length > 0 && porcentaje != 100) {
                    $("#statusErrorsModal .errorMensaje").empty().text("El total de los porcentajes de cada beneficiario debe ser igual al 100%");
                    $("#statusErrorsModal .errorEncabezado").empty().text("Informacion incompleta!");
                    $('#statusErrorsModal').modal('show');
                    return true;
                }
                var liElement = $("ul.pagination li").filter(function() {
                    return $(this).text().trim() === "6";
                });
                if ($("#sexo").val() != "2") {
                    liElement.addClass("disabled").prop("disabled", true);
                }else{
                    liElement.removeClass("disabled").prop("disabled", false);
                }

                return camposVacios;
                break;
            case 'siguiente7':
                var datos = [];
                var hdnDatosDependientes = [];
                var camposVacios = false;
                var porcentaje = 0.00;
                var bloqueo = true;
                
                $('#tablaDependientes tbody tr').each(function(){
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
                $.each(datos,function(index){
                    var objTemp = {id: index};
                    $(this).find('input, select').each(function() {
                        if ($(this).val() == '') {
                            camposVacios = true;
                            $(this).addClass('is-invalid').next('.invalid-feedback').html('Campo requerido.');
                        }else{
                            objTemp[$(this).attr("nombre")] = $(this).val();

                            if ($(this).attr("nombre") == "parentescoDependiente" && $(this).val() == "5" && $("#sexo").val() == "1") {
                                bloqueo = false;
                            }
                        }
                    });
                    if (camposVacios) {
                        bloqueo = true;
                    }else{
                        hdnDatosDependientes.push(objTemp);
                    }
                });
                if ($("#sexo").val() == "2") {
                    bloqueo = false;
                }

                var liElement = $("ul.pagination li").filter(function() {
                    return $(this).text().trim() === "6";
                });
                if (bloqueo) {
                    liElement.addClass("disabled").prop("disabled", true);
                }else{
                    liElement.removeClass("disabled").prop("disabled", false);
                }
                
                if (camposVacios) {
                    $("#statusErrorsModal .errorMensaje").empty().text("Debe ingresar todos los campos requeridos.");
                    $("#statusErrorsModal .errorEncabezado").empty().text("Informacion incompleta!");
                    $('#statusErrorsModal').modal('show');
                    return camposVacios;
                }

                $("#hdnDatosDependientes").val(JSON.stringify(hdnDatosDependientes));
                return camposVacios;
                break;
            default:
            break;
        }
    }

    function pasarPagina(siguiente) {
        let liActivo = $("#progressbar").find('li.active:last');
        let estadosProhibidos = ["2", "4"];
        if (siguiente === "siguiente2"){
            if (!estadosProhibidos.includes($("#estadoCivil").val())) {
                $("#seccion2").hide().next().next().show();
                liActivo.next().next().addClass("active");
                liActivo.next().addClass("disabled2");
            }else{
                $("#seccion2").hide().next().show();
                liActivo.next().addClass("active");
            }
        }
        else if (siguiente === "siguiente5"){
            $("#seccion5").hide().next().show();
            liActivo.next().addClass("active");
        }
        else if (siguiente === "siguiente7"){
            $("#seccion7").hide().next().show();
            liActivo.next().addClass("active");
        }
    }
</script>

</body>
</html>