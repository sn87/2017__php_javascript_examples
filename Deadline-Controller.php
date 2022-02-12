<?php
App::uses('AppController', 'Controller');
App::uses('Deadline', 'Model');

/**
 * Deadlines Controller
 *
 */
class DeadlinesController extends AppController {

	public $paginate = array();

	public function beforeFilter() {
		parent::beforeFilter();
	}
	public $components = array(
			'Search.Prg'
	);

	
	public function index() {
		// Initiate Seach Plugin
		$this->Prg->commonProcess();
		$this->Deadline->recursive = 0;
		$this->paginate['Deadline'] = array('contain'=>array('CaseFile'=>array('PatentOffice','CaseFileI18n'), 'DeadlineReason', 'DeadlineType'), 'conditions'=>array('Deadline.deleted'=>false));		
		
		// Filter by date
		$dl_from = "";
		$dl_to = "";
		if (!empty($this->request->query['dl_from']) || !empty($this->request->query['dl_to'])) {
			
			$data = Deadline::prepareDateFilter($this->request->query);
			$data1=$data['data1'];
			$data2=$data['data2'];
			$dl_from = $this->request->query['dl_from'];
			$dl_to = $this->request->query['dl_to'];
			$this->paginate['Deadline']['conditions'] = array("deadline BETWEEN '$data1' AND '$data2'", 'Deadline.deleted'=>false);
		}
		
		// Filter by responsible users of CaseFile
		$user_ids = array();
	
		$deadlineTypes = $this->Deadline->DeadlineType->find('list');
		$deadlineReasons = $this->Deadline->DeadlineReason->find('list');
		$users = $this->Deadline->CaseFile->User->find('list');
		
		// Paginate with Search Plugin Query
		$this->set('deadlines', $this->paginate($this->Deadline->parseCriteria($this->passedArgs)));
		$this->set('deadlineTypes', $deadlineTypes);
		$this->set('deadlineReasons', $deadlineReasons);
		$this->set('users', $users);
		$this->set('selectedUsers', $user_ids);
		$this->set('dl_from', $dl_from);
		$this->set('dl_to', $dl_to);

	}
	
	
	/**
	 * @return void
	 */
	public function view($id = null) {
		$this->Deadline->recursive = 0;
		if (empty($id) || !($deadline = $this->Deadline->find('first', array('contain'=>array('Parent'=>array('DeadlineType', 'DeadlineReason.name'),'Child'=>array('DeadlineType', 'DeadlineReason.name'),'CaseFile'=>array('CaseFileI18n', 'PatentOffice'), 'DeadlineReason', 'DeadlineType'),'conditions'=>array('Deadline.id'=>$id))))) {
			$this->Flash->error(__('invalidRecord'));
			$this->redirect(array('action' => 'index'));
		}		
		
		// Save original value from db before deadlines timezone gets converted to users timezone
		$timezone = null;
		$deadline['Deadline']['org_deadline'] = $deadline['Deadline']['deadline'];
				
		$deadlineEdits = $this->Deadline->DeadlineEdit->find('all', array('contain'=>array('User'=>array('id', 'username', 'role_id', 'email')), 'conditions'=>array('deadline_id'=>$deadline['Deadline']['id']), 'order'=>'date DESC'));
		$this->set(compact('deadline','deadlineReason','deadlineEdits'));
	}
	


	
	/**
	 * @return void
	 */
	public function add($id = null) {
		if (empty($id) || !($caseFile = $this->Deadline->CaseFile->get($id, array(), array('CaseFileI18n','PatentOffice')))) {
			throw new NotFoundException();
		}
		
		
		if ($this->request->is(array('post', 'put'))) {
			
			$manual_int_deadline = 0;
			if(isset($this->request->data['Deadline']['manual_int_deadline'])) $manual_int_deadline = $this->request->data['Deadline']['manual_int_deadline'];
			$intDl = null;
			$dl = $this->request->data;

			// calculate internal deadline if field deadline is not empty and DeadlineType is "Main Deadline" (id=1) 
			// and button for deactivating auto-calculation of internal deadline was not pressed
			if($dl['Deadline']['deadline'] != '' && $dl['Deadline']['deadline_type_id'] == 1 && $manual_int_deadline == 0) {
				// if deadline reason was given, get the ammount of months for internal deadline calculation from the deadline reason table
				if (!empty($dl['Deadline']['deadline_reason_id'])){
					$int_dl_time['months'] = $this->Deadline->DeadlineReason->field('int_dl_months', array('id' => $dl['Deadline']['deadline_reason_id']));
					$int_dl_time['days'] = $this->Deadline->DeadlineReason->field('int_dl_days', array('id' => $dl['Deadline']['deadline_reason_id']));
					$intDl['Deadline']['deadline'] = $this->Deadline->calcInternalDl($dl['Deadline']['deadline'], $int_dl_time);
				}else{
					$intDl['Deadline']['deadline'] = $this->Deadline->calcInternalDl($dl['Deadline']['deadline']);
				}
					
				if ($intDl['Deadline']['deadline'] == FALSE) {
					$this->Flash->error(__('Internal deadline could not be calculated. Please try again or set option "deactivate auto calculation of internal deadline" and add Internal deadline by "Add Deadline Child" function'));
					$this->redirect(array('action'=>'add', $id));									
				}				
				if($dl['Deadline']['deadline_reason_id'] != '')
					$intDl['Deadline']['deadline_reason_id'] = $dl['Deadline']['deadline_reason_id'];				
				$intDl['Deadline']['deadline_type_id'] = 2;
				$intDl['Deadline']['case_file_id'] = $id;
				$intDl['Deadline']['note'] = "";
			}
			
			// Set Case File Id
			$dl['Deadline']['case_file_id'] = $id;
			
			// If internal Deadline was calculated
			if ($intDl != null) {
	         $this->Deadline->begin(); // Start transaction 
	         $this->Deadline->create();
				// Save Deadline (and internal Deadline, if it was calculated
				if ($this->Deadline->save($dl)){							
					$dlId = $this->Deadline->id;
					$intDl['Deadline']['parent_id'] = $dlId;
					$this->Deadline->create();
					unset($dl);
					if ($this->Deadline->save($intDl)){		
						$intDlId = $this->Deadline->id;
						unset($intDl);				
						// Create DeadlineEdit-Objects (Edit-History) for Deadline and Internal Deadline
						$this->Deadline->DeadlineEdit->create();
						$deadlineEdit['DeadlineEdit']['deadline_id'] = $dlId;
						$deadlineEdit['DeadlineEdit']['type'] = 'created';
						$deadlineEdit['DeadlineEdit']['date'] = date('Y-m-d H:i:s');
						$deadlineEdit['DeadlineEdit']['user_id'] = $this->Auth->user('id');	
						$this->Deadline->DeadlineEdit->create();
						$intDeadlineEdit['DeadlineEdit']['deadline_id'] = $intDlId;
						$intDeadlineEdit['DeadlineEdit']['type'] = 'created';
						$intDeadlineEdit['DeadlineEdit']['date'] = date('Y-m-d H:i:s');
						$intDeadlineEdit['DeadlineEdit']['user_id'] = $this->Auth->user('id');
						$dlEdits = array($deadlineEdit, $intDeadlineEdit);
						// Save both DeadlineEdit-Objects
						if($this->Deadline->DeadlineEdit->saveAll($dlEdits)) {
							$this->Flash->success(__('Deadline added'));
							$this->Deadline->commit(); //Commit transaction
							$this->redirect(array('controller'=>'CaseFiles', 'action'=>'view', '?' => array('tab' => 'deadlines'), $id));							
						}else{ 
								$this->Flash->error(__('Error while saving. Please try again'));
						}
					}else{ 
							$this->Flash->error(__('Error while saving. Please try again'));
					}
				} else {
					$this->Flash->error(__('Form contains errors'));
					$this->Deadline->rollback(); //Rollback transaction
				}
			
			// If no internal deadline was calculated
			}else{
	         $this->Deadline->begin(); // Start transaction 
	         $this->Deadline->create();
				// Save Deadline (and internal Deadline, if it was calculated
				if ($this->Deadline->save($dl)){
					// Create DeadlineEdit-Object (Edit-History) for Deadline
					$this->Deadline->DeadlineEdit->create();
					$deadlineEdit['DeadlineEdit']['deadline_id'] = $this->Deadline->id;
					$deadlineEdit['DeadlineEdit']['type'] = 'created';
					$deadlineEdit['DeadlineEdit']['date'] = date('Y-m-d H:i:s');
					$deadlineEdit['DeadlineEdit']['user_id'] = $this->Auth->user('id');									
					// Save DeadlineEdit-Object
					if($this->Deadline->DeadlineEdit->save($deadlineEdit)) {
						$this->Flash->success(__('Deadline added'));
						$this->Deadline->commit(); //Commit transaction
						$this->redirect(array('controller'=>'CaseFiles', 'action'=>'view', '?' => array('tab' => 'deadlines'), $id));
					}else{ 
							$this->Flash->error(__('Error while saving. Please try again'));
					}
				} else {
					$this->Flash->error(__('Form contains errors'));
					$this->Deadline->rollback(); //Rollback transaction
				}
			}
		}		
		$deadlineReasons = $this->Deadline->DeadlineReason->find('list', array('order'=>array('name'=>'asc')));
		$deadlineTypes = $this->Deadline->DeadlineType->find('list');
		$this->set(compact('caseFile','deadlineReasons','deadlineTypes', 'relDeadlines'));
	}
	
	
	/**
	 * @return void
	 */
	public function add_child($id = null) {
		if (empty($id) || !($deadline = $this->Deadline->find('first', array('contain'=>array('CaseFile.id'=>'PatentOffice.timezone', 'DeadlineReason.name', 'DeadlineType.name', 'Child'=>'DeadlineType.alias'), 'conditions'=>array('Deadline.id'=>$id))))) {
			throw new NotFoundException();
		}
		
		// Check if Parent Deadline already has an internal deadline set
		$intDl = false;
		foreach ($deadline['Child'] as $child) {
			if ($child['DeadlineType']['alias'] == 'internal') $intDl = true;	
		}
		
		if ($this->request->is(array('post', 'put'))) {

			$dl = $this->request->data;
			$dl['Deadline']['parent_id'] = $deadline['Deadline']['id'];		
			$dl['Deadline']['case_file_id'] = $deadline['Deadline']['case_file_id'];		

         $this->Deadline->begin(); // Start transaction 
         $this->Deadline->create();
         
			// Save Deadline (and internal Deadline, if it was calculated
			if ($this->Deadline->save($dl)){
				// Create DeadlineEdit-Object (Edit-History) for Deadline
				$this->Deadline->DeadlineEdit->create();
				$deadlineEdit['DeadlineEdit']['deadline_id'] = $this->Deadline->id;
				$deadlineEdit['DeadlineEdit']['type'] = 'created';
				$deadlineEdit['DeadlineEdit']['date'] = date('Y-m-d H:i:s');
				$deadlineEdit['DeadlineEdit']['user_id'] = $this->Auth->user('id');
										
				// Save DeadlineEdit-Object
				if($this->Deadline->DeadlineEdit->save($deadlineEdit)) {
					$this->Flash->success(__('Deadline added'));
					$this->Deadline->commit(); //Commit transaction
					$this->redirect(array('action'=>'view', $id));
				}else{ 
						$this->Flash->error(__('Error while saving. Please try again'));
				}
			} else {
				$this->Flash->error(__('Form contains errors'));
				$this->Deadline->rollback(); //Rollback transaction
			}
			
		}		
		if (empty($this->request->data)) {
			$this->request->data['Deadline']['case_file_id'] = $deadline['Deadline']['case_file_id'];
		}
		$caseFiles = $this->Deadline->CaseFile->find('all', array('fields'=>array('id', 'title', 'representation', 'filenumber'), 'conditions'=>array('CaseFile.id'=>$deadline['Deadline']['case_file_id'])));		
		$caseFiles = Set::combine($caseFiles, '{n}.CaseFile.id', array('{0}', '{n}.CaseFile.identifier'));                                              
		$deadlineReasons = $this->Deadline->DeadlineReason->find('list', array('order'=>array('name'=>'asc')));
		$deadlineTypes = null;
		if ($intDl == false) {
			$deadlineTypes = $this->Deadline->DeadlineType->find('list', array('conditions'=> array("not" => array ( "DeadlineType.alias" => "main"))));
		}else if ($intDl == true){
			$deadlineTypes = $this->Deadline->DeadlineType->find('list', array('conditions'=> array("not" => array ( "DeadlineType.alias" => array("main", "internal")))));
		}
		$this->set(compact('caseFiles','deadlineReasons','deadlineTypes','deadline'));
	}
	
	
	/**
	 * @return void
	 */
	public function edit($id = null) {
		
		// If id is not empty and loading the requested deadline-object and associated data (see 'contain')  was successful		
		if (empty($id) || !($deadline = $this->Deadline->find('firstConvertDt', array('contain'=>array('Parent','Child'=>array('DeadlineType.alias', 'DeadlineReason.name'),'CaseFile.id'=>'PatentOffice.timezone', 'DeadlineReason', 'DeadlineType'), 'conditions'=>array('Deadline.id'=>$id))))) {
			$this->Flash->error(__('invalidRecord'));
			$this->redirect(array('action' => 'index'));
		}

		if ($this->request->is(array('post', 'put'))) {
			
			$manual_int_deadline = 0;
			if(isset($this->request->data['Deadline']['manual_int_deadline'])) $manual_int_deadline = $this->request->data['Deadline']['manual_int_deadline'];
			$dl = $this->request->data;
			$intDl = null;
			$msg = array();
			
			if (isset($deadline['Child'])) {

				foreach ($deadline['Child'] as $child) {
	 				if ($intDl == null && isset($child['DeadlineType']['alias']) && $child['DeadlineType']['alias'] == 'internal')
	 					$intDl['Deadline'] = $child;
	 			}
			}
			

			// If internal Deadline was calculated
			if ($intDl != null && $manual_int_deadline == 0) {
				
				// if deadline reason was given, get the ammount of months for internal deadline calculation from the deadline reason table
				if (!empty($dl['Deadline']['deadline_reason_id'])){
					$int_dl_time['months'] = $this->Deadline->DeadlineReason->field('int_dl_months', array('id' => $dl['Deadline']['deadline_reason_id']));
					$int_dl_time['days'] = $this->Deadline->DeadlineReason->field('int_dl_days', array('id' => $dl['Deadline']['deadline_reason_id']));
					$intDl['Deadline']['deadline'] = $this->Deadline->calcInternalDl($dl['Deadline']['deadline'], $int_dl_time);
				}else{
					$intDl['Deadline']['deadline'] = $this->Deadline->calcInternalDl($dl['Deadline']['deadline']);
				}
				
				if ($intDl['Deadline']['deadline'] == FALSE) {
					$this->Flash->error(__('Internal deadline could not be calculated. Please try again or edit this Main Deadline with option "deactivate auto calculation of internal deadline" set and then delete Internal deadline of this Main Deadline and add a new Internal deadline by "Add Deadline Child" function in Deadline/view'));
					$this->redirect(array('action'=>'edit', $id));									
				}				
			
				if($dl['Deadline']['deadline_reason_id'] != '' && $dl['Deadline']['deadline_reason_id'] != $intDl['Deadline']['deadline_reason_id'])
					$intDl['Deadline']['deadline_reason_id'] = $dl['Deadline']['deadline_reason_id'];				

				$intDl['Deadline']['modified'] = date('Y-m-d H:i:s');
				$deadlines = array();
				$deadlines = array($dl, $intDl);
				
				$this->Deadline->begin(); // Start transaction 	         
				// Save Deadline (and internal Deadline, if it was calculated
				if ($this->Deadline->saveAll($deadlines)){							
		
	
					// Create DeadlineEdit-Objects (Edit-History) for Deadline and Internal Deadline
					$this->Deadline->DeadlineEdit->create();
					$deadlineEdit['DeadlineEdit']['deadline_id'] = $id;
					$deadlineEdit['DeadlineEdit']['type'] = 'edited';
					$deadlineEdit['DeadlineEdit']['date'] = date('Y-m-d H:i:s');
					$deadlineEdit['DeadlineEdit']['user_id'] = $this->Auth->user('id');
					
					$this->Deadline->DeadlineEdit->create();
					$intDeadlineEdit['DeadlineEdit']['deadline_id'] = $intDl['Deadline']['id'];
					$intDeadlineEdit['DeadlineEdit']['type'] = 'edited';
					$intDeadlineEdit['DeadlineEdit']['date'] = date('Y-m-d H:i:s');
					$intDeadlineEdit['DeadlineEdit']['user_id'] = $this->Auth->user('id');
					$dlEdits = array($deadlineEdit, $intDeadlineEdit);

					// Save both DeadlineEdit-Objects
					if($this->Deadline->DeadlineEdit->saveAll($dlEdits)) {
						$this->Flash->success(__('Deadline edited'));
						$this->Deadline->commit(); //Commit transaction
						$this->redirect(array('action'=>'view', $id));
					}else{ 
							$this->Flash->error(__('Error while saving. Please try again'));
					}
				} else {
					$this->Flash->error(__('Form contains errors'));
					$this->Deadline->rollback(); //Rollback transaction
				}
			
			// If no internal deadline was calculated
			}else{
	         $this->Deadline->begin(); // Start transaction 
	         $this->Deadline->create();
	         
				// Save Deadline (and internal Deadline, if it was calculated
				if ($this->Deadline->save($dl)){
					// Create DeadlineEdit-Object (Edit-History) for Deadline
					$this->Deadline->DeadlineEdit->create();
					$deadlineEdit['DeadlineEdit']['deadline_id'] = $this->Deadline->id;
					$deadlineEdit['DeadlineEdit']['type'] = 'edited';
					$deadlineEdit['DeadlineEdit']['date'] = date('Y-m-d H:i:s');
					$deadlineEdit['DeadlineEdit']['user_id'] = $this->Auth->user('id');
											
					// Save DeadlineEdit-Object
					if($this->Deadline->DeadlineEdit->save($deadlineEdit)) {
						$this->Flash->success(__('Deadline edited'));
						$this->Deadline->commit(); //Commit transaction
						$this->redirect(array('action'=>'view', $id));
					}else{ 
							$this->Flash->error(__('Error while saving. Please try again'));
					}
				} else {
					$this->Flash->error(__('Form contains errors'));
					$this->Deadline->rollback(); //Rollback transaction
				}
			}
		}
		if (empty($this->request->data)) {
			$format = Configure::read('DateFormat');
			$deadline['Deadline']['deadline'] = date($format, strtotime($deadline['Deadline']['deadline']));
			$this->request->data = $deadline;
		}
		$caseFiles = $this->Deadline->CaseFile->find('all', array('fields'=>array('id', 'filenumber', 'title', 'representation'),'conditions'=>array('CaseFile.id'=>$deadline['Deadline']['case_file_id'])));		
		
		$caseFiles = Set::combine($caseFiles, '{n}.CaseFile.id', array('{0}', '{n}.CaseFile.identifier'));                                              
		
		//$relDeadlines = $this->Deadline->RelDeadline->find('list', array('conditions'=>array('NOT'=>array('RelDeadline.id'=>$id))));//'all', array('order' =>array('RelDeadline.created DESC'), 'conditions'=>array('NOT'=>array('RelDeadline.id'=>$id))));
		$relDeadlines = $this->Deadline->Child->find('all', array('contain'=>array('DeadlineType.name', 'DeadlineReason.name'), 'order' =>array('Child.created DESC'), 'conditions'=>array('NOT'=>array('Child.id'=>$id))));

		// Format the person records to be selected as array. [id] => [Personkind.title] => "Person.fullname [Persontype.title]"		
		$relDeadlines = Set::combine($relDeadlines, '{n}.Child.id', array('{0} [{1}]', '{n}.Child.deadline', '{n}.Child.name'), '{n}.Child.name');
		
		$deadlineReasons = $this->Deadline->DeadlineReason->find('list', array('order'=>array('name'=>'asc')));
		$deadlineTypes = $this->Deadline->DeadlineType->find('list');
		$this->set(compact('caseFiles', 'deadline', 'deadlineReasons', 'deadlineTypes'));
	}


