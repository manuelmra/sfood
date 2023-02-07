<?php 

const WEEK_DAYS = ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'];
const SEPARATOR_DASH = '-';
const SEPARATOR_SLASH = '/';
const SEPARATOR_COLON = ':';
const SEPARATOR_SEMICOLON = ';';
const SEPARATOR_AM = 'AM';
const SEPARATOR_PM = 'PM';
const EMPTY_ARRAY = [];
const INITIAL_START_HOUR_OF_DAY = 0;
const LAST_START_HOUR_OF_DAY = 23;
const MIDDAY_HOUR = 12;
const ACTIVE_HOUR = 1;

function getDays($daysString) {

    $postion01 = strpos($daysString, SEPARATOR_SLASH);
    $postion02 = strpos($daysString, SEPARATOR_DASH);

    if (!($postion01 || $postion02)) {
        return EMPTY_ARRAY;
    }

    if($postion01){
        $daysArray = explode(SEPARATOR_SLASH, $daysString);
    }

    if($postion02){
        $daysArray = explode(SEPARATOR_DASH, $daysString);
        $firstDayIndex = array_search($daysArray[0], WEEK_DAYS);
        $lastDayIndex = array_search($daysArray[1], WEEK_DAYS);
        if ($firstDayIndex < $lastDayIndex) {
            $daysArray = array_slice(WEEK_DAYS, $firstDayIndex, $lastDayIndex);
        } else {
            $firstArray = array_slice(WEEK_DAYS, $firstDayIndex);
            $secondArray = array_slice(WEEK_DAYS, 0, $lastDayIndex + 1);
            $daysArray = array_merge($firstArray, $secondArray);                        
        }
    }
    $daysArrayByName = convertNumberDayToName($daysArray);
    return $daysArrayByName;

}

function getDaySchedule($timeString){
    $hoursArray = explode(SEPARATOR_DASH, $timeString);
    $firstHourString = $hoursArray[0];
    $lastHourString = $hoursArray[1];

    $firstHourSuffix = substr($hoursArray[0], -2);

    $firstHour = intval(substr($firstHourString, 0, strlen($firstHourString) - 2));
    $firstHour = (($firstHour == MIDDAY_HOUR) && ($firstHourSuffix == SEPARATOR_PM)) ? 0 : $firstHour;
    $initialHour = $firstHour + getHoursToAdd($firstHourSuffix);


    $lastHourSuffix = substr($hoursArray[1], -2);
    $lastHour = intval(substr($lastHourString, 0, strlen($lastHourString) - 2));
    $lastHour = (($lastHour == MIDDAY_HOUR) && ($lastHourSuffix == SEPARATOR_PM)) ? 0 : $lastHour;
    $endHour = $lastHour + getHoursToAdd($lastHourSuffix);

    $hoursRange = getHoursArray($initialHour, $endHour);

    return $hoursRange;
}

function getAllDaySchedule($stringDayTime){
    $stringDayTimeArray = explode(SEPARATOR_SLASH, $stringDayTime);
    $allDaySchedule = EMPTY_ARRAY;
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
                    $currentSchedule[$key][$hourKey] =  ACTIVE_HOUR;
                }
            }
        }
    }
    ksort($currentSchedule[$key]);



    
    return $currentSchedule;
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
            $resultRange[$key] = ACTIVE_HOUR;
        }
    }

    ksort($resultRange);
    return $resultRange;
}

function getHoursArray($firstHour, $secondHour){
    $hours = EMPTY_ARRAY;
    if ($firstHour < $secondHour) {
        for($i = $firstHour; $i < $secondHour; $i++){
            $hours[$i] = ACTIVE_HOUR;
        }
    } else {
        for($i = $firstHour; $i <= LAST_START_HOUR_OF_DAY; $i++){
            $hours[$i] = ACTIVE_HOUR;
        }
        for($i = INITIAL_START_HOUR_OF_DAY; $i < $secondHour; $i++){
            $hours[$i] = ACTIVE_HOUR;
        }
    }
    ksort($hours);
    return $hours;
}

function getHoursToAdd($separator){
    if ($separator == SEPARATOR_PM) {
        return 12;
    } else {
        return 0;
    }
}

function convertNumberDayToName($dayNumbers){

    $dayIndex = EMPTY_ARRAY;
    $dayNames = EMPTY_ARRAY;
    foreach($dayNumbers as $key => $dayName ){
        $index = array_search($dayName, WEEK_DAYS);
        $dayIndex[] = $index;
    }
    sort($dayIndex);
    foreach($dayIndex as $index){
        $name = WEEK_DAYS[$index];
        $dayNames[$name] = EMPTY_ARRAY;
    }
    return $dayNames;
}

$stringDays = "Fr-Mo:12PM-4PM;Tu/Th:3PM-8PM";
$stringDays = "Fr-Mo:12PM-4PM/6PM-8PM;Tu/Fr:3PM-8PM";
//$stringDays = "Fr-Mo:12PM-4PM;Tu/Th:8PM-3AM";
//$stringDays ="Mo-Fr:7AM-8AM/10AM-11AM/12PM-1PM";
//$stringDays ="Mo-Fr:4AM-8AM/7AM-1PM/9PM-1AM";

$currentDays = EMPTY_ARRAY;
$lineDaySchedule = explode(SEPARATOR_SEMICOLON, $stringDays);

echo $stringDays ; 

$allDaysSchedule = EMPTY_ARRAY;
foreach($lineDaySchedule as $lineDay) {
    $dayString = explode(SEPARATOR_COLON, $lineDay);
    $newDays = getDays($dayString[0]);
    $allDaySchedule= getAllDaySchedule($dayString[1]);
    $newDaysWithSchedule = assignScheduleToDays($newDays, $allDaySchedule);
    $allDaysSchedule = mergeDaysSchedule($newDaysWithSchedule, $allDaysSchedule);
}

echo '<pre>aaaa';
print_r($allDaysSchedule);
echo '</pre>bbb';

?>