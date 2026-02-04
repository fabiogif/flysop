/**
 * Mapa e autocomplete de endereço para formulário de ocorrências (ES6, sem jQuery).
 * Depende de Google Maps API com places: carregar com callback=initOccurrenceMap.
 */
const COMPONENT_FORM = [
  'location',
  'locality',
  'administrative_area_level_1',
  'country',
  'postal_code',
];

const ADDRESS_FORMAT = {
  street_number: 'short_name',
  route: 'long_name',
  locality: 'long_name',
  administrative_area_level_1: 'short_name',
  country: 'long_name',
  postal_code: 'short_name',
};

function getInput(id) {
  const el = document.getElementById(id ? `${id}-input` : null);
  return el || document.getElementById(id);
}

function getAddressComp(place, type) {
  if (!place.address_components) return '';
  for (const component of place.address_components) {
    if (component.types[0] === type) {
      return component[ADDRESS_FORMAT[type]] || '';
    }
  }
  return '';
}

function fillInAddress(place) {
  const locationInput = getInput('location');
  if (locationInput) {
    locationInput.value =
      `${getAddressComp(place, 'street_number')} ${getAddressComp(place, 'route')}`.trim();
  }
  for (const component of COMPONENT_FORM) {
    if (component === 'location') continue;
    const el = getInput(component);
    if (el) el.value = getAddressComp(place, component);
  }
}

export function initOccurrenceMap(config = {}) {
  const {
    mapId = 'map',
    center = { lat: -12.95307, lng: -38.49706 },
    zoom = 20,
  } = config;

  const mapEl = document.getElementById(mapId);
  if (!mapEl || typeof google === 'undefined' || !google.maps) return null;

  const map = new google.maps.Map(mapEl, {
    center,
    zoom,
    mapTypeControl: false,
    fullscreenControl: true,
    streetViewControl: true,
  });

  const marker = new google.maps.Marker({ map, draggable: false });
  const locationInput = getInput('location');
  if (!locationInput) return map;

  const autocomplete = new google.maps.places.Autocomplete(locationInput, {
    fields: ['address_components', 'geometry', 'name'],
    types: ['address'],
  });

  const latEl = document.getElementById('latitude');
  const lngEl = document.getElementById('longitude');

  autocomplete.addListener('place_changed', () => {
    marker.setVisible(false);
    const place = autocomplete.getPlace();
    if (!place.geometry) {
      if (place.name) window.alert(`Sem detalhes para: "${place.name}"`);
      return;
    }
    map.setCenter(place.geometry.location);
    marker.setPosition(place.geometry.location);
    marker.setVisible(true);
    fillInAddress(place);
    if (latEl) latEl.value = place.geometry.location.lat();
    if (lngEl) lngEl.value = place.geometry.location.lng();
  });

  return map;
}

/**
 * Para uso com callback da API do Google (script externo chama initMap).
 */
export function registerGlobalInit() {
  window.initOccurrenceMap = () => {
    initOccurrenceMap({
      mapId: 'map',
      center: { lat: -12.95307, lng: -38.49706 },
      zoom: 20,
    });
  };
  if (typeof window.initMap === 'undefined') {
    window.initMap = window.initOccurrenceMap;
  }
}
