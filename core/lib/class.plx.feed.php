<?php

/**
 * Classe plxFeed responsable du traitement global des flux de syndication
 *
 * @package PLX
 * @author	Florent MONTHEL, Stephane F, Amaury Graillat
 **/
class plxFeed extends plxMotor {

	/**
	 * Constructeur qui initialise certaines variables de classe
	 * et qui lance le traitement initial
	 *
	 * @param	filename	emplacement du fichier XML de configuration
	 * @return	null
	 * @author	Florent MONTHEL, Stéphane F
	 **/
	public function __construct($filename) {

		# Version de PluXml
		if(!is_readable(PLX_ROOT.'version')) {
			header('Content-Type: text/plain charset=UTF-8');
			printf(L_FILE_VERSION_REQUIRED, PLX_ROOT);
			exit;
		}
		$f = file(PLX_ROOT.'version');
		$this->version = $f['0'];

		$this->get = plxUtils::getGets();

		$this->getConfiguration($filename);
		$this->racine = $this->aConf['racine'];
		$this->bypage = $this->aConf['bypage_feed'];
		$this->tri = 'desc';
		$this->clef = (!empty($this->aConf['clef']))?$this->aConf['clef']:'';

		$this->plxGlob_arts = plxGlob::getInstance(PLX_ROOT.$this->aConf['racine_articles']);
		$this->plxGlob_coms = plxGlob::getInstance(PLX_ROOT.$this->aConf['racine_commentaires']);
		# Traitement des plugins
		$this->plxPlugins = new plxPlugins(PLX_ROOT.$this->aConf['plugins'], $this->aConf['default_lang']);
		$this->plxPlugins->loadPlugins();
		# Récupération des données dans les autres fichiers xml
		$this->getCategories(PLX_ROOT.$this->aConf['categories']);
		$this->getUsers(PLX_ROOT.$this->aConf['users']);
		# Hook plugins
		eval($this->plxPlugins->callHook('plxFeedConstruct'));
	}

	/**
	 * Méthode qui effectue une analyse de la situation et détermine
	 * le mode à appliquer. Cette méthode alimente ensuite les variables
	 * de classe adéquates
	 *
	 * @return	null
	 * @author	Florent MONTHEL, Stéphane F
	 **/
	public function fprechauffage() {

		# Hook plugins
		if(eval($this->plxPlugins->callHook('plxFeedPreChauffageBegin'))) return;

		if($this->get AND preg_match('#^(atom|rss)/categorie([0-9]+)/?$#',$this->get,$capture)) {
			$this->mode = 'article'; # Mode du flux
			# On récupère la catégorie cible
			$this->cible = str_pad($capture[2],3,'0',STR_PAD_LEFT); # On complete sur 3 caracteres
			# On modifie le motif de recherche
			$this->motif = '/^[0-9]{4}.[home,|0-9,]*'.$this->cible.'[home,|0-9,]*.[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/';
		}
		elseif($this->get AND preg_match('#^(atom|rss)/commentaires/?$#',$this->get)) {
			$this->mode = 'commentaire'; # Mode du flux
		}
		elseif($this->get AND preg_match('#^(atom|rss)/commentaires/article([0-9]+)/?$#',$this->get,$capture)) {
			$this->mode = 'commentaire'; # Mode du flux
			# On recupere l'article cible
			$this->cible = str_pad($capture[2],4,'0',STR_PAD_LEFT); # On complete sur 4 caracteres
			# On modifie le motif de recherche
			$this->motif = '/^'.$this->cible.'.([0-9,|home]*).[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/';
		}
		elseif($this->get AND preg_match('#^admin([a-zA-Z0-9]+)/commentaires/(hors|en)-ligne/?$#',$this->get,$capture)) {
			$this->mode = 'admin'; # Mode du flux
			$this->cible = '-';	# /!\: il ne faut pas initialiser à blanc sinon ça prend par défaut les commentaires en ligne (faille sécurité)
			if ($capture[1] == $this->clef) {
				if($capture[2] == 'hors')
					$this->cible = '_';
				elseif($capture[2] == 'en')
					$this->cible = '';
			}
		} else {
			$this->mode = 'article'; # Mode du flux
			# On modifie le motif de recherche
			$this->motif = '/^[0-9]{4}.([0-9,|home]*).[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/';
		}
		# Hook plugins
		eval($this->plxPlugins->callHook('plxFeedPreChauffageEnd'));

	}

