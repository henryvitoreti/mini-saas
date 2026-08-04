# Modelagem de Dominio e Banco de Dados

## Objetivo

Definir a estrutura de dados planejada do sistema de acordo com o ERD atual, mantendo o projeto simples, entregavel e coerente com o MVP. Esta documentacao nao cria tabelas, migrations ou funcionalidades.

A modelagem esta dividida em dois contextos:

- **Banco base**: armazena dados globais da plataforma e resolve o tenant.
- **Banco do tenant**: armazena os dados operacionais de cada tenant.

---

## Estrategia de Banco de Dados

### 1. Banco base

Responsavel por identificar tenants, dominios, roles centralizadas e permissoes globais disponiveis na plataforma.

### 2. Banco do tenant

Responsavel pelos dados internos de cada tenant, incluindo usuarios, clientes, catalogo, vendas, estoque, ordens de servico e dados da empresa.

---

# 1. Banco Base

## Tabela: `tenants`

Armazena os tenants cadastrados na plataforma.

### Campos

- `id`
- `active`
- `data`
- `created_at`
- `updated_at`
- `deleted_at`

### Descricao dos campos

- `id`: identificador do tenant.
- `active`: indica se o tenant esta ativo.
- `data`: dados adicionais do tenant em formato JSON.
- `deleted_at`: permite exclusao logica do tenant.

### Constraints

- primary key: `id`

### Observacoes

- O campo `id` segue o padrao do pacote de tenancy.
- Dados especificos de cada tenant ficam no banco do tenant, nao no banco base.

---

## Tabela: `domains`

Armazena os dominios vinculados aos tenants.

### Campos

- `id`
- `domain`
- `tenant_id`
- `created_at`
- `updated_at`

### Descricao dos campos

- `domain`: dominio ou subdominio usado para resolver o tenant.
- `tenant_id`: tenant relacionado ao dominio.

### Constraints

- primary key: `id`
- foreign key: `tenant_id` -> `tenants.id`
- unique: `domain`

### Observacoes

- Um tenant pode possuir um ou mais dominios.
- A resolucao do tenant acontece a partir do dominio da requisicao.

---

## Tabela: `permissions`

Armazena as permissoes globais disponiveis na plataforma.

### Campos

- `id`
- `name`
- `slug`
- `base_front_url`
- `base_api_url`
- `created_at`
- `updated_at`

### Descricao dos campos

- `name`: nome exibido da permissao.
- `slug`: identificador unico da permissao.
- `base_front_url`: rota base no frontend relacionada a permissao.
- `base_api_url`: rota base na API relacionada a permissao.

### Constraints

- primary key: `id`
- unique: `slug`

### Observacoes

- As permissoes ficam no banco base porque sao globais da plataforma.
- O controle de disponibilidade da permissao para uma role fica no pivot `permission_role`.
- `show_locked_routes` e `is_active` sao configuracoes do vinculo entre role e permissao, nao da permissao global.

---

## Tabela: `roles`

Armazena os perfis de acesso centralizados da plataforma.

### Campos

- `id`
- `name`
- `slug`
- `description`
- `is_active`
- `created_at`
- `updated_at`
- `deleted_at`

### Constraints

- primary key: `id`
- unique: `slug`

### Observacoes

- Roles ficam no banco base e sao manipuladas pelo admin da plataforma.
- Tenants nao possuem mais copia local de roles.
- A empresa do tenant aponta para uma role central por `company.role_id`.

---

## Tabela: `permission_role`

Relaciona roles centralizadas com permissoes globais da plataforma.

### Campos

- `role_id`
- `permission_id`
- `show_locked_routes`
- `is_active`

### Descricao dos campos

- `role_id`: role central relacionada.
- `permission_id`: permissao global relacionada.
- `show_locked_routes`: indica se a rota bloqueada pode aparecer na interface com cadeado.
- `is_active`: indica se a permissao esta liberada para a role.

### Constraints

- referencia: `role_id` -> `roles.id`
- referencia: `permission_id` -> `permissions.id`

### Observacoes

- A tabela funciona como pivot central entre roles e permissoes.
- Como `roles`, `permissions` e `permission_role` ficam no banco base, o relacionamento entre elas nao atravessa bancos.

---

# 2. Banco do Tenant

## Tabela: `users`

Armazena os usuarios internos do tenant.

### Campos

