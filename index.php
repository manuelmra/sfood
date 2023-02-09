<?php 

const EMPTY_ARRAY = [];
const SEPARATOR_SEMICOLON = ';';
const SEPARATOR_COLON = ':';

spl_autoload_register(function ($className) {
    include 'src/' . $className . '.php';
});

//$stringDays = "Fr-Mo:12PM-4PM;Tu/Th:3PM-8PM";
//$stringDays = "Fr-Mo:12PM-4PM/6PM-8PM;Tu/Fr:3PM-8PM";
//$stringDays = "Fr-Mo:12PM-4PM;Tu/Th:8PM-3AM";
//$stringDays ="Mo-Fr:7AM-8AM/10AM-11AM/12PM-1PM";
//$stringDays ="Mo-Fr:4AM-8AM/7AM-1PM/9PM-1AM";
$stringDays = "Tu/We/Th:12AM-3AM;Mo-We:12PM-12AM";


$currentDays = EMPTY_ARRAY;
$lineDaySchedule = explode(SEPARATOR_SEMICOLON, $stringDays);

echo $stringDays ; 

$allDaysSchedule = EMPTY_ARRAY;
foreach($lineDaySchedule as $lineDay) {
    $dayString = explode(SEPARATOR_COLON, $lineDay);
    $newDays = Scheduler::getDays($dayString[0]);
    $allDaySchedule= Scheduler::getScheduleOfAllDay($dayString[1]);
    $newDaysWithSchedule = Scheduler::assignScheduleToDays($newDays, $allDaySchedule);
    $allDaysSchedule = Scheduler::mergeDaysSchedule($newDaysWithSchedule, $allDaysSchedule);
}
$finalSchedule = Scheduler::orderFinalScheduleByDay($allDaysSchedule);
echo '<pre>aaaa';
print_r($finalSchedule);
echo '</pre>bbb';
