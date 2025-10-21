<?php
/**
 * ============================================================
 * HEADER HTML CENTRALIZADO
 * Tribunal Superior Agrario - Sistema de Exhortos (UTICS)
 * Autor: Ing. Raúl Pérez Rangel
 * ============================================================
 */
if (!function_exists("mostrarHeader")) {
    function mostrarHeader($titulo = "Sistema de Exhortos") {
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title><?= htmlspecialchars($titulo) ?></title>

            <!-- Estilos base -->
            <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
            <link href="bootstrap/css/bootstrap-theme.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

            <!-- Scripts globales -->
            <script src="js/jquery.min.js"></script>
            <script src="bootstrap/js/bootstrap.min.js"></script>

            <style>
                body {
                    background: #f7f8fa;
                    font-family: "Segoe UI", Roboto, sans-serif;
                    color: #2c3e50;
                }
                h2, h3 {
                    color: #004085;
                    font-weight: 600;
                }
                .btn-nuevo {
                    background-color: #004085;
                    color: #fff;
                    font-weight: 500;
                    border: none;
                    border-radius: 50px;
                    padding: 12px 28px;
                    font-size: 16px;
                    transition: all 0.3s ease;
                    box-shadow: 0 3px 8px rgba(0,0,0,0.15);
                }
                .btn-nuevo:hover {
                    background-color: #01366d;
                    transform: translateY(-2px);
                    box-shadow: 0 5px 10px rgba(0,0,0,0.25);
                    color: #fff;
                }
                .btn-ver {
                    color: #004085;
                    border: 1px solid #004085;
                    border-radius: 6px;
                    background: #fff;
                    padding: 6px 10px;
                    transition: all 0.2s ease;
                }
                .btn-ver:hover {
                    background: #004085;
                    color: #fff;
                    transform: scale(1.05);
                }
                footer {
                    text-align: center;
                    margin-top: 40px;
                    color: #666;
                    font-size: 13px;
                }
            </style>
        </head>
        <?php
    }
}
?>
