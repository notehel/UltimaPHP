<?php

/**
* Ultima PHP - OpenSource Ultima Online Server written in PHP
* Version: 0.1 - Pre Alpha
*/

class Centaur extends Mobile {
	public function summon() {
		$this->name = "centaur";
		$this->body = 101;
		$this->type = "";
		$this->flags = 0x00;
		$this->color = 0x00;
		$this->basesoundid = 0;
		$this->str = rand(202, 300);
		$this->dex = rand(104, 260);
		$this->int = rand(91, 100);
		$this->maxhits = rand(130, 172);
		$this->hits = $this->maxhits;
		$this->damage = 13;
		$this->damageMax = 24;
		$this->resist_physical = rand(45, 55);
		$this->resist_fire = rand(35, 45);
		$this->resist_cold = rand(25, 35);
		$this->resist_poison = rand(45, 55);
		$this->resist_energy = rand(35, 45);
		$this->karma = 0;
		$this->fame = 6500;
		$this->virtualarmor = 50;

}}
?>
