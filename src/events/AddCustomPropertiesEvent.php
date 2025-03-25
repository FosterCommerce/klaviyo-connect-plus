<?php

namespace fostercommerce\klaviyoconnectplus\events;

use yii\base\Event;

class AddCustomPropertiesEvent extends Event
{
	public $name;

	public $properties = [];
}
