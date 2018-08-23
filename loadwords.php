<!doctype html>
<html lang="en">
<?php

$filenames = array("2cities.txt", "3men.txt", "alice.txt", "crusoe.txt", "darkness.txt", "dracula.txt", "earnest.txt", "expectations.txt", "frankenstein.txt", "holmes.txt", "liberty.txt", "machine.txt", "middlemarch.txt", "peterrabbit.txt", "pride.txt", "screw.txt", "styles.txt", "treasure.txt", "tsawyer.txt", "worlds.txt");
$max = 10;
$int_options = array("options"=> array("min_range"=>6, "max_range"=>$max));
$found = 0;
$unique_words = array();

foreach($filenames as $filename){
	get_words($filename);
}	
$output = print_r($unique_words, true);
file_put_contents('words.txt', $output);
echo("Found: " . $found . " unique words.");

  include_once 'Database.php';
  include_once 'Word.php';
  // Instantiate DB & connect
  $database = new Database();
  $db = $database->connect();

foreach($unique_words as $unique_word){

  // Instantiate blog post object
  $word = new Word($db);
  $word->word = $unique_word;
  // Create word entry
  if($word->create()) {
    echo json_encode(
      array('message' => 'Word Created')
    );
  } else {
    echo json_encode(
      array('message' => 'Word Not Created')
    );
  }

}
  
function get_words($fname) {
	$myfile = fopen($fname, "r") or die("Unable to open file!");
	$buffer = fread($myfile,filesize($fname));
	fclose($myfile);
	
	if (strlen($buffer))										// if there is anything in the buffer
	  {
	    $words = str_word_count($buffer,1);
	    global $unique_words, $int_options, $found;
	    for($x=0;$x<count($words);$x++)
	      {
	         if (filter_var(strlen($words[$x]), FILTER_VALIDATE_INT, $int_options))                 // if word length between min and max
	           {
	             $str = strtolower($words[$x]);                                                     // convert to lower case 
	             if (preg_match("/^[a-z]+$/",$str))                                                 // only a to z ok
	               {
	                 if (!in_array($str,$unique_words))
	                   {
	                     $unique_words[] = $str;
	                     $found++;
	                    }
	               }
	           }
	      }
	  }
	else
	  {
	    die("no buffer");
	  }
}

?>