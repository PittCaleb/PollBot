<?PHP

$behind = 999;

function showhelp() {
	echo "PollBot Usage:\n";
	echo "  PollBot  PollID  UniqueString\n";
	echo "\nExample:\n";
	echo "  PollBot  9277348  Cohen\n";
}

function FindID ($base, $poll, $searchname) {
	$preurl = $base.$poll;
	$predata = file_get_contents($preurl);
	$mystart = strpos($predata, $searchname);
	$idstart = strrpos(substr($predata,0,$mystart) , "value=");
	return substr($predata,$idstart+7,8);
}

function getrank ($base, $poll, $myguy, $myname) {

	$preurl = $base.$poll."/?view=results";
	$predata = file_get_contents($preurl);
	$labelstart = 'class="results"';
	$resultstart = strpos($predata, $labelstart);
	
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
		
	$myindex = 0;
	for ($i = 0; $i < $entries; $i++) {
		if (strpos($names[$i],$myname)) {
			$myindex = $i;
		}
	}
	$diff = $votes[$entries-1] - $votes[$myindex];
	echo date('h:i:s A')." - $myname has $votes[$myindex] votes and needs $diff votes to catch ".$votes[$entries-1]." votes (".$names[$entries-1].")\n";
	$GLOBALS['behind'] = $votes[$myindex] - $votes[$myindex-1];
	echo "              $myname has $votes[$myindex] votes and is ".$GLOBALS['behind']." votes ahead of ".$votes[$myindex-1]." votes (".$names[$myindex-1].")\n";

	return $diff;
}

//print_r($argv); echo sizeof($argv); echo "\n\n\n";  
	
if (sizeof($argv) != 3) {
	echo "Incorrect number of arguments passed\n\n";
	showhelp();
	exit();
}

if ((sizeof($argv) == 1) || ((isset($argv[1])) && ($argv[1] == "help"))) {
	showhelp();
	exit();
}

$pollid = $argv[1];
$myname = $argv[2];
$baseurl = "https://polldaddy.com/poll/";
$votetimes = 10;
$timeout = 20;

$voting_id = FindID($baseurl, $pollid, $myname);

$votescript = 'py polldaddy_vote.py '.$pollid.' '.$voting_id.' '.$votetimes.' '.$timeout;

set_time_limit(0);

while(true){
	$votesneeded = getrank($baseurl, $pollid, $voting_id, $myname);
	if ($votesneeded > 10) {
		echo "              Keeping up with first:    Voting 10 times, please wait...\n";
		$output = shell_exec($votescript);
		echo $output;
	}
	elseif ($behind < 100) {
		echo "              Staying away from behind: Voting 10 times, please wait...\n";
		$output = shell_exec($votescript);
		echo $output;
	} else {
		echo "              Sleeping 10 minutes...\n";
		sleep(600);
	}
	sleep(3);
	echo "Checking standing again...\n";
}
?>