<template>
  <div class="driver-tracker-container">
    <div ref="mapElement" :style="{ width: '100%', height: height + 'px' }"></div>
    <div v-if="driverInfo" class="mt-2 text-sm text-gray-600">
      Última atualização: {{ lastUpdate }}
    </div>
  </div>
</template>

<script>
import L from 'leaflet';

// Pinos SVG simples (ocorrência = vermelho, motorista = azul) — substitui os ícones
// estáticos do Google (mapfiles/ms/icons), removidos junto da migração para Leaflet/OSM
// (Google Maps passou a exigir billing habilitado, sem alternativa gratuita real).
function dotIcon(color) {
  const svg = '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22">' +
    '<circle cx="11" cy="11" r="9" fill="' + color + '" stroke="#fff" stroke-width="2"/></svg>';
  return L.icon({
    iconUrl: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
    iconSize: [22, 22],
    iconAnchor: [11, 11]
  });
}

export default {
  props: {
    occurrenceId: {
      type: [Number, String],
      required: true
    },
    occurrenceLat: {
      type: [Number, String],
      required: true
    },
    occurrenceLng: {
      type: [Number, String],
      required: true
    },
    height: {
      type: Number,
      default: 400
    },
    routeHistoryUrl: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      map: null,
      occurrenceMarker: null,
      driverMarker: null,
      polyline: null,
      routePoints: [],
      driverInfo: null,
      lastUpdatedAt: null
    };
  },
  computed: {
    lastUpdate() {
      if (!this.lastUpdatedAt) return 'Aguardando...';
      const date = new Date(this.lastUpdatedAt);
      return date.toLocaleTimeString();
    }
  },
  mounted() {
    this.initMap();
    this.loadRouteHistory().then(() => {
      this.listenForDriverPosition();
    });
  },
  beforeDestroy() {
    if (window.Echo) {
      window.Echo.private(`occurrence.${this.occurrenceId}`).stopListening('DriverPositionUpdated');
    }
  },
  methods: {
    initMap() {
      const occurrencePos = [parseFloat(this.occurrenceLat), parseFloat(this.occurrenceLng)];

      this.map = L.map(this.$refs.mapElement, { zoomControl: true }).setView(occurrencePos, 15);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>'
      }).addTo(this.map);

      // Marcador da ocorrência (destino)
      this.occurrenceMarker = L.marker(occurrencePos, { title: 'Local da Ocorrência', icon: dotIcon('#dc3545') }).addTo(this.map);

      // Rota (linha)
      this.polyline = L.polyline([], { color: '#0000FF', weight: 4 }).addTo(this.map);
    },

    // Carrega o trajeto já percorrido antes de assinar o canal ao vivo — sem isso a
    // polyline nascia vazia e só ganhava forma a partir do primeiro evento recebido
    // depois que a tela foi aberta (OccurrencesController::driverRoute() já existia
    // pronto no back-end, mas nenhum front o chamava).
    loadRouteHistory() {
      if (!this.routeHistoryUrl || typeof window.axios === 'undefined') {
        return Promise.resolve();
      }
      return window.axios.get(this.routeHistoryUrl)
        .then((response) => {
          const data = response.data || {};
          const route = Array.isArray(data.route) ? data.route : [];
          this.routePoints = route
            .filter((p) => p && p.lat != null && p.lng != null)
            .map((p) => [parseFloat(p.lat), parseFloat(p.lng)]);
          if (this.routePoints.length) {
            this.polyline.setLatLngs(this.routePoints);
          }
          if (data.last_position) {
            const pos = [parseFloat(data.last_position.lat), parseFloat(data.last_position.lng)];
            this.lastUpdatedAt = data.last_position.created_at;
            this.driverInfo = data.driver;
            this.driverMarker = L.marker(pos, { title: data.driver ? data.driver.name : 'Motorista', icon: dotIcon('#007bff') }).addTo(this.map);
            this.map.fitBounds(L.latLngBounds([this.occurrenceMarker.getLatLng(), this.driverMarker.getLatLng()]), { padding: [40, 40] });
          }
        })
        .catch((error) => {
          console.warn('Não foi possível carregar o histórico de rota.', error);
        });
    },

    listenForDriverPosition() {
      if (typeof window.Echo === 'undefined') {
        console.warn('Laravel Echo não está configurado.');
        return;
      }

      window.Echo.private(`occurrence.${this.occurrenceId}`)
        .listen('DriverPositionUpdated', (e) => {
          this.updateDriverLocation(e);
        });
    },

    updateDriverLocation(data) {
      const pos = [parseFloat(data.latitude), parseFloat(data.longitude)];
      this.lastUpdatedAt = data.created_at;
      this.driverInfo = data.driver;

      // Adiciona ponto na rota
      this.routePoints.push(pos);
      this.polyline.setLatLngs(this.routePoints);

      // Atualiza marcador
      if (!this.driverMarker) {
        this.driverMarker = L.marker(pos, { title: data.driver ? data.driver.name : 'Motorista', icon: dotIcon('#007bff') }).addTo(this.map);

        // Ajustar zoom para mostrar ambos os marcadores
        this.map.fitBounds(L.latLngBounds([this.occurrenceMarker.getLatLng(), this.driverMarker.getLatLng()]), { padding: [40, 40] });
      } else {
        this.driverMarker.setLatLng(pos);
      }
    }
  }
}
</script>

<style scoped>
.driver-tracker-container {
  border: 1px solid #ddd;
  border-radius: 4px;
  overflow: hidden;
}
</style>