- `id`
- `name`
- `email`
- `password`
- `phone`
- `is_active`
- `last_login_at`
- `created_at`
- `updated_at`
- `deleted_at`

### Constraints

- primary key: `id`
- unique: `email`

### Observacoes

- Usuarios nao possuem mais `role_id` no ERD atual.
- O perfil de acesso do tenant e definido em `company.role_id`.
- Nao ha tabela pivot `role_user` no ERD atual.

---

## Tabela: `customers`

Armazena os clientes do tenant.

### Campos

- `id`
- `name`
- `type`
- `document`
- `email`
- `phone`
- `secondary_phone`
- `zip_code`
- `street`
- `number`
- `complement`
- `district`
- `city`
- `state`
- `notes`
- `is_active`
- `created_at`
- `updated_at`
- `deleted_at`

### Descricao dos campos

- `type`: tipo de cliente, como pessoa fisica ou pessoa juridica.
- `document`: CPF ou CNPJ do cliente.

### Constraints

- primary key: `id`

### Observacoes

- O endereco do cliente fica diretamente na tabela.
- Nao ha tabela separada de enderecos no ERD atual.

---

## Tabela: `product_categories`

Armazena as categorias dos produtos.

### Campos

- `id`
- `name`
- `description`
- `is_active`
- `created_at`
- `updated_at`
- `deleted_at`

### Constraints

- primary key: `id`

---

## Tabela: `products`

Armazena os produtos comercializados ou utilizados pela empresa.

### Campos

- `id`
- `category_id`
- `name`
- `sku`
- `barcode`
- `description`
- `cost_price`
- `sale_price`
- `stock_quantity`
- `allow_negative_stock`
- `is_active`
- `created_at`
- `updated_at`
- `deleted_at`

### Constraints

- primary key: `id`
- foreign key: `category_id` -> `product_categories.id`

---

## Tabela: `services`

Armazena os servicos oferecidos pela empresa.

### Campos

- `id`
- `name`
- `description`
- `price`
- `is_active`
- `created_at`
- `updated_at`
- `deleted_at`

### Constraints

- primary key: `id`

---

## Tabela: `sale_orders`

Armazena os dados consolidados das vendas.

### Campos

- `id`
- `customer_id`
- `user_id`
- `subtotal`
- `discount`
- `total`
- `notes`
- `created_at`
- `updated_at`
- `deleted_at`

### Constraints

- primary key: `id`
- foreign key: `customer_id` -> `customers.id`
- foreign key: `user_id` -> `users.id`

---

## Tabela: `sale_order_items`

Armazena os produtos de cada venda.

### Campos

- `id`
- `sale_order_id`
- `product_id`
- `quantity`
- `unit_price`
- `discount`
- `subtotal`
- `total`
- `created_at`
- `updated_at`

### Constraints

- primary key: `id`
- foreign key: `sale_order_id` -> `sale_orders.id`
- foreign key: `product_id` -> `products.id`

---

## Tabela: `work_orders`

Armazena as ordens de servico do tenant.

### Campos

- `id`
- `order_number`
- `customer_id`
- `assigned_user_id`
- `status` (`enum`)
- `completed_at`
- `customer_complaint`
- `technical_diagnosis`
- `general_notes`
- `subtotal`
- `discount`
- `total`
- `created_at`
- `updated_at`
- `deleted_at`

### Constraints

- primary key: `id`
- foreign key: `customer_id` -> `customers.id`
- foreign key: `assigned_user_id` -> `users.id`
- unique: `order_number`

---

## Tabela: `work_order_product_items`

Armazena os produtos utilizados em cada ordem de servico.

### Campos

- `id`
- `work_order_id`
- `product_id`
- `quantity`
- `unit_price`
- `discount`
- `subtotal`
- `total`
- `created_at`
- `updated_at`

### Constraints

- primary key: `id`
- foreign key: `work_order_id` -> `work_orders.id`
- foreign key: `product_id` -> `products.id`

---

## Tabela: `work_order_service_items`

Armazena os servicos utilizados em cada ordem de servico.

### Campos

- `id`
- `work_order_id`
- `service_id`
- `quantity`
- `unit_price`
- `discount`
- `subtotal`
- `total`
- `created_at`
- `updated_at`

### Constraints

- primary key: `id`
- foreign key: `work_order_id` -> `work_orders.id`
- foreign key: `service_id` -> `services.id`

