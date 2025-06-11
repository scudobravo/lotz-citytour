<template>
    <div class="relative w-full h-full">
        <div ref="mapContainer" class="w-full h-full"></div>
        <div v-if="selectedPoint" class="absolute top-4 right-4 bg-white p-4 rounded-lg shadow-lg max-w-sm">
            <div class="flex items-start gap-4">
                <img v-if="selectedPoint.image_path" :src="selectedPoint.image_path" :alt="selectedPoint.name" class="w-24 h-24 object-cover rounded">
                <div>
                    <h3 class="font-bold text-lg">{{ selectedPoint.name }}</h3>
                    <p class="text-gray-600">{{ selectedPoint.description }}</p>
                </div>
            </div>
            <button @click="selectedPoint = null" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import mapboxgl from 'mapbox-gl';
import 'mapbox-gl/dist/mapbox-gl.css';

const props = defineProps({
    points: {
        type: Array,
        required: true
    },
    center: {
        type: Object,
        required: true
    },
    zoom: {
        type: Number,
        default: 13
    }
});

const mapContainer = ref(null);
const map = ref(null);
const markers = ref([]);
const selectedPoint = ref(null);

onMounted(() => {
    mapboxgl.accessToken = 'pk.eyJ1IjoibG90emFwcCIsImEiOiJjbHRqZ2FqZ2owMDFtMmpxcGJqZ2owMDFtIn0.1234567890';
    
    map.value = new mapboxgl.Map({
        container: mapContainer.value,
        style: 'mapbox://styles/mapbox/streets-v12',
        center: [props.center.longitude, props.center.latitude],
        zoom: props.zoom
    });

    map.value.on('load', () => {
        addMarkers();
    });
});

const addMarkers = () => {
    // Rimuovi i marker esistenti
    markers.value.forEach(marker => marker.remove());
    markers.value = [];

    // Aggiungi i nuovi marker
    props.points.forEach(point => {
        const el = document.createElement('div');
        el.className = 'marker';
        el.style.width = '30px';
        el.style.height = '30px';
        el.style.backgroundImage = 'url(/images/marker.png)';
        el.style.backgroundSize = 'cover';
        el.style.cursor = 'pointer';

        const marker = new mapboxgl.Marker(el)
            .setLngLat([point.longitude, point.latitude])
            .addTo(map.value);

        el.addEventListener('click', () => {
            selectedPoint.value = point;
        });

        markers.value.push(marker);
    });
};

watch(() => props.points, () => {
    if (map.value) {
        addMarkers();
    }
}, { deep: true });

watch(() => props.center, (newCenter) => {
    if (map.value) {
        map.value.flyTo({
            center: [newCenter.longitude, newCenter.latitude],
            zoom: props.zoom,
            duration: 2000
        });
    }
});
</script>

<style scoped>
.marker {
    transition: transform 0.2s;
}

.marker:hover {
    transform: scale(1.1);
}
</style> 