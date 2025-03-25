<?php

namespace fostercommerce\klaviyoconnectplus\services;

use craft\helpers\App;
use fostercommerce\klaviyoconnectplus\models\Settings;
use fostercommerce\klaviyoconnectplus\Plugin;
use yii\base\Component;

abstract class Base extends Component
{
	private ?Settings $settings = null;

	protected function getSetting(string $name): mixed
	{
		if (! $this->settings instanceof Settings) {
			/** @var Settings $settings */
			$settings = Plugin::getInstance()->getSettings();
			$this->settings = $settings;
		}

		$value = $this->settings->{$name};

		if (is_string($value)) {
			return App::parseEnv($value);
		}

		return $value;
	}
}
