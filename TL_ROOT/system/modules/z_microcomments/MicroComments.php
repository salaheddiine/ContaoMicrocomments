<?php if(!defined('TL_ROOT')) die('You cannot access this file directly!');

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
 * Class MicroComments
 *
 * @copyright  Dominik Zogg 2011
 * @author     Dominik Zogg <http://www.dominik-zogg.ch>
 * @package    Controller
 */
class MicroComments extends Frontend
{

    /**
     * Add microcomments to a template
     * @param object
     * @param object
     * @param string
     * @param integer
     * @param array
     */
    public function addMicroCommentsToTemplate($objTemplate, $objConfig, $strSource, $intParent, $arrNotifies)
    {
        global $objPage;
        $this->import('String');

        $limit = null;
        $arrMicrocomments = array();

        // Pagination
        if($objConfig->perPage > 0)
        {
            $page = $this->Input->get('page') ? $this->Input->get('page') : 1;
            $limit = $objConfig->perPage;
            $offset = ($page - 1) * $objConfig->perPage;

            // Get the total number of microcomments
            $objTotal = $this->Database->prepare("SELECT COUNT(*) AS count FROM tl_comments WHERE source=? AND parent=?" . (!BE_USER_LOGGED_IN ? " AND published=1" : ""))
                                       ->execute($strSource, $intParent);

            // Initialize the pagination menu
            $objPagination = new Pagination($objTotal->count, $objConfig->perPage);
            $objTemplate->pagination = $objPagination->generate("\n  ");
        }

        // Get all published microcomments
        $objMicrocommentsStmt = $this->Database->prepare("
            SELECT
                c.*,
                CONCAT_WS(' ', m.firstname, m.lastname) AS name,
                m.email AS email,
                m.website AS website,
                m.avatar AS avatar
            FROM
                tl_comments AS c
            LEFT JOIN
                tl_member AS m ON (c.member != 0 AND c.member = m.id)
            WHERE
                c.source=? AND
                c.parent=?
                " . (!BE_USER_LOGGED_IN ? " AND c.published=1" : "") . "
            ORDER BY
                c.date " . (($objConfig->order == 'descending') ? " DESC" : "") ."
        ");

        if($limit)
        {
            $objMicrocommentsStmt->limit($limit, $offset);
        }

        $objMicrocomments = $objMicrocommentsStmt->execute($strSource, $intParent);
        $total = $objMicrocomments->numRows;

        if($total > 0)
        {
            $count = 0;

            if($objConfig->template == '')
            {
                $objConfig->template = 'com_micro_default';
            }

            $objPartial = new FrontendTemplate($objConfig->template);

            while ($objMicrocomments->next())
            {
                $objPartial->setData($objMicrocomments->row());

                // Clean the RTE output
                if($objPage->outputFormat == 'xhtml')
                {
                    $objMicrocomments->comment = $this->String->toXhtml($objMicrocomments->comment);
                }
                else
                {
                    $objMicrocomments->comment = $this->String->toHtml5($objMicrocomments->comment);
                }

                $objPartial->microcomment = trim(str_replace(array('{{', '}}'), array('&#123;&#123;', '&#125;&#125;'), $objMicrocomments->comment));

                $objPartial->datim = $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $objMicrocomments->date);
                $objPartial->date = $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $objMicrocomments->date);
                $objPartial->class = (($count < 1) ? ' first' : '') . (($count >= ($total - 1)) ? ' last' : '') . (($count % 2 == 0) ? ' even' : ' odd');
                $objPartial->by = $GLOBALS['TL_LANG']['MSC']['comment_by'];
                $objPartial->id = 'c' . $objMicrocomments->id;
                $objPartial->timestamp = $objMicrocomments->date;
                $objPartial->datetime = date('Y-m-d\TH:i:sP', $objMicrocomments->date);

                $arrMicrocomments[] = $objPartial->parse();
                ++$count;
            }
        }

        $objTemplate->comments = $arrMicrocomments;
        $objTemplate->commentsTotal = $limit ? $objTotal->count : $total;

        // Get the front end user object
        $this->import('FrontendUser', 'Member');

        // Access control
        if(!FE_USER_LOGGED_IN)
        {
            $objTemplate->isloggedIn = false;
            return;
        }
        $objTemplate->isloggedIn = true;

        // Microcomment field
        $arrFields['comment'] = array
        (
            'name' => 'comment',
            'label' => $GLOBALS['TL_LANG']['MSC']['com_comment'],
            'inputType' => 'text',
            'eval' => array('mandatory'=>true, 'preserveTags'=>true)
        );

        $doNotSubmit = false;
        $arrWidgets = array();
        $strFormId = 'com_micro_'. $strSource .'_'. $intParent;

        // Initialize widgets
        foreach ($arrFields as $arrField)
        {
            $strClass = $GLOBALS['TL_FFL'][$arrField['inputType']];

            // Continue if the class is not defined
            if(!$this->classFileExists($strClass))
            {
                continue;
            }

            $arrField['eval']['required'] = $arrField['eval']['mandatory'];
            $objWidget = new $strClass($this->prepareForWidget($arrField, $arrField['name'], $arrField['value']));

            // Validate the widget
            if($this->Input->post('FORM_SUBMIT') == $strFormId)
            {
                $objWidget->validate();

                if($objWidget->hasErrors())
                {
                    $doNotSubmit = true;
                }
            }

            $arrWidgets[$arrField['name']] = $objWidget;
        }

