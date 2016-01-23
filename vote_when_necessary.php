<?PHP

$behind = 999;

function getrank ($base, $poll, $myguy, $myname) {

	$preurl = $base.$poll."/?view=results";
	$predata = file_get_contents($preurl);
	
	// echo $predata;
	
	$labelstart = 'class="results"';
	
	//echo "\n|$labelstart|\n";
	
	$resultstart = strpos($predata, $labelstart);
	
	//echo "start=$resultstart \nresults start at \n".substr($predata, $resultstart,200);
	
	
	$continue = 1;
	$entries = 1;
	$offset = $resultstart;
	
	while ($continue) {
		$labelstart = strpos($predata, "class=\"label\"",$offset);
		if ($labelstart) {
			$namestart = strpos($predata, ">",$labelstart);
			$nameend = strpos($predata, "</div>",$labelstart);
			$names[$entries] = substr($predata, $namestart+1, $nameend-$namestart-1);
			
			$labelstart = strpos($predata, "class=\"votes\"",$nameend);
			$votesstart = strpos($predata, ">",$labelstart) + 1;
			$votesend = strpos($predata, " ",$votesstart);
			$votes[$entries] = str_replace(",","",substr($predata, $votesstart, $votesend-$votesstart));
			
			$offset = $votesend;
			
			//echo "Entry #$entries  Name=$names[$entries]  Votes=$votes[$entries]\n";
			
			$entries++;
		}
		else {
			$continue = 0;
		}
	}
	$entries--;
	$ar1 = array(10, 100, 100, 0);
	$ar2 = array(1, 3, 2, 4);
	array_multisort($votes, $names);
	
	// print_r($names);
	// print_r($votes);
	
	$myindex = 0;
	for ($i = 0; $i < $entries; $i++) {
		// echo "in loop, i = $i, entries = $entries\n";
		if ($names[$i] == $myname) {
			$myindex = $i;
		}
	}
	$diff = $votes[$entries-1] - $votes[$myindex];
	echo date('h:i:s A')." - $myname has $votes[$myindex] votes and needs $diff votes to catch ".$votes[$entries-1]." votes (".$names[$entries-1].")\n";
	$GLOBALS['behind'] = $votes[$myindex] - $votes[$myindex-1];
	echo "              $myname has $votes[$myindex] votes and is ".$GLOBALS['behind']." votes ahead of ".$votes[$myindex-1]." votes (".$names[$myindex-1].")\n";

	return $diff;
}

	set_time_limit(0);
	$pollid = "9277348";
	$voting_id = "42299039";
	$myname = "Josh Cohen, Scotch Plains-Fanwood";
	$baseurl = "https://polldaddy.com/poll/";

	while(true){
		$votesneeded = getrank($baseurl, $pollid, $voting_id, $myname);
		//echo "person behind = ".$behind."\n";
		if ($votesneeded > 10) {
			echo "              Keeping up with first:    Voting 10 times, please wait...\n";
			$output = shell_exec('py C:\Temp\polldaddy\vote_josh_10.py');
			//$output = shell_exec('dir');
			echo $output;
		}
		elseif ($behind < 100) {
			echo "              Staying away from behind: Voting 10 times, please wait...\n";
			$output = shell_exec('py C:\Temp\polldaddy\vote_josh_10.py');
			//$output = shell_exec('dir');
			echo $output;
		} else {
			echo "              Sleeping 10 minutes...\n";
			sleep(600);
		}
		sleep(3);
		echo "Checking standing again...\n";
	}
?>