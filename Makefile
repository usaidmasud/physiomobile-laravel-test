prod:
	docker compose -f docker-compose-prod.yml up -d

prod-down:
	docker compose -f docker-compose-prod.yml down -v

build:
	docker compose build --no-cache

up:
	docker compose up -d

down:
	docker compose down -v

stop:
	docker compose stop

restart:
	docker compose restart
	
create-project:
	docker compose run --rm app bash -c "rm -rf * && composer create-project --prefer-dist laravel/laravel ."

up-pma:
	docker compose up -d pma
	
# php artisan l5-swagger:generate
