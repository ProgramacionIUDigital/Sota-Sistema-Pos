<?php
/*=============================================
Traemos columnas
=============================================*/
$url = "columns?linkTo=id_module_column&equalTo=".$module->id_module;
$columns = CurlController::request($url,"GET",array());
$columns = ($columns->status == 200) ? $columns->results : array();

/* NECESARIO PARA EDITAR COLUMNAS */
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
Traemos datos
=============================================*/
$urlTable = $module->title_module."?orderBy=id_".$module->suffix_module."&orderMode=DESC&startAt=".$startAt."&endAt=".$limit;
$table = CurlController::request($urlTable,"GET",array());
$table = ($table->status == 200) ? $table->results : array();

/*=============================================
Total de registros para paginación
=============================================*/
$urlTotal = $module->title_module."?select=id_".$module->suffix_module;
$totalData = CurlController::request($urlTotal,"GET",array())->total;
$totalPages = ceil($totalData / $limit);

/* Caché de relaciones para no llamar la API dos veces por la misma relación */
$cacheRel = array();
?>

<?php if (!empty($routesArray[1]) && $routesArray[1] == "manage"): ?>

    <?php include "manage/manage.php"; ?>

<?php else: ?>

<div class="col-12 mb-3">

    <div class="card rounded p-3 w-100 shadow-sm">

        <div class="card-header bg-white border-0">

            <div class="d-flex align-items-center justify-content-between">

                <div class="d-flex align-items-center gap-2">

                    <a href="/<?php echo $module->url_page ?>/manage"
                        class="btn btn-primary btn-sm">
                        Agregar registro
                    </a>

                    <button type="button"
                        class="btn btn-danger btn-sm btnDeleteCentral">
                        Eliminar seleccionados
                    </button>

                </div>

                <div class="flex-grow-1 d-flex justify-content-center">

                    <div class="position-relative" style="width:260px;">

                        <input type="text"
                            id="smartSearch"
                            class="form-control rounded-pill form-control-sm pe-5"
                            placeholder="Buscar...">

                        <i class="bi bi-search position-absolute"
                            style="right:35px; top:7px; color:#aaa;"></i>

                        <i class="bi bi-x-circle-fill position-absolute"
                            id="clearSearch"
                            style="right:12px; top:7px; cursor:pointer; display:none; color:#dc3545;"></i>

                    </div>

                </div>

                <?php if ($_SESSION["admin"]->rol_admin == "superadmin"): ?>

                    <div class="d-flex align-items-center border rounded bg-white shadow-sm">

                        <button type="button"
                            class="btn btn-sm text-muted myModule"
                            item='<?php echo json_encode($module) ?>'
                            idPage="<?php echo $page->results[0]->id_page ?>">

                            <i class="bi bi-pencil-square"></i>
                            <span class="small fw-bold ms-1">Editar módulo</span>

                        </button>

                        <button type="button"
                            class="btn btn-sm text-muted deleteModule"
                            idModule=<?php echo base64_encode($module->id_module) ?>>

                            <i class="bi bi-trash"></i>

                        </button>

                    </div>

                <?php endif ?>

            </div>

        </div>

        <div class="card-body">

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

                    <thead>
                        <tr>
                            <th>#</th>
                            <th><input type="checkbox" id="checkAll"></th>

                            <?php foreach ($columns as $item): ?>
                                <?php if ($item->visible_column == 1): ?>
                                    <th><?php echo $item->alias_column ?></th>
                                <?php endif ?>
                            <?php endforeach ?>

                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>

                    <tbody id="loadTable">

                        <?php foreach ($table as $key => $value):
                            $value = (array)$value;
                            $idItem = $value["id_".$module->suffix_module];
                        ?>

                            <tr>

                                <td><?php echo ($startAt + $key + 1) ?></td>

                                <td>
                                    <input type="checkbox"
                                        class="checkItem"
                                        value="<?php echo base64_encode($idItem) ?>">
                                </td>

                                <?php foreach ($columns as $item): ?>
                                    <?php if ($item->visible_column == 1): ?>
                                        <td>
                                            <?php
                                            $val = urldecode($value[$item->title_column]);

                                            if($item->type_column == "relations" && $item->matrix_column == "offices"){

                                                if($item->matrix_column != null && $value[$item->title_column] > 0){

                                                    /* Caché: solo llama a la API una vez por tipo de relación */
                                                    $cKeyMeta = $item->matrix_column."_meta";
                                                    if(!isset($cacheRel[$cKeyMeta])){
                                                        $urlRel = "relations?rel=modules,pages&type=module,page&linkTo=type_module,title_module&equalTo=tables,".$item->matrix_column."&select=url_page,suffix_module";
                                                        $resRel = CurlController::request($urlRel,"GET",array());
                                                        if($resRel->status == 200){
                                                            $cacheRel[$cKeyMeta] = array(
                                                                "urlPage"      => $resRel->results[0]->url_page,
                                                                "suffixModule" => $resRel->results[0]->suffix_module
                                                            );
                                                        }
                                                    }

                                                    if(isset($cacheRel[$cKeyMeta])){
                                                        $urlPage      = $cacheRel[$cKeyMeta]["urlPage"];
                                                        $suffixModule = $cacheRel[$cKeyMeta]["suffixModule"];

                                                        $urlData  = $item->matrix_column.'?linkTo=id_'.$suffixModule."&equalTo=".$value[$item->title_column];
                                                        $relation = CurlController::request($urlData,"GET",array());

                                                        if($relation->status == 200){
                                                            $arrayRelation = (array)$relation->results[0];
                                                            echo '<a href="'.$urlPage.'/manage/'.base64_encode($value[$item->title_column]).'" target="_blank" class="badge border rounded bg-indigo text-white">'.urldecode($arrayRelation[array_keys($arrayRelation)[1]]).'</a>';
                                                        } else {
                                                            echo $value[$item->title_column];
                                                        }
                                                    } else {
                                                        echo $value[$item->title_column];
                                                    }
                                                }else{
                                                    echo $value[$item->title_column]; 
                                                }

                                            } else if($item->type_column == "image"){

                                                echo '<img src="'.$val.'" class="rounded shadow-sm" style="width:45px;height:45px;object-fit:cover;">';

                                            } else {

                                                echo TemplateController::reduceText($val,25);

                                            }
                                            ?>
                                        </td>
                                    <?php endif ?>
                                <?php endforeach ?>

                                <td class="text-center">

                                    <button type="button"
                                        class="btn btn-danger btn-sm"
                                        onclick="eliminarAhora('<?php echo base64_encode($idItem) ?>')">
                                        <i class="bi bi-trash"></i>
                                    </button>

                                </td>

                            </tr>

                        <?php endforeach ?>

                    </tbody>
                </table>

            </div>

            <!-- PAGINACIÓN -->
            <?php if($totalPages > 1): ?>
            <div class="d-flex justify-content-between align-items-center mt-3">

                <small>Mostrando
                    <?php echo ($startAt + 1) ?> a
                    <?php echo min($startAt + $limit, $totalData) ?> de
                    <?php echo $totalData ?> registros
                </small>

                <ul class="pagination pagination-sm rounded mb-0">

                    <?php
                    $baseUrl = strtok($_SERVER["REQUEST_URI"], '?');
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

                    <li class="page-item <?php echo ($currentPage==$totalPages)?'disabled':'' ?>">
                        <a class="page-link" href="<?php echo $baseUrl ?>?page=<?php echo $currentPage+1 ?><?php echo $limitParam ?>"><i class="bi bi-chevron-right"></i></a>
                    </li>
                    <li class="page-item <?php echo ($currentPage==$totalPages)?'disabled':'' ?>">
                        <a class="page-link" href="<?php echo $baseUrl ?>?page=<?php echo $totalPages ?><?php echo $limitParam ?>"><i class="bi bi-chevron-double-right"></i></a>
                    </li>

                </ul>

            </div>
            <?php endif ?>

        </div>
    </div>
