<?php

namespace fostercommerce\klaviyoconnectplus\services;

use Craft;
use craft\elements\User as UserElement;

class Map extends Base
{
	public function mapUser(?UserElement $user = null): array
	{
		if (! $user instanceof UserElement) {
			$user = Craft::$app->user->getIdentity();
		}

		if (! $user) {
			return [];
		}

		return [
			'external_id' => $user->id,
			'email' => $user->email,
			'first_name' => $user->firstName,
			'last_name' => $user->lastName,
		];
	}
}
