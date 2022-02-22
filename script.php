<?php
/**
 * @package   Fields plugin for file
 * @version   0.0.1
 * @author    https://www.brainforge.co.uk
 * @copyright Copyright (C) 2022 Jonathan Brain. All rights reserved.
 * @license   GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

// no direct access
defined('_JEXEC') or die('Restricted Access');

class plgfieldsBffileInstallerScript {

	function install($parent) {
	}

	function uninstall($parent) {
		$mediaDir = JPATH_SITE . '/media/plg_fields_bffile';

		if (is_dir($mediaDir))
		{
			\Joomla\CMS\Filesystem\Folder::delete($mediaDir);
		}
	}

	function update($parent) {
	}

	function preflight($type, $parent) {
	}

	function postflight($type, $parent) {
	}
}
?>
