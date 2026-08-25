<?php
/**
 * Extendida a classe devido ao problema de serializar a classe a versão atual do PHP no WEbMax
 */
class DateTimeCustom extends DateTime {
    private $_str;
    private $defaultFormat = "d.m.Y H:i:s";
   
   	/**
	 * creating between two date
	 * @param string since
	 * @param string until
	 * @param string step
	 * @param string date format
	 * @return array
	 * @author Ali OYGUR <alioygur@gmail.com>
	 */
	public static function dateRange($first, $last, $step = '+1 day', $format = 'd/m/Y' ) { 
	    $dates = array();
	    $current = strtotime($first);
	    $last = strtotime($last);
	
	    while( $current <= $last ) { 
	
	        $dates[] = date($format, $current);
	        $current = strtotime($step, $current);
	    }
	
	    return $dates;
	}
   
    public function __sleep(): array {
        $this->_str = $this->format($this->defaultFormat);
        return array('_str');
    }
   
    public function __wakeup(): void {
        $this->__construct($this->_str);
    }
    
    public function __toString(): string {
    	return $this->format($this->defaultFormat);
    }
    public function __construct($time = "now"){
    	date_default_timezone_set ( "America/Sao_Paulo" );
    	parent::__construct($time);
    }
    
    public function addDaysWithoutWeekends(DateTime $date, $total){
    	$daysAdded = 0;
    	while($daysAdded < $total){
    		$date->modify("+1 day");
    		$weekends = array(0, 6);
    		if(!in_array($date->format("w"), $weekends)) $daysAdded++;
    	}
    }
    
    public function getWeekendSeconds(DateTime $datetimeInit,DateTime $datetimeEnd){
    	$weekendDays = 0;
    	while($datetimeInit < $datetimeEnd){
    		if($datetimeInit->format("w")=="0") $weekendDays++;
    		elseif($datetimeInit->format("w")=="6") $weekendDays = $weekendDays + 0.5;
    		$datetimeInit->modify("+1day");
    	}
    	return $weekendDays*86400;
    }
    
    public function getDatetimeSeconds(DateTime $dateTime){
    	$seconds = mktime($dateTime->format("H"), $dateTime->format("i"), $dateTime->format("s"),
    			$dateTime->format("m"), $dateTime->format("d"), $dateTime->format("Y"));
    	return $seconds;
    }
    
    public function subtract(DateTimeCustom $finalDate, DateTimeCustom $initialDate){
    	$tempFinal = $finalDate->format("Y-m-d H:i:s");
    	$tempInitial = $initialDate->format("Y-m-d H:i:s");
    	
    	$finalSeconds = strtotime($tempFinal);
    	$initialSeconds = strtotime($tempInitial);
    	
    	$totalSeconds = $finalSeconds - $initialSeconds;
    	
    	$diffHours = intval($totalSeconds / 3600);
    	$diffMinutes = intval(($totalSeconds / 60) - ($diffHours * 60));
    	$diffSeconds = $totalSeconds - ($diffMinutes * 60) - ($diffHours * 3600);
    	return array(
    		"Hours" => $diffHours,
    		"Minutes" => $diffMinutes,
    		"Seconds" => $diffSeconds
    	);
    }
    
    public function sum(DateTime $finalDate, DateTime $initialDate){
    	$tempFinal = $finalDate->format("Y-m-d H:i:s");
    	$tempInitial = $initialDate->format("Y-m-d H:i:s");
    	
    	$finalSeconds = strtotime($tempFinal);
    	$initialSeconds = strtotime($tempInitial);
    	
    	$totalSeconds = $finalSeconds + $initialSeconds;
    	
    	$diffHours = intval($totalSeconds / 3600);
    	$diffMinutes = intval(($totalSeconds / 60) - ($diffHours * 60));
    	$diffSeconds = $totalSeconds - ($diffMinutes * 60) - ($diffHours * 3600);
    	return array(
    		"Hours" => $diffHours,
    		"Minutes" => $diffMinutes,
    		"Seconds" => $diffSeconds
    	);
    }
    
