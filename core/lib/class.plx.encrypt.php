<?php
/**
 * Classe plxEncrypt responsable du cryptage et d�cryptage de donn�es
 *
 * @package PLX
 * @author	Stephane F
 **/

define('ENCRYPTION_KEY', 'ab12cd34#$');

class plxEncrypt {

	/**
	 * M�thode qui crypte le texte $plainText
	 *
	 * @param	plainText	cha�ne � crypter
	 * @return	string	cha�ne crypt�e
	 * @author	Stephane F.
	 **/
	private static function base64url_encode($plainText) {

		$base64 = base64_encode($plainText);
		$base64url = strtr($base64, '+/=', '-_,');
		return $base64url;
	}

	/**
	 * M�thode qui d�crypte le texte $plainText
	 *
	 * @param	plainText	cha�ne � d�crypter
	 * @return	string	cha�ne d�crypt�e
	 * @author	Stephane F.
	 **/
	private static function base64url_decode($plainText) {

		$base64url = strtr($plainText, '-_,', '+/=');
		$base64 = base64_decode($base64url);
		return $base64;
	}

	public static function encryptId($int, $class='') {

		return plxEncrypt::base64url_encode($int.'*'.substr(sha1($class.$int.ENCRYPTION_KEY), 0, 6));
	}

	public static function decryptId($int, $class='') {

		$parts = explode('*', plxEncrypt::base64url_decode($int));
		if(sizeof($parts) != 2)
			return 0;
		return substr(sha1($class.$parts[0].ENCRYPTION_KEY), 0, 6) === $parts[1] ? $parts[0] : 0;
	}

}
?>