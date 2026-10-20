import { Product } from '../types';

export const mockProducts: Product[] = [
  // 1. Farming & Agricultural Tools
  {
    id: 'prod-tool-01',
    code: 'GGH-TL-101',
    name: 'Hand-Forged Desi Hansiya (Tempered Grass & Paddy Sickle)',
    slug: 'hand-forged-desi-hansiya-sickle',
    category: 'Farming & Agricultural Tools',
    categorySlug: 'farming-traditional-tools',
    subCategory: 'Sickles & Harvesting',
    price: 249,
    originalPrice: 350,
    discount: 29,
    rating: 4.95,
    reviewCount: 142,
    image: 'https://images.unsplash.com/photo-1589923188900-85dae523342b?auto=format&fit=crop&w=600&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1589923188900-85dae523342b?auto=format&fit=crop&w=800&q=80',
      'https://images.unsplash.com/photo-1504917599217-d4dc5ebe6122?auto=format&fit=crop&w=800&q=80'
    ],
    seller: {
      id: 'seller-1',
      name: 'S S Industries Agro & Tool Works',
      slug: 'ss-industries-hardoi',
      type: 'Cottage Industry',
      location: 'Madhoganj Baghauli Road, Newada',
      district: 'Hardoi',
      state: 'Uttar Pradesh',
      verified: true,
      rating: 4.96,
      avatar: 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=150&q=80',
      phone: '+91 7897492100'
    },
    description: 'Manufactured from high-carbon reclaimed automotive leaf-spring steel at S S Industries Works in Hardoi. Double-beveled razor edge with oiled Sheesham wood handle riveted securely for harvesting fodder and paddy without dulling.',
    story: 'Forged under controlled temperature coal hearths in Hardoi, each blade receives traditional oil quenching giving Rockwell hardness 54-56 HRC.',
    attributes: {
      'Blade Material': 'High-Carbon Leaf Spring Steel',
      'Handle': 'Seasoned Sheesham Wood',
      'Blade Length': '10.5 inches',
      'Weight': '380 grams',
      'Origin': 'Hardoi, Uttar Pradesh',
      'Manufacturer': 'S S Industries'
    },
    stockStatus: 'In Stock',
    isBestSeller: true,
    isTool: true,
    tags: ['hansiya', 'sickle', 'harvesting', 'ss industries', 'hardoi', 'iron tool'],
    isDemoData: false
  },
  {
    id: 'prod-tool-02',
    code: 'GGH-TL-102',
    name: 'Hardened High-Carbon Steel Garden & Weeding Khurpi (खुरपी)',
    slug: 'hardened-steel-garden-khurpi-6inch',
    category: 'Farming & Agricultural Tools',
    categorySlug: 'farming-traditional-tools',
    subCategory: 'Weeding & Scraping',
    price: 189,
    originalPrice: 260,
    discount: 27,
    rating: 4.9,
    reviewCount: 98,
    image: 'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?auto=format&fit=crop&w=600&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?auto=format&fit=crop&w=800&q=80'
    ],
    seller: {
      id: 'seller-1',
      name: 'S S Industries Agro & Tool Works',
      slug: 'ss-industries-hardoi',
      type: 'Cottage Industry',
      location: 'Madhoganj Baghauli Road, Newada',
      district: 'Hardoi',
      state: 'Uttar Pradesh',
      verified: true,
      rating: 4.96,
      avatar: 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=150&q=80',
      phone: '+91 7897492100'
    },
    description: 'Forged straight weeding khurpi with sharp tapering tip designed for weed eradication, aerating vegetable garden beds, and scraping unwanted wild grasses. Hardened blade resists blunting in compacted soils.',
    story: 'Engineered at S S Industries Hardoi plant for both urban terrace gardening enthusiasts and extensive rural crop weeding.',
    attributes: {
      'Blade Width': '3 inches',
      'Blade Length': '6 inches',
      'Handle': 'Ergonomic Teak Wood Grip',
      'Thickness': '3.5 mm Heavy Gauge',
      'Origin': 'Hardoi, Uttar Pradesh'
    },
    stockStatus: 'In Stock',
    isBestSeller: true,
    isTool: true,
    tags: ['khurpi', 'weeding', 'garden tools', 'ss industries', 'trowel', 'hardoi'],
    isDemoData: false
  },
  {
    id: 'prod-tool-03',
    code: 'GGH-TL-103',
    name: 'Heavy Forged Iron Fawda / Fadawa Spade (फावड़ा / Belcha)',
    slug: 'heavy-forged-iron-fawda-spade',
    category: 'Farming & Agricultural Tools',
    categorySlug: 'farming-traditional-tools',
    subCategory: 'Digging Tools',
    price: 499,
    originalPrice: 650,
    discount: 23,
    rating: 4.92,
    reviewCount: 84,
    image: 'https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=600&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=800&q=80'
    ],
    seller: {
      id: 'seller-2',
      name: 'S S Industries Sandila Industrial Hub',
      slug: 'ss-industries-sandila',
      type: 'Cottage Industry',
      location: 'UPSIDA Sandila Industrial Area',
      district: 'Hardoi',
      state: 'Uttar Pradesh',
      verified: true,
      rating: 4.94,
      avatar: 'https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=150&q=80',
      phone: '+91 9005756321'
    },
    description: 'Solid forged iron earth-digging spade (fadawa/phawda) with reinforced eyelet collar for wooden handles. Ideal for farm furrow digging, canal irrigation preparation, field leveling, and clay excavation.',
    story: 'Heavy gauge steel hot-pressed at Sandila UPSIDA plant with anti-rust black protective oxide finish.',
    attributes: {
      'Weight': '1.75 kg',
      'Socket Diameter': '38 mm',
      'Finish': 'Tempered Black Anti-Corrosion',
      'Origin': 'Sandila, Hardoi'
    },
    stockStatus: 'In Stock',
    isBestSeller: true,
    isTool: true,
    tags: ['fawda', 'fadawa', 'spade', 'shovel', 'digging', 'sandila', 'ss industries'],
    isDemoData: false
  },
  {
    id: 'prod-tool-04',
    code: 'GGH-TL-104',
    name: 'Heavy Forged Iron Woodcutting Kulhadi (कुल्हाड़ी / Axe with Sal Handle)',
    slug: 'heavy-forged-iron-woodcutting-kulhadi-axe',
    category: 'Farming & Agricultural Tools',
    categorySlug: 'farming-traditional-tools',
    subCategory: 'Wood Chopping & Axes',
    price: 549,
    originalPrice: 750,
    discount: 27,
    rating: 4.96,
    reviewCount: 112,
    image: 'https://images.unsplash.com/photo-1508873696983-2df5293cb32f?auto=format&fit=crop&w=600&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1508873696983-2df5293cb32f?auto=format&fit=crop&w=800&q=80'
    ],
    seller: {
      id: 'seller-1',
      name: 'S S Industries Agro & Tool Works',
      slug: 'ss-industries-hardoi',
      type: 'Cottage Industry',
      location: 'Madhoganj Baghauli Road, Newada',
      district: 'Hardoi',
      state: 'Uttar Pradesh',
      verified: true,
      rating: 4.96,
      avatar: 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=150&q=80',
      phone: '+91 7897492100'
    },
    description: 'Forged from high-grade tempered rail/spring steel with razor-honed cutting wedge. Fitted with a heavy, oiled Sal wood handle wedged securely with steel shims for heavy wood chopping, timber pruning, and village fuel preparation.',
    story: 'Forged under hydraulic hammer and finished by hand on anvil hearths at S S Industries Madhoganj plant.',
    attributes: {
      'Head Weight': '1.25 Kilograms',
      'Handle Length': '24 inches Seasoned Sal Wood',
      'Blade Edge': 'Precision Tempered Wedge',
      'Origin': 'Hardoi, Uttar Pradesh'
    },
    stockStatus: 'In Stock',
    isBestSeller: true,
    isTool: true,
    tags: ['kulhadi', 'axe', 'woodcutting', 'ss industries', 'hardoi', 'iron tool'],
    isDemoData: false
  },
  {
    id: 'prod-tool-05',
    code: 'GGH-TL-105',
    name: 'Double-Edged Sugarcane Harvesting Sickle (Ganna Kataiya)',
    slug: 'sugarcane-harvesting-sickle-ganna-kataiya',
    category: 'Farming & Agricultural Tools',
    categorySlug: 'farming-traditional-tools',
    subCategory: 'Harvesting Implements',
    price: 349,
    originalPrice: 480,
    discount: 27,
    rating: 4.88,
    reviewCount: 62,
    image: 'https://images.unsplash.com/photo-1589923188900-85dae523342b?auto=format&fit=crop&w=600&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1589923188900-85dae523342b?auto=format&fit=crop&w=800&q=80'
    ],
    seller: {
      id: 'seller-1',
      name: 'S S Industries Agro & Tool Works',
      slug: 'ss-industries-hardoi',
      type: 'Cottage Industry',
      location: 'Madhoganj Baghauli Road, Newada',
      district: 'Hardoi',
      state: 'Uttar Pradesh',
      verified: true,
      rating: 4.96,
      avatar: 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=150&q=80',
      phone: '+91 7897492100'
    },
    description: 'Specially weighted curved blade designed specifically for thick sugarcane cane cutting without splintering stalks. Features rear debarking hook.',
    story: 'Tailored for the sugarcane belts of Hardoi, Lakhimpur, and Sitapur by S S Industries craftsmen.',
    attributes: {
      'Blade Thickness': '4 mm',
      'Overall Length': '16 inches',
      'Grip': 'Shock-absorbing Wrapped Rubberized Wood'
    },
    stockStatus: 'In Stock',
    isTool: true,
    tags: ['sugarcane', 'sickle', 'ganna katai', 'hardoi'],
    isDemoData: false
  },
  {
    id: 'prod-net-01',
    code: 'GGH-AG-401',
    name: 'Heavy UV-Stabilized Crop Protection & Pond Fishing Net (मछली व फसल सुरक्षा जाल)',
    slug: 'heavy-uv-stabilized-crop-protection-pond-fishing-net',
    category: 'Farming & Agricultural Tools',
    categorySlug: 'farming-traditional-tools',
    subCategory: 'Farm Protection & Fisheries',
    price: 499,
    originalPrice: 699,
    discount: 28,
    rating: 4.93,
    reviewCount: 92,
    image: 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&w=600&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&w=800&q=80'
    ],
    seller: {
      id: 'seller-2',
      name: 'S S Industries Sandila Industrial Hub',
      slug: 'ss-industries-sandila',
      type: 'Cottage Industry',
      location: 'UPSIDA Sandila Industrial Area',
      district: 'Hardoi',
      state: 'Uttar Pradesh',
      verified: true,
      rating: 4.94,
      avatar: 'https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=150&q=80',
      phone: '+91 9005756321'
    },
    description: 'High-density multi-strand polyethylene knitted net with reinforced braided border rope. Ideal for protecting vegetable nurseries and orchards from birds and monkeys, as well as cast netting in village fisheries.',
    story: 'Manufactured and knotted by skilled weavers under S S Industries rural supply chain with UV additives for 5+ years all-weather longevity.',
    attributes: {
      'Size': '10 meters x 3 meters (30 sq meters coverage)',
      'Mesh Size': '15 mm Diamond Mesh',
      'Material': 'UV-Resistant Virgin HDPE Monofilament'
    },
    stockStatus: 'In Stock',
    isTool: true,
    tags: ['net', 'fishing net', 'crop net', 'bird net', 'jaal', 'farm protection'],
    isDemoData: false
  },

  // 2. Workshop & Iron Implements
  {
    id: 'prod-hard-01',
    code: 'GGH-HD-001',
    name: 'Hand-Forged Blacksmith Sledge Hammer (3 kg Head)',
    slug: 'hand-forged-blacksmith-sledge-hammer-3kg',
    category: 'Workshop & Iron Implements',
    categorySlug: 'hardware-cottage-implements',
    subCategory: 'Hammers & Anvils',
    price: 699,
    originalPrice: 950,
    discount: 26,
    rating: 4.91,
    reviewCount: 76,
    image: 'https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=600&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=800&q=80'
    ],
    seller: {
      id: 'seller-2',
      name: 'S S Industries Sandila Industrial Hub',
      slug: 'ss-industries-sandila',
      type: 'Cottage Industry',
      location: 'UPSIDA Sandila Industrial Area',
      district: 'Hardoi',
      state: 'Uttar Pradesh',
      verified: true,
      rating: 4.94,
      avatar: 'https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=150&q=80',
      phone: '+91 9005756321'
    },
    description: 'Forged EN-9 high-tensile carbon steel hammer block with induction hardened impact faces. Fitted with seasoned Hickory/Sal wood long shaft.',
    story: 'Manufactured by S S Industries for stone cracking, metal shaping, and rural construction work.',
    attributes: {
      'Head Weight': '3000 grams (3 kg)',
      'Shaft Length': '30 inches',
      'Material': 'Drop-Forged Carbon Alloy'
    },
    stockStatus: 'In Stock',
    isBestSeller: true,
    isHardware: true,
    tags: ['hammer', 'sledge hammer', 'hathodi', 'sandila', 'ss industries'],
    isDemoData: false
  },
  {
    id: 'prod-hard-02',
    code: 'GGH-HD-002',
    name: 'Cast Steel Rotary Chaff Cutter Blades (Pair of 2)',
    slug: 'cast-steel-rotary-chaff-cutter-blades',
    category: 'Workshop & Iron Implements',
    categorySlug: 'hardware-cottage-implements',
    subCategory: 'Machinery Spares',
    price: 549,
    originalPrice: 750,
    discount: 27,
    rating: 4.93,
    reviewCount: 112,
    image: 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=600&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=800&q=80'
    ],
    seller: {
      id: 'seller-1',
      name: 'S S Industries Agro & Tool Works',
      slug: 'ss-industries-hardoi',
      type: 'Cottage Industry',
      location: 'Madhoganj Baghauli Road, Newada',
      district: 'Hardoi',
      state: 'Uttar Pradesh',
      verified: true,
      rating: 4.96,
      avatar: 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=150&q=80',
      phone: '+91 7897492100'
    },
    description: 'Precision curved toka blades made from high-strength manganese steel. Tempered edges slice fodder cleanly without jamming the flywheel.',
    story: 'Standard fitting for manual and motorized chaff cutters used across dairy farms in northern India.',
    attributes: {
      'Pack Size': '2 Matched Blades',
      'Steel Grade': 'Manganese Spring Steel',
      'Hole Spacing': 'Standard 4-bolt Universal'
    },
    stockStatus: 'In Stock',
    isHardware: true,
    tags: ['chaff cutter', 'toka blade', 'cattle fodder', 'ss industries'],
    isDemoData: false
  },

  // 3. Handloom Textiles & Apparel (Featuring Chikan Suit)
  {
    id: 'prod-app-01',
    code: 'GGH-TX-201',
    name: 'Handcrafted Pure Cotton Lucknowi Chikan Suit Set with Dupatta (चिकन सूट)',
    slug: 'handcrafted-pure-cotton-lucknowi-chikan-suit-set',
    category: 'Handloom Textiles & Apparel',
    categorySlug: 'handmade-apparel-textiles',
    subCategory: 'Chikankari Women Wear',
    price: 1199,
    originalPrice: 1750,
    discount: 31,
    rating: 4.94,
    reviewCount: 168,
    image: 'https://images.unsplash.com/photo-1583391733956-3750e0ff4e8b?auto=format&fit=crop&w=600&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1583391733956-3750e0ff4e8b?auto=format&fit=crop&w=800&q=80'
    ],
    seller: {
      id: 'seller-4',
      name: 'Awadh Mahila Weavers & Chikan SHG',
      slug: 'awadh-mahila-jute-shg',
      type: 'SHG',
      location: 'Kakori Rural Cluster',
      district: 'Lucknow',
      state: 'Uttar Pradesh',
      verified: true,
      rating: 4.92,
      avatar: 'https://images.unsplash.com/photo-1594744803329-e58b31de8bf5?auto=format&fit=crop&w=150&q=80',
      phone: '+91 94512 88410'
    },
    description: 'Fine hand-embroidered shadow work, bakhiya, and phanda motifs on breathable cambric cotton. Includes unstitched kurta fabric, matching bottom material, and pure chiffon matching dupatta with hand-knotted borders.',
    story: 'Embroidered by skilled rural artisan women in Kakori and Malihabad village clusters keeping centuries-old Awadh needlework alive.',
    attributes: {
      'Fabric': '100% Breathable Cambric Cotton',
      'Embroidery Type': 'Handmade Lucknowi Chikankari',
      'Kurta Length': '2.5 Meters',
      'Bottom Length': '2.5 Meters',
      'Dupatta': '2.25 Meters Chiffon'
    },
    stockStatus: 'In Stock',
    isBestSeller: true,
    isRuralWomenMade: true,
    tags: ['chiken suit', 'chikan suit', 'chikankari', 'lucknow', 'cotton suit', 'women shg'],
    isDemoData: false
  },
  {
    id: 'prod-tex-01',
    code: 'GGH-TX-001',
    name: 'Handloom Pure Khadi Cotton Men\'s Kurta (Natural Off-White)',
    slug: 'handloom-pure-khadi-cotton-mens-kurta-off-white',
    category: 'Handloom Textiles & Apparel',
    categorySlug: 'handmade-apparel-textiles',
    subCategory: 'Men\'s Handloom',
    price: 649,
    originalPrice: 950,
    discount: 32,
    rating: 4.89,
    reviewCount: 145,
    image: 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=600&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=800&q=80'
    ],
    seller: {
      id: 'seller-6',
      name: 'Kashi Handloom Weavers Cooperative',
      slug: 'kashi-handloom-weavers',
      type: 'Cottage Industry',
      location: 'Kotwa, Varanasi',
      district: 'Varanasi',
      state: 'Uttar Pradesh',
      verified: true,
      rating: 4.86,
      avatar: 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=150&q=80',
      phone: '+91 97920 11450'
    },
    description: 'Woven on manual wooden pit looms from hand-spun desi cotton yarns. Extremely breathable in hot summers and naturally soft after every wash.',
    story: 'Woven by hereditary master weavers in rural Varanasi keeping the authentic Swadeshi textile heritage alive.',
    attributes: {
      'Fabric': '100% Handspun Khadi Cotton',
      'Fit': 'Comfort Relaxed Fit',
      'Collar': 'Mandarin Bandhgala Collar',
      'Available Sizes': 'M, L, XL, XXL'
    },
    stockStatus: 'In Stock',
    isBestSeller: true,
    tags: ['khadi kurta', 'handloom', 'varanasi', 'pure cotton'],
    isDemoData: false
  },
  {
    id: 'prod-tex-02',
    code: 'GGH-TX-002',
    name: 'Traditional Handloom Cotton Gamchha (Pack of 3)',
    slug: 'traditional-handloom-cotton-gamchha-pack-of-3',
    category: 'Handloom Textiles & Apparel',
    categorySlug: 'handmade-apparel-textiles',
    subCategory: 'Daily Utility Textiles',
    price: 199,
    originalPrice: 280,
    discount: 29,
    rating: 4.95,
    reviewCount: 310,
    image: 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?auto=format&fit=crop&w=600&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?auto=format&fit=crop&w=800&q=80'
    ],
    seller: {
      id: 'seller-6',
      name: 'Kashi Handloom Weavers Cooperative',
      slug: 'kashi-handloom-weavers',
      type: 'Cottage Industry',
      location: 'Kotwa, Varanasi',
      district: 'Varanasi',
      state: 'Uttar Pradesh',
      verified: true,
      rating: 4.86,
      avatar: 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=150&q=80',
      phone: '+91 97920 11450'
    },
    description: 'High-absorbency, fast-drying village cotton gamchhas with classic red, blue, and green border checks. Indispensable for farming, travel, and bath.',
    story: 'Woven by hand in rural eastern UP, famous for lightweight sweat absorption and skin friendliness.',
    attributes: {
      'Pack Size': '3 Gamchhas',
      'Length': '1.75 meters each',
      'Weave': 'Loose Open Weave Cotton'
    },
    stockStatus: 'In Stock',
    isBestSeller: true,
    tags: ['gamchha', 'towel', 'khadi', 'handloom', 'cotton'],
    isDemoData: false
  },

  // 4. Village Decor, Pottery & Handmade Mats
  {
    id: 'prod-mat-01',
    code: 'GGH-FC-301',
    name: 'Natural Handwoven River Reed & Grass Floor Chatai (Handmade Mat / हाथ से बनी चटाई)',
    slug: 'natural-handwoven-river-reed-grass-floor-chatai-mat',
    category: 'Village Decor & Wooden Toys',
    categorySlug: 'village-home-decor-toys',
    subCategory: 'Rugs & Mats',
    price: 389,
    originalPrice: 550,
    discount: 29,
    rating: 4.91,
    reviewCount: 140,
    image: 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=600&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80'
    ],
    seller: {
      id: 'seller-5',
      name: 'Purvanchal Kauna Grass Weavers SHG',
      slug: 'purvanchal-grass-shg',
      type: 'SHG',
      location: 'Ghaghra River Basin',
      district: 'Ayodhya',
      state: 'Uttar Pradesh',
      verified: true,
      rating: 4.88,
      avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80',
      phone: '+91 93456 78901'
    },
    description: 'Traditional summer sleeping and sitting chatai hand-braided from natural wetlands river reed grass and cotton warp threads. Naturally cool, sweat-free, foldable, and 100% biodegradable.',
    story: 'Harvested from seasonal riverbeds and woven on ancestral bamboo floor looms by village women collectives.',
    attributes: {
      'Dimensions': '6 feet x 3.5 feet (Standard Single Bed/Sitting)',
      'Material': 'Natural River Sedge Grass & Cotton Weft',
      'Care': 'Sun dry and wipe clean with damp cloth'
    },
    stockStatus: 'In Stock',
    isBestSeller: true,
    isRuralWomenMade: true,
    tags: ['handmade mat', 'chatai', 'river grass', 'floor mat', 'kauna'],
    isDemoData: false
  },
  {
    id: 'prod-bsk-01',
    code: 'GGH-FC-302',
    name: 'Traditional Hand-Twined Moonj Grass Storage Basket with Lid (मूँज डलिया)',
    slug: 'traditional-hand-twined-moonj-grass-storage-basket-tokri',
    category: 'Village Decor & Wooden Toys',
    categorySlug: 'village-home-decor-toys',
    subCategory: 'Grass Craft & Baskets',
    price: 349,
    originalPrice: 480,
    discount: 27,
    rating: 4.92,
    reviewCount: 78,
    image: 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?auto=format&fit=crop&w=600&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1584917865442-de89df76afd3?auto=format&fit=crop&w=800&q=80'
    ],
    seller: {
      id: 'seller-4',
      name: 'Awadh Mahila Eco Jute & Cloth SHG',
      slug: 'awadh-mahila-jute-shg',
      type: 'SHG',
      location: 'Mallawan Rural Cluster, Hardoi',
      district: 'Hardoi',
      state: 'Uttar Pradesh',
      verified: true,
      rating: 4.92,
      avatar: 'https://images.unsplash.com/photo-1594744803329-e58b31de8bf5?auto=format&fit=crop&w=150&q=80',
      phone: '+91 94512 88410'
    },
    description: 'Eco-friendly, sturdy village utility storage basket hand-coiled from wild Moonj and Kush grass. Includes fitted lid and handles, used for storing grains, rotis, dry fruits, and handcrafted artifacts.',
    story: 'Traditional craft of Hardoi and Sitapur women artisans using wild riverbank grasses naturally dried and coiled.',
    attributes: {
      'Diameter': '10 inches',
      'Height': '8 inches with Dome Lid',
      'Material': '100% Wild Moonj Grass & Cotton Binding'
    },
    stockStatus: 'In Stock',
    isRuralWomenMade: true,
    tags: ['moonj', 'tokri', 'basket', 'daliya', 'grass craft', 'women shg'],
    isDemoData: false
  },
  {
    id: 'prod-pot-01',
    code: 'GGH-PT-501',
    name: 'Pure Desi Clay Terracotta Cooking Handi with Lid (मिट्टी की हांडी)',
    slug: 'pure-desi-clay-terracotta-cooking-handi-with-lid',
    category: 'Village Decor & Wooden Toys',
    categorySlug: 'village-home-decor-toys',
    subCategory: 'Earthenware & Pottery',
    price: 320,
    originalPrice: 450,
    discount: 29,
    rating: 4.95,
    reviewCount: 186,
    image: 'https://images.unsplash.com/photo-1594744803329-e58b31de8bf5?auto=format&fit=crop&w=600&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1594744803329-e58b31de8bf5?auto=format&fit=crop&w=800&q=80'
    ],
    seller: {
      id: 'seller-7',
      name: 'Gorakhpur Earthen Clay Artisans Guild',
      slug: 'gorakhpur-terracotta-artisans',
      type: 'Artisan',
      location: 'Naurangabad Pottery Hub',
      district: 'Gorakhpur',
      state: 'Uttar Pradesh',
      verified: true,
      rating: 4.92,
      avatar: 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=150&q=80',
      phone: '+91 98390 44210'
    },
    description: 'Wheel-thrown from alluvial soil and wood-kiln fired without lead, glaze, or chemical treatments. Retains 100% food nutrients, delivers rich earthy aroma to village dals and curries, and is safe with heat diffuser.',
    story: 'Crafted on hereditary potter wheels by village master kumhars, preserving ancient Vedic earthen cooking wisdom.',
    attributes: {
      'Capacity': '2.0 Liters',
      'Material': '100% Pure Natural Clay',
      'Kiln Fired': 'Traditional Wood & Husk Kiln'
    },
    stockStatus: 'In Stock',
    isBestSeller: true,
    tags: ['mitti handi', 'terracotta', 'pottery', 'clay pot', 'cooking handi'],
    isDemoData: false
  },
  {
    id: 'prod-toy-01',
    code: 'GGH-TY-001',
    name: 'GI Channapatna Natural Wood Stacking Ring Toy',
    slug: 'gi-channapatna-natural-wood-stacking-ring-toy',
    category: 'Village Decor & Wooden Toys',
    categorySlug: 'village-home-decor-toys',
    subCategory: 'Wooden Toys',
    price: 349,
    originalPrice: 480,
    discount: 27,
    rating: 4.94,
    reviewCount: 182,
    image: 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=600&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=800&q=80'
    ],
    seller: {
      id: 'seller-5',
      name: 'Purvanchal Kauna Grass & Toy SHG',
      slug: 'purvanchal-grass-shg',
      type: 'SHG',
      location: 'Ayodhya Craft Hub',
      district: 'Ayodhya',
      state: 'Uttar Pradesh',
      verified: true,
      rating: 4.88,
      avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80',
      phone: '+91 93456 78901'
    },
    description: 'Turned on traditional wood lathes using seasoned soft ivory wood. Colored exclusively with food-safe non-toxic natural vegetable lac extracts.',
    story: 'Centuries-old craft patronized by village toy makers, completely safe for toddlers.',
    attributes: {
      'Material': 'Aale Mara (Ivory Wood)',
      'Colors': 'Natural Vegetable Lac Dyes (Turmeric, Indigo)',
      'Age Group': '1+ Years Safe'
    },
    stockStatus: 'In Stock',
    isBestSeller: true,
    tags: ['wooden toy', 'channapatna', 'safe toy', 'natural wood'],
    isDemoData: false
  },

  // 5. Eco Jute Bags & Storage
  {
    id: 'prod-jute-01',
    code: 'GGH-JT-001',
    name: 'Heavy-Duty 50kg Braided Jute Burlap Grain Sack (जूट की बोरी)',
    slug: 'heavy-duty-50kg-braided-jute-burlap-grain-sack',
    category: 'Eco Jute Bags & Cloth Jholas',
    categorySlug: 'jute-bags-cloth-jholas',
    subCategory: 'Farm Grain Storage',
    price: 149,
    originalPrice: 220,
    discount: 32,
    rating: 4.96,
    reviewCount: 220,
    image: 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=600&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=800&q=80'
    ],
    seller: {
      id: 'seller-4',
      name: 'Awadh Mahila Eco Jute & Cloth SHG',
      slug: 'awadh-mahila-jute-shg',
      type: 'SHG',
      location: 'Mallawan Rural Cluster, Hardoi',
      district: 'Hardoi',
      state: 'Uttar Pradesh',
      verified: true,
      rating: 4.92,
      avatar: 'https://images.unsplash.com/photo-1594744803329-e58b31de8bf5?auto=format&fit=crop&w=150&q=80',
      phone: '+91 94512 88410'
    },
    description: 'Double-stitched coarse natural golden jute sack capable of carrying up to 50kg of wheat, rice, potatoes, or pulses. Breathable weave prevents moisture build-up and fungal spoilage in village godowns.',
    story: 'Woven by women self-help collectives in Hardoi using high-strength natural jute yarn.',
    attributes: {
      'Capacity': '50 kg Grain Weight',
      'Material': '100% Biodegradable Natural Jute',
      'Stitching': 'Heavy 3-ply Jute Thread Hemmed'
    },
    stockStatus: 'In Stock',
    isBestSeller: true,
    tags: ['jute sack', 'bori', 'grain storage', 'jute bag', 'wheat storage'],
    isDemoData: false
  },
  {
    id: 'prod-jute-02',
    code: 'GGH-JT-002',
    name: 'Multi-Compartment Hand-Embroidered Jute Market Bag (झोला)',
    slug: 'multi-compartment-hand-embroidered-jute-market-bag',
    category: 'Eco Jute Bags & Cloth Jholas',
    categorySlug: 'jute-bags-cloth-jholas',
    subCategory: 'Totes & Market Jholas',
    price: 299,
    originalPrice: 420,
    discount: 29,
    rating: 4.88,
    reviewCount: 94,
    image: 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=600&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=800&q=80'
    ],
    seller: {
      id: 'seller-4',
      name: 'Awadh Mahila Eco Jute & Cloth SHG',
      slug: 'awadh-mahila-jute-shg',
      type: 'SHG',
      location: 'Mallawan Rural Cluster, Hardoi',
      district: 'Hardoi',
      state: 'Uttar Pradesh',
      verified: true,
      rating: 4.92,
      avatar: 'https://images.unsplash.com/photo-1594744803329-e58b31de8bf5?auto=format&fit=crop&w=150&q=80',
      phone: '+91 94512 88410'
    },
    description: 'Sturdy shopping tote with padded cotton rope handles, water-resistant interior lining, and 4 separate compartments for vegetables and milk bottles.',
    story: 'Designed to replace single-use plastic bags in rural and suburban haat bazaars.',
    attributes: {
      'Weight Capacity': '15 kg',
      'Material': 'Golden Jute with Cotton Lining',
      'Closure': 'Heavy Zip Top'
    },
    stockStatus: 'In Stock',
    isRuralWomenMade: true,
    tags: ['jute tote', 'jhola', 'eco bag', 'market bag', 'women shg'],
    isDemoData: false
  },

  // 6. Rural Machinery & Seed Sowing
  {
    id: 'prod-mach-01',
    code: 'GGH-MC-001',
    name: 'Single-Step Push Seed Dibbler & Furrow Planter (बीज रोपाई यंत्र)',
    slug: 'single-step-push-seed-dibbler-planter',
    category: 'Rural Utility & Manual Machinery',
    categorySlug: 'rural-utility-machinery',
    subCategory: 'Planting Equipment',
    price: 1299,
    originalPrice: 1750,
    discount: 26,
    rating: 4.91,
    reviewCount: 52,
    image: 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=600&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=800&q=80'
    ],
    seller: {
      id: 'seller-1',
      name: 'S S Industries Agro & Tool Works',
      slug: 'ss-industries-hardoi',
      type: 'Cottage Industry',
      location: 'Madhoganj Baghauli Road, Newada',
      district: 'Hardoi',
      state: 'Uttar Pradesh',
      verified: true,
      rating: 4.96,
      avatar: 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=150&q=80',
      phone: '+91 7897492100'
    },
    description: 'Labor-saving manual seed planter designed by S S Industries engineers. Penetrates ground, drops measured seed, and closes furrow in one single walking step without bending back.',
    story: 'Built to reduce ergonomic strain on small farmers during gram, maize, and mustard sowing season.',
    attributes: {
      'Material': 'Galvanized Steel & Heavy ABS Seed Barrel',
      'Depth Control': 'Adjustable 1.5 to 3 inches',
      'Weight': '2.1 kg'
    },
    stockStatus: 'In Stock',
    isTool: true,
    tags: ['seed planter', 'dibbler', 'sowing machine', 'hardoi', 'ss industries'],
    isDemoData: false
  },
  {
    id: 'prod-mach-02',
    code: 'GGH-MC-002',
    name: 'Handcrafted Wild Bamboo Winnowing Supa Tray (सूप)',
    slug: 'handcrafted-wild-bamboo-winnowing-supa-tray',
    category: 'Rural Utility & Manual Machinery',
    categorySlug: 'rural-utility-machinery',
    subCategory: 'Winnowing & Chaff Separation',
    price: 189,
    originalPrice: 260,
    discount: 27,
    rating: 4.94,
    reviewCount: 160,
    image: 'https://images.unsplash.com/photo-1584589167171-541ce45f1eea?auto=format&fit=crop&w=600&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1584589167171-541ce45f1eea?auto=format&fit=crop&w=800&q=80'
    ],
    seller: {
      id: 'seller-4',
      name: 'Awadh Mahila Eco Jute & Cloth SHG',
      slug: 'awadh-mahila-jute-shg',
      type: 'SHG',
      location: 'Mallawan Rural Cluster, Hardoi',
      district: 'Hardoi',
      state: 'Uttar Pradesh',
      verified: true,
      rating: 4.92,
      avatar: 'https://images.unsplash.com/photo-1594744803329-e58b31de8bf5?auto=format&fit=crop&w=150&q=80',
      phone: '+91 94512 88410'
    },
    description: 'Hand-split bamboo winnowing fan tray used in every village home for separating chaff from wheat, lentils, and paddy. Bound with cane bark edges.',
    story: 'Woven by artisan families using mature wild bamboo treated with neem water for pest resistance.',
    attributes: {
      'Dimensions': '18 x 16 inches',
      'Material': '100% Wild Natural Bamboo',
      'Weight': '320 grams'
    },
    stockStatus: 'In Stock',
    isRuralWomenMade: true,
    tags: ['supa', 'soop', 'winnowing', 'bamboo craft', 'chaff separation'],
    isDemoData: false
  },

  // 7. Handmade by Rural Women
  {
    id: 'prod-women-01',
    code: 'GGH-WM-001',
    name: 'Traditional Natural Lac Glass-Embedded Bangle Set (Pack of 12)',
    slug: 'traditional-natural-lac-glass-embedded-bangle-set',
    category: 'Handmade by Rural Women',
    categorySlug: 'handmade-by-rural-women',
    subCategory: 'Handmade Jewelry',
    price: 399,
    originalPrice: 600,
    discount: 33,
    rating: 4.93,
    reviewCount: 124,
    image: 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?auto=format&fit=crop&w=600&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?auto=format&fit=crop&w=800&q=80'
    ],
    seller: {
      id: 'seller-5',
      name: 'Purvanchal Kauna Grass & Toy SHG',
      slug: 'purvanchal-grass-shg',
      type: 'SHG',
      location: 'Ayodhya Craft Hub',
      district: 'Ayodhya',
      state: 'Uttar Pradesh',
      verified: true,
      rating: 4.88,
      avatar: 'https://images.unsplash.com/photo-1567532939604-b6b5b0db2604?auto=format&fit=crop&w=150&q=80',
      phone: '+91 93456 78901'
    },
    description: 'Hand-rolled natural insect resin (lac) heated and pressed with mirror micro-sequins and colorful silk threads. Traditional bridal and festival wrist wear.',
    story: 'Artisans heat organic lac over charcoal fires and shape each bangle with seasoned wooden tools.',
    attributes: {
      'Set Count': '12 Pieces',
      'Sizes Available': '2.4, 2.6, 2.8',
      'Base Material': '100% Natural Lac Resin'
    },
    stockStatus: 'In Stock',
    isBestSeller: true,
    isRuralWomenMade: true,
    tags: ['lac bangles', 'jaipur', 'women shg', 'jewelry', 'handcrafted'],
    isDemoData: false
  },
  {
    id: 'prod-women-02',
    code: 'GGH-WM-002',
    name: 'Hand-Knitted Macrame Boho Wall Hanging Tapestry',
    slug: 'hand-knitted-macrame-boho-wall-hanging-tapestry',
    category: 'Handmade by Rural Women',
    categorySlug: 'handmade-by-rural-women',
    subCategory: 'Home Decor',
    price: 499,
    originalPrice: 750,
    discount: 33,
    rating: 4.91,
    reviewCount: 94,
    image: 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=600&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=800&q=80'
    ],
    seller: {
      id: 'seller-4',
      name: 'Awadh Mahila Eco Jute & Cloth SHG',
      slug: 'awadh-mahila-jute-shg',
      type: 'SHG',
      location: 'Mallawan Rural Cluster, Hardoi',
      district: 'Hardoi',
      state: 'Uttar Pradesh',
      verified: true,
      rating: 4.92,
      avatar: 'https://images.unsplash.com/photo-1594744803329-e58b31de8bf5?auto=format&fit=crop&w=150&q=80',
      phone: '+91 94512 88410'
    },
    description: 'Intricately knotted from unbleached natural cotton cords onto a drift wood branch. Adds rustic warmth and bohemian aesthetics to living rooms and balconies.',
    story: 'Crafted by young women trained under SHG skill livelihood centers in Hardoi district.',
    attributes: {
      'Width': '18 inches',
      'Length': '28 inches',
      'Cord': '100% Pure Virgin Cotton Cord'
    },
    stockStatus: 'In Stock',
    isRuralWomenMade: true,
    tags: ['macrame', 'wall hanging', 'cotton decor', 'women shg'],
    isDemoData: false
  }
];
