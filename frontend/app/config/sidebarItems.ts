export type SidebarItem = {
    key: string;
    label: string;
    icon?: string;
    to?: string;
    allowed: boolean;
    show: boolean;
    opened?: boolean;
    children?: SidebarItem[];
};

export const sidebarItems: SidebarItem[] = [
    {
        key: 'dashboard',
        label: 'Dashboard',
        icon: 'fa-solid fa-gauge-high',
        to: '/',
        allowed: true,
        show: true,
    },
    {
        key: 'cadastros',
        label: 'Cadastros',
        icon: 'fa-solid fa-warehouse',
        allowed: true,
        show: true,
        children: [
            {
                key: 'clientes',
                label: 'Clientes',
                icon: 'fa-solid fa-users',
                to: '/clientes',
                allowed: true,
                show: true,
            },
            {
                key: 'veiculos',
                label: 'Veículos',
                icon: 'fa-solid fa-car',
                to: '/veiculos',
                allowed: false,
                show: true,
            },
        ],
    },
    {
        key: 'operacional',
        label: 'Operacional',
        icon: 'fa-solid fa-screwdriver-wrench',
        allowed: false,
        show: true,
        children: [
            {
                key: 'ordens-servico',
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
                        key: 'checklist-modelos',
                        label: 'Modelos',
                        to: '/checklists/modelos',
                        allowed: false,
                        show: true,
                    },
                    {
                        key: 'checklist-historico',
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
        key: 'relatorios',
        label: 'Relatórios',
        icon: 'fa-solid fa-chart-line',
        allowed: false,
        show: true,
        children: [
            {
                key: 'relatorio-os',
                label: 'OS por status',
                icon: 'fa-solid fa-circle-info',
                to: '/relatorios/os',
                allowed: false,
                show: true,
            },
            {
                key: 'relatorio-produtividade',
                label: 'Produtividade',
                icon: 'fa-solid fa-chart-line',
                to: '/relatorios/produtividade',
                allowed: false,
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
