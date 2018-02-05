test:
	php ./vendor/bin/phpunit

test-coverage:
	php -dzend_extension=/usr/local/opt/php72-xdebug/xdebug.so ./vendor/bin/phpunit --coverage-html=./coverage
