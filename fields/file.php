<?php
/**
 * @package   Fields plugin for file
 * @version   0.0.1
 * @author    https://www.brainforge.co.uk
 * @copyright Copyright (C) 2022 Jonathan Brain. All rights reserved.
 * @license   GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Joomla\CMS\Form\Field\FileField;
use Joomla\CMS\Uri\Uri;

defined('JPATH_PLATFORM') or die;

class BffileFormFieldFile extends FileField {
	protected $type = 'file';

	/*
	 */
	protected function getInput() {
		$radio4id = 'radio4_com_fields_' . $this->fieldname;
		$input4id = 'input4_com_fields_' . $this->fieldname;

		$input = '';
		$input .= '<style>#' . $this->id . ' { display: inline; }</style>';

		if (!empty($this->value))
		{
			$data = json_decode($this->value);
			$value = htmlspecialchars($this->value, ENT_QUOTES);

			$input .= '<input type="hidden"
							  name="jform[com_fields_bffile_raw][' . $this->fieldname . ']"
							  value="' . $value . '">';

			$radioId = 'jform_com_fields_' . $this->fieldname;
			$radioName = 'jform[com_fields][' . $this->fieldname . ']';
			$input .= '
<div id="' . $radio4id . '">
  <input type="radio"
  		 id="' . $radioId . '"
  		 name="' . $radioName . '"
  		 value="' . $value . '"
         checked="checked"/>
  <label for="' . $radioId . '">' . $data->filename . '</label>
  &nbsp;&nbsp;&nbsp;&nbsp;
  <input type="radio"
  		 id="' . $radioId . '_X"
  		 name="' . $radioName . '"
  		 onclick="bffile_delete();"
  		 value=""/>
  <label for="' . $radioId . '_X"><span class="icon-cancel-circle" style="color:#aa0000;"> </span></label>
  <div>
  <img id="bffile_image_' . $this->fieldname . '" class="bffile_image_' . $this->fieldname . '"
       src="' . Uri::root() . '/media/plg_fields_bffile/' . $data->context . '/' . $data->storedname . '"/>
  </div>
</div>';

			$input .= '<style>
#' . $input4id . ' { display: none; }
.bffile_image_' . $this->fieldname . ' { max-width:5rem; }
</style>';

			$input .= '<script>
function bffile_delete() {
    document.getElementById("' . $input4id . '").style.display = "block";
    document.getElementById("' . $radio4id . '").style.display = "none";
}
</script>';
		}

		$input .= '<div id="' . $input4id . '">' .
			parent::getInput() . '</div>';

		return $input;
	}

	/*
	 */
	public function renderField($options = array()) {
		$this->id = 'jform_com_fields_bffile_' . $this->fieldname;
		$this->name = 'jform[com_fields_bffile][' . $this->fieldname . ']';

		return parent::renderField($options);
	}
}