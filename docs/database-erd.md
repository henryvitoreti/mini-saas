# ERD - Modelagem Inicial

Este documento separa a modelagem em dois contextos:

- **Banco base**: armazena informações globais da plataforma.
- **Banco do tenant**: armazena os dados operacionais de cada tenant.

O ERD descreve a estrutura planejada e não cria tabelas, migrations ou funcionalidades.

## Banco base

```mermaid
erDiagram
    TENANTS {
        varchar id PK
        boolean active
        json data
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    DOMAINS {
        int id PK
        varchar domain UK
        varchar tenant_id FK
        timestamp created_at
        timestamp updated_at
    }

    PERMISSIONS {
        bigint id PK
        varchar name
        varchar slug UK
        varchar base_front_url
        varchar base_api_url
        timestamp created_at
        timestamp updated_at
    }
    
    ROLES {
        bigint id PK
        varchar name
        varchar slug UK
        text description
        boolean is_active
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    PERMISSION_ROLE {
        bigint role_id FK
        bigint permission_id FK
        boolean show_locked_routes
        boolean is_active
    }

    TENANTS ||--o{ DOMAINS : "possui"
    ROLES ||--o{ PERMISSION_ROLE : "possui"
    PERMISSIONS ||--o{ PERMISSION_ROLE : "possui"
```

## Banco do tenant

```mermaid
erDiagram
    USERS {
        bigint id PK
        varchar name
        varchar email UK
        varchar password
        varchar phone
        boolean is_active
        timestamp last_login_at
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    CUSTOMERS {
        bigint id PK
        varchar name
        enum type
        varchar document
        varchar email
        varchar phone
        varchar secondary_phone
        varchar zip_code
        varchar street
        varchar number
        varchar complement
        varchar district
        varchar city
        varchar state
        text notes
        boolean is_active
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    PRODUCT_CATEGORIES {
        bigint id PK
        varchar name
        text description
        boolean is_active
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    PRODUCTS {
        bigint id PK
        bigint category_id FK
        varchar name
        varchar sku
        varchar barcode
        text description
        decimal cost_price
        decimal sale_price
        decimal stock_quantity
        boolean allow_negative_stock
        boolean is_active
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    SERVICES {
        bigint id PK
        varchar name
        text description
        decimal price
        boolean is_active
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    SALE_ORDERS {
        bigint id PK
        bigint customer_id FK
        bigint user_id FK
        decimal subtotal
        decimal discount
        decimal total
        text notes
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    SALE_ORDER_ITEMS {
        bigint id PK
        bigint sale_order_id FK
        bigint product_id FK
        decimal quantity
        decimal unit_price
        decimal discount
        decimal subtotal
        decimal total
        timestamp created_at
        timestamp updated_at
    }

    WORK_ORDERS {
        bigint id PK
        varchar order_number UK
        bigint customer_id FK
        bigint assigned_user_id FK
        enum status
        timestamp completed_at
        text customer_complaint
        text technical_diagnosis
        text general_notes
        decimal subtotal
        decimal discount
        decimal total
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    WORK_ORDER_PRODUCT_ITEMS {
        bigint id PK
        bigint work_order_id FK
        bigint product_id FK
        decimal quantity
        decimal unit_price
        decimal discount
        decimal subtotal
        decimal total
        timestamp created_at
        timestamp updated_at
    }

    WORK_ORDER_SERVICE_ITEMS {
        bigint id PK
        bigint work_order_id FK
        bigint service_id FK
        decimal quantity
        decimal unit_price
        decimal discount
        decimal subtotal
        decimal total
        timestamp created_at
        timestamp updated_at
    }

    STOCK_TRANSACTIONS {
        bigint id PK
        bigint product_id FK
        bigint user_id FK
        bigint sale_order_id FK
        bigint work_order_id FK
        enum type
        decimal quantity
        decimal previous_quantity
        decimal current_quantity
        decimal unit_cost
        text notes
        timestamp created_at
    }

    COMPANY {
        int id PK
        bigint role_id FK
        varchar name
        varchar document
        varchar email
        varchar phone
        varchar secondary_phone
        varchar zip_code
        varchar street
        varchar number
        varchar complement
        varchar district
        varchar city
        varchar state
        varchar logo_path
        text notes
        timestamp created_at
        timestamp updated_at
    }

    PRODUCT_CATEGORIES ||--o{ PRODUCTS : "possui"
    CUSTOMERS ||--o{ SALE_ORDERS : "possui"
    USERS ||--o{ SALE_ORDERS : "registra"
    SALE_ORDERS ||--o{ SALE_ORDER_ITEMS : "possui"
    PRODUCTS ||--o{ SALE_ORDER_ITEMS : "integra"
    CUSTOMERS ||--o{ WORK_ORDERS : "possui"
    USERS ||--o{ WORK_ORDERS : "responsavel"
    WORK_ORDERS ||--o{ WORK_ORDER_PRODUCT_ITEMS : "possui"
    PRODUCTS ||--o{ WORK_ORDER_PRODUCT_ITEMS : "integra"
    WORK_ORDERS ||--o{ WORK_ORDER_SERVICE_ITEMS : "possui"
    SERVICES ||--o{ WORK_ORDER_SERVICE_ITEMS : "integra"
    PRODUCTS ||--o{ STOCK_TRANSACTIONS : "movimenta"
    USERS ||--o{ STOCK_TRANSACTIONS : "registra"
    SALE_ORDERS ||--o{ STOCK_TRANSACTIONS : "origina"
    WORK_ORDERS ||--o{ STOCK_TRANSACTIONS : "origina"
```
