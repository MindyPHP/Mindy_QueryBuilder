test:
	php ./vendor/bin/phpunit

test-coverage-build:
	php -dzend_extension=/usr/local/opt/php72-xdebug/xdebug.so ./vendor/bin/phpunit --coverage-html=./coverage

test-coverage:
	php -dzend_extension=/usr/local/opt/php72-xdebug/xdebug.so ./vendor/bin/phpunit --coverage-text
