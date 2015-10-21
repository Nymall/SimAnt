<?php
/*
*Sim Ant: Copywright 2014 Nicholas Fagerlid
*/

class WORLD{
	public $food;
	public $nest;
	public $turn;//Internal Turn used for timings. Reset every 4 turns.
	public $worldname;
	public $foodcounter;
	public $locationid;
	public $numofactive;
	public $turnoutput;
	public $absturn;//Absolute turn: Eg, the game is on turn 50
	const VERSION = '114';//WARNING: Only edit if the file structure has changed. This will delete all saves.
	
	function __construct(){
		$this->foodcounter = 0;
		$this->locationid[0] = array(0, 0);
		$this->numofactive = 0;
		$this->worldname = &$this;
		if(file_exists("saves/" . $_SERVER['REMOTE_ADDR'] . ".csv")){//check if the save exists for this IP
			$this->getfromcsv();
		} else {//if no, create some generic entries to start the game
			$this->generatenew();
		}
	}
	
	function generatenew(){
		$this->nest['green'] = new NEST("green", 20, 20, $this->worldname, 0, 'idle', true);
		$this->nest['red'] = new NEST("red", 1, 1, $this->worldname, 0, 'idle', true);
		$this->nest['orange'] = new NEST("orange", 1, 20, $this->worldname, 0, 'idle', true);
		$this->nest['blue'] = new NEST("blue", 20, 1, $this->worldname, 0, 'idle', true);
		$this->spawnfood(rand(1,19), rand(1,19));
		$this->spawnfood(rand(1,19), rand(1,19));
		$this->turn = 0;
		$this->absturn = 0;
	}
	
	function checklocation($posx, $posy){//check if there is a ant at specific x,y
		//print_r($this->locationid);
		$taken = true;
			foreach($this->locationid as $key => $loc){
				if($this->locationid[$key][0] == $posx && $this->locationid[$key][1] == $posy){
					$taken = false;
				}
			}
		return $taken;
	}
	
	function findenemy($posx, $posy, $type){//check if there is a ant at specific x,y
		//print_r($this->locationid);
		$taken = true;
			foreach($this->nest as $nest){
				if($nest->type != $type){
					if(count($nest->ant) > 0){
						foreach($nest->ant as $key => $ant){
							if($ant->posx < $posx + 2 && $ant->posx > $posx - 2 && $ant->posy < $posy + 2 && $ant->posy > $posy - 2){
								return array($nest->type, $key);
							}
						}
					}
				}
			}
		return 0;
	}
	
	function savetocsv(){//export the file to csv. WARNING: Changes here must be mirrored in getfromcsv()
		$numofnests = 0;
		$output = "";
		$nestline = "";
		foreach($this->nest as $keya => $nest){
			if($this->nest[$keya]->isdead == 0){
			$numofants = 0;
			$antline = "";
			foreach($nest->ant as $keyb => $ant){
				if($this->nest[$keya]->ant[$keyb]->isdead == 0){
					$antline .= "," . $this->nest[$keya]->ant[$keyb]->posx . "," . $this->nest[$keya]->ant[$keyb]->posy . "," . $this->nest[$keya]->ant[$keyb]->brain->update() . "," . $this->nest[$keya]->ant[$keyb]->quality . "," . $this->nest[$keya]->ant[$keyb]->stamina . "," . $this->nest[$keya]->ant[$keyb]->health . "," . $this->nest[$keya]->ant[$keyb]->lastpos . "," . $this->nest[$keya]->ant[$keyb]->enemyposx . "," . $this->nest[$keya]->ant[$keyb]->enemyposy;
					$numofants++;
				}
			}
			$nestline .= "," . $this->nest[$keya]->type . "," . $this->nest[$keya]->posx . "," . $this->nest[$keya]->posy . "," . $this->nest[$keya]->storedfood . "," . $this->nest[$keya]->brain->update() . "," . $numofants . $antline;
			$numofnests++;
			}
		}
		$output = self::VERSION . "," . $this->absturn . "," . $this->turn . "," . $numofnests . $nestline;
		$numoffood = 0;
		$foodset = "";
		foreach($this->food as $keyc => $food){
			if($this->food[$keyc]->used == 0){
				$foodset .= "," . $this->food[$keyc]->posx() . "," . $this->food[$keyc]->posy() . "," . $this->food[$keyc]->quality() . "," . $this->food[$keyc]->name();
				$numoffood++;
			}
		}
		$output .= "," . $numoffood . $foodset;
		file_put_contents("saves/" . $_SERVER['REMOTE_ADDR'] . ".csv", $output);
	}
	
