<?php include(dirname(__FILE__).'/header.php'); ?>

	<div id="section">
<div id="breadcrumbs">		
					FTP:\><?php $plxShow->mainTitle('link'); ?> <span class="sep">\</span> <?php $plxShow->pageBlog('<a class="#page_status" href="#page_url" title="#page_name">#page_name</a>'); ?>					
            </div> <!-- #breadcrumbs Fin -->
		<div id="article">

			<?php while($plxShow->plxMotor->plxRecord_arts->loop()): ?>
				<h2><?php $plxShow->artTitle('link'); ?></h2>
				<div class="art-chapo"><?php $plxShow->artChapo(); ?></div>
				<p class="art-topinfos"><?php $plxShow->lang('WRITTEN_BY') ?> <?php $plxShow->artAuthor() ?> le <?php $plxShow->artDate('#num_day #month #num_year(4)'); ?></p>
				<p class="art-infos"><?php $plxShow->lang('CLASSIFIED_IN') ?> = <?php $plxShow->artCat(); ?><br><?php $plxShow->lang('TAGS') ?> = <?php $plxShow->artTags(); ?><br><?php $plxShow->artNbCom(); ?></p>
			<?php endwhile; ?>

			<p id="pagination"><?php $plxShow->pagination(); ?></p>

		</div>

		<?php include(dirname(__FILE__).'/sidebar.php'); ?>

	</div>

<?php include(dirname(__FILE__).'/footer.php'); ?>
