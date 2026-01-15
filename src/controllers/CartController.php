<?php

namespace fostercommerce\klaviyoconnectplus\controllers;

use Craft;
use craft\commerce\Plugin as Commerce;
use craft\web\Controller;
use fostercommerce\klaviyoconnectplus\models\Settings;
use fostercommerce\klaviyoconnectplus\Plugin;
use yii\web\HttpException;
use yii\web\Response;

class CartController extends Controller
{
	protected array|int|bool $allowAnonymous = true;

	public function actionRestore(): Response
	{
		if (! Craft::$app->plugins->isPluginEnabled('commerce')) {
			throw new HttpException(400, 'Craft Commerce needs to be installed and enabled to restore carts.');
		}

		$number = Craft::$app->getRequest()->getParam('number');

		if (! $number) {
			throw new HttpException(400, 'Cart number is required');
		}

		$commerce = Commerce::getInstance();
		$order = $commerce->getOrders()->getOrderByNumber($number);

		if (! $order) {
			throw new HttpException(404, 'Cart not found');
		}

		if ($order->isCompleted) {
			throw new HttpException(400, 'Cannot restore a completed order');
		}

		if (! $order->hasLineItems()) {
			throw new HttpException(400, 'Cart is empty');
		}

		// Get current user
		$currentUser = Craft::$app->getUser()->getIdentity();
		$currentUserId = $currentUser ? $currentUser->id : null;

		// Check if cart belongs to a user account
		// If user is not logged in, or logged in as different user
		if ($order->customerId && (! $currentUserId || $order->customerId !== $currentUserId)) {
			// Show message page using CP template mode
			$loginPath = Craft::$app->getConfig()->getGeneral()->getLoginPath();
			$loginUrl = \craft\helpers\UrlHelper::url($loginPath, [
				'return' => Craft::$app->getRequest()->getAbsoluteUrl(),
			]);
			$view = Craft::$app->getView();
			$oldMode = $view->getTemplateMode();
			$view->setTemplateMode(\craft\web\View::TEMPLATE_MODE_CP);
			$html = $view->renderTemplate('klaviyo-connect-plus/login-required', [
				'loginUrl' => $loginUrl,
				'message' => Craft::t('klaviyo-connect-plus', 'This cart belongs to a user account. Please log in to view it.'),
			]);
			$view->setTemplateMode($oldMode);
			return $this->asRaw($html);
		}

		// At this point, either:
		// - Cart has no customer (guest cart), OR
		// - Current user owns the cart

		// Clear current cart
		$cartsService = $commerce->getCarts();
		$cartsService->forgetCart();

		// Set session
		$session = Craft::$app->getSession();
		$session->set('commerce_cart', $order->number);

		// Use reflection to set the cart
		try {
			$reflection = new \ReflectionClass($cartsService);
			$cartProperty = $reflection->getProperty('_cart');
			$cartProperty->setAccessible(true);
			$cartProperty->setValue($cartsService, $order);
		} catch (\Exception $exception) {
			Craft::error('Reflection failed: ' . $exception->getMessage(), 'klaviyo-connect-plus');
		}

		$session->setNotice(Craft::t('klaviyo-connect-plus', 'Your cart has been restored.'));

		/** @var Settings $settings */
		$settings = Plugin::getInstance()->getSettings();
		$cartUrl = $settings->cartUrl;

		if ((string) $cartUrl === '') {
			throw new HttpException(400, 'Cart URL is required.');
		}

		return $this->redirect($cartUrl);
	}
}
