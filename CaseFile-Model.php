<?php
App::uses('AppModel', 'Model');
/**
 * CaseFile Model
 *
 */
class CaseFile extends AppModel {

	public $order = array();

	public $scaffoldSkipFields = array('note', 'priority');
	public $recursive = -1;
	
	var $actsAs = array(
			'Search.Searchable', 
			'Attribute' => array('identifier', 'name'), 
			'Transactional',
			'Translate' => array(
					'title',
					'representation'
			),
			'Containable'
		);

	var $findMethods = array('firstConvertDt' => true);
		
	// Use a different model (and table)
	public $translateModel = 'CaseFileI18n';
		
	public $locale = array('eng','deu','chi','fra','esp', 'ita');
	
	public $validate = array(
		'filenumber' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'Error: Empty filenumber. Please Go back to index and add Case File again',
			),
		),
        'User' => array( 
            'multiple' => array( 
                'rule' => array('multiple',array('min' => 1)), 
                'message' => 'Please select at least 1 responsible user'), 
        ), 
		'pct_application_date' => array(
				'rule' => '/^((31(?!\\ (Feb(ruary)?|Apr(il)?|June?|(Sep(?=\\b|t)t?|Nov)(ember)?)))|((30|29)(?!\\ Feb(ruary)?))|(29(?=\\ Feb(ruary)?\\ (((1[6-9]|[2-9]\\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00)))))|(0?[1-9])|1\\d|2[0-8])\\-(Jan(uary)?|Feb(ruary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))|Aug(ust)?|Oct(ober)?|(Sep(?=\\b|t)t?|Nov|Dec)(ember)?)\\-((1[6-9]|[2-9]\\d)\\d{2})$/i', 
       		    'message' => 'Enter a valid date in Day-Month-Year format, e.g.: 01-January-2012',
				'allowEmpty' => true
		),
		'nat_reg_entering_date' => array(
					'rule' => '/^((31(?!\\ (Feb(ruary)?|Apr(il)?|June?|(Sep(?=\\b|t)t?|Nov)(ember)?)))|((30|29)(?!\\ Feb(ruary)?))|(29(?=\\ Feb(ruary)?\\ (((1[6-9]|[2-9]\\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00)))))|(0?[1-9])|1\\d|2[0-8])\\-(Jan(uary)?|Feb(ruary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))|Aug(ust)?|Oct(ober)?|(Sep(?=\\b|t)t?|Nov|Dec)(ember)?)\\-((1[6-9]|[2-9]\\d)\\d{2})$/i',
					'message' => 'Enter a valid date in Day-Month-Year format, e.g.: 01-January-2012',
					'allowEmpty' => true
		),
		'pct_publication_date' => array(
				'rule' => '/^((31(?!\\ (Feb(ruary)?|Apr(il)?|June?|(Sep(?=\\b|t)t?|Nov)(ember)?)))|((30|29)(?!\\ Feb(ruary)?))|(29(?=\\ Feb(ruary)?\\ (((1[6-9]|[2-9]\\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00)))))|(0?[1-9])|1\\d|2[0-8])\\-(Jan(uary)?|Feb(ruary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))|Aug(ust)?|Oct(ober)?|(Sep(?=\\b|t)t?|Nov|Dec)(ember)?)\\-((1[6-9]|[2-9]\\d)\\d{2})$/i',
				'message' => 'Enter a valid date in Day-Month-Year format, e.g.: 01-January-2012',
				'allowEmpty' => true
		),
		'pct_date_int_search_rep' => array(
				'rule' => '/^((31(?!\\ (Feb(ruary)?|Apr(il)?|June?|(Sep(?=\\b|t)t?|Nov)(ember)?)))|((30|29)(?!\\ Feb(ruary)?))|(29(?=\\ Feb(ruary)?\\ (((1[6-9]|[2-9]\\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00)))))|(0?[1-9])|1\\d|2[0-8])\\-(Jan(uary)?|Feb(ruary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))|Aug(ust)?|Oct(ober)?|(Sep(?=\\b|t)t?|Nov|Dec)(ember)?)\\-((1[6-9]|[2-9]\\d)\\d{2})$/i',
				'message' => 'Enter a valid date in Day-Month-Year format, e.g.: 01-January-2012',
				'allowEmpty' => true
		),
		'pct_date_written_opinion_int_search_rep' => array(
				'rule' => '/^((31(?!\\ (Feb(ruary)?|Apr(il)?|June?|(Sep(?=\\b|t)t?|Nov)(ember)?)))|((30|29)(?!\\ Feb(ruary)?))|(29(?=\\ Feb(ruary)?\\ (((1[6-9]|[2-9]\\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00)))))|(0?[1-9])|1\\d|2[0-8])\\-(Jan(uary)?|Feb(ruary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))|Aug(ust)?|Oct(ober)?|(Sep(?=\\b|t)t?|Nov|Dec)(ember)?)\\-((1[6-9]|[2-9]\\d)\\d{2})$/i',
				'message' => 'Enter a valid date in Day-Month-Year format, e.g.: 01-January-2012',
				'allowEmpty' => true
		),
		'pct_date_int_pre_exam_report' => array(
				'rule' => '/^((31(?!\\ (Feb(ruary)?|Apr(il)?|June?|(Sep(?=\\b|t)t?|Nov)(ember)?)))|((30|29)(?!\\ Feb(ruary)?))|(29(?=\\ Feb(ruary)?\\ (((1[6-9]|[2-9]\\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00)))))|(0?[1-9])|1\\d|2[0-8])\\-(Jan(uary)?|Feb(ruary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))|Aug(ust)?|Oct(ober)?|(Sep(?=\\b|t)t?|Nov|Dec)(ember)?)\\-((1[6-9]|[2-9]\\d)\\d{2})$/i',
				'message' => 'Enter a valid date in Day-Month-Year format, e.g.: 01-January-2012',
				'allowEmpty' => true
		),
		'grant_reg_date' => array(
				'rule' => '/^((31(?!\\ (Feb(ruary)?|Apr(il)?|June?|(Sep(?=\\b|t)t?|Nov)(ember)?)))|((30|29)(?!\\ Feb(ruary)?))|(29(?=\\ Feb(ruary)?\\ (((1[6-9]|[2-9]\\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00)))))|(0?[1-9])|1\\d|2[0-8])\\-(Jan(uary)?|Feb(ruary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))|Aug(ust)?|Oct(ober)?|(Sep(?=\\b|t)t?|Nov|Dec)(ember)?)\\-((1[6-9]|[2-9]\\d)\\d{2})$/i',
				'message' => 'Enter a valid date in Day-Month-Year format, e.g.: 01-January-2012',
				'allowEmpty' => true
		),
		'grant_reg_pub_date' => array(
				'rule' => '/^((31(?!\\ (Feb(ruary)?|Apr(il)?|June?|(Sep(?=\\b|t)t?|Nov)(ember)?)))|((30|29)(?!\\ Feb(ruary)?))|(29(?=\\ Feb(ruary)?\\ (((1[6-9]|[2-9]\\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00)))))|(0?[1-9])|1\\d|2[0-8])\\-(Jan(uary)?|Feb(ruary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))|Aug(ust)?|Oct(ober)?|(Sep(?=\\b|t)t?|Nov|Dec)(ember)?)\\-((1[6-9]|[2-9]\\d)\\d{2})$/i',
				'message' => 'Enter a valid date in Day-Month-Year format, e.g.: 01-January-2012',
				'allowEmpty' => true
		),
		'publication_date_trans' => array(
				'rule' => '/^((31(?!\\ (Feb(ruary)?|Apr(il)?|June?|(Sep(?=\\b|t)t?|Nov)(ember)?)))|((30|29)(?!\\ Feb(ruary)?))|(29(?=\\ Feb(ruary)?\\ (((1[6-9]|[2-9]\\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00)))))|(0?[1-9])|1\\d|2[0-8])\\-(Jan(uary)?|Feb(ruary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))|Aug(ust)?|Oct(ober)?|(Sep(?=\\b|t)t?|Nov|Dec)(ember)?)\\-((1[6-9]|[2-9]\\d)\\d{2})$/i',
				'message' => 'Enter a valid date in Day-Month-Year format, e.g.: 01-January-2012',
				'allowEmpty' => true
		),
		'status_id' => array(
				'numeric' => array(
						'rule' => array('numeric'),
						'message' => 'Select Status',
				)
		),		
	);


	public $hasAndBelongsToMany = array(
	'Person' =>
	    array(
		'className'              => 'Person',
		'joinTable'              => 'case_files_persons',
		'foreignKey'             => 'case_file_id',
		'associationForeignKey'  => 'person_id',
	    'unique' => 'false',
		'with' => 'CaseFilesPerson',
	    ),
	'User' =>
	    array(		
		'className'              => 'User',
		'joinTable'              => 'case_files_users',
		'foreignKey'             => 'case_file_id',
		'associationForeignKey'  => 'user_id',
		'with' => 'CaseFilesUser',
	    ),
	'RelCaseFile' =>
	    array(
		'className'              => 'CaseFile',
		'joinTable'              => 'case_files_rel_case_files',
		'foreignKey'             => 'case_file_id',
		'associationForeignKey'  => 'rel_case_file_id',
		'with' => 'CaseFilesRelCaseFile',
	    ),
	'RelCaseFile2' =>
	    array(
		'className'              => 'CaseFile',
		'joinTable'              => 'case_files_rel_case_files',
		'foreignKey'             => 'rel_case_file_id',
		'associationForeignKey'  => 'case_file_id',
		'with' => 'CaseFilesRelCaseFile',
	    ),
	'Letter'=>
		array(
			'className'              => 'Letter',
			'joinTable'              => 'case_files_letters',
			'foreignKey'             => 'case_file_id',
			'associationForeignKey'  => 'letter_id',
			'with' => 'CaseFilesLetter',
		),		 	    
	);

	public $belongsTo = array(
		'PatentOffice' => array(
			'className' => 'PatentOffice',
			'foreignKey' => 'patent_office_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'PCT_RO' => array(
				'className' => 'PatentOffice',
				'foreignKey' => 'pct_receiving_office',
				'conditions' => '',
				'fields' => '',
				'order' => ''
		),
		'PCT_ISA' => array(
				'className' => 'PatentOffice',
				'foreignKey' => 'pct_international_search_authority',
				'conditions' => '',
				'fields' => '',
				'order' => ''
		),
		'PCT_IEA' => array(
				'className' => 'PatentOffice',
				'foreignKey' => 'pct_international_examination_authority',
				'conditions' => '',
				'fields' => '',
				'order' => ''
		),			
		'Kind' => array(
		    'className'    => 'Kind',
		    'foreignKey'   => 'kind_id'
		),
		'Status' => array(
		    'className'    => 'Status',
		    'foreignKey'   => 'status_id'
		)
	);

	public $hasMany = array(

		'Deadline' => array(
			'className' => 'Deadline',
			'foreignKey' => 'case_file_id',
			'conditions' => '',
			'fields' => '',
			'order' => 'Deadline.deleted ASC, Deadline.deadline DESC'
		),		
		'CaseFilePriority' => array(
			'className' => 'CaseFilePriority',
			'foreignKey' => 'case_file_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'CaseFilesPerson' => array(
				'className'              => 'CaseFilesPerson',
				'foreignKey'             => 'case_file_id',
		),
		'CaseFilesValidation' => array(
				'className' => 'CaseFilesValidation',
				'foreignKey' => 'case_file_id',
				'conditions' => '',
				'fields' => '',
				'order' => '',
		),
		'LetterInstance' => array(
				'className' => 'LetterInstance',
				'foreignKey' => 'case_file_id',
				'conditions' => '',
				'fields' => '',
				'order' => ''
		),
		'CaseFileI18n'=> array(
				'className' => 'CaseFileI18n',
				'foreignKey' => 'foreign_key',
				'conditions' => '',
				'fields' => '',
				'order' => ''
		)
			
	);

	

	/*
	 * Search in /index page
	 */
    public $filterArgs = array(
    	'search' => array(
    			'type' => 'like', 
    			'field' => array(
    					'CaseFileI18nSearch.content', 
    					'filenumber',    					
    					'application_number',
    					'pct_application_number',
    					'pct_publication_number',
    					'grant_reg_number',
    					'electr_filling_number',
    					'publication_number',
    					'classes_for_products_services',
    					'main_class_for_products_services',
    					'note',
    					'CaseFilesPerson.correspondence_attorneys_number', //'CaseFile.CaseFilesPerson.correspondence_attorneys_number',
    					'CaseFilesPerson.applicant_number'
    				)
    		),

    );
    
    
    /**
     * 
     * Custom find method
     * uses list instead of count to prevent errors
     * @param unknown_type $conditions
     * @param unknown_type $recursive
     * @param unknown_type $extra
     * @return number
     */
    function paginateCount($conditions = array(), $recursive = null, $extra = array()) {
    	$conditions = compact('conditions');
    	if ($recursive != $this->recursive) {
    		$conditions['recursive'] = $recursive;
    	}
    	if ($recursive == -1) {
    		$extra['contain'] = array();
    	}    	
    	return count($this->find('list', array_merge($conditions, $extra)));
    }
    
    public function findByPerson($data = array()) {
    	$query = $this->getQuery('all', array(
    			'conditions' => array(
    					'CaseFilesPerson.correspondence_attorneys_number' => $data['search']
    			),
    			//'contain' => array('CaseFilesPerson'),
    			'joins' => array(
    				array(
    					'table' => 'case_files_persons',
    					'alias' => 'CaseFilesPerson',
    					'type' => 'inner',
    					'foreignKey' => false,
    					'conditions'=> array('CaseFilesPerson.case_file_id = CaseFile.id')
    				)
    			)
    	));    	
    	return $query;
    }
	

	/*
	 * Calculates filenumber from $id of Case-File, $country_num of associated Patent-Office and $kind_short of associated Kind
	 */
	public static function filenumber($kind_short, $country_num, $id) {
		return str_pad($id, 6, '0', STR_PAD_LEFT) . $kind_short . $country_num;
	}
	
	/*
	 * Changes filenumber from $filenumber of existing Case-File, $country_num of new associated Patent-Office and $kind_short of new associated Kind
	 */
	public static function changeFilenumber($filenumber, $kind_short, $country_num) {
		if (!isset($filenumber, $kind_short, $country_num)) return false;
		$number = substr($filenumber,0,6); 
		return $number . $kind_short . $country_num;
	}
		
	// Check by end of filenumber if Case File is Validatable
	public static function validatable($caseFile = array()) {
		$return = false;
		$epValidatablePairs = array();
		$intValidatablePairs = array();
		$kindPOffice = null;
		$epValidatable = Configure::read('EpValidatable');
		$intValidatable = Configure::read('IntValidatable');
	
		if (!empty($epValidatable)) {
			$epValidatablePairs = explode(';',$epValidatable);
		}
		if (!empty($intValidatable)) {
			$intValidatablePairs = explode(';',$intValidatable);
		}
		if (empty($caseFile)) return false;
	
		if (!empty($caseFile['CaseFile'])) $caseFile = $caseFile['CaseFile'];
	
		if(!empty($caseFile['filenumber'])) {
			$kindPOffice = substr($caseFile['filenumber'], '-3');
		}
	
		if(!empty($kindPOffice) && !empty($epValidatablePairs) && in_array($kindPOffice, $epValidatablePairs)) {
			$return = array('status'=>true, 'ep'=>true);
		}else if(!empty($kindPOffice) && !empty($intValidatablePairs) && in_array($kindPOffice, $intValidatablePairs)) {
			$return = array('status'=>true, 'ep'=>false);
		}else{
			$return = array('status'=>false, 'ep'=>false);
		}
		return $return;
	}
	
	// Returns combination of filenumber and title or representation as identifying name
	public static function identifier($caseFile) {
		$i18n = !empty($caseFile['CaseFileI18n']) ? $caseFile['CaseFileI18n'] : null;		
		if (array_key_exists('CaseFile', $caseFile))	{
			$caseFile = $caseFile['CaseFile'];
		}else if (array_key_exists('RelCaseFile', $caseFile))	{
			$caseFile = $caseFile['RelCaseFile'];
		}
		if (empty($i18n)) {
			$i18n = !empty($caseFile['CaseFileI18n']) ? $caseFile['CaseFileI18n'] : null;
		}
		
		if (!empty($caseFile['filenumber'])) {
			$identifier = $caseFile['filenumber'];
		} else {
			$identifier = 'Unknown #';
		}
		
		if (!empty($i18n)) {
			$index_title = "";
			foreach($i18n as $translation) {
				$field = $translation['field'];
				$lang = $translation['locale'];
				if ($lang == 'eng' && !empty($translation['content'])) {
					$index_title = $translation['content'];
				}else if ($lang == 'deu' && empty($index_title) && !empty($translation['content'])) {
					$index_title = $translation['content'];
				}else if (in_array($lang,array('esp','fra','ita')) && empty($index_title) && !empty($translation['content'])) {
					$index_title = $translation['content'];
				}else if (empty($index_title) && !empty($translation['content'])) {
					$index_title = $translation['content'];
				}
				
			}			
			$caseFile['title'] = $index_title;
		}
		
		if (!empty($caseFile['title'])) $identifier .= ': '.$caseFile['title'];
		if (!empty($caseFile['representation'])) $identifier .= ': '.$caseFile['representation'];
				  
		return $identifier;
	}
	
	/**
	 * Create multidimensional array of directories and files
	 * With names of directories and files as keys and filepaths as values
	 * 
	 * @param String $filenumber
	 * @param DirectoryIterator $dir
	 * @return Array $data multidimensional array of directories and files
	 */
	public static function listEFiles($filenumber = null, DirectoryIterator $dir = null)
	{
		if (empty($dir)) {
			$path =Configure::read('eFilesPath').$filenumber."-eFile";
			try{
				$dir = new DirectoryIterator($path);
			}catch(Exception $e){
				
			}
		}
		 
		$data = array();
		if (!empty($dir)) {
			foreach ( $dir as $node )
			{
				if ( $node->isDir() && !$node->isDot() )
				{
					$data[$node->getFilename()] =  CaseFile::listEFiles(null, new DirectoryIterator( $node->getPathname() ) );
				}
				else if ( $node->isFile() )
				{
					$data[$node->getFilename()] = $node->getPathname();
				}
			}
		}
		return $data;
	}
	 
	
	//This function search an array to get a date or datetime field.
	function _changeDate($queryDataConditions , $dateFormat){
		foreach($queryDataConditions as $key => $value){
			if(is_array($value)){
				$queryDataConditions[$key] = $this->_changeDate($value,$dateFormat);
			} else {
				$columns = $this->getColumnTypes();			
				foreach($columns as $column => $type){
					if(($type != 'date') && ($type != 'datetime')) unset($columns[$column]);
				}
				//we look for date or datetime fields on database model
				foreach($columns as $column => $type){
					if(strstr($key,$column)){
						if($type == 'datetime') $queryDataConditions[$key] = $this->_changeDateFormat($value,$dateFormat.' H:i:s ');
						if($type == 'date') $queryDataConditions[$key] = $this->_changeDateFormat($value,$dateFormat);
					}
				}
	
			}
		}
		return $queryDataConditions;
	}
	
	function _changeDateFormat($date = null,$dateFormat){
		$return = null;
		if (!empty($date))
			$return = date($dateFormat, strtotime($date));
		return $return;
	}
	
	function beforeSave() {		
		$this->databaseFormat = Configure::read('DateTimeFormatDb');		
		$this->data = $this->_changeDate($this->data, $this->databaseFormat);
		return true;
	}
	
	protected function _findFirstConvertDt($state, $query, $results = array()) {
		if ($state === 'before') {
			if (!empty($query['conditions']))
				$queryData['conditions'] = $this->_changeDate($query['conditions'] , $this->databaseFormat);
			return $query;
		} elseif ($state === 'after') {
			$this->dateFormat = Configure::read('DateFormat');
			$results = $this->_changeDate($results, $this->dateFormat);
			if (empty($results[0])) {
				return array();
			}
			return $results[0];
		}
	}
	
	function requireNotEmpty($validationFields = array(), $shouldNotBeEmpty) {
	    return !empty($this->data[$this->name][$shouldNotBeEmpty]);
	}
	
	
	// Get Case File fields by patentOfficeId and kindId to dynamically generate fileds in the form and generate the filenumber,
	public function getCaseFileFields ($patentOfficeId, $kindId = '', $filenumber = '') {
	
		if (!isset($filenumber) || !isset($patentOfficeId)) return false;
		$return = array();
	
		// Get fields 'short' and 'number' from selected Kind and PatentOffice for filenumber generation
		$kind =  $this->Kind->field('short', array('id' => $kindId));
		$pOfficeNumber = $this->PatentOffice->field('number', array('PatentOffice.id' => $patentOfficeId));
	
		// Generate filenumber
		if (empty($filenumber)) {
			$id = $this->field('id', array(), array('id DESC')) + 1;
			$return['filenumber'] = CaseFile::filenumber($kind, $pOfficeNumber, $id); //originally saved to session
		}else{
			$return['filenumber'] = CaseFile::changeFilenumber($filenumber, $kind, $pOfficeNumber); //originally saved to session
		}
	
		// Read from database which fields of Case File will be shown in step 2.
		$fields = array();
		$unusedFields = array();

		$cfFieldClass = ClassRegistry::init('CaseFileField');
		if ($kind != '')	{
			// If Kind is set, find the corresponding CaseFileField-Record, containing the available fields
	
			$kindsForAllCountrys = array('E','N','L','K','T','V','S','Q','R');
			$caseFileFields = null;
			if (in_array($kind, $kindsForAllCountrys)) {
				// If Kind is available for all countrys, CaseFileFields can be searched only by KindShort
				$caseFileFields = $cfFieldClass->findByKindShort($kind);
			}else{
				// If Kind is depending on Kind and Country, CaseFileFields must be searched only by KindShort and CountryNumber
				$caseFileFields = $cfFieldClass->findByCountryNumberAndKindShort($pOfficeNumber, $kind);
			}
					
			$return['fields'] = array();
			// If there are no values in the databasew for a special combination, throw an error
			if (empty($caseFileFields['CaseFileField']['id']) || empty($caseFileFields['CaseFileField']['fields'])) {
				$return['errors'][] = 'This combination of PatentOffice and Kind is not available in database';
			}else{
				// Create an array of the database values for viewable fields
				$return['fields'] = explode(";",$caseFileFields['CaseFileField']['fields']);
			}
			$allFields = array();
			$allCaseFileFields = $cfFieldClass->findByKindShort('ALLFIELDS');
			if (!empty($allCaseFileFields)) {
				$allFields = explode(";",$allCaseFileFields['CaseFileField']['fields']);
			}
			$tempArr = array_diff($allFields, $return['fields']);
			$return['unusedFields'] = array_values($tempArr);
		}else{
			// If Kind is not set, show all fields
			$allCaseFileFields = $cfFieldClass->findByKindShort('ALLFIELDS');
			$return['fields'] = explode(";",$allCaseFileFields['CaseFileField']['fields']); // write to session with for each
		}
	
		return $return;
	
	}
	
	public function getTranslations() {
		if (!empty($this['CaseFileI18n'])) {
			foreach($this['CaseFileI18n'] as $translation) {
				$field = $translation['field'];
				$lang = $translation['locale'];
				$this['CaseFile'][$field."_".$lang] = $translation['content'];
			}			
		}	
	}
	
	
}
