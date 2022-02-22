<?php
/**
 * @package   Fields plugin for file
 * @version   0.0.1
 * @author    https://www.brainforge.co.uk
 * @copyright Copyright (C) 2022 Jonathan Brain. All rights reserved.
 * @license   GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

defined('_JEXEC') or die;

/**
 * Fields Text Plugin
 *
 * @since  3.7.0
 */
class PlgfieldsBffile extends \Joomla\Component\Fields\Administrator\Plugin\FieldsPlugin
{
	protected $app;
	public $params;

	/*
	 */
	public function onCustomFieldsPrepareDom($field, \DOMElement $parent, Form $form)
	{
		$fieldNode = parent::onCustomFieldsPrepareDom($field, $parent, $form);

		if (!$fieldNode)
		{
			return $fieldNode;
		}

		$fieldNode->setAttribute('type', 'bffile.file');
		$fieldNode->setAttribute('addfieldpath', __DIR__ . '/fields/file.php');
		$fieldNode->setAttribute('accept', $field->fieldparams->get('accept', '*'));

		return $fieldNode;
	}

	/*
	 */
	public function onContentPrepareForm(Form $form, $data)
	{
		parent::onContentPrepareForm($form, $data);
	}

	/*
	 */
	public function onUserAfterSave($userData, $isNew, $success, $msg)
	{
		// It is not possible to manipulate the user during save events
		// Check if data is valid or we are in a recursion
		if (!$userData['id'] || !$success)
		{
			return;
		}

		$user = Factory::getUser($userData['id']);

		$task = $this->app->input->getCmd('task');

		// Skip fields save when we activate a user, because we will lose the saved data
		if (in_array($task, array('activate', 'block', 'unblock')))
		{
			return;
		}

		// Trigger the events with a real user
		$this->onContentAfterSave('com_users.user', $user, false);
	}

	/*
	 */
	public function onContentAfterSave($context, $item, $isNew, $data = [])
	{
		// Check if data is an array and the item has an id
		if (empty($item->id))
		{
			return;
		}

		$filestoreBase = '/media/plg_fields_bffile/' . $context;

		$filestorePath = JPATH_SITE . $filestoreBase . '/';

		$fileNames = [];
		$tmpFiles = [];

		$jform = $this->app->input->get('jform', [], 'ARRAY');

		if (!empty($jform['com_fields_bffile_raw']))
		{
			// If necessary delete a previously uploaded file, if any
			foreach($jform['com_fields_bffile_raw'] as $name=>$value)
			{
				if (!empty($jform['com_fields']) && !empty($jform['com_fields'][$name]))
				{
					continue;
				}

				$value = json_decode($value);
				$file = $filestorePath . $value->storedname;
				if (is_file($file))
				{
					unlink($file);
				}
				$fileNames[$name] = null;
			}
		}

		$files = $this->app->input->files->get('jform', [], 'ARRAY');
		if (empty($files) || empty($files['com_fields_bffile'])) return;
		$files = $files['com_fields_bffile'];

		foreach($files as $name=>$data)
		{
			switch ($data['error'])
			{
				case UPLOAD_ERR_OK:
					$fileNames[$name] = $data['name'];
					$tmpFiles[$name] = $data['tmp_name'];
					break;
				case UPLOAD_ERR_NO_FILE:
					break;
				default:
					$this->app->enqueueMessage(Text::sprintf('PLG_FIELDS_BFFILE_FILE_ERROR',
						$name,
						$data['error'],
						Text::_('PLG_FIELDS_BFFILE_FILE_ERROR_' . $data['error'])),
						'error');
					break;
			}
		}

		if (empty($fileNames))
		{
			return;
		}

		foreach($fileNames as $name=>$value)
		{
			$jform['com_fields'][$name] = $value;
		}
		$this->app->input->set('jform', $jform);

		// Create correct context for category
		if ($context === 'com_categories.category')
		{
			$context = $item->extension . '.categories';

			// Set the catid on the category to get only the fields which belong to this category
			$item->catid = $item->id;
		}

		// Check the context
		$parts = FieldsHelper::extract($context, $item);

		if (!$parts)
		{
			return;
		}

		// Compile the right context for the fields
		$context = $parts[0] . '.' . $parts[1];

		// Loading the fields
		$fields = FieldsHelper::getFields($context, $item);

		if (!$fields)
		{
			return;
		}

		if (!is_dir($filestorePath))
		{
			if (!mkdir($filestorePath, 0777, true))
			{
				$this->app->enqueueMessage(Text::sprintf('PLG_FIELDS_BFFILE_CANNOTCREATE_FOLDER', $filestoreBase), 'error');
				return;
			}
		}

		// Loading the model

		/** @var \Joomla\Component\Fields\Administrator\Model\FieldModel $model */
		$model = Factory::getApplication()->bootComponent('com_fields')->getMVCFactory()
			->createModel('Field', 'Administrator', ['ignore_request' => true]);

		// Loop over the fields
		foreach ($fields as $field)
		{
			// Field not available on form, keep stored value
			if (!array_key_exists($field->name, $fileNames))
			{
				continue;
			}

			$filename = $fileNames[$field->name];

			// If no value set (empty) remove value from database
			if (strlen($filename))
			{
				$value = new stdClass();
				$value->filename = $filename;
				$value->context  = basename($filestorePath);
				$value->storedname = $field->name . '.' . $item->id . '.' . pathinfo($value->filename, PATHINFO_EXTENSION);

				$storedFilePath = $filestorePath . $value->storedname;

				if (!rename($tmpFiles[$field->name], $storedFilePath))
				{
					$this->app->enqueueMessage(Text::sprintf('PLG_FIELDS_BFFILE_CANNOTRENAME',
						$tmpFiles[$field->name], $filestoreBase . '/' . $value->storedname), 'error');
					return;
				}
				chmod($storedFilePath, 0644);

				$value = json_encode($value);
			}
			else
			{
				$value = null;
			}

			// Setting the value for the field and the item
			$model->setFieldValue($field->id, $item->id, $value);
		}
	}

	/*
	 */
	public function onContentAfterDelete($context, $item): void
	{
		$parts = FieldsHelper::extract($context, $item);

		if (!$parts || empty($item->id))
		{
			return;
		}

		$context = $parts[0] . '.' . $parts[1];

		/** @var \Joomla\Component\Fields\Administrator\Model\FieldModel $model */
		$model = Factory::getApplication()->bootComponent('com_fields')->getMVCFactory()
			->createModel('Field', 'Administrator', ['ignore_request' => true]);
		$model->cleanupValues($context, $item->id);
	}

	/*
	 */
	public function onUserAfterDelete($user, $success, $msg): void
	{
		$item     = new stdClass;
		$item->id = $user['id'];

		$this->onContentAfterDelete('com_users.user', $item);
	}
}
