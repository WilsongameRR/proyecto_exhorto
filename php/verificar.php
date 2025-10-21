<?php
include 'conexion.php';
session_start(); 

// URL por defecto si falla el login
$strUrl = '../index.php?error=invalid';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $txtUsr = trim($_POST["txtUsr"] ?? "");
    $txtPwd = trim($_POST["txtPwd"] ?? "");

    if ($txtUsr !== "" && $txtPwd !== "") {

        $strSql = "SELECT user_id, username, nomcompleto, access_control, fk_idarea, fk_id_tua, password 
                   FROM users 
                   WHERE username = ? AND password = ?";
        $stmt = $con->prepare($strSql);
        $stmt->bind_param("ss", $txtUsr, $txtPwd);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();

            // Guardar datos en sesión
            $_SESSION["userid"]        = $row["user_id"];
            $_SESSION["username"]      = $row["username"];
            $_SESSION["nomcompleto"]   = $row["nomcompleto"];
            $_SESSION["access_control"]= $row["access_control"];
            $_SESSION["area"]          = $row["fk_idarea"];
            $_SESSION["fk_id_tua"]     = $row["fk_id_tua"];

            // Redirigir a bienvenida
            $strUrl = '../bienvenida.php';
        } else {
            // Credenciales incorrectas
            $strUrl = '../index.php?error=incorrecto';
        }

        $stmt->close();
    } else {
        // Campos vacíos
        $strUrl = '../index.php?error=campos_vacios';
    }
}

// Redirección final
header("Location: $strUrl");
exit();
?>
