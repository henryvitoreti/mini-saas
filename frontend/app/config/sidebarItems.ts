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

export const sidebarItems: SidebarItem[] = [
    {
        key: 'dashboard',
        label: 'Início',
        icon: 'fa-solid fa-gauge-high',
        to: '/',
        allowed: true,
        show: true,
    },
    {
        key: 'registrations',
        label: 'Cadastros',
        icon: 'fa-solid fa-warehouse',
        allowed: true,
        show: true,
        children: [
            {
                key: 'customers',
                label: 'Clientes',
                icon: 'fa-solid fa-users',
                to: '/clientes',
                allowed: true,
                show: true,
            },
            {
                key: 'vehicles',
                label: 'Veículos',
                icon: 'fa-solid fa-car',
                to: '/veiculos',
                allowed: false,
                show: true,
            },
        ],
    },
    {
        key: 'operational',
        label: 'Operacional',
        icon: 'fa-solid fa-screwdriver-wrench',
        allowed: false,
        show: true,
        children: [
            {
                key: 'work-orders',
                label: 'Ordens de Serviço',
                icon: 'fa-solid fa-file-lines',
                to: '/ordens-servico',
                allowed: false,
                show: true,
            },
            {
                key: 'checklists',
                label: 'Checklists',
                icon: 'fa-solid fa-clipboard-check',
                allowed: false,
                show: true,
                children: [
                    {
                        key: 'model-checklist',
                        label: 'Modelos',
                        to: '/checklists/modelos',
                        allowed: false,
                        show: true,
                    },
                    {
                        key: 'history-checklist',
                        label: 'Histórico',
                        to: '/checklists/historico',
                        allowed: false,
                        show: true,
                    },
                ],
            },
        ],
    },
    {
        key: 'reports',
        label: 'Relatórios',
        icon: 'fa-solid fa-chart-line',
        allowed: false,
        show: true,
        children: [
            {
                key: 'report-work-orders',
                label: 'OS por status',
                icon: 'fa-solid fa-circle-info',
                to: '/relatorios/os',
                allowed: false,
                show: true,
            },
            {
                key: 'report-produtivity',
                label: 'Produtividade',
                icon: 'fa-solid fa-chart-line',
                to: '/relatorios/produtividade',
                allowed: false,
                show: true,
            },
        ],
    },
    {
        key: 'admin',
        label: 'Administrativo',
        icon: 'fa-solid fa-user',
        allowed: true,
        show: true,
        onlyAdmin: true,
        children: [
            {
                key: 'domains',
                label: 'Domínio',
                icon: 'fa-solid fa-server',
                to: '/dominios',
                allowed: true,
                show: true,
            },
        ],
    },
    {
        key: 'configuracoes',
        label: 'Configurações',
        icon: 'fa-solid fa-gear',
        to: '/configuracoes',
        allowed: false,
        show: true,
    },
];
