<?php

/**
 * This is the edit-action, it will display a form to edit a campaign
 *
 * @package		backend
 * @subpackage	mailmotor
 *
 * @author		Dave Lens <dave@netlash.com>
 * @since		2.0
 */
class BackendMailmotorEditCampaign extends BackendBaseActionEdit
{
	/**
	 * Execute the action
	 *
	 * @return	void
	 */
	public function execute()
	{
		// get parameters
		$this->id = $this->getParameter('id', 'int');

		// does the item exist
		if(BackendMailmotorModel::existsCampaign($this->id))
		{
			// call parent, this will probably add some general CSS/JS or other required files
			parent::execute();

			// get all data for the item we want to edit
			$this->getData();

			// load the form
			$this->loadForm();

			// validate the form
			$this->validateForm();

			// parse
			$this->parse();

			// display the page
			$this->display();
		}

		// no item found, throw an exceptions, because somebody is fucking with our URL
		else $this->redirect(BackendModel::createURLForAction('index') . '&error=non-existing');
	}


	/**
	 * Get the data
	 *
	 * @return	void
	 */
	private function getData()
	{
		// get the record
		$this->record = (array) BackendMailmotorModel::getCampaign($this->id);

		// no item found, throw an exceptions, because somebody is fucking with our URL
		if(empty($this->record)) $this->redirect(BackendModel::createURLForAction('campaigns') . '&error=non-existing');
	}


	/**
	 * Load the form
	 *
	 * @return	void
	 */
	private function loadForm()
	{
		// create form
		$this->frm = new BackendForm('edit');

		// create elements
		$this->frm->addText('name', $this->record['name']);
	}


	/**
	 * Parse the form
	 *
	 * @return	void
	 */
	protected function parse()
	{
		// call parent
		parent::parse();

		// assign the active record and additional variables
		$this->tpl->assign('campaign', $this->record);
	}


	/**
	 * Validate the form
	 *
	 * @return	void
	 */
	private function validateForm()
	{
		// is the form submitted?
		if($this->frm->isSubmitted())
		{
			// cleanup the submitted fields, ignore fields that were added by hackers
			$this->frm->cleanupFields();

			// shorten fields
			$txtName = $this->frm->getField('name');

			// validate fields
			if($txtName->isFilled(BL::err('NameIsRequired')))
			{
				if($txtName->getValue() != $this->record['name'] && BackendMailmotorModel::existsCampaignByName($txtName->getValue())) $txtName->addError(BL::err('CampaignExists'));
			}

			// no errors?
			if($this->frm->isCorrect())
			{
				// build item
				$item['id'] = $this->id;
				$item['name'] = $txtName->getValue();
				$item['created_on'] = BackendModel::getUTCDate('Y-m-d H:i:s');

				// update the item
				BackendMailmotorModel::updateCampaign($item);

				// trigger event
				BackendModel::triggerEvent($this->getModule(), 'after_edit_campaign', array('item' => $item));

				// everything is saved, so redirect to the overview
				$this->redirect(BackendModel::createURLForAction('campaigns') . '&report=edited&var=' . urlencode($item['name']) . '&highlight=id-' . $item['id']);
			}
		}
	}
}

?>