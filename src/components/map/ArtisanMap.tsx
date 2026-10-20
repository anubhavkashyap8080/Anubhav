import React, { useEffect, useRef, useState } from 'react';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { usePlatform } from '../../context/PlatformContext';
import { Seller } from '../../types';
import { MapPin, Navigation, Phone, ExternalLink, ShieldCheck, Compass, Sliders } from 'lucide-react';
import { Link } from 'react-router-dom';

export const ArtisanMap: React.FC = () => {
  const { vendors, mapSettings } = usePlatform();
  const mapContainerRef = useRef<HTMLDivElement>(null);
  const mapInstanceRef = useRef<L.Map | null>(null);
  const markersRef = useRef<L.LayerGroup | null>(null);

  const [selectedVendor, setSelectedVendor] = useState<Seller | null>(null);
  const [userLocation, setUserLocation] = useState<{ lat: number; lng: number } | null>(null);
  const [radiusFilter, setRadiusFilter] = useState<number>(mapSettings.searchRadiusKm || 50);
  const [locating, setLocating] = useState(false);

  // Calculate distance in KM using Haversine formula
  const getDistanceKm = (lat1: number, lon1: number, lat2: number, lon2: number) => {
    const R = 6371; // Earth's radius in km
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a =
      Math.sin(dLat / 2) * Math.sin(dLat / 2) +
      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
      Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return Math.round(R * c);
  };

  // Filter vendors based on radius if user location is detected
  const filteredVendors = vendors.filter(v => {
    if (!v.coordinates) return true;
    if (!userLocation) return true;
    const dist = getDistanceKm(userLocation.lat, userLocation.lng, v.coordinates.lat, v.coordinates.lng);
    return dist <= radiusFilter;
  });

  // Initialize Leaflet Map
  useEffect(() => {
    if (!mapContainerRef.current) return;
    if (mapInstanceRef.current) return; // already initialized

    // Center on S S Industries Hardoi / Sandila by default
    const map = L.map(mapContainerRef.current, {
      center: [mapSettings.defaultCenter.lat, mapSettings.defaultCenter.lng],
      zoom: mapSettings.defaultZoom,
      zoomControl: true,
      attributionControl: true
    });

    // Free OpenStreetMap Tile Layer (no API key required)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors | S S Industries',
      maxZoom: 19,
    }).addTo(map);

    const layerGroup = L.layerGroup().addTo(map);
    mapInstanceRef.current = map;
    markersRef.current = layerGroup;

    return () => {
      map.remove();
      mapInstanceRef.current = null;
    };
  }, []);

  // Update Markers on vendor/filter change
  useEffect(() => {
    if (!mapInstanceRef.current || !markersRef.current) return;

    markersRef.current.clearLayers();

    // Custom Icon generator
    const createCustomIcon = (isSpecial: boolean) => L.divIcon({
      className: 'custom-map-marker',
      html: `
        <div style="
          background-color: ${isSpecial ? '#e65c00' : '#0c5a34'};
          color: white;
          width: 34px;
          height: 34px;
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          border: 3px solid white;
          box-shadow: 0 4px 10px rgba(0,0,0,0.3);
          font-size: 14px;
          font-weight: bold;
        ">
          ${isSpecial ? '🏭' : '📍'}
        </div>
      `,
      iconSize: [34, 34],
      iconAnchor: [17, 17],
      popupAnchor: [0, -18]
    });

    filteredVendors.forEach(vendor => {
      if (!vendor.coordinates) return;

      const isSSIndustries = vendor.id.includes('seller-1') || vendor.id.includes('seller-2');
      const marker = L.marker([vendor.coordinates.lat, vendor.coordinates.lng], {
        icon: createCustomIcon(isSSIndustries)
      });

      marker.bindPopup(`
        <div style="font-family: inherit; font-size: 12px; min-width: 180px; padding: 2px;">
          <strong style="color: #0c5a34; font-size: 13px; display: block; margin-bottom: 2px;">${vendor.name}</strong>
          <span style="color: #666; display: block; margin-bottom: 4px;">${vendor.location}</span>
          <span style="display: inline-block; background: #e8f5e9; color: #2e7d32; padding: 2px 6px; border-radius: 4px; font-weight: 600; font-size: 10px;">
            ${vendor.type} • ★ ${vendor.rating}
          </span>
          <div style="margin-top: 6px;">
            <a href="tel:${vendor.phone}" style="color: #e65c00; font-weight: bold; text-decoration: none;">📞 ${vendor.phone}</a>
          </div>
        </div>
      `);

      marker.on('click', () => {
        setSelectedVendor(vendor);
      });

      marker.addTo(markersRef.current!);
    });

    // If user location is detected, add user marker
    if (userLocation) {
      const userMarker = L.marker([userLocation.lat, userLocation.lng], {
        icon: L.divIcon({
          className: 'user-location-marker',
          html: `
            <div style="
              background-color: #2563eb;
              color: white;
              width: 32px;
              height: 32px;
              border-radius: 50%;
              display: flex;
              align-items: center;
              justify-content: center;
              border: 3px solid white;
              box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            ">
              🧭
            </div>
          `,
          iconSize: [32, 32],
          iconAnchor: [16, 16]
        })
      }).bindPopup('<strong>Your Current Location</strong>');
      userMarker.addTo(markersRef.current!);
    }
  }, [filteredVendors, userLocation]);

  // Geolocation detector
  const handleDetectLocation = () => {
    if (!navigator.geolocation) {
      alert('Geolocation is not supported by your browser.');
      return;
    }

    setLocating(true);
    navigator.geolocation.getCurrentPosition(
      (pos) => {
        const coords = { lat: pos.coords.latitude, lng: pos.coords.longitude };
        setUserLocation(coords);
        setLocating(false);
        if (mapInstanceRef.current) {
          mapInstanceRef.current.flyTo([coords.lat, coords.lng], 11);
        }
      },
      () => {
        // Fallback: Default to Lucknow / Sandila vicinity
        setUserLocation({ lat: 26.8467, lng: 80.9462 });
        setLocating(false);
        if (mapInstanceRef.current) {
          mapInstanceRef.current.flyTo([26.8467, 80.9462], 10);
        }
      }
    );
  };

  return (
    <div className="space-y-6">
      
      {/* Map Control Bar */}
      <div className="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <div className="flex items-center gap-3 w-full md:w-auto">
          <button
            onClick={handleDetectLocation}
            disabled={locating}
            className="px-4 py-2 bg-brand-green hover:bg-brand-green-dark text-white rounded-xl text-xs font-semibold flex items-center gap-2 shadow-sm transition-all"
          >
            <Compass className={`w-4 h-4 ${locating ? 'animate-spin' : ''}`} />
            <span>{locating ? 'Detecting Location...' : 'Find Artisans Near Me'}</span>
          </button>

          {userLocation && (
            <span className="text-[11px] text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">
              📍 Location active
            </span>
          )}
        </div>

        {/* Radius Filter */}
        <div className="flex items-center gap-3 w-full md:w-auto text-xs">
          <span className="font-semibold text-gray-700 flex items-center gap-1.5 shrink-0">
            <Sliders className="w-3.5 h-3.5 text-brand-orange" />
            <span>Distance Radius:</span>
          </span>
          <div className="flex gap-1.5 overflow-x-auto pb-1 md:pb-0">
            {[20, 50, 100, 500].map(r => (
              <button
                key={r}
                onClick={() => setRadiusFilter(r)}
                className={`px-3 py-1 rounded-lg text-xs font-medium transition-all ${
                  radiusFilter === r
                    ? 'bg-brand-orange text-white shadow-xs font-bold'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                }`}
              >
                {r === 500 ? 'All India' : `${r} km`}
              </button>
            ))}
          </div>
        </div>
      </div>

      {/* Map + Sidebar Layout */}
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        {/* Leaflet OpenStreetMap Container */}
        <div className="lg:col-span-8 rounded-3xl overflow-hidden shadow-lg border border-gray-200 h-[480px] relative z-10">
          <div ref={mapContainerRef} className="w-full h-full" />
        </div>

        {/* Vendors Directory Card List */}
        <div className="lg:col-span-4 space-y-4 max-h-[480px] overflow-y-auto pr-1">
          <h4 className="font-bold text-sm text-gray-900 font-display flex items-center justify-between">
            <span>Nearby Workshops ({filteredVendors.length})</span>
            <span className="text-[11px] text-brand-green font-semibold">Free OpenStreetMap</span>
          </h4>

          {filteredVendors.map(vendor => {
            const isSelected = selectedVendor?.id === vendor.id;
            const distance = userLocation && vendor.coordinates
              ? getDistanceKm(userLocation.lat, userLocation.lng, vendor.coordinates.lat, vendor.coordinates.lng)
              : null;

            return (
              <div
                key={vendor.id}
                onClick={() => {
                  setSelectedVendor(vendor);
                  if (mapInstanceRef.current && vendor.coordinates) {
                    mapInstanceRef.current.flyTo([vendor.coordinates.lat, vendor.coordinates.lng], 13);
                  }
                }}
                className={`p-4 rounded-2xl border transition-all cursor-pointer ${
                  isSelected
                    ? 'border-brand-green bg-emerald-50/70 shadow-md ring-2 ring-brand-green'
                    : 'border-gray-200 bg-white hover:border-emerald-300 hover:shadow-sm'
                }`}
              >
                <div className="flex items-start justify-between gap-2">
                  <div>
                    <h5 className="font-bold text-xs text-gray-900 line-clamp-1">
                      {vendor.name}
                    </h5>
                    <p className="text-[11px] text-gray-500 flex items-center gap-1 mt-0.5">
                      <MapPin className="w-3 h-3 text-brand-orange shrink-0" />
                      <span className="truncate">{vendor.location}</span>
                    </p>
                  </div>
                  {distance !== null && (
                    <span className="text-[10px] font-bold text-brand-orange bg-orange-50 px-2 py-0.5 rounded-full shrink-0">
                      ~{distance} km
                    </span>
                  )}
                </div>

                <div className="mt-3 flex items-center justify-between text-xs pt-2 border-t border-gray-100">
                  <a
                    href={`tel:${vendor.phone}`}
                    className="text-brand-green font-semibold hover:underline flex items-center gap-1 text-[11px]"
                    onClick={(e) => e.stopPropagation()}
                  >
                    <Phone className="w-3 h-3" />
                    <span>{vendor.phone}</span>
                  </a>
                  <Link
                    to={`/seller/${vendor.slug}`}
                    className="text-gray-600 hover:text-brand-green font-medium flex items-center gap-1 text-[11px]"
                    onClick={(e) => e.stopPropagation()}
                  >
                    <span>Store</span>
                    <ExternalLink className="w-3 h-3" />
                  </Link>
                </div>
              </div>
            );
          })}
        </div>

      </div>

    </div>
  );
};
