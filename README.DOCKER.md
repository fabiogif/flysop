# Configuração Docker para Desenvolvimento

Este projeto está configurado para rodar em Docker com PHP 8.1, compatível com Laravel 8.

## Pré-requisitos

- Docker Desktop instalado e rodando
- Docker Compose v2

## Como usar

### Subir a aplicação

```bash
docker-compose -f docker-compose.dev.yml up -d
```

### Ver logs da aplicação

```bash
docker-compose -f docker-compose.dev.yml logs -f app
```

### Executar comandos Artisan

```bash
docker-compose -f docker-compose.dev.yml exec app php artisan [comando]
```

### Parar a aplicação

```bash
docker-compose -f docker-compose.dev.yml down
```

### Parar e remover volumes (limpar banco de dados)

```bash
docker-compose -f docker-compose.dev.yml down -v
```

## Serviços disponíveis

- **Aplicação Laravel**: http://localhost:8000
- **Nginx**: http://localhost:80
- **PostgreSQL**: localhost:5432
  - Database: laravel
  - User: laravel
  - Password: secret
- **MySQL**: localhost:3306
  - Database: laravel
  - User: laravel
  - Password: secret
- **MinIO Console**: http://localhost:8900
  - User: minio
  - Password: password
- **MinIO API**: http://localhost:9001

## Estrutura Docker

- `Dockerfile.dev`: Imagem PHP 8.1-FPM para desenvolvimento
- `docker-compose.dev.yml`: Configuração dos serviços Docker
- `docker/nginx/default.conf`: Configuração do Nginx
- `docker/php/local.ini`: Configurações PHP

## Variáveis de Ambiente

As variáveis de ambiente são carregadas do arquivo `.env`. Para usar o banco de dados local do Docker, certifique-se de que o `.env` está configurado com:

```
DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret
```

## Troubleshooting

### Problema de conexão com banco de dados

Se houver problemas de autenticação, recrie os volumes:

```bash
docker-compose -f docker-compose.dev.yml down -v
docker-compose -f docker-compose.dev.yml up -d
```

### Rebuild da imagem

```bash
docker-compose -f docker-compose.dev.yml build --no-cache app
```

### Acessar shell do container

```bash
docker-compose -f docker-compose.dev.yml exec app bash
```
