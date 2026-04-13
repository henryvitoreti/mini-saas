# 🔐 Sistema de Permissões

## 🎯 Visão Geral

As permissões são **fixas em código**, não armazenadas no banco dos tenants.

---

## 🧠 Estrutura

Formato:

module.action

### Exemplos

- dashboard.read
- customers.read
- customers.create
- vehicles.update
- work_orders.change_status

---

## 🧱 Origem

Definidas em:
- enum
- config
- classe central

---

## 🏢 Tipos

### Global (landlord)
- admin
- suporte

---

### Interno (tenant)
- workshop_admin
- manager
- mechanic
- attendant

---

## 🧑‍💼 Roles

No banco do tenant:
- users
- roles
- role_user

---

## 🧭 Integração com frontend

Cada rota define:
- permission necessária

---

## 🎯 Comportamentos

### Hidden
rota não aparece

### Disabled
rota aparece bloqueada

### Futuro
solicitação de permissão

---

## ⚙️ Configuração da Role

Pode definir:
- mostrar rotas bloqueadas
- permitir solicitação futura

---

## 🔄 Backend

Middleware valida:
- role do usuário
- permission necessária

---

## 🚫 Evitar

- duplicar permissões por tenant
- atrelar permissão à URL diretamente
- lógica espalhada

---

## 🎯 Objetivo

Criar um sistema:

- consistente
- escalável
- integrado com frontend
- preparado para evolução futura