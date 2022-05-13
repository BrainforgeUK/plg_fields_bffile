<?php
/**
 * @package   Fields plugin for file
 * @version   0.0.1
 * @author    https://www.brainforge.co.uk
 * @copyright Copyright (C) 2022 Jonathan Brain. All rights reserved.
 * @license   GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

/** @var object $field */
$value = $field->value;

if ($value == '')
{
	return;
}

$data = json_decode($value);
$link = PlgfieldsBffile::getFileLink($data, $field->name);

echo $link;
