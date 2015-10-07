<?php
############################################
##            LICENSE AFL3.0              ##
## Copyright (c) 2015-2016 Nicola Bombaci ##
############################################

class swam{
	private	$next;
	private $workit;
	public  $printer;
	public 	$temp;
	public  $file;
	public 	$debug_mode = null;

	function __construct($workit, $file){
		$this->debug_mode = $workit->debug_mode;
		$this->workit = $workit;
		$this->temp   = fopen($file, 'a+');
		fwrite($this->temp,"<?php\n");
	}

	private function check_on($row,$i){
		$debug_mode = $this->debug_mode;
		$check 		= $this->workit->get_string_between($row," "," ");
		if($debug_mode){
			echo "Control ON <br>";
			echo "Check Line '".$row."'<br>";
			echo "Check Value '".$check."'<br>";
		}
		if($check ==  "on"){
			$this->workit->line[$i][0]	=  	$this->workit->delete_first_tag($row," on");
			if($debug_mode)	echo "<b>Control Passed</b><br><hr>";

			return 1;
		}
		else{
			if($debug_mode)	echo "<b>Control Not Passed</b><br><hr>";

			return 0;
		}
	}

	private function check_in($row,$i){
		$debug_mode = $this->debug_mode;
		$check 		= $this->workit->get_string_between($row," "," ");
		if($debug_mode){
			echo "Control IN <br>";
			echo "Check Line '".$row."'<br>";
			echo "Check Value '".$check."'<br>";
		}
		if($check ==  "in"){
			$this->workit->line[$i][0]	=  $this->workit->delete_first_tag($row," in");
			if($debug_mode)	echo "<b>Control Passed</b><br><hr>";

			return 1;
		}
		else{
			if($debug_mode)	echo "<b>Control Not Passed</b><br><hr>";

			return 0;
		}
	}

	private function check($row,$i){
		if($this->check_on($row,$i))
			return $this->on_read($i);
		else if($this->check_in($row,$i))
			return $this->in_read($i);
		else
			return 1;
	}

	public function read(){
		$lenght 	= $this->workit->lenght;
		$line  		= $this->workit->line;
		$debug_mode = $this->debug_mode;

		for($i = 0; $i < $lenght-1 ; $i++){
			#echo "Checking the line ".($i+1)." Containing ".($line[$i][0])."<br>";
			$line[$i][0] = str_replace("\n", "", $line[$i][0]);
			$this->workit->line[$i][0] = " ".$line[$i][0]." ";
			if($debug_mode)	echo "$i - Setting Array '".$this->workit->line[$i][0]."'<br>";
		}
		if($debug_mode)	echo "<hr>";

		return $this->check($this->workit->line[0][0],0);
	}

	private function on_read($i){
		$lenght 	= $this->workit->lenght;
		$line   	= $this->workit->line;
		$debug_mode = $this->debug_mode;

		//Current line
		$current		=	$this->workit->line[$i][0];
		//Current name of tag
		$spoiler 		=	$this->workit->get_string_between($current," "," ");
		//Open tag
		$this->printer .= "echo '<";
		//Insert name of tag
		$this->printer .=  	$spoiler;
		if($debug_mode)	echo "<b>Opening $spoiler</b><br>";

		//Extract the tag and change previous variable
		$this->workit->line[$i][0] 		=	$this->workit->delete_first_tag($current," ".$spoiler);
		$cur_pos						            =	$this->workit->line[$i][1];	//Save positions
		//Print the element inside line after the tag
		$this->detail_read($i);
		//Close Tag
		$this->printer	.=	">';\n";
		//Update $this->next value
		$this->next 	=	$i+1;
		if($debug_mode){
			echo "Next Value: ".$this->next;
			echo "<br>Index Value: $i<br>";
		}
		//Check next line
		if (($this->next) < ($lenght-1)){
			while (($line[$this->next][1]) > $cur_pos) {
				if($debug_mode)	echo "Major Values of <b>$spoiler</b><br><hr>";

				if($this->check($this->workit->line[$this->next][0],$this->next)) ;
				else if($debug_mode)	echo "<b>Reading on line $this->next</b><br>";
			}
			if (($line[$this->next][1]) <= $cur_pos) {
				if($debug_mode)	echo "Equal or Min Values - <b>Closing $spoiler</b><br><hr>";

				$this->printer	.=	"echo '</$spoiler>';\n";
				fwrite($this->temp,$this->printer);
				$this->printer  =	"";
				return 0;
			}
		}
		else{
			if($debug_mode)	echo "<b>Last Line - Closing $spoiler</b><br><hr>";

			$this->printer	.=	"echo '</$spoiler>';\n";
			fwrite($this->temp, $this->printer);
			fclose($this->temp);
			return 1;
		}
	}