	/**
	 * Méthode qui effectue le traitement selon le mode du moteur
	 *
	 * @return	null ou redirection si une erreur est détectée
	 * @author	Florent MONTHEL, Stéphane F
	 **/
	public function fdemarrage() {

		# Hook plugins
		if(eval($this->plxPlugins->callHook('plxFeedDemarrageBegin'))) return;

		# Flux de commentaires d'un article precis
		if($this->mode == 'commentaire' AND $this->cible) {
			if(!$this->getArticles()) { # Aucun article, on redirige
				$this->cible = $this->cible + 0;
				header('Location: '.$this->urlRewrite('?article'.$this->cible.'/'));
				exit;
			} else { # On récupère les commentaires
				$regex = '/^'.$this->cible.'.[0-9]{10}-[0-9]+.xml$/';
				$this->getCommentaires($regex,'rsort',0,$this->bypage);
			}
		}
		# Flux de commentaires global
		elseif($this->mode == 'commentaire') {
			$regex = '/^[0-9]{4}.[0-9]{10}-[0-9]+.xml$/';
			$this->getCommentaires($regex,'rsort',0,$this->bypage);
		}
		# Flux admin
		elseif($this->mode == 'admin') {
			if(empty($this->clef)) { # Clef non initialisée
				header('Content-Type: text/plain');
				echo L_FEED_NO_PRIVATE_URL;
				exit;
			}
			# On récupère les commentaires
			$this->getCommentaires('/^'.$this->cible.'[0-9]{4}.[0-9]{10}-[0-9]+.xml$/','rsort',0,$this->bypage);
		}
		# Flux d'articles
		else {
			# Flux des articles d'une catégorie précise
			if($this->cible) {
				# On va tester la catégorie
				if(empty($this->aCats[ $this->cible ])) { # Pas de catégorie, on redirige
					$this->cible = $this->cible + 0;
					header('Location: '.$this->urlRewrite('?categorie'.$this->cible.'/'));
					exit;
				}
			}
			$this->getArticles(); # Recupération des articles (on les parse)
		}

		# Selon le mode, on appelle la méthode adéquate
		switch($this->mode) {
			case 'article' : $this->getRssArticles(); break;
			case 'commentaire' : $this->getRssCommentaires(); break;
			case 'admin' : $this->getAdminCommentaires(); break;
			default : break;
		}
		# Hook plugins
		eval($this->plxPlugins->callHook('plxFeedDemarrageEnd'));

	}

	/**
	 * Méthode qui affiche le flux rss des articles du site
	 *
	 * @return	flux sur stdout
	 * @author	Florent MONTHEL, Stephane F, Amaury GRAILLAT
	 **/
	public function getRssArticles() {

		# Initialisation
		$last_updated = '';
		$entry_link = '';
		$entry = '';
		if($this->cible) { # Articles d'une catégorie
			$catId = $this->cible + 0;
			$title = $this->aConf['title'].' - '.$this->aCats[ $this->cible ]['name'];
			$link = $this->urlRewrite('?categorie'.$catId.'/'.$this->aCats[ $this->cible ]['url']);
		} else { # Articles globaux
			$title = $this->aConf['title'];
			$link = $this->urlRewrite();
		}
		# On va boucler sur les articles (si il y'en a)
		if($this->plxRecord_arts) {
			while($this->plxRecord_arts->loop()) {
				# Traitement initial
				if($this->aConf['feed_chapo']) {
					$content = $this->plxRecord_arts->f('chapo');
					if(trim($content)=='') $content = $this->plxRecord_arts->f('content');
				} else {
					$content = $this->plxRecord_arts->f('chapo').$this->plxRecord_arts->f('content');
				}
				$content .= $this->aConf['feed_footer'];
				$artId = $this->plxRecord_arts->f('numero') + 0;
				$author = $this->aUsers[$this->plxRecord_arts->f('author')]['name'];
				# On check la date de publication
				if($this->plxRecord_arts->f('date') > $last_updated)
					$last_updated = $this->plxRecord_arts->f('date');

				# On affiche le flux dans un buffer
				$entry .= "\t<item>\n";
				$entry .= "\t\t".'<title>'.plxUtils::strCheck($this->plxRecord_arts->f('title')).'</title> '."\n";
				$entry .= "\t\t".'<link>'.$this->urlRewrite('?article'.$artId.'/'.$this->plxRecord_arts->f('url')).'</link>'."\n";
				$entry .= "\t\t".'<guid>'.$this->urlRewrite('?article'.$artId.'/'.$this->plxRecord_arts->f('url')).'</guid>'."\n";
				$entry .= "\t\t".'<description>'.plxUtils::strCheck(plxUtils::rel2abs($this->racine,$content)).'</description>'."\n";
				$entry .= "\t\t".'<pubDate>'.plxDate::dateIso2rfc822($this->plxRecord_arts->f('date')).'</pubDate>'."\n";
				$entry .= "\t\t".'<dc:creator>'.plxUtils::strCheck($author).'</dc:creator>'."\n";
				$entry .= "\t</item>\n";
			}
		}

		# On affiche le flux
		header('Content-Type: text/xml; charset='.PLX_CHARSET);
		echo '<?xml version="1.0" encoding="'.PLX_CHARSET.'" ?>'."\n";
		echo '<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom">'."\n";
		echo '<channel>'."\n";
		echo "\t".'<title>'.plxUtils::strCheck($title).'</title>'."\n";
		echo "\t".'<link>'.$link.'</link>'."\n";
		echo "\t".'<language>' . $this->aConf['default_lang'] . '</language>'."\n";
		echo "\t".'<description>'.plxUtils::strCheck($this->aConf['description']).'</description>'."\n";
		echo '<atom:link xmlns:atom="http://www.w3.org/2005/Atom" rel="self" type="application/rss+xml" href="' . $this->racine. 'feed.php' . '" />'."\n";
		$last_updated = plxDate::dateIso2rfc822($last_updated);
		echo "\t".'<lastBuildDate>'.$last_updated.'</lastBuildDate>'."\n";
		echo "\t".'<generator>PluXml</generator>'."\n";
		echo $entry;
		echo '</channel>'."\n";
		echo '</rss>';
	}

