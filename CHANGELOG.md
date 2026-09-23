# Release Notes for Klaviyo Connect Plus

## Unreleased

### Fixed
- Fixed a bug where changes an `addProfileProperties` handler made to `$event->profile` weren't sent to Klaviyo.
- Fixed an error on edit screens when the saved Klaviyo list isn't in the connected account.
- Fixed an error when the action named in a `forward` param returned no response, such as a failed payment.
- Klaviyo API failures no longer break host requests. Errors are caught, logged to the `klaviyo-connect-plus` category, and the request continues.
- Klaviyo API calls now hard-cap at 10s (5s connect) with retries disabled, so an unresponsive Klaviyo can't stall the host request.
