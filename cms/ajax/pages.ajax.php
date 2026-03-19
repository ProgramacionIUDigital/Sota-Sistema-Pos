<?php 

require_once "../controllers/curl.controller.php";

class PagesAjax{

    public $token; 
    public $table;    // Variable dinámica para la tabla
    public $column;   // Variable dinámica para la columna ID
    public $idPageDelete;

    /*=============================================
    Eliminar Registro (Páginas o Tablas Dinámicas)
    =============================================*/ 
    public function deletePage(){

        // Si se envió una tabla desde el JS, la usamos. Si no, por defecto es 'pages'
        $tableDelete = (isset($this->table) && !empty($this->table)) ? $this->table : "pages";
        $columnDelete = (isset($this->column) && !empty($this->column)) ? $this->column : "id_page";

        /*=============================================
        Validación de seguridad para Páginas
        =============================================*/
        if($tableDelete == "pages"){
            $url = "modules?linkTo=id_page_module&equalTo=".base64_decode($this->idPageDelete);
            $getModule = CurlController::request($url,"GET",array());

            if($getModule->status == 200){
                echo "error_modules"; 
                return;
            }
        }

        /*=============================================
        Petición DELETE física a la Base de Datos
        =============================================*/
        // Aquí construimos la URL usando las variables que vienen de tu tables.php
        $url = $tableDelete."?id=".base64_decode($this->idPageDelete)."&nameId=".$columnDelete."&token=".$this->token."&table=admins&suffix=admin";
        $method = "DELETE";
        $fields = array();

        $delete = CurlController::request($url,$method,$fields);

        if($delete->status == 200){
            echo "200"; // Éxito
        }else{
            echo "Error API: " . $delete->status;
        }
    }

    /*=============================================
    Cambiar el orden de página (Mantener funcionalidad original)
    =============================================*/ 
    public $idPage;
    public $index; 

    public function updatePageOrder(){
        $url = "pages?id=".base64_decode($this->idPage)."&nameId=id_page&token=".$this->token."&table=admins&suffix=admin";
        $method = "PUT";
        $fields = "order_page=".$this->index;
        $updateOrder = CurlController::request($url,$method,$fields);
        if($updateOrder->status == 200){
            echo $updateOrder->status;
        }
    }
}

/*=============================================
Recibir peticiones POST
=============================================*/
if(isset($_POST["idPage"])){
    $ajax = new PagesAjax();
    $ajax -> idPage = $_POST["idPage"];
    $ajax -> index = $_POST["index"];
    $ajax -> token = $_POST["token"];
    $ajax -> updatePageOrder();
}

if(isset($_POST["idPageDelete"])){
    $ajax = new PagesAjax();
    $ajax -> idPageDelete = $_POST["idPageDelete"];
    $ajax -> token = $_POST["token"];
    // Capturamos lo que envías desde el onclick de tables.php
    $ajax -> table = isset($_POST["table"]) ? $_POST["table"] : null;
    $ajax -> column = isset($_POST["column"]) ? $_POST["column"] : null;
    $ajax -> deletePage();
}