	/**
	 * @return void
	 */
	public function delete($id = null) {
		if (!$this->request->is(array('post', 'put'))) {
			throw new MethodNotAllowedException();
		}
		if (empty($id) || !($deadline = $this->Deadline->find('first', array('contain'=>array('DeadlineType.alias', 'Child'=>'DeadlineType.alias'), 'conditions'=>array('Deadline.id'=>$id), 'fields'=>array('id','deadline_type_id', 'case_file_id' ))))) {
			$this->Flash->error(__('invalidRecord'));
			$this->redirect(array('action'=>'index'));
		}
		$ids = array($id);
		
		// find deadline children
		if($deadline['DeadlineType']['alias'] == 'main' && isset($deadline['Child']) && count($deadline['Child']) > 0 ) {
			foreach($deadline['Child'] as $child) {				
				//if ($child['DeadlineType']['alias'] == 'internal') 
				$ids[] = $child['id'];
			}
		}
		$caseFileId = $deadline['Deadline']['case_file_id'];
		
		$this->Deadline->begin();

		$errors = false;
		foreach ($ids as $id) {
			if (!$this->soft_delete($id)) $errors = true;
		}
		
		if ($errors == false) {
			$this->Deadline->commit();
			$this->Flash->success(__('Deadline marked as deleted'));
			$this->redirect(array('controller'=>'case_files', 'action' => 'view', $caseFileId));
		}else{
			$this->Deadline->rollback();
			$this->Flash->error(__('Error while markind Deadline as deleted. Please try agaian'));
			$this->redirect(array('action' => 'index'));
		}

	}
	
	
	
