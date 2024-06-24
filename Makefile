up:
	./vendor/bin/sail up
down:
	./vendor/bin/sail down
stop:
	./vendor/bin/sail stop
migrate:
	./vendor/bin/sail artisan migrate

doc_generate:
	@echo "Start generate api documentation"
	php artisan l5-swagger:generate
