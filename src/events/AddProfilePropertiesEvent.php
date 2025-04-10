<?php

namespace fostercommerce\klaviyoconnectplus\events;

use yii\base\Event;

class AddProfilePropertiesEvent extends Event
{
	public ?string $event = null;

	public array $properties = [];

	public array $profile;

	public mixed $context;
}
