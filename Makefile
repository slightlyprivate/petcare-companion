DEV_COMPOSE = docker-compose.dev.yml

.PHONY: up upd down seed migrate logs ps 

up:
	docker compose -f $(DEV_COMPOSE) up

upd:
	docker compose -f $(DEV_COMPOSE) up -d

down:
	docker compose -f $(DEV_COMPOSE) down

migrate:
	docker compose -f $(DEV_COMPOSE) exec app php artisan migrate

seed:
	docker compose -f $(DEV_COMPOSE) exec app php artisan migrate
	docker compose -f $(DEV_COMPOSE) exec app php artisan db:seed

logs:
	docker compose -f $(DEV_COMPOSE) logs -f --tail=100

ps:
	docker compose -f $(DEV_COMPOSE) ps

