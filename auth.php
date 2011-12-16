<?php

require_once 'pgdb.php';
require_once 'common.php';

/* Luokka, joka pitää visusti huolta käyttäjien
 * tunnistamisesta. On tarvittaessa hyvin ankara.
 */
class AUTH {
	private $kayttaja;
	private $yllapitelija;

	private $kysely = "SELECT tunnus, yllapeto FROM kayttajat WHERE tunnus = '%s' AND salasana = '%s'";

	/* Oletuskonstruktori:
	 * Olion luonnin yhteydessä mennään suoraan asiaan ja
	 * tarkistaan käyttäjän paperit eli ajetaan pikkuleipä-
	 * tiedot tietokannan kautta ja tsekataan, onko käyttäjä
	 * kosher.
	 */
	function __construct() {
		$this->kayttaja = false;
		$this->yllapitelija = false;

		$user = hae_pipari("user");
		$pass = hae_pipari("pass");

		if (empty($user) or empty($pass)) {
			return;
		}

		$kysely = sprintf($this->kysely, pg_escape_string($user), pg_escape_string($pass));

		$vastaus = with(new PGDB)->kysele($kysely)->anna_rivi()->taulukkona();

		if ($vastaus !== false) {
			$this->kayttaja = $vastaus["tunnus"];
			$this->yllapitelija = $vastaus["yllapeto"];

			if (!headers_sent()) {
				aseta_pipari("user", $user);
				aseta_pipari("pass", $pass);
			}
		}
	}

	/* Kertoo, onko käyttäjä tunnistettu eli palauttaa
	 * totuusarvon true tai false sen mukaan onko käyttäjä
	 * tunnistettu vai ei.
	 */
	public function ok() {
		return ($this->kayttaja !== false);
	}

	/* Kertoo, kuten yllä, onko käyttäjällä ylläpeto-oikeudet.
	 */
	public function yllapeto() {
		return ($this->yllapitelija !== false);
	}

	/* Palauttaa käyttäjänimen, jos käyttäjä on tunnistettu.
	 */
	public function kayttaja() {
		return $this->kayttaja;
	}
}

?>
