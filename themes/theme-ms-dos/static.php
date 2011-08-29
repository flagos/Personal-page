<?php include(dirname(__FILE__).'/header.php'); ?>

	<div id="section">
			<div id="breadcrumbs">		
					FTP:\><?php $plxShow->mainTitle('link'); ?> <span class="sep">\</span> page <span class="sep">\</span> <?php $plxShow->staticTitle(); ?>\					
            </div> <!-- end #breadcrumbs -->
		<div id="article">

				<h2><?php $plxShow->staticTitle(); ?></h2>
				<div class="static-content"><?php $plxShow->staticContent(); ?></div>

		</div>

		<?php include(dirname(__FILE__).'/sidebar.php'); ?>

	</div>

<?php include(dirname(__FILE__).'/footer.php'); ?>
