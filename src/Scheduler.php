<?php

const WEEK_DAYS = ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'];
const SEPARATOR_DASH = '-';
const SEPARATOR_SLASH = '/';
const SEPARATOR_AM = 'AM';
const SEPARATOR_PM = 'PM';
const INITIAL_START_HOUR_OF_DAY = 0;
const LAST_START_HOUR_OF_DAY = 23;
const MIDDAY_HOUR = 12;
const ACTIVE_HOUR = 1;

class Scheduler {
    static function getDays($daysString) {

        $position01 = strpos($daysString, SEPARATOR_SLASH);
        $position02 = strpos($daysString, SEPARATOR_DASH);
    
        if (!($position01 || $position02)) {
            return EMPTY_ARRAY;
        }
    
        if($position01){
            $daysArray = explode(SEPARATOR_SLASH, $daysString);
        }
    
        if($position02){
            $daysArray = explode(SEPARATOR_DASH, $daysString);
            $firstDayIndex = array_search($daysArray[0], WEEK_DAYS);
            $lastDayIndex = array_search($daysArray[1], WEEK_DAYS);
            if ($firstDayIndex < $lastDayIndex) {
                $daysArray = array_slice(WEEK_DAYS, $firstDayIndex, $lastDayIndex + 1);
            } else {
                $firstArray = array_slice(WEEK_DAYS, $firstDayIndex);
                $secondArray = array_slice(WEEK_DAYS, 0, $lastDayIndex + 1);
                $daysArray = array_merge($firstArray, $secondArray);                        
            }
        }
        $daysArrayByName = Scheduler::convertNumberDayToName($daysArray);
        return $daysArrayByName;
    
    }
   
    static function getHoursArray($firstHour, $secondHour){
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

    static function getScheduleOfAllDay($dayTimesString){
        $dayTimesStringArray = explode(SEPARATOR_SLASH, $dayTimesString);
        $scheduleOfAllDay = EMPTY_ARRAY;
        foreach($dayTimesStringArray as $oneDayTimeString) {
            $oneStringArray = Scheduler::getHoursRange($oneDayTimeString);
            $scheduleOfAllDay = Scheduler::mergeRangeSchedule($oneStringArray, $scheduleOfAllDay);
        }
        return $scheduleOfAllDay;

    }

    static function getHoursRange($timeString){
        $hoursArray = explode(SEPARATOR_DASH, $timeString);
        return Scheduler::getHoursArray(Scheduler::convertHourStringToHOur($hoursArray[0]), Scheduler::convertHourStringToHOur($hoursArray[1]));

    }

    static function convertNumberDayToName(array $dayNumbers) {

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
    
    static function assignScheduleToDays($days, $schedule){
        $daysSchedule = $days;
        foreach($daysSchedule as $key => $value) {
            $daysSchedule[$key] = $schedule;
        }
        return $daysSchedule;
    }
    
    static function convertHourStringToHOur($hourString) {
        $suffix = substr($hourString, -2);
        $hour = intval(substr($hourString, 0, strlen($hourString) - 2));
        $firstHour = ($hour == MIDDAY_HOUR) ? 0 : $hour;
        return  $firstHour + Scheduler::getHoursToAdd($suffix);
        
    }

    static function getHoursToAdd($separator){
        if ($separator == SEPARATOR_PM) {
            return 12;
        } else {
            return 0;
        }
    }

    static function mergeRangeSchedule($newRange, $currentRange){
        $resultRange = $currentRange;
        foreach($newRange as $key => $oneHour) {
            if (!(array_key_exists($key, $resultRange))){
                $resultRange[$key] = ACTIVE_HOUR;
            }
        }
    
        ksort($resultRange);
        return $resultRange;
    }

    static function mergeDaysSchedule($newDaysSchedule, $currentDaysSchedule){
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

    static function orderFinalScheduleByDay($currentSchedule){
        
        $scheduleOrdered = EMPTY_ARRAY;
        foreach(WEEK_DAYS as $dayName) {
    
             if (array_key_exists($dayName, $currentSchedule)){
                $scheduleOrdered[$dayName] = $currentSchedule[$dayName];
             }
        } 
        return $scheduleOrdered;
    }

}