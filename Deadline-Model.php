<?php
App::uses('AppModel', 'Model');
App::uses('CakeSession', 'Model/Datasource');

/**
 * Deadline Model
 *
 */
class Deadline extends AppModel {

	public $order = array();
	public $recursive = -1;
	public $actsAs = array('Transactional','Search.Searchable', 'Containable');
	public $findMethods = array('convertDt' => true, 'firstConvertDt' => true, 'firstConvertDtNoTime' => true);
	

	public $validate = array(
		'deadline' => array(
				'rule' => '/^((31(?!\\ (Feb(ruary)?|Apr(il)?|June?|(Sep(?=\\b|t)t?|Nov)(ember)?)))|((30|29)(?!\\ Feb(ruary)?))|(29(?=\\ Feb(ruary)?\\ (((1[6-9]|[2-9]\\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00)))))|(0?[1-9])|1\\d|2[0-8])\\-(Jan(uary)?|Feb(ruary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))|Aug(ust)?|Oct(ober)?|(Sep(?=\\b|t)t?|Nov|Dec)(ember)?)\\-((1[6-9]|[2-9]\\d)\\d{2})$/i',//array('customDateTimeValidation'), 
       		    'message' => 'Enter a valid date in Day-Month-Year format, e.g.: 01-January-2012',
				'allowEmpty' => false
		),
		'case_file_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'Select Case File',
			)
		),
		'deadline_type_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'Select Deadline Type',
			)
		)

	);


	public $belongsTo = array(
		'CaseFile' => array(
			'className' => 'CaseFile',
			'foreignKey' => 'case_file_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'DeadlineReason' => array(
			'className' => 'DeadlineReason',
			'foreignKey' => 'deadline_reason_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'DeadlineType' => array(
			'className' => 'DeadlineType',
			'foreignKey' => 'deadline_type_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),	
		'Parent' => array(
			'className' => 'Deadline',
			'foreignKey' => 'parent_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),		
	);
	
	public $hasMany = array(
		'DeadlineEdit' => array(
			'className' => 'DeadlineEdit',
			'foreignKey' => 'deadline_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Child' => array(
			'className' => 'Deadline',
			'foreignKey' => 'parent_id',
			'conditions' => '',
			'fields' => '',
			'order' => 'Child.deleted ASC, Child.deadline DESC'
		)
	);
	
	/*
	 * Search and Filter in /index page with plugin
	*/
	public $filterArgs = array(
			'deadline_type_id' => array(
					'type' => 'lookup',
					'formField' => 'deadline_type_input',
					'modelField' => 'name',
					'model' => 'DeadlineType'
			),
			'deadline_reason_id' => array(
					'type' => 'lookup',
					'formField' => 'deadline_reason_input',
					'modelField' => 'name',
					'model' => 'DeadlineReason'
			),

	);

	// Custom Filter by Date
	public static function prepareDateFilter($dates = array()) {
	
		if(!empty($dates['dl_from'])){
			// format date
			$dl = $dates['dl_from'];
			$data1 = date("Y-m-d", strtotime($dl));
			$data1 = $data1.' 00:00:00';
		}
		if(!empty($dates['dl_to'])){
			$dl = $dates['dl_to'];
			$data2 = date("Y-m-d", strtotime($dl));
			$data2 = $data2.' 23:59:59';
		}
	
		if (empty($data1)){
			$data1 = '0001-01-01 00:00:00';
		}
		if (empty($data2)){
			// get date of today in users timezone
			$timezone = CakeSession::read("Auth.User.timezone");
			$format=Configure::read('DateTimeFormat');
			$timezonePOffice = new DateTimeZone($timezone);
			$today = date("Y-m-d");
			$dateObj = new DateTime($today, $timezonePOffice);
			$data2 = $dateObj->format("Y-m-d");
			$data2 = $data2.' 23:59:59';
		}
		$returnDates = array('data1' => $data1, 'data2' => $data2);
		return $returnDates;
	}	
	
	public static function prepareUserFilter($user_ids = array()) {
		$paginate = array();
		$paginateDeadline['fields'] =
		array(
				'Deadline.*',
				'CaseFile2.id',
				'Deadline.case_file_id',
				'CaseFilesUser.case_file_id',
				'CaseFilesUser.user_id',
				'User.id',
				'User.username',
				'Group.id',
				'Group.alias',
				'DeadlineType.id',
				'DeadlineReason.id',
				'DeadlineType.name',
				'DeadlineReason.name',
		);
		$paginateDeadline['joins'] =
		array(
				array(
						'alias' => 'DeadlineType',
						'table' => 'deadline_types',
						'type' => 'INNER',
						'conditions' => '`Deadline`.`deadline_type` = `DeadlineType`.`id`'
				),	
				array(
						'alias' => 'DeadlineReason',
						'table' => 'deadline_reasons',
						'type' => 'INNER',
						'conditions' => '`Deadline`.`deadline_type` = `DeadlineReason`.`id`'
				),
				array(
						'alias' => 'CaseFile2',
						'table' => 'case_files',
						'type' => 'INNER',
						'conditions' => '`Deadline`.`case_file_id` = `CaseFile2`.`id`'
				),
				array (
						'alias' => 'CaseFilesUser',
						'table' => 'case_files_users',
						'type' => 'INNER',
						'conditions' => '`CaseFile2`.`id` = `CaseFilesUser`.`case_file_id`'
				),
				array (
						'alias' => 'User',
						'table' => 'users',
						'type' => 'INNER',
						'conditions' => '`CaseFilesUser`.`user_id` = `User`.`id`'
				),
				array (
						'alias' => 'Group',
						'table' => 'roles',
						'type' => 'INNER',
						'conditions' => '`User`.`role_id` = `Group`.`id`'
				)
		);
		$paginateDeadline['group'] = 'Deadline.id';
		$paginateDeadline['conditions'] = array("User.id"=>$user_ids);
		
		return $paginateDeadline;		
	}
	
	
	public function findByUser($data = array()) {
		$query = $this->getQuery('all', array(
				'conditions' => array(
						'CaseFilesUser.user_id' => $data['user_id']
				),
				'joins' => array(
						array(
								'table' => 'case_files_persons',
								'alias' => 'CaseFilesPerson',
								'type' => 'inner',
								'foreignKey' => false,
								'conditions'=> array('CaseFilesPerson.case_file_id = CaseFile.id')
						),
						array (
								'alias' => 'CaseFilesUser',
								'table' => 'case_files_users',
								'type' => 'INNER',
								'conditions' => '`CaseFile2`.`id` = `CaseFilesUser`.`case_file_id`'
						),						
				)
		));
		return $query;
	}
	
	
	
	
	
	// Returns combiation of DeadlineType, deadline and Deadlinereason as identifying name
	public static function identifier($deadline, $generation = "deadline", $timezone = null) {

		$identifier = null;
		if ($timezone == null && isset($deadline['CaseFile']['PatentOffice']['timezone'])) {
			$timezone = $deadline['CaseFile']['PatentOffice']['timezone'];
		}else if ($timezone == null) {
			$timezone = CakeSession::read("Auth.User.timezone");
		}

		if ($generation == null) {			
			if (!empty($deadline['deadline'])) {
				if (isset($deadline['CaseFile']['PatentOffice']['timezone'])) $timezone = $deadline['CaseFile']['PatentOffice']['timezone'];
				$identifier = $deadline['deadline'];
				$identifier = Deadline::convTimezone($identifier, $timezone);
			} else {
				$identifier = 'Unknown #';
			}
			
			if (!empty($deadline['DeadlineType']['name'])) $identifier = $deadline['DeadlineType']['name'].': '.$identifier;
			if (!empty($deadline['DeadlineReason']['name'])) $identifier .= ' ('.$deadline['DeadlineReason']['name'].')';
		}else if ($generation == "noconvert") {
				
				if (!empty($deadline['deadline'])) {
				if (isset($deadline['CaseFile']['PatentOffice']['timezone'])) $timezone = $deadline['CaseFile']['PatentOffice']['timezone'];
				$identifier = $deadline['deadline'];
			} else {
				$identifier = 'Unknown #';
			}
				
			if (!empty($deadline['DeadlineType']['name'])) $identifier = $deadline['DeadlineType']['name'].': '.$identifier;
			if (!empty($deadline['DeadlineReason']['name'])) $identifier .= ' ('.$deadline['DeadlineReason']['name'].')';
		}else if ($generation == "deadline" && array_key_exists('Deadline', $deadline))	{
			
			if (!empty($deadline['Deadline']['deadline'])) {
				if (isset($deadline['CaseFile']['PatentOffice']['timezone'])) $timezone = $deadline['CaseFile']['PatentOffice']['timezone'];
				$identifier = $deadline['Deadline']['deadline'];
				$identifier = Deadline::convTimezone($identifier, $timezone);
			} else {
				$identifier = 'Unknown #';
			}		
	
			if (!empty($deadline['DeadlineType']['name'])) $identifier = $deadline['DeadlineType']['name'].': '.$identifier;			
			if (!empty($deadline['DeadlineReason']['name'])) $identifier .= ' ('.$deadline['DeadlineReason']['name'].')';
			
		}else if ($generation == "parent" &&  array_key_exists('Parent', $deadline))	{
			
			if (!empty($deadline['Parent']['deadline'])) {
				
				$identifier = $deadline['Parent']['deadline'];
				$identifier = Deadline::convTimezone($identifier, $timezone);
			} else {
				$identifier = 'Unknown #';
			}		
	
			if (!empty($deadline['Parent']['DeadlineType']['name'])) $identifier = $deadline['Parent']['DeadlineType']['name'].': '.$identifier;			
			if (!empty($deadline['Parent']['DeadlineReason']['name'])) $identifier .= ' ('.$deadline['Parent']['DeadlineReason']['name'].')';
		
		}else if ($generation == "child")	{
			
			if (!empty($deadline['deadline'])) {
				if (isset($deadline['CaseFile']['PatentOffice']['timezone'])) $timezone = $deadline['CaseFile']['PatentOffice']['timezone'];
				$identifier = $deadline['deadline'];				
				$identifier = Deadline::convTimezone($identifier, $timezone);
			} else {
				$identifier = 'Unknown #';
			}		
	
			if (!empty($deadline['DeadlineType']['name'])) $identifier = $deadline['DeadlineType']['name'].': '.$identifier;
			if (!empty($deadline['DeadlineReason']['name'])) $identifier .= ' ('.$deadline['DeadlineReason']['name'].')';
		
		}		
			  
		return $identifier;
	}	
	
	
	
	
	
	private function _changeDateFormat($date = null,$dateFormat){
		$return = null;
		if (!empty($date))
			$return = date($dateFormat, strtotime($date));
		return $return;
	}
	
	public function changeDateFormat($date = null){
		$format = Configure::read('DateTimeFormat');
		return $this->__changeDateFormat($date,$format);
	}
	
	protected function _findConvertDt($state, $query, $results = array()) {
 		if ($state === 'before') {		
			return $query;
		} elseif ($state === 'after') {
			$this->dateFormat = Configure::read('DateTimeFormat');
			foreach ($results as $key=>$result) {
				if (isset($result['Deadline']['deadline'])) $results[$key]['Deadline']['deadline'] = $this->_changeDateFormat($result['Deadline']['deadline'], $this->dateFormat);
			}
			return $results;
		}
	}
	
	protected function _findFirstConvertDt($state, $query, $results = array()) {
		if ($state === 'before') {
			return $query;
		} elseif ($state === 'after') {
			$this->dateFormat = Configure::read('DateTimeFormat');
			foreach ($results as $key=>$result) {
				if (isset($result['Deadline']['deadline'])) $results[$key]['Deadline']['deadline'] = $this->_changeDateFormat($result['Deadline']['deadline'], $this->dateFormat);
			}
			if (empty($results[0])) {
				return array();
			}
			return $results[0];
		}
	}
	
	protected function _findFirstConvertDtNoTime($state, $query, $results = array()) {
		if ($state === 'before') {
			return $query;
		} elseif ($state === 'after') {
			$this->dateFormat = Configure::read('DateFormat');
			foreach ($results as $key=>$result) {
				if (isset($result['Deadline']['deadline'])) $results[$key]['Deadline']['deadline'] = $this->_changeDateFormat($result['Deadline']['deadline'], $this->dateFormat);
			}
			if (empty($results[0])) {
				return array();
			}
			return $results[0];
		}
	}
		
	
	function beforeSave($options = array()) {
		$this->databaseFormat = Configure::read('DateTimeFormatDb');
		$this->data['Deadline']['deadline'] = $this->_changeDateFormat($this->data['Deadline']['deadline'], $this->databaseFormat);
		$this->data['Deadline']['deadline'] = $this->dayEnd($this->data['Deadline']['deadline']);
    	return true;
}

	/**
	 * Add 23h 59m and 59s (=1Day-1Second), because the deadline doesnt end at the beginning of the day, but at the end of the day (e.g.: 23:59:59)
	 * @param String $dayBeginStr date in form of a string, time needs to be 00:00:00
	 * @param String $format formatting options for reurned date-string, standard value is 'Y-m-d H:i:s' 
	 * @return String $dayEndString date-string with same date but time is now set to 23:59:59 
	 */ 
	function dayEnd($dayBeginStr, $format = 'Y-m-d H:i:s') {	
		$return = null;
		if (!empty($dayBeginStr)) {
			if (is_object($dayBeginStr)) { $dateObj = $dayBeginStr; }else{
			$dayBeginStrMod = date("Y-m-d", strtotime($dayBeginStr)); }
			$dateObj = new DateTime($dayBeginStrMod);
			$dateObj->modify('+1 day');			
			$dateObj->modify('-1 second');
			$dayEndStr = $dateObj->format($format);
			unset($datObj);
			$return = $dayEndStr;
		}			
		return $return;
	}
	
	/**
	  * Create DateTimeObject from database-date with correct timezone according to PatentOffice and
	  * Convert date to the timezone of the user
	  * @param Array $date With with keys [Day] [Month] [Year] , Date to be converted
	  * @param String $timezone The timezone of the related PatentOffice as String value
	  * @return String $convDate The converted Date
	  */
	 public function convTimezone($date, $timezone) {		 			 	
			// Deadline Dates are saved in database without timezone of the patentoffice. Application timezone is set to UTC +/-0
			// So its necessary give the DateTime-Object the timezone from the patentoffice of the casefile before convertig to the users timezone
			// If the Patent Office has no timezone or there is no Patent Office, convert time from database to users timezone
 			$return = null;
	 		if (!empty($date)) {
				if(empty($timezone)) { 
					$timezone = CakeSession::read("Auth.User.timezone");
				} 	
				$format=Configure::read('DateTimeFormat'); 		
				$timezonePOffice = new DateTimeZone($timezone);
				$dateMod = date("Y-m-d H:i:s", strtotime($date));
				$dateObj = new DateTime($dateMod, $timezonePOffice);
				
				// Convert to users timezone. If $timezone was empty, the date will be the same as in database
				// (However, the next step will modify the date (see 'Add 23h 59m...' below))
				$timezoneUser = new DateTimeZone(CakeSession::read("Auth.User.timezone"));
				$dateObj->setTimezone($timezoneUser);//$dateObjMod->setTimezone($timezoneUser);			
	
				// Format the date
				$dateString = $dateObj->format($format);
				$return = $dateString;
	 		}
	 		return $return;
	 }
	 
	 
	 /**
	  * Format Date according to Config value
	  * @param String/Array $date as String or Array with with keys [Day] [Month] [Year] , Date to be converted
	  * @return String $dateString The formatted DateTime
	  */
	 public static function formatDateTime($date) {
	 		$dateString = $date;		 			 	
			$format=Configure::read('DateTimeFormat'); 		
			$dateObj = new DateTime($date);
			// Format the date
			$dateString = $dateObj->format($format);
			return $dateString;
	 }
	 
	public static function isDate($str){ 
	    $stamp = strtotime( $str ); 
	    if (!is_numeric($stamp)) 
	        return FALSE; 
	    $month = date( 'm', $stamp ); 
	    $day   = date( 'd', $stamp ); 
	    $year  = date( 'Y', $stamp ); 
	    if (checkdate($month, $day, $year)) 
	        return TRUE; 
	    return FALSE; 
	}
	 
	 /** 
	  * Calculates internal deadline vi the following procedure:
	  * 1. Subract amount of months, keep day the same or choose last day of the month
	  * 2. Subtract amount of days
	  * @param String $dl date string, preferably in the format 'Y-m-d'
	  * @return String Internal deadline date (formating via config option 'DateFormat')
	  */
	 public function calcInternalDl($dl, $int_dl_time = array('months'=>1, 'days'=>0)) {
	 	$dlStr = null;
	 	$modDlStr = null;
		$dlDay = null;
		$dateStmp = null;
		// process parameters
 		if (!empty($dl)) {
 			$stamp = strtotime($dl);
 			// check if timestamp is valid numeric value
 			if (is_numeric($stamp)) {
 				$dlDay = intval(date("d", $stamp));
 				$dlStr = date("Y-m-d", $stamp);
 			}
 			unset($stamp);
 		}
		// check if valid date
 		$isDate = Deadline::isDate($dlStr);
		if (($isDate == true) && isset($dlDay)) {
			$tmMonths = intval($int_dl_time['months']);
			$tmDays = intval($int_dl_time['days']);
			// subtract months
			if ($tmMonths > 0) {
				// set subtract date string according to plural/singular
				$subM = ($tmMonths > 1) ? $int_dl_time['months']." months" : $int_dl_time['months']." month";
				$dateStmp = strtotime($dlStr . ' - ' . $subM);
				$day = intval(date("d", $dateStmp));
				$dlStr = date('Y-m-d', $dateStmp);
				// if the numbers of the start date day and the modified date day are different,
				// then the modified date month had less days than the start date day
				// so we have to subtract one month and calculate the last possible day of the modified date
				// Example: 31.5.2000 - 1 Month = 1.5.2000 but should be 30.4.2000
				if ($day != $dlDay) {
					// set date to first day of the month (just to be sure not to end up two month before)
					// and subtract one month
					$dateStmp = strtotime(date('Y-m-1', $dateStmp) . ' - 1 month');
					// get last day of the month
					$dlStr = date("Y-m-t", $dateStmp);
					$dateStmp = strtotime($dlStr);//  date('Y-m-t', $dateStmp));
				}
			}
			// subtract days
			if ($tmDays > 0) {
				$subD = (intval($int_dl_time['days']) > 1) ? $int_dl_time['days']." days" : $int_dl_time['days']." day";
				//var_dump($subD);
				$dateStmp = strtotime($dlStr . ' - ' . $subD);
			}
			// get formated date string and check if it is valid date
			$format = Configure::read('DateFormat');
			if (is_numeric($dateStmp)) {
				$dlStr = date($format, $dateStmp);
				$isDate = Deadline::isDate($dlStr);
				if ($isDate == true) {
					$modDlStr = $dlStr;
				}
			}
		}
		return $modDlStr; 	
	 }
	 

}
