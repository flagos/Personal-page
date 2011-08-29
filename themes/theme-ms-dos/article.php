<?php include(dirname(__FILE__).'/header.php'); ?>

	<div id="section">
			<div id="breadcrumbs">		
					$ ><?php $plxShow->mainTitle('link'); ?> <span class="sep">/</span> Blog <span class="sep">/</span> <?php $plxShow->artCat(); ?>  <span class="sep">/</span> 	<?php $plxShow->artTitle(''); ?>/				
			</div> <!-- end #breadcrumbs -->
		<div id="article">

				<h2><?php $plxShow->artTitle(''); ?></h2>
				<div class="art-chapo"><?php $plxShow->artContent(); ?></div>
				<div class="author-infos"><?php $plxShow->artAuthorInfos('#art_authorinfos'); ?></div>
				<p class="art-topinfos"><?php $plxShow->lang('WRITTEN_BY') ?> <?php $plxShow->artAuthor() ?> le <?php $plxShow->artDate('#num_day #month #num_year(4)'); ?></p>
				<p class="art-infos"><?php $plxShow->lang('CLASSIFIED_IN') ?> = <?php $plxShow->artCat(); ?> <br> <?php $plxShow->lang('TAGS') ?> = <?php $plxShow->artTags(); ?></p>
				<?php include(dirname(__FILE__).'/commentaires.php'); ?>
		</div>

		<?php include(dirname(__FILE__).'/sidebar.php'); ?>

	</div>

<?php include(dirname(__FILE__).'/footer.php'); ?>

