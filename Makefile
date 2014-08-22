install:
	./composer.phar install

test:
	./vendor/bin/phpcs --standard=PSR2 ./src
	./vendor/bin/phpmd ./src text cleancode,codesize,controversial,unusedcode
