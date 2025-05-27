import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Card } from '@/components/ui/card';
import { Link } from '@inertiajs/react';
import { Suspense, lazy, useState, useEffect } from 'react';
import { getGeospatialStats, GeospatialStats } from '@/services/statsService';
import { getOrganizations } from '@/services/organizationService';
import { getServices } from '@/services/serviceService';
import NotificationCenter from '@/components/notifications/NotificationCenter';
import { useNotifications } from '@/hooks/useNotifications';

// Importación lazy para el mapa
const DashboardMap = lazy(() => import('@/components/maps/DashboardMap'));

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

export default function Dashboard() {
    const [stats, setStats] = useState<GeospatialStats>({
        totalOrganizations: 0,
        totalServices: 0,
        totalBeneficiaries: 0,
        serviceTypeDistribution: []
    });
    const [loading, setLoading] = useState<boolean>(true);

    // Hook para notificaciones
    const { unreadCount } = useNotifications({
        enableRealTime: true,
        pollInterval: 30000
    });

    useEffect(() => {
        const loadStats = async () => {
            try {
                setLoading(true);
                // Intentar obtener estadísticas de la API
                const statsData = await getGeospatialStats();
                setStats(statsData);
                setLoading(false);
            } catch (error) {
                console.error('Error loading stats:', error);
                // Si falla, intentar calcular manualmente
                try {
                    const [orgs, services] = await Promise.all([
                        getOrganizations(),
                        getServices()
                    ]);
                    setStats({
                        totalOrganizations: orgs.length,
                        totalServices: services.length,
                        totalBeneficiaries: services.length * 10, // Cálculo simple
                        serviceTypeDistribution: []
                    });
                } catch (err) {
                    console.error('Fallback stats failed too:', err);
                }
                setLoading(false);
            }
        };

        loadStats();
    }, []);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard - ShelterConnect" />
            
            <div className="space-y-6">
                {/* Header con notificaciones */}
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900">Dashboard ShelterConnect</h1>
                        <p className="text-gray-600">
                            Sistema de gestión de servicios sociales y albergues
                        </p>
                    </div>
                    <div className="flex items-center space-x-4">
                        <NotificationCenter />
                        {unreadCount > 0 && (
                            <div className="flex items-center space-x-2 text-sm text-blue-600">
                                <span>Tienes {unreadCount} notificaciones no leídas</span>
                            </div>
                        )}
                    </div>
                </div>

                {/* Estadísticas principales */}
                <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <Card className="p-6">
                        <div className="flex items-center">
                            <div className="flex-shrink-0">
                                <div className="p-3 bg-blue-100 rounded-md">
                                    <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                            </div>
                            <div className="ml-5 w-0 flex-1">
                                <dl>
                                    <dt className="text-sm font-medium text-gray-500 truncate">
                                        Organizaciones
                                    </dt>
                                    <dd className="text-lg font-medium text-gray-900">
                                        {loading ? '...' : stats.totalOrganizations}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </Card>
                    
                    <Card className="p-6">
                        <div className="flex items-center">
                            <div className="flex-shrink-0">
                                <div className="p-3 bg-green-100 rounded-md">
                                    <svg className="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                    </svg>
                                </div>
                            </div>
                            <div className="ml-5 w-0 flex-1">
                                <dl>
                                    <dt className="text-sm font-medium text-gray-500 truncate">
                                        Servicios
                                    </dt>
                                    <dd className="text-lg font-medium text-gray-900">
                                        {loading ? '...' : stats.totalServices}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </Card>
                    
                    <Card className="p-6">
                        <div className="flex items-center">
                            <div className="flex-shrink-0">
                                <div className="p-3 bg-amber-100 rounded-md">
                                    <svg className="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div className="ml-5 w-0 flex-1">
                                <dl>
                                    <dt className="text-sm font-medium text-gray-500 truncate">
                                        Beneficiarios
                                    </dt>
                                    <dd className="text-lg font-medium text-gray-900">
                                        {loading ? '...' : stats.totalBeneficiaries}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </Card>
                </div>
                
                <div className="grid gap-6 grid-cols-1 md:grid-cols-3">
                    {/* Mapa */}
                    <div className="md:col-span-2 bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">
                        <div className="p-6 border-b border-gray-200">
                            <div className="flex justify-between items-center">
                                <div>
                                    <h3 className="text-lg font-semibold text-gray-900">Mapa Interactivo</h3>
                                    <p className="text-sm text-gray-600">Ubicación de organizaciones y servicios</p>
                                </div>
                                <Link
                                    href="/geoview"
                                    className="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                >
                                    Ver Vista Completa
                                </Link>
                            </div>
                        </div>
                        <div className="p-6">
                            <Suspense fallback={
                                <div className="flex items-center justify-center h-64 bg-gray-50 rounded-lg">
                                    <div className="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-600"></div>
                                </div>
                            }>
                                <DashboardMap />
                            </Suspense>
                        </div>
                    </div>
                    
                    {/* Estadísticas */}
                    <div className="border-sidebar-border/70 dark:border-sidebar-border relative overflow-hidden rounded-xl border">
                        <div className="bg-white p-4 h-full">
                            <h2 className="text-xl font-semibold mb-4">Actividad Reciente</h2>
                            <div className="space-y-4">
                                <div className="p-3 border rounded-lg">
                                    <h3 className="font-medium">Nueva intervención</h3>
                                    <p className="text-sm text-gray-500">Atención médica básica</p>
                                    <p className="text-xs text-gray-400 mt-1">Hace 2 horas</p>
                                </div>
                                <div className="p-3 border rounded-lg">
                                    <h3 className="font-medium">Nuevo beneficiario</h3>
                                    <p className="text-sm text-gray-500">Juan García</p>
                                    <p className="text-xs text-gray-400 mt-1">Hace 5 horas</p>
                                </div>
                                <div className="p-3 border rounded-lg">
                                    <h3 className="font-medium">Servicio actualizado</h3>
                                    <p className="text-sm text-gray-500">Albergue temporal</p>
                                    <p className="text-xs text-gray-400 mt-1">Ayer</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