        $objTemplate->fields = $arrWidgets;
        $objTemplate->action = ampersand($this->Environment->request);
        $objTemplate->messages = $this->getMessages();
        $objTemplate->formId = $strFormId;
        $objTemplate->hasError = $doNotSubmit;

        // Confirmation message
        if($_SESSION['TL_MICROCOMMENT_ADDED'])
        {
            global $objPage;

            // Do not index the page
            $objPage->noSearch = 1;
            $objPage->cache = 0;

            $objTemplate->confirm = $GLOBALS['TL_LANG']['MSC']['com_confirm'];
            $_SESSION['TL_MICROCOMMENT_ADDED'] = false;
        }

        // Add the microcomment
        if($this->Input->post('FORM_SUBMIT') == $strFormId && !$doNotSubmit)
        {
            $this->import('String');

            // Do not parse any tags in the microcomment
            $strMicrocomment = htmlspecialchars(trim($arrWidgets['comment']->value));
            $strMicrocomment = str_replace(array('&amp;', '&lt;', '&gt;'), array('[&]', '[lt]', '[gt]'), $strMicrocomment);

            // Remove multiple line feeds
            $strMicrocomment = preg_replace('@\n\n+@', "\n\n", $strMicrocomment);

            // Prevent cross-site request forgeries
            $strMicrocomment = preg_replace('/(href|src|on[a-z]+)="[^"]*(contao\/main\.php|typolight\/main\.php|javascript|vbscri?pt|script|alert|document|cookie|window)[^"]*"+/i', '$1="#"', $strMicrocomment);

            $time = time();

            // Prepare the record
            $arrSet = array
            (
                'tstamp' => $time,
                'source' => $strSource,
                'parent' => $intParent,
                'name' => $this->Member->firstname . ' ' . $this->Member->lastname,
                'email' => $this->Member->email,
                'website' => $this->Member->website,
                'comment' => $this->convertLineFeeds($strMicrocomment),
                'ip' => $this->Environment->ip,
                'date' => $time,
                'published' => ($objConfig->moderate ? '' : 1),
                'member' => $this->Member->id,
            );

            $insertId = $this->Database->prepare("INSERT INTO tl_comments %s")->set($arrSet)->execute()->insertId;

            // HOOK: add custom logic
            if(isset($GLOBALS['TL_HOOKS']['addMicrocomment']) && is_array($GLOBALS['TL_HOOKS']['addMicrocomment']))
            {
                foreach ($GLOBALS['TL_HOOKS']['addMicrocomment'] as $callback)
                {
                    $this->import($callback[0]);
                    $this->$callback[0]->$callback[1]($insertId, $arrSet);
                }
            }

            // Notification
            $objEmail = new Email();

            $objEmail->from = $GLOBALS['TL_ADMIN_EMAIL'];
            $objEmail->fromName = $GLOBALS['TL_ADMIN_NAME'];
            $objEmail->subject = sprintf($GLOBALS['TL_LANG']['MSC']['microcom_subject'], $this->Environment->host);

            // Convert the microcomment to plain text
            $strMicrocomment = strip_tags($strMicrocomment);
            $strMicrocomment = $this->String->decodeEntities($strMicrocomment);
            $strMicrocomment = str_replace(array('[&]', '[lt]', '[gt]'), array('&', '<', '>'), $strMicrocomment);

            // Add microcomment details
            $objEmail->text = sprintf($GLOBALS['TL_LANG']['MSC']['com_message'],
                                      $this->Member->firstname . ' ' . $this->Member->lastname . ' (' . $this->Member->email . ')',
                                      $strMicrocomment,
                                      $this->Environment->base . $this->Environment->request,
                                      $this->Environment->base . 'contao/main.php?do=comments&act=edit&id=' . $insertId);

            // Do not send notifications twice
            if(is_array($arrNotifies))
            {
                $arrNotifies = array_unique($arrNotifies);
            }

            $objEmail->sendTo($arrNotifies);

            // Pending for approval
            if($objConfig->moderate)
            {
                $_SESSION['TL_MICROCOMMENT_ADDED'] = true;
            }

            $this->reload();
        }
    }

    /**
     * Convert line feeds to <br /> tags
     * @param string
     * @return string
     */
    public function convertLineFeeds($strMicrocomment)
    {
        global $objPage;
        $strMicrocomment = nl2br_pre($strMicrocomment, ($objPage->outputFormat == 'xhtml'));

        // Use paragraphs to generate new lines
        if(strncmp('<p>', $strMicrocomment, 3) !== 0)
        {
            $strMicrocomment = '<p>'. $strMicrocomment .'</p>';
        }

        $arrReplace = array
        (
            '@<br>\s?<br>\s?@' => "</p>\n<p>", // Convert two linebreaks into a new paragraph
            '@\s?<br></p>@'    => '</p>',      // Remove BR tags before closing P tags
            '@<p><div@'        => '<div',      // Do not nest DIVs inside paragraphs
            '@</div></p>@'     => '</div>'     // Do not nest DIVs inside paragraphs
        );

        return preg_replace(array_keys($arrReplace), array_values($arrReplace), $strMicrocomment);
    }
}