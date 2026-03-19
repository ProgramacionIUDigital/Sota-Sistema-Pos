<?php

/*=============================================
Traemos columnas de la tabla
=============================================*/
$url = "columns?linkTo=id_module_column&equalTo=".$module->id_module;
$method = "GET";
$fields = array();

$columns = CurlController::request($url,$method,$fields);
if($columns->status == 200){
    $columns = $columns->results;
}else{
    $columns = array();
}
$module->columns = $columns;

/*=============================================
Paginación
=============================================*/
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
if(!in_array($limit, [10,25,50,100])) $limit = 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($currentPage < 1) $currentPage = 1;
$startAt = ($currentPage - 1) * $limit;

/*=============================================
Traemos contenido de la tabla
=============================================*/
$totalPages = 0;
$totalData  = 0;
$url   = $module->title_module."?orderBy=id_".$module->suffix_module."&orderMode=DESC&startAt=".$startAt."&endAt=".$limit;
$table = CurlController::request($url,$method,$fields);

if($table->status == 200){
    $table = $table->results;
    $url2     = $module->title_module."?select=id_".$module->suffix_module;
    $resTotal = CurlController::request($url2,$method,$fields);
    $totalData  = isset($resTotal->total) ? (int)$resTotal->total : 0;
    $totalPages = ($limit > 0 && $totalData > 0) ? ceil($totalData/$limit) : 0;
}else{
    $table = array();
}

$totalColumns = 3;
?>

<?php if (!empty($routesArray[1]) && $routesArray[1] == "manage"): ?>

    <?php
    include "manage/manage.php";
    include "views/modules/modals/files.php";
    ?>

<?php else: ?>

