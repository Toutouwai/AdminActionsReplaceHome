<?php namespace ProcessWire;
class ReplaceHome extends ProcessAdminActions {

	protected $description;
	protected $notes;

	/**
	 * Construct
	 */
	public function __construct() {
		parent::__construct();
		$this->description = $this->_('Replaces the template and content of the home page with that of a selected page.');
		$this->notes = $this->_('This action is destructive! It deletes content/files/images from the existing home page. In addition to the automatic Admin Actions database backup you should create a backup of /site/assets/files/ before running this action, and consider also making a manual database backup for extra safety.');
	}

	/**
	 * Action options
	 */
	protected function defineOptions() {
		return array(
			array(
				'name' => 'sourcePage',
				'label' => 'Source page',
				'description' => 'The page whose template and content will replace the home page.',
				'type' => 'pageListSelect',
				'required' => true,
			),
		);
	}

	/**
	 * Execute action
	 */
	protected function executeAction($options) {
		$pages = $this->wire()->pages;
		$sourcePage = $pages->get((int) $options['sourcePage']);
		$sourceFieldgroup = $sourcePage->template->fieldgroup;
		$homePage = $pages->get(1);
		$homeFieldgroup = $homePage->template->fieldgroup;

		// Remove all fields from original home template
		foreach($homeFieldgroup as $field) {
			// Skip if global field
			if($field->hasFlag(\ProcessWire\Field::flagGlobal)) continue;
			$homeFieldgroup->remove($field);
		}
		$homeFieldgroup->save();

		// Add all fields from source page template
		foreach($sourceFieldgroup as $field) {
			$field = $sourceFieldgroup->getFieldContext($field);
			if(!$homeFieldgroup->hasField($field)) {
				$homeFieldgroup->add($field);
				$homeFieldgroup->save();
			}
			$this->wire()->fields->saveFieldgroupContext($field, $homeFieldgroup);
		}

		// Add page clone hook to deal with any Repeater items
		$this->wire()->pages->addHookAfter('clone', $this, 'afterPageClone');

		// Set field values from source page
		$pages->of(false);
		foreach($sourcePage->fields as $field) {
			// Files/Images field
			if($field->type instanceof \ProcessWire\FieldtypeFile) {
				foreach($sourcePage->$field as $file) {
					/** @var \ProcessWire\Pagefile $file */
					$homePage->$field->add($file);
				}
			}
			// Other field
			else {
				$homePage->$field = $sourcePage->$field;
			}
		}
		$homePage->save();

		// Update textarea URLs
		$this->updateTextareaFileUrls($homePage, $sourcePage);

		$this->successMessage = $this->_('The home page template and content have been replaced successfully.');
		return true;
	}

	/**
	 * After Pages::clone
	 * Update file URLs in all textareas on cloned page
	 *
	 * @param \ProcessWire\HookEvent $event
	 */
	protected function afterPageClone(\ProcessWire\HookEvent $event) {
		$originalPage = $event->arguments(0);
		$newPage = $event->return;
		$this->updateTextareaFileUrls($newPage, $originalPage);
	}

	/**
	 * Update file URLs in all textareas on a page
	 *
	 * @param string $value
	 * @param \ProcessWire\Page $newPage
	 * @param \ProcessWire\Page $originalPage
	 */
	protected function updateTextareaFileUrls($newPage, $originalPage) {
		$languages = $this->wire()->languages;
		$this->wire()->pages->of(false);
		foreach($newPage->fields as $field) {
			// Only for textarea fields
			if(!$field->type instanceof \ProcessWire\FieldtypeTextarea) continue;
			// Multilanguage textarea
			if($languages && $field->type instanceof \ProcessWire\FieldtypeTextareaLanguage) {
				foreach($languages as $language) {
					$value = $newPage->getLanguageValue($language, $field->name);
					$value = $this->updateFileUrls($value, $newPage, $originalPage);
					$newPage->setLanguageValue($language, $field->name, $value);
				}
			}
			// Normal textarea
			else {
				$newPage->$field = $this->updateFileUrls($newPage->$field, $newPage, $originalPage);
			}
		}
		$newPage->save();
	}

	/**
	 * Update file URLs in a string
	 *
	 * @param string $value
	 * @param \ProcessWire\Page $newPage
	 * @param \ProcessWire\Page $originalPage
	 * @return string
	 */
	protected function updateFileUrls($value, $newPage, $originalPage) {
		$base = $this->wire()->config->urls->files;
		return str_replace("{$base}{$originalPage}/", "{$base}{$newPage}/", $value);
	}

}
