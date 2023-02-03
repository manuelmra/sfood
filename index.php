<?php 

function getDays($stringDays) {
    $separator01 = "/";
    $separator02 = "-";

    $daysOfWeek = array(
        'Mo',
        'Tu',
        'We',
        'Th',
        'Fr',
        'Sa',
        'Su',
    );

    $postion01 = strpos($stringDays, $separator01);
    $postion02 = strpos($stringDays, $separator02);

    if (!($postion01 || $postion02)) {
        return array();
    }

    if($postion01){
        $daysArray = explode($separator01, $stringDays);
    }

    if($postion02){
        $daysArray = explode($separator02, $stringDays);
        $firstDayIndex = array_search($daysArray[0], $daysOfWeek);
        $lastDayIndex = array_search($daysArray[1], $daysOfWeek);
        if ($firstDayIndex < $lastDayIndex) {
            $daysArray = array_slice($daysOfWeek, $firstDayIndex, $lastDayIndex);
        } else {
            $firstArray = array_slice($daysOfWeek, $firstDayIndex);
            $secondArray = array_slice($daysOfWeek, 0, $lastDayIndex + 1);
            $daysArray = array_merge($firstArray, $secondArray);                        
        }
    }
    $daysArrayByName = convertNumberDayToName($daysArray);
    return $daysArrayByName;

}

function getDaySchedule($stringTime){
    $hoursArray = explode('-', $stringTime);
    $firstHourString = $hoursArray[0];
    $lastHourString = $hoursArray[1];

    $firstHourSuffix = substr($hoursArray[0], -2);
    $initialHour = intval(substr($firstHourString, 0, strlen($firstHourString) - 2));
    $initialHour = (($initialHour == 12) && ($firstHourSuffix == 'PM')) ? 0 : $initialHour;
    $firstHour = $initialHour + getHoursToAdd($firstHourSuffix);

    $lastHourSuffix = substr($hoursArray[1], -2);
    $lastHour = intval(substr($lastHourString, 0, strlen($lastHourString) - 2));
    $lastHour = (($lastHour == 12) && ($lastHourSuffix == 'PM')) ? 0 : $lastHour;
    $endHour = $lastHour + getHoursToAdd($lastHourSuffix);

    $hoursRange = getHoursArray($firstHour, $endHour);

    return $hoursRange;
}

function getAllDaySchedule($stringDayTime){
    $stringDayTimeArray = explode("/", $stringDayTime);
    $allDaySchedule = array();
    foreach($stringDayTimeArray as $oneDayString) {
        $oneStringArray = getDaySchedule($oneDayString);
        $allDaySchedule = mergeRangeSchedule($oneStringArray, $allDaySchedule);
    }
    return $allDaySchedule;
}

function mergeDaysSchedule($newDaysSchedule, $currentDaysSchedule){
    $currentSchedule = $currentDaysSchedule;
    foreach($newDaysSchedule as $key => $oneDaySchedule) {
        if (!(array_key_exists($key, $currentSchedule))){
            $currentSchedule[$key] = $oneDaySchedule;
        } else {
            // Add only hours not registered
            foreach($oneDaySchedule as $hourKey => $value) {
                if (!(array_key_exists($hourKey, $currentSchedule[$key]))){
                    $currentSchedule[$key][$hourKey] =  1;
                }
            }
        }
    }
}

function assignScheduleToDays($days, $schedule){
    $daysSchedule = $days;
    foreach($daysSchedule as $key => $value) {
        $daysSchedule[$key] = $schedule;
    }
    return $daysSchedule;
}

function mergeRangeSchedule($newRange, $currentRange){
    $resultRange = $currentRange;
    foreach($newRange as $key => $oneHour) {
        if (!(array_key_exists($key, $resultRange))){
            $resultRange[$key] = 1;
        }
    }

    ksort($resultRange);
    return $resultRange;
}

function getHoursArray($firstHour, $secondHour){
    $firstHourDay = 0;
    $lastHourDay = 23;
    $hours = array();
    if ($firstHour < $secondHour) {
        for($i = $firstHour; $i < $secondHour; $i++){
            $hours[$i] = 1;
        }
    } else {
        for($i = $firstHour; $i <= $lastHourDay; $i++){
            $hours[$i] = 1;
        }
        for($i = $firstHourDay; $i < $secondHour; $i++){
            $hours[$i] = 1;
        }
    }
    ksort($hours);
    return $hours;
}

function getHoursToAdd($stringHour){
    if ($stringHour == 'PM') {
        return 12;
    } else {
        return 0;
    }
}

function convertNumberDayToName($dayNumbers){
    $daysOfWeek = array(
        'Mo',
        'Tu',
        'We',
        'Th',
        'Fr',
        'Sa',
        'Su',
    );
    $dayIndex = array();
    $dayNames = array();
    foreach($dayNumbers as $key => $dayName ){
        $index = array_search($dayName, $daysOfWeek);
        $dayIndex[] = $index;
    }
    sort($dayIndex);
    foreach($dayIndex as $index){
        $name = $daysOfWeek[$index];
        $dayNames[$name] = array();
    }
    return $dayNames;
}

$stringDays = "Fr-Mo:12PM-4PM;Tu/Th:3PM-8PM";
$stringDays = "Fr-Mo:12PM-4PM/6PM-8PM;Tu/Fr:3PM-8PM";
//$stringDays = "Fr-Mo:12PM-4PM;Tu/Th:8PM-3AM";
//$stringDays ="Mo-Fr:7AM-8AM/10AM-11AM/12PM-1PM";
//$stringDays ="Mo-Fr:4AM-8AM/7AM-1PM/9PM-1AM";

$currentDays = array();
$lineDaySchedule = explode(';', $stringDays);

echo $stringDays ; 

$allDaysSchedule = array();
foreach($lineDaySchedule as $lineDay) {
    $dayString = explode(':', $lineDay);
    $newDays = getDays($dayString[0]);
    $allDaySchedule= getAllDaySchedule($dayString[1]);
    $newDaysWithSchedule = assignScheduleToDays($newDays, $allDaySchedule);
    mergeDaysSchedule($newDaysWithSchedule, $allDaysSchedule);
    echo '<pre>';
    print_r($newDaysWithSchedule);
    echo '</pre>';
}

// echo '<pre>';
// print_r($newDays);
// echo '</pre>';

?>