# Mini-SaaS

Projeto em desenvolvimento de um Mini-SaaS multi-tenant para gestão empresarial.

## Estrutura inicial

- `backend/`: aplicação Laravel
- `frontend/`: aplicação Nuxt 4
- `docker/`: arquivos de infraestrutura Docker
- `scripts/`: scripts utilitários
- `docs/`: documentação do projeto

## Stack

### Backend
- Laravel 12
- PHP 8.3+
- PostgreSQL
- Redis

### Frontend
- Nuxt 4
- Vue 3
- TypeScript
- SCSS
- Bootstrap 5

## Como executar o projeto

### Pré-requisitos

- Docker com Docker Compose v2 em execução.

### 1. Subir os containers

Na raiz do projeto, construa as imagens e inicie os serviços:

```bash
docker compose up -d --build
```

Na primeira inicialização, os containers criam os arquivos `.env` a partir dos exemplos, instalam as dependências e configuram a chave da aplicação e o segredo JWT do backend.

Para acompanhar a inicialização, use:

```bash
docker compose logs -f backend frontend
```

### 2. Executar as migrations do banco base

As migrations do banco base criam as tabelas globais da plataforma, como `tenants`, `domains`, `roles`, `permissions` e `permission_role`.

```bash
docker compose exec -it saas-backend php artisan migrate
```

### 3. Popular o banco base

O seeder principal cria os dados iniciais do banco base, incluindo roles, permissões e o tenant `base`.

```bash
docker compose exec -it saas-backend php artisan db:seed
```

### 4. Executar as migrations dos tenants

O tenant `base` é provisionado automaticamente quando é criado. Ainda assim, execute o comando abaixo para garantir que todos os bancos de tenant existentes recebam as migrations atuais. Repita este passo sempre que uma migration for adicionada em `backend/database/migrations/tenant`.

```bash
docker compose exec -it saas-backend php artisan tenants:migrate
```

### 5. Opcional: popular os bancos dos tenants

Para inserir os registros iniciais definidos em `DatabaseTenantSeeder` em todos os tenants existentes, execute:

```bash
docker compose exec -it saas-backend php artisan tenant:seed
```

Esse passo é opcional. O provisionamento padrão já cria a empresa e o usuário inicial do tenant; o seed adiciona os dados de exemplo definidos para cada banco de tenant.

### Endereços locais

- Aplicação: `http://app.127.0.0.1.sslip.io:8081`
- API: `http://api.app.127.0.0.1.sslip.io:8081/api`
- Dashboard do Traefik: `http://localhost:8080`

## Uso e direitos

Este é um projeto pessoal desenvolvido para portfólio. Não é um software aberto e não concede permissão para uso, cópia, modificação, distribuição ou comercialização, total ou parcial, sem autorização prévia e expressa do autor.
