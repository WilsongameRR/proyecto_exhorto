<html>
<head>
    <title>Login</title>                       
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="bootstrap/css/bootstrap-theme.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/estiloslogin.css">
    <script src="js/jquery.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>

    <style>
        .alerta-login {
            max-width: 400px;
            margin: 20px auto;
            border-radius: 8px;
            text-align: center;
            font-weight: 500;
        }
    </style>
</head>
<body class="hold-transition login-page">

<?php
if (isset($_GET["error"])) {
    $mensaje = "";
    $clase = "alert-danger";

    if ($_GET["error"] === "incorrecto") {
        $mensaje = "❌ Usuario o contraseña incorrectos.";
    } elseif ($_GET["error"] === "campos_vacios") {
        $mensaje = "⚠️ Por favor, llena todos los campos.";
    } elseif ($_GET["error"] === "invalid") {
        $mensaje = "⚠️ Acceso denegado. Inicia sesión nuevamente.";
    }

    if ($mensaje !== "") {
        echo "
        <div id='alertaLogin' class='alert $clase alerta-login'>
            $mensaje
        </div>";
    }
}
?>

<div class="login-box">
    <div>
        <img src="images/tas.png" width="350">
    </div>

    <div class="login-logo">
        <a><b>SISTEMA</b></a> 
    </div>

    <div class="login-box-body">
        <p class="login-box-msg">Ingresa tu Usuario y Contrase&ntilde;a para Iniciar Sesi&oacute;n</p>

        <form action="php/verificar.php" method="POST">
            <div class="form-group has-feedback">
                <input id="txtUsr" name="txtUsr" type="text" class="form-control" placeholder="Usuario" required>
                <span class="glyphicon glyphicon-user form-control-feedback"></span>
            </div>

            <div class="form-group has-feedback">
                <input id="txtPwd" name="txtPwd" type="password" class="form-control" placeholder="Contrase&ntilde;a" required>
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>

            <div class="row">
                <div class="col-xs-4 col-xs-offset-4">
                    <button type="submit" name="btnLogin" class="btn btn-primary btn-block btn-flat">
                        Ingresar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- 🔔 Ocultar automáticamente la alerta y limpiar URL -->
<script>
$(document).ready(function(){
    // Desvanece el mensaje después de 3.5 segundos
    setTimeout(function(){
        $("#alertaLogin").fadeOut(600);
    }, 3500);

    // Limpia la URL para que al refrescar no aparezca el mensaje otra vez
    if (window.history.replaceState) {
        const url = window.location.href.split('?')[0];
        window.history.replaceState(null, null, url);
    }
});
</script>

</body>
</html>
