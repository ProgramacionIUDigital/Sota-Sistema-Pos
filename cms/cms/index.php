<?php

require_once "controllers/template.controller.php";
require_once "controllers/curl.controller.php"; // <--- AGREGA ESTA LÍNEA

$index = new TemplateController();
$index -> index();