import { BlogPost } from '../types';

export const mockBlogCategories = [
  'All',
  'Gaon Ki Rasoi',
  'Desi Food',
  'Mahila Udyamita',
  'Handmade Products',
  'Rural Business',
  'Farming & Traditional Tools',
  'Village Lifestyle'
];

export const mockBlogPosts: BlogPost[] = [
  {
    id: 'post-1',
    title: 'The Lost Art of Bilona Ghee: Why Slow-Churned Gir Cow Ghee Heals From Within',
    slug: 'lost-art-of-bilona-ghee-gir-cow-benefits',
    excerpt: 'Explore how ancient Indian Vedic households churned curd with wooden bilonas over slow cow-dung fires to produce real A2 elixir, far superior to industrial factory butter ghee.',
    content: `
      <h2>The Sacred Science of Vedic Bilona</h2>
      <p>In modern supermarket aisles, nearly all "pure ghee" is produced by mechanically skimming raw cream from mixed milk and boiling it at extreme industrial temperatures. While this produces high yield and uniform texture, it strips the ghee of its true therapeutic properties.</p>
      <p>True Vedic ghee begins with whole milk from indigenous, free-grazing Gir cows. The warm milk is gently cultured into whole curd overnight in terracotta pots. At dawn, Maldhari women churn this curd with two-way wooden churners (bilonas) powered by hand or slow bi-directional ropes.</p>
      
      <h3>The 5 Steps of Vedic Ghee Making:</h3>
      <ul>
        <li><strong>Step 1 - Desi Cow Milk:</strong> Sourced exclusively from humped Gir cows containing the natural A2 beta-casein protein.</li>
        <li><strong>Step 2 - Whole Curd Fermentation:</strong> Milk is boiled in clay pots and cultured into thick, probiotic-rich curd.</li>
        <li><strong>Step 3 - Wooden Bilona Churning:</strong> Curd is churned clockwise and counterclockwise to separate the golden <em>makkhan</em> (butter) from nutritious buttermilk.</li>
        <li><strong>Step 4 - Slow Wood-Fire Heating:</strong> The butter is clarified over slow cow-dung or neem wood coals, releasing sweet nutty aromas.</li>
        <li><strong>Step 5 - Danedaar Cooling:</strong> Hand-filtered through fine muslin cloth into sterilized glass jars.</li>
      </ul>

      <blockquote>"Bilona ghee contains butyric acid, natural omega-3 and omega-9 fatty acids, and fat-soluble vitamins A, D, E, and K that protect gut lining and boost natural immunity."</blockquote>

      <h2>Bringing the Village Churn to City Tables</h2>
      <p>Through GanvGaliHut, cooperative gaushalas in Jasdan and Saurashtra deliver this golden nectar straight from village courtyards to your kitchen, without middlemen adulteration.</p>
    `,
    category: 'Desi Food',
    categorySlug: 'desi-food',
    image: 'https://images.unsplash.com/photo-1589301760014-d929f3979dbc?auto=format&fit=crop&w=800&q=80',
    author: {
      name: 'Sunita Devi',
      role: 'Rural Dairy Custodian & Maldhari Elder',
      avatar: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=200&q=80'
    },
    publishedAt: 'October 15, 2026',
    readTime: '6 min read',
    featured: true,
    tags: ['A2 Ghee', 'Bilona', 'Desi Cow', 'Ayurveda', 'Gaon Ki Rasoi'],
    status: 'Published',
    seo: {
      title: 'The Lost Art of Bilona Ghee: Why Slow-Churned Gir Cow Ghee Heals',
      metaDescription: 'Learn why traditional Vedic Bilona Ghee made from Gir cow curd is the ultimate superfood for digestion and immunity.',
      canonicalUrl: 'https://ganvgalihat.com/blog/lost-art-of-bilona-ghee-gir-cow-benefits',
      ogImage: 'https://images.unsplash.com/photo-1589301760014-d929f3979dbc?auto=format&fit=crop&w=800&q=80',
      indexRobots: true
    }
  },
  {
    id: 'post-2',
    title: 'From Courtyard to Commerce: How 18 Barabanki Women Built a Papad & Pickle Empire',
    slug: 'courtyard-to-commerce-barabanki-women-shg-success',
    excerpt: 'Discover the inspiring journey of Saraswati Mahila Bachat Gat, who turned ancestral spice recipes and terrace sun-drying into economic freedom and village dignity.',
    content: `
      <h2>A Quiet Revolution in Dewa Village</h2>
      <p>Eight years ago, Radha Verma and five neighboring homemakers in Dewa, Barabanki had never had bank accounts in their own names. Money was tight between crop harvest seasons, and household savings were precarious.</p>
      <p>What they did have was inherited mastery: the exact proportions of Hathras hing, stone-cracked cumin, and black pepper needed to roll translucent, blister-free moong dal papads that crisp up with a single touch of hot oil.</p>

      <h3>Overcoming Initial Hesitations</h3>
      <p>Starting with just ₹1,500 collected from small savings, the women purchased raw unpolished moong dal, cold-pressed mustard oil, and clean white cotton drying sheets. Working together under the shade of a neem tree, they began rolling 5 kilograms of papads daily.</p>
      
      <h3>The Turning Point: Direct-to-Consumer Marketplaces</h3>
      <p>Local traders often paid them a meager ₹40 per kilo, reselling the same papads in Lucknow for ₹160. Connecting directly to conscious urban buyers on GanvGaliHut transformed their enterprise:</p>
      <ul>
        <li>Direct earnings tripled from ₹40 to ₹140 per pack.</li>
        <li>18 women now earn regular independent monthly incomes.</li>
        <li>Every daughter in the collective is now enrolled in senior secondary school or college.</li>
      </ul>

      <blockquote>"When a customer in Bangalore or Delhi writes a review saying our pickle tastes just like their dadi's cooking, our hearts fill with pride." — Radha Verma, SHG Leader</blockquote>
    `,
    category: 'Mahila Udyamita',
    categorySlug: 'mahila-udyamita',
    image: 'https://images.unsplash.com/photo-1594744803329-e58b31de8bf5?auto=format&fit=crop&w=800&q=80',
    author: {
      name: 'Radha Verma',
      role: 'President, Saraswati Mahila SHG',
      avatar: 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=200&q=80'
    },
    publishedAt: 'October 12, 2026',
    readTime: '5 min read',
    featured: false,
    tags: ['Women SHG', 'Empowerment', 'Papad', 'Pickles', 'Rural Business'],
    status: 'Published',
    seo: {
      title: 'From Courtyard to Commerce: Barabanki Women SHG Story | GanvGaliHut',
      metaDescription: 'The inspiring story of 18 village women in Barabanki who created a sustainable artisan business selling handmade papad and pickles.',
      canonicalUrl: 'https://ganvgalihat.com/blog/courtyard-to-commerce-barabanki-women-shg-success',
      ogImage: 'https://images.unsplash.com/photo-1594744803329-e58b31de8bf5?auto=format&fit=crop&w=800&q=80',
      indexRobots: true
    }
  },
  {
    id: 'post-3',
    title: 'Anatomy of the Desi Hansiya: Why Hand-Forged Village Sickles Outperform Factory Tools',
    slug: 'anatomy-of-desi-hansiya-hand-forged-village-tools',
    excerpt: 'Village blacksmith Ramkishan Lohar explains the metallurgical wisdom behind hammer-forged carbon steel blades and why ergonomics matter during harvest marathons.',
    content: `
      <h2>The Heat, the Anvil, and the Song of Carbon Steel</h2>
      <p>Inside the soot-blackened workshop in Deedwana, Rajasthan, the charcoal bellows roar with a rhythmic breath that has not ceased for four generations. Ramkishan Lohar pulls a cherry-red leaf spring from the coals and swings a heavy 4-pound hammer with surgical precision.</p>

      <h3>Why Leaf Spring Forging Matters</h3>
      <p>Unlike modern mass-produced garden tools stamped out of thin sheet metal that bend on the first encounter with a hidden stone, the traditional <em>Desi Hansiya</em> is forged from high-carbon vehicular spring steel. This material possesses an extraordinary memory: it absorbs heavy vibration and holds a razor edge across months of intense use.</p>

      <h3>The Secret of Hand-Chiseled Teeth (Danda)</h3>
      <p>Factory sickles use machine grindings that quickly dull. A village lohar cuts each micro-tooth individually using a hardened cold chisel at a precise 45-degree angle. This creates a natural micro-serration that saws effortlessly through fibrous stalks of wheat, bajra, and thorny weeds.</p>

      <blockquote>"A machine stamps a shape; a blacksmith tempers the soul of the iron."</blockquote>
    `,
    category: 'Farming & Traditional Tools',
    categorySlug: 'farming-traditional-tools',
    image: 'https://images.unsplash.com/photo-1504917599217-d4dc5ebe6122?auto=format&fit=crop&w=800&q=80',
    author: {
      name: 'Ramkishan Lohar',
      role: 'Master Ironsmith & Toolmaker',
      avatar: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=200&q=80'
    },
    publishedAt: 'October 08, 2026',
    readTime: '7 min read',
    featured: false,
    tags: ['Farming Tools', 'Hansiya', 'Blacksmith', 'Hand Forged', 'Agriculture'],
    status: 'Published',
    seo: {
      title: 'Anatomy of the Desi Hansiya: Hand-Forged Village Sickles vs Factory Tools',
      metaDescription: 'Discover the traditional blacksmith craft of hand-forged sickles, khurpis, and fawdas in rural India.',
      canonicalUrl: 'https://ganvgalihat.com/blog/anatomy-of-desi-hansiya-hand-forged-village-tools',
      ogImage: 'https://images.unsplash.com/photo-1504917599217-d4dc5ebe6122?auto=format&fit=crop&w=800&q=80',
      indexRobots: true
    }
  },
  {
    id: 'post-4',
    title: 'Plastic-Free Homes: How Bengal Golden Jute and Sabai Grass Replace Synthetic Waste',
    slug: 'plastic-free-homes-bengal-golden-jute-sabai-grass',
    excerpt: 'From sturdy vegetable jholas to handwoven fruit baskets, explore how village natural fibers provide beautiful, 100% biodegradable solutions for urban households.',
    content: `
      <h2>Rediscovering Nature’s Miracle Fiber</h2>
      <p>Long before petroleum-based plastic bags and polyester totes took over our shopping routines, Indian households relied on the humble <em>jute ka jhola</em>. Jute is one of the most environmentally benign crops on Earth: it absorbs more carbon dioxide per hectare than tropical trees and enriches soil microbiology.</p>

      <h3>The Sustainable Crafts of the Sundarbans</h3>
      <p>In the delta villages of North 24 Parganas, women artisans take raw golden jute retted in fresh pond water and spin it into thick, tactile threads. These threads are woven on wooden handlooms into durable, washable shopping bags capable of lifting 20 kilograms without snapping.</p>
      
      <h3>Complementing with Wild Sabai Grass</h3>
      <p>Further west, tribal women harvest wild Sabai grass from forest margins, coiling and braiding it into handsome lidded baskets, dining table mats, and roti containers that look stunning in modern kitchens while decomposing safely when retired.</p>
    `,
    category: 'Handmade Products',
    categorySlug: 'handmade-products',
    image: 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=800&q=80',
    author: {
      name: 'Aparna Mondal',
      role: 'Jute Artisan Coordinator',
      avatar: 'https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&w=200&q=80'
    },
    publishedAt: 'September 28, 2026',
    readTime: '4 min read',
    featured: false,
    tags: ['Jute Bags', 'Eco Friendly', 'Sabai Grass', 'Zero Waste', 'Sustainable Living'],
    status: 'Published',
    seo: {
      title: 'Plastic-Free Homes with Bengal Jute & Sabai Grass | GanvGaliHut',
      metaDescription: 'Replace single-use plastics with eco-friendly handwoven golden jute bags and wild grass baskets crafted by rural Indian women.',
      canonicalUrl: 'https://ganvgalihat.com/blog/plastic-free-homes-bengal-golden-jute-sabai-grass',
      ogImage: 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=800&q=80',
      indexRobots: true
    }
  },
  {
    id: 'post-5',
    title: 'Why Lakadong Turmeric is the Queen of Healing Spices',
    slug: 'why-lakadong-turmeric-is-queen-of-healing-spices',
    excerpt: 'Deep in the misty hills of northeast India and Kerala forest tracts, a unique golden rhizome boasts over 8% natural curcumin—triple that of standard market haldi.',
    content: `
      <h2>The Healing Power of High Curcumin</h2>
      <p>Standard grocery store turmeric powders typically contain between 1.5% and 3% curcumin, and are often heavily diluted with starch and polishing agents. In contrast, pure Lakadong turmeric grown in pristine tribal soils reaches up to 8.5% curcumin naturally.</p>
      
      <p>When combined with cold-pressed virgin coconut oil or warm A2 cow milk and a pinch of black pepper, the bioavailability of curcumin increases by over 2000%, offering deep cellular anti-inflammatory support.</p>
    `,
    category: 'Gaon Ki Rasoi',
    categorySlug: 'gaon-ki-rasoi',
    image: 'https://images.unsplash.com/photo-1615485290382-441e4d049cb5?auto=format&fit=crop&w=800&q=80',
    author: {
      name: 'Vipin Chandran',
      role: 'Agroforestry Researcher & Spice Grower',
      avatar: 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=200&q=80'
    },
    publishedAt: 'September 20, 2026',
    readTime: '5 min read',
    featured: false,
    tags: ['Turmeric', 'Lakadong', 'Curcumin', 'Ayurveda', 'Organic Spices'],
    status: 'Published',
    seo: {
      title: 'Why Lakadong Turmeric is the Queen of Healing Spices | GanvGaliHut',
      metaDescription: 'Discover the extraordinary anti-inflammatory benefits of 8%+ curcumin Lakadong turmeric straight from tribal growers.',
      canonicalUrl: 'https://ganvgalihat.com/blog/why-lakadong-turmeric-is-queen-of-healing-spices',
      ogImage: 'https://images.unsplash.com/photo-1615485290382-441e4d049cb5?auto=format&fit=crop&w=800&q=80',
      indexRobots: true
    }
  }
];
