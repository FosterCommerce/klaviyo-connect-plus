# Release Notes for Klaviyo Connect Plus

## 1.0.1 - 2026-05-05

### Fixed
- Klaviyo API failures no longer break host requests. Errors are caught, logged to the `klaviyo-connect-plus` category, and the request continues.
- Klaviyo API calls now hard-cap at 10s (5s connect) with retries disabled, so an unresponsive Klaviyo can't stall the host request.

## 1.0.0

- Initial release
