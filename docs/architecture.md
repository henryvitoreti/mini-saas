# 🏗️ Visão Geral da Arquitetura

## 🎯 Objetivo

Este projeto é um Mini-SaaS multi-tenant voltado para a gestão operacional de empresas, com foco em:

- Arquitetura limpa (Clean Architecture)
- Escalabilidade
- Organização de código
- Qualidade profissional para portfólio

---

## 🧱 Arquitetura em Camadas

O sistema segue o padrão:

Controller → Service → Repository → Model (Banco de Dados)

### Responsabilidades

#### Controller
- Recebe a requisição HTTP
- Valida entrada (via FormRequest)
- Chama a Service
- Retorna resposta padronizada

#### Service
- Contém a regra de negócio
- Orquestra o fluxo da aplicação
- Controla transações

#### Repository
- Responsável pelo acesso ao banco
- Encapsula queries
- Centraliza persistência

#### Model
- Representa entidades
- Define relacionamentos
- Define casts

---

## 🧠 Princípios adotados

- Separação de responsabilidades
- Evitar controllers gordos
- Evitar lógica de negócio em models
- Código legível e explícito
- Reutilização de componentes
- Padronização

---

## ⚙️ Stack

### Backend
- Laravel 12
- PHP 8.3+
- PostgreSQL
- Redis

### Frontend
- Nuxt 4
- Vue 3 (Composition API)
- TypeScript
- SCSS
- Bootstrap 5

### Infraestrutura
- Docker
- Preparado para AWS / CI/CD

---

## 📦 Estrutura do Monorepo

```
saas/
├── backend/
├── frontend/
├── docker/
├── scripts/
├── docs/
├── .env.example
├── docker-compose.yml
└── README.md
```

---

## 🎯 Objetivos da Arquitetura

- Suporte a multi-tenancy
- Escalabilidade
- Facilidade de manutenção
- Estrutura de produto real
