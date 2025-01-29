<?php
require('../conexion.php');

$opcion = $_POST["opcion"];
$a = $_POST["a"];
$b = $_POST["b"];

switch ($opcion) {
    case 'getFormulario':
        $numEnfermedades = true;
        $selectEnfermedades = "SELECT f.ENFERMEDAD, CONCAT(UCASE(SUBSTRING(f.NOMBRE, 1, 1)), LOWER(SUBSTRING(f.NOMBRE, 2))) AS NOMBRE, f.OTRA
        from enfermedad f
        where f.pregunta = ?";
        $resultEnfermedades= $conn->prepare($selectEnfermedades);
        $resultEnfermedades->bind_param("s", $a);
        $resultEnfermedades->execute();
        $resultEnfermedades = $resultEnfermedades->get_result();
        $enfermedades = "<option value=''>Seleccione...</option>";
        if ($resultEnfermedades->num_rows > 0) {
            while($row = $resultEnfermedades->fetch_assoc()) {
                $enfermedades .= '<option value="'.$row["ENFERMEDAD"].'" otra="'.$row["OTRA"].'">'.$row["NOMBRE"].'</option>';
            }
        }else{
            $numEnfermedades = false;
        }
        $resultEnfermedades->close();

        $selectPreguntas = "SELECT s.SUBPREGUNTA, s.TEXTO, s.TAG 
        from pregunta p 
        inner join pregunta_subpregunta ps  on p.PREGUNTA = ps.PREGUNTA  
        inner join subpregunta s on ps.SUB_PREGUNTA = s.SUBPREGUNTA 
        where p.PREGUNTA = ? 
        order by s.ORDEN";
        $resultPreguntas = $conn->prepare($selectPreguntas);
        $resultPreguntas->bind_param("s", $a);
        $resultPreguntas->execute();
        $resultPreguntas = $resultPreguntas->get_result();
        $preguntas = [];
        if ($resultPreguntas->num_rows > 0) {
            while($row = $resultPreguntas->fetch_assoc()) {
                $preguntas[$row["SUBPREGUNTA"]] = ["texto"=>$row["TEXTO"], "tag"=>$row["TAG"]];
            }
        }
        $resultPreguntas->close();

        $selectNumTrata = "SELECT p.CANTIDAD 
        from parametro p 
        where p.PARAMETRO = 4";
        $resultNumTrata = $conn->query($selectNumTrata);
        $row = $resultNumTrata->fetch_assoc();
        $resultNumTrata->close();

        $resp = '<form class="form_'.$a.'">';
        foreach ($preguntas as $key => $pregunta) {
            $resp .= '<div class="form-group"><label form-label">'.$pregunta["texto"].':</label>';
            if ($key == 12) {
                $resp .= '<div><select id="'.$pregunta["tag"].'_'.$a.'" subRespuesta="'.$key.'" name="'.$pregunta["tag"].'_'.$a.'" class="form-control form-control-sm '.$pregunta["tag"].'">'.$b.'</select><div class="invalid-feedback"></div></div>';
            }elseif($key == 13) {
                if ($numEnfermedades) {
                    $resp .= '<div><select id="'.$pregunta["tag"].'_'.$a.'" subRespuesta="'.$key.'" name="'.$pregunta["tag"].'_'.$a.'" class="form-control form-control-sm '.$pregunta["tag"].'">'.$enfermedades.'</select><div class="invalid-feedback"></div></div>';
                    $resp .= '<div><input type="text" id="otra_'.$a.'" subRespuesta="'.$key.'" name="otra_'.$a.'" class="form-control form-control-sm otra" style="display: none;" placeholder="Otra"><div class="invalid-feedback"></div></div>';
                }else {
                    $resp .= '<div><input type="text" id="'.$pregunta["tag"].'_'.$a.'" subRespuesta="'.$key.'" name="'.$pregunta["tag"].'_'.$a.'" class="form-control form-control-sm '.$pregunta["tag"].'"><div class="invalid-feedback"></div></div>';
                }
            }elseif ($key == 9) {
                $resp .= '<div><textarea id="'.$pregunta["tag"].'_'.$a.'" name="'.$pregunta["tag"].'_'.$a.'" subRespuesta="'.$key.'" class="form-control form-control-sm '.$pregunta["tag"].'"></textarea><div class="invalid-feedback"></div></div>';
            }elseif ($key == 14) {
                $resp .= '<fieldset class="input-group-vertical">';
                for ($i=1; $i <= $row["CANTIDAD"]; $i++) {
                    $resp .= '<div class="form-group">
                        <label class="sr-only"></label>
                        <input type="text" name="tratamiento_'.$i.'" subRespuesta="'.$key.'" class="form-control form-control-sm tratamiento" placeholder="Tratamiento '.$i.'">
                    </div>';
                }
                $resp .= '</fieldset>';
            }else {
                $resp .= '<div><input type="text" id="'.$pregunta["tag"].'_'.$a.'" name="'.$pregunta["tag"].'_'.$a.'" subRespuesta="'.$key.'" class="form-control form-control-sm '.$pregunta["tag"].'"><div class="invalid-feedback"></div></div>';
            }
            $resp .= "</div>";
        }
        $resp .= "</form>";
        
        echo $resp;
    break;
    
    default:
        # code...
    break;
}
?>