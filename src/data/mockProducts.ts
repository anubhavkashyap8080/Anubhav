import { Product } from '../types';

export const mockProducts: Product[] = [
  // 1. Farming & Agricultural Tools
  {
    id: 'prod-tool-01',
    name: 'Hand-Forged Desi Hansiya (Tempered Grass Sickle)',
    slug: 'hand-forged-desi-hansiya-sickle',
    category: 'Farming & Agricultural Tools',
    categorySlug: 'farming-traditional-tools',
    subCategory: 'Sickles & Cutting Tools',
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
    name: 'Hardened High-Carbon Steel Garden Khurpi (6-inch Blade)',
    slug: 'hardened-steel-garden-khurpi-6inch',
    category: 'Farming & Agricultural Tools',
    categorySlug: 'farming-traditional-tools',
    subCategory: 'Weeding Trowels',
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
    description: 'Forged straight weeding khurpi with sharp tapering tip designed for aeration, deep root weed extraction, and vegetable garden tillage. Does not bend in hard dry soils.',
    story: 'Engineered at S S Industries Hardoi plant for both urban terrace gardening enthusiasts and extensive rural crop weeding.',
    attributes: {
      'Blade Width': '3 inches',
      'Blade Length': '6 inches',
      'Handle': 'Ergonomic Teak Wood Handle',
      'Thickness': '3.5 mm Heavy Gauge',
      'Origin': 'Hardoi, Uttar Pradesh'
    },
    stockStatus: 'In Stock',
    isBestSeller: true,
    isTool: true,
    tags: ['khurpi', 'weeding', 'garden tools', 'ss industries', 'trowel'],
    isDemoData: false
  },
  {
    id: 'prod-tool-03',
    name: 'Heavy Forged Iron Fawda (Agriculture Spade / Shovel)',
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
    description: 'Solid forged iron earth-digging spade (phawda) with reinforced reinforced eyelet for wooden handles. Ideal for canal digging, farm field leveling, and clay excavation.',
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
    tags: ['fawda', 'spade', 'shovel', 'digging', 'sandila', 'ss industries'],
    isDemoData: false
  },
  {
    id: 'prod-tool-04',
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

  // 2. Workshop & Iron Implements
  {
    id: 'prod-hard-01',
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
    description: 'Precision curved fodder cutter (Kutti machine) blades with slotted mounting holes. Maintains sharpness through hundreds of tons of green sorghum and dry straw.',
    story: 'Standardized replacement blades fitting all standard hand and motor-operated Indian tokas, manufactured by S S Industries.',
    attributes: {
      'Pack Size': '2 Matched Blades',
      'Length': '14.5 inches',
      'Thickness': '5 mm Alloy Steel'
    },
    stockStatus: 'In Stock',
    isBestSeller: true,
    isHardware: true,
    tags: ['kutti blade', 'chaff cutter', 'toka', 'hardoi', 'ss industries'],
    isDemoData: false
  },
  {
    id: 'prod-hard-03',
    name: 'Hand-Forged Wood Carving Chisel Set (4 Sizes)',
    slug: 'hand-forged-wood-carving-chisel-set',
    category: 'Workshop & Iron Implements',
    categorySlug: 'hardware-cottage-implements',
    subCategory: 'Carpentry Implements',
    price: 449,
    originalPrice: 620,
    discount: 28,
    rating: 4.87,
    reviewCount: 54,
    image: 'https://images.unsplash.com/photo-1504917599217-d4dc5ebe6122?auto=format&fit=crop&w=600&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1504917599217-d4dc5ebe6122?auto=format&fit=crop&w=800&q=80'
    ],
    seller: {
      id: 'seller-3',
      name: 'Ramkishan Lohar Traditional Forgery',
      slug: 'ramkishan-lohar-tools',
      type: 'Artisan',
      location: 'Baghauli Village, Hardoi',
      district: 'Hardoi',
      state: 'Uttar Pradesh',
      verified: true,
      rating: 4.88,
      avatar: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=150&q=80',
      phone: '+91 8303578806'
    },
    description: 'Set of 4 heavy woodworking chisels (Rookhi) sizes: 1/4", 1/2", 3/4", and 1". High-carbon spring steel holds a razor edge for wood profiling and mortising.',
    story: 'Handcrafted by village ironsmith Ramkishan Lohar for local cartwrights and door carpenters.',
    attributes: {
      'Sizes': '6mm, 12mm, 19mm, 25mm',
      'Handle': 'Turned Babool Hardwood'
    },
    stockStatus: 'In Stock',
    isHardware: true,
    tags: ['chisels', 'carpentry', 'wood carving', 'iron implements'],
    isDemoData: false
  },

  // 3. Eco Jute Bags & Cloth Jholas
  {
    id: 'prod-jute-01',
    name: 'Heavy-Duty Double-Stitched Golden Jute Market Tote',
    slug: 'heavy-duty-double-stitched-golden-jute-market-tote',
    category: 'Eco Jute Bags & Cloth Jholas',
    categorySlug: 'jute-bags-cloth-jholas',
    subCategory: 'Market Jholas',
    price: 199,
    originalPrice: 280,
    discount: 29,
    rating: 4.94,
    reviewCount: 210,
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
    description: '100% natural biodegradable golden jute fiber woven by rural women artisans. Features padded cotton handles, 18-liter capacity, and reinforced inner cotton lining.',
    story: 'Stitched by women of the Mallawan SHG cluster in Hardoi, providing women daily financial autonomy.',
    attributes: {
      'Capacity': '18 Liters (Holds up to 15 kg)',
      'Dimensions': '15" x 14" x 5"',
      'Handles': 'Padded Soft Webbing Cotton'
    },
    stockStatus: 'In Stock',
    isBestSeller: true,
    isRuralWomenMade: true,
    tags: ['jute bag', 'jhola', 'eco tote', 'hardoi shg', 'plastic free'],
    isDemoData: false
  },
  {
    id: 'prod-jute-02',
    name: 'Artisan Sabai Grass & Jute Storage Basket (Set of 2)',
    slug: 'artisan-sabai-grass-jute-storage-basket-set',
    category: 'Eco Jute Bags & Cloth Jholas',
    categorySlug: 'jute-bags-cloth-jholas',
    subCategory: 'Handwoven Baskets',
    price: 399,
    originalPrice: 550,
    discount: 27,
    rating: 4.88,
    reviewCount: 82,
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
    description: 'Hand-braided wild sabai grass and golden jute twine storage baskets. Perfect for fruits, bathroom organizers, bread, or laundry.',
    story: 'Woven using age-old coiling techniques by women artisans during harvest lull seasons.',
    attributes: {
      'Sizes': 'Medium (8" dia) & Large (11" dia)',
      'Material': 'Natural Sabai Grass & Jute'
    },
    stockStatus: 'In Stock',
    isRuralWomenMade: true,
    tags: ['basket', 'sabai grass', 'jute', 'storage', 'handwoven'],
    isDemoData: false
  },
  {
    id: 'prod-jute-03',
    name: 'Rural Artisan Laptop & Office Messenger Jute Bag',
    slug: 'rural-artisan-laptop-messenger-jute-bag',
    category: 'Eco Jute Bags & Cloth Jholas',
    categorySlug: 'jute-bags-cloth-jholas',
    subCategory: 'Office & Travel',
    price: 499,
    originalPrice: 799,
    discount: 38,
    rating: 4.91,
    reviewCount: 65,
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
    description: 'Eco-professional 15.6" laptop messenger bag made of fine-treated laminated jute fabric with brass zipper hardware, pen slots, and padded shoulder strap.',
    story: 'Brings rural artisan craft right into corporate boardrooms with sustainable style.',
    attributes: {
      'Compatibility': 'Up to 15.6 inch Laptops',
      'Compartments': '3 Pockets with Foam Padding'
    },
    stockStatus: 'In Stock',
    isRuralWomenMade: true,
    tags: ['laptop bag', 'office bag', 'jute', 'sustainable'],
    isDemoData: false
  },

  // 4. Village Decor & Wooden Toys
  {
    id: 'prod-toy-01',
    name: 'GI Channapatna Handcrafted Natural Wooden Stacking Ring Toy',
    slug: 'channapatna-handcrafted-wooden-stacking-ring-toy',
    category: 'Village Decor & Wooden Toys',
    categorySlug: 'village-home-decor-toys',
    subCategory: 'Traditional Toys',
    price: 299,
    originalPrice: 420,
    discount: 29,
    rating: 4.97,
    reviewCount: 240,
    image: 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=600&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=800&q=80'
    ],
    seller: {
      id: 'seller-5',
      name: 'Channapatna Wooden Artisan Guild',
      slug: 'channapatna-wood-guild',
      type: 'Artisan',
      location: 'Channapatna Craft Town',
      district: 'Ramanagara',
      state: 'Karnataka',
      verified: true,
      rating: 4.95,
      avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80',
      phone: '+91 98451 22340'
    },
    description: 'GI-tagged heirloom wooden toy turned on traditional lathes from soft Wrightia Tinctoria (Aale Mara) wood. Colored with 100% natural vegetable dyes (turmeric, kumkum, indigo) — completely safe for toddlers.',
    story: 'Preserving the 200-year lacquerware craft initiated during Tipu Sultan’s reign in Karnataka.',
    attributes: {
      'Material': 'Aale Mara (Ivory Wood)',
      'Colors': 'Natural Turmeric & Herbal Lacquer',
      'Rings': '7 Color Graduated Rings',
      'Child Safe': '100% Non-Toxic'
    },
    stockStatus: 'In Stock',
    isBestSeller: true,
    isCraft: true,
    tags: ['channapatna', 'wooden toy', 'non-toxic', 'baby toy', 'heritage craft'],
    isDemoData: false
  },
  {
    id: 'prod-toy-02',
    name: 'Channapatna Wooden Pull-Along Elephant Toy',
    slug: 'channapatna-wooden-pull-along-elephant-toy',
    category: 'Village Decor & Wooden Toys',
    categorySlug: 'village-home-decor-toys',
    subCategory: 'Traditional Toys',
    price: 349,
    originalPrice: 480,
    discount: 27,
    rating: 4.93,
    reviewCount: 130,
    image: 'https://images.unsplash.com/photo-1566576912321-d58ddd7a6088?auto=format&fit=crop&w=600&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1566576912321-d58ddd7a6088?auto=format&fit=crop&w=800&q=80'
    ],
    seller: {
      id: 'seller-5',
      name: 'Channapatna Wooden Artisan Guild',
      slug: 'channapatna-wood-guild',
      type: 'Artisan',
      location: 'Channapatna Craft Town',
      district: 'Ramanagara',
      state: 'Karnataka',
      verified: true,
      rating: 4.95,
      avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80',
      phone: '+91 98451 22340'
    },
    description: 'Charming smooth wooden elephant on rotating wheels with pull string. Flaps ears gently when pulled across the floor.',
    story: 'Handcrafted by master woodcarver families in Channapatna.',
    attributes: {
      'Age Recommendation': '1 - 6 Years',
      'Weight': '280 grams'
    },
    stockStatus: 'In Stock',
    isCraft: true,
    tags: ['elephant toy', 'channapatna', 'wooden', 'pull toy'],
    isDemoData: false
  },
  {
    id: 'prod-decor-01',
    name: 'Natural Braided Coir Fiber Door Mat (Heavy Duty)',
    slug: 'natural-braided-coir-fiber-door-mat-heavy-duty',
    category: 'Village Decor & Wooden Toys',
    categorySlug: 'village-home-decor-toys',
    subCategory: 'Rugs & Mats',
    price: 279,
    originalPrice: 390,
    discount: 28,
    rating: 4.89,
    reviewCount: 95,
    image: 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=600&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80'
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
    description: 'Handwoven natural coconut coir entrance mat with non-slip natural latex backing. Highly effective at scraping mud and dust.',
    story: 'Coir fiber is spun on traditional village charkhas and hand-tufted by SHG craftswomen.',
    attributes: {
      'Dimensions': '60 cm x 40 cm (1.5 ft x 2 ft)',
      'Thickness': '18 mm Thick Coir Bristles'
    },
    stockStatus: 'In Stock',
    isCraft: true,
    tags: ['doormat', 'coir', 'floor mat', 'entrance mat'],
    isDemoData: false
  },
  {
    id: 'prod-decor-02',
    name: 'Hand-Cast Brass Temple Wind Chime Bells (Cluster of 5)',
    slug: 'hand-cast-brass-temple-wind-chime-bells',
    category: 'Village Decor & Wooden Toys',
    categorySlug: 'village-home-decor-toys',
    subCategory: 'Metal Crafts',
    price: 599,
    originalPrice: 850,
    discount: 30,
    rating: 4.96,
    reviewCount: 110,
    image: 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=600&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=800&q=80'
    ],
    seller: {
      id: 'seller-7',
      name: 'Moradabad Village Bell-Metal Guild',
      slug: 'moradabad-brass-guild',
      type: 'Artisan',
      location: 'Peetal Basti, Moradabad',
      district: 'Moradabad',
      state: 'Uttar Pradesh',
      verified: true,
      rating: 4.89,
      avatar: 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=150&q=80',
      phone: '+91 98371 44520'
    },
    description: 'Deep resonant bells cast from pure bell metal brass alloy using ancient sand casting. Tied with raw jute cord for doorways and temple corners.',
    story: 'Produces a soothing harmonic resonance that purifies home aura and wards off negative vibrations.',
    attributes: {
      'Material': 'Pure Bell Brass (Kansa)',
      'Number of Bells': '5 Tuned Bells',
      'Total Length': '24 inches'
    },
    stockStatus: 'In Stock',
    isBestSeller: true,
    isCraft: true,
    tags: ['brass', 'bells', 'temple chime', 'moradabad', 'home decor'],
    isDemoData: false
  },

  // 5. Handloom Textiles & Apparel
  {
    id: 'prod-tex-01',
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

  // 6. Rural Utility & Manual Machinery
  {
    id: 'prod-mach-01',
    name: 'Manual Single-Row Push Seed & Fertilizer Dibbler',
    slug: 'manual-single-row-push-seed-fertilizer-dibbler',
    category: 'Rural Utility & Manual Machinery',
    categorySlug: 'rural-utility-machinery',
    subCategory: 'Farm Machinery',
    price: 1299,
    originalPrice: 1800,
    discount: 28,
    rating: 4.92,
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
    description: 'Labor-saving manual seed planter designed by S S Industries engineers. Penetrates ground, drops measured seed and closes furrow in one single walking step without bending back.',
    story: 'Eliminates acute back strain for smallholder farmers during wheat, corn, and mustard sowing seasons.',
    attributes: {
      'Seed Compatibility': 'Corn, Wheat, Gram, Soyabean, Peanut',
      'Material': 'Galvanized Steel & Heavy ABS Seed Barrel',
      'Manufacturer': 'S S Industries Hardoi'
    },
    stockStatus: 'In Stock',
    isTool: true,
    tags: ['seed planter', 'dibbler', 'farm equipment', 'ss industries', 'hardoi'],
    isDemoData: false
  },
  {
    id: 'prod-mach-02',
    name: 'Traditional Woven Bamboo Supa (Grain Winnowing Tray)',
    slug: 'traditional-woven-bamboo-supa-winnowing-tray',
    category: 'Rural Utility & Manual Machinery',
    categorySlug: 'rural-utility-machinery',
    subCategory: 'Grain Processing',
    price: 149,
    originalPrice: 220,
    discount: 32,
    rating: 4.94,
    reviewCount: 168,
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
    description: 'Hand-split bamboo winnowing supa crafted with reinforced cane rims. Used across village households for separating chaff, husks, and dust from harvested grains.',
    story: 'Woven by artisan families using mature wild bamboo treated with neem water for pest resistance.',
    attributes: {
      'Material': '100% Wild Natural Bamboo',
      'Dimensions': '18 inches x 15 inches',
      'Weight': '220 grams'
    },
    stockStatus: 'In Stock',
    isBestSeller: true,
    isRuralWomenMade: true,
    tags: ['supa', 'winnowing tray', 'bamboo craft', 'chaff separation'],
    isDemoData: false
  },

  // 7. Handmade by Rural Women
  {
    id: 'prod-women-01',
    name: 'Jaipur Handcrafted Traditional Lac Bangles (Set of 12)',
    slug: 'jaipur-handcrafted-traditional-lac-bangles-set-of-12',
    category: 'Handmade by Rural Women',
    categorySlug: 'handmade-by-rural-women',
    subCategory: 'Artisanal Jewelry',
    price: 299,
    originalPrice: 450,
    discount: 33,
    rating: 4.96,
    reviewCount: 220,
    image: 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?auto=format&fit=crop&w=600&q=80',
    gallery: [
      'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?auto=format&fit=crop&w=800&q=80'
    ],
    seller: {
      id: 'seller-8',
      name: 'Jaipur Rural Lac & Bangles SHG',
      slug: 'jaipur-lac-bangles-shg',
      type: 'SHG',
      location: 'Sanganer Outskirts, Jaipur',
      district: 'Jaipur',
      state: 'Rajasthan',
      verified: true,
      rating: 4.93,
      avatar: 'https://images.unsplash.com/photo-1567532939604-b6b5b0db2604?auto=format&fit=crop&w=150&q=80',
      phone: '+91 94140 77210'
    },
    description: 'Hand-molded natural tree resin bangles adorned with fine brass beads and mirror embellishments. Does not irritate skin and holds rich ceremonial luster.',
    story: 'Molded over hot charcoal irons by village women artisans, keeping alive a 400-year-old royal artisan legacy.',
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
