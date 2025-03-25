<?php

namespace fostercommerce\klaviyoconnectplus;

use Craft;
use craft\base\Model;
use craft\commerce\elements\Order;
use craft\commerce\events\OrderStatusEvent;
use craft\commerce\events\RefundTransactionEvent;
use craft\commerce\services\OrderHistories;
use craft\commerce\services\Payments;
use craft\elements\User;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\services\Fields;
use craft\services\Utilities;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use fostercommerce\klaviyoconnectplus\models\Settings;
use fostercommerce\klaviyoconnectplus\queue\jobs\TrackOrderComplete;
use fostercommerce\klaviyoconnectplus\utilities\KCUtilities;
use fostercommerce\klaviyoconnectplus\variables\Variable;
use yii\base\Event;

/**
 * @package fostercommerce\klaviyoconnectplus
 * @property \fostercommerce\klaviyoconnectplus\services\Api $api
 * @property \fostercommerce\klaviyoconnectplus\services\Track $track
 * @property \fostercommerce\klaviyoconnectplus\services\Map $map
 * @property \fostercommerce\klaviyoconnectplus\services\Cart $cart
 */
class Plugin extends \craft\base\Plugin
{
	public bool $hasCpSettings = true;

	public function init(): void
	{
		parent::init();

		$this->setComponents([
			'api' => \fostercommerce\klaviyoconnectplus\services\Api::class,
			'track' => \fostercommerce\klaviyoconnectplus\services\Track::class,
			'map' => \fostercommerce\klaviyoconnectplus\services\Map::class,
			'cart' => \fostercommerce\klaviyoconnectplus\services\Cart::class,
		]);

		/** @var Settings $settings */
		$settings = $this->getSettings();

		// TODO: Fix this page - the date range picker is not working
		// Event::on(
		// 	Utilities::class,
		// 	Utilities::EVENT_REGISTER_UTILITIES,
		// 	static function(RegisterComponentTypesEvent $event): void {
		// 		$event->types[] = KCUtilities::class;
		// 	}
		// );

		Event::on(
			UrlManager::class,
			UrlManager::EVENT_REGISTER_CP_URL_RULES,
			static function (RegisterUrlRulesEvent $event): void {
				$event->rules['klaviyo-connect-plus/sync-orders'] = 'klaviyo-connect-plus/api/sync-orders';
			}
		);

		Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, static function (RegisterComponentTypesEvent $event): void {
			$event->types[] = \fostercommerce\klaviyoconnectplus\fields\ListField::class;
			$event->types[] = \fostercommerce\klaviyoconnectplus\fields\ListsField::class;
		});

		if ($settings->trackSaveUser) {
			Event::on(User::class, User::EVENT_AFTER_SAVE, static function (Event $event): void {
				self::getInstance()->track->onSaveUser($event);
			});
		}

		if (Craft::$app->plugins->isPluginEnabled('commerce')) {
			if ($settings->trackCommerceCartUpdated) {
				Event::on(Order::class, Order::EVENT_AFTER_SAVE, static function (Event $e): void {
					self::getInstance()->track->onCartUpdated($e);
				});
			}

			if ($settings->trackCommerceOrderCompleted) {
				Event::on(Order::class, Order::EVENT_AFTER_COMPLETE_ORDER, static function (Event $e): void {
					/** @var Order $order */
					$order = $e->sender;
					Craft::$app->getQueue()->delay(10)->push(new TrackOrderComplete([
						'name' => $e->name,
						'orderId' => $order->id,
					]));
				});
			}

			if ($settings->trackCommerceStatusUpdated) {
				Event::on(
					OrderHistories::class,
					OrderHistories::EVENT_ORDER_STATUS_CHANGE,
					static function (OrderStatusEvent $e): void {
						self::getInstance()->track->onStatusChanged($e);
					}
				);
			}

			if ($settings->trackCommerceRefunded) {
				Event::on(
					Payments::class,
					Payments::EVENT_AFTER_REFUND_TRANSACTION,
					static function (RefundTransactionEvent $e): void {
						self::getInstance()->track->onOrderRefunded($e);
					}
				);
			}
		}

		Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, static function (Event $event): void {
			$variable = $event->sender;
			$variable->set('klaviyoconnectplus', Variable::class);
		});
	}

	protected function settingsHtml(): ?string
	{
		return Craft::$app->getView()->renderTemplate('klaviyo-connect-plus/settings', [
			'settings' => $this->getSettings(),
		]);
	}

	protected function createSettingsModel(): ?Model
	{
		return new Settings();
	}
}