    public function addTimeString($finalDate, $initialDate){
    	$finalSeconds = intval($finalDate[0].$finalDate[1].$finalDate[2])*3600 + intval($finalDate[4].$finalDate[5])*60
    					+ intval($finalDate[7].$finalDate[8]);
    	$initialSeconds = intval($initialDate[0].$initialDate[1].$initialDate[2])*3600 + intval($initialDate[4].$initialDate[5])*60
    					+ intval($initialDate[7].$initialDate[8]);
    	
    	$totalSeconds = $finalSeconds + $initialSeconds;
    	$addHours = intval($totalSeconds / 3600);
    	$addMinutes = intval(($totalSeconds / 60) - ($addHours * 60));
    	$addSeconds = $totalSeconds - ($addMinutes * 60) - ($addHours * 3600);
    	if($addHours < 10){
    		$addHours = "00".$addHours;
    	}
    	elseif($addHours < 100){
    		$addHours = "0".$addHours;
    	}
    	if($addMinutes < 10){
    		$addMinutes = "0".$addMinutes;
    	}
    	if($addSeconds < 10){
    		$addSeconds = "0".$addSeconds;
    	}
    	
    	$output = $addHours.":".$addMinutes.":".$addSeconds;
    	return $output;
    }
 
    /**
     * Formata a soma de duas datas
     * @param $finalDate Primeira data
     * @param $initialDate Segunda data
     * @param $displayEmptyScales Exibe escalas vazias
     * @param $format Formato da saída {H:m:s,Hhmmss}
     * @return String diferença formatada
     */
    public function addDateTimes($finalDate, $initialDate, $displayEmptyScales=false,$format="Hhmmss"){
    	$result = $this->sum($finalDate,$initialDate);
    	$addHours = abs($result["Hours"]);
    	$addMinutes = abs($result["Minutes"]);
    	$addSeconds = abs($result["Seconds"]);
    	$output = "";
    	
    	// formatando a saída
    	if($format == "Hhmmss"){
    		if($displayEmptyScales){
	    		$output .= $addHours."h";
		    	$output .= $addMinutes."m";
		    	$output .= $addSeconds."s";
	    	}else{
		    	if($addHours > 0){
		    		$output .= $addHours."h";
		    	}
		    	if($addHours > 0 || $addMinutes > 0){
		    		$output .= $addMinutes."m";
		    	}
		    	if($addHours > 0 || $addSeconds > 0){
		    		$output .= $addSeconds."s";
		    	}
	    	}
    	}else{
    		$output = $addHours.":".$addMinutes.":".$addSeconds;
    	}    	
    	return $output;
    }
    
	/**
	 * @date 14/02/2013
	 * Os próximos dois métodos foram portados da transação HELPDESK.
	 */
    public function diffDT(Date $date1, Time $time1, Date $date2, Time $time2){
    	$timestamp1     = mktime($time1->getHour(),$time1->getMinute(),$time1->getSecond(),$date1->getMonth(),$date1->getDay(),$date1->getYear());
    	$timestamp2     = mktime($time2->getHour(),$time2->getMinute(),$time2->getSecond(),$date2->getMonth(),$date2->getDay(),$date2->getYear());
    	
    	$diff = ($timestamp1 - $timestamp2);
    	return $diff;
    }
    public function formatDiffDT($diff)
    {
        // Verificando se o numero tem sinal
        $signal = false;
        if($diff < 0) $signal = true;

        // Deixando o número positivo
        $diff = abs($diff);

		// MAIOR QUE 60 MINUTOS
        if($diff >= 3600) {
        	// MAIOR QUE 24 HORAS
            if($diff >= 86400) {
            	// MAIOR QUE 1 MES
                if($diff >= 2592000) {
                	// MAIOR QUE 1 ANO
                    if($diff >= 31104000) {
                    	if($signal == true) return "Menos de um ano";
                        else return "Mais de um ano";
                    }else{
                        $months = floor(((($diff/60)/60)/24)/30);
                        if ($signal == true) $string = "Daqui a :months: mes(es)";
                        else $string = "Mais de :months: mes(es)";
                        
                        $string = str_replace(":months:", $months, $string);
                    }
                }else{
                    $days = floor((($diff/60)/60)/24);
                    if($signal == true) $string = "Daqui a :days: dia(s)";
                    else $string = ":days: dia(s) atrás";
                    
                    $string = str_replace(":days:", $days, $string);
                }
            }else{
                $hour = intval($diff/3600);
                $min  = floor(($diff - ($hour*3600))/60);
                
                if ($signal == true) $string = "Daqui a :hour:h:min:m";
                else $string = ":hour:h:min:m atrás";
                
                $string = str_replace(":hour:", $hour, $string);
                $string = str_replace(":min:", $min, $string);
            }
        }else{
        	$string = ":min:m";
        	$string = str_replace(":min:", floor($diff/60), $string);
        }
        return $string;
    }

