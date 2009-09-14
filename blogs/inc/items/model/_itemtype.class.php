<?php
/**
 * This file implements the Item Type class.
 *
 * This file is part of the evoCore framework - {@link http://evocore.net/}
 * See also {@link http://sourceforge.net/projects/evocms/}.
 *
 * @copyright (c)2003-2009 by Francois PLANQUE - {@link http://fplanque.net/}
 * Parts of this file are copyright (c)2005-2006 by PROGIDISTRI - {@link http://progidistri.com/}.
 *
 * {@internal License choice
 * - If you have received this file as part of a package, please find the license.txt file in
 *   the same folder or the closest folder above for complete license terms.
 * - If you have received this file individually (e-g: from http://evocms.cvs.sourceforge.net/)
 *   then you must choose one of the following licenses before using the file:
 *   - GNU General Public License 2 (GPL) - http://www.opensource.org/licenses/gpl-license.php
 *   - Mozilla Public License 1.1 (MPL) - http://www.opensource.org/licenses/mozilla1.1.php
 * }}
 *
 * {@internal Open Source relicensing agreement:
 * PROGIDISTRI S.A.S. grants Francois PLANQUE the right to license
 * PROGIDISTRI S.A.S.'s contributions to this file and the b2evolution project
 * under any OSI approved OSS license (http://www.opensource.org/licenses/).
 * }}
 *
 * @package evocore
 *
 * {@internal Below is a list of authors who have contributed to design/coding of this file: }}
 * @author fplanque: Francois PLANQUE.
 * @author mbruneau: Marc BRUNEAU / PROGIDISTRI
 *
 * @version $Id$
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

load_class( '_core/model/dataobjects/_dataobject.class.php', 'DataObject' );

/**
 * ItemType Class
 *
 * @package evocore
 */
class ItemType extends DataObject
{
	var $name;


	/**
	 * Constructor
	 *
	 *
	 * @param table Database row
	 */
	function ItemType( $db_row = NULL )
	{
		// Call parent constructor:
		parent::DataObject( 'T_items__type', 'ptyp_', 'ptyp_ID' );

		$this->delete_restrictions = array(
				array( 'table'=>'T_ityp_col', 'fk'=>'itco_ityp_ID', 'msg'=>T_('%d related collections') ), // "Lignes de missions"
				array( 'table'=>'T_items__item', 'fk'=>'post_ptyp_ID', 'msg'=>T_('%d related items') ), // "Lignes de visit reports"
			);

 		if( $db_row != NULL )
		{
			$this->ID      		 = $db_row->ptyp_ID 		;
			$this->name  			 = $db_row->ptyp_name 	;
		}
	}

	/**
	 * Get the name of the ItemType
	 * @return string
	 */
	function get_name()
	{
		return $this->name;
	}
}

/*
 * $Log$
 * Revision 1.4  2009/09/14 13:17:28  efy-arrin
 * Included the ClassName in load_class() call with proper UpperCase
 *
 * Revision 1.3  2009/03/08 23:57:44  fplanque
 * 2009
 *
 * Revision 1.2  2008/01/21 09:35:31  fplanque
 * (c) 2008
 *
 * Revision 1.1  2007/06/25 11:00:28  fplanque
 * MODULES (refactored MVC)
 *
 */
?>