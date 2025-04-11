<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
</head>
<body>
<?php


function escribirLog($mensaje, $tipo = 'info') {
    $fecha = date('Y-m-d H:i:s');
    $logFile = 'aplicacion.log'; 
    $mensajeSeguro = htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); 
    $mensajeCompleto = "[{$fecha}] [{$tipo}] {$mensaje}\n";
    file_put_contents($logFile, $mensajeCompleto, FILE_APPEND);
    echo "<script>console.log('{$tipo}: {$mensajeSeguro}')</script>"; 
}

$usuario = $_POST["usu"];
$clave = $_POST["clave"];


if (strpos($usuario, "'") !== false || strpos($usuario, "#") !== false || strpos($clave, "'") !== false || strpos($clave, "#") !== false) {
    escribirLog("Posible intento de inyección SQL detectado. Usuario: {$usuario}, Clave: {$clave}", 'warning');
    echo "<br><b>Se han detectado caracteres sospechosos en el usuario o la contraseña. Por seguridad, la operación se ha detenido.</b><br>\n";
    exit(); 
}


$conexion = mysqli_connect("localhost", "root", "");
if (!$conexion) {
    escribirLog("ERROR: Imposible establecer conexión con la base de datos.", 'error');
    echo "ERROR: Imposible establecer conexión con la base de datos para ese usuario y esa clave.<br>\n";
} else {
    escribirLog("Conexión con la base de datos establecida correctamente.");
    echo "Conexion con la base de datos establecida correctamente...<br><br>\n";
}


$db = mysqli_select_db($conexion, "ejemplo");
if (!$db) {
    escribirLog("ERROR: Imposible seleccionar la base de datos 'ejemplo'. Error: " . mysqli_error($conexion), 'error');
    echo "ERROR: Imposible seleccionar la base de datos.<br>\n";
} else {
    escribirLog("Base de datos 'ejemplo' seleccionada satisfactoriamente.");
    echo "Base de datos seleccionada satisfactoriamente...<br><br>\n";
}

if ($db) {
    $sql = "SELECT * FROM acceso WHERE login='$usuario' AND clave=md5('$clave')";
    $resul = mysqli_query($conexion, $sql);


    if (!$resul) {
        escribirLog("ERROR: Imposible realizar la consulta SQL: " . mysqli_error($conexion) . ". SQL: " . $sql, 'error');
        echo "ERROR: Imposible realizar consulta.<br>\n";
    } else {
        escribirLog("Consulta SQL realizada satisfactoriamente. SQL: " . $sql);
        echo "Consulta realizada satisfactoriamente!<br>\n";

        echo "Se encontraron " . mysqli_num_rows($resul) . " registros.<br>";

        if (mysqli_num_rows($resul) == 0) {
            escribirLog("Intento de inicio de sesión fallido. Usuario: {$usuario}", 'warning');
            echo "<br><b>Usuario y/o clave incorrectos!.<br></b>\n";
        } else {
            escribirLog("Inicio de sesión exitoso. Usuario: {$usuario}", 'info');
            echo "<br>REGISTROS ENCONTRADOS:<br>\n";
          
            while ($fila = mysqli_fetch_row($resul)) {
                echo "<b>USUARIO:</b>$fila[0] <b>CLAVE:</b>$fila[1] <b>NOMBRE:</b>$fila[2] <b>HAS CONSEGUIDO ENTRAR EN LA PAGINA WEB!</b><br>";
            }
        }
    }
}

mysqli_close($conexion);

?>
</body>
</html>