</div>

<script>
/* ================= SELECTOR LÍMITE ================= */
$("#selectLimit").on("change", function(){
    var limit = $(this).val();
    fncSweetAlert("loading", "Cargando " + limit + " registros...", "");
    var base = "<?php echo strtok($_SERVER['REQUEST_URI'],'?') ?>";
    setTimeout(function(){
        window.location.href = base + "?page=1&limit=" + limit;
    }, 300);
});

/* ================= ALERTA EN PAGINACIÓN ================= */
$(document).on("click", ".pagination .page-link", function(){
    var parent = $(this).closest(".page-item");
    if(parent.hasClass("disabled") || parent.hasClass("active")) return;
    fncSweetAlert("loading", "Cargando registros...", "");
});

/* ================= BUSCAR ================= */
$("#smartSearch").on("keyup", function(){
    let value = $(this).val().toLowerCase();
    $("#loadTable tr").filter(function(){
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
    $("#clearSearch").toggle(value.length > 0);
});

/* LIMPIAR */
$("#clearSearch").on("click", function(){
    $("#smartSearch").val("");
    $(this).hide();
    $("#loadTable tr").show();
});

/* CHECK ALL */
$("#checkAll").on("click", function(){
    $(".checkItem").prop("checked", $(this).prop("checked"));
});

/* ================= ELIMINAR UNO ================= */
function eliminarAhora(id){
    Swal.fire({
        title: '¿Eliminar este registro?',
        text: 'Esta acción es irreversible',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Sí, eliminar'
    }).then((r)=>{
        if(r.isConfirmed){
            var datos = new FormData();
            datos.append("idPageDelete", id);
            datos.append("table", "<?php echo $module->title_module ?>");
            datos.append("column", "id_<?php echo $module->suffix_module ?>");
            datos.append("token", localStorage.getItem("tokenAdmin"));
            $.ajax({
                url:"ajax/pages.ajax.php",
                method:"POST",
                data:datos,
                cache:false,
                contentType:false,
                processData:false,
                success:function(res){
                    if(res.trim()=="200"){
                        location.reload();
                    }else{
                        Swal.fire('Error','No se pudo eliminar','error');
                    }
                }
            });
        }else{
            $(".checkItem").prop("checked", false);
            $("#checkAll").prop("checked", false);
        }
    });
}

/* ================= ELIMINAR MULTIPLE ================= */
$(".btnDeleteCentral").on("click", function(){
    let ids=[];
    $(".checkItem:checked").each(function(){
        ids.push($(this).val());
    });
    if(ids.length==0){
        Swal.fire('Seleccione registros','','warning');
        return;
    }
    Swal.fire({
        title: '¿Eliminar registros seleccionados?',
        text: 'Esta acción es irreversible',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((r)=>{
        if(r.isConfirmed){
            ids.forEach(function(id){
                var datos = new FormData();
                datos.append("idPageDelete", id);
                datos.append("table", "<?php echo $module->title_module ?>");
                datos.append("column", "id_<?php echo $module->suffix_module ?>");
                datos.append("token", localStorage.getItem("tokenAdmin"));
                $.ajax({
                    url:"ajax/pages.ajax.php",
                    method:"POST",
                    data:datos,
                    cache:false,
                    contentType:false,
                    processData:false
                });
            });
            setTimeout(()=>location.reload(),700);
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
