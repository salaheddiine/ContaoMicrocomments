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
 * Class ItemMicroComments
 *
 * @copyright  Dominik Zogg 2011
 * @author     Dominik Zogg <http://www.dominik-zogg.ch>
 * @package    Controller
 */
class ItemMicroComments extends ContentElement
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'ce_microcomments';

    /**
     *
     */
    public function __construct($objParent, $strTable)
    {
        $objConfig = new stdClass();
        $objConfig->table = $strTable;
        $objConfig->parentid = $objParent->id;
        $objConfig->perPage = $objParent->com_micro_perPage;
        $objConfig->order = $objParent->com_micro_order;
        $objConfig->template = $objParent->com_micro_template;
        $objConfig->protected = $objParent->com_micro_protected;
        $objConfig->groups = $objParent->com_micro_groups;
        $this->Config = $objConfig;
    }

    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE')
        {
            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### MICROCOMMENTS ###';
            $objTemplate->title = $this->headline;

            return $objTemplate->parse();
        }
        return parent::generate();
    }


    /**
     * Generate module
     */
    protected function compile()
    {
        $this->import('MicroComments');
        $this->MicroComments->addMicroCommentsToTemplate($this->Template, $this->Config, $this->Config->table, $this->Config->parentid, $GLOBALS['TL_ADMIN_EMAIL']);
    }
}