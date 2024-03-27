<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create User</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
      background-image: linear-gradient(133.97deg, #3239A0 -1.22%, #121945 80.66%), linear-gradient(0deg, #FFFFFF, #FFFFFF);
      height:100vh;
      box-sizing: content-box;
    }

    /* Contenedor principal */
    .container {
      max-width: 600px;
      margin: 0px auto;
      background-color: rgba(255,255,255,0.03);
      border-radius: 10px;
      padding-top:10px;
      height:250px;
      padding-top: 50px;
      padding-bottom: 50px;
    }

    /* Encabezado */
    .header {
      text-align: center;
    }

    .header h1 {
      color: #ff9102;
      font: bold 20px Arial, sans-serif;
    }

    /* Contenido */
    .content {
      color: #000;
      padding:0 60px;
      text-align:center;
      font: bold 20px Arial, sans-serif;
    }

    /* Botón */
    .btn {
      display: inline-block;
      padding: 10px 80px;
      background-color: #007bff;
      color: #fff;
      text-decoration: none;
      border-radius: 5px;
    }

    .btn:hover {
      background-color: #0056b3;
    }

    .click {
      margin-top:-10px;
    }

    .parrafo-click {
      color:#fff;
      font-weight:bold;
    }
  </style>
</head>
<body style="background-image: linear-gradient(133.97deg, #ffffff -1.22%, #ffffff 80.66%), linear-gradient(0deg, #FFFFFF, #FFFFFF);">
  
  <table width="100%" cellspacing="0" cellpadding="0">
    <tr>
        <td style="background-color: #fff; height: 120px;">
            <img src="{{ asset('img/logo.svg') }}" alt="logo" style="width: 120px; height: 120px; margin-left: auto; margin-right: auto; display: block;">
        </td>
    </tr>
</table>

  <table cellspacing="0" cellpadding="0" class="container">
    <tr>
      <td>
        <div class="header">
            <h1>¡El usuario ha sido creado!</h1>
        </div>
        <div class="content">
            <p>¡Solo falta la aprobación del Administrador!</p>
          </div>
        </div>
      </td>
    </tr>
  </table>

  <table width="100%" cellspacing="0" cellpadding="0">
    <tr>
        <td style="background-color: #ff9102; height: 60px;margin-top: 50px">
        </td>
    </tr>
</table>
</body>
</html>