#!/bin/sh
set -eu

if [ -z "${APP_SECRET:-}" ]; then
	APP_SECRET="$(php -r 'echo bin2hex(random_bytes(16));')"
	export APP_SECRET
fi

php bin/console cache:clear \
	--env="${APP_ENV:-prod}" \
	--no-interaction

exec docker-php-entrypoint "$@"