	function spawnfood($posx, $posy, $quality=0, $name='Seed'){//add food to the game registry
		if($quality == 0){ 
			$quality = rand(1,10);
		}
		$this->food[$this->foodcounter] = new FOOD($posx, $posy, $this->foodcounter, $quality, $name);
		$this->foodcounter++;
	}
	
	function getfromcsv(){//get data from turn csv file WARNING: Changes here must mirror changes in savetocsv()
		$this->foodcounter = 0;
		$name = "saves/" . $_SERVER['REMOTE_ADDR'] . ".csv";
		$file = file_get_contents($name);
		//echo $file;
		$array = str_getcsv($file, ", ");
		$reversed = array_reverse($array);
		$version = array_pop($reversed);
		if($version == self::VERSION){//test the version number of the save file
		$this->absturn = array_pop($reversed);
		$this->turn = array_pop($reversed);
		$numofnests = array_pop($reversed);
		for( $i = 1; $i <= $numofnests; $i++){
				$nesttype = array_pop($reversed);
				$this->nest[$nesttype] = new NEST($nesttype, array_pop($reversed), array_pop($reversed), $this->worldname,  array_pop($reversed), array_pop($reversed), false);
				$numofants = array_pop($reversed);
				for( $j = 1; $j <= $numofants; $j++){
					$this->nest[$nesttype]->loadant(array_pop($reversed), array_pop($reversed), array_pop($reversed), array_pop($reversed), array_pop($reversed), array_pop($reversed), array_pop($reversed), array_pop($reversed), array_pop($reversed));
				}
		}
		$numoffood = array_pop($reversed);
		for($i = 1; $i <= $numoffood; $i++){
			$this->food[$this->foodcounter] = new FOOD(array_pop($reversed), array_pop($reversed),  $this->foodcounter, array_pop($reversed), array_pop($reversed));
			$this->foodcounter++;
		}
		} else {//test failed. rebuild game world
			$this->generatenew();
		}
	}
	
	function runturn(){//The turn logic. I know, helpful, eh?
		if(count($this->nest) >= 1){
		$this->absturn++;//increment the absolute turn
		foreach($this->nest as $key => $nest){
			$this->nest[$key]->update();//run every nest's FSM
			if(count($this->nest[$key]->ant) < 1){//if a nest looses all workers, it crumbles.
				$this->turnoutput .= "<b>Nest ran out of workers and crumbled at " . $this->nest[$key]->posx . "-" . $this->nest[$key]->posy . "</b><br>";
				$this->nest[$key]->isdead = 1;
				$this->spawnfood($this->nest[$key]->posx, $this->nest[$key]->posy, 10, "Crumbled<br>Nest");
				$this->spawnfood($this->nest[$key]->posx, $this->nest[$key]->posy, 10, "Crumbled<br>Nest");
			}
		}
		if($this->turn >= 4 && $this->foodcounter < 25){//if the food is under 25, and the turn is over 4, spawn new food.
			$this->turn = 0;
			$this->spawnfood(rand(1,19), rand(1,19));
		} else {
			$this->turn++;
		}
		if(count($this->food) <= 1){//aleways make sure 2 food items are availible on the stage
			$this->food[$this->foodcounter] = new FOOD(rand(1,19), rand(1,19), $this->foodcounter);
			$this->foodcounter++;
		}
		$objectlocation = array();
		foreach($this->nest as $keya => $nest){
			if($this->nest[$keya]->ant[0] != null){
				foreach($nest->ant as $keyb => $ant){//run the actions for each ant.
					$this->nest[$keya]->ant[$keyb]->update();
					array_unshift($objectlocation, "<td width='50' height='50' style='background-color:" . $this->nest[$keya]->ant[$keyb]->type . ";'><b style='color:white;'>ant<b/></td>", $this->nest[$keya]->ant[$keyb]->posx, $this->nest[$keya]->ant[$keyb]->posy);
				}
			}
			array_unshift($objectlocation, "<td width='50' height='50'><b><i>nest</i></b></td>", $this->nest[$keya]->posx, $this->nest[$keya]->posy);
		}
		foreach($this->food as $keyc => $food){
			array_unshift($objectlocation, "<td width='50' height='50' style='background-color:pink;'><i>" . $this->food[$keyc]->name() . "</i></td>", $this->food[$keyc]->posx, $this->food[$keyc]->posy);
		}
		for( $i = 0; $i <= 19; $i++){
			for( $j = 0; $j <= 19; $j++){
				$playfield[$i][$j] = "<td width='50' height='50'>grass</td>";
			}
		}
		for( $i = 0; $i < count($objectlocation); $i){//place each object on the playfield grid
			$playfield[$objectlocation[$i + 1] - 1][$objectlocation[$i + 2] - 1] = $objectlocation[$i];
			$i += 3;
		}
		$output = "<table border='1' style='text-align:center;'>";
		for($i = 0; $i <= 19; $i++){
			$output .= '<tr>';
			for($j = 0; $j <= 19; $j++){
				$output .= "" . $playfield[$i][$j] . '';
			}
			$output .= '</tr>';
		}
		$output .= '</table>';
		echo $output . "," . "Turn " . $this->absturn . ":<br>" . $this->turnoutput;
		$this->savetocsv();
		} else {
		echo "Everyone died...<br>It's game over man! Game over!<br> The world lasted " . $this->absturn . " turns.";
		}
	}
	