---

## Tabela: `stock_transactions`

Armazena as movimentacoes de estoque dos produtos.

### Campos

- `id`
- `product_id`
- `user_id`
- `sale_order_id`
- `work_order_id`
- `type` (`enum`)
- `quantity`
- `previous_quantity`
- `current_quantity`
- `unit_cost`
- `notes`
- `created_at`

### Constraints

- primary key: `id`
- foreign key: `product_id` -> `products.id`
- foreign key: `user_id` -> `users.id`
- foreign key: `sale_order_id` -> `sale_orders.id`
- foreign key: `work_order_id` -> `work_orders.id`

---

## Tabela: `company`

Armazena os dados da empresa do tenant.

### Campos

- `id`
- `role_id`
- `name`
- `document`
- `email`
- `phone`
- `secondary_phone`
- `zip_code`
- `street`
- `number`
- `complement`
- `district`
- `city`
- `state`
- `logo_path`
- `notes`
- `created_at`
- `updated_at`

### Descricao dos campos

- `name`: nome da empresa.
- `document`: CNPJ ou documento da empresa.
- `logo_path`: caminho do arquivo de logo da empresa.
- `notes`: observacoes internas sobre a empresa.

### Constraints

- primary key: `id`
- referencia logica: `role_id` -> `roles.id` no banco base

### Observacoes

- Cada tenant deve possuir apenas um registro em `company`.
- O tenant base deve iniciar com um registro chamado `Base`.
- A tabela fica no banco do tenant porque representa dados especificos da empresa.
- `role_id` aponta para uma role central e nao deve depender de foreign key fisica entre bancos.

---

# 3. Regras de Soft Delete

## Tabelas com soft delete

- `tenants`
- `roles`
- `users`
- `customers`
- `product_categories`
- `products`
- `services`
- `sale_orders`
- `work_orders`

## Tabelas sem soft delete

- `domains`
- `permissions`
- `permission_role`
- `sale_order_items`
- `work_order_product_items`
- `work_order_service_items`
- `stock_transactions`
- `company`

### Justificativa

Soft delete e usado apenas onde faz sentido restaurar dados ou evitar exclusao acidental de cadastros principais.

Itens operacionais, tabelas pivot, configuracoes globais e dados unicos por tenant ficam sem soft delete. Cadastros principais, vendas e ordens de servico possuem `deleted_at` conforme a estrutura documentada.

---

# 4. Relacionamentos

## Banco base

- `tenants` hasMany `domains`
- `domains` belongsTo `tenants`
- `roles` hasMany `permission_role`
- `permission_role` belongsTo `roles`
- `permissions` hasMany `permission_role`
- `permission_role` belongsTo `permissions`
- `roles` belongsToMany `permissions` por `permission_role`
- `permissions` belongsToMany `roles` por `permission_role`

## Banco do tenant

- `product_categories` hasMany `products`
- `products` belongsTo `product_categories`
- `sale_orders` belongsTo `customers`
- `sale_orders` belongsTo `users`
- `sale_orders` hasMany `sale_order_items`
- `sale_order_items` belongsTo `sale_orders`
- `sale_order_items` belongsTo `products`
- `work_orders` belongsTo `customers`
- `work_orders` belongsTo `users` usando `assigned_user_id`
- `work_orders` hasMany `work_order_product_items`
- `work_order_product_items` belongsTo `work_orders`
- `work_order_product_items` belongsTo `products`
- `work_orders` hasMany `work_order_service_items`
- `work_order_service_items` belongsTo `work_orders`
- `work_order_service_items` belongsTo `services`
- `stock_transactions` belongsTo `products`
- `stock_transactions` belongsTo `users`
- `stock_transactions` belongsTo `sale_orders`
- `stock_transactions` belongsTo `work_orders`

## Relacionamento logico entre bancos

- `company.role_id` referencia logicamente `roles.id` no banco base

---

# 5. Estrutura final do ERD atual

## Banco base

- `tenants`
- `domains`
- `permissions`
- `roles`
- `permission_role`

## Banco do tenant

- `users`
- `customers`
- `product_categories`
- `products`
- `services`
- `sale_orders`
- `sale_order_items`
- `work_orders`
- `work_order_product_items`
- `work_order_service_items`
- `stock_transactions`
- `company`
