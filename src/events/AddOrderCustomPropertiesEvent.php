<?php

namespace fostercommerce\klaviyoconnectplus\events;

use craft\commerce\elements\Order;
use yii\base\Event;

class AddOrderCustomPropertiesEvent extends Event
{
	public array $properties = [];

	public Order $order;

	public string $event;
}