	public function soft_delete($id = null) {
		if (empty($id) || !($deadline = $this->Deadline->find('firstConvertDtNoTime', array('contain'=>array('DeadlineReason', 'DeadlineType.alias','Child'=>'DeadlineType.alias','CaseFile.id'=>'PatentOffice.timezone'), 'conditions'=>array('Deadline.id'=>$id), 'fields'=>array('id','deadline_type_id','deadline','deleted','case_file_id' ) )))) {
			return false;
		}

		// soft-delete
		if (!isset($deadline['Deadline']['deleted']) || !$deadline['Deadline']['deleted'] == true) {
			$deadline['Deadline']['deleted'] = true;
			if (isset($deadline['DeadlineReason']['id']) && isset($deadline['Deadline']['case_file_id'])) {
				$return = $this->Deadline->CaseFile->Letter->LetterInstance->deactivateRelated($deadline['Deadline']['id'], $deadline['Deadline']['case_file_id']);
			}
			if ($this->Deadline->save($deadline)){
				// Create DeadlineEdit-Object (Edit-History) for Deadline
				$this->Deadline->DeadlineEdit->create();
				$deadlineEdit['DeadlineEdit']['deadline_id'] = $this->Deadline->id;
				$deadlineEdit['DeadlineEdit']['type'] = 'deleted';
				$deadlineEdit['DeadlineEdit']['date'] = date('Y-m-d H:i:s');
				$deadlineEdit['DeadlineEdit']['user_id'] = $this->Auth->user('id');
				// Save DeadlineEdit-Object
				if(!$this->Deadline->DeadlineEdit->save($deadlineEdit)) {
					return false;
				}
			} else {
				return false;
			}
		}
  	return true;
	
	}
	


/****************************************************************************************
 * ADMIN functions
 ****************************************************************************************/


