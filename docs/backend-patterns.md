# ⚙️ Padrões do Backend

## 🧱 Arquitetura

Controller → Service → Repository

---

## 📦 Controllers

### Responsabilidades
- Receber requisição
- Validar dados
- Chamar Service
- Retornar resposta

### Regras
- Não conter regra de negócio
- Não acessar banco diretamente

---

## 🧠 Services

### Responsabilidades
- Regra de negócio
- Orquestração de fluxo
- Controle de transações

### Regras
- Pode lançar exceptions
- Pode usar múltiplos repositories
- Não deve retornar resposta HTTP

---

## 🗄️ Repositories

### Responsabilidades
- Acesso a dados
- Queries
- Persistência

### Métodos base
- find
- create
- update
- delete
- paginate
- updateOrCreate
- firstOrCreate
- search

---

## 🔁 Transações

Devem ser usadas em Services quando houver:

- múltiplas operações
- dependência entre ações

---

## 📥 FormRequest

- Validação de entrada
- Regras de campos

---

## 📤 API Resource

- Transformação de resposta
- Padronização de saída

---

## ❌ Exceptions

Usar exceptions semânticas:

- TenantNotFoundException
- WorkOrderNotFoundException
- BusinessRuleException

---

## 📡 Padrão de resposta

```
{
    "message": "...",
    "data": {},
    "error": {}
}
```

---

## 🧠 Convenções

- Classes: PascalCase
- Métodos: camelCase
- Constantes: UPPER_CASE
- Tabelas: snake_case plural
- Colunas: snake_case

---

## 🚫 Evitar

- lógica em controller
- model com regra de negócio
- lógica inline complexa
- respostas inconsistentes