	/****************************************************************************************************
	*Credit to Jay Williams
	* @link http://gist.github.com/385876
	****************************************************************************************************/
	function csv_to_array($filename='', $delimiter=',')
	{
		if(!file_exists($filename) || !is_readable($filename))
			return FALSE;

		$header = NULL;
		$data = array();
		if (($handle = fopen($filename, 'r')) !== FALSE)
		{
			while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
			{
				if(!$header)
					$header = $row;
				else
					$data[] = array_combine($header, $row);
			}
			fclose($handle);
		}
		return $data;
	}
	//********************************************************************************************************
}

class FSM{//this is the Finite State Machine. But if you made it this far, you probably already know that.
	public $activestate;
	
	function __construct(){
	}
	
	function setstate($state){
		$this->activestate = $state;
	}
	
	function update(){
        if ($this->activestate != null) {
			$action = $this->activestate;
            return $action;
        }
    }
}

class FOOD{
	public $posx;
	public $posy;
	public $quality;
	public $id;
	public $used;
	public $name;
	
	function __construct($posix, $posiy, $id, $quality = 0, $name = "seed"){
		$this->posx = $posix;
		$this->posy = $posiy;
		$this->id = $id;
		$this->used = 0;
		$this->name = $name;
		if($quality == 0){
			$this->quality = rand(1,3);
		} else {
			$this->quality = $quality;
		}
	}
	
	public function posx(){
		return $this->posx;
	}
	
	public function posy(){
		return $this->posy;
	}
	
	public function quality(){
		return $this->quality;
	}
	
	public function id(){
		return $this->id;
	}
	
	public function used(){
		return $this->used;
	}
	
	public function name(){
		return $this->name;
	}
	
	public function editused($edit){
		$this->used = $edit;
	}
}

class NEST{
	public $ant;
	public $posx;
	public $posy;
	public $type;
	public $storedfood;
	public $isdead;
	public $world;
	
	function __construct($type, $posx, $posy, world &$world, $storedfood = 0, $brain='idle', $genant = false){
		$this->type = $type;
		$this->posx = $posx;
		$this->posy = $posy;
		$this->isdead = 0;
		$this->world = &$world;
		$this->storedfood = $storedfood;
		if($genant == true){
			$this->newant($posx, $posy);
		}
		$this->brain = new FSM();
		$this->brain->setState($brain);
	}
	
	public function appendtoturn($text){
		$this->world->turnoutput .= $text . "<br>";
	}
	