	private function in_read($i){
		$lenght 	= $this->workit->lenght;
		$line   	= $this->workit->line;
		$debug_mode = $this->debug_mode;

		//Current line
		$current		=	$this->workit->line[$i][0];
		//Current name of tag
		$spoiler 		=	$this->workit->get_string_between($current," "," ");
		//Open tag
		switch ($spoiler) {
			case 'php':
				break;

			default:
				$this->printer .= "echo '<";
				//Insert name of tag
				$this->printer .=  	$spoiler;
				break;
		}

		if($debug_mode)	echo "<b>Opening $spoiler</b><br>";

		//Extract the tag and change previous variable
		$this->workit->line[$i][0] 		=	$this->workit->delete_first_tag($current," ".$spoiler);
		$cur_pos						=	$this->workit->line[$i][1];	//Save position
		//Print the element inside line after the tag
		$this->detail_read($i);
		//Close Tag
		if ($spoiler == 'php') ;
		else $this->printer	.=	">";
		//Update $this->next value
		$this->next 	=	$i+1;
		if($debug_mode){
			echo "Next Value: ".$this->next;
			echo "<br>Index Value: $i<br>";
		}
		//Check next line
		if (($this->next) < ($lenght-1)){

			while (((($line[$this->next][1]) - $cur_pos) == 1 ) && ($this->check($line[$this->next][0],$this->next) == 1)) {
				if($debug_mode)	echo "Next line is a content of <b>$spoiler</b><br><hr>";
				//Reading the content inside the tag in
				$this->content_read($this->next);
				if($debug_mode)	echo "<b>Reading content on line $this->next</b><br>";

				$this->next++;
			}
			if ((($line[$this->next][1]) <= $cur_pos) || ((($line[$this->next][1]) - $cur_pos) > 1 )) {
				if($debug_mode)	echo "Out of <b>$spoiler</b> tag - <b>Closing $spoiler</b><br><hr>";

				if ($spoiler == 'php') ;
				else $this->printer	.=	"</$spoiler>';\n";
				fwrite($this->temp,$this->printer);
				$this->printer  =	"";
				return 0;
			}
		}
		else{
			if($debug_mode)	echo "<b>Last Line - Closing $spoiler</b><br><hr>";

			if ($spoiler == 'php') ;
			else $this->printer	.=	"</$spoiler>';\n";
			fwrite($this->temp,$this->printer);
			fclose($this->temp);
			return 1;
		}
	}
	//This function read the content
	//Is used for in tag
	private function content_read($i){
		//$current is the line passed
		$current	= $this->workit->line[$i][0];
		//fixing the escape chars
		$current	=	$this->escape_str($current);
		//removing " " after and before $current
		$current 	= trim($current, " ");
		//if $current is empty don't print anything
		if ($current == '') ;
		//else print $current and its content
		else $this->special_str($current);
		return 0;
	}
	//This function read the details inside a tag
	//Is used for on tag
	private function detail_read($i){
		$line   	=   $this->workit->line;
		$current	=   $line[$i][0];
        while($current) {
            $element    = $this->workit->get_string_between($current, " ", " ");
            $current 	= $this->workit->delete_first_tag($current, " " . $element);
            $sign       = $element[0];
            switch($sign){
                case '$':
                    $this->printer = $this->printer . "'.".$element.".'";
                    break;
                case '#':
                    $element       = substr($element, 1);
                    $this->printer = $this->printer . " id=\"".$element."\"";
                    break;
                case '@':
                    $element       = substr($element, 1);
                    $this->printer = $this->printer . " class=\"".$element."\"";
                    break;
                default:
                    if(strlen($element)>1) {
                        $this->printer = $this->printer . " " . $element;
                    }
                    else{
                        $this->printer = $this->printer .  $element;
                    }
                    break;
            }
        }
		return 0;
	}
	//This function prevent to escape dangerous chars
	private function escape_str($string) {
		$string = str_replace("'", "\'", $string);
		return $string;
	}
	//This function find special tag inside a content
	private function special_str($string) {
		$tok 	=	strtok($string, " ");
		while ($tok !== false) {
			$spec = $tok{0};
			$tag	=	substr($tok, 1 , strlen($tok) - 1);
			switch ($spec) {
				case '|':
					$this->printer  =   $this->printer." <".$tag." ";
					$tok = strtok(" ");

					while ($tok{0} != "[") {
						$this->printer  =   $this->printer.$tok." ";
						$tok = strtok(" ");
					}
					//If there's only one word open and close in one step
					if ($tok{0} == "[" &&  $tok{strlen($tok) - 1} == "]" ) {
						$this->printer  =   $this->printer.">".substr($tok, 1, strlen($tok) - 2);
					}
					//If there are more than one words concat them
					else {
						$this->printer  =   $this->printer.">".substr($tok, 1 , strlen($tok) - 1)." ";
						$tok = strtok(" ");

						while ($tok{strlen($tok) - 1} != "]") {
							$this->printer  =   $this->printer.$tok." ";
							$tok = strtok(" ");
						}
						$this->printer	=		$this->printer.substr($tok, 0 , strlen($tok) - 1);
					}
					$this->printer  =   $this->printer."</".$tag."> ";
					break;
				default:
					$this->printer  =   $this->printer.$tok." ";
					break;
			}
			$tok = strtok(" ");
		}
		return 0;
	}
}
