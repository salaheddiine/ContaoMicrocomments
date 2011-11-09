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
 * Add palettes to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['microcomments'] = '{title_legend},name,headline,type;{microcomment_legend},com_micro_order,com_micro_perPage;{template_legend:hide},com_micro_template;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

/**
 * Add fields to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['com_micro_order'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['com_micro_order'],
    'default'                 => 'descending',
    'exclude'                 => true,
    'inputType'               => 'select',
    'options'                 => array('ascending', 'descending'),
    'reference'               => &$GLOBALS['TL_LANG']['MSC'],
    'eval'                    => array('tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['com_micro_perPage'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['com_micro_perPage'],
    'default'                 => 0,
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array('rgxp'=>'digit', 'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['com_micro_template'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['com_micro_template'],
    'default'                 => 'com_micro_default',
    'exclude'                 => true,
    'inputType'               => 'select',
    'options_callback'        => array('tl_module_microcomments', 'getCommentsTemplates')
);

/**
 * Class tl_module_microcomments
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Dominik Zogg 2011
 * @author     Dominik Zogg <http://www.dominik-zogg.ch>
 * @package    Controller
 */
class tl_module_microcomments extends Backend
{

    /**
     * Return all comments templates as array
     * @param object
     * @return array
     */
    public function getCommentsTemplates(DataContainer $dc)
    {
        $intPid = $dc->activeRecord->pid;

        if ($this->Input->get('act') == 'overrideAll')
        {
            $intPid = $this->Input->get('id');
        }

        // Get the page ID
        $objArticle = $this->Database->prepare("SELECT pid FROM tl_module WHERE id=?")
                                     ->limit(1)
                                     ->execute($intPid);

        // Inherit the page settings
        $objPage = $this->getPageDetails($objArticle->pid);

        // Get the theme ID
        $objLayout = $this->Database->prepare("SELECT pid FROM tl_layout WHERE id=? OR fallback=1 ORDER BY fallback")
                                    ->limit(1)
                                    ->execute($objPage->layout);

        // Return all gallery templates
        return $this->getTemplateGroup('com_micro_', $objLayout->pid);
    }
}