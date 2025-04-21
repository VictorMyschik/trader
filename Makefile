restart:
	docker-compose up -d --force-recreate

init:
	docker-compose down && docker-compose up -d --build

stop:
	docker-compose down

supervisor:
	docker-compose down supervisor && docker-compose up -d --build supervisor

init-prod:
	docker-compose -f docker-compose.prod.yml down && docker-compose -f docker-compose.prod.yml up -d --remove-orphans

stop-prod:
	docker-compose -f docker-compose.prod.yml down
