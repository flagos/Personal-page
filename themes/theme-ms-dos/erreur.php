<?php include(dirname(__FILE__).'/header.php'); ?>

	<div id="section">
			<div id="breadcrumbs">		
					$ <?php $plxShow->mainTitle('link'); ?> <span class="sep">/</span> Archives ou Cat&eacute;gorie ou Erreur /
            </div> <!-- end #breadcrumbs -->

		<div id="article">

				<h2><?php $plxShow->lang('ERROR') ?> :</h2>
				<div class="error-content"><?php $plxShow->erreurMessage(); ?></div>

		</div>

		<?php include(dirname(__FILE__).'/sidebar.php'); ?>

	</div>

<?php include(dirname(__FILE__).'/footer.php'); ?>

