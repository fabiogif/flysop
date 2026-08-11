<template>
  <div class="driver-tracker-container">
    <div ref="mapElement" :style="{ width: '100%', height: height + 'px' }"></div>
    <div v-if="driverInfo" class="mt-2 text-sm text-gray-600">
      Última atualização: {{ lastUpdate }}
    </div>
  </div>
</template>

<script>
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
      const occurrencePos = { 
        lat: parseFloat(this.occurrenceLat), 
        lng: parseFloat(this.occurrenceLng) 
      };

      this.map = new google.maps.Map(this.$refs.mapElement, {
        center: occurrencePos,
        zoom: 15,
        mapTypeControl: false,
        streetViewControl: false
      });

      // Marcador da ocorrência (destino)
      this.occurrenceMarker = new google.maps.Marker({
        position: occurrencePos,
        map: this.map,
        title: 'Local da Ocorrência',
        icon: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png'
      });

      // Rota (linha)
      this.polyline = new google.maps.Polyline({
        path: this.routePoints,
        geodesic: true,
        strokeColor: '#0000FF',
        strokeOpacity: 1.0,
        strokeWeight: 4,
        map: this.map
      });
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
            .map((p) => ({ lat: parseFloat(p.lat), lng: parseFloat(p.lng) }));
          if (this.routePoints.length) {
            this.polyline.setPath(this.routePoints);
          }
          if (data.last_position) {
            const pos = { lat: parseFloat(data.last_position.lat), lng: parseFloat(data.last_position.lng) };
            this.lastUpdatedAt = data.last_position.created_at;
            this.driverInfo = data.driver;
            this.driverMarker = new google.maps.Marker({
              position: pos,
              map: this.map,
              title: data.driver ? data.driver.name : 'Motorista',
              icon: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png'
            });
            const bounds = new google.maps.LatLngBounds();
            bounds.extend(this.occurrenceMarker.getPosition());
            bounds.extend(this.driverMarker.getPosition());
            this.map.fitBounds(bounds);
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
      const pos = { lat: parseFloat(data.latitude), lng: parseFloat(data.longitude) };
      this.lastUpdatedAt = data.created_at;
      this.driverInfo = data.driver;

      // Adiciona ponto na rota
      this.routePoints.push(pos);
      this.polyline.setPath(this.routePoints);

      // Atualiza marcador
      if (!this.driverMarker) {
        this.driverMarker = new google.maps.Marker({
          position: pos,
          map: this.map,
          title: data.driver ? data.driver.name : 'Motorista',
          icon: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png'
        });
        
        // Ajustar zoom para mostrar ambos os marcadores
        const bounds = new google.maps.LatLngBounds();
        bounds.extend(this.occurrenceMarker.getPosition());
        bounds.extend(this.driverMarker.getPosition());
        this.map.fitBounds(bounds);
      } else {
        this.driverMarker.setPosition(pos);
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