    /**
     * Formata a diferença entre duas datas
     * @param $finalDate Primeira data
     * @param $initialDate Segunda data
     * @param $displayEmptyScales Exibe escalas vazias
     * @param $format Formato da saída {H:m:s,Hhmmss}
     * @return String diferença formatada
     */
    public function diffDateTimes(DateTime $finalDate, DateTime $initialDate, $displayEmptyScales=false,$format="Hhmmss"){
    	$result = $this->subtract($finalDate,$initialDate);
    	$diffHours = abs($result["Hours"]);
    	$diffMinutes = abs($result["Minutes"]);
    	$diffSeconds = abs($result["Seconds"]);
    	$output = "";
    	
    	// formatando a saída
    	if($format == "Hhmmss"){
    		if($displayEmptyScales){
	    		$output .= $diffHours."h";
		    	$output .= $diffMinutes."m";
		    	$output .= $diffSeconds."s";
	    	}else{
		    	if($diffHours > 0){
		    		$output .= $diffHours."h";
		    	}
		    	if($diffHours > 0 || $diffMinutes > 0){
		    		$output .= $diffMinutes."m";
		    	}
		    	if($diffHours > 0 || $diffSeconds > 0){
		    		$output .= $diffSeconds."s";
		    	}
	    	}
    	}else{
    		$output = $diffHours.":".$diffMinutes.":".$diffSeconds;
    	}    	
    	return $output;
    }
    
    /**
     * @param $output {time|text}
     * @param $maxScale {D,H,M,S}
     */
    public static function formatTimeBySeconds($seconds,$output="time",$maxScale="D"){
    	// definindo segundos de cada escala
    	$seconds = intval($seconds);
    	$day2sec = 86400; 
    	$hour2sec = 3600; 
    	$minute2sec = 60; 
    	
    	// extraindo informações
    	$days = floor($seconds / $day2sec);
    	$seconds -= $days * $day2sec;
    	
    	$hours = floor($seconds / $hour2sec);
    	$seconds -= $hours * $hour2sec;
    	
    	$minutes = floor($seconds / $minute2sec);
    	$seconds -= $minutes * $minute2sec;
    	
    	if($output == "time"){
    		if($hours < 10){
	    		$hours = "0".$hours;
	    	}
	    	if($minutes < 10){
	    		$minutes = "0".$minutes;
	    	}
	    	if($seconds < 10){
	    		$seconds = "0".$seconds;
	    	}
	    	switch($maxScale){
	    	case "D":
	    		return $days.":".$hours.":".$minutes.":".$seconds;
	    		break;
	    	case "H":
	    		return $hours.":".$minutes.":".$seconds;
	    		break;
	    	case "M":
	    		return $minutes.":".$seconds;
	    		break;
	    	case "S":
	    		return $seconds;
	    		break;
	    	}
    	}else{
    		$output = "";
    		if($days > 0){
    			$output .= $days." dia(s) ";
    		}
    		if($hours > 0){
    			$output .= $hours." hora(s) ";
    		}
    		if($minutes > 0){
    			$output .= $minutes." minuto(s) ";
    		}
    		if($seconds > 0){
    			$output .= $seconds." segundo(s) ";
    		}
    		return trim($output);
    	}
    }
    
    public static function getDiffDays(DateTime $d1, DateTime $d2){
    	$format = "Y-m-d H:i:s";
    	$date1 = $d1->format($format);
		$date2 = $d2->format($format);
		
		$ts1 = strtotime($date1);
		$ts2 = strtotime($date2);
		
		$seconds_diff = abs($ts2 - $ts1);
		
		return floor($seconds_diff/3600/24);
    }
}
?>