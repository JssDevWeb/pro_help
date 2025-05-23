import { useState, useEffect } from 'react';
import { getServiceTypes } from '@/services/statsService';

interface FilterPanelProps {
  onFilterChange: (filters: FilterOptions) => void;
  initialFilters?: FilterOptions;
}

export interface FilterOptions {
  type: 'all' | 'organizations' | 'services' | 'nearby';
  serviceTypes: string[];
  radius: number;
  showHeatmap: boolean;
}

export default function FilterPanel({ onFilterChange, initialFilters = { type: 'all', serviceTypes: [], radius: 5, showHeatmap: false } }: FilterPanelProps) {
  const [filter, setFilter] = useState<FilterOptions>(initialFilters);
  const [availableServiceTypes, setAvailableServiceTypes] = useState<string[]>([]);
  const [isExpanded, setIsExpanded] = useState(false);
  
  useEffect(() => {
    const loadServiceTypes = async () => {
      try {
        const types = await getServiceTypes();
        setAvailableServiceTypes(types);
      } catch (error) {
        console.error('Error cargando tipos de servicios:', error);
      }
    };
    
    loadServiceTypes();
  }, []);
  
  const handleTypeChange = (type: 'all' | 'organizations' | 'services' | 'nearby') => {
    const newFilters = { ...filter, type };
    setFilter(newFilters);
    onFilterChange(newFilters);
  };
  
  const handleRadiusChange = (radius: number) => {
    const newFilters = { ...filter, radius };
    setFilter(newFilters);
    onFilterChange(newFilters);
  };
    const handleServiceTypeToggle = (serviceType: string) => {
    let newServiceTypes: string[];
    
    if (filter.serviceTypes.includes(serviceType)) {
      newServiceTypes = filter.serviceTypes.filter(type => type !== serviceType);
    } else {
      newServiceTypes = [...filter.serviceTypes, serviceType];
    }
    
    const newFilters = { ...filter, serviceTypes: newServiceTypes };
    setFilter(newFilters);
    onFilterChange(newFilters);
  };
  
  const handleHeatmapToggle = () => {
    const newFilters = { ...filter, showHeatmap: !filter.showHeatmap };
    setFilter(newFilters);
    onFilterChange(newFilters);
  };
  
  return (
    <div className="bg-white rounded-lg shadow-md p-4">
      <div className="flex justify-between items-center mb-4">
        <h3 className="font-semibold text-lg">Filtros</h3>
        <button 
          onClick={() => setIsExpanded(!isExpanded)}
          className="text-gray-600 hover:text-gray-800"
        >
          {isExpanded ? 'Menos filtros' : 'Más filtros'}
        </button>
      </div>
      
      <div className="flex flex-wrap gap-2 mb-4">
        <button
          onClick={() => handleTypeChange('all')}
          className={`px-3 py-1 rounded-md text-sm ${filter.type === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100'}`}
        >
          Todos
        </button>
        <button
          onClick={() => handleTypeChange('organizations')}
          className={`px-3 py-1 rounded-md text-sm ${filter.type === 'organizations' ? 'bg-blue-600 text-white' : 'bg-gray-100'}`}
        >
          Organizaciones
        </button>
        <button
          onClick={() => handleTypeChange('services')}
          className={`px-3 py-1 rounded-md text-sm ${filter.type === 'services' ? 'bg-blue-600 text-white' : 'bg-gray-100'}`}
        >
          Servicios
        </button>
        <button
          onClick={() => handleTypeChange('nearby')}
          className={`px-3 py-1 rounded-md text-sm ${filter.type === 'nearby' ? 'bg-blue-600 text-white' : 'bg-gray-100'}`}
        >
          Cercanos
        </button>
      </div>
      
      {(filter.type === 'nearby' || isExpanded) && (
        <div className="mb-4">
          <label htmlFor="radius" className="block text-sm font-medium text-gray-700 mb-1">
            Radio de búsqueda: {filter.radius} km
          </label>
          <input
            type="range"
            id="radius"
            name="radius"
            min="1"
            max="50"
            value={filter.radius}
            onChange={(e) => handleRadiusChange(parseInt(e.target.value))}
            className="w-full"
          />
          <div className="flex justify-between text-xs text-gray-500">
            <span>1 km</span>
            <span>25 km</span>
            <span>50 km</span>
          </div>
        </div>
      )}
      
      {(filter.type === 'services' || isExpanded) && availableServiceTypes.length > 0 && (
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Tipos de servicios
          </label>
          <div className="grid grid-cols-2 gap-2">
            {availableServiceTypes.map((type) => (
              <label key={type} className="flex items-center">
                <input
                  type="checkbox"
                  checked={filter.serviceTypes.includes(type)}
                  onChange={() => handleServiceTypeToggle(type)}
                  className="mr-2 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                />
                <span className="text-sm">{type}</span>
              </label>
            ))}          </div>
        </div>
      )}
      
      {/* Toggle para mapa de calor */}
      {isExpanded && (
        <div className="border-t pt-4">
          <label className="flex items-center">
            <input
              type="checkbox"
              checked={filter.showHeatmap}
              onChange={handleHeatmapToggle}
              className="mr-2 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            />
            <span className="text-sm font-medium">Mostrar mapa de calor</span>
          </label>
          <p className="text-xs text-gray-500 mt-1">
            Visualiza la densidad de servicios en el área
          </p>
        </div>
      )}
    </div>
  );
}
