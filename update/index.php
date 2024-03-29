<?php
# ------------------ BEGIN LICENSE BLOCK ------------------
#
# This file is part of PluXml : http://pluxml.org
#
# Copyright (c) 2010-2011 Stephane Ferrari and contributors
# Copyright (c) 2008-2009 Florent MONTHEL and contributors
# Copyright (c) 2006-2008 Anthony GUERIN
# Licensed under the GPL license.
# See http://www.gnu.org/licenses/gpl.html
#
# ------------------- END LICENSE BLOCK -------------------

define('PLX_ROOT', '../');
define('PLX_CORE', PLX_ROOT.'core/');
define('PLX_PLUGINS', PLX_ROOT.'plugins/');
define('PLX_CONF', PLX_ROOT.'data/configuration/parametres.xml');
define('PLX_UPDATER', true);

# On inclut les librairies nécessaires
include(PLX_ROOT.'config.php');

# Chargement des langues
$lang = DEFAULT_LANG;
if(isset($_POST['default_lang'])) $lang=$_POST['default_lang'];
loadLang(PLX_CORE.'lang/'.$lang.'/core.php');
loadLang(PLX_CORE.'lang/'.$lang.'/update.php');

# On vérifie que PHP 5 ou superieur soit installé
if(version_compare(PHP_VERSION, '5.0.0', '<')){
    header('Content-Type: text/plain charset=UTF-8');
    echo L_WRONG_PHP_VERSION;
    exit;
}

include(PLX_CORE.'lib/class.plx.date.php');
include(PLX_CORE.'lib/class.plx.glob.php');
include(PLX_CORE.'lib/class.plx.utils.php');
include(PLX_CORE.'lib/class.plx.msg.php');
include(PLX_CORE.'lib/class.plx.record.php');
include(PLX_CORE.'lib/class.plx.motor.php');
include(PLX_CORE.'lib/class.plx.admin.php');
include(PLX_CORE.'lib/class.plx.encrypt.php');
include(PLX_CORE.'lib/class.plx.plugins.php');
include(PLX_CORE.'lib/class.plx.token.php');
include(PLX_ROOT.'update/class.plx.updater.php');

# Echappement des caractères
if($_SERVER['REQUEST_METHOD'] == 'POST') {
	$_POST = plxUtils::unSlash($_POST);
}

# Création de l'objet principal et lancement du traitement
$plxUpdater = new plxUpdater();

foreach($plxUpdater->updateList as $num_version => $infos) {
	if($num_version!=$plxUpdater->newVersion) {
		$versions[$num_version] = 'PluXml '.$num_version;
	}
}
?>
<?php
plxUtils::cleanHeaders();
session_start();
# Control du token du formulaire
plxToken::validateFormToken($_POST);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $lang ?>" lang="<?php echo $lang ?>">
<head>
	<meta name="robots" content="noindex, nofollow" />
	<title><?php echo L_UPDATE_TITLE.' '.$plxUpdater->newVersion ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo strtolower(PLX_CHARSET) ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo PLX_CORE ?>admin/theme/base.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="<?php echo PLX_CORE ?>admin/theme/style.css" media="screen" />
</head>

<body>

<div id="main">
	<div id="top">
		<div style="margin-left:200px"><h2><?php echo L_UPDATE_TITLE.' '.$plxUpdater->newVersion ?></h2></div>
	</div>
	<div id="content">
	<?php if(empty($_POST['submit'])) : ?>
		<?php if($plxUpdater->oldVersion==$plxUpdater->newVersion) : ?>
		<div class="panel" style="padding:10px 10px 10px 10px">
			<p><strong><?php echo L_UPDATE_UPTODATE ?></strong></p>
			<p><?php echo L_UPDATE_NOT_AVAILABLE ?></p>
			<p><a href="<?php echo PLX_ROOT; ?>" title="<?php echo L_UPDATE_BACK ?>"><?php echo L_UPDATE_BACK ?></a></p>
		</div>
		<?php else: ?>
		<form action="index.php" method="post">
		<fieldset class="panel">
			<p class="field"><label for="id_default_lang"><?php echo L_SELECT_LANG ?></label><p>
			<?php plxUtils::printSelect('default_lang', plxUtils::getLangs(), $lang) ?>&nbsp;
			<input type="submit" name="select_lang" value="<?php echo L_INPUT_CHANGE ?>" />
			<?php echo plxToken::getTokenPostMethod() ?>
		</fieldset class="panel">
		<fieldset class="panel">
			<p><strong><?php echo L_UPDATE_WARNING1.' '.$plxUpdater->oldVersion ?></strong></p>
			<?php if(empty($plxUpdater->oldVersion)) : ?>
			<p><?php echo L_UPDATE_SELECT_VERSION ?></p>
			<p><?php plxUtils::printSelect('version',$versions,''); ?></p>
			<p><?php echo L_UPDATE_WARNING2 ?></p>
			<?php endif; ?>
			<p class="msg"><?php echo L_UPDATE_WARNING3 ?></p>
			<p style="text-align:center"><input type="submit" name="submit" value="<?php echo L_UPDATE_START ?>" /></p>
		</fieldset>
		</form>
		<?php endif; ?>
	<?php else: ?>
		<div class="panel" style="padding:10px 10px 10px 10px">
			<?php
			$version = isset($_POST['version']) ? $_POST['version'] : '';
			$plxUpdater->start($version);
			?>
			<p><a href="<?php echo PLX_ROOT; ?>" title="<?php echo L_UPDATE_BACK ?>"><?php echo L_UPDATE_BACK ?></a></p>
		</div>
	<?php endif; ?>
	</div>
</div>

</body>
</html>