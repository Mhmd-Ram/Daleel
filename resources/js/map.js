/**
 * Leaflet maps for event locations.
 *
 * Loaded only on the pages that need it (the event form and the event detail
 * page) via a script stack, so the rest of the site keeps the small bundle it
 * had after the motion cleanup.
 *
 * Two behaviours, chosen by which data attribute the container carries:
 * - [data-map-picker] lets an organizer place a pin and writes the result into
 *   the form's hidden latitude/longitude inputs.
 * - [data-map-view] draws a read-only pin for an event that already has one.
 *
 * Address search goes through our own /geocode endpoint rather than calling
 * Nominatim from the browser: their usage policy forbids client-side
 * type-ahead and requires an identifying User-Agent, which only the server can
 * set reliably. Search is therefore explicit, on submit, never as you type.
 */
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

// Leaflet resolves its default marker images by relative path, which breaks
// once Vite fingerprints them. Point it at the bundled URLs instead.
//
// Deleting `_getIconUrl` first is the part that matters. Icon.Default overrides
// it to PREPEND its own auto-detected image path to whatever `iconUrl` says, so
// handing it a resolved URL produced that URL twice over - ".../images/http://
// .../images/marker-icon.png" - which 404s, leaving a marker element with no
// picture in it. Removing the override falls back to Icon's plain accessor,
// which returns the option untouched.
delete L.Icon.Default.prototype._getIconUrl;

L.Icon.Default.mergeOptions({
    iconUrl: markerIcon,
    iconRetinaUrl: markerIcon2x,
    shadowUrl: markerShadow,
});

const OSM_TILES = 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
const OSM_ATTRIBUTION =
    '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';

// Centre of Libya, used when an event has no pin yet.
const LIBYA_CENTRE = [27.0, 17.0];
const LIBYA_ZOOM = 5;
const PLACED_ZOOM = 15;

const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function createMap(element, centre, zoom) {
    const map = L.map(element, {
        center: centre,
        zoom,
        scrollWheelZoom: false, // Otherwise the page cannot be scrolled past the map.
        zoomAnimation: !prefersReducedMotion,
        fadeAnimation: !prefersReducedMotion,
    });

    L.tileLayer(OSM_TILES, { attribution: OSM_ATTRIBUTION, maxZoom: 19 }).addTo(map);

    // The map is sized by CSS, which Leaflet cannot know about at construction
    // time; without this the tiles render into a zero-height box.
    requestAnimationFrame(() => map.invalidateSize());

    return map;
}

function setupPicker(element) {
    const latitudeInput = document.getElementById(element.dataset.mapLatInput);
    const longitudeInput = document.getElementById(element.dataset.mapLngInput);

    if (!latitudeInput || !longitudeInput) {
        return;
    }

    const existing =
        latitudeInput.value !== '' && longitudeInput.value !== ''
            ? [parseFloat(latitudeInput.value), parseFloat(longitudeInput.value)]
            : null;

    const map = createMap(element, existing ?? LIBYA_CENTRE, existing ? PLACED_ZOOM : LIBYA_ZOOM);
    let marker = existing ? L.marker(existing).addTo(map) : null;

    const place = (latlng, zoom) => {
        if (marker) {
            marker.setLatLng(latlng);
        } else {
            marker = L.marker(latlng).addTo(map);
        }

        latitudeInput.value = latlng.lat.toFixed(7);
        longitudeInput.value = latlng.lng.toFixed(7);
        map.setView(latlng, zoom ?? map.getZoom());
        element.dispatchEvent(new CustomEvent('map:placed', { bubbles: true }));
    };

    map.on('click', (event) => place(event.latlng));

    const clearButton = document.querySelector(`[data-map-clear="${element.id}"]`);
    clearButton?.addEventListener('click', () => {
        if (marker) {
            map.removeLayer(marker);
            marker = null;
        }
        latitudeInput.value = '';
        longitudeInput.value = '';
        element.dispatchEvent(new CustomEvent('map:cleared', { bubbles: true }));
    });

    setupSearch(element, place);
}

function setupSearch(element, place) {
    const searchInput = document.querySelector(`[data-map-search="${element.id}"]`);
    const searchButton = document.querySelector(`[data-map-search-go="${element.id}"]`);
    const status = document.querySelector(`[data-map-search-status="${element.id}"]`);

    if (!searchInput || !searchButton) {
        return;
    }

    const say = (message) => {
        if (status) {
            status.textContent = message;
        }
    };

    let inFlight = false;

    const search = async () => {
        const query = searchInput.value.trim();

        if (query === '' || inFlight) {
            return;
        }

        inFlight = true;
        searchButton.disabled = true;
        say('Searching...');

        try {
            const response = await fetch(
                `/geocode?q=${encodeURIComponent(query)}`,
                { headers: { Accept: 'application/json' } },
            );

            if (!response.ok) {
                say(
                    response.status === 429
                        ? 'Too many searches just now. Wait a moment and try again.'
                        : 'Address search is unavailable. Click the map to place the pin instead.',
                );
                return;
            }

            const results = await response.json();

            if (results.length === 0) {
                say('No match. Try a broader search, or click the map to place the pin.');
                return;
            }

            const [best] = results;
            place(L.latLng(best.latitude, best.longitude), PLACED_ZOOM);
            say(best.label);
        } catch {
            say('Address search is unavailable. Click the map to place the pin instead.');
        } finally {
            inFlight = false;
            searchButton.disabled = false;
        }
    };

    searchButton.addEventListener('click', search);

    // Enter searches rather than submitting the whole event form.
    searchInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            search();
        }
    });
}

function setupView(element) {
    const latitude = parseFloat(element.dataset.mapLat);
    const longitude = parseFloat(element.dataset.mapLng);

    if (Number.isNaN(latitude) || Number.isNaN(longitude)) {
        return;
    }

    const map = createMap(element, [latitude, longitude], PLACED_ZOOM);
    const marker = L.marker([latitude, longitude]).addTo(map);

    if (element.dataset.mapLabel) {
        marker.bindPopup(element.dataset.mapLabel);
    }
}

document.querySelectorAll('[data-map-picker]').forEach(setupPicker);
document.querySelectorAll('[data-map-view]').forEach(setupView);