	/**
	 * Méthode qui affiche le flux rss des commentaires du site
	 *
	 * @return	flux sur stdout
	 * @author	Florent MONTHEL, Amaury GRAILLAT
	 **/
	public function getRssCommentaires() {

		# Traitement initial
		$last_updated = '';
		$entry_link = '';
		$entry = '';
		if($this->cible) { # Commentaires d'un article
			$artId = $this->plxRecord_arts->f('numero') + 0;
			$title = $this->aConf['title'].' - '.$this->plxRecord_arts->f('title').' - '.L_FEED_COMMENTS;
			$link = $this->urlRewrite('?article'.$artId.'/'.$this->plxRecord_arts->f('url'));
		} else { # Commentaires globaux
			$title = $this->aConf['title'].' - '.L_FEED_COMMENTS;
			$link = $this->urlRewrite();
		}

		# On va boucler sur les commentaires (si il y'en a)
		if($this->plxRecord_coms) {
			while($this->plxRecord_coms->loop()) {
				# Traitement initial
				$artId = $this->plxRecord_coms->f('article') + 0;
				if($this->cible) { # Commentaires d'un article
					$title_com = $this->plxRecord_arts->f('title').' - ';
					$title_com .= 'par '.$this->plxRecord_coms->f('author').' le ';
					$title_com .= plxDate::dateIsoToHum($this->plxRecord_coms->f('date'),'#day #num_day #month #num_year(4), #hour:#minute');
					$link_com = $this->urlRewrite('?article'.$artId.'/'.$this->plxRecord_arts->f('url').'/rss#c'.$this->plxRecord_coms->f('numero'));
				} else { # Commentaires globaux
					$title_com = $this->plxRecord_coms->f('author').' le ';
					$title_com .= plxDate::dateIsoToHum($this->plxRecord_coms->f('date'),'#day #num_day #month #num_year(4), #hour:#minute');
					$link_com = $this->urlRewrite('?article'.$artId.'/#c'.$this->plxRecord_coms->f('numero'));
				}
				# On check la date de publication
				if($this->plxRecord_coms->f('date') > $last_updated)
					$last_updated = $this->plxRecord_coms->f('date');

				# On affiche le flux dans un buffer
				$entry .= "\t<item>\n";
				$entry .= "\t\t".'<title>'.strip_tags(html_entity_decode($title_com, ENT_QUOTES, PLX_CHARSET)).'</title> '."\n";
				$entry .= "\t\t".'<link>'.$link_com.'</link>'."\n";
				$entry .= "\t\t".'<guid>'.$link_com.'</guid>'."\n";
				$entry .= "\t\t".'<description>'.plxUtils::strCheck(strip_tags($this->plxRecord_coms->f('content'))).'</description>'."\n";
				$entry .= "\t\t".'<pubDate>'.plxDate::dateIso2rfc822($this->plxRecord_coms->f('date')).'</pubDate>'."\n";
				$entry .= "\t\t".'<dc:creator>'.plxUtils::strCheck($this->plxRecord_coms->f('author')).'</dc:creator>'."\n";
				$entry .= "\t</item>\n";
			}
		}

		# On affiche le flux
		header('Content-Type: text/xml; charset='.PLX_CHARSET);
		echo '<?xml version="1.0" encoding="'.PLX_CHARSET.'" ?>'."\n";
		echo '<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom">'."\n";
		echo '<channel>'."\n";
		echo '<atom:link xmlns:atom="http://www.w3.org/2005/Atom" rel="self" type="application/rss+xml" href="' . $this->urlRewrite('feed.php?rss/commentaires/') . '" />'."\n";

		$entry .= "\t\t".'<title>'.strip_tags(html_entity_decode($title, ENT_QUOTES, PLX_CHARSET)).'</title> '."\n";
		echo "\t".'<link>'.$link.'</link>'."\n";
		echo "\t".'<language>' . $this->aConf['default_lang'] . '</language>'."\n";
		echo "\t".'<description>'.plxUtils::strCheck($this->aConf['description']).'</description>'."\n";

		$last_updated = plxDate::dateIso2rfc822($last_updated);
		echo "\t".'<lastBuildDate>'.$last_updated.'</lastBuildDate>'."\n";
		echo "\t".'<generator>PluXml</generator>'."\n";
		echo $entry;
		echo '</channel>'."\n";
		echo '</rss>';
	}

