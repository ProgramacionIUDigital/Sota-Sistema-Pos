<?php 
/*=============================================
LÓGICA INICIAL (NO TOCADA)
=============================================*/
ob_start();
session_start();

$routesArray = explode("/", $_SERVER["REQUEST_URI"]);
array_shift($routesArray);

foreach ($routesArray as $key => $value) {
	$routesArray[$key] = explode("?",$value)[0];
}

$url = "admins";
$method = "GET";
$fields = array();
$adminTable = CurlController::request($url,$method,$fields);

if($adminTable->status == 404){
	$admin = null;
}else{
	$admin = $adminTable->results[0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" href="https://cdn-icons-png.flaticon.com/512/9966/9966194.png">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

	<?php if (!empty($admin)): ?>
		<title><?php echo $admin->title_admin ?></title>
		<?php if ($admin->font_admin != null): ?>
			<?php echo $admin->font_admin ?>
		<?php endif ?>

		<style>
			<?php if ($admin->font_admin != null):?>
				body{ font-family: <?php echo str_replace("+"," ",explode("=",explode(":",explode("?",$admin->font_admin)[1])[0])[1]) ?>, sans-serif !important; }
			<?php endif ?>
			.backColor{ background: <?php echo $admin->color_admin ?> !important; color: #FFF !important; border: 0 !important; }
			.form-check-input:checked{ background-color: <?php echo $admin->color_admin ?> !important; border-color: <?php echo $admin->color_admin ?> !important; }
			.textColor{ color: <?php echo $admin->color_admin ?> !important; }
			.page-item.active .page-link { z-index: 3; color: #fff !important; background-color: <?php echo $admin->color_admin ?> !important; border-color: <?php echo $admin->color_admin ?> !important; }
			.page-link { color: <?php echo $admin->color_admin ?> !important; }
		</style>
	<?php else: ?>
		<title>CMS Builder</title>
	<?php endif ?>

	<link rel="stylesheet" href="/views/assets/plugins/bootstrap5/bootstrap.min.css" >
	<link rel="stylesheet" href="/views/assets/plugins/fontawesome-free/css/all.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.3/font/bootstrap-icons.min.css">
	<link rel="stylesheet" href="/views/assets/plugins/material-preloader/material-preloader.css">
	<link rel="stylesheet" href="/views/assets/plugins/toastr/toastr.min.css">
	<link rel="stylesheet" href="/views/assets/plugins/daterangepicker/daterangepicker.css">
	<link rel="stylesheet" href="/views/assets/plugins/tags-input/tags-input.css">
	<link rel="stylesheet" href="/views/assets/plugins/select2/select2.min.css">
    <link rel="stylesheet" href="/views/assets/plugins/select2/select2-bootstrap4.min.css">
    <link rel="stylesheet" href="/views/assets/plugins/datetimepicker/datetimepicker.min.css">
    <link rel="stylesheet" href="/views/assets/plugins/summernote/summernote-bs4.min.css"> 
    <link rel="stylesheet" href="/views/assets/plugins/summernote/summernote.min.css">
    <link rel="stylesheet" href="/views/assets/plugins/summernote/emoji.css">
    <link rel="stylesheet" href="/views/assets/plugins/codemirror/codemirror.css">
	<link rel="stylesheet" href="/views/assets/plugins/codemirror/monokai.css">

	<link rel="stylesheet" href="/views/assets/css/custom/custom.css">
	<link rel="stylesheet" href="/views/assets/css/dashboard/dashboard.css">
	<link rel="stylesheet" href="/views/assets/css/colors/colors.css">
	<link rel="stylesheet" href="/views/assets/css/fms/fms.css">

	<script src="/views/assets/plugins/jquery/jquery.min.js"></script>
	<script src="/views/assets/plugins/jquery-ui/jquery-ui.min.js"></script>
	<script src="/views/assets/plugins/bootstrap5/bootstrap.bundle.min.js"></script>
	<script src="/views/assets/plugins/sweetalert/sweetalert.min.js"></script> 
	<script src="/views/assets/plugins/material-preloader/material-preloader.js"></script> 
	<script src="/views/assets/plugins/toastr/toastr.min.js"></script>
	<script src="/views/assets/plugins/twbs-pagination/twbs-pagination.min.js"></script> 
	<script src="/views/assets/plugins/moment/moment.min.js"></script>
	<script src="/views/assets/plugins/moment/moment-with-locales.min.js"></script>
	<script src="/views/assets/plugins/daterangepicker/daterangepicker.js"></script> 
	<script src="/views/assets/plugins/tags-input/tags-input.js"></script> 
	<script src="/views/assets/plugins/select2/select2.full.min.js"></script>
	<script src="/views/assets/plugins/datetimepicker/datetimepicker.full.min.js"></script>
	<script src="/views/assets/plugins/summernote/summernote.min.js"></script>
	<script src="/views/assets/plugins/summernote/summernote-bs4.js"></script>
    <script src="/views/assets/plugins/summernote/summernote-code-beautify-plugin.js"></script>
	<script src="/views/assets/plugins/summernote/emoji.config.js"></script>
	<script src="/views/assets/plugins/summernote/tam-emoji.min.js"></script>
	<script src="/views/assets/plugins/codemirror/codemirror.js"></script>
	<script src="/views/assets/plugins/codemirror/xml.js"></script>
	<script src="/views/assets/plugins/codemirror/formatting.js"></script>
	<script src="/views/assets/plugins/chartjs/chartjs.min.js"></script>
    <script src="/views/assets/js/alerts/alerts.js"></script>
</head>

<body>
	<?php 
	if(!isset($_SESSION["admin"])){
		if($admin == null){
			include "pages/install/install.php";
		}else{
			include "pages/login/login.php";
		}
	}
	?>

	<?php if (isset($_SESSION["admin"])): ?>
		<div class="d-flex backDashboard" id="wrapper">
			<?php include "modules/sidebar.php" ?>
			<div id="page-content-wrapper">
				<?php include "modules/nav.php" ?>

				<?php if (!empty($routesArray[0])): ?>
					<?php if ($routesArray[0] == "logout"): ?>
						<?php include "pages/".$routesArray[0]."/".$routesArray[0].".php"; ?>
					<?php else: ?>
						<?php 
                        $permissions = json_decode(urldecode($_SESSION["admin"]->permissions_admin), true);
                        if ($_SESSION["admin"]->rol_admin == "superadmin" || $_SESSION["admin"]->rol_admin == "admin" || ($_SESSION["admin"]->rol_admin == "editor" && isset($permissions[$routesArray[0]]) && $permissions[$routesArray[0]] == "on")): ?>

							<?php 
								$url = "pages?linkTo=url_page&equalTo=".$routesArray[0];
								$page = CurlController::request($url,"GET",array());
								
								if($page->status == 200 && $page->results[0]->type_page == "modules"){
									include "pages/dynamic/dynamic.php";
								}else if($page->status == 200 && $page->results[0]->type_page == "custom"){
									include "pages/custom/".$routesArray[0]."/".$routesArray[0].".php";
								}else{
									include "pages/404/404.php";
								}
							?>
						<?php else: ?>
							<?php include "pages/404/404.php"; ?>
						<?php endif ?>
					<?php endif ?>
				<?php else: ?>
					<?php 
                        // Carga de página inicial por orden
						$url = "pages?linkTo=order_page&equalTo=1";
						$page = CurlController::request($url,"GET",array());
						if($page->status == 200){
							if($page->results[0]->type_page == "modules") include "pages/dynamic/dynamic.php";
							else include "pages/custom/".$page->results[0]->url_page."/".$page->results[0]->url_page.".php";
						}
					?>
				<?php endif ?>
			</div>
		</div>

		<?php 
		include "modules/modals/profile.php"; 
		require_once "controllers/admins.controller.php";
		$update = new AdminsController();
	    $update->updateAdmin();

	    if($_SESSION["admin"]->rol_admin == "superadmin"){
		    include "views/modules/modals/pages.php";
		    require_once "controllers/pages.controller.php";
			$managePage = new PagesController();
		    $managePage->managePage();

		    include "views/modules/modals/modules.php";
		    require_once "controllers/modules.controller.php";
			$manageModule = new ModulesController();
			$manageModule->manageModule();
		}
		?>

		<script src="/views/assets/js/dashboard/dashboard.js"></script>
		<script src="/views/assets/js/pages/pages.js"></script>
		<script src="/views/assets/js/modules/modules.js"></script>
		<script src="/views/assets/js/dynamic-forms/dynamic-forms.js"></script>
		<script src="/views/assets/js/dynamic-tables/dynamic-tables.js"></script>
		<script src="/views/assets/js/fms/fms.js"></script>
        <script src="/views/assets/js/purchase/purchase.js"></script>
	<?php endif ?>

	<script src="/views/assets/js/forms/forms.js"></script>
</body>
</html>