<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Dominik Zogg 2011
 * @author     Dominik Zogg <http://www.dominik-zogg.ch>
 * @package    microcomments
 * @license    LGPLv3
 */


/**
 * Add content element
 */
$GLOBALS['TL_CTE']['includes']['microcomments'] = 'ContentMicroComments';


/**
 * Front end modules
 */
$GLOBALS['FE_MOD']['application']['microcomments'] = 'ModuleMicroComments';


/**
 * Helper functions
 */

/**
 * addMicroComments
 * @param object $objParent parent object a single event for example
 * @param string $strTable table of parent
 * @return string rendered microcomments template
 */
function addMicroComments($objParent, $strTable)
{
    $objMicroComments = new ItemMicroComments($objParent, $strTable);
    return($objMicroComments->generate());
}