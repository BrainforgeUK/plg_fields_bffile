<?php
/**
 * @package   Fields plugin for file
 * @version   0.0.1
 * @author    https://www.brainforge.co.uk
 * @copyright Copyright (C) 2022-2023 Jonathan Brain. All rights reserved.
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

		ob_start();
		?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var field = document.getElementById("<?php echo $this->id; ?>");
        field.form.setAttribute('enctype', "multipart/form-data");
    });
</script>
		<?php
		$input .= ob_get_clean();

		if (!empty($this->value))
		{
			$data = json_decode($this->value);
			$value = htmlspecialchars($this->value, ENT_QUOTES);

			$radioId = 'jform_com_fields_' . $this->fieldname;
			$radioName = 'jform[com_fields][' . $this->fieldname . ']';

			$link = PlgfieldsBffile::getFileLink($data, $this->fieldname);
			ob_start();
            ?>
<input type="hidden"
       name="jform[com_fields_bffile_raw][<?php echo $this->fieldname; ?>]"
       value="<?php echo $value;?>">

<div id="<?php echo $radio4id; ?>">
    <input type="radio"
           id="<?php echo $radioId;?>"
           name="<?php echo $radioName;?>"
           value="<?php echo $value;?>"
           checked="checked"/>
    <label for="<?php echo $radioId;?>"><?php echo $data->filename;?></label>
    &nbsp;&nbsp;&nbsp;&nbsp;
    <input type="radio"
           id="<?php echo $radioId;?>_X"
           name="<?php echo $radioName;?>"
           onclick="bffile_delete();"
           value=""/>
    <label for="<?php echo $radioId;?>_X"><span class="icon-cancel-circle" style="color:#aa0000;"> </span></label>
    <div>
        <?php echo $link; ?>
    </div>
</div>
            <?php
			$input .= ob_get_clean();

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