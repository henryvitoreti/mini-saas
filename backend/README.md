# Backend

API Laravel responsável pela autenticação, regras de negócio, persistência de dados e isolamento multi-tenant da plataforma. Para preparar os containers e o ambiente local, consulte o [README da raiz](../README.md).

## Stack

- Laravel 13 e PHP 8.3+
- PostgreSQL
- Redis
- JWT (`tymon/jwt-auth`)
- `stancl/tenancy` para multi-tenancy por banco de dados

## Arquitetura

O fluxo padrão é:

```text
Controller → Service → Repository → Model
```

| Camada | Responsabilidade |
| --- | --- |
| Controllers | Recebem a requisição, delegam o caso de uso ao Service e retornam Resources e respostas HTTP padronizadas. |
| Services | Orquestram regras de negócio, transações e integrações entre repositórios. |
| Repositories | Centralizam consultas, filtros, paginação e acesso aos Models. |
| Requests | Estendem `FormRequest`, normalizam a entrada e declaram regras e mensagens de validação. |
| Resources | Transformam Models no contrato de resposta da API, inclusive nas versões simples e detalhada. |

Controllers não devem concentrar regra de negócio nem serializar Models diretamente; use Resources nas respostas externas.

## Estrutura principal

```text
app/
├── Console/Commands/     # comandos Artisan do projeto
├── Helpers/              # helpers de contexto, empresa e permissões
├── Http/
│   ├── Controllers/Api/  # endpoints da API
│   ├── Middleware/       # tenancy, autenticação e autorização
│   ├── Requests/         # FormRequests
│   └── Resources/        # serialização das respostas
├── Models/               # modelos Eloquent
├── Repositories/         # acesso e consultas aos dados
├── Services/             # casos de uso e regras de negócio
└── Support/              # utilitários de infraestrutura e roteamento

database/
├── migrations/           # migrations do banco central
├── migrations/tenant/    # migrations aplicadas a cada banco de tenant
└── seeders/              # seeders centrais e de tenant

routes/api.php            # rotas HTTP da API
```

## Multi-tenancy

O tenant é resolvido pelo domínio da requisição, a partir da tabela central `domains`. O middleware `tenant.domain` localiza o tenant ativo e inicializa o contexto correspondente antes de a rota continuar.

O banco central contém `tenants`, `domains`, `roles`, `permissions` e `permission_role`. Cada tenant possui seu próprio banco para `company`, `users`, `customers` e os dados operacionais que forem adicionados.

`tenants.role_id` e `company.role_id` devem permanecer sincronizados: ambos apontam logicamente para a role central que define as permissões efetivas da empresa. Consulte [docs/tenancy.md](../docs/tenancy.md) e [docs/permissions.md](../docs/permissions.md) para os detalhes do modelo.

## Autenticação e autorização

- `POST /login` autentica o usuário do tenant atual e retorna um token JWT, empresa e permissões.
- Rotas autenticadas usam o middleware `jwt.tenant.auth` e o header `Authorization: Bearer <token>`.
- O middleware `company.permission` valida a permissão ativa da role da empresa para rotas operacionais.
- O frontend pode usar a lista de permissões para a interface, mas a API é a fonte de verdade para autorização.

## Respostas e validação

As respostas usam o formato abaixo:

```json
{
  "message": "Operação realizada com sucesso.",
  "data": {}
}
```

Em erros, a API retorna `message` e `errors`. Listagens paginadas ficam em `data.items` e incluem os metadados de paginação.

Use um `FormRequest` em operações de escrita. Ele deve normalizar os dados em `prepareForValidation()`, delegar regras ao Model quando aplicável e retornar mensagens específicas quando necessário. Falhas de validação retornam `422` com erros por campo.

## Comandos úteis

Execute os comandos Laravel pelo serviço `backend` do Docker Compose:

```bash
# Inspeção
docker compose exec backend php artisan route:list
docker compose exec backend php artisan optimize:clear

# Banco central
docker compose exec backend php artisan migrate
docker compose exec backend php artisan db:seed
docker compose exec backend php artisan make:migration create_example_table

# Bancos dos tenants
docker compose exec backend php artisan tenants:migrate
docker compose exec backend php artisan tenant:seed
docker compose exec backend php artisan tenants:make-migration create_example_table

# Atualiza o catálogo central de permissões e seus vínculos com roles
docker compose exec backend php artisan permission:update
```

`make:migration` cria uma migration do banco central. `tenants:make-migration` cria a migration em `database/migrations/tenant`; depois de criá-la, execute `tenants:migrate` para aplicá-la a todos os tenants existentes. O comando `tenant:seed` executa `DatabaseTenantSeeder` em todos os tenants.

## Convenções

- Mantenha rotas agrupadas em `routes/api.php` e aplique os middlewares adequados ao contexto central ou tenant.
- Use `ApiResponseTrait` para respostas consistentes e `ApiBaseController` como base dos controllers de API.
- Proteja dados de tenant com o contexto de tenancy; não consulte ou grave dados operacionais na conexão central.
- Use Resources para respostas externas e Repositories para consultas reutilizáveis.
- Cadastre permissões e roles somente no banco central; não replique essas tabelas nos bancos de tenant.
- Ao alterar o pacote de acesso de uma empresa, sincronize os dois `role_id` e invalide o cache de permissões do tenant.

## Uso e direitos

Este é um projeto pessoal desenvolvido para portfólio. Não é um software aberto e não concede permissão para uso, cópia, modificação, distribuição ou comercialização, total ou parcial, sem autorização prévia e expressa do autor.
