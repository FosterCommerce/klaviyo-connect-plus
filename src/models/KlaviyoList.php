<?php

namespace fostercommerce\klaviyoconnectplus\models;

use craft\base\Model;

class KlaviyoList extends Model implements \Stringable
{
	public string $id;

	public string $name;

	/**
	 * __toString.
	 *
	 * @author	Unknown
	 * @since	v0.0.1
	 * @version	v1.0.0	Monday, May 23rd, 2022.
	 * @access	public
	 */
	public function __toString(): string
	{
		return $this->name;
	}
}
