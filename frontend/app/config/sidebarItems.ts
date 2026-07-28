import type { AuthenticatedPermissions } from '@/types/auth';

export type SidebarItem = {
  key: string;
  label: string;
  icon?: string;
  to?: string;
  allowed: boolean;
  show: boolean;
  onlyAdmin?: boolean;
  opened?: boolean;
  children?: SidebarItem[];
};

type SidebarItemDefinition = Omit<SidebarItem, 'allowed'|'show'|'children'> & {
  permissionBaseFrontUrl?: string;
  children?: SidebarItemDefinition[];
};

const sidebarItemDefinitions: SidebarItemDefinition[] = [
  {
    key: 'dashboard',
    label: 'Início',
    icon: 'fa-solid fa-gauge-high',
    to: '/',
  },
  {
    key: 'registrations',
    label: 'Cadastros',
    icon: 'fa-solid fa-warehouse',
    children: [
      {
        key: 'customers',
        label: 'Clientes',
        icon: 'fa-solid fa-users',
        to: '/clientes',
        permissionBaseFrontUrl: 'clientes',
      },
      {
        key: 'vehicles',
        label: 'Veículos',
        icon: 'fa-solid fa-car',
        to: '/veiculos',
        permissionBaseFrontUrl: 'veiculos',
      },
    ],
  },
  {
    key: 'operational',
    label: 'Operacional',
    icon: 'fa-solid fa-screwdriver-wrench',
    children: [
      {
        key: 'work-orders',
        label: 'Ordens de Serviço',
        icon: 'fa-solid fa-file-lines',
        to: '/ordens-servico',
        permissionBaseFrontUrl: 'ordens-servico',
      },
      {
        key: 'checklists',
        label: 'Checklists',
        icon: 'fa-solid fa-clipboard-check',
        children: [
          {
            key: 'model-checklist',
            label: 'Modelos',
            to: '/checklists/modelos',
            permissionBaseFrontUrl: 'checklists/modelos',
          },
          {
            key: 'history-checklist',
            label: 'Histórico',
            to: '/checklists/historico',
            permissionBaseFrontUrl: 'checklists/historico',
          },
        ],
      },
    ],
  },
  {
    key: 'reports',
    label: 'Relatórios',
    icon: 'fa-solid fa-chart-line',
    children: [
      {
        key: 'report-work-orders',
        label: 'OS por status',
        icon: 'fa-solid fa-circle-info',
        to: '/relatorios/os',
        permissionBaseFrontUrl: 'relatorios/os',
      },
      {
        key: 'report-produtivity',
        label: 'Produtividade',
        icon: 'fa-solid fa-chart-line',
        to: '/relatorios/produtividade',
        permissionBaseFrontUrl: 'relatorios/produtividade',
      },
    ],
  },
  {
    key: 'admin',
    label: 'Administrativo',
    icon: 'fa-solid fa-user',
    onlyAdmin: true,
    children: [
      {
        key: 'domains',
        label: 'Domínio',
        icon: 'fa-solid fa-server',
        to: '/dominios',
      },
    ],
  },
  {
    key: 'configuracoes',
    label: 'Configurações',
    icon: 'fa-solid fa-gear',
    to: '/configuracoes',
    permissionBaseFrontUrl: 'configuracoes',
  },
];

function normalizeBaseFrontUrl(baseFrontUrl: string): string {
  return baseFrontUrl.replace(/^\/+|\/+$/g, '');
}

function getPermissionState(
  permissionBaseFrontUrl: string,
  permissions: AuthenticatedPermissions,
): Pick<SidebarItem, 'allowed'|'show'> {
  const normalizedBaseFrontUrl = normalizeBaseFrontUrl(permissionBaseFrontUrl);
  const permission = permissions.find(({ base_front_url }) => (
    normalizeBaseFrontUrl(base_front_url) === normalizedBaseFrontUrl
  ));

  if (permission?.is_active) {
    return { allowed: true, show: true };
  }

  return {
    allowed: false,
    show: permission?.show_locked_routes ?? false,
  };
}

function buildSidebarItem(
  definition: SidebarItemDefinition,
  permissions: AuthenticatedPermissions,
): SidebarItem {
  const {
    children: childDefinitions,
    permissionBaseFrontUrl,
    ...item
  } = definition;
  const children = childDefinitions?.map((child) => buildSidebarItem(child, permissions));

  if (permissionBaseFrontUrl) {
    return {
      ...item,
      ...getPermissionState(permissionBaseFrontUrl, permissions),
      children,
    };
  }

  if (children) {
    const hasVisibleChild = children.some((child) => child.show);

    return {
      ...item,
      allowed: hasVisibleChild,
      show: hasVisibleChild,
      children,
    };
  }

  return {
    ...item,
    allowed: true,
    show: true,
  };
}

export function createSidebarItems(permissions: AuthenticatedPermissions): SidebarItem[] {
  return sidebarItemDefinitions.map((definition) => buildSidebarItem(definition, permissions));
}