	function loadant($posx, $posy, $brain, $quality, $stamina, $health, $lastpos, $enemyposx, $enemyposy ){
		$this->ant[] = new ANT($posx, $posy, $this->type, $this->world, $brain, $quality, $stamina, $health, $lastpos, $enemyposx, $enemyposy);
	}
	
	function newant($posx, $posy){
		$this->ant[] = new ANT($posx, $posy, $this->type, $this->world);
	}
	
	function idle(){
		if($this->storedfood > 10){
			$this->brain->setState('spawnant');
		}
	}
	
	function spawnant(){
		if($this->world->checklocation($this->posx, $this->posy)){
			$this->newant($this->posx, $this->posy);
			$this->storedfood = $this->storedfood - 10;
			$this->brain->setState('idle');
		}
	}
	
	function update(){
	//Updates the FSM, running the function stored in the brain. Either spawnant or idle.
	$action = $this->brain->update();
	$this->{$action}();
	}
}

class ANT{
	public $posx;
	public $posy;
	public $brain;
	public $hasfood;
	public $quality;
	public $home;
	public $type;
	public $world;
	public $stamina;
	public $isdead;
	public $locationid;
	public $health;
	public $lastpos;
	public $damageresist;
    public $enemyposx;
    public $enemyposy;
	
	function __construct($posix, $posiy, $type, &$world, $brain = 'stunned', $quality = 0, $stamina = 50, $health='10', $lastpos = 0, $enemyposx = 0, $enemyposy = 0,){
		$this->world = &$world;
		$this->posx = $posix;
		$this->posy = $posiy;
		$this->type = $type;
		$this->quality = $quality;
		$this->home = &$this->world->nest[$type];
		$this->brain = new FSM();
		$this->brain->setstate($brain);
		$this->stamina = $stamina;
		$locid = $this->islocid();
		$this->locationid = $locid;
		$this->isdead = 0;
		$this->lastpos = $lastpos;
		$this->health = $health;
		$this->damageresist = false;
        $this->enemyposx = $enemyposx;
        $this->enemyposy = $enemyposy;
		if($brain == 'stunned'){
			$this->appendtoturn("<b>A new " . $this->type . " ant has been born!</b>");
		}
		$world->locationid[$this->locationid] = array($posix, $posiy);
	}
	
	public function appendtoturn($text){
		$this->world->turnoutput .= $text . "<br>";
	}
		
	public function islocid(){
		$active = &$this->world->numofactive;
		$active++;
		return $active;
	}
	
	function booststamina(){
		if($this->stamina < 200){
			$this->stamina = $this->stamina + 10;
		}
	}
	
	function drainstamina(){
		$this->stamina--;
		if($this->health <= 0){
				$this->appendtoturn("<b>" . $this->type . " ant died from it's wounds at " . $this->posx . "-" . $this->posy . "</b>");
				$this->isdead = 1;
				$this->world->spawnfood($this->posx, $this->posy, 0.5, "dead<br>ant");
				if($this->hasfood == true){
					$this->world->spawnfood($this->posx, $this->posy, $this->quality, "Ant<br>food");
				}
				return false;
		} elseif($this->stamina <= 0){
			if($this->quality >= 1){
				$this->brain->setstate("eatfood");
				return false;
			} else {
				$this->appendtoturn("<b>" . $this->type . " ant died at " . $this->posx . "-" . $this->posy . "</b>");
				$this->isdead = 1;
				$this->world->spawnfood($this->posx, $this->posy, 0.5, "dead<br>ant");
				if($this->hasfood == true){
					$this->world->spawnfood($this->posx, $this->posy, $this->quality, "Ant<br>food");
				}
				return false;
			}
		} else {
			return true;
		}
	}
	
	function eatfood(){//consume up to 5 food to restore stamina
		if($this->quality > 5){
			$this->quality = $this->quality - 5;
			$this->stamina = $this->stamina + 15;
			$this->brain->setstate("gohome");
			$this->appendtoturn("<b>" . $this->type . "</b>(" . $this->stamina . ") ant ate a part of it's food!");
		} else {
			$this->stamina = $this->quality * 3;
			$this->quality = 0;
			$this->hasfood = false;
			$this->brain->setstate("findleaf");
			$this->appendtoturn("<b>" . $this->type . "</b>(" . $this->stamina . ") ant ate all the food it was holding!");
		}
		$this->appendtoturn("<b>" . $this->type . "</b>(" . $this->stamina . ") ant looks healthier!");
	}
	
