# ERD - Modelagem Inicial

Este documento separa a modelagem em dois contextos:

- **Banco base**: armazena informações globais da plataforma.
- **Banco do tenant**: armazena os dados operacionais de cada oficina.

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

    VEHICLES {
        bigint id PK
        varchar plate
        varchar brand
        varchar model
        varchar version
        int year_manufacture
        int year_model
        varchar color
        varchar fuel_type
        varchar transmission_type
        bigint odometer
        varchar chassis
        text notes
        boolean is_active
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    WORK_ORDERS {
        bigint id PK
        varchar order_number UK
        bigint customer_id FK
        bigint vehicle_id FK
        bigint assigned_user_id FK
        varchar status
        varchar priority
        date entry_date
        date expected_delivery_date
        timestamp completed_at
        bigint odometer
        text customer_complaint
        text technical_diagnosis
        text general_notes
        timestamp created_at
        timestamp updated_at
    }

    CHECKLISTS {
        bigint id PK
        bigint work_order_id FK
        varchar title
        varchar status
        text notes
        timestamp created_at
        timestamp updated_at
    }

    CHECKLIST_ITEMS {
        bigint id PK
        bigint checklist_id FK
        varchar label
        text description
        boolean is_required
        boolean is_checked
        timestamp checked_at
        int position
        timestamp created_at
        timestamp updated_at
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

    CUSTOMERS ||--o{ WORK_ORDERS : "possui"
    VEHICLES ||--o{ WORK_ORDERS : "possui"
    USERS ||--o{ WORK_ORDERS : "responsavel"
    WORK_ORDERS ||--o{ CHECKLISTS : "possui"
    CHECKLISTS ||--o{ CHECKLIST_ITEMS : "possui"
```