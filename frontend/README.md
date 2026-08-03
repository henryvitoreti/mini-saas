# Frontend

Interface Nuxt da plataforma. Ela apresenta os módulos disponíveis para o tenant atual, consome a API Laravel e aplica as permissões recebidas na navegação. Para instalar dependências e iniciar os containers, consulte o [README da raiz](../README.md).

## Stack

- Nuxt 4
- Vue 3 com Composition API
- TypeScript
- Bootstrap 5 e SCSS
- ofetch/Nuxt `$fetch` por meio do client HTTP centralizado

## Estrutura principal

```text
app/
├── assets/scss/          # estilos, tokens e responsividade
├── components/           # componentes de domínio, formulário, layout e UI
├── composables/          # estado e comportamento reutilizável
│   └── forms/            # composables de formulário
├── config/               # configuração de menu, logos e aplicação
├── layouts/              # estruturas visuais compartilhadas
├── middleware/           # proteção e redirecionamento de rotas
├── pages/                # rotas baseadas em arquivos
├── services/             # contratos de comunicação com a API
│   └── api/              # client HTTP e handlers globais
├── stores/               # reservado para stores de domínio, se necessários
├── types/                # contratos TypeScript de API, entidades e UI
└── utils/                # formatadores e funções puras reutilizáveis
```

| Elemento | Responsabilidade |
| --- | --- |
| Pages | Compõem a tela e conectam componentes, composables e Services. |
| Components | Encapsulam UI reutilizável e componentes específicos de domínio. |
| Composables | Reúnem estado reativo e comportamento compartilhado, como autenticação e formulários. |
| Services | Centralizam chamadas e adaptações de resposta de cada domínio da API. |
| Stores | Reservado para estado de domínio persistente quando necessário. Atualmente não há store implementada: o estado global usa `useState` do Nuxt dentro de composables. |
| Types | Definem contratos de entidades, payloads, respostas e componentes. |

## Comunicação com a API

`services/api/http-client.ts` é o único client HTTP da aplicação. Ele resolve a URL da API para o subdomínio do tenant, inclui o token Bearer, controla carregamento global e converte respostas `422` em `ApiValidationError`.

Crie ou use um Service de domínio — por exemplo, `CustomerService`, `RoleService`, `TenantService`, `PermissionService` ou `AuthService` — para consumir endpoints. Páginas e componentes de domínio não devem chamar `apiHttpClient` diretamente quando já existir um Service para aquele recurso.

`AppDataTable` é uma exceção intencional: como componente genérico de listagem, recebe `baseApiUrl` e realiza internamente as requisições de tabela, filtros, paginação, exportação e ações CRUD.

## Componentes e tabelas

Os componentes base ficam em `components/ui` e `components/form`, incluindo entradas de texto, select, data, switch, checkbox, dialog, toast e loading global.

Use `AppDataTable` em listagens CRUD. Informe, no mínimo, `columns`, `base-url`, `base-api-url`, os rótulos da entidade e uma chave de armazenamento quando a tela precisar persistir filtros e limite. O componente já oferece busca, filtros por slot, paginação, responsividade, ações e exportação.

## Formulários e erros de validação

Formulários de domínio devem usar um composable em `composables/forms`, construído sobre `useBaseForm`.

Cada `attribute` define o estado e o contrato de um campo:

```ts
{
  responseKey: 'name',
  payloadKey: 'name',
  value: null,
  label: 'Nome',
  errorMessage: null,
}
```

- `responseKey` preenche o campo a partir da resposta da API.
- `payloadKey` determina a chave enviada, inclusive em estruturas aninhadas.
- `errorMessage` recebe a mensagem de validação exibida pelo componente de campo.

Em respostas `422`, o client cria `ApiValidationError` e `useBaseForm.handleError()` associa os erros aos attributes pelo `payloadKey`. Outros erros são tratados com toast e uma mensagem contextual do composable.

## Rotas, responsividade e tema

O Nuxt gera rotas a partir de `app/pages`. Por exemplo, `pages/clientes/index.vue` gera `/clientes`, `pages/clientes/criar.vue` gera `/clientes/criar` e `pages/clientes/[id]/editar.vue` gera a rota dinâmica de edição. O middleware global de autenticação redireciona usuários sem token válido para `/login`.

Utilize os componentes de layout e as classes SCSS existentes antes de criar CSS local. A interface é responsiva com Bootstrap e breakpoints próprios para formulários, tabelas, header e sidebar.

Os temas claro e escuro são controlados pelo atributo `data-theme` no documento. A preferência é mantida no navegador, e os componentes devem usar as variáveis CSS da aplicação para funcionar nos dois temas.

## Convenções

- Prefira Composition API com `script setup` e TypeScript.
- Mantenha a lógica de domínio fora das Pages; use composables e Services.
- Defina novos contratos em `types/` antes de espalhar tipos inline.
- Reutilize componentes base, tokens SCSS e padrões de formulário existentes.
- Mantenha URLs de frontend, API e permissões alinhadas com o backend ao adicionar novos módulos.

## Uso e direitos

Este é um projeto pessoal desenvolvido para portfólio. Não é um software aberto e não concede permissão para uso, cópia, modificação, distribuição ou comercialização, total ou parcial, sem autorização prévia e expressa do autor.
