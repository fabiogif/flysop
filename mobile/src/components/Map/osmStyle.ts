// Tiles OpenStreetMap sem API key. ponytail: tile.openstreetmap.org não é para tráfego de produção
// alto (tile usage policy); trocar por provedor próprio/pago (MapTiler, Stadia) ao escalar.
export const OSM_STYLE = {
  version: 8,
  sources: {
    osm: {
      type: 'raster',
      tiles: ['https://tile.openstreetmap.org/{z}/{x}/{y}.png'],
      tileSize: 256,
      attribution: '© OpenStreetMap contributors',
    },
  },
  layers: [{ id: 'osm', type: 'raster', source: 'osm' }],
};

export const DEFAULT_CENTER = { latitude: -12.9714, longitude: -38.5014 };

export interface Coordinates {
  latitude: number;
  longitude: number;
}
