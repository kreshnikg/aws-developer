up: ## start all containers
	docker-compose up -d

down: ## stop all containers
	docker-compose down

bash: ## bash session on php container. useful to run tests and stuff
	docker container exec -it aws-developer-php-1 bash

test: ## bash session on php container. useful to run tests and stuff
	docker container exec aws-developer-php-1 php vendor/bin/phpunit