<?php
## LICENSE AFL3.0 # Copyright (c) 2015-2016 Nicola Bombaci ##
$debug=false;$swam=new SWcompile($debug);class SWcompile{private $workit;private $debug;function __construct($debug){$this->workit=new workit($debug);;}function parse($input){$this->workit->tokenize($input);try{$parser=new swam($this->workit);$parser->read();return $parser->printer;}catch(Exception $e){echo "Error :".$e;}}}class workit{public $line=array();private $token="\r\n";public $lenght;public $debug_mode=null;function __construct($debug){$this->debug_mode=$debug;}public function get_string_between($string,$start,$end){$ini=strpos($string,$start);$ini+=strlen($start);$len=strpos($string,$end,$ini)- $ini;return substr($string,$ini,$len);}public function delete_first_tag($string,$start){$ini=strpos($string,$start);$ini+=strlen($start);return substr($string,$ini);}public function tokenize($string){$debug_mode=$this->debug_mode;$a=0;$tok=strtok($string,$this->token);while($tok){if($debug_mode)echo "<b>token '".$tok."'</b><br>";$count=null;preg_replace('/(\\/\\/+)/i','',$tok,-1,$count);if($count<1){$this->line[$a][0]=preg_replace('/[\\t]/','',$tok,-1,$count);$this->line[$a][1]=$count;$this->line[$a][2]=count(str_word_count($this->line[$a][0],1,"0..9"));if($debug_mode)echo $this->line[$a][1]." Word = '".$this->line[$a][0]."' <br>";$a++;}$string=$this->delete_first_tag($string,$tok);$tok=strtok($this->token);}$this->lenght=count($this->line);if($debug_mode)echo "<hr>Lenght(Array) = ".$this->lenght."<hr>";}}class swam{private $next=0;private $equal=0;private $tag="on";private $workit;public $printer;public $debug_mode=null;function __construct($workit){$this->workit=$workit;$this->debug_mode=$workit->debug_mode;}function start(){while(($this->next)<($this->workit->lenght - 1))$this->check($this->workit->line[$this->next][0],$this->next);}public function read(){$lenght=$this->workit->lenght;$debug_mode=$this->debug_mode;for($i=0;$i<$lenght;$i++){$this->workit->line[$i][0]=" ".str_replace("\n","",$this->workit->line[$i][0])." ";if($debug_mode)echo "$i - Setting Array '".$this->workit->line[$i][0]."'<br>";}if($debug_mode)echo "<hr>";$this->start();}private function check($row,$i){if($this->check_tag($row,$i))return $this->tag_read($i);else{$this->auto_read($i,false);if($this->debug_mode)echo "<b>Reading content on line $this->next</b><hr>";$this->equal++;$this->next++;}}private function check_tag($row,$i){$debug_mode=$this->debug_mode;$check=$this->workit->get_string_between($row," "," ");if($debug_mode){echo "Control ON <br>";echo "Check Line '".$row."'<br>";echo "Check Value '".$check."'<br>";}if($check==$this->tag){$this->workit->line[$i][0]=$this->workit->delete_first_tag($row," on");if($debug_mode)echo "<b>Control Passed</b><br><hr>";return true;}else{if($debug_mode)echo "<b>Control Not Passed</b><br><hr>";return false;}}private function tag_read($i){$debug_mode=$this->debug_mode;$current=$this->workit->line[$i][0];$spoiler=$this->workit->get_string_between($current," "," ");$this->printer.=" <".$spoiler." ";if($debug_mode)echo "<b>Opening $spoiler</b><br>";$this->workit->line[$i][0]=$this->workit->delete_first_tag($current," ".$spoiler);$cur_pos=$this->workit->line[$i][1];$this->auto_read($i,true);$this->printer.=">";$this->next++;if($debug_mode){echo "Next Value: ".$this->next;echo "<br>Index Value: $i<br>";}while(($this->workit->line[$this->next][1]>$cur_pos)&&($this->next<$this->workit->lenght)){if($debug_mode)echo "Major Values of <b>$spoiler</b><br><hr>";if($this->equal>0)$this->printer.=" ";$this->check($this->workit->line[$this->next][0],$this->next);if(!isset($this->workit->line[$this->next][0]))break;}if($debug_mode)echo "Equal or Min Position - <b>Closing $spoiler</b><br><hr>";$this->printer.="</$spoiler> ";$this->equal=0;}private function auto_read($pos,$head){$string=$this->workit->line[$pos][0];$end=$this->workit->line[$pos][2];$start=1;$element=strtok($string," ");while($element!==false){$content=$this->fast_attributes($element,$head);if($start==$end){$this->printer.=$content;}else{$this->printer.=$content." ";}$start++;$element=strtok(" ");}}private function fast_attributes($string,$head){$sign=$string{0};if($head){switch($sign){case '#':$string=substr($string,1);return "id=\"".$string."\"";case '.':$return="class=\"";$e=strtok($string,".");while($e!==false){$return.=$e." ";$e=strtok(".");}$return=substr($return,0,-1);$return.="\"";return $return;}}return $string;}}