	//Findleaf - paths to the next availible food object.
	function findleaf(){
		if($this->hasfood == false && $this->drainstamina() && !is_array($this->world->findenemy($this->posx, $this->posy, $this->type))){
			$foods = array();
			foreach($this->world->food as $key => $food){
				$foods[$key][] = $this->world->food[$key]->posx();
				$foods[$key][] = $this->world->food[$key]->posy();
				$foods[$key][] = $this->world->food[$key]->quality();
				$foods[$key][] = $key;
				$foods[$key][] = $this->world->food[$key]->name();
			}
			$tilesaway = 100;
			$order = array();
			foreach($foods as $key => $item){
				$xtest = abs($item[0] - $this->posx);
				$ytest = abs($item[1] - $this->posy);
				$testvalue = floor(($xtest + $ytest) / 2);
				if( $testvalue < $tilesaway ){
					$tilesaway = $testvalue;
					array_unshift($order, $item[0], $item[1], $item[2], $item[3], $item[4]);
				}
			}
			//print_r($order);
			if($order[0] < $this->posx && $order[1] > $this->posy){
				if($this->world->checklocation($this->posx + 1, $this->posy - 1)){
					$this->posy++;
					$this->posx--;
				} elseif($this->world->checklocation($this->posx - 1, $this->posy)){
					$this->posx++;
				} elseif($this->world->checklocation($this->posx, $this->posy + 1)){
					$this->posy--;
				}
			} elseif($order[0] < $this->posx && $order[1] < $this->posy){
				if($this->world->checklocation($this->posx + 1, $this->posy + 1)){
					$this->posy--;
					$this->posx--;
				} elseif($this->world->checklocation($this->posx + 1, $this->posy)){
					$this->posx--;
				} elseif($this->world->checklocation($this->posx, $this->posy + 1)){
					$this->posy--;
				}
			} elseif($order[0] > $this->posx && $order[1] > $this->posy){
				if($this->world->checklocation($this->posx - 1, $this->posy - 1)){
					$this->posy++;
					$this->posx++;
				} elseif($this->world->checklocation($this->posx - 1, $this->posy)){
					$this->posx++;
				} elseif($this->world->checklocation($this->posx, $this->posy - 1)){
					$this->posy++;
				}
			} elseif($order[0] > $this->posx && $order[1] < $this->posy){
				if($this->world->checklocation($this->posx - 1, $this->posy + 1)){
					$this->posy--;
					$this->posx++;
				} elseif($this->world->checklocation($this->posx - 1, $this->posy)){
					$this->posx++;
				} elseif($this->world->checklocation($this->posx, $this->posy - 1)){
					$this->posy--;
				}
			}elseif($order[0] > $this->posx){
				if($this->world->checklocation($this->posx + 1, $this->posy)){
					$this->posx++;
				}
			} elseif($order[0] < $this->posx){
				if($this->world->checklocation($this->posx - 1, $this->posy)){
					$this->posx--;
				}
			} elseif($order[1] > $this->posy){
				if($this->world->checklocation($this->posx, $this->posy + 1)){
					$this->posy++;
				}
			} elseif($order[1] < $this->posy){
				if($this->world->checklocation($this->posx, $this->posy - 1)){
					$this->posy--;
				}
			} 
			$this->appendtoturn("<b>" . $this->type . "</b>(" . $this->stamina . ") ant moved to " . $order[4] . " at " . $order[0] . "-" . $order[1]);
			if($order[0] == $this->posx && $order[1] == $this->posy && $this->world->food[$order[3]]->used == 0){
				$this->booststamina();
				$this->hasfood = true;
				$this->quality = $order[2];
				//echo "<br>Order 2:" . $order[2] . "<br>Quality:" . $this->quality . "<br>";
				$this->world->food[($order[3])]->editused(1);
				$this->brain->setstate("gohome");
				$this->appendtoturn("<b>" . $this->type . "</b>(" . $this->stamina . ") ant picked up " . $order[4] . " at " . $order[0] . "-" . $order[1]);
			}
			if($this->lastpos == $this->posx . $this->posy){
				$this->checkifsameloc();
			}
		} elseif(is_array($this->world->findenemy($this->posx, $this->posy, $this->type))){
			$this->brain->setstate("attack");
			$this->update();
		}else{
			$this->brain->setstate("gohome");
		}
	}
	
