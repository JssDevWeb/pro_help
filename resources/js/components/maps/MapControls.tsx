import { useState } from 'react';

interface MapControlsProps {
  onToggleHeatmap: () => void;
  onToggleClustering: () => void;
  showHeatmap: boolean;
  enableClustering: boolean;
  totalPoints: number;
}

/**
 * Componente de controles para el mapa que permite cambiar configuraciones de visualización
 */
const MapControls: React.FC<MapControlsProps> = ({
  onToggleHeatmap,
  onToggleClustering,
  showHeatmap,
  enableClustering,
  totalPoints
}) => {
  const [isExpanded, setIsExpanded] = useState(false);

  return (
    <div className="absolute top-4 right-4 z-1000 bg-white rounded-lg shadow-md">
      {/* Botón de toggle para expandir/contraer controles */}
      <div className="p-2">
        <button
          onClick={() => setIsExpanded(!isExpanded)}
          className="text-gray-600 hover:text-gray-800 transition-colors"
          title="Controles del mapa"
        >
          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4" />
          </svg>
        </button>
      </div>

      {/* Panel expandido de controles */}
      {isExpanded && (
        <div className="p-4 border-t">
          <h4 className="font-medium text-sm text-gray-900 mb-3">Opciones de visualización</h4>
          
          {/* Info del mapa */}
          <div className="text-xs text-gray-500 mb-3">
            {totalPoints} puntos en el mapa
          </div>
          
          {/* Control de mapa de calor */}
          <div className="mb-3">
            <label className="flex items-center">
              <input
                type="checkbox"
                checked={showHeatmap}
                onChange={onToggleHeatmap}
                className="mr-2 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              />
              <span className="text-sm">Mapa de calor</span>
            </label>
            <p className="text-xs text-gray-500 ml-6">
              Visualiza densidad de servicios
            </p>
          </div>
          
          {/* Control de clustering */}
          {!showHeatmap && (
            <div className="mb-3">
              <label className="flex items-center">
                <input
                  type="checkbox"
                  checked={enableClustering}
                  onChange={onToggleClustering}
                  className="mr-2 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                />
                <span className="text-sm">Agrupar marcadores</span>
              </label>
              <p className="text-xs text-gray-500 ml-6">
                Agrupa marcadores cercanos
              </p>
            </div>
          )}
          
          {/* Leyenda */}
          <div className="border-t pt-3">
            <h5 className="text-xs font-medium text-gray-700 mb-2">Leyenda</h5>
            <div className="space-y-1">
              <div className="flex items-center text-xs">
                <div className="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                <span>Organizaciones</span>
              </div>
              <div className="flex items-center text-xs">
                <div className="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                <span>Servicios</span>
              </div>
              {showHeatmap && (
                <div className="flex items-center text-xs">
                  <div className="w-3 h-3 bg-gradient-to-r from-blue-400 to-red-600 rounded mr-2"></div>
                  <span>Densidad</span>
                </div>
              )}
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default MapControls;