<div class="col-12 mb-3">

    <div class="card rounded p-3 w-100 shadow-sm">

        <div class="card-header bg-white border-0">
            <div class="d-flex align-items-center justify-content-between gap-3" style="height:38px;">

                <div class="d-flex align-items-center gap-2 flex-shrink-0 h-100">
                    <a href="/<?php echo $module->url_page ?>/manage" class="btn btn-primary btn-sm h-100 d-flex align-items-center">
                        Agregar registro
                    </a>
                    <button type="button" class="btn btn-danger btn-sm btnDeleteCentral h-100 d-flex align-items-center">
                        Eliminar seleccionados
                    </button>
                </div>

                <div class="flex-grow-1 d-flex justify-content-center align-items-center h-100">
                    <div class="position-relative w-100" style="max-width:300px;">
                        <input type="text" id="smartSearch"
                            class="form-control rounded-pill form-control-sm pe-5 ps-3"
                            placeholder="Buscar..."
                            style="height:38px;">
                        <i class="bi bi-search position-absolute"
                            style="right:36px; top:50%; transform:translateY(-50%); color:#aaa; pointer-events:none;"></i>
                        <i class="bi bi-x-circle-fill position-absolute" id="clearSearch"
                            style="right:12px; top:50%; transform:translateY(-50%); cursor:pointer; display:none; color:#dc3545;"></i>
                    </div>
                </div>

                <?php if ($_SESSION["admin"]->rol_admin == "superadmin"): ?>
                    <div class="d-flex align-items-center border rounded bg-white shadow-sm">
                        <button type="button" class="btn btn-sm text-muted myModule"
                            item='<?php echo json_encode($module) ?>'
                            idPage="<?php echo $page->results[0]->id_page ?>">
                            <i class="bi bi-pencil-square"></i>
                            <span class="small fw-bold ms-1">Editar módulo</span>
                        </button>
                        <button type="button" class="btn btn-sm text-muted deleteModule"
                            idModule=<?php echo base64_encode($module->id_module) ?>>
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                <?php endif ?>

            </div>
        </div>

        <div class="card-body">

            <input type="hidden" id="contentModule"  value='<?php echo json_encode($module) ?>'>
            <input type="hidden" id="orderByTable"   value="id_<?php echo $module->suffix_module ?>">
            <input type="hidden" id="orderModeTable" value="DESC">
            <input type="hidden" id="limitTable"     value="<?php echo $limit ?>">
            <input type="hidden" id="pageTable"      value="<?php echo $currentPage ?>">
            <input type="hidden" id="rolAdmin"       value="<?php echo $_SESSION["admin"]->rol_admin ?>">
            <input type="hidden" id="searchTable"    value="">
            <input type="hidden" id="between1"       value="<?php echo date("Y-m-d", 0) ?>">
            <input type="hidden" id="between2"       value="<?php echo date("Y-m-d") ?>">
            <input type="hidden" id="checkItems"     value="" table="<?php echo $module->title_module ?>" suffix="<?php echo $module->suffix_module ?>">

            <div class="d-flex align-items-center gap-2 mb-3">
                <small>Mostrar</small>
                <select id="selectLimit" class="form-select form-select-sm rounded" style="width:65px">
                    <?php
                    $selectedLimit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
                    foreach([10,25,50,100] as $opt){
                        $sel = ($selectedLimit == $opt) ? 'selected' : '';
                        echo '<option value="'.$opt.'" '.$sel.'>'.$opt.'</option>';
                    }
                    ?>
                </select>
                <small>Registros</small>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <style>thead tr th { position: sticky; top: 0; background-color: #ffffff !important; z-index: 2; border-bottom: 2px solid #dee2e6; }</style>
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-center"><input type="checkbox" id="checkAll"></th>

                            <?php foreach ($columns as $item): ?>
                                <?php if ($item->visible_column == 1): ?>
                                    <th class="text-capitalize"><?php echo $item->alias_column ?></th>
                                <?php endif ?>
                            <?php endforeach ?>

                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>

                    <tbody id="loadTable">

                        <?php if (!empty($table)): ?>
                            <?php foreach (json_decode(json_encode($table),true) as $key => $value): ?>

                            <tr>
                                <td><?php echo ($startAt + $key + 1) ?></td>

                                <td class="text-center">
                                    <input type="checkbox" class="checkItem"
                                        value="<?php echo base64_encode($value["id_".$module->suffix_module]) ?>">
                                </td>

                                <?php foreach ($columns as $index => $item): ?>
                                    <?php if ($item->visible_column == 1): ?>
                                    <td>
                                    <?php

                                    /*== IMAGEN ==*/
                                    if($item->type_column == "image"){
                                        echo '<a href="'.urldecode($value[$item->title_column]).'" target="_blank">
                                            <img src="'.urldecode($value[$item->title_column]).'" class="rounded" style="width:60px;height:60px;object-fit:cover;object-position:center;">
                                        </a>';

                                    /*== VIDEO ==*/
                                    }else if($item->type_column == "video"){
                                        echo '<a href="'.urldecode($value[$item->title_column]).'" target="_blank">
                                            <img src="/views/assets/img/video.png" class="rounded" style="width:60px;height:60px;object-fit:cover;">
                                        </a>';

                                    /*== ARCHIVO ==*/
                                    }else if($item->type_column == "file"){
                                        echo '<a href="'.urldecode($value[$item->title_column]).'" target="_blank">
                                            <img src="/views/assets/img/file.png" class="rounded" style="width:60px;height:60px;object-fit:cover;">
                                        </a>';

                                    /*== BOOLEANO ==*/
                                    }else if($item->type_column == "boolean"){
                                        if($value[$item->title_column] == 1){ $checked='checked'; $label="ON"; }
                                        else{ $checked=''; $label="OFF"; }
                                        if($_SESSION["admin"]->rol_admin == "superadmin" || $module->editable_module == 1){
                                            echo '<div class="form-check form-switch">
                                                <input class="form-check-input px-3 changeBoolean" type="checkbox" id="mySwtich" '.$checked.'
                                                    idItem="'.base64_encode($value["id_".$module->suffix_module]).'"
                                                    table="'.$module->title_module.'"
                                                    suffix="'.$module->suffix_module.'"
                                                    column="'.$item->title_column.'">
                                                <label class="form-check-label ps-1 align-middle" for="mySwitch">'.$label.'</label>
                                            </div>';
                                        }else{
                                            echo '<label class="form-check-label ps-1 align-middle" for="mySwitch">'.$label.'</label>';
                                        }

                                    /*== ARRAY ==*/
                                    }else if($item->type_column == "array"){
                                        $typeArray = explode(",",urldecode($value[$item->title_column]));
                                        foreach($typeArray as $num => $elem){
                                            echo '<span class="badge badge-sm badge-default rounded bg-dark py-1 px-2 mx-1 mt-1 border small">'.TemplateController::reduceText($elem,25).'</span>';
                                        }

                                    /*== OBJETO ==*/
                                    }else if($item->type_column == "object"){
                                        $typeJSON = json_decode(urldecode($value[$item->title_column]));
                                        foreach($typeJSON as $num => $elem){
                                            echo '<span class="badge badge-sm badge-default rounded py-1 px-2 mx-1 mt-1 border text-dark text-uppercase small">'.$num.': '.$elem.'</span>';
                                        }

                                    /*== ENLACE ==*/
                                    }else if($item->type_column == "link"){
                                        echo '<a href="'.$value[$item->title_column].'" target="_blank" class="badge badge-default border rounded bg-indigo">'.TemplateController::reduceText(urldecode($value[$item->title_column]),20).'</a>';

                                    /*== COLOR ==*/
                                    }else if($item->type_column == "color"){
                                        echo '<div class="rounded" style="width:25px;height:25px;background:'.urldecode($value[$item->title_column]).'"></div>';

                                    /*== DINERO ==*/
                                    }else if($item->type_column == "money"){
                                        $num = (float)str_replace(',', '.', urldecode($value[$item->title_column]));
                                        echo '<span style="white-space:nowrap;">$ '.number_format($num, 2, ',', '.').'</span>';

                                    /*== RELACIONES — lógica exacta del profesor ==*/
                                    }else if($item->type_column == "relations"){
                                        if($item->matrix_column != null && $value[$item->title_column] > 0){
                                            $url = "relations?rel=modules,pages&type=module,page&linkTo=type_module,title_module&equalTo=tables,".$item->matrix_column."&select=url_page,suffix_module";
                                            $method = "GET";
                                            $array = array();
                                            $urlPage    = CurlController::request($url,$method,$fields)->results[0]->url_page;
                                            $suffixMod  = CurlController::request($url,$method,$fields)->results[0]->suffix_module;
                                            $url        = $item->matrix_column.'?linkTo=id_'.$suffixMod."&equalTo=".$value[$item->title_column]."&select=*";
                                            $relation   = CurlController::request($url,$method,$fields);
                                            if($relation->status == 200 && !empty($relation->results)){
                                                $arrayRel = (array)$relation->results[0];
                                                $keysRel  = array_keys($arrayRel);
                                                $labelRel = isset($keysRel[1]) ? urldecode($arrayRel[$keysRel[1]]) : $value[$item->title_column];
                                                echo '<a href="'.$urlPage.'/manage/'.base64_encode($value[$item->title_column]).'" target="_blank" class="badge badge-default border rounded bg-indigo">'.$labelRel.'</a>';
                                            }else{
                                                echo $value[$item->title_column];
                                            }
                                        }else{
                                            echo $value[$item->title_column];
                                        }

                                    /*== ORDEN ==*/
                                    }else if($item->type_column == "order"){
                                        echo '<input type="number" class="form-control form-control-sm rounded changeOrder" value="'.$value[$item->title_column].'" style="width:55px"
                                            idItem="'.base64_encode($value["id_".$module->suffix_module]).'"
                                            table="'.$module->title_module.'"
                                            suffix="'.$module->suffix_module.'"
                                            column="'.$item->title_column.'">';

                                    /*== STOCK entero ==*/
                                    }else if($item->title_column == "stock_product"){
                                        echo (int)urldecode($value[$item->title_column]);

                                    /*== TEXTO GENÉRICO ==*/
                                    }else{
                                        echo TemplateController::reduceText(urldecode($value[$item->title_column]),25);
                                    }
                                    ?>
                                    </td>
                                    <?php endif ?>
                                <?php endforeach ?>

                                <td class="text-center">
                                    <button type="button" class="btn btn-danger btn-sm"
                                        onclick="eliminarAhora('<?php echo base64_encode($value["id_".$module->suffix_module]) ?>')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>

                            </tr>

                            <?php endforeach ?>

                        <?php else: ?>
                            <tr>
                                <td colspan="<?php echo $totalColumns ?>" class="text-center py-3">No hay registros disponibles</td>
                            </tr>
                        <?php endif ?>

                    </tbody>
                </table>
            </div>

            <?php if($totalPages > 1): ?>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <small>Mostrando
                    <?php echo ($totalData == 0) ? 0 : ($startAt + 1) ?> a
                    <?php echo min($startAt + $limit, $totalData) ?> de
                    <?php echo $totalData ?> registros
                </small>
                <ul class="pagination pagination-sm rounded mb-0">
                    <?php
                    $baseUrl    = strtok($_SERVER["REQUEST_URI"], '?');
                    $limitParam = $limit != 10 ? '&limit='.$limit : '';
                    ?>
                    <li class="page-item <?php echo ($currentPage==1)?'disabled':'' ?>">
                        <a class="page-link" href="<?php echo $baseUrl ?>?page=1<?php echo $limitParam ?>"><i class="bi bi-chevron-double-left"></i></a>
                    </li>
                    <li class="page-item <?php echo ($currentPage==1)?'disabled':'' ?>">
                        <a class="page-link" href="<?php echo $baseUrl ?>?page=<?php echo $currentPage-1 ?><?php echo $limitParam ?>"><i class="bi bi-chevron-left"></i></a>
                    </li>
                    <?php for($i=1; $i<=$totalPages; $i++): ?>
                        <?php if($i==1 || $i==$totalPages || ($i>=$currentPage-2 && $i<=$currentPage+2)): ?>
                            <li class="page-item <?php echo ($i==$currentPage)?'active':'' ?>">
                                <a class="page-link" href="<?php echo $baseUrl ?>?page=<?php echo $i ?><?php echo $limitParam ?>"><?php echo $i ?></a>
                            </li>
                        <?php elseif($i==$currentPage-3 || $i==$currentPage+3): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif ?>
                    <?php endfor ?>
                    <li class="page-item <?php echo ($currentPage>=$totalPages)?'disabled':'' ?>">
                        <a class="page-link" href="<?php echo $baseUrl ?>?page=<?php echo $currentPage+1 ?><?php echo $limitParam ?>"><i class="bi bi-chevron-right"></i></a>
                    </li>
                    <li class="page-item <?php echo ($currentPage>=$totalPages)?'disabled':'' ?>">
                        <a class="page-link" href="<?php echo $baseUrl ?>?page=<?php echo $totalPages ?><?php echo $limitParam ?>"><i class="bi bi-chevron-double-right"></i></a>
                    </li>
                </ul>
            </div>
            <?php endif ?>

        </div>
    </div>
</div>

<?php
include "views/modules/modals/booleans.php";
include "views/modules/modals/selects.php";
?>

<script>
/* ================= SELECTOR LÍMITE ================= */
$("#selectLimit").on("change", function(){
    var base = "<?php echo strtok($_SERVER['REQUEST_URI'],'?') ?>";
    window.location.href = base + "?page=1&limit=" + $(this).val();
});

/* ================= ORDENAMIENTO AJAX ================= */
$(document).on("click", ".orderFilter", function(){
    var orderBy   = $(this).attr("orderBy");
    var orderMode = $(this).attr("orderMode");
    var nextMode  = (orderMode == "ASC") ? "DESC" : "ASC";
    $(".orderFilter").attr("orderMode", "ASC");
    $(this).attr("orderMode", nextMode);
    var datos = new FormData();
    datos.append("contentModule", $("#contentModule").val());
    datos.append("orderBy",       orderBy);
    datos.append("orderMode",     nextMode);
    datos.append("limit",         $("#limitTable").val());
    datos.append("page",          $("#pageTable").val());
    datos.append("rolAdmin",      $("#rolAdmin").val());
    datos.append("search",        $("#searchTable").val());
    datos.append("between1",      $("#between1").val());
    datos.append("between2",      $("#between2").val());
    datos.append("token",         localStorage.getItem("tokenAdmin"));
    $.ajax({
        url: "ajax/dynamic-tables.ajax.php", method: "POST", data: datos,
        cache: false, contentType: false, processData: false,
        success: function(res){
            var r = JSON.parse(res);
            if(r.HTMLTable !== undefined){ $("#loadTable").html(r.HTMLTable); }
        }
    });
});

/* ================= BÚSQUEDA INTELIGENTE ================= */
$("#smartSearch").on("keyup", function(){
    let value = $(this).val().toLowerCase();
    $("#loadTable tr").filter(function(){
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
    });
    $("#clearSearch").toggle(value.length > 0);
});
$("#clearSearch").on("click", function(){
    $("#smartSearch").val(""); $(this).hide(); $("#loadTable tr").show();
});

/* ================= CHECK ALL ================= */
$("#checkAll").on("click", function(){
    $(".checkItem").prop("checked", $(this).prop("checked"));
});

/* ================= ELIMINAR UNO ================= */
function eliminarAhora(id){
    Swal.fire({
        title: '¿Eliminar este registro?', text: 'Esta acción es irreversible', icon: 'warning',
        showCancelButton: true, confirmButtonColor: '#dc3545',
        cancelButtonText: 'Cancelar', confirmButtonText: 'Sí, eliminar'
    }).then((r) => {
        if(r.isConfirmed){
            var datos = new FormData();
            datos.append("idPageDelete", id);
            datos.append("table",  "<?php echo $module->title_module ?>");
            datos.append("column", "id_<?php echo $module->suffix_module ?>");
            datos.append("token",  localStorage.getItem("tokenAdmin"));
            $.ajax({
                url: "ajax/pages.ajax.php", method: "POST", data: datos,
                cache: false, contentType: false, processData: false,
                success: function(res){
                    if(res.trim() == "200"){ location.reload(); }
                    else{ Swal.fire('Error','No se pudo eliminar','error'); }
                }
            });
        }else{
            $(".checkItem").prop("checked", false);
            $("#checkAll").prop("checked", false);
        }
    });
}

/* ================= ELIMINAR MÚLTIPLE ================= */
$(".btnDeleteCentral").on("click", function(){
    let ids = [];
    $(".checkItem:checked").each(function(){ ids.push($(this).val()); });
    if(ids.length == 0){ Swal.fire('Seleccione registros','','warning'); return; }
    Swal.fire({
        title: '¿Eliminar registros seleccionados?', text: 'Esta acción es irreversible', icon: 'warning',
        showCancelButton: true, confirmButtonColor: '#dc3545',
        confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar'
    }).then((r) => {
        if(r.isConfirmed){
            ids.forEach(function(id){
                var datos = new FormData();
                datos.append("idPageDelete", id);
                datos.append("table",  "<?php echo $module->title_module ?>");
                datos.append("column", "id_<?php echo $module->suffix_module ?>");
                datos.append("token",  localStorage.getItem("tokenAdmin"));
                $.ajax({ url: "ajax/pages.ajax.php", method: "POST", data: datos,
                    cache: false, contentType: false, processData: false });
            });
            setTimeout(() => location.reload(), 700);
        }else{
            $(".checkItem").prop("checked", false);
            $("#checkAll").prop("checked", false);
        }
    });
});
</script>

<?php endif ?>

<?php
if (!empty($routesArray[1]) && $routesArray[1] == "manage"){
    include "views/modules/modals/files.php";
}
?>