	function stunned(){
		$this->brain->setstate("findleaf");
	}
	
	function attack(){
		//this function allows the ant to attack other ants
		//in the future, ants will be able to attack nests
		$this->drainstamina();
		$test = $this->world->findenemy($this->posx, $this->posy, $this->type);
		if(!is_array($test)){
			$this->brain->setstate("findleaf");
			$this->update();
		} else {
			$this->world->nest[$test[0]]->ant[$test[1]]->reaction();
			$placehold = &$this->world->nest[$test[0]]->ant[$test[1]];
            $placehold->enemyposx = $this->posx;
            $placehold->enemyposy = $this->posy;
			$this->appendtoturn("<b>" . $this->type . "</b>(" . $this->stamina . ") attacked a ant at " . $placehold->posx . "-" . $placehold->posy . "!");
			if($placehold->damageresist = true){
				$placehold->health = $placehold->health - rand(1,2);
				$this->health = $this->health - 1;
			} else {
				$placehold->health = $placehold->health - rand(1,4);
			}
			$this->appendtoturn("<b>" . $placehold->type . "</b>(" . $placehold->stamina . ") has " . $placehold->health . " health left.");
		}
	}
	
	function stunned_attack(){
		//this is the failure option for the defender
		$test = $this->world->findenemy($this->posx, $this->posy, $this->type);
		if(!is_array($test)){
			$this->brain->setstate("findleaf");
		} else {
			$this->brain->setstate("attack");
		}
	}
	
	function defend(){
		$this->drainstamina();
		//the ant takes minimal damage, defends and can either flee or attack.
		//if the ant is holding food, the ant flees
		//else, the ant either stays defending and gets a bonus to health, or attacks
        if($this->quality > 0){
            $this->brain->setstate('runaway');
            $this->update();
        } else {
            if(rand(1,2) == 1){
                $this->brain->setstate('attack');
                $this->update();
            } else {
                $this->brain->setstate('defend');
                $this->health = $this->health + rand(3,7);
            }
        }
	}
	
	function reaction(){
		//forces the ant into either a defensive or stunned state.
		if(rand(1,4) == 1){
			$this->brain->setstate("stunned_attack");
		} else {
			$this->brain->setstate("defend");
			$this->damageresist = true;
			$this->appendtoturn("<b>" . $this->type . "</b>(" . $this->stamina . ") defended at " . $this->posx . "-" . $this->posy . ".");
		}
	}
	
	//gohome - Paths back to the ant lair
	function gohome(){
		$this->drainstamina();
		if($this->isdead == 0){
		$this->appendtoturn("<b>" . $this->type . "</b>(" . $this->stamina . ") ant moved towards home at " . $this->world->nest[$this->type]->posx . "-" . $this->world->nest[$this->type]->posy);
        $this->moveto($this->world->nest[$this->type]->posx, this->world->nest[$this->type]->posy);
		if($this->world->nest[$this->type]->posy == $this->posy && $this->world->nest[$this->type]->posx == $this->posx){
			$this->appendtoturn("<b>" . $this->type . "</b>(" . $this->stamina . ") ant arrived at home at " . $this->world->nest[$this->type]->posx . "-" . $this->world->nest[$this->type]->posy);
			$this->booststamina();
			$this->hasfood = false;
			$this->world->nest[$this->type]->storedfood = $this->world->nest[$this->type]->storedfood + $this->quality;
			//echo "<br>Quality:" . $this->world->nest[$this->type]->storedfood + $this->quality . "<br>";
			$this->quality = 0;
			$this->brain->setstate("findleaf");
		}
		}
	}
	
