<?php

namespace fostercommerce\klaviyoconnectplus\utilities;

use Craft;
use craft\base\Utility;

class KCUtilities extends Utility
{
	/**
	 * displayName.
	 *
	 * @author	Unknown
	 * @since	v0.0.1
	 * @version	v1.0.0	Monday, May 23rd, 2022.
	 * @access	public static
	 */
	public static function displayName(): string
	{
		return Craft::t('klaviyo-connect-plus', 'Klaviyo Connect Plus');
	}

	/**
	 * id.
	 *
	 * @author	Unknown
	 * @since	v0.0.1
	 * @version	v1.0.0	Monday, May 23rd, 2022.
	 * @access	public static
	 */
	public static function id(): string
	{
		return 'klaviyo-connect-plus';
	}

	/**
	 * iconPath.
	 *
	 * @author	Unknown
	 * @since	v0.0.1
	 * @version	v1.0.0	Monday, May 23rd, 2022.
	 * @access	public static
	 */
	public static function icon(): string
	{
		return Craft::getAlias('@fostercommerce/klaviyoconnectplus/icon-mask.svg');
	}

	/**
	 * contentHtml.
	 *
	 * @author	Unknown
	 * @since	v0.0.1
	 * @version	v1.0.0	Monday, May 23rd, 2022.
	 * @access	public static
	 */
	public static function contentHtml(): string
	{
		return Craft::$app->getView()->renderTemplate('klaviyo-connect-plus/utilities');
	}
}
