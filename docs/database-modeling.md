# Modelagem de Dominio e Banco de Dados

## Objetivo

Definir a estrutura de dados inicial do sistema de acordo com o ERD atual, mantendo o projeto simples, entregavel e coerente com o MVP.

A modelagem esta dividida em dois contextos:

- **Banco base**: armazena dados globais da plataforma e resolve o tenant.
- **Banco do tenant**: armazena os dados operacionais de cada oficina.

---

## Estrategia de Banco de Dados

### 1. Banco base

Responsavel por identificar tenants, dominios, roles centralizadas e permissoes globais disponiveis na plataforma.

### 2. Banco do tenant

Responsavel pelos dados internos da oficina, incluindo usuarios, clientes, veiculos, ordens de servico, checklists e dados da empresa.

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
- Dados especificos da oficina ficam no banco do tenant, nao no banco base.

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

Armazena os usuarios internos da oficina.

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

Armazena os clientes da oficina.

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

## Tabela: `vehicles`

Armazena os veiculos cadastrados na oficina.

### Campos

- `id`
- `plate`
- `brand`
- `model`
- `version`
- `year_manufacture`
- `year_model`
- `color`
- `fuel_type`
- `transmission_type`
- `odometer`
- `chassis`
- `notes`
- `is_active`
- `created_at`
- `updated_at`
- `deleted_at`

### Constraints

- primary key: `id`

### Observacoes

- O veiculo nao possui vinculo direto com cliente no ERD atual.
- O vinculo entre cliente e veiculo acontece pela ordem de servico.
- Isso permite que o mesmo veiculo esteja relacionado a clientes diferentes ao longo do tempo.

---

## Tabela: `work_orders`

Armazena as ordens de servico da oficina.

### Campos

- `id`
- `order_number`
- `customer_id`
- `vehicle_id`
- `assigned_user_id`
- `status`
- `priority`
- `entry_date`
- `expected_delivery_date`
- `completed_at`
- `odometer`
- `customer_complaint`
- `technical_diagnosis`
- `general_notes`
- `created_at`
- `updated_at`

### Constraints

- primary key: `id`
- foreign key: `customer_id` -> `customers.id`
- foreign key: `vehicle_id` -> `vehicles.id`
- foreign key: `assigned_user_id` -> `users.id`
- unique: `order_number`

### Observacoes

- Nao ha soft delete para ordens de servico no ERD atual.
- A exclusao de uma ordem de servico nao deve ser um fluxo comum.
- O fluxo principal deve ser controle por status.

### Status sugeridos

- `open`
- `in_progress`
- `waiting_approval`
- `completed`
- `cancelled`

### Prioridades sugeridas

- `low`
- `medium`
- `high`

---

## Tabela: `checklists`

Armazena checklists vinculados a ordens de servico.

### Campos

- `id`
- `work_order_id`
- `title`
- `status`
- `notes`
- `created_at`
- `updated_at`

### Constraints

- primary key: `id`
- foreign key: `work_order_id` -> `work_orders.id`

### Observacoes

- Nao ha soft delete para checklists no ERD atual.
- A estrutura foi mantida simples para permitir evolucao futura.

### Status sugeridos

- `pending`
- `completed`

---

## Tabela: `checklist_items`

Armazena os itens de cada checklist.

### Campos

- `id`
- `checklist_id`
- `label`
- `description`
- `is_required`
- `is_checked`
- `checked_at`
- `position`
- `created_at`
- `updated_at`

### Constraints

- primary key: `id`
- foreign key: `checklist_id` -> `checklists.id`

### Observacoes

- Nao ha soft delete para itens de checklist no ERD atual.
- Itens podem ser removidos definitivamente.

---

## Tabela: `company`

Armazena os dados da empresa/oficina do tenant.

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

- `name`: nome da empresa/oficina.
- `document`: CNPJ ou documento da empresa.
- `logo_path`: caminho do arquivo de logo da empresa.
- `notes`: observacoes internas sobre a empresa.

### Constraints

- primary key: `id`
- referencia logica: `role_id` -> `roles.id` no banco base

### Observacoes

- Cada tenant deve possuir apenas um registro em `company`.
- O tenant base deve iniciar com um registro chamado `Base`.
- A tabela fica no banco do tenant porque representa dados especificos da oficina.
- `role_id` aponta para uma role central e nao deve depender de foreign key fisica entre bancos.

---

# 3. Regras de Soft Delete

## Tabelas com soft delete

- `tenants`
- `roles`
- `users`
- `customers`
- `vehicles`

## Tabelas sem soft delete

- `domains`
- `permissions`
- `permission_role`
- `work_orders`
- `checklists`
- `checklist_items`
- `company`

### Justificativa

Soft delete e usado apenas onde faz sentido restaurar dados ou evitar exclusao acidental de cadastros principais.

Tabelas operacionais, tabelas pivot, configuracoes globais e dados unicos por tenant ficam sem soft delete no ERD atual.

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

- `work_orders` belongsTo `customers`
- `work_orders` belongsTo `vehicles`
- `work_orders` belongsTo `users` usando `assigned_user_id`
- `checklists` belongsTo `work_orders`
- `checklist_items` belongsTo `checklists`

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
- `vehicles`
- `work_orders`
- `checklists`
- `checklist_items`
- `company`

---

# 6. Pontos deixados para evolucao futura

Os itens abaixo seguem fora do ERD atual para reduzir complexidade inicial:

- planos
- assinaturas
- usuarios globais
- roles por usuario
- auditoria avancada
- tabela separada de enderecos
- vinculo direto entre cliente e veiculo
- historico de dono do veiculo
