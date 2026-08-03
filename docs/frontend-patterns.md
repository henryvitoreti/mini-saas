# 🎨 Padrões do Frontend

## ⚙️ Stack

- Nuxt 4
- Vue 3 (Composition API)
- TypeScript
- SCSS
- Bootstrap 5

---

## 🧱 Estrutura

```
frontend/
├── app/
│   ├── app.vue
│   ├── pages/
│   ├── layouts/
│   ├── components/
│   ├── composables/
│   ├── middleware/
│   ├── assets/
│   │   └── scss/
│   ├── types/
│   ├── constants/
│   ├── utils/
│   ├── services/
│   └── stores/
├── public/
├── scripts/
├── server/
├── nuxt.config.ts
```

---

## 🧠 Princípios

- Reutilização
- Separação de responsabilidades
- UI limpa
- Baixo acoplamento

---

## 📦 Components

### Base
- Button
- Input
- Select
- Card
- Badge
- Modal
- Toast
- Table

---

## 🔄 Estado

- `useState` do Nuxt para estado global compartilhado
- Composables para encapsular estado e comportamento reutilizáveis

---

## 🌐 API

- Client HTTP centralizado
- Interceptors
- Tratamento de erro
- Token JWT

---

## 🔐 Autenticação

- Baseada em JWT
- Middleware para rotas protegidas

---

## 🧭 Navegação

- Sidebar + Header
- Rotas dinâmicas por permissão

---

## 🎨 Estilo

- Bootstrap 5 como base
- SCSS customizado
- Design consistente

---

## 📦 Services

- AuthService
- CustomerService
- PermissionService
- RoleService
- TenantService
- ProductCategoryService
- ProductService
- ServiceService
- SaleOrderService
- WorkOrderService
- StockTransactionService

---

## ⚡ Composables

- lógica reutilizável
- comportamento reativo

---

## 🚫 Evitar

- lógica de negócio em componentes
- chamadas duplicadas de API
- CSS desnecessário
