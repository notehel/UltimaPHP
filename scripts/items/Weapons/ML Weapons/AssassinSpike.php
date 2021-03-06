<?php

/**
* Ultima PHP - OpenSource Ultima Online Server written in PHP
* Version: 0.1 - Pre Alpha
*/

class AssassinSpike extends TypeNormal {
	public function build() {
		$this->name = "assassin spike";
		$this->graphic = 0x2D21;
		$this->type = "";
		$this->flags = 0x00;
		$this->value = 0;
		$this->amount = 1;
		$this->color = 0;
		$this->aosstrengthreq = 15;
		$this->aosmindamage = 10;
		$this->aosmaxdamage = 12;
		$this->aosspeed = 50;
		$this->mlspeed = 2;
		$this->oldstrengthreq = 15;
		$this->oldmindamage = 10;
		$this->oldspeed = 50;
		$this->defhitsound = 0;
		$this->defmisssound = 0x239;
		$this->hits = 30;
		$this->maxHits = 60;
		$this->weight = 4.0;

}}
?>
