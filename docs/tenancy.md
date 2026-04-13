# 🏢 Estratégia de Multi-Tenancy

## 🎯 Visão Geral

O sistema utiliza arquitetura multi-tenant com **isolamento por banco de dados**.

Cada tenant possui seu próprio banco.

---

## 🧱 Estrutura

### Banco Landlord (global)

Responsável por dados do sistema SaaS.

#### Tabelas
- tenants
- tenant_domains
- plans
- subscriptions
- tenant_database_configs
- usuários globais (admin/suporte)

---

### Banco do Tenant

Cada tenant possui seu banco isolado.

#### Tabelas
- users
- roles
- customers
- vehicles
- work_orders
- checklists
- checklist_items

---

## 🌐 Resolução do Tenant

Baseado em subdomínio.

### Exemplo

oficina1.app.com → tenant A  
oficina2.app.com → tenant B

---

## 🔄 Fluxo da Requisição

1. Requisição chega
2. Subdomínio é identificado
3. Tenant é buscado no landlord
4. Conexão dinâmica é configurada
5. Contexto do tenant é definido
6. Requisição segue usando o banco do tenant

---

## ⚙️ Componentes principais

- TenantResolver
- TenantContext
- TenantManager
- DatabaseConnectionManager
- TenantMiddleware

---

## 🏗️ Criação de Tenant

Fluxo:

1. Criar tenant no landlord
2. Criar banco do tenant
3. Rodar migrations
4. Rodar seed inicial
5. Criar usuário admin

---

## 🔐 Isolamento

- Cada tenant possui banco próprio
- Nenhum dado é compartilhado
- Segurança garantida na conexão

---

## 🎯 Benefícios

- Isolamento real
- Escalabilidade
- Backup individual
- Arquitetura SaaS profissional