	/**
	 * Méthode qui affiche le flux RSS des commentaires du site pour l'administration
	 *
	 * @return	flux sur stdout
	 * @author	Florent MONTHEL, Amaury GRAILLAT
	 **/
	public function getAdminCommentaires() {
		# Traitement initial
		$last_updated = '';
		$entry = '';
		if($this->cible == '_') { # Commentaires hors ligne
			$link = $this->racine.'core/admin/comments.php?sel=offline&amp;page=1';
			$title = $this->aConf['title'].' - '.L_FEED_OFFLINE_COMMENTS;
			$link_feed = $this->racine.'feed.php?admin'.$this->clef.'/commentaires/hors-ligne';
		} else { # Commentaires en ligne
			$link = $this->racine.'core/admin/comments.php?sel=online&amp;page=1';
			$title = $this->aConf['title'].' - '.L_FEED_ONLINE_COMMENTS;
			$link_feed = $this->racine.'feed.php?admin'.$this->clef.'/commentaires/en-ligne';
		}

		# On va boucler sur les commentaires (si il y'en a)
		if($this->plxRecord_coms) {
			while($this->plxRecord_coms->loop()) {
				$artId = $this->plxRecord_coms->f('article') + 0;
				$comId = $this->cible.$this->plxRecord_coms->f('article').'.'.$this->plxRecord_coms->f('numero');
				$title_com = $this->plxRecord_coms->f('author').' le ';
				$title_com .= plxDate::dateIsoToHum($this->plxRecord_coms->f('date'),'#day #num_day #month #num_year(4), #hour:#minute');
				$link_com = $this->racine.'core/admin/comment.php?c='.$comId;
				# On check la date de publication
				if($this->plxRecord_coms->f('date') > $last_updated)
					$last_updated = $this->plxRecord_coms->f('date');
				# On affiche le flux dans un buffer
				$entry .= "\t<item>\n";
				$entry .= "\t\t".'<title>'.strip_tags(html_entity_decode($title_com, ENT_QUOTES, PLX_CHARSET)).'</title> '."\n";
				$entry .= "\t\t".'<link>'.$link_com.'</link>'."\n";
				$entry .= "\t\t".'<guid>'.$link_com.'</guid>'."\n";
				$entry .= "\t\t".'<description>'.plxUtils::strCheck(strip_tags($this->plxRecord_coms->f('content'))).'</description>'."\n";
				$entry .= "\t\t".'<pubDate>'.plxDate::dateIso2rfc822($this->plxRecord_coms->f('date')).'</pubDate>'."\n";
				$entry .= "\t\t".'<dc:creator>'.plxUtils::strCheck($this->plxRecord_coms->f('author')).'</dc:creator>'."\n";
				$entry .= "\t</item>\n";
			}
		}

		# On affiche le flux
		header('Content-Type: text/xml; charset='.PLX_CHARSET);
		echo '<?xml version="1.0" encoding="'.PLX_CHARSET.'" ?>'."\n";
		echo '<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom">'."\n";
		echo '<channel>'."\n";
		echo "\t".'<title>'.plxUtils::strCheck($title).'</title>'."\n";
		echo "\t".'<description>'.plxUtils::strCheck($this->aConf['description']).'</description>'."\n";
		echo "\t".'<link>'.$link.'</link>'."\n";
		echo "\t".'<language>' . $this->aConf['default_lang'] . '</language>'."\n";
		echo '<atom:link xmlns:atom="http://www.w3.org/2005/Atom" rel="self" type="application/rss+xml" href="' . $link_feed . '" />'."\n";
		$last_updated = plxDate::dateIso2rfc822($last_updated);
		echo "\t".'<lastBuildDate>'.$last_updated.'</lastBuildDate>'."\n";
		echo "\t".'<generator>PluXml</generator>'."\n";
		echo $entry;
		echo '</channel>'."\n";
		echo '</rss>';
	}
}
?>