	/**
	 * @return void
	 */
	public function admin_index() {
		$this->Deadline->recursive = 0;
		$deadlines = $this->paginate();
		$this->set(compact('deadlines'));
	}

	/**
	 * @return void
	 */
	public function admin_view($id = null) {
		$this->Deadline->recursive = 0;
		if (empty($id) || !($deadline = $this->Deadline->find('first', array('conditions'=>array('Deadline.id'=>$id))))) {
			$this->Flash->error(__('invalidRecord'));
			$this->redirect(array('action' => 'index'));
		}
		$this->set(compact('deadline'));
	}

	/**
	 * @return void
	 */
	public function admin_add() {
		if ($this->request->is(array('post', 'put'))) {
			$this->Deadline->create();
			if ($this->Deadline->save($this->request->data)) {
				$var = $this->request->data['Deadline']['id'];
				$this->Flash->success(__('record add %s saved', h($var)));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->error(__('formContainsErrors'));
			}
		}

		$caseFiles = $this->Deadline->CaseFile->find('list');
		$this->set(compact('caseFiles'));
	}

	/**
	 * @return void
	 */
	public function admin_edit($id = null) {
		if (empty($id) || !($deadline = $this->Deadline->find('first', array('conditions'=>array('Deadline.id'=>$id))))) {
			$this->Flash->error(__('invalidRecord'));
			$this->redirect(array('action' => 'index'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->Deadline->save($this->request->data)) {
				$var = $this->request->data['Deadline']['id'];
				$this->Flash->success(__('record edit %s saved', h($var)));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->error(__('formContainsErrors'));
			}
		}
		if (empty($this->request->data)) {
			$this->request->data = $deadline;
		}
		$caseFiles = $this->Deadline->CaseFile->find('list');
		$this->set(compact('caseFiles'));
	}

	/**
	 * @return void
	 */
	public function admin_delete($id = null) {
		if (!$this->request->is(array('post', 'put'))) {
			throw new MethodNotAllowedException();
		}
		if (empty($id) || !($deadline = $this->Deadline->find('first', array('conditions'=>array('Deadline.id'=>$id), 'fields'=>array('id'))))) {
			$this->Flash->error(__('invalidRecord'));
			$this->redirect(array('action'=>'index'));
		}
		$var = $deadline['Deadline']['id'];

		if ($this->Deadline->delete($id)) {
			$this->Flash->success(__('record del %s done', h($var)));
			$this->redirect(array('action' => 'index'));
		}
		$this->Flash->error(__('record del %s not done exception', h($var)));
		$this->redirect(array('action' => 'index'));
	}





}
