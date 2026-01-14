<?php

namespace fostercommerce\klaviyoconnectplus\controllers;

use Craft;
use craft\web\Controller;
use craft\commerce\Plugin as Commerce;
use fostercommerce\klaviyoconnectplus\models\Settings;
use fostercommerce\klaviyoconnectplus\Plugin;
use yii\web\HttpException;
use yii\web\Response;

class CartController extends Controller
{
	protected array|int|bool $allowAnonymous = true;

	public function actionRestore(): Response
	{
		if (!Craft::$app->plugins->isPluginEnabled('commerce')) {
			throw new HttpException(400, 'Craft Commerce needs to be installed and enabled to restore carts.');
		}

		$number = Craft::$app->getRequest()->getParam('number');

		if (!$number) {
			throw new HttpException(400, 'Cart number is required');
		}

		$commerce = Commerce::getInstance();
		$order = $commerce->getOrders()->getOrderByNumber($number);

		if (!$order) {
			throw new HttpException(404, 'Cart not found');
		}

		if ($order->isCompleted) {
			throw new HttpException(400, 'Cannot restore a completed order');
		}

		if (!$order->hasLineItems()) {
			throw new HttpException(400, 'Cart is empty');
		}

		// Restore the cart using reflection (Commerce 5 workaround)
		$cartsService = $commerce->getCarts();
		$cartsService->forgetCart();

		try {
			$reflection = new \ReflectionClass($cartsService);
			$cartProperty = $reflection->getProperty('_cart');
			$cartProperty->setAccessible(true);
			$cartProperty->setValue($cartsService, $order);

			$session = Craft::$app->getSession();
			$session->set('commerce_cart', $order->number);

			$session->setNotice(Craft::t('klaviyo-connect-plus', 'Your cart has been restored.'));
		} catch (\Exception $e) {
			Craft::error('Failed to restore cart: ' . $e->getMessage(), 'klaviyo-connect-plus');
			throw new HttpException(500, 'Failed to restore cart');
		}

		/** @var Settings $settings */
		$settings = Plugin::getInstance()->getSettings();
		$cartUrl = $settings->cartUrl;

		if ((string) $cartUrl === '') {
			throw new HttpException(400, 'Cart URL is required. Configure it in Settings -> Klaviyo Connect Plus -> Cart URL');
		}

		return $this->redirect($cartUrl);
	}
}
