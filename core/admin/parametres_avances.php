<?php

/**
 * Edition des paramètres avancés
 *
 * @package PLX
 * @author	Florent MONTHEL, Stephane F
 **/

include(dirname(__FILE__).'/prepend.php');

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# On édite la configuration
if(!empty($_POST)) {
	$plxAdmin->editConfiguration($plxAdmin->aConf,$_POST);
	header('Location: parametres_avances.php');
	exit;
}

# On inclut le header
include(dirname(__FILE__).'/top.php');
?>

<h2><?php echo L_CONFIG_ADVANCED_DESC ?></h2>

<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsAdvancedTop')) # Hook Plugins ?>

<form action="parametres_avances.php" method="post" id="form_settings">
	<fieldset class="config">
		<p class="field"><label for="id_urlrewriting"><?php echo L_CONFIG_ADVANCED_URL_REWRITE ?>&nbsp;:</label></p>
		<?php if(plxUtils::testModRewrite(false)) : ?>
			<?php plxUtils::printSelect('urlrewriting',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['urlrewriting']);?>
			<?php if(is_file(PLX_ROOT.'.htaccess') AND $plxAdmin->aConf['urlrewriting']==0) { ?>
				&nbsp;<?php echo L_CONFIG_ADVANCED_URL_REWRITE_ALERT ?>
			<?php } ?>
		<?php else: ?>
			<?php echo L_MODREWRITE_NOT_AVAILABLE ?>
		<?php endif; ?>
		<p class="field"><label for="id_gzip"><?php echo L_CONFIG_ADVANCED_GZIP ?>&nbsp;:</label></p>
		<?php plxUtils::printSelect('gzip',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['gzip']);?>
		<a class="help" title="<?php echo L_CONFIG_ADVANCED_GZIP_HELP ?>">&nbsp;</a>
		<p class="field"><label for="id_capcha"><?php echo L_CONFIG_ADVANCED_CAPCHA ?>&nbsp;:</label></p>
		<?php plxUtils::printSelect('capcha',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['capcha']);?>
		<p class="field"><label for="id_userfolders"><?php echo L_CONFIG_ADVANCED_USERFOLDERS ?>&nbsp;:</label></p>
		<?php plxUtils::printSelect('userfolders',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['userfolders']);?>
		<p class="field"><label for="id_clef"><?php echo L_CONFIG_ADVANCED_ADMIN_KEY ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('clef', $plxAdmin->aConf['clef'], 'text', '30-30'); ?>
		<a class="help" title="<?php echo L_CONFIG_ADVANCED_KEY_HELP ?>">&nbsp;</a>
	</fieldset>
	<fieldset class="config">
		<p class="field"><label for="id_images"><?php echo L_CONFIG_ADVANCED_PICS_FOLDER ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('images', $plxAdmin->aConf['images']); ?>
		<a class="help" title="<?php echo L_HELP_SLASH_END ?>">&nbsp;</a>
		<p class="field"><label for="id_documents"><?php echo L_CONFIG_ADVANCED_DOCS_FOLDER ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('documents', $plxAdmin->aConf['documents']); ?>
		<a class="help" title="<?php echo L_HELP_SLASH_END ?>">&nbsp;</a>
		<p class="field"><label for="id_racine_articles"><?php echo L_CONFIG_ADVANCED_ARTS_FOLDER ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('racine_articles', $plxAdmin->aConf['racine_articles']); ?>
		<a class="help" title="<?php echo L_HELP_SLASH_END ?>">&nbsp;</a>
		<p class="field"><label for="id_racine_commentaires"><?php echo L_CONFIG_ADVANCED_COMS_FOLDER ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('racine_commentaires', $plxAdmin->aConf['racine_commentaires']); ?>
		<a class="help" title="<?php echo L_HELP_SLASH_END ?>">&nbsp;</a>
		<p class="field"><label for="id_racine_statiques"><?php echo L_CONFIG_ADVANCED_STATS_FOLDER ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('racine_statiques', $plxAdmin->aConf['racine_statiques']); ?>
		<a class="help" title="<?php echo L_HELP_SLASH_END ?>">&nbsp;</a>
		<p class="field"><label for="id_categories"><?php echo L_CONFIG_ADVANCED_CATS_FILE ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('categories', $plxAdmin->aConf['categories']); ?>
		<p class="field"><label for="id_statiques"><?php echo L_CONFIG_ADVANCED_STATICS_FILE ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('statiques', $plxAdmin->aConf['statiques']); ?>
		<p class="field"><label for="id_users"><?php echo L_CONFIG_ADVANCED_USERS_FILE ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('users', $plxAdmin->aConf['users']); ?>
		<p class="field"><label for="id_tags"><?php echo L_CONFIG_ADVANCED_TAGS_FILE ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('tags', $plxAdmin->aConf['tags']); ?>
		<p class="field"><label for="id_plugins"><?php echo L_CONFIG_ADVANCED_PLUGINS_FILE ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('plugins', $plxAdmin->aConf['plugins']); ?>
	</fieldset>
	<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsAdvanced')) ?>
	<p class="center">
		<?php echo plxToken::getTokenPostMethod() ?>
		<input class="button update" type="submit" value="<?php echo L_CONFIG_ADVANCED_UPDATE ?>" />
	</p>
</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminSettingsAdvancedFoot'));
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>