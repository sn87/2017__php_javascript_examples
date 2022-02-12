<?php
App::uses('AppController', 'Controller');
App::uses('CaseFile', 'Model');

/**
 * CaseFiles Controller
 *
 */
class CaseFilesController extends AppController {

	public $paginate = array();
	public $components = array(
			'Search.Prg'
	);

	public function beforeFilter() {
		parent::beforeFilter();
	}

	/**
	 * use beforeRender to send session parameters to the layout view ()
	 */
	public function beforeRender() {
		parent::beforeRender();
		$params = $this->Session->read('form.params');
		$this->set('params', $params);
	}


/****************************************************************************************
 * USER functions
 ****************************************************************************************/


	
	/**
	 * @return void
	 */
	public function index() {
		// Delete temporary form objects from editing/adding any objects
		$this->Session->delete('form');
		// Initiate Seach Plugin
		$this->Prg->commonProcess();
		//$this->CaseFile->bindTranslation(array('title' => 'titleTranslation'));
		// need at least recursive 1 for this to work.		
		$this->CaseFile->recursive = 1;
		// Set joins for search plugin
		$this->paginate = array(
			'joins' => array(
				array(
					'table' => 'case_files_persons',
					'alias' => 'CaseFilesPerson',
					'type' => 'left',
					'foreignKey' => false,
					'conditions'=> array('CaseFilesPerson.case_file_id = CaseFile.id')
				),
				array(
					'table' => 'case_file_i18ns',
					'alias' => 'CaseFileI18nSearch',
					'type' => 'left',
					'foreignKey' => false,
					'conditions'=> array('CaseFileI18nSearch.foreign_key = CaseFile.id')
				)
			),
			'group'=>'CaseFile.id'
		);
		$caseFiles = $this->paginate($this->CaseFile->parseCriteria($this->passedArgs));
		
		foreach ($caseFiles as $key=>$caseFile) {
			$index_title = "";
			if (!empty($caseFile['CaseFileI18n'])) {				
				foreach($caseFile['CaseFileI18n'] as $translation) {
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
			}
			$caseFiles[$key]['CaseFile']['index_title'] = $index_title;
		}
		
		
		// Paginate with Search Plugin query	
		$this->set('caseFiles', $caseFiles);
	}
	
	public function unfiled() {
		// Delete temporary form objects from editing/adding any objects
		$this->Session->delete('form');
		// Initiate Seach Plugin
		$this->Prg->commonProcess();
		//$this->CaseFile->bindTranslation(array('title' => 'titleTranslation'));
		// need at least recursive 1 for this to work.
		$this->CaseFile->recursive = 1;
		// Set joins for search plugin
		$this->paginate = array(
				'joins' => array(
						array(
								'table' => 'case_files_persons',
								'alias' => 'CaseFilesPerson',
								'type' => 'left',
								'foreignKey' => false,
								'conditions'=> array('CaseFilesPerson.case_file_id = CaseFile.id')
						),
						array(
								'table' => 'case_file_i18ns',
								'alias' => 'CaseFileI18nSearch',
								'type' => 'left',
								'foreignKey' => false,
								'conditions'=> array('CaseFileI18nSearch.foreign_key = CaseFile.id')
						)
				),
				'group'=>'CaseFile.id',
				'conditions'=>array('status_id'=>array(null, 1))
		);
		$caseFiles = $this->paginate($this->CaseFile->parseCriteria($this->passedArgs));
	
		foreach ($caseFiles as $key=>$caseFile) {
			$index_title = "";
			if (!empty($caseFile['CaseFileI18n'])) {
				foreach($caseFile['CaseFileI18n'] as $translation) {
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
			}
			$caseFiles[$key]['CaseFile']['index_title'] = $index_title;
		}
	
	
		// Paginate with Search Plugin query
		$this->set('caseFiles', $caseFiles);
		$this->render('index');
	}
	
	public function client_index() {
		$this->CaseFile->recursive = 0;
		$ids = $this->CaseFile->CaseFilesUser->find('list', array('fields'=>array('case_file_id'),'conditions'=>array('CaseFilesUser.user_id'=>$this->Auth->user('id'))));
		$this->paginate = array('conditions'=>array('CaseFile.id'=>$ids));
		$caseFiles = $this->paginate();
		$this->set(compact('caseFiles'));
	}

	/**
	 * @return void
	 */
	public function view($id = null) {
		if (empty($id) || !($caseFile = $this->CaseFile->find('firstConvertDt', array('contain'=>array('CaseFileI18n', 'PatentOffice', 'PCT_RO', 'PCT_ISA', 'PCT_IEA', 'CaseFilePriority', 'CaseFilePriority'=>array('Country'), 'Deadline'=>array('DeadlineReason','DeadlineType'), 'CaseFilesPerson'=>array('Persontype.title','Person'=>array('Personkind')),  'User'=>array('id', 'username', 'role_id', 'email','Group.alias'), 'Status', 'Kind', 'RelCaseFile'=>array('Kind.title','CaseFileI18n','PatentOffice.name'), 'RelCaseFile2'=>array('Kind.title','CaseFileI18n','PatentOffice.name'), 'CaseFilesValidation'=>array('id',  'country_id', 'patent_number'), 'CaseFilesValidation.Country'=>array('id', 'code', 'ep_country'), 'LetterInstance'=>array('Letter', 'User'=>array('fields'=>array('id', 'username', 'email')), 'conditions'=>array('LetterInstance.sent'=>0))), 'conditions'=>array('CaseFile.id'=>$id))))) {
			$this->Flash->error(__('Invalid record'), 'error');
			$this->redirect(array('action' => 'index'));
		}
		
		// delete sessions entries for previous link referers for correct refering
		$this->Session->delete('referer');
		
		// convert date format of Priorities
		if (!empty($caseFile['CaseFilePriority'])) {
			foreach ($caseFile['CaseFilePriority'] as $key=>$prio) {
				$caseFile['CaseFilePriority'][$key]['date'] = CaseFilePriority::changeDateFormat($prio['date']);
			}
		}
				
		// Split users in to Clients and Employees
		$caseFile['Employee'] = array();
		$caseFile['Client'] = array();
		foreach ($caseFile['User'] as $user) {			
			if ($user['Group']['alias'] != 'superadmin' && $user['Group']['alias'] != 'client') {
				$caseFile['Employee'][] = $user; 
			}else if($user['Group']['alias'] == 'client') {
				$caseFile['Client'][] = $user; 
			}
		}
		// set translations to Case File model
		if (!empty($caseFile['CaseFileI18n'])) {
			foreach($caseFile['CaseFileI18n'] as $translation) {
				$field = $translation['field'];
				$lang = $translation['locale'];
				$caseFile['CaseFile'][$field."_".$lang] = $translation['content'];
			}			
		}		
		$caseFile['CaseFilePersons'] = $this->paginate();	
		$this->set(compact('caseFile'));		
		// Set names of the tabs and set id from request. 
		// These variables are used in templates and elements (view/elements/casfiles)
		$tab = 'details';
		if (array_key_exists('tab',$this->request['url'])) {
			$tab = $this->request['url']['tab']; 
		}
		// Get ul-list with directories and file-links of eFiles
		$filesArr = CaseFile::listEFiles($caseFile['CaseFile']['filenumber']);
		$files = $this->makeList($filesArr);
		// path to case file efiles
		$path = Configure::read('eFilesPath').$caseFile['CaseFile']['filenumber']."-eFile";

		$this->Session->delete('form');
		$this->set('files',$files);
		$this->set('path', $path);
		$this->set('tab',$tab);
		$this->set('tab1','details');
		$this->set('tab2','deadlines');
		$this->set('tab3','assigned');
		$this->set('tab4','efiles');
		$this->set('id', $id);
	}
	
	/**
	 * Create ul-list with links from multidimensional array of files and dirs
	 * 
	 * @param array $arr files and directories
	 */
	private function makeList($arr)
	{
		// sort array natural and case-insensitive
		ksort($arr, SORT_NATURAL | SORT_FLAG_CASE);
		// build list
		$return = '<ul class=\'filelist\'>';
		foreach ($arr as $key=>$item)
		{
			if (is_array($item)) {
				$return .= '<li>'.$key . $this->makeList($item).'</li>';
			}else{
				$return .= '<li><a href="'.$item.'">'.$key.'</a></li>';
			}
		}
		$return .= '</ul>';
		return $return;
	}
	
	
	
	/**
	 * @return void
	 */
	public function client_view($id = null) {
		
		// security check: only CaseFiles associated with the user can be accessed
		$ids = $this->CaseFile->CaseFilesUser->find('list', array('fields'=>array('case_file_id'),'conditions'=>array('CaseFilesUser.user_id'=>$this->Auth->user('id'))));		
		if(!in_array($id, $ids)) {
			$this->Flash->error(__('Invalid record'));
			$this->redirect(array('action' => 'client_index'));	
		}
		
		if (empty($id) || !($caseFile = $this->CaseFile->find('firstConvertDt', array('contain'=>array('PatentOffice', 'PCT_RO', 'PCT_ISA', 'PCT_IEA', 'Deadline', 'CaseFilePriority', 'CaseFilePriority.Country', 'Deadline.DeadlineReason', 'CaseFilesPerson.Person', 'CaseFilesPerson.Person.Personkind','CaseFilesPerson.Persontype.title', 'User'=>'Group.alias', 'Contactuser', 'Status', 'Kind', 'RelCaseFile', 'RelCaseFile.Kind.title', 'RelCaseFile.PatentOffice.name', 'RelCaseFile2', 'RelCaseFile2.Kind.title', 'RelCaseFile2.PatentOffice.name'), 'conditions'=>array('CaseFile.id'=>$id))))) {
			$this->Flash->error(__('invalidRecord'));
			$this->redirect(array('action' => 'client_index'));
		}	
		// Merge arrays: active and passive parts of a relationship from CaseFile to CaseFile
		$caseFile['RelCaseFile']=$caseFile['RelCaseFile']+$caseFile['RelCaseFile2'];
		
		// convert date format of Priorities
		if (!empty($caseFile['CaseFilePriority'])) {
			foreach ($caseFile['CaseFilePriority'] as $key=>$prio) {
				$caseFile['CaseFilePriority'][$key]['date'] = CaseFilePriority::changeDateFormat($prio['date']);
			}
		}

		// Split users in to Clients and Employees
		$caseFile['Employee'] = array();
		$caseFile['Client'] = array();
		foreach ($caseFile['User'] as $user) {
			if ($user['Group']['alias'] != 'superadmin' && $user['Group']['alias'] != 'client') {
				$caseFile['Employee'][] = $user; 
			}else if($user['Group']['alias'] == 'client') {
				$caseFile['Client'][] = $user; 
			}
		}
		$this->set(compact('caseFile'));
		
		$caseFile['Client'] = array();
		foreach ($caseFile['User'] as $user) {
			if ($user['Group']['alias'] == '' || $user['Group']['alias'] != 'client')
				$caseFile['Employee'][] = $user;
		}
		$this->set(compact('caseFile'));
		
		// Set names of the tabs and set id from request. 
		// These variables are used in templates and elements (view/elements/casfiles)
		$tab = 'details';
		if (array_key_exists('tab',$this->request['url'])) {
			$tab = $this->request['url']['tab']; 
		}
		$this->set('tab',$tab);
		$this->set('tab1','details');
		$this->set('tab2','efiles');
		$this->set('id', $id);
	}
	
	

	// Add Case Files in 2 Steps. First Step is selecting Kind and PatentOffice
	public function add() {
		$this->Session->delete('form');
		$this->Session->write('form.params.steps', 2);  //Limit to 2 steps
		$this->Session->write('form.params.maxProgress', 0);
		$this->redirect(array('action' => 'add_step', 1));
	}


	public function add_step($stepNumber) {
		/**
		 * determines the max allowed step (the last completed + 1)
		 * if choosen step is not allowed (URL manually changed) the user gets redirected
		 * otherwise we store the current step value in the session
		 */
		$maxAllowed = $this->Session->read('form.params.maxProgress') + 1;
		if ($stepNumber > $maxAllowed) {
			$this->redirect('/case_files/add_step/'.$maxAllowed);
		} else {
			$this->Session->write('form.params.currentStep', $stepNumber);
		}
		
		/**
		 * check if some data has been submitted via POST
		 * if not, sets the current data to the session data, to automatically populate previously saved fields
		 */
		if ($this->request->is(array('post', 'put'))) {
			

			/**
			 * set passed data to the model, so we can validate against it without saving
			 */
			$this->CaseFile->set($this->request->data);
			
			// If button for adding Priority is pressed, increase the show.priority_count value and save all data to session
			if(isset($this->request->data['add_priority'])) 	{		

				$prevSessionData = $this->Session->read('form.data');
				// Merge new data from form with session old form data, to save any changes				
				$currentSessionData = Hash::merge( (array) $prevSessionData, $this->request->data);
				// Reset the Keys of the priority elements to maintain correct form construction
				if (isset($currentSessionData['CaseFilePriority'])) {
					$currentSessionData['CaseFilePriority'] = array_values($currentSessionData['CaseFilePriority']);
				}
				$cnt_priority = intval($this->Session->read('form.params.show.priority_count')); 
				$cnt_priority++;
				
				$this->Session->write('form.params.show.priority_count', $cnt_priority);
				$this->Session->write('form.params.priority.button', true);
				$this->Session->write('form.data', $currentSessionData);


				$this->redirect(array('action' => 'add_step', $stepNumber));
			}
			
			// If button for adding Priority is pressed, decrease the show.priority_count value and save all data to session
			if(isset($this->request->data['del_priority'])) 	{		
			
				$prevSessionData = $this->Session->read('form.data');
				// Merge new data from form with session old form data, to save any changes
				$currentSessionData = Hash::merge( (array) $prevSessionData, $this->request->data);
				$priorityNumber = $this->request->data['del_priority'];			

				$cnt_priority = intval($this->Session->read('form.params.show.priority_count')); 
				$cnt_priority--;
				$this->Session->write('form.params.show.priority_count', $cnt_priority);
				$this->Session->write('form.params.priority.button', true);
				// Unset Priority in session data
				if (isset($currentSessionData['CaseFilePriority'][$priorityNumber])) {
					unset($currentSessionData['CaseFilePriority'][$priorityNumber]);
					// Reset the Keys of the priority elements to maintain correct form construction
					$currentSessionData['CaseFilePriority'] = array_values($currentSessionData['CaseFilePriority']);
				}
				$this->Session->write('form.data', $currentSessionData);
				$this->redirect(array('action' => 'add_step', $stepNumber));
			}

			// If button for deleting Priority is pressed
			if(isset($this->request->data['del_priority'])) 	{
				$prevSessionData = $this->Session->read('form.data');
				// Merge new data from form with session old form data, to save any changes
				$currentSessionData = Hash::merge( (array) $prevSessionData, $this->request->data);
				$priorityNumber = $this->request->data['del_priority'];
				$priorityId = null;
			
				if (isset($caseFile['CaseFilePriority'][$priorityNumber]['id'])) {
					$priorityId = $caseFile['CaseFilePriority'][$priorityNumber]['id'];
				}
			
				// If the Priority to be deleted has not been saved already (only exists in the form),
				// decrease the show.priority_count value and save all data to session
				if (empty($priorityId) || !($caseFile = $this->CaseFile->CaseFilePriority->find('first', array('conditions'=>array('CaseFilePriority.id'=>$priorityId), 'fields'=>array('id'), 'callbacks' => false)))) {
					$cnt_priority = intval($this->Session->read('form.params.show.priority_count'));
					$cnt_priority--;
					$this->Session->write('form.params.show.priority_count', $cnt_priority);
					$this->Session->write('form.params.priority.button', true);
					// Unset Priority in session data
					if (isset($currentSessionData['CaseFilePriority'][$priorityNumber])) {
						unset($currentSessionData['CaseFilePriority'][$priorityNumber]);
						// Reset the Keys of the priority elements to maintain correct form construction
						$currentSessionData['CaseFilePriority'] = array_values($currentSessionData['CaseFilePriority']);
					}
						
					$this->Session->write('form.data', $currentSessionData);
					$this->redirect(array('action' => 'edit', $id));
					// If the Priority was loaded from the Priority-Table, delete it in the table and in the form
				}else{
					if ($this->CaseFile->CaseFilePriority->delete($priorityId)) {
						$cnt_priority = intval($this->Session->read('form.params.show.priority_count'));
						$cnt_priority--;
						$this->Session->write('form.params.show.priority_count', $cnt_priority);
						$this->Session->write('form.params.priority.button', true);
			$this->Flash->errorhMessage(__('Deleted Priority %1$d. If there was a Priority with a number higher than %1$d, it now has the number %1$d.', h(($priorityNumber+1))), 'success');
						$this->Session->delete('form.params.priority.button');
						// Unset Priority in session data
						if (isset($currentSessionData['CaseFilePriority'][$priorityNumber])) {
							unset($currentSessionData['CaseFilePriority'][$priorityNumber]);
							// Reset the Keys of the priority elements to maintain correct form construction
							$currentSessionData['CaseFilePriority'] = array_values($currentSessionData['CaseFilePriority']);
						}
						$this->Session->write('form.data', $currentSessionData);
						$this->redirect(array('action' => 'edit', $id));
					}
					$this->Flash->error(__('Error while deleting priority #%d', h(($priorityNumber+1))));
					$this->redirect(array('action' => 'edit', $id));
				}
			}

			/**
			 * if data validates we merge previous session data with submitted data, using CakePHP powerful Hash class (previously called Set)
			 */
			if ($this->CaseFile->validates()) {
				
				$prevSessionData = $this->Session->read('form.data');
				$currentSessionData = Hash::merge( (array) $prevSessionData, $this->request->data);

				/**
				 * if this is not the last step we replace session data with the new merged array
				 * update the max progress value and redirect to the next step
				 */
				if ($stepNumber < $this->Session->read('form.params.steps')) {


					$this->Session->write('form.data', $currentSessionData);
					$this->Session->write('form.params.maxProgress', $stepNumber);
					$this->redirect(array('action' => 'add_step', $stepNumber+1));
				} else {
					/**
					 * otherwise, this is the final step, so we have to save the data to the database
					 */
						
					// If there are empty CaseFilePrioritys, dont save them
					$cfPrioritys = $currentSessionData['CaseFilePriority'];
					for ($i = 0; $i<count($cfPrioritys); $i++) {
						if (empty($cfPrioritys[$i]['date']) && empty($cfPrioritys[$i]['number']) && empty($cfPrioritys[$i]['country_id'])) 
								unset($currentSessionData['CaseFilePriority'][$i]);
					}
					if (empty($currentSessionData['CaseFilePriority'])) unset($currentSessionData['CaseFilePriority']);			
					// extract translations
					$translations = array();
					if (!empty($currentSessionData['CaseFile']['title_trans'])) {
						$translations['title'] = $currentSessionData['CaseFile']['title_trans'];
						unset($currentSessionData['CaseFile']['title_trans']);
					}
					if (!empty($currentSessionData['CaseFile']['representation_trans'])) {
						$translations['representation'] = $currentSessionData['CaseFile']['representation_trans'];
						unset($currentSessionData['CaseFile']['representation_trans']);
					}
					$this->CaseFile->locale = 'eng';
					$errors = 0;
					if ($this->CaseFile->saveAll($currentSessionData, array('deep' => true))) {
						if (!empty($translations)) {
							$cfData = $currentSessionData['CaseFile'];
							$save['CaseFile'] = $cfData;
							$save['CaseFile']['id'] = $this->CaseFile->getLastInsertId();
							foreach ($translations as $field=>$translation) {
								if (!empty($translation)) {
									foreach ($translation as $locale=>$value) {
										if (!empty($value)) {
											$this->CaseFile->locale = $locale;
											$save['CaseFile'][$field] = strtoupper($value); 
											if(!$this->CaseFile->save($save)) {
												$errors += 1;
											}											
										}		
									}
								}
							}
						}
					} else {
						$errors += 1;						
					}
					if ($errors == 0) {
						$this->Flash->success(__('Case File added'));
						$this->Session->delete('form');
						$this->redirect(array('action' => 'view', $this->CaseFile->id));
					}else{
						$this->Flash->error(__('Form contains errors'));
					}
				}
			}
		} else {

			$this->request->data = $this->Session->read('form.data');
		}

		// Dynamically generate fileds of the formular in step 2 and generate the filenumber, 
		// depending on selected Kind and Patentoffice in step 1
		if ($stepNumber >= 2) {

			// Get fields 'short' and 'number' from selected Kind and PatentOffice for filenumber generation
			$kind =  $this->CaseFile->Kind->field('short', array('id' => $this->Session->read('form.data.CaseFile.kind_id')));
			$pOfficeNumber = $this->CaseFile->PatentOffice->field('number', array('PatentOffice.id' => $this->Session->read('form.data.CaseFile.patent_office_id')));
			$id = $this->CaseFile->field('id', array(), array('id DESC')) + 1;
											
			// Generate filenumber
			$filenumber = CaseFile::filenumber($kind, $pOfficeNumber, $id);				
			$this->Session->write('form.data.CaseFile.filenumber', $filenumber);
				
			// Read from database which fields of Case File will be shown in step 2. 		
			$fields = array();
			$unusedFields = array();
			$this->loadModel('CaseFileField');
			if ($kind != '')	{
				// If Kind is set, find the corresponding CaseFileField-Record, containing the available fields
								
				$kindsForAllCountrys = array('E','N','L','K','T','V','S','Q','R');
				$caseFileFields = null;			
				if (in_array($kind, $kindsForAllCountrys)) {
					// If Kind is available for all countrys, CaseFileFields can be searched only by KindShort
					$caseFileFields = $this->CaseFileField->findByKindShort($kind);
				}else{
					// If Kind is depending on Kind and Country, CaseFileFields must be searched only by KindShort and CountryNumber
					$caseFileFields = $this->CaseFileField->findByCountryNumberAndKindShort($pOfficeNumber, $kind);
				}
				
				// If there are no values in the databasew for a special combination, throw an error			
				if ($caseFileFields['CaseFileField']['id'] == "") {
					$this->Session->setFlash('This combination of PatentOffice and Kind is not available in database');
					$this->redirect('/case_files/add');
				}
							
				// Create an array of the database values for viewable fields
				$fields = explode(";",$caseFileFields['CaseFileField']['fields']);
				$allCaseFileFields = $this->CaseFileField->findByKindShort('ALLFIELDS'); 
				$allFields = explode(";",$allCaseFileFields['CaseFileField']['fields']);
				$unusedFields = array_diff($allFields, $fields);
			}else{
				// If Kind is not set, show all fields
				$allCaseFileFields = $this->CaseFileField->findByKindShort('ALLFIELDS'); 
				$fields = explode(";",$allCaseFileFields['CaseFileField']['fields']);
			}

			// Write that array to session
			foreach ($fields as $field) {
				$this->Session->write('form.params.show.'.$field, True);			
			}
			foreach ($unusedFields as $unusedField) {
				$this->Session->write('form.params.show.'.$unusedField, False);			
			}
			
			// Check if priority should be showed: If the add_priority or del_priority button was not pressed an if Kind is not A,K or T
			$button = $this->Session->read('form.params.priority.button');
			if (!isset($button)) {
				if ($this->Session->read('form.params.show.priority') == true) {
						$this->Session->write('form.params.show.priority_count', 1);
				}
			}
			
						
		}
		
		$employees = $this->CaseFile->User->find('all', array('fields'=>array('id', 'username', 'role_id'), 'contain'=>array('Group.name'), 'conditions'=>array('NOT' => array('Group.alias' => array('client','superadmin')))));
		$employees = Set::combine($employees, '{n}.User.id', array('{0}', '{n}.User.username'), '{n}.Group.name');
		$patentOffices = $this->CaseFile->PatentOffice->find('list');
		$this->set('kinds', $this->CaseFile->Kind->find('list'));
		$this->set('statuses', $this->CaseFile->Status->find('list'));
		$this->set('countries', $this->CaseFile->CaseFilePriority->Country->find('list', array('order' => array('Country.name' => 'asc'))));
		$this->set(compact('patentOffices', 'employees'));
		/**
		 * here we load the proper view file, depending on the stepNumber variable passed via GET
		 */
		$this->render('add_step_'.$stepNumber);
	}
	
	

	
	// Get Case File fields by patentOfficeId and kindId to dynamically generate fileds in the form and generate the filenumber,
	public function getCaseFileFields ($patentOfficeId, $kindId = '', $filenumber = null) {
		
		if (!isset($filenumber) || !isset($patentOfficeId)) return false;
		$return = array();
		
		// Get fields 'short' and 'number' from selected Kind and PatentOffice for filenumber generation
		$kind =  $this->CaseFile->Kind->field('short', array('id' => $kindId));
		$pOfficeNumber = $this->CaseFile->PatentOffice->field('number', array('PatentOffice.id' => $patentOfficeId));
				
		// Generate filenumber
		if (empty($filenumber)) {
			$id = $this->CaseFile->field('id', array(), array('id DESC')) + 1;
			$return['filenumber'] = CaseFile::filenumber($kind, $pOfficeNumber, $id); //originally saved to session
		}else{
			$return['filenumber'] = CaseFile::changeFilenumber($filenumber, $kind, $pOfficeNumber); //originally saved to session
		}
	
		// Read from database which fields of Case File will be shown in step 2.
		$fields = array();
		$unusedFields = array();
		$this->loadModel('CaseFileField');
		if ($kind != '')	{
			// If Kind is set, find the corresponding CaseFileField-Record, containing the available fields
			$kindsForAllCountrys = array('E','N','L','K','T','V','S','Q','R');
			$caseFileFields = null;
			if (in_array($kind, $kindsForAllCountrys)) {
				// If Kind is available for all countrys, CaseFileFields can be searched only by KindShort
				$caseFileFields = $this->CaseFileField->findByKindShort($kind);
			}else{
				// If Kind is depending on Kind and Country, CaseFileFields must be searched only by KindShort and CountryNumber
				$caseFileFields = $this->CaseFileField->findByCountryNumberAndKindShort($pOfficeNumber, $kind);
			}
	
			$return['fields'] = array();
			// If there are no values in the databasew for a special combination, throw an error
			if (empty($caseFileFields['CaseFileField']['id']) || empty($caseFileFields['CaseFileField']['fields'])) {
				//$this->Session->setFlash('This combination of PatentOffice and Kind is not available in database');
				$return['errors'][] = 'This combination of PatentOffice and Kind is not available in database';
				//$this->redirect('/case_files/add');
			}else{
				// Create an array of the database values for viewable fields
				$return['fields'] = explode(";",$caseFileFields['CaseFileField']['fields']);
			}
			$allCaseFileFields = $this->CaseFileField->findByKindShort('ALLFIELDS');
			$allFields = explode(";",$allCaseFileFields['CaseFileField']['fields']);
			$tempArr = array_diff($allFields, $return['fields']);
			$return['unusedFields'] = array_values($tempArr);
		}else{
			// If Kind is not set, show all fields
			$allCaseFileFields = $this->CaseFileField->findByKindShort('ALLFIELDS');
			$return['fields'] = explode(";",$allCaseFileFields['CaseFileField']['fields']); // write to session with for each
		}
	
		return $return;
	
	}

	// return json with case file fields and filenumber
	public function get_fields_filenumber ($patentOfficeId, $kindId = '', $filenumber = null) {
		$this->autoRender = false;
		//$this->request->onlyAllow('ajax');
		$json = "[]";
		$return =  $this->CaseFile->getCaseFileFields($patentOfficeId, $kindId, $filenumber);//$return = $this->getCaseFileFields($patentOfficeId, $kindId, $filenumber);
		if (!empty($return)) {
			$json = json_encode($return);
		}
		return $json;		
	}


	// Unlock a Case File, so that it can be edited again by other users
	public function unlock($id) {
		$locked_by = $this->CaseFile->field('locked_by', array('id' => $id));
		$filenumber = $this->CaseFile->field('filenumber', array('id' => $id));
		
		// Only the user who locked the Case file, the admin and the superadmin can unlock the Case File
		if (!($lockedBy == $this->Session->read('Auth.User.id') || $this->Session->read('Auth.User.Group.alias') == 'admin' || $this->Session->read('Auth.User.Group.alias') == 'superadmin')) {
			$this->Flash->error(__('You are not authorized to access this location'));
			$this->redirect(array('action' => 'index'));
		}
		
		$cfFields = array('CaseFile'=>array('id'=>$id, 'locked_at'=> NULL, 'locked_by'=> NULL));
		$this->Session->delete('form');
		if ($this->CaseFile->save($cfFields)) {
			$this->Flash->success(__('Case File %s has been unlocked', $filenumber));
			$this->redirect(array('action' => 'index'));
		}else{
			$this->Flash->error(__('Case File %s could not be unlocked. Please try again', $filenumber));
			$this->redirect(array('action' => 'index'));
		}
	}
	

	public function edit($id = null) {
		if (empty($id) || !($caseFile = $this->CaseFile->find('firstConvertDt', array('contain'=>array('CaseFileI18n', 'PatentOffice.number', 'Kind.short', 'CaseFilePriority'), 'conditions'=>array('CaseFile.id'=>$id))))) {
			$this->Flash->error(__('Invalid record'.$id));
			$this->redirect(array('action' => 'index'));
		}
		
		// convert date format of Priorities
		if (!empty($caseFile['CaseFilePriority'])) {
			foreach ($caseFile['CaseFilePriority'] as $key=>$prio) {
				$caseFile['CaseFilePriority'][$key]['date'] = CaseFilePriority::changeDateFormat($prio['date']);
			}
		}
		
		// set translations to Case File model
		if (!empty($caseFile['CaseFileI18n'])) {
			$trans = array();
			foreach($caseFile['CaseFileI18n'] as $translation) {
				$field = $translation['field'];
				$lang = $translation['locale'];
				$trans['CaseFile'][$field][$lang] = $translation['content'];
			}
			$caseFile['CaseFile'] = array_merge($caseFile['CaseFile'], $trans['CaseFile']);
			unset($caseFile['CaseFileI18n']);
		}
	
		// Delete session entry 'form' if it has a different id compared to this object
		$idSess = $this->Session->read('form.data.CaseFile.id');
		if ($idSess != $id)
			$this->Session->delete('form');
	
		$lock = true;
		$lockTimeSec = (intval(Configure::read('LockTime'))*60);
		$editTimeSec = $lockTimeSec;
		$lockedDate = null;
		$diff_seconds = null;
		$currentDate = date_create('now');
		// Check if CaseFile is already being edited via 'locked_at' field
		if (!empty($caseFile['CaseFile']['locked_at'])) {
				
			$dateStrMod = date("Y-m-d H:i:s", strtotime($caseFile['CaseFile']['locked_at']));
			$lockedDate = date_create($dateStrMod);
				
			$diff_seconds = $currentDate->format('U') - $lockedDate->format('U');
				
	
			$editTimeSec = $lockTimeSec-$diff_seconds;
			if ($caseFile['CaseFile']['locked_by'] != $this->Session->read('Auth.User.id')) {
				if ($diff_seconds < $lockTimeSec) {
					$timeToExpire = round(($lockTimeSec - $diff_seconds)/60);
					$this->Flash->error(__('Case File %s is already being edited. <br /> It will be editable again when current edit is finished or when the time for editing has expired in %2d minutes.', $caseFile['CaseFile']['filenumber'], $timeToExpire));
					$this->redirect(array('action' => 'index'));
				}
			}
				
		}
		// Insert timestamp into field 'locked_at' to prevent other users from editing the same Case File at the same time
		if ($caseFile['CaseFile']['locked_by'] != $this->Session->read('Auth.User.id') && !isset($this->request->data['cf_submit'])) {
			$cfFields = array('CaseFile'=>array('id'=>$id, 'locked_at'=> date('Y-m-d H:i:s'), 'locked_by' => $this->Session->read('Auth.User.id')));
			$this->CaseFile->save($cfFields);
			if (empty($diff_Seconds)) {
				$editTimeSec = $lockTimeSec;
					
			}else{
				$editTimeSec = $lockTimeSec-$diff_seconds;
				if ($editTimeSec < 0) $editTimeSec = 0;
			}
			$editTime = round($editTimeSec/60);
			$this->Flash->error(__('This Case File is locked for %d minutes and cannot be changed by other users.
					<br/> Please use the button "List Case Files" or "Cancel" to abort editing and unlock the Case File again.', $editTime));
		}
	
	
		if ($this->request->is(array('post', 'put'))) {
				
			// If Cancel Button was pressed redirect to unlock()-function
			if(isset($this->request->data['cf_cancel'])) 	{
				$this->unlock($this->request->data['CaseFile']['id']);
			}
				
			// If button for adding Priority is pressed, increase the show.priority_count value and save all data to session
			if(isset($this->request->data['add_priority'])) 	{
				$prevSessionData = $this->Session->read('form.data');
				// Merge new data from form with session old form data, to save any changes
				$currentSessionData = Hash::merge( (array) $prevSessionData, $this->request->data);
				$cnt_priority = 0;
				// Reset the Keys of the priority elements to maintain correct form construction
				if (isset($currentSessionData['CaseFilePriority'])) {
					$currentSessionData['CaseFilePriority'] = array_values($currentSessionData['CaseFilePriority']);
					$cnt_priority = sizeof($currentSessionData['CaseFilePriority']);
				}
				$cnt_priority++;
				$this->Session->write('form.params.show.priority_count', $cnt_priority);
				$this->Session->write('form.params.priority.button', true);
				$this->Session->write('form.data', $currentSessionData);
				$this->redirect(array('action' => 'edit', $id));
	
			}
				
			// If button for deleting Priority is pressed
			if(isset($this->request->data['del_priority'])) 	{
				$prevSessionData = $this->Session->read('form.data');
				// Merge new data from form with session old form data, to save any changes
				$currentSessionData = Hash::merge( (array) $prevSessionData, $this->request->data);
				$priorityNumber = $this->request->data['del_priority'];
				$priorityId = null;
	
				if (isset($caseFile['CaseFilePriority'][$priorityNumber]['id'])) {
					$priorityId = $caseFile['CaseFilePriority'][$priorityNumber]['id'];
				}
	
				// If the Priority to be deleted has not been saved already (only exists in the form),
				// decrease the show.priority_count value and save all data to session
				if (empty($priorityId) || !($caseFile = $this->CaseFile->CaseFilePriority->find('first', array('conditions'=>array('CaseFilePriority.id'=>$priorityId), 'fields'=>array('id'), 'callbacks' => false)))) {
					$cnt_priority = intval($this->Session->read('form.params.show.priority_count'));
					$cnt_priority--;
					$this->Session->write('form.params.show.priority_count', $cnt_priority);
					$this->Session->write('form.params.priority.button', true);
					// Unset Priority in session data
					if (isset($currentSessionData['CaseFilePriority'][$priorityNumber])) {
						unset($currentSessionData['CaseFilePriority'][$priorityNumber]);
						// Reset the Keys of the priority elements to maintain correct form construction
						$currentSessionData['CaseFilePriority'] = array_values($currentSessionData['CaseFilePriority']);
					}
						
					$this->Session->write('form.data', $currentSessionData);
					$this->redirect(array('action' => 'edit', $id));
					// If the Priority was loaded from the Priority-Table, delete it in the table and in the form
				}else{
					if ($this->CaseFile->CaseFilePriority->delete($priorityId)) {
						$cnt_priority = intval($this->Session->read('form.params.show.priority_count'));
						$cnt_priority--;
						$this->Session->write('form.params.show.priority_count', $cnt_priority);
						$this->Session->write('form.params.priority.button', true);
	
						$this->Flash->error(__('Deleted Priority %1$d. If there was a Priority with a number higher than %1$d, it now has the number %1$d.', h(($priorityNumber+1))));
						$this->Session->delete('form.params.priority.button');
						// Unset Priority in session data
						if (isset($currentSessionData['CaseFilePriority'][$priorityNumber])) {
							unset($currentSessionData['CaseFilePriority'][$priorityNumber]);
							// Reset the Keys of the priority elements to maintain correct form construction
							$currentSessionData['CaseFilePriority'] = array_values($currentSessionData['CaseFilePriority']);
						}
						$this->Session->write('form.data', $currentSessionData);
						$this->redirect(array('action' => 'edit', $id));
					}
					$this->Flash->error(__('Error while deleting priority #%d', h(($priorityNumber+1))));
					$this->redirect(array('action' => 'edit', $id));
				}
			}
				
			// reset locked_at field
			$this->request->data['CaseFile']['locked_at'] = NULL;
			$this->request->data['CaseFile']['locked_by'] = NULL;
	
			// Only save if the file has not been changed by anyone else
			$locked_by = $this->CaseFile->field('locked_by', array('id' => $id));
			if ($locked_by == $this->Session->read('Auth.User.id')) {
				// If there are empty CaseFilePrioritys, dont save them
				$req = $this->request->data;
				if (!empty($req['CaseFilePriority'])) {
					$cfPrioritys = $req['CaseFilePriority'];
					for ($i = 0; $i<count($cfPrioritys); $i++) {
						if (empty($cfPrioritys[$i]['date']) && empty($cfPrioritys[$i]['number']) && empty($cfPrioritys[$i]['country_id']))
							unset($req['CaseFilePriority'][$i]);
					}
					if (empty($req['CaseFilePriority'])) unset($req['CaseFilePriority']);
				}
				
				// extract translations
				$translations = array();
				if (!empty($req['CaseFile']['title_trans'])) {
					$translations['title'] = $req['CaseFile']['title_trans'];
					unset($req['CaseFile']['title_trans']);
				}
				if (!empty($req['CaseFile']['representation_trans'])) {
					$translations['representation'] = $req['CaseFile']['representation_trans'];
					unset($req['CaseFile']['representation_trans']);
				}
				
				$this->CaseFile->locale = 'eng';
				$errors = 0;
				if ($this->CaseFile->saveAll($req, array('deep' => true))) {
					// save treanslations
					if (!empty($translations)) {
						$cfData = $req['CaseFile'];
						$save['CaseFile'] = $cfData;
						$save['CaseFile']['id'] = $this->request->data['CaseFile']['id'];
						foreach ($translations as $field=>$translation) {
							if (!empty($translation)) {
								foreach ($translation as $locale=>$value) {
									$this->CaseFile->locale = $locale;
									$save['CaseFile'][$field] = strtoupper($value);
									if(!$this->CaseFile->save($save)) {
										$errors += 1;
									}
								}
							}
						}
					}
				} else {
					$errors += 1;
				}
				if ($errors == 0) {
					$this->Flash->success(__('Case File added'));
					$this->Session->delete('form');
					$this->redirect(array('action' => 'view', $this->request->data['CaseFile']['id']));
				}else{
					$this->Flash->error(__('Form contains errors'));
				}
				
				
			}else{
				$this->Flash->error(__('Your time for editing the Case File has run out and meanwhile another user edited this Case File. <br> Please try editing again'));
				$this->Session->delete('form');
				$this->redirect(array('action' => 'view', $this->request->data['CaseFile']['id']));
			}
		}
		if (empty($this->request->data)) {
			// Merge data from session and database and set to form
			if (!empty($this->Session->read('form.data'))) {
				$form = $this->Session->read('form.data');
				if (!empty($form['CaseFile']['title_trans'])) {
					$form['CaseFile']['title'] = $form['CaseFile']['title_trans'];
					unset($form['CaseFile']['title_trans']);
				}
				$caseFile = Hash::merge((array)$caseFile, $form);
				unset($form);
			}
			$this->request->data = $caseFile;
			
		}
	
		$kind = '';
		if (!empty($caseFile['Kind']['short'])) $kind = $caseFile['Kind']['short'];
		$pOfficeNumber = '';
		if (!empty($caseFile['PatentOffice']['number'])) 	$pOfficeNumber = $caseFile['PatentOffice']['number'];

		$fields = array();
		$unusedFields = array();
			
		$this->loadModel('CaseFileField');
		if ($kind != '')	{
			// If Kind is set, find the corresponding CaseFileField-Record, containing the available fields
	
			$kindsForAllCountrys = array('E','N','L','K','T','V','S','Q','R');
			$caseFileFields = null;
			if (in_array($kind, $kindsForAllCountrys)) {
				// If Kind is available for all countrys, CaseFileFields can be searched only by KindShort
				$caseFileFields = $this->CaseFileField->findByKindShort($kind);
			}else{
				// If Kind is depending on Kind and Country, CaseFileFields must be searched only by KindShort and CountryNumber
				$caseFileFields = $this->CaseFileField->findByCountryNumberAndKindShort($pOfficeNumber, $kind);
			}
	
			// If there are no values in the databasew for a special combination, throw an error
			if (empty($caseFileFields) || $caseFileFields['CaseFileField']['id'] == "") {
				$this->Session->setFlash('This combination of PatentOffice and Kind is not available in database');
				$this->redirect('/case_files');
			}
				
			// Create an array of the database values for viewable fields
			$fields = explode(";",$caseFileFields['CaseFileField']['fields']);
			$allCaseFileFields = $this->CaseFileField->findByKindShort('ALLFIELDS');
			$allFields = explode(";",$allCaseFileFields['CaseFileField']['fields']);
			$unusedFields = array_diff($allFields, $fields);
		}else{
			// If Kind is not set, show all fields
			$allCaseFileFields = $this->CaseFileField->findByKindShort('ALLFIELDS');
			$fields = explode(";",$allCaseFileFields['CaseFileField']['fields']);
		}
	
		// Write that array to session
		foreach ($fields as $field) {
			$this->Session->write('form.params.show.'.$field, True);
		}
		foreach ($unusedFields as $unusedField) {
			$this->Session->write('form.params.show.'.$unusedField, False);
		}
			
		// Check if priority should be showed: If the add_priority or del_priority button was not pressed an if Kind is not A,K or T
		$button = $this->Session->read('form.params.priority.button');
		if (!isset($button)) {
			if ($this->Session->read('form.params.show.priority')==true) {
				$cnt_priority = count($caseFile['CaseFilePriority']); $this->Session->write('form.params.show.priority_count', $cnt_priority);
			}else{
				$this->Session->write('form.params.show.priority_count', 0);
			}
		}
			
			
		// Controller logic for showing PCT fields via checking if PCT data is already set on the model
		// Default value for variable that is passed to the view
		$pctFields = 0;
	
		// Get all keys from the model-array starting with 'chin_'
		$cfKeys = array_keys($caseFile['CaseFile']);
		$pctFieldNames = array();
		foreach ($cfKeys as $key) {
	
			if (substr($key, 0, 4) == 'pct_' && $key != 'pct_application_date') {
				$pctFieldNames[] = $key;
			}
		}
	
		// Check if pct data is already set on the model
		foreach($pctFieldNames as $pctFieldName) {
			if ($caseFile['CaseFile'][$pctFieldName] != "") {
				// if there is data, set view-variable to 1 (true)
				$pctFields = 1;
			}
		}
	
		// Unset now useless stuff for saving ressources
		unset($cfKeys, $pctFieldNames);
	
	
		$this->set('pctFields',$pctFields);
		// End of controller logic for showing PCT data
			
			
		// Set values for select boxes
		$patentOffices = $this->CaseFile->PatentOffice->find('list');
		$this->set('kinds', $this->CaseFile->Kind->find('list'));
		$this->set('statuses', $this->CaseFile->Status->find('list'));
		//$this->set('contactusers', $this->CaseFile->Contactuser->find('list', array('contain'=>array('Group.alias'),'conditions' =>array('Group.alias'=>array('admin', 'deadline_employee', 'employee')))));
		$this->set('countries', $this->CaseFile->CaseFilePriority->Country->find('list', array('order' => array('Country.name' => 'asc'))));
		$this->set('id', $caseFile['CaseFile']['id']);
		$this->set(compact('patentOffices'));
		$this->set(compact('editTimeSec'));
	}
	
	
	public function edit_jquery($id = null) {		
		if (empty($id) || !($caseFile = $this->CaseFile->find('firstConvertDt', array('contain'=>array('CaseFileI18n', 'PatentOffice.number', 'Kind.short', 'CaseFilePriority'), 'conditions'=>array('CaseFile.id'=>$id))))) {
			$this->Flash->error(__('Invalid record'.$id));
			$this->redirect(array('action' => 'index'));
		}		
		
		//$this->CaseFile->locale = array('eng');
		
		// convert date format of Priorities
		if (!empty($caseFile['CaseFilePriority'])) {
			foreach ($caseFile['CaseFilePriority'] as $key=>$prio) {
				$caseFile['CaseFilePriority'][$key]['date'] = CaseFilePriority::changeDateFormat($prio['date']);
			}	
		}
		
		// set translations to Case File model
		if (!empty($caseFile['CaseFileI18n'])) {
			$trans = array();
			foreach($caseFile['CaseFileI18n'] as $translation) {
				$field = $translation['field'];
				$lang = $translation['locale'];
				$trans['CaseFile'][$field][$lang] = $translation['content'];
			}
			$caseFile['CaseFile'] = array_merge($caseFile['CaseFile'], $trans['CaseFile']);
			unset($caseFile['CaseFileI18n']);
		}
		
		
		// Delete session entry 'form' if it has a different id compared to this object
		$idSess = $this->Session->read('form.data.CaseFile.id');
		if ($idSess != $id)
			$this->Session->delete('form');
		
		$lock = true;
		$lockTimeSec = (intval(Configure::read('LockTime'))*60);
		$editTimeSec = $lockTimeSec;
		$lockedDate = null;
		$diff_seconds = null;
		$currentDate = date_create('now');
		// Check if CaseFile is already being edited via 'locked_at' field
		if (!empty($caseFile['CaseFile']['locked_at'])) {			
			
			$dateStrMod = date("Y-m-d H:i:s", strtotime($caseFile['CaseFile']['locked_at']));				
			$lockedDate = date_create($dateStrMod);
			
			$diff_seconds = $currentDate->format('U') - $lockedDate->format('U');
			

			$editTimeSec = $lockTimeSec-$diff_seconds;
			if ($caseFile['CaseFile']['locked_by'] != $this->Session->read('Auth.User.id')) {		
				if ($diff_seconds < $lockTimeSec) {
					$timeToExpire = round(($lockTimeSec - $diff_seconds)/60);//$interval->i;
					$this->Flash->error(__('Case File %s is already being edited. <br /> It will be editable again when current edit is finished or when the time for editing has expired in %2d minutes.', $caseFile['CaseFile']['filenumber'], $timeToExpire));
					$this->redirect(array('action' => 'index'));
				}
			}
			
		}
		// Insert timestamp into field 'locked_at' to prevent other users from editing the same Case File at the same time
		if ($caseFile['CaseFile']['locked_by'] != $this->Session->read('Auth.User.id') && !isset($this->request->data['cf_submit'])) {
			$cfFields = array('CaseFile'=>array('id'=>$id, 'locked_at'=> date('Y-m-d H:i:s'), 'locked_by' => $this->Session->read('Auth.User.id')));
			$this->CaseFile->save($cfFields);
		if (empty($diff_Seconds)) {
			$editTimeSec = $lockTimeSec;
			
		}else{
			$editTimeSec = $lockTimeSec-$diff_seconds;
			if ($editTimeSec < 0) $editTimeSec = 0;
		}
		$editTime = round($editTimeSec/60);
		$this->Flash->success(__('This Case File is locked for %d minutes and cannot be changed by other users. 
<br/> Please use the button "List Case Files" or "Cancel" to abort editing and unlock the Case File again.', $editTime));
		}
		

		if ($this->request->is(array('post', 'put'))) {
			
			// If Cancel Button was pressed redirect to unlock()-function
			if(isset($this->request->data['cf_cancel'])) 	{
				$this->unlock($this->request->data['CaseFile']['id']);
			}
					
			// If button for adding Priority is pressed, increase the show.priority_count value and save all data to session
			if(isset($this->request->data['add_priority'])) 	{
				$prevSessionData = $this->Session->read('form.data');
				// Merge new data from form with session old form data, to save any changes
				$currentSessionData = Hash::merge( (array) $prevSessionData, $this->request->data);
				$cnt_priority = 0;
				// Reset the Keys of the priority elements to maintain correct form construction
				if (isset($currentSessionData['CaseFilePriority'])) {
					$currentSessionData['CaseFilePriority'] = array_values($currentSessionData['CaseFilePriority']);
					$cnt_priority = sizeof($currentSessionData['CaseFilePriority']);
				}
				$cnt_priority++;		
				$this->Session->write('form.params.show.priority_count', $cnt_priority);
				$this->Session->write('form.params.priority.button', true);
				$this->Session->write('form.data', $currentSessionData);
				$this->redirect(array('action' => 'edit', $id));
				
			}
			
			// If button for deleting Priority is pressed
			if(isset($this->request->data['del_priority'])) 	{	
				$prevSessionData = $this->Session->read('form.data');
				// Merge new data from form with session old form data, to save any changes
				$currentSessionData = Hash::merge( (array) $prevSessionData, $this->request->data);
				$priorityNumber = $this->request->data['del_priority'];
				$priorityId = null;
				
				if (isset($caseFile['CaseFilePriority'][$priorityNumber]['id'])) {
					$priorityId = $caseFile['CaseFilePriority'][$priorityNumber]['id'];
				}
				
				// If the Priority to be deleted has not been saved already (only exists in the form),
				// decrease the show.priority_count value and save all data to session
				if (empty($priorityId) || !($caseFile = $this->CaseFile->CaseFilePriority->find('first', array('conditions'=>array('CaseFilePriority.id'=>$priorityId), 'fields'=>array('id'), 'callbacks' => false)))) {
					$cnt_priority = intval($this->Session->read('form.params.show.priority_count')); 
					$cnt_priority--;	
					$this->Session->write('form.params.show.priority_count', $cnt_priority);
					$this->Session->write('form.params.priority.button', true);
					// Unset Priority in session data								
					if (isset($currentSessionData['CaseFilePriority'][$priorityNumber])) {
						unset($currentSessionData['CaseFilePriority'][$priorityNumber]);	
						// Reset the Keys of the priority elements to maintain correct form construction
						$currentSessionData['CaseFilePriority'] = array_values($currentSessionData['CaseFilePriority']);
					}
					
					$this->Session->write('form.data', $currentSessionData);
					$this->redirect(array('action' => 'edit', $id));
				// If the Priority was loaded from the Priority-Table, delete it in the table and in the form
				}else{
					if ($this->CaseFile->CaseFilePriority->delete($priorityId)) {
						$cnt_priority = intval($this->Session->read('form.params.show.priority_count'));
						$cnt_priority--;
						$this->Session->write('form.params.show.priority_count', $cnt_priority);
						$this->Session->write('form.params.priority.button', true);
						
						$this->Flash->success(__('Deleted Priority %1$d. If there was a Priority with a number higher than %1$d, it now has the number %1$d.', h(($priorityNumber+1))));
						$this->Session->delete('form.params.priority.button');
						// Unset Priority in session data
						if (isset($currentSessionData['CaseFilePriority'][$priorityNumber])) {
							unset($currentSessionData['CaseFilePriority'][$priorityNumber]);
							// Reset the Keys of the priority elements to maintain correct form construction
							$currentSessionData['CaseFilePriority'] = array_values($currentSessionData['CaseFilePriority']);
						}						
						$this->Session->write('form.data', $currentSessionData);
						$this->redirect(array('action' => 'edit', $id));
					}
					$this->Flash->error(__('Error while deleting priority #%d', h(($priorityNumber+1))));
					$this->redirect(array('action' => 'edit', $id));
				}
			}	

			// hidden input in order to check if combination of kind and patentoffice is available in database
			if ($this->CaseFile->validates()) {
			
				// reset locked_at field
				$this->request->data['CaseFile']['locked_at'] = NULL;
				$this->request->data['CaseFile']['locked_by'] = NULL;
	
				// Only save if the file has not been changed by anyone else
				$locked_by = $this->CaseFile->field('locked_by', array('id' => $id));	
				if ($locked_by == $this->Session->read('Auth.User.id')) {
					// If there are empty CaseFilePrioritys, dont save them
					$req = $this->request->data;
					if (!empty($req['CaseFilePriority'])) {
						$cfPrioritys = $req['CaseFilePriority'];
						for ($i = 0; $i<count($cfPrioritys); $i++) {
							if (empty($cfPrioritys[$i]['date']) && empty($cfPrioritys[$i]['number']) && empty($cfPrioritys[$i]['country_id']))
								unset($req['CaseFilePriority'][$i]);
						}
						if (empty($req['CaseFilePriority'])) unset($req['CaseFilePriority']);
					}
					// update CaseFile and related objects
					if ($this->CaseFile->saveAll($req, array($deep = true))) {	
						$this->Flash->success(__('Case File updated'));
						$this->Session->delete('form');					
						$this->redirect(array('action' => 'view', $this->request->data['CaseFile']['id']));
					} else {
						$this->Flash->error(__('Form contains errors'));
					}
				}else{
						$this->Flash->error(__('Your time for editing the Case File has run out and meanwhile another user edited this Case File. <br> Please try editing again'));
						$this->Session->delete('form');
						$this->redirect(array('action' => 'view', $this->request->data['CaseFile']['id']));
				}
			}else{
				$this->Flash->error(__('Form contains errors'));
			}
		}
		if (empty($this->request->data)) {
			// Merge data from session and database and set to form
			
			$caseFile = Hash::merge( (array)$caseFile, $this->Session->read('form.data'));
			$this->request->data = $caseFile;
		}
		
			// get case file fields as arrays
			$kindId = empty($caseFile['CaseFile']['kind_id']) ? '' : $caseFile['CaseFile']['kind_id'];
			$pOfficeId = empty($caseFile['CaseFile']['patent_office_id']) ? '' : $caseFile['CaseFile']['patent_office_id'];
			$filenumber = empty($caseFile['CaseFile']['filenumber']) ? '' : $caseFile['CaseFile']['filenumber'];
				
			$return =  $this->CaseFile->getCaseFileFields($pOfficeId, $kindId, $filenumber);//$return = $this->getCaseFileFields($patentOfficeId, $kindId, $filenumber);
			// Write these arrays to session
			if (!empty($return['fields'])) {
				foreach ($return['fields'] as $field) {
					$this->Session->write('form.params.show.'.$field, True);			
				}
			}
			if (!empty($return['unusedFields'])) {
				foreach ($return['unusedFields'] as $unusedField) {
					$this->Session->write('form.params.show.'.$unusedField, False);			
				}
			}
			
			// Check if priority should be showed: If the add_priority or del_priority button was not pressed an if Kind is not A,K or T
			//FIX: Get value from CaseFields (Append list of combinations with priority[number])
			$button = $this->Session->read('form.params.priority.button');
			if (!isset($button)) {
				if ($this->Session->read('form.params.show.priority')==true) {
						$cnt_priority = count($caseFile['CaseFilePriority']); $this->Session->write('form.params.show.priority_count', $cnt_priority);
				}else{
						$this->Session->write('form.params.show.priority_count', 0);
				}
			}
			
			
		// Controller logic for showing PCT fields via checking if PCT data is already set on the model
			// Default value for variable that is passed to the view		
			$pctFields = 0;		      

	    	// Get all keys from the model-array starting with 'chin_'
			$cfKeys = array_keys($caseFile['CaseFile']);
			$pctFieldNames = array();		
			foreach ($cfKeys as $key) {
				
				if (substr($key, 0, 4) == 'pct_' && $key != 'pct_application_date') {
					$pctFieldNames[] = $key;
				}
			}

			// Check if pct data is already set on the model
			foreach($pctFieldNames as $pctFieldName) {
				if ($caseFile['CaseFile'][$pctFieldName] != "") {
				// if there is data, set view-variable to 1 (true)			
				$pctFields = 1;				
				}	
			}
						
			// Unset now useless stuff for saving ressources
			unset($cfKeys, $pctFieldNames);
	
	
			$this->set('pctFields',$pctFields);
		// End of controller logic for showing PCT data		
			
			
		// Set values for select boxes
		$patentOffices = $this->CaseFile->PatentOffice->find('list');
		$this->set('kinds', $this->CaseFile->Kind->find('list'));
		$this->set('statuses', $this->CaseFile->Status->find('list'));
		$this->set('countries', $this->CaseFile->CaseFilePriority->Country->find('list', array('order' => array('Country.name' => 'asc'))));
		$this->set('id', $caseFile['CaseFile']['id']);
		$this->set(compact('patentOffices'));
		$this->set(compact('editTimeSec'));
	}
	
	/**
	 * Assign a User to a Case-File 
	 * @return void
	 */
	public function assign_user($id = null) {
		if (empty($id) || !($caseFile = $this->CaseFile->find('first', array('fields' => array('id', 'filenumber', 'title', 'representation'), 'conditions'=>array('CaseFile.id'=>$id), 'contain'=>array('CaseFileI18n','Kind.title', 'PatentOffice.name','User'=>array('id', 'username', 'role_id')))))) {
			$this->Flash->error(__('Invalid record'));
			$this->redirect(array('action' => 'index'));
		}
		if ($this->request->is(array('post', 'put'))) {
			$this->request->data['CaseFile']=$caseFile['CaseFile'];
			//var_dump($this->request->data);die;
							
			if ($this->CaseFile->save($this->request->data)) {	
				$this->Flash->success(__('Assignment succeeded'));
				$this->redirect(array('action' => 'view', '?' => array('tab' => 'assigned'), $id));
			} else {
				$this->Flash->error(__('Form contains errors'));
			}
		}
		if (empty($this->request->data)) {
			$this->request->data = $caseFile;
		}
                                          
		$users= $this->CaseFile->User->find('all', array('fields'=>array('id', 'username', 'role_id'), 'contain'=>array('Group.name'), 'conditions'=>array('NOT' => array('Group.alias' => array('superadmin')))));
		// Format the user records to be selected as array. [id] => [Group.name] => "User.username"
		
		$users = Set::combine($users, '{n}.User.id', array('{0}', '{n}.User.username'), '{n}.Group.name');
		$this->set(compact('caseFile', 'users', 'employees'));
	}
	
	/**
	 * Assign a related CaseFile to a CaseFile
	 * @return void
	 */
	public function assign_rel_case_file($id = null) {	
	if (empty($id) || !($caseFile = $this->CaseFile->find('first', array('fields' => array('id', 'filenumber'), 'conditions'=>array('CaseFile.id'=>$id), 'contain'=>array('CaseFileI18n', 'RelCaseFile.CaseFileI18n', 'RelCaseFile2.CaseFileI18n', 'RelCaseFile'=>array('id', 'filenumber'), 'RelCaseFile2'=>array('id', 'filenumber')))))) {
			$this->Flash->error(__('Invalid record'));
			$this->redirect(array('action' => 'index'));
		}		
		
		if ($this->request->is(array('post', 'put'))) {    
			$this->request->data['CaseFile']=$caseFile['CaseFile'];
			if ($this->CaseFile->save($this->request->data)) {	
				$this->Flash->error(__('Assignment succeeded'));
				$this->redirect(array('action' => 'view', '?' => array('tab' => 'assigned'), $id));
			}else{
				$this->Flash->error(__('Form contains errors'));
			}
		}
		if (empty($this->request->data)) {
			$this->request->data = $caseFile;
		}


		$relCaseFiles = $this->CaseFile->RelCaseFile->find('all', array('fields' => array('id', 'filenumber', 'title', 'representation'), 'contain'=>array('CaseFileI18n','Kind.title', 'PatentOffice.name'), 'order' =>array('RelCaseFile.created DESC'), 'conditions'=>array('NOT'=>array('RelCaseFile.id'=>$id))));		
		// If an Element of $relCaseFiles isnt assigned to a patentOffice, show 'No Patent Office' as Patent Office name 
		foreach ($relCaseFiles as $key=>$relCaseFile){
			if (empty($relCaseFile['PatentOffice']['name'])) {
				$relCaseFile['PatentOffice']['name'] = 'No Patent Office'; $relCaseFiles[$key] = $relCaseFile;
			}   
		}
		// Format the person records to be selected as array. [id] => [Personkind.title] => "Person.fullname [Persontype.title]"		
		$relCaseFiles = Set::combine($relCaseFiles, '{n}.RelCaseFile.id', array('{0} [{1}]', '{n}.RelCaseFile.identifier', '{n}.Kind.title'), '{n}.PatentOffice.name');                                              
		$this->set(compact('relCaseFiles', 'caseFile'));
	}


	
	public function lettermail($id = null) {
		if (empty($id) || !($caseFile = $this->CaseFile->find('first', array(/*'fields' => array('id', 'filenumber', 'title', 'representation'),*/ 'conditions'=>array('CaseFile.id'=>$id), 'contain'=>array('CaseFileI18n', 'CaseFilePriority'=>'Country.code', 'Kind', 'PatentOffice'=>array('Country', 'PatentOfficeLocation'=>array('Country')), 'CaseFilesPerson'=>array('Person'=>array('Gender', 'Personkind', 'Country'),'Persontype.title'), 'Status'))))) {
			$this->Flash->error(__('Invalid record'));
			$this->redirect(array('action' => 'index'));
		}
		
		$pTypeNames = array(0=>'Contact Person (first)', 1=>'Correspondence Attorney'); //FIXME add these values to config, maybe use alias ;; alternative: seletc filed in view
		
		$this->loadModel('Letter');
		$pTClass = ClassRegistry::init('Persontype');
		
		// set translations to Case File model
		if (!empty($caseFile['CaseFileI18n'])) {
			foreach($caseFile['CaseFileI18n'] as $translation) {
				$field = $translation['field'];
				$lang = $translation['locale'];
				$caseFile['CaseFile'][$field."_".$lang] = $translation['content'];
			}
		}
		
		// Find Contact Person by Persontype. Prefer those with higher key in array $pTypeNames (higher priority)
		$pTypeIds = array();
		$cP = null;
		$pTypeIds = $pTClass->find('list', array('fields'=>array('id'), 'conditions'=>array('title'=>$pTypeNames)));
		if (!empty($pTypeIds)) {
			$cP = Letter::createContactPerson($caseFile, $pTypeIds);
		}
		
		$selElements = array();
		$patentNameRes = Letter::createPatentName($caseFile);
		$patentOwnersRes = Letter::getOwners($caseFile);
		
		$ownerNames = "";
		if (!empty($patentOwnersRes['ownersName'])) {
			$first = true;
			foreach ($patentOwnersRes['ownersName'] as $owner){
				if ($first == false) {					
					$ownerNames .= "\n\t\t";
				}
				$ownerNames .= $owner['name'];
				$first = false;
			}
		}
		
		$selElements['title']['patent_name'] = "";
		if (!empty($patentNameRes['name'])) {
			$selElements['title']['patent_name'] = $patentNameRes['name'];			
		}
		$selElements['ow']['names'] = $ownerNames;
		$selElements['ow']['desc'] = "";
		if (!empty($patentOwnersRes['ownersLabel'])){
			$selElements['ow']['desc'] = $patentOwnersRes['ownersLabel'];
		}
		$selElements['cf']['title'] = '';
		if (!empty($caseFile['CaseFile']['title_eng']) || !empty($caseFile['CaseFile']['title_deu']) || !empty($caseFile['CaseFile']['title'])) {
			if (!empty($caseFile['CaseFile']['title_eng'])) {
				$selElements['cf']['title'] = $caseFile['CaseFile']['title_eng'];
				if (!empty($caseFile['CaseFile']['title_chi'])) {
					$selElements['cf']['title'] .= " (".$caseFile['CaseFile']['title_chi'].")";
				}
			}else if (!empty($caseFile['CaseFile']['title_deu'])) {
				$selElements['cf']['title'] = $caseFile['CaseFile']['title_deu'];
				if (!empty($caseFile['CaseFile']['title_chi'])) {
					$selElements['cf']['title'] .= " (".$caseFile['CaseFile']['title_chi'].")";
				}
			}else if (!empty($caseFile['CaseFile']['title'])) {
				$selElements['cf']['title'] = $caseFile['CaseFile']['title'];
			}
		}else if (!empty($caseFile['CaseFile']['representation_eng']) || !empty($caseFile['CaseFile']['representation_deu']) || !empty($caseFile['CaseFile']['representation'])) {
			if (!empty($caseFile['CaseFile']['representation_eng'])) {
				$selElements['cf']['title'] = $caseFile['CaseFile']['representation_eng'];
				if (!empty($caseFile['CaseFile']['representation_chi'])) {
					$selElements['cf']['title'] .= " (".$caseFile['CaseFile']['representation_chi'].")";
				}
			}else if (!empty($caseFile['CaseFile']['representation_deu'])) {
				$selElements['cf']['title'] = $caseFile['CaseFile']['representation_deu'];
				if (!empty($caseFile['CaseFile']['representation_chi'])) {
					$selElements['cf']['title'] .= " (".$caseFile['CaseFile']['representation_chi'].")";
				}			
			}else if (!empty($caseFile['CaseFile']['representation'])) {
				$selElements['cf']['title'] = $caseFile['CaseFile']['representation'];
			}
		}
		$selElements['cf']['filenumber'] = '';
		if (!empty($caseFile['CaseFile']['filenumber'])) {
			$selElements['cf']['filenumber'] = $caseFile['CaseFile']['filenumber'];
		}
		
		$selElements['cp']['your_ref'] = !empty($cP['contactPerson']['your_ref']) ? $cP['contactPerson']['your_ref'] : 'Please indicate';
		$selElements['cp']['name_email'] = !empty($cP['contactPerson']['name']) ? $cP['contactPerson']['name'] : '';
		$selElements['cp']['name_email'] .= !empty($selElements['cp']['name_email']) ? ', ' : '';
		$selElements['cp']['name_email'] .= !empty($cP['contactPerson']['email']) ? $cP['contactPerson']['email'] : '';
		
		$errors = array();
		if (!empty($patentNameRes['errors']) || !empty($patentOwnersRes['errors']) || !empty($cP['errors']) ) {				
			if (!empty($patentNameRes['errors'])){
				$errors[0]['head'] = 'Errors while creating 1. line in title section';
				$errors[0]['body'] = $patentNameRes['errors'];
			}
			if (!empty($patentOwnersRes['errors'])){
				$errors[1]['head'] = 'Errors while creating 3. line in title section:';
				$errors[1]['body'] = $patentOwnersRes['errors'];
			}
			if (!empty($cP['errors'])){
				$errors[1]['head'] = 'Errors while creating 4. line in title section and contact section:';
				$errors[2]['body'] = $cP['errors'];
			}
		}
		
		$titlesec = 
		$selElements['title']['patent_name']."\n".
		"Title:\t\t".$selElements['cf']['title']."\n".
		$selElements['ow']['desc'].":\t".$selElements['ow']['names']."\n".
		"Your Ref.:\t".$selElements['cp']['your_ref']."\t-\t".
		"Our Ref.:\t".$selElements['cf']['filenumber'];

		$rows = substr_count( $titlesec, "\n" ) + 2;
		$contact = $selElements['cp']['name_email'];
				
		return array('titlesec'=>$titlesec, 'rows'=>$rows, 'contact'=>$contact, 'errors'=>$errors);
	}
	
	public function test_title() {
		$caseFiles = $this->CaseFile->find('all', array('contain'=>array('CaseFilePriority'=>'Country.code', 'Kind', 'PatentOffice'=>array('PatentOfficeLocation'=>array('Country')), 'CaseFilesPerson'=>array('Person','Persontype.title'), 'Status')));
		$errors = array();
		if (!empty($caseFiles)) {
			$this->loadModel('Letter');
			foreach ($caseFiles as $caseFile) {
				$patentNameRes = Letter::createPatentName($caseFile);
				$patentOwnersRes = Letter::getOwners($caseFile);
				if (!empty($patentNameRes['errors'])) {
					$methErrors = $patentNameRes['errors'];
					$filenumber = $caseFile['CaseFile']['filenumber'];
					$errors[] = array('filenumber'=>$filenumber, 'errors'=>$methErrors, 'method'=>'Letter::createPatentName2');
				}
				if (!empty($patentOwnersRes['errors'])) {
					$methErrors = $patentOwnersRes['errors'];
					$filenumber = $caseFile['CaseFile']['filenumber'];
					$errors[] = array('filenumber'=>$filenumber, 'errors'=>$methErrors, 'method'=>'Letter::getOwners');
				}
			}
		}
		echo '<pre>'; var_dump($errors); echo '</pre>'; die;
	}
	
	public function save_kinds() {
		$this->CaseFile->Kind->create();
		$post = array('id'=>1,'title'=>'Patent');
		if ($this->CaseFile->Kind->save($post)) {
			return $this->redirect(array('action' => 'index'));
		}
		$this->CaseFile->Kind->locale = 'en_us';
		
	}
	
	public function translate() {
	ini_set('max_execution_time', 1200);
		$this->autoRender = false;
		$cfs = $this->CaseFile->find('all',array('fields'=>array('id', 'representation', 'title')));
		$db = ConnectionManager::getDataSource('default');
		foreach ($cfs as $cf) {
			$fields = $cf['CaseFile'];
			$id = $cf['CaseFile']['id'];			
			echo $id;
			  if (!empty($fields['title'])) {
				  $url = "http://ws.detectlanguage.com/0.2/detect";
				  $data = array("q"=>$fields['title'], "key"=>"dc54c13130088af7cda6286008b14caa");
				  $title = strtoupper($fields['title']);
				  $response = $this->rest_helper($url, $data);

				  if (!empty($response->data->detections[0]->language)) {
					  if ($response->data->detections[0]->language == 'en') {
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'title',?)",array($title));
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'representation','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'representation','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'representation','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'representation','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'representation','')");	
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'representation','')");							  
					  }else if ($response->data->detections[0]->language = 'de') {
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'representation','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'title',?)",array($title));
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'representation','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'representation','')");	
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'representation','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'representation','')");	
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'representation','')");								  
					  }else if ($response->data->detections[0]->language = 'es') {
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'representation','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'representation','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'representation','')");	
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'title',?)",array($title));
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'representation','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'representation','')");	
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'representation','')");								  
					  }else if ($response->data->detections[0]->language = 'fr') {
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'representation','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'representation','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'representation','')");	
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'representation','')");	;
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'title',?)",array($title));
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'representation','')");	
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'representation','')");								  
					  }else{
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'title',?)",array($title));
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'representation','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'representation','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'representation','')");	
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'representation','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'representation','')");	
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'representation','')");								  
					  }
				  }else{
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'title',?)",array($title));
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'representation','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'representation','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'representation','')");	
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'representation','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'representation','')");	
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'representation','')");							  
				  }
			  }else if (!empty($fields['representation'])) {
				  $url = "http://ws.detectlanguage.com/0.2/detect";
				  $data = array("q"=>$fields['representation'], "key"=>"dc54c13130088af7cda6286008b14caa");
				  $rep = strtoupper($fields['representation']);
				  $response = $this->rest_helper($url, $data);
				  if (!empty($response->data->detections[0]->language)) {
					  if ($response->data->detections[0]->language == 'en') {
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'representation',?)",array($rep));
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'representation','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'representation','')");	
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'representation','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'representation','')");	
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'representation','')");								  
					  }else if ($response->data->detections[0]->language = 'de') {
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'representation','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'representation',?)",array($rep));
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'representation','')");		
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'representation','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'representation','')");	
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'representation','')");			
					  }else if ($response->data->detections[0]->language = 'es') {
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'representation','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'representation','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'representation','')");		
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'representation',?)",array($rep));	;
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'representation','')");	
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'representation','')");		
					  }else if ($response->data->detections[0]->language = 'fr') {
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'representation','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'representation','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'representation','')");		
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'representation','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'representation',?)",array($rep));	
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'representation','')");								  
					  }else{
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'representation',?)",array($rep));
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'representation','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'representation','')");	
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'representation','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'representation','')");	
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'representation','')");								  
					  }
				  }else{
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'representation',?)",array($rep));
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'representation','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'representation','')");		
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'representation','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'representation','')");	
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'title','')");
						  $this->CaseFile->query("INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'representation','')");						  
				  }

			  }
		
		}
		 
		 
	}
	
	public function translate2echo() {
		ini_set('max_execution_time', 1200);
		$this->autoRender = false;
		$cfs = $this->CaseFile->find('all',array('fields'=>array('id', 'representation', 'title')));
		$db = ConnectionManager::getDataSource('default');
		$link = mysqli_connect('localhost', 'root', 'a7qeGED35nhYAhuiGKKpTBfxYv6MghnfslhoDjO8kkK9nqCug', 'proinew')
		OR die(mysql_error());
		foreach ($cfs as $cf) {
			$fields = $cf['CaseFile'];
			$id = $cf['CaseFile']['id'];

			if (!empty($fields['title'])) {
				$url = "http://ws.detectlanguage.com/0.2/detect";
				$data = array("q"=>$fields['title'], "key"=>"dc54c13130088af7cda6286008b14caa");
				$title = strtoupper(mysqli_real_escape_string($link,$fields['title']));
				$response = $this->rest_helper($url, $data);
	
				if (!empty($response->data->detections[0]->language)) {
					if ($response->data->detections[0]->language == 'en') {
						echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'title','$title'); <br/>";
						echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'representation',''); <br/>";
						echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'title',''); <br/>";
						echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'representation',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'representation',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'representation',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'representation',''); <br/>";	
						echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'representation',''); <br/>";							  
					}else if ($response->data->detections[0]->language = 'de') {
					echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'representation',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'title','$title'); <br/>";
					echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'representation',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'representation',''); <br/>";	
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'representation',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'representation',''); <br/>";	
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'representation',''); <br/>";								  
						  }else if ($response->data->detections[0]->language = 'es') {
					echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'representation',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'representation',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'representation',''); <br/>";	
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'title','$title'); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'representation',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'representation',''); <br/>";	
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'representation',''); <br/>";								  
						  }else if ($response->data->detections[0]->language = 'fr') {
					echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'title',''); <br/>";
					echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'representation',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'representation',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'representation',''); <br/>";	
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'representation',''); <br/>";	;
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'title','$title'); <br/>";
					echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'representation',''); <br/>";
					echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'representation',''); <br/>";								  
						  }else{
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'title','$title'); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'representation',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'representation',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'representation',''); <br/>";	
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'representation',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'representation',''); <br/>";	
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'representation',''); <br/>";								  
						  }
					  }else{
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'title','$title'); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'representation',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'representation',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'representation',''); <br/>";	
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'representation',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'representation',''); <br/>";	
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'representation',''); <br/>";							  
					  }
				  }else if (!empty($fields['representation'])) {
					$url = "http://ws.detectlanguage.com/0.2/detect";
					  $data = array("q"=>$fields['representation'], "key"=>"dc54c13130088af7cda6286008b14caa");
					  $rep = strtoupper(mysqli_real_escape_string($link,$fields['representation']));
						$response = $this->rest_helper($url, $data);
					if (!empty($response->data->detections[0]->language)) {
					if ($response->data->detections[0]->language == 'en') {
						echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'representation','$rep'); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'representation',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'representation',''); <br/>";	
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'representation',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'representation',''); <br/>";	
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'representation',''); <br/>";								  
						  }else if ($response->data->detections[0]->language = 'de') {
						echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'representation',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'representation','$rep'); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'representation',''); <br/>";		
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'representation',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'representation',''); <br/>";	
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'representation',''); <br/>";			
						  }else if ($response->data->detections[0]->language = 'es') {
						echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'representation',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'representation',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'representation',''); <br/>";		
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'representation','$rep'); <br/>";	;
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'representation',''); <br/>";	
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'representation',''); <br/>";		
						  }else if ($response->data->detections[0]->language = 'fr') {
						echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'representation',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'representation',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'representation',''); <br/>";		
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'representation',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'representation','$rep'); <br/>";	
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'representation',''); <br/>";								  
						  }else{
						echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'representation','$rep'); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'representation',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'representation',''); <br/>";	
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'representation',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'representation',''); <br/>";	
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'representation',''); <br/>";								  
						  }
					  }else{
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('eng', 'CaseFile',$id,'representation','$rep'); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('deu', 'CaseFile',$id,'representation',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('chi', 'CaseFile',$id,'representation',''); <br/>";		
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('spa', 'CaseFile',$id,'representation',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'representation',''); <br/>";	
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'title',''); <br/>";
							  echo "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('ita', 'CaseFile',$id,'representation',''); <br/>";						  
					  }
	
				  }
		}
							
							
	}	
	
	public function testapi(){
		$title_fr_unesc= "Procede D'Amplification Optimale Sur Une Reaction En Chaine Par Polymerase";
		$id = 1;
		$stmt = "INSERT INTO `case_file_i18ns`(`locale`, `model`, `foreign_key`, `field`, `content`) VALUES ('fra', 'CaseFile',$id,'title',?);";
		$this->CaseFile->query($stmt,array($title_fr_unesc));
	}
	
	
	function rest_helper($url, $params = null, $verb = 'POST', $format='json')
	{
		$cparams = array(
				'http' => array(
						'method' => $verb,
						'ignore_errors' => true
				)
		);
		if ($params !== null) {
			$params = http_build_query($params);
			if ($verb == 'POST') {
				$cparams['http']['content'] = $params;
			} else {
				$url .= '?' . $params;
			}
		}
	
		$context = stream_context_create($cparams);
		$fp = @fopen($url, 'rb', false, $context);
		if (!$fp) {
			$res = false;
		} else {
			// If you're trying to troubleshoot problems, try uncommenting the
			// next two lines; it will show you the HTTP response headers across
			// all the redirects:
			// $meta = stream_get_meta_data($fp);
			// var_dump($meta['wrapper_data']);
			$res = stream_get_contents($fp);
		}
	
		if ($res === false) {
			throw new Exception("$verb $url failed: $php_errormsg");
		}
	
		switch ($format) {
			case 'json':
				$r = json_decode($res);
				if ($r === null) {
					throw new Exception("failed to decode $res as json");
				}
				return $r;
	
			case 'xml':
				$r = simplexml_load_string($res);
				if ($r === null) {
					throw new Exception("failed to decode $res as xml");
				}
				return $r;
		}
		return $res;
	}

	public function correct() {
		$trans = $this->CaseFile->CaseFileI18n->find('all');		
		$is = array('ä', 'ö', 'ü',);
		$should = array('Ä','Ö','Ü');
		foreach($trans as $tran){
			if (!empty($tran['CaseFileI18n']['content'])) {
				var_dump($tran['CaseFileI18n']['content']);
				$tran['CaseFileI18n']['content'] = str_replace($is, $should, $tran['CaseFileI18n']['content']);
				$this->CaseFile->CaseFileI18n->save($tran);
			}
		}
		$trans2 = $this->CaseFile->CaseFileI18n->find('all');
		var_dump('--------------------------------------------');
		foreach($trans2 as $tran){
			if (!empty($tran['CaseFileI18n']['content'])) {
				var_dump($tran['CaseFileI18n']['content']);
			}
		}
		die;
	}
	


/****************************************************************************************
 * protected/interal functions
 ****************************************************************************************/
	private function search($array, $key, $value)
	{
		$results = array();
	
		if (is_array($array)) {
			if (isset($array[$key]) && $array[$key] == $value) {
				$results[] = $array;
			}
	
			foreach ($array as $subarray) {
				$results = array_merge($results, $this->search($subarray, $key, $value));
			}
		}
	
		return $results;
	}

/****************************************************************************************
 * deprecated/test functions
 ****************************************************************************************/

}