		//runaway - Paths away from tile
	function runaway(){
        if($this->enemyposx < $this->posx){
            $targetx = $this->posx + 1;
        } elseif($this->enemyposx > $this->posx) {
            $targetx = $this->posx - 1;
        }
        
        if($this->enemyposy < $this->posy){
            $targety = $this->posy + 1;
        } elseif($this->enemyposy > $this->posy) {
            $targety = $this->posy - 1;
        }
        //Target set, now we try to move towards it.
        $this->moveto($targetx, $targety);
        //Reset the FSM
        if($this->quality > 0){
            $this->brain->setstate("gohome");
        } else {
            $this->brain->setstate("findleaf");
        }
		$this->drainstamina();
	}
    
    function moveto($posx, $posy){
        if($posx < $this->posx && $posy > $this->posy){
				if($this->world->checklocation($this->posx + 1, $this->posy - 1)){
					$this->posy++;
					$this->posx--;
				} elseif($this->world->checklocation($this->posx - 1, $this->posy)){
					$this->posx++;
				} elseif($this->world->checklocation($this->posx, $this->posy + 1)){
					$this->posy--;
				}
			} elseif($posx < $this->posx && $posy < $this->posy){
				if($this->world->checklocation($this->posx + 1, $this->posy + 1)){
					$this->posy--;
					$this->posx--;
				} elseif($this->world->checklocation($this->posx + 1, $this->posy)){
					$this->posx--;
				} elseif($this->world->checklocation($this->posx, $this->posy + 1)){
					$this->posy--;
				}
			} elseif($posx > $this->posx && $posy > $this->posy){
				if($this->world->checklocation($this->posx - 1, $this->posy - 1)){
					$this->posy++;
					$this->posx++;
				} elseif($this->world->checklocation($this->posx - 1, $this->posy)){
					$this->posx++;
				} elseif($this->world->checklocation($this->posx, $this->posy - 1)){
					$this->posy++;
				}
			} elseif($posx > $this->posx && $posy < $this->posy){
				if($this->world->checklocation($this->posx - 1, $this->posy + 1)){
					$this->posy--;
					$this->posx++;
				} elseif($this->world->checklocation($this->posx - 1, $this->posy)){
					$this->posx++;
				} elseif($this->world->checklocation($this->posx, $this->posy - 1)){
					$this->posy--;
				}
			} elseif($>posx > $this->posx){
			if($this->world->checklocation($this->posx + 1, $this->posy)){
				$this->posx++;
			} elseif(($this->posy + 1) < 21){
				$this->posy--;
			} else {
				$this->posy++;
			}
		} elseif($posx < $this->posx){
			if($this->world->checklocation($this->posx - 1, $this->posy)){
				$this->posx--;
			} elseif(($this->posy - 1) > 0){
				$this->posy++;
			} else {
				$this->posy--;
			}
		} elseif($posy > $this->posy){
			if($this->world->checklocation($this->posx, $this->posy + 1)){
				$this->posy++;
			} elseif(($this->posx + 1) < 21){
				$this->posx--;
			} else {
				$this->posx++;
			}
		} elseif($posy < $this->posy){
			if($this->world->checklocation($this->posx, $this->posy-1)){
				$this->posy--;
			} elseif(($this->posy - 1) > 0){
				$this->posx++;
			} else {
				$this->posx--;
			}
		}
    }
	
	function update(){
	//Updates the FSM, running the function stored in the brain. Either findleaf, gohome or runaway.
	$action = $this->brain->update();
	$this->{$action}();
	$this->world->locationid[$this->locationid] = array($this->posx, $this->posy);
	$this->lastpos = $this->posx . $this->posy;
	}
	
	function checkifsameloc(){//This is the new tiebreaker. If a ant has not moved since last turn, force a movement.
		if($this->world->checklocation($this->posx, $this->posy + 1) && $this->world->checklocation($this->posx, $this->posy - 1)){
				if(rand(2,1) == 1){
					$this->posy++;
				} else {
					$this->posy++;
				}
			} elseif($this->world->checklocation($this->posx, $this->posy + 1)){
				$this->posy--;
			} else {
				$this->posy++;
			}
		//$this->appendtoturn("<b>" . $this->type . "</b>(" . $this->stamina . ") ERROR STATE AT " . $this->world->nest[$this->type]->posx . "-" . $this->world->nest[$this->type]->posy);
		//uncomment to check when this function is firing, and on what object.
	}

}

$world = new WORLD();
$world->runturn();


?>