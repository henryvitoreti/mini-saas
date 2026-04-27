# 🗃️ Modelagem de Domínio e Banco de Dados

## 🎯 Objetivo

Definir a estrutura de dados inicial do sistema antes da criação das migrations, mantendo o projeto simples, entregável e coerente com o prazo do MVP.

A modelagem foi pensada para:

- suportar multi-tenancy com landlord + database por tenant;
- evitar complexidade desnecessária no início;
- permitir evolução futura sem comprometer a entrega inicial.

---

## 🧱 Estratégia de Banco de Dados

O sistema será dividido em:

### 1. Banco base (landlord)
Responsável apenas por identificar e resolver os tenants.

### 2. Banco do tenant
Responsável pelos dados operacionais da oficina.

---

# 1. Banco Base (Landlord)

## Tabela: `tenants`

Armazena os tenants cadastrados no sistema.

### Campos

- `id`
- `name`
- `slug`
- `domain`
- `database_name`
- `is_active`
- `created_at`
- `updated_at`
- `deleted_at`

### Descrição dos campos

- `name`: nome do tenant/oficina
- `slug`: identificador interno derivado do nome
- `domain`: domínio ou subdomínio do tenant
- `database_name`: nome do banco de dados do tenant
- `is_active`: indica se o tenant está ativo
- `deleted_at`: permite exclusão lógica para futura remoção definitiva

### Constraints e índices

- unique: `slug`
- unique: `domain`
- unique: `database_name`
- index: `is_active`

### Observações

- Não haverá tabela separada para domínios
- Não haverá tabela separada para configuração de banco
- Não haverá plano, assinatura ou usuário global neste MVP
- O tenant poderá ser marcado para exclusão lógica antes da remoção definitiva do banco

---

# 2. Banco do Tenant

## Tabela: `roles`

Armazena os perfis de acesso internos da oficina.

### Campos

- `id`
- `name`
- `slug`
- `description`
- `show_locked_routes`
- `allow_permission_request`
- `is_active`
- `created_at`
- `updated_at`
- `deleted_at`

### Constraints e índices

- unique: `slug`
- index: `is_active`

### Observações

Esses campos suportam a ideia de exibir rotas bloqueadas no frontend:

- `show_locked_routes`
- `allow_permission_request`

---

## Tabela: `users`

Usuários internos da oficina.

### Campos

- `id`
- `role_id`
- `name`
- `email`
- `password`
- `phone`
- `is_active`
- `last_login_at`
- `created_at`
- `updated_at`
- `deleted_at`

### Constraints e índices

- foreign key: `role_id` → `roles.id`
- unique: `email`
- index: `role_id`
- index: `is_active`

### Observações

- Cada usuário possui apenas uma role
- Não haverá tabela pivot `role_user`

---

## Tabela: `customers`

Clientes da oficina.

### Campos

- `id`
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
- `notes`
- `is_active`
- `created_at`
- `updated_at`
- `deleted_at`

### Constraints e índices

- index: `document`
- index: `name`
- index: `phone`
- index: `is_active`

### Observações

- Endereço será armazenado diretamente na tabela
- Não haverá tabela separada de endereço neste MVP

---

## Tabela: `vehicles`

Veículos cadastrados no sistema.

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

### Constraints e índices

- index: `plate`
- index: `brand`
- index: `model`
- index: `is_active`

### Observações

- Veículo não terá vínculo direto com cliente
- O vínculo entre cliente e veículo será feito pela ordem de serviço
- Isso permite que o mesmo veículo possa estar relacionado a clientes diferentes ao longo do tempo

---

## Tabela: `work_orders`

Ordens de serviço da oficina.

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

### Constraints e índices

- foreign key: `customer_id` → `customers.id`
- foreign key: `vehicle_id` → `vehicles.id`
- foreign key: `assigned_user_id` → `users.id`
- unique: `order_number`
- index: `customer_id`
- index: `vehicle_id`
- index: `assigned_user_id`
- index: `status`
- index: `entry_date`

### Observações

- Não haverá soft delete
- A exclusão da OS não deve ser um fluxo comum
- O ideal é trabalhar com mudança de status

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

Checklist vinculado a uma ordem de serviço.

### Campos

- `id`
- `work_order_id`
- `title`
- `status`
- `notes`
- `created_at`
- `updated_at`

### Constraints e índices

- foreign key: `work_order_id` → `work_orders.id`
- index: `work_order_id`
- index: `status`

### Observações

- Não haverá soft delete
- Estrutura mantida simples para permitir crescimento futuro

### Status sugeridos

- `pending`
- `completed`

---

## Tabela: `checklist_items`

Itens do checklist.

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

### Constraints e índices

- foreign key: `checklist_id` → `checklists.id`
- index: `checklist_id`
- index: `position`

### Observações

- Não haverá soft delete
- Itens poderão ser removidos definitivamente

---

# 3. Regras de Soft Delete

## Tabelas com soft delete

- `tenants`
- `roles`
- `users`
- `customers`
- `vehicles`

## Tabelas sem soft delete

- `work_orders`
- `checklists`
- `checklist_items`

### Justificativa

Soft delete será usado apenas onde faz sentido restaurar dados ou evitar exclusão acidental.

Não será usado em tabelas com comportamento mais operacional, rápido ou que não devam ser restauradas.

---

# 4. Relacionamentos

## Banco base

- `tenants` não possui relacionamento com outras tabelas neste MVP

## Banco do tenant

- `roles` hasMany `users`
- `users` belongsTo `roles`
- `work_orders` belongsTo `customers`
- `work_orders` belongsTo `vehicles`
- `work_orders` belongsTo `users` (`assigned_user_id`)
- `checklists` belongsTo `work_orders`
- `checklist_items` belongsTo `checklists`

---

# 5. Estrutura final aprovada para o MVP

## Banco base
- `tenants`

## Banco do tenant
- `roles`
- `users`
- `customers`
- `vehicles`
- `work_orders`
- `checklists`
- `checklist_items`

---

# 6. Pontos deixados para evolução futura

Os itens abaixo foram intencionalmente removidos do MVP para reduzir complexidade e aumentar a chance de entrega no prazo:

- planos
- assinaturas
- múltiplos domínios por tenant
- configuração de banco em tabela separada
- usuários globais
- roles globais
- permissions em banco
- auditoria avançada
- vínculo direto entre cliente e veículo
- histórico de dono do veículo
