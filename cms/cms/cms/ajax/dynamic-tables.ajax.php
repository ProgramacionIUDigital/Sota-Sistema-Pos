<?php

require_once "../controllers/curl.controller.php";
require_once "../controllers/template.controller.php";

class DynamicTablesController{

    public $contentModule;
    public $orderBy;
    public $orderMode;
    public $limit;
    public $page;
    public $rolAdmin;
    public $search;
    public $between1;
    public $between2;
    public $token;

    public function loadAjaxTable(){

        $module = json_decode($this->contentModule);
        $startAt = ($this->page - 1) * $this->limit;
        
        /*=============================================
        Detección de Columna de Búsqueda (Fallback)
        =============================================*/
        // Si no hay columnas de texto, usaremos por defecto el título del módulo
        $linkTo = "title_".$module->suffix_module; 

        foreach ($module->columns as $key => $value) {
            if($value->visible_column == 1 && $value->type_column == "text"){
                $linkTo = $value->title_column;
                break; // Usamos la primera columna de texto para asegurar compatibilidad
            }
        }

        /*=============================================
        Construcción de URL
        =============================================*/
        if($this->search != ""){
            // Búsqueda simple en una sola columna para evitar errores 500
            $url = $module->title_module."?linkTo=".$linkTo."&search=".str_replace(" ", "_", $this->search)."&orderBy=".$this->orderBy."&orderMode=".$this->orderMode."&startAt=".$startAt."&endAt=".$this->limit."&select=*";
        }else{
            // Carga por fecha
            $url = $module->title_module."?linkTo=date_created_".$module->suffix_module."&between1=".$this->between1."&between2=".$this->between2."&orderBy=".$this->orderBy."&orderMode=".$this->orderMode."&startAt=".$startAt."&endAt=".$this->limit."&select=*";
        }

        $response = CurlController::request($url, "GET", array());

        $table = array();
        $totalData = 0;
        $totalPages = 0;

        if($response->status == 200){
            $table = $response->results;
            $totalData = $response->total;
            $totalPages = ceil($totalData / $this->limit);
        }

        /*=============================================
        Generación de HTML Simple
        =============================================*/
        $HTMLTable = "";

        if(!empty($table)){
            foreach($table as $key => $value){
                $value = (array)$value;
                $HTMLTable .= '<tr>';
                $HTMLTable .= '<td>'.($key+1+$startAt).'</td>';

                // Checkbox para edición
                if ($this->rolAdmin == "superadmin" || $module->editable_module == 1){
                    $HTMLTable .= '<td><input type="checkbox" class="checkItem" idItem="'.base64_encode($value["id_".$module->suffix_module]).'"></td>';
                }

                // Columnas dinámicas
                foreach ($module->columns as $item){
                    if ($item->visible_column == 1){
                        $HTMLTable .= '<td>';
                        if($item->type_column == "image"){
                            $HTMLTable .= '<img src="'.urldecode($value[$item->title_column]).'" width="40" class="rounded">';
                        } else {
                            $HTMLTable .= TemplateController::reduceText(urldecode($value[$item->title_column]), 25);
                        }
                        $HTMLTable .= '</td>';
                    }
                }

                // Botones de acción
                $HTMLTable .= '<td>
                    <a href="/'.$module->url_page.'/manage/'.base64_encode($value["id_".$module->suffix_module]).'" class="btn btn-sm text-primary p-0"><i class="bi bi-pencil"></i></a>
                </td>';
                
                $HTMLTable .= '</tr>';
            }
        }

        echo json_encode(array(
            "HTMLTable" => $HTMLTable,
            "totalData" => (int)$totalData,
            "totalPages" => (int)$totalPages
        ));
    }
}

/*--- Disparador ---*/
if(isset($_POST["contentModule"])){
    $ajax = new DynamicTablesController();
    $ajax->contentModule = $_POST["contentModule"];
    $ajax->orderBy = $_POST["orderBy"];
    $ajax->orderMode = $_POST["orderMode"];
    $ajax->limit = $_POST["limit"];
    $ajax->page = $_POST["page"];
    $ajax->rolAdmin = $_POST["rolAdmin"];
    $ajax->search = $_POST["search"];
    $ajax->between1 = $_POST["between1"];
    $ajax->between2 = $_POST["between2"];
    $ajax->loadAjaxTable();
}