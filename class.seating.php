<?php
// Example PHP class for Sister's Seating
// Joel McCauley - 11/16/16 - For Showclix

class map {
	private $map_data; 	//seat chart
	private $rows;	//number of rows
	private $cols;	//number of seats in a row

	function __construct() {
		$this->map_data = array();
	}


	/**
	* build
	* - Creates rows and seats
	* - Parses reserved/available into separate arrays
	* - Generates usefulinformation for the array/(possible)json object
	* - returns the map
	*/
	public function build($rows, $cols, $reserved) {
		$this->rows = $rows;
		$this->cols = $cols;

		$this->map_data["front_center"] = null;
		$this->map_data["col_total"] = null;
		$this->map_data["available_total"] = null;
		$this->map_data["reserved_total"] = null;
		$this->map_data["seats_total"] = null;

		for($i=0;$i<$rows;$i++) {
			for($x=0;$x<$cols;$x++) {
				if(in_array("R".($i+1)."C".($x+1),$reserved)) {
					$reserved_seats[] = "R".($i+1)."C".($x+1);
					$this->map_data[$i]["reserved"] = $reserved_seats;
					$reserved_total++;
				}else{
					$available_seats[] = "R".($i+1)."C".($x+1);
					$this->map_data[$i]["available"] = $available_seats;
					$available_total++;
				}
			}
			$available_seats = array();
			$reserved_seats = array();
		}

		$this->map_data["front_center"] = round(($cols/2),0, PHP_ROUND_HALF_UP);
		$this->map_data["col_total"] = $cols;
		$this->map_data["available_total"] = $available_total;
		$this->map_data["reserved_total"] = $reserved_total;
		$this->map_data["seats_total"] = ($rows * $cols);

		return $this->map_data;
	}


	/**
	* reserve
	* - Stores old reservations for rebuild
	* - Formats the seat names for parsing in the
	* - Calculates the manhattan distance
	* - Forwards the data to the reserve_seats handler
	* - Merges old reservations
	* - Rebuilds the seating map
	*/
	public function reserve($map, $num) {
		$seats_available = array();
		$seats_reserved = array();
		$seats_reserved_list = array();
		$seats_distance = array();
		$reserve_list = array();

		for($i=0;$i<$this->rows;$i++) {
			$seats_reserved = $map[$i]["reserved"];
			$seats_available = $map[$i]["available"];

			if(!empty($seats_reserved)) {
				foreach($seats_reserved as $seat_id) {
					$seats_reserved_list[] = $seat_id;
				}
			}

			if(!empty($seats_available)) {
				foreach($seats_available as $seat_id) {
					$sid_split = explode(".", str_replace(array("R", "C"), ".", $seat_id));
					$distance = (abs(1 - $sid_split[1]) + abs($map["front_center"] - $sid_split[2]));
					$seats_distance[$i][$sid_split[2]] = $distance;
				}
			}

		}

		foreach($seats_distance as $row => $distance) {
			$seats_new = $this->reserve_seats($row,$distance,$num);
			if($seats_new) break;
		}

		if(isset($seats_new)) {
			$reserve_list = array_merge($seats_new, $seats_reserved_list);
			$map = $this->build($this->rows, $this->cols, $reserve_list);

			return $map;

		} else {

			echo "not available\n";
			return $map;
		}
	}

	/**
	* reserve_seats
	* - Creates a new data array that handles 3 key parts:
	* - Finds the best Manhattan Distance seat
	* - Ensures the seats are contiguous
	* - Reformats for viewing
	*/
	private function reserve_seats($row,$distance,$num) {
		$min = min($distance);
		foreach($distance as $index => $dist) {
			if($dist == $min){
				$before=0;
				$after=0;

				for($i=1;$i<$num;$i++) {
					if(array_key_exists($index + $i,$distance)){
						$after++;
					}else{
						break;
					}
				}
				for($i=1;$i<$num;$i++) {
					if(array_key_exists($index - $i,$distance)) {
						$before++;
					}else{
						break;
					}
				}
				if(($before + $after+1)>=$num) {
					$seat_id_res = array();
					for($i=0;$i<$num;$i++) {
						$seat_id_res[]= "R".($row+1)."C".($index - $before + $i);
					}
					return $seat_id_res;
				}
			}
		}

		return null;
	}

}
?>
