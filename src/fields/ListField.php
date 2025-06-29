<?php

namespace fostercommerce\klaviyoconnectplus\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use fostercommerce\klaviyoconnectplus\models\Settings;
use fostercommerce\klaviyoconnectplus\Plugin;
use GuzzleHttp\Exception\ClientException;

class ListField extends Field
{
	public static function displayName(): string
	{
		return Craft::t('klaviyo-connect-plus', 'Klaviyo List');
	}

	public function getInputHtml(mixed $value, ?ElementInterface $element = null): string
	{
		try {
			$lists = Plugin::getInstance()->api->getLists();
		} catch (ClientException) {
			$lists = [];
		}

		/** @var Settings $settings */
		$settings = Plugin::getInstance()->getSettings();

		$allLists = $settings->klaviyoListsAll;
		$availableLists = $settings->klaviyoAvailableLists;

		$listOptions = [];
		foreach ($lists as $list) {
			if ($allLists || in_array($list->id, $availableLists, true)) {
				$listOptions[$list->id] = $list->name;
			}
		}

		return Craft::$app->getView()->renderTemplate('klaviyo-connect-plus/fieldtypes/select', [
			'name' => $this->handle,
			'options' => $listOptions,
			'value' => $value ? $value->id : null,
		]);
	}

	public function normalizeValue(mixed $value, ?ElementInterface $element = null): mixed
	{
		if (is_array($value)) {
			$value = $value['id'] ?? null;
		}

		try {
			$lists = Plugin::getInstance()->api->getLists();
		} catch (ClientException) {
			$lists = [];
		}

		if ($value !== null) {
			foreach ($lists as $list) {
				if ($list->id === $value) {
					return $list;
				}
			}
		}

		return $value;
	}
}
