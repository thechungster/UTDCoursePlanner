<?php
//calendarGenerator.php
//generates an html calendar based on an array of courses
//

include_once('timeslot.php');

function generateCalendar($courseArr, $id){
	$returnStr = '<table id="table'. $id .'" class="calendar">
    <thead>
        <tr>
            <th>&nbsp;</th>
            <th width="20%">Monday</th>
            <th width="20%">Tuesday</th>
            <th width="20%">Wednesday</th>
            <th width="20%">Thursday</th>
            <th width="20%">Friday</th>
        </tr>
    </thead>
    <tbody>';
    $hour = 8;
	$min = 0;
	$rowsBeingUsed = array(-1,-1,-1,-1,-1); //represents values for whether the current row is in use (if class is happening)
	$currentClass = array(NULL,NULL,NULL,NULL,NULL);
    while($hour < 22){
    	$returnStr .= '
    		<tr>
			<td>'.($hour<10?"0":'').$hour.":".($min==0?"0":'').$min.'</td>'; //generate leading time for the rows
		for($x = 0; $x < 5; $x++){
			if($rowsBeingUsed[$x] >= 0) $rowsBeingUsed[$x] -= 1; //update the class happening variable

			//$x represents the current day of the week
			$c = doesClassStartAtTime($x, $hour, $min, $courseArr);
			if($c){
				$currentClass[$x] = $c;
				$rowsNeeded = calculateNumberOfRowsForClass($c, $x);
				$rowsBeingUsed[$x] = $rowsNeeded-1; //take into account the current iteration
    			$returnStr .= '
					<td class=" has-events" rowspan="'. ($rowsNeeded) .'" onclick="openNewTab(\''.$c->getClassURL().'\')"' .(($c->classIsOpen)?"":'style="background-color:red;"'). '>
                    	<span class="title">'.$c->classSection . " | " . $c->classRoom	.'</span> <span class="lecturer"><a href="' . $c->getClassURL() . '" target="_blank">'.$c->classInstructor.'</a></span> <span class="location">'.$c->getClasstime().'</span>
            	</td>';
			}
    		else if($rowsBeingUsed[$x] == -1){
				$returnStr .= '<td class=" no-events" rowspan="1"><span style=\'width:0px;\'></span></td>'; //add the span to show empty spaces on all browsers
				$currentClass[$x] = NULL;
			}
    	}
    	$returnStr .= '</tr>';
    	$min += 30;
    	if($min == 60){
    		$min = 0;
    		$hour++;
    	}
    }
    $returnStr .= '
    	</tbody>
		</table>';
	return $returnStr;
}

//no longer being used
function isClassHappening($day, $hour, $min, $courseArr){
	foreach($courseArr as $c){
		$time = $c->getTimeslotForDay($day);
		if($time == false) continue;
		if($time->doesTimeConflict($day, new time($hour, $min)) === true)
			return true;
	}
	return false;
}

function doesClassStartAtTime($day, $hour, $min, $courseArr){
	foreach($courseArr as $c){
		$time = $c->getTimeslotForDay($day);
		if($time === false) continue;
		if($time->startTime->hour === $hour && (abs($time->startTime->min - $min) < 16))
			return $c;
    }
    return 0;
}

//determines if the current class will need a half row at the end of the timeblock on the calendar
function doesClassNeedHalfRowEnding($class, $day){
	$t = $class->getTimeslotForDay($day)->endTime->toInteger();
	return $t % 50 != 0;
}

function calculateNumberOfRowsForClass($class, $day){
	$timeslot = $class->getTimeslotForDay($day);
	$totalClassTime = $timeslot->endTime->toInteger() - $timeslot->startTime->toInteger();
	return ceil($totalClassTime / 50); //50 is 30 mins after converted to integer
}